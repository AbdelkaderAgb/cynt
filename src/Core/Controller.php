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
}
