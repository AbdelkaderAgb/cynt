<?php
/**
 * CYN Tourism - API Partners Endpoint
 */

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 20);
        $offset = ($page - 1) * $limit;

        $partners = Database::fetchAll(
            "SELECT id, company, contact_name, email, phone, address, status, created_at
             FROM partners WHERE status = 'active' ORDER BY company ASC LIMIT $limit OFFSET $offset"
        );

        $total = Database::fetchOne("SELECT COUNT(*) as count FROM partners WHERE status = 'active'")['count'];

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
        $input = json_decode(file_get_contents('php://input'), true);

        $id = Database::insert(
            "INSERT INTO partners (company, contact_name, email, phone, address, status, created_at)
             VALUES (?, ?, ?, ?, ?, 'active', NOW())",
            [
                $input['company'] ?? '',
                $input['contact_name'] ?? '',
                $input['email'] ?? '',
                $input['phone'] ?? '',
                $input['address'] ?? ''
            ]
        );

        jsonResponse([
            'success' => true,
            'id' => $id,
            'message' => 'Partner created successfully'
        ], 201);
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
