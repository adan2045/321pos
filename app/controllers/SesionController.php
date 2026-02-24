<?php
namespace app\controllers;

class SesionController
{
    public static function iniciar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function estaAutenticado()
    {
        self::iniciar();
        return isset($_SESSION['user_id']);
    }

    public static function redirigirSiNoAutenticado()
    {
        if (!self::estaAutenticado()) {
            header("Location: /login/login");
            exit();
        }
    }

    public static function obtenerRol()
    {
        return $_SESSION['user_rol'] ?? null;
    }

    public static function obtenerEmail()
    {
        return $_SESSION['user_email'] ?? null;
    }

    public static function cerrarSesion()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    session_unset();  // limpia las variables de sesión
    session_destroy(); // destruye la sesión

    header("Location: " . self::$ruta . "/admin/gestion");
    exit;
}
}
