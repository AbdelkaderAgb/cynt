<?php
/**
 * CYN Tourism - API Router
 * RESTful API for mobile app integration
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/src/Core/Auth.php';

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));
$endpoint = end($pathParts);

// Simple API key authentication
function authenticateApi() {
    $headers = getallheaders();
    $apiKey = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $apiKey = str_replace('Bearer ', '', $apiKey);

    // Check API key in database
    $validKey = Database::fetchOne(
        "SELECT * FROM api_keys WHERE api_key = ? AND is_active = 1",
        [$apiKey]
    );

    if (!$validKey) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized - Invalid API Key']);
        exit;
    }
    return $validKey['user_id'];
}

// Response helper
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Handle preflight requests
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Public endpoints (no auth required)
$publicEndpoints = ['login', 'health'];

if (!in_array($endpoint, $publicEndpoints)) {
    $userId = authenticateApi();
}

// Route to appropriate handler
switch ($endpoint) {
    case 'health':
        jsonResponse(['status' => 'ok', 'timestamp' => date('Y-m-d H:i:s')]);
        break;

    case 'login':
        // API login to get token
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        $user = Database::fetchOne(
            "SELECT * FROM users WHERE email = ? AND status = 'active'",
            [$email]
        );

        if ($user && password_verify($password, $user['password'])) {
            // Generate or return API key
            $apiKey = Database::fetchOne(
                "SELECT api_key FROM api_keys WHERE user_id = ? AND is_active = 1 LIMIT 1",
                [$user['id']]
            );
            jsonResponse([
                'success' => true,
                'api_key' => $apiKey['api_key'] ?? null,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name']
                ]
            ]);
        } else {
            jsonResponse(['error' => 'Invalid credentials'], 401);
        }
        break;

    case 'vouchers':
        require_once 'vouchers.php';
        break;

    case 'invoices':
        require_once 'invoices.php';
        break;

    case 'partners':
        require_once 'partners.php';
        break;

    case 'transfers':
        require_once 'transfers.php';
        break;

    case 'dashboard':
        require_once 'dashboard.php';
        break;

    default:
        jsonResponse(['error' => 'Endpoint not found'], 404);
}
