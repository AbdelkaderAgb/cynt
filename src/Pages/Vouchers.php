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
        // List vouchers
        $page = (int)($_GET['page'] ?? 1);
        $limit = min(max((int)($_GET["limit"] ?? 20), 1), 100);
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        if (!empty($_GET['company'])) {
            $where[] = "company_name LIKE ?";
            $params[] = '%' . $_GET['company'] . '%';
        }

        if (!empty($_GET['date_from'])) {
            $where[] = "pickup_date >= ?";
            $params[] = $_GET['date_from'];
        }

        if (!empty($_GET['date_to'])) {
            $where[] = "pickup_date <= ?";
            $params[] = $_GET['date_to'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $vouchers = Database::getInstance()->fetchAll(
            "SELECT id, voucher_no, company_name, hotel_name, pickup_date, pickup_time,
             pickup_location, dropoff_location, transfer_type, total_pax, flight_number, created_at
             FROM vouchers $whereClause ORDER BY pickup_date DESC LIMIT " . intval($limit) . " OFFSET " . intval($offset) . "",
            $params
        );

        $total = Database::getInstance()->fetchOne(
            "SELECT COUNT(*) as count FROM vouchers $whereClause",
            $params
        )['count'];

        jsonResponse([
            'data' => $vouchers,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => (int)$total,
                'total_pages' => ceil($total / $limit)
            ]
        ]);
        break;

    case 'POST':
        // Create voucher
        $rawInput = json_decode(file_get_contents('php://input'), true) ?: [];
        $input = array_map(function($v) { return is_string($v) ? trim($v) : $v; }, $rawInput);

        // Validation
        $required = ['company_name', 'pickup_date', 'pickup_location', 'dropoff_location'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                jsonResponse(['error' => "Field '$field' is required"], 400);
            }
        }

        // Insert voucher
        $voucherNo = 'VC-' . date('Ym') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);

        $id = Database::getInstance()->insert('vouchers', [
            'voucher_no' => $voucherNo,
            'company_name' => $input['company_name'],
            'hotel_name' => $input['hotel_name'] ?? '',
            'pickup_date' => $input['pickup_date'],
            'pickup_time' => $input['pickup_time'] ?? '',
            'pickup_location' => $input['pickup_location'],
            'dropoff_location' => $input['dropoff_location'],
            'transfer_type' => $input['transfer_type'] ?? 'One Way',
            'total_pax' => $input['total_pax'] ?? 1,
            'flight_number' => $input['flight_number'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        jsonResponse([
            'success' => true,
            'id' => $id,
            'voucher_no' => $voucherNo,
            'message' => 'Voucher created successfully'
        ], 201);
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
