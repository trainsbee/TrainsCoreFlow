<?php
namespace Core;

class Router {
    private $routes = [];
    private $controllers = [];

    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }

    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute($method, $path, $handler) {
        $this->routes[] = compact('method', 'path', 'handler');
    }

    /**
     * Registrar una instancia de controlador
     */
    public function registerController($name, $controllerInstance) {
        $this->controllers[$name] = $controllerInstance;
    }

    /**
     * Obtener una instancia de controlador registrada
     */
    public function getController($name) {
        return $this->controllers[$name] ?? null;
    }

    public function dispatch($requestUri, $requestMethod) {
        foreach ($this->routes as $route) {
            $pattern = "@^" . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_-]+)', $route['path']) . "$@D";

            if ($route['method'] === $requestMethod && preg_match($pattern, $requestUri, $matches)) {
                $handler = $route['handler'];

                if (is_array($handler)) {
                    $controllerName = $handler[0];
                    $method = $handler[1];
                    
                    // Obtener la instancia del controlador ya inyectada
                    $controller = $this->getController($controllerName);
                    
                    if (!$controller) {
                        throw new \Exception("Controller '$controllerName' not registered");
                    }
                    
                    $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                    return call_user_func_array([$controller, $method], $params);
                }
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }
}
