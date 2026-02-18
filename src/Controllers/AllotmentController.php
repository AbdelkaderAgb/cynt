<?php
/**
 * CYN Tourism — AllotmentController
 * Manages room allotments per hotel — allocated rooms, usage tracking, release days.
 * Uses existing allotments table.
 */
class AllotmentController extends Controller
{
    /**
     * List allotments for a hotel
     * GET /hotels/allotments?hotel_id=X
     */
    public function index(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        $hotelId = (int)($_GET['hotel_id'] ?? 0);
        if (!$hotelId) { header('Location: ' . url('hotels/profiles')); exit; }

        $hotel = $db->prepare("SELECT * FROM hotels WHERE id = ?");
        $hotel->execute([$hotelId]);
        $hotel = $hotel->fetch();
        if (!$hotel) { header('Location: ' . url('hotels/profiles')); exit; }

        // Room types
        $rooms = $db->prepare("SELECT * FROM hotel_rooms WHERE hotel_id = ? ORDER BY room_type");
        $rooms->execute([$hotelId]);
        $roomTypes = $rooms->fetchAll();

        // Allotments with room type names
        $stmt = $db->prepare(
            "SELECT a.*, hr.room_type 
             FROM allotments a 
             JOIN hotel_rooms hr ON a.room_type_id = hr.id 
             WHERE a.hotel_id = ? 
             ORDER BY a.date_from"
        );
        $stmt->execute([$hotelId]);
        $allotments = $stmt->fetchAll();

        $this->view('hotel_profiles/allotments', [
            'hotel'      => $hotel,
            'allotments' => $allotments,
            'roomTypes'  => $roomTypes,
            'pageTitle'  => 'Allotments — ' . htmlspecialchars($hotel['name']),
            'activePage' => 'hotel_profiles',
        ]);
    }

    /**
     * Store / update an allotment
     * POST /hotels/allotments/store
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $db = Database::getInstance()->getConnection();

        $hotelId    = (int)($_POST['hotel_id'] ?? 0);
        $id         = (int)($_POST['allotment_id'] ?? 0);
        $roomTypeId = (int)($_POST['room_type_id'] ?? 0);
        $dateFrom   = $_POST['date_from'] ?? '';
        $dateTo     = $_POST['date_to'] ?? '';
        $totalRooms = (int)($_POST['total_rooms'] ?? 0);
        $releaseDays= (int)($_POST['release_days'] ?? 7);
        $status     = $_POST['status'] ?? 'active';

        if (!$hotelId || !$roomTypeId || !$dateFrom || !$dateTo) {
            header('Location: ' . url('hotels/allotments') . '?hotel_id=' . $hotelId . '&error=1');
            exit;
        }

        if ($id) {
            $stmt = $db->prepare(
                "UPDATE allotments SET room_type_id = ?, date_from = ?, date_to = ?, total_rooms = ?, release_days = ?, status = ? WHERE id = ? AND hotel_id = ?"
            );
            $stmt->execute([$roomTypeId, $dateFrom, $dateTo, $totalRooms, $releaseDays, $status, $id, $hotelId]);
        } else {
            $stmt = $db->prepare(
                "INSERT INTO allotments (hotel_id, room_type_id, date_from, date_to, total_rooms, release_days, status) VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$hotelId, $roomTypeId, $dateFrom, $dateTo, $totalRooms, $releaseDays, $status]);
        }

        header('Location: ' . url('hotels/allotments') . '?hotel_id=' . $hotelId . '&saved=1');
        exit;
    }

    /**
     * Delete an allotment
     * GET /hotels/allotments/delete?id=X&hotel_id=Y
     */
    public function delete(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        $id      = (int)($_GET['id'] ?? 0);
        $hotelId = (int)($_GET['hotel_id'] ?? 0);

        if ($id) {
            $db->prepare("DELETE FROM allotments WHERE id = ? AND hotel_id = ?")->execute([$id, $hotelId]);
        }

        header('Location: ' . url('hotels/allotments') . '?hotel_id=' . $hotelId . '&deleted=1');
        exit;
    }

    /**
     * API: Check availability for a hotel + room + date range
     * GET /api/allotments/check?hotel_id=X&room_type_id=Y&date_from=Z&date_to=W
     */
    public function checkApi(): void
    {
        $db = Database::getInstance()->getConnection();

        $hotelId    = (int)($_GET['hotel_id'] ?? 0);
        $roomTypeId = (int)($_GET['room_type_id'] ?? 0);
        $dateFrom   = $_GET['date_from'] ?? '';
        $dateTo     = $_GET['date_to'] ?? '';

        // Find overlapping allotments
        $stmt = $db->prepare(
            "SELECT * FROM allotments 
             WHERE hotel_id = ? AND room_type_id = ? AND status = 'active'
             AND date_from <= ? AND date_to >= ?
             ORDER BY date_from"
        );
        $stmt->execute([$hotelId, $roomTypeId, $dateTo, $dateFrom]);
        $allotments = $stmt->fetchAll();

        $available = 0;
        foreach ($allotments as $a) {
            $available += ($a['total_rooms'] - $a['used_rooms']);
        }

        $this->jsonResponse([
            'success'    => true,
            'available'  => max(0, $available),
            'allotments' => $allotments,
        ]);
    }
}
