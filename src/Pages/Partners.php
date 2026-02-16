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

        $partners = Database::getInstance()->fetchAll(
            "SELECT id, company, contact_name, email, phone, address, status, created_at
             FROM partners WHERE status = 'active' ORDER BY company ASC LIMIT " . intval($limit) . " OFFSET " . intval($offset) . ""
        );

        $total = Database::getInstance()->fetchOne("SELECT COUNT(*) as count FROM partners WHERE status = 'active'")['count'];

        jsonResponse([
            'data' => $partners,
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

        $id = Database::getInstance()->insert('partners', [
            'company' => $input['company'] ?? '',
            'contact_name' => $input['contact_name'] ?? '',
            'email' => $input['email'] ?? '',
            'phone' => $input['phone'] ?? '',
            'address' => $input['address'] ?? '',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        jsonResponse([
            'success' => true,
            'id' => $id,
            'message' => 'Partner created successfully'
        ], 201);
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
