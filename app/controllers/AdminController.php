<?php
namespace app\controllers;

use \Controller;
use \Response;
use \DataBase;
use app\controllers\SesionController;

class AdminController extends Controller{
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
			"head" => $head,
			"nav" => $nav,
			"footer" => $footer,
		]);
	}
	public function actionGestion()
	{	
		static::path();
		$footer = SiteController::footer();
		$head = SiteController::head();
		$nav = SiteController::nav();
		$path = static::path();
		Response::render($this->viewDir(__NAMESPACE__), "gestion", [
			"title" => $this->title . "gestion de locales",
			'ruta'=>self::$ruta,
			"head" => $head,
			"nav" => $nav,
			"footer" => $footer,
		]);
	}
}