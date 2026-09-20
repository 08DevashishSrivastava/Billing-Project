<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private string $basePath;

    public function __construct(string $basePath = '')
    {
        $this->basePath = $basePath;
    }

    public function get(string $path, array $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, array $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, array $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, array $handler): self
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $this->basePath . $path,
            'handler' => $handler,
        ];
        return $this;
    }

    public function resolve(): mixed
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        error_log("ROUTE HIT: " . $method . " " . $uri);
        
        // Handle PUT/DELETE via POST with _method field
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            $pattern = $this->convertToRegex($route['path']);
            
            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                [$controllerClass, $action] = $route['handler'];
                
                if (!class_exists($controllerClass)) {
                    // Log error if needed, for now just 404
                    break; 
                }
                
                $controller = new $controllerClass();
                
                if (!method_exists($controller, $action)) {
                    // Route exists but method missing -> 404 with specific message
                    http_response_code(404);
                    echo "<h1>404 - Route exists but controller method missing: {$action}</h1>";
                    return null;
                }
                
                return $controller->$action(...array_values($params));
            }
        }

        http_response_code(404);
        return $this->render404();
    }

    private function convertToRegex(string $path): string
    {
        // Strict numeric ID enforcement: {id} -> ([0-9]+)
        // Other params can remain generic [^/]+
        $pattern = preg_replace('/\{id\}/', '(?P<id>[0-9]+)', $path);
        // If there are other named params (not expected but good for future), fallback to generic
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $pattern = str_replace('{param}', '(?P<param>[^/]+)', $pattern);
        
        return '#^' . $pattern . '/?$#';
    }

    private function render404(): void
    {
        echo '<h1>404 - Page Not Found</h1>';
    }

    public static function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
}
