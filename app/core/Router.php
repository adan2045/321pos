<?php
namespace app\core;

class Router {
    protected $routes = [];

    public function __construct() {
        $this->loadRoutes();
    }

    private function loadRoutes() {
    // Rutas existentes
    $this->add('', 'site', 'index');
    $this->add('cajero', 'cajero', 'index');
    $this->add('cajero/mesas', 'cajero', 'mesas');

    // NUEVAS RUTAS: CRUD Mesas
    $this->add('mesa/listado', 'mesa', 'listado');
    $this->add('mesa/formulario', 'mesa', 'formulario');
    $this->add('mesa/guardar', 'mesa', 'guardar');
    $this->add('mesa/modificar', 'mesa', 'modificar');
    $this->add('mesa/actualizar', 'mesa', 'actualizar');
    $this->add('mesa/eliminar', 'mesa', 'eliminar');

     // NUEVAS RUTAS: fichadas
    $this->add('fichada/registrar', 'fichada', 'registrar');


    // NUEVAS RUTAS: CRUD Usuarios
    $this->add('usuario/formulario', 'usuario', 'formulario');
    $this->add('usuario/guardar', 'usuario', 'guardar');
    $this->add('usuario/listado', 'usuario', 'listado'); 
    $this->add('usuario/modificar', 'usuario', 'modificar');
    $this->add('usuario/eliminar', 'usuario', 'eliminar');
    $this->add('usuario/actualizar', 'usuario', 'actualizar');
}


    public function add($route, $controller, $action) {
        $this->routes[$route] = [
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function dispatch() {
        $url = $_GET['url'] ?? '';

        if (array_key_exists($url, $this->routes)) {
            $controller = $this->routes[$url]['controller'];
            $action = $this->routes[$url]['action'];

            $controllerClass = "app\\controllers\\".ucfirst($controller)."Controller";

            if (class_exists($controllerClass)) {
                $controllerInstance = new $controllerClass();

                if (method_exists($controllerInstance, $action.'Action')) {
                    call_user_func([$controllerInstance, $action.'Action']);
                } else {
                    $this->showError(404, "Método no encontrado");
                }
            } else {
                $this->showError(404, "Controlador no encontrado");
            }
        } else {
            $this->showError(404, "Ruta no definida");
        }
    }

    private function showError($code, $message) {
        http_response_code($code);
        echo "<h1>Error $code</h1><p>$message</p>";
        exit;
    }
}
