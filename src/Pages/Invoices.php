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
        $page = (int)($_GET['page'] ?? 1);
        $limit = min(max((int)($_GET["limit"] ?? 20), 1), 100);
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        if (!empty($_GET['status'])) {
            $where[] = "status = ?";
            $params[] = $_GET['status'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $invoices = Database::getInstance()->fetchAll(
            "SELECT id, invoice_no, company_name, hotel_name, total_amount, currency, status, created_at
             FROM invoices $whereClause ORDER BY created_at DESC LIMIT " . intval($limit) . " OFFSET " . intval($offset) . "",
            $params
        );

        $total = Database::getInstance()->fetchOne(
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
        $rawInput = json_decode(file_get_contents('php://input'), true) ?: [];
        $input = array_map(function($v) { return is_string($v) ? trim($v) : $v; }, $rawInput);

        $invoiceNo = 'INV-' . date('Ym') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);

        $id = Database::getInstance()->insert('invoices', [
            'invoice_no' => $invoiceNo,
            'company_name' => $input['company_name'] ?? '',
            'hotel_name' => $input['hotel_name'] ?? '',
            'total_amount' => $input['total_amount'] ?? 0,
            'currency' => $input['currency'] ?? 'USD',
            'status' => $input['status'] ?? 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        jsonResponse([
            'success' => true,
            'id' => $id,
            'invoice_no' => $invoiceNo
        ], 201);
        break;

    case 'PUT':
        $rawInput = json_decode(file_get_contents('php://input'), true) ?: [];
        $input = array_map(function($v) { return is_string($v) ? trim($v) : $v; }, $rawInput);
        $id = $input['id'] ?? 0;

        if (!$id) {
            jsonResponse(['error' => 'ID is required'], 400);
        }

        Database::getInstance()->query(
            "UPDATE invoices SET status = ? WHERE id = ?",
            [$input['status'], $id]
        );

        jsonResponse(['success' => true, 'message' => 'Invoice updated']);
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
