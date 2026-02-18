<?php
/**
 * CYN Tourism - Base Controller
 * 
 * All controllers extend this class to gain view rendering,
 * redirect helpers, and JSON response capabilities.
 * 
 * @package CYN_Tourism
 * @version 3.0.0
 */

class Controller
{
    /**
     * Render a view inside the layout
     * 
     * @param string $view    View path relative to views/ (e.g. 'dashboard/index')
     * @param array  $data    Variables to extract into the view scope
     * @param string $layout  Layout file to wrap the view (null = no layout)
     */
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/app'): void
    {
        // Send security headers on every page render
        $this->sendSecurityHeaders();

        $basePath = App::getBasePath();
        $viewFile = $basePath . '/views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$viewFile}");
        }

        // Extract data variables into scope
        extract($data);

        // Capture the view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // If layout specified, wrap content inside it
        if ($layout) {
            $layoutFile = $basePath . '/views/' . $layout . '.php';
            if (!file_exists($layoutFile)) {
                throw new \RuntimeException("Layout not found: {$layoutFile}");
            }
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Send security-related HTTP headers
     * Called automatically on every view render
     */
    protected function sendSecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        // Prevent MIME-type sniffing
        header('X-Content-Type-Options: nosniff');

        // Prevent clickjacking — allow same-origin framing only
        header('X-Frame-Options: SAMEORIGIN');

        // Legacy XSS filter (still useful for older browsers)
        header('X-XSS-Protection: 1; mode=block');

        // Control referrer leakage
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Restrict browser features the app doesn't need
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

        // Content Security Policy — allow CDN resources (Tailwind, Alpine, FA, Fonts, Chart.js)
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.tailwindcss.com",
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com data:",
            "img-src 'self' data: blob:",
            "connect-src 'self'",
            "frame-ancestors 'self'",
        ]);
        header('Content-Security-Policy: ' . $csp);
    }

    /**
     * Render a view without a layout (standalone page like login)
     */
    protected function viewStandalone(string $view, array $data = []): void
    {
        $this->view($view, $data, null);
    }

    /**
     * Redirect to a route path
     */
    protected function redirect(string $path): void
    {
        App::redirect($path);
    }

    /**
     * Return a JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Alias for json() — used by InvoiceController, ReceiptController, ExportController
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        $this->json($data, $statusCode);
    }

    /**
     * Get the current authenticated user or null
     */
    protected function user(): ?array
    {
        return Auth::user();
    }

    /**
     * Require the user to be authenticated
     */
    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
        }
    }

    /**
     * Require the user to be an admin
     */
    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo '<h1>403 — Forbidden</h1>';
            exit;
        }
    }

    /**
     * Validate CSRF token on POST requests
     * Call at the start of any store/update/delete action
     */
    protected function requireCsrf(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST[CSRF_TOKEN_NAME] ?? '';
            if (!validate_csrf_token($token)) {
                http_response_code(403);
                echo '<h1>403 — Invalid CSRF Token</h1>';
                exit;
            }
        }
    }

    /**
     * Sanitize and trim a POST string value
     */
    protected function postString(string $key, string $default = ''): string
    {
        return trim(htmlspecialchars($_POST[$key] ?? $default, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Get a POST integer value (nullable)
     */
    protected function postInt(string $key, ?int $default = null): ?int
    {
        $val = $_POST[$key] ?? '';
        if ($val === '' || $val === null) {
            return $default;
        }
        return (int)$val;
    }

    /**
     * Get a POST float value
     */
    protected function postFloat(string $key, float $default = 0.0): float
    {
        return (float)($_POST[$key] ?? $default);
    }

    /**
     * Get a nullable foreign key from POST (driver_id, guide_id, etc.)
     * Returns null if empty, int if set — enforces RULE 3
     */
    protected function postNullableId(string $key): ?int
    {
        $val = $_POST[$key] ?? '';
        return !empty($val) ? (int)$val : null;
    }
}
