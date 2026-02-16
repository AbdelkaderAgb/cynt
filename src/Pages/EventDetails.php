<?php
/**
 * CYN Tourism - Event Details API
 * Fetches details for calendar modal
 */

require_once dirname(__DIR__, 2) . '/config/config.php'; require_once dirname(__DIR__, 2) . '/src/Core/Auth.php';
require_once dirname(__DIR__, 2) . '/src/Core/helpers.php';

header('Content-Type: application/json');

if (!Auth::check()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = intval($_GET['id'] ?? 0);
$type = $_GET['type'] ?? '';

if (!$id || !$type) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    $data = null;
    
    switch ($type) {
        case 'transfer':
            $data = Database::getInstance()->fetchOne("SELECT * FROM vouchers WHERE id = ?", [$id]);
            if ($data) {
                // Formatting for display
                $data['formatted_date'] = date('d/m/Y', strtotime($data['pickup_date']));
                $data['formatted_time'] = substr($data['pickup_time'], 0, 5);
                $data['type_label'] = 'Transfer';
                $data['status_label'] = ucfirst($data['status']);
            }
            break;
            
        case 'tour':
            $data = Database::getInstance()->fetchOne("SELECT * FROM tours WHERE id = ?", [$id]);
             if ($data) {
                $data['formatted_date'] = date('d/m/Y', strtotime($data['tour_date']));
                $data['formatted_time'] = substr($data['meeting_time'], 0, 5);
                $data['type_label'] = 'Tour';
                $data['status_label'] = ucfirst($data['status'] ?? 'Confirmed');
                // Map fields to common names for JS
                $data['pickup_location'] = $data['meeting_point'];
                $data['dropoff_location'] = $data['tour_name']; // Use tour name as destination equivalent
            }
            break;
            
        case 'hotel':
            $data = Database::getInstance()->fetchOne("SELECT * FROM hotel_vouchers WHERE id = ?", [$id]);
            if ($data) {
                $data['formatted_date'] = date('d/m/Y', strtotime($data['check_in']));
                $data['formatted_time'] = '14:00'; // Default check-in
                $data['type_label'] = 'Hotel';
                $data['status_label'] = 'Confirmed';
                $data['pickup_location'] = $data['hotel_name'];
                $data['dropoff_location'] = $data['room_type'];
            }
            break;
            
        default:
            throw new Exception('Invalid type');
    }
    
    if ($data) {
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
