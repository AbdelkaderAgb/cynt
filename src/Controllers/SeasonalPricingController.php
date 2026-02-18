<?php
/**
 * CYN Tourism — SeasonalPricingController
 * Manages pricing seasons and per-room-type rates per hotel.
 * Uses existing pricing_seasons + pricing_rates tables.
 */
class SeasonalPricingController extends Controller
{
    /**
     * List seasons for a hotel (embedded in hotel profile page)
     * GET /hotels/seasons?hotel_id=X
     */
    public function index(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        $hotelId = (int)($_GET['hotel_id'] ?? 0);
        if (!$hotelId) { header('Location: ' . url('hotels/profiles')); exit; }

        // Get hotel info
        $hotel = $db->prepare("SELECT * FROM hotels WHERE id = ?");
        $hotel->execute([$hotelId]);
        $hotel = $hotel->fetch();
        if (!$hotel) { header('Location: ' . url('hotels/profiles')); exit; }

        // Get seasons
        $stmt = $db->prepare("SELECT * FROM pricing_seasons WHERE hotel_id = ? ORDER BY date_from");
        $stmt->execute([$hotelId]);
        $seasons = $stmt->fetchAll();

        // Get room types for this hotel
        $rooms = $db->prepare("SELECT * FROM hotel_rooms WHERE hotel_id = ? ORDER BY room_type");
        $rooms->execute([$hotelId]);
        $roomTypes = $rooms->fetchAll();

        // Get rates for each season
        foreach ($seasons as &$season) {
            $rStmt = $db->prepare(
                "SELECT pr.*, hr.room_type 
                 FROM pricing_rates pr
                 JOIN hotel_rooms hr ON pr.room_type_id = hr.id
                 WHERE pr.season_id = ?"
            );
            $rStmt->execute([$season['id']]);
            $season['rates'] = $rStmt->fetchAll();
        }
        unset($season);

        $this->view('hotel_profiles/seasons', [
            'hotel'     => $hotel,
            'seasons'   => $seasons,
            'roomTypes' => $roomTypes,
            'pageTitle' => __('season') . ' — ' . htmlspecialchars($hotel['name']),
            'activePage'=> 'hotel_profiles',
        ]);
    }

    /**
     * Store / update a season
     * POST /hotels/seasons/store
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $db = Database::getInstance()->getConnection();

        $hotelId    = (int)($_POST['hotel_id'] ?? 0);
        $seasonId   = (int)($_POST['season_id'] ?? 0);
        $name       = trim($_POST['name'] ?? '');
        $dateFrom   = $_POST['date_from'] ?? '';
        $dateTo     = $_POST['date_to'] ?? '';
        $multiplier = (float)($_POST['multiplier'] ?? 1.00);
        $isBlackout = isset($_POST['is_blackout']) ? 1 : 0;

        if (!$hotelId || !$name || !$dateFrom || !$dateTo) {
            header('Location: ' . url('hotels/seasons') . '?hotel_id=' . $hotelId . '&error=1');
            exit;
        }

        if ($seasonId) {
            // Update
            $stmt = $db->prepare(
                "UPDATE pricing_seasons SET name = ?, date_from = ?, date_to = ?, multiplier = ?, is_blackout = ? WHERE id = ? AND hotel_id = ?"
            );
            $stmt->execute([$name, $dateFrom, $dateTo, $multiplier, $isBlackout, $seasonId, $hotelId]);
        } else {
            // Create
            $stmt = $db->prepare(
                "INSERT INTO pricing_seasons (hotel_id, name, date_from, date_to, multiplier, is_blackout) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$hotelId, $name, $dateFrom, $dateTo, $multiplier, $isBlackout]);
            $seasonId = (int)$db->lastInsertId();
        }

        // Save rates
        $rates = $_POST['rates'] ?? [];
        if (is_array($rates) && $seasonId) {
            // Delete old rates
            $db->prepare("DELETE FROM pricing_rates WHERE season_id = ?")->execute([$seasonId]);
            
            $insStmt = $db->prepare(
                "INSERT INTO pricing_rates (season_id, room_type_id, price_single, price_double, price_triple, price_quad, price_child, currency) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            foreach ($rates as $roomId => $r) {
                $insStmt->execute([
                    $seasonId,
                    (int)$roomId,
                    (float)($r['single'] ?? 0),
                    (float)($r['double'] ?? 0),
                    (float)($r['triple'] ?? 0),
                    (float)($r['quad'] ?? 0),
                    (float)($r['child'] ?? 0),
                    $r['currency'] ?? 'USD',
                ]);
            }
        }

        header('Location: ' . url('hotels/seasons') . '?hotel_id=' . $hotelId . '&saved=1');
        exit;
    }

    /**
     * Delete a season
     * GET /hotels/seasons/delete?id=X&hotel_id=Y
     */
    public function delete(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        $id      = (int)($_GET['id'] ?? 0);
        $hotelId = (int)($_GET['hotel_id'] ?? 0);

        if ($id) {
            // Rates cascade-delete via FK
            $db->prepare("DELETE FROM pricing_seasons WHERE id = ? AND hotel_id = ?")->execute([$id, $hotelId]);
        }

        header('Location: ' . url('hotels/seasons') . '?hotel_id=' . $hotelId . '&deleted=1');
        exit;
    }

    /**
     * API: Get rates for a season (JSON)
     * GET /api/seasons/rates?season_id=X
     */
    public function ratesApi(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        $seasonId = (int)($_GET['season_id'] ?? 0);
        $stmt = $db->prepare(
            "SELECT pr.*, hr.room_type 
             FROM pricing_rates pr 
             JOIN hotel_rooms hr ON pr.room_type_id = hr.id 
             WHERE pr.season_id = ?"
        );
        $stmt->execute([$seasonId]);
        $rates = $stmt->fetchAll();

        $this->jsonResponse(['success' => true, 'rates' => $rates]);
    }

    /**
     * API: Check which season applies for a date + hotel
     * GET /api/seasons/check?hotel_id=X&date=YYYY-MM-DD
     */
    public function checkApi(): void
    {
        $db = Database::getInstance()->getConnection();

        $hotelId = (int)($_GET['hotel_id'] ?? 0);
        $date    = $_GET['date'] ?? date('Y-m-d');

        $stmt = $db->prepare(
            "SELECT * FROM pricing_seasons 
             WHERE hotel_id = ? AND date_from <= ? AND date_to >= ?
             ORDER BY is_blackout DESC, multiplier DESC
             LIMIT 1"
        );
        $stmt->execute([$hotelId, $date, $date]);
        $season = $stmt->fetch();

        if ($season) {
            // Get rates for this season
            $rStmt = $db->prepare(
                "SELECT pr.*, hr.room_type 
                 FROM pricing_rates pr 
                 JOIN hotel_rooms hr ON pr.room_type_id = hr.id 
                 WHERE pr.season_id = ?"
            );
            $rStmt->execute([$season['id']]);
            $season['rates'] = $rStmt->fetchAll();
        }

        $this->jsonResponse([
            'success'    => true,
            'season'     => $season ?: null,
            'is_blackout'=> $season ? (bool)$season['is_blackout'] : false,
        ]);
    }
}
