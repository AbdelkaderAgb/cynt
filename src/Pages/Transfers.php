<?php
/**
 * CYN Tourism - API Endpoint
 * This file is normally included via index.php API routing.
 * Safety includes added for direct access protection.
 */

// Safety: if accessed directly (not via index.php), load dependencies
if (!function_exists('jsonResponse')) {
    require_once dirname(__DIR__, 2) . '/config/config.php'; require_once dirname(__DIR__, 2) . '/src/Core/Auth.php';
    require_once dirname(__DIR__, 2) . '/src/Core/helpers.php';
    
    // Require API auth for direct access
    header('Content-Type: application/json');
    
    function jsonResponse($data, $status = 200) {
        http_response_code($status);
        echo json_encode($data);
        exit;
    }
    
    // Block direct web access without auth
    if (!Auth::check()) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $date = $_GET['date'] ?? date('Y-m-d');

        $transfers = Database::getInstance()->fetchAll(
            "SELECT v.*, p.company as partner_name
             FROM vouchers v
             LEFT JOIN partners p ON v.company_name = p.company
             WHERE v.pickup_date = ?
             ORDER BY v.pickup_time ASC",
            [$date]
        );

        jsonResponse([
            'date' => $date,
            'count' => count($transfers),
            'data' => $transfers
        ]);
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
