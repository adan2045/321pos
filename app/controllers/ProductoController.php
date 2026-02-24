<?php
namespace app\controllers;

use \Controller;
use \Response;
use \App;
use app\models\ProductoModel;

class ProductoController extends Controller
{
    public function actionListado()
    {
        static::path();
        $head = \app\controllers\SiteController::head();
        $nav = \app\controllers\SiteController::nav();
        $footer = \app\controllers\SiteController::footer();

        $model = new ProductoModel();
        $productos = $model->obtenerTodos();

        Response::render($this->viewDir(__NAMESPACE__), 'listado', [
            'title' => 'Listado de Productos',
            'head' => $head,
            'ruta' => App::baseUrl(),
            'nav' => $nav,
            'footer' => $footer,
            'productos' => $productos
        ]);
    }

    public function actionFormulario()
    {
        static::path();
        SesionController::redirigirSiNoAutenticado();
        $head = \app\controllers\SiteController::head();
        $nav = \app\controllers\SiteController::nav();
        $footer = \app\controllers\SiteController::footer();

        Response::render($this->viewDir(__NAMESPACE__), 'formulario', [
            'title' => 'Formulario Producto',
            'head' => $head,
            'ruta' => App::baseUrl(),
            'nav' => $nav,
            'footer' => $footer
        ]);
    }

    public function actionToggleEstado()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'] ?? null;
        $activo = $_POST['activo'] ?? null;

        if ($id !== null && $activo !== null) {
            $productoModel = new ProductoModel();
            $productoModel->actualizarEstado($id, $activo);
            echo "ok";
        } else {
            http_response_code(400);
            echo "Datos incompletos";
        }
    }
}

    public function actionGuardar()
    {
        static::path();
        SesionController::redirigirSiNoAutenticado();
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $nombre = $_POST["nombre"] ?? '';
            $descripcion = $_POST["descripcion"] ?? '';
            $precio = $_POST["precio"] ?? 0;
            $categoria = $_POST["categoria"] ?? '';

            if ($nombre && $descripcion && $precio && $categoria) {
                $model = new ProductoModel();
                $model->guardar($nombre, $descripcion, $precio, $categoria);

                echo "<p style='color: green; font-weight: bold;'>✅ Producto guardado correctamente.</p>";
                echo "<script>setTimeout(() => window.location.href = '" . App::baseUrl() . "/producto/listado', 1500);</script>";
                exit;
            } else {
                echo "<p style='color: red;'>Faltan campos obligatorios.</p>";
            }
        }
    }

    public function actionModificar()
    {
        static::path();
        SesionController::redirigirSiNoAutenticado();
        $id = $_GET['id'] ?? null;
        if (!$id || !is_numeric($id)) {
            echo "<p style='color:red;'>ID inválido.</p>";
            return;
        }

        $model = new ProductoModel();
        $producto = $model->obtenerPorId($id);

        if (!$producto) {
            echo "<p style='color:red;'>Producto no encontrado.</p>";
            return;
        }

        $producto = (array) $producto;

        $head = \app\controllers\SiteController::head();
        $nav = \app\controllers\SiteController::nav();
        $footer = \app\controllers\SiteController::footer();

        Response::render($this->viewDir(__NAMESPACE__), 'modificar', [
            'title' => 'Editar Producto',
            'head' => $head,
            'ruta' => App::baseUrl(),
            'nav' => $nav,
            'footer' => $footer,
            'producto' => $producto
        ]);
    }

    public function actionActualizar()
    {
        static::path();
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $id = $_POST["id"] ?? null;
            $nombre = $_POST["nombre"] ?? '';
            $descripcion = $_POST["descripcion"] ?? '';
            $precio = $_POST["precio"] ?? 0;
            $categoria = $_POST["categoria"] ?? '';

            if ($id && $nombre && $descripcion && $precio && $categoria) {
                $model = new ProductoModel();
                $model->actualizar($id, $nombre, $descripcion, $precio, $categoria);

                echo "<p style='color: green; font-weight: bold;'>✅ Producto actualizado correctamente.</p>";
                echo "<script>setTimeout(() => window.location.href = '" . App::baseUrl() . "/producto/listado', 1500);</script>";
                exit;
            } else {
                echo "<p style='color: red;'>Faltan campos obligatorios.</p>";
            }
        }
    }

    public function actionEliminar()
    {
        static::path();
        $id = $_GET['id'] ?? null;

        if ($id) {
            $model = new ProductoModel();
            $model->eliminar($id);
        }

        header("Location: " . App::baseUrl() . "/producto/listado");
        exit;
    }
}