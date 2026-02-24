<?php
namespace app\controllers;
use \Controller;
use app\models\CategoriaModel;

class SiteController extends Controller
{
	// Constructor
	public function __construct()
	{
		// self::$sessionStatus = SessionController::sessionVerificacion();
	}

	public static function head()
	{
		static::path();
		$head = file_get_contents(APP_PATH . '/views/inc/head.php');
		$head = str_replace('#PATH#', self::$ruta, $head);
		return $head;
	}

	public static function nav()
{
    $ruta = \App::$ruta;
    ob_start();
    include APP_PATH . '/views/inc/nav.php';
    return ob_get_clean();
}
	public static function footer()
	{
		static::path();
		$footer = file_get_contents(APP_PATH . '/views/inc/footer.php');
		$footer = str_replace('#PATH#', self::$ruta, $footer);
		return $footer;
	}

	public static function GenerarQR($url, $nombreQr, $directorio){
    	require_once(APP_PATH.'librerias/php/phpqrcode/qrlib.php');

		$ruta_archivoNombre = $directorio.'/'.$nombreQr.'.png';
		// echo $ruta_archivoNombre."<hr>";

		$contenido = $url;
		$respuesta = \QRcode::png($contenido, $ruta_archivoNombre, QR_ECLEVEL_L, 10);
		// var_dump($respuesta);
	}
	
	public function actionTestQr(){
		static::path();

		$directorio=ROOT_PATH.'qr/';
		$nombreQr='QR01';
		$url='www.google.com';
		$resultado = self::GenerarQR($url,$nombreQr,$directorio);

		echo "<img src='".self::$ruta."".$directorio."$nombreQr.png'>";
	}
}
