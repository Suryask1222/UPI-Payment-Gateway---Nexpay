<?php

class Router {
    private $routes = [];

    
    public function add($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => '/' . ltrim($path, '/'),
            'controller' => $controller,
            'action' => $action
        ];
    }

    
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        
        $path = $_GET['route'] ?? $_SERVER['PATH_INFO'] ?? '/';
        $path = '/' . ltrim(explode('?', $path)[0], '/');

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $path) {
                $controllerClass = $route['controller'];
                $actionMethod = $route['action'];
                
                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    if (method_exists($controller, $actionMethod)) {
                        $controller->$actionMethod();
                        return;
                    }
                }
            }
        }

        
        header("HTTP/1.0 404 Not Found");
        echo "<h1>404 Not Found</h1><p>The requested route [{$path}] could not be resolved.</p>";
        exit;
    }
}
