<?php
namespace app\controllers;
use \Controller;
use \Response;
use \DataBase;

class HomeController extends Controller
{

	// Constructor
	public function __construct()
	{

	}
	public function actionIndex($var=null)
	{	
		$footer = SiteController::footer();
		$head = SiteController::head();
		$nav = SiteController::nav();
		$path = static::path();
		Response::render($this->viewDir(__NAMESPACE__), "inicio", [
			"title" => $this->title . "Inicio",
			'ruta'=>self::$ruta,
			"head" => $head,
			"nav" => $nav,
			"footer" => $footer,
		]);
	}


	public function actionCrear()
	{
		$head = SiteController::head();
		$nav = SiteController::nav();
		$path = static::path();

		Response::render($this->viewDir(__NAMESPACE__), "crear", [
			"title" => $this->title . "Crear usuario",
			"head" => $head,
			"nav" => $nav,
			'ruta'=>self::$ruta,
		]);
	}


	public function action404()
	{
		$head = SiteController::head();
		Response::render($this->viewDir(__NAMESPACE__), "404", [
			"title" => $this->title . ' 404 se rompio todo!s',
			"head" => $head,
		]);
	}

	private function actionHola()
	{
		echo 'hola';
	}
}