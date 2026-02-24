<?php
namespace app\controllers;

use \Controller;
use \Response;
use app\models\CajaModel;

class PosController extends Controller
{
    public function __construct() {}

    public function actionIndex($var = null)
    {
        // Base path del proyecto (ej: /321POS/public/)
        $ruta = static::path();

        // Si querés chequear sesión acá, lo enchufás:
        // SesionController::verificarSesion();

        // Datos para los modales de gestión (monto sugerido = último cierre)
        $cajaModel = new CajaModel();
        $ultimoCierre = $cajaModel->obtenerUltimoCierre();

        // Layout
        $footer = SiteController::footer();
        $head   = SiteController::head();
        $nav    = SiteController::nav();

        Response::render($this->viewDir(__NAMESPACE__), "index", [
            "title"        => "321POS - Mostrador",
            "ruta"         => $ruta,
            "ultimoCierre" => $ultimoCierre,
            "head"         => $head,
            "nav"          => $nav,
            "footer"       => $footer,
        ]);
    }
}