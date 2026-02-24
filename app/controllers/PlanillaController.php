<?php
namespace app\controllers;

use \Controller;
use \App;
use \Response;
use app\models\CajaModel;

class PlanillaController extends Controller
{
    public function actionIndex($var = null)
    {
        $fechaHoy = date('Y-m-d');
        $cajaModel = new CajaModel();

        $datos = $cajaModel->obtenerTotalesDelDia($fechaHoy);
        $productos = $cajaModel->resumenPorProducto($fechaHoy);

        $head = \app\controllers\SiteController::head();
        $nav = \app\controllers\SiteController::nav();
        $footer = \app\controllers\SiteController::footer();

        Response::render('planilla', 'index', [
            'title' => 'Planilla de Caja',
            'head' => $head,
            'nav' => $nav,
            'footer' => $footer,
            'ruta' => App::baseUrl(),
            'fecha' => $fechaHoy,
            'datos' => $datos,
            'productos' => $productos
        ]);
    }
    public function actionPos($var = null)
{
    $fechaHoy = date('Y-m-d');
    $cajaModel = new \app\models\CajaModel();

    $datos = $cajaModel->obtenerTotalesDelDia($fechaHoy);
    $productos = $cajaModel->resumenPorProducto($fechaHoy);

    \Response::render('planilla', 'pos', [
        'title' => 'Planilla POS',
        'fecha' => $fechaHoy,
        'datos' => $datos,
        'productos' => $productos
    ]);
}

    public function actionEmbed($var = null)
    {
        $fechaHoy = date('Y-m-d');
        $cajaModel = new CajaModel();

        // 👇 pasar las mismas variables que la vista planillaCaja.php espera
        $datos = $cajaModel->obtenerTotalesDelDia($fechaHoy);
        $productos = $cajaModel->resumenPorProducto($fechaHoy);

        // Si la vista cajero/planillaCaja.php usa $ruta también:
        $ruta = App::baseUrl();

        // Render de embed (sin head/nav/footer)
        Response::render('planilla', 'embed', [
    'title' => 'Planilla (Embed)',
    'ruta' => $ruta,
    'fecha' => $fechaHoy,
    'datos' => $datos,
    'productos' => $productos,
    'head' => '',     // ✅ evita warning
    'nav' => '',      // opcional
    'footer' => ''    // opcional

        ]);
    }
}