<?php
/**
 * CYN Tourism - API Invoices Endpoint
 */

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 20);
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        if (!empty($_GET['status'])) {
            $where[] = "status = ?";
            $params[] = $_GET['status'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $invoices = Database::fetchAll(
            "SELECT id, invoice_no, company_name, hotel_name, total_amount, currency, status, created_at
             FROM invoices $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset",
            $params
        );

        $total = Database::fetchOne(
            "SELECT COUNT(*) as count FROM invoices $whereClause",
            $params
        )['count'];

        jsonResponse([
            'data' => $invoices,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => (int)$total,
                'total_pages' => ceil($total / $limit)
            ]
        ]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        $invoiceNo = 'INV-' . date('Ym') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $id = Database::insert(
            "INSERT INTO invoices (invoice_no, company_name, hotel_name, total_amount, currency, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())",
            [
                $invoiceNo,
                $input['company_name'] ?? '',
                $input['hotel_name'] ?? '',
                $input['total_amount'] ?? 0,
                $input['currency'] ?? 'USD',
                $input['status'] ?? 'pending'
            ]
        );

        jsonResponse([
            'success' => true,
            'id' => $id,
            'invoice_no' => $invoiceNo
        ], 201);
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? 0;

        if (!$id) {
            jsonResponse(['error' => 'ID is required'], 400);
        }

        Database::execute(
            "UPDATE invoices SET status = ? WHERE id = ?",
            [$input['status'], $id]
        );

        jsonResponse(['success' => true, 'message' => 'Invoice updated']);
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
