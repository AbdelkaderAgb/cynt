<?php
/**
 * CYN Tourism — RoomingListController
 * Manages guest rooming lists per hotel voucher.
 * Uses existing rooming_list table.
 */
class RoomingListController extends Controller
{
    /**
     * View rooming list for a voucher
     * GET /hotels/rooming-list?voucher_id=X
     */
    public function index(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        $voucherId = (int)($_GET['voucher_id'] ?? 0);
        if (!$voucherId) { header('Location: ' . url('hotel-voucher')); exit; }

        // Get voucher info
        $vStmt = $db->prepare("SELECT * FROM hotel_vouchers WHERE id = ?");
        $vStmt->execute([$voucherId]);
        $voucher = $vStmt->fetch();
        if (!$voucher) { header('Location: ' . url('hotel-voucher')); exit; }

        // Get rooming list
        $stmt = $db->prepare("SELECT * FROM rooming_list WHERE voucher_id = ? ORDER BY id");
        $stmt->execute([$voucherId]);
        $guests = $stmt->fetchAll();

        $this->view('hotels/rooming_list', [
            'voucher'    => $voucher,
            'guests'     => $guests,
            'pageTitle'  => 'Rooming List — #' . ($voucher['voucher_no'] ?? $voucherId),
            'activePage' => 'hotel_voucher',
        ]);
    }

    /**
     * Save rooming list (bulk save from form)
     * POST /hotels/rooming-list/store
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $db = Database::getInstance()->getConnection();

        $voucherId = (int)($_POST['voucher_id'] ?? 0);
        if (!$voucherId) { header('Location: ' . url('hotel-voucher')); exit; }

        // Delete existing entries and re-insert
        $db->prepare("DELETE FROM rooming_list WHERE voucher_id = ?")->execute([$voucherId]);

        $names       = $_POST['guest_name'] ?? [];
        $passports   = $_POST['passport_no'] ?? [];
        $nationalities = $_POST['nationality'] ?? [];
        $roomNumbers = $_POST['room_number'] ?? [];
        $roomTypes   = $_POST['room_type'] ?? [];
        $checkIns    = $_POST['check_in'] ?? [];
        $checkOuts   = $_POST['check_out'] ?? [];
        $notes       = $_POST['guest_notes'] ?? [];

        $stmt = $db->prepare(
            "INSERT INTO rooming_list (voucher_id, guest_name, passport_no, nationality, room_number, room_type, check_in, check_out, notes) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        foreach ($names as $i => $name) {
            $name = trim($name);
            if (empty($name)) continue;

            $stmt->execute([
                $voucherId,
                $name,
                trim($passports[$i] ?? ''),
                trim($nationalities[$i] ?? ''),
                trim($roomNumbers[$i] ?? ''),
                trim($roomTypes[$i] ?? ''),
                $checkIns[$i] ?? null,
                $checkOuts[$i] ?? null,
                trim($notes[$i] ?? ''),
            ]);
        }

        header('Location: ' . url('hotels/rooming-list') . '?voucher_id=' . $voucherId . '&saved=1');
        exit;
    }

    /**
     * Export rooming list as CSV
     * GET /hotels/rooming-list/export?voucher_id=X
     */
    public function export(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        $voucherId = (int)($_GET['voucher_id'] ?? 0);
        $stmt = $db->prepare("SELECT * FROM rooming_list WHERE voucher_id = ? ORDER BY id");
        $stmt->execute([$voucherId]);
        $guests = $stmt->fetchAll();

        // Get voucher info for filename
        $vStmt = $db->prepare("SELECT voucher_no FROM hotel_vouchers WHERE id = ?");
        $vStmt->execute([$voucherId]);
        $voucher = $vStmt->fetch();
        $filename = 'rooming_list_' . ($voucher['voucher_no'] ?? $voucherId) . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        // BOM for Excel UTF-8
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Guest Name', 'Passport No', 'Nationality', 'Room No', 'Room Type', 'Check-in', 'Check-out', 'Notes']);

        foreach ($guests as $g) {
            fputcsv($out, [
                $g['guest_name'],
                $g['passport_no'],
                $g['nationality'],
                $g['room_number'],
                $g['room_type'],
                $g['check_in'] ?? '',
                $g['check_out'] ?? '',
                $g['notes'] ?? '',
            ]);
        }

        fclose($out);
        exit;
    }
}
