<?php
/**
 * CYN Tourism - API Transfers Endpoint
 */

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $date = $_GET['date'] ?? date('Y-m-d');

        $transfers = Database::fetchAll(
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
