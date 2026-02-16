<?php
/**
 * CYN Tourism - Application Router & Dispatcher
 * 
 * Minimal MVC router for shared hosting (no Composer required).
 * All requests flow through public/index.php → App::run()
 * 
 * @package CYN_Tourism
 * @version 3.0.0
 */

class App
{
    private static array $routes = [];
    private static string $basePath = '';

    /**
     * Register the application base path
     */
    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim($path, '/');
    }

    /**
     * Get the application base path
     */
    public static function getBasePath(): string
    {
        return self::$basePath;
    }

    /**
     * Register a GET route
     */
    public static function get(string $uri, array $handler): void
    {
        self::addRoute('GET', $uri, $handler);
    }

    /**
     * Register a POST route
     */
    public static function post(string $uri, array $handler): void
    {
        self::addRoute('POST', $uri, $handler);
    }

    /**
     * Register a route for any HTTP method
     */
    public static function any(string $uri, array $handler): void
    {
        self::addRoute('ANY', $uri, $handler);
    }

    /**
     * Add a route to the routing table
     */
    private static function addRoute(string $method, string $uri, array $handler): void
    {
        self::$routes[] = [
            'method'     => strtoupper($method),
            'uri'        => '/' . trim($uri, '/'),
            'controller' => $handler[0],
            'action'     => $handler[1] ?? 'index',
        ];
    }

    /**
     * Run the application — match the current request to a route and dispatch
     */
    public static function run(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = self::parseUri();

        foreach (self::$routes as $route) {
            if ($route['uri'] !== $requestUri) {
                continue;
            }

            if ($route['method'] !== 'ANY' && $route['method'] !== $requestMethod) {
                continue;
            }

            self::dispatch($route['controller'], $route['action']);
            return;
        }

        // No route matched — 404
        http_response_code(404);
        if (file_exists(self::$basePath . '/views/errors/404.php')) {
            include self::$basePath . '/views/errors/404.php';
        } else {
            echo '<h1>404 — Page Not Found</h1>';
        }
    }

    /**
     * Parse the request URI into a clean route path
     */
    private static function parseUri(): string
    {
        // Support ?url= rewriting (Apache) or PATH_INFO
        if (isset($_GET['url'])) {
            $uri = $_GET['url'];
        } else {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            
            // Remove script directory from URI for subdirectory installations
            $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
            if ($scriptDir !== '/' && $scriptDir !== '\\') {
                $uri = substr($uri, strlen($scriptDir));
            }
        }

        $uri = '/' . trim($uri ?? '', '/');
        return $uri === '' ? '/' : $uri;
    }

    /**
     * Instantiate a controller and call its action method
     */
    private static function dispatch(string $controllerName, string $action): void
    {
        $controllerFile = self::$basePath . '/src/Controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            throw new \RuntimeException("Controller file not found: {$controllerFile}");
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            throw new \RuntimeException("Controller class not found: {$controllerName}");
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $action)) {
            throw new \RuntimeException("Action '{$action}' not found in {$controllerName}");
        }

        $controller->$action();
    }

    /**
     * Generate a URL for a given path
     */
    public static function url(string $path = '/'): string
    {
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        $base = ($scriptDir !== '/' && $scriptDir !== '\\') ? $scriptDir : '';
        return $base . '/' . ltrim($path, '/');
    }

    /**
     * Redirect to a given URL path
     */
    public static function redirect(string $path): void
    {
        header('Location: ' . self::url($path));
        exit;
    }
}
