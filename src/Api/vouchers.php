<?php
/**
 * CYN Tourism - API Vouchers Endpoint
 */

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // List vouchers
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 20);
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

        $vouchers = Database::fetchAll(
            "SELECT id, voucher_no, company_name, hotel_name, pickup_date, pickup_time,
             pickup_location, dropoff_location, transfer_type, total_pax, flight_number, created_at
             FROM vouchers $whereClause ORDER BY pickup_date DESC LIMIT $limit OFFSET $offset",
            $params
        );

        $total = Database::fetchOne(
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
        $input = json_decode(file_get_contents('php://input'), true);

        // Validation
        $required = ['company_name', 'pickup_date', 'pickup_location', 'dropoff_location'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                jsonResponse(['error' => "Field '$field' is required"], 400);
            }
        }

        // Insert voucher
        $voucherNo = 'VC-' . date('Ym') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $id = Database::insert(
            "INSERT INTO vouchers (voucher_no, company_name, hotel_name, pickup_date, pickup_time,
             pickup_location, dropoff_location, transfer_type, total_pax, flight_number, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $voucherNo,
                $input['company_name'],
                $input['hotel_name'] ?? '',
                $input['pickup_date'],
                $input['pickup_time'] ?? '',
                $input['pickup_location'],
                $input['dropoff_location'],
                $input['transfer_type'] ?? 'One Way',
                $input['total_pax'] ?? 1,
                $input['flight_number'] ?? ''
            ]
        );

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
