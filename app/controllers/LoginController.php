<?php
namespace app\controllers;

use \Controller;
use \Response;
use \app\models\UsuarioModel;

class LoginController extends Controller 
{
    public function __construct() {}

    public function actionLogin()
    {
        $path = static::path();
        $mail = '';
        $password = '';
        $error_mail = '';
        $error_pass = '';
        $general_error = '';

        if (isset($_POST['ingreso'])) {
            $errorFlag = false;

            // --- VALIDACIONES MAIL ---
            if (!isset($_POST['mail'])) {
                $error_mail = "No existe mail";
                $errorFlag = true;
            } else {
                $mail = trim($_POST['mail']);
            }

            if (empty($error_mail)) {
                if (empty($mail)) {
                    $error_mail = 'No puede estar vacío';
                    $errorFlag = true;
                } elseif (strlen($mail) < 5 || strlen($mail) > 120) {
                    $error_mail = 'Debe tener entre 5 y 120 caracteres';
                    $errorFlag = true;
                } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                    $error_mail = 'Formato de correo no válido';
                    $errorFlag = true;
                }
            }

            // --- VALIDACIONES PASSWORD ---
            if (!isset($_POST['password'])) {
                $error_pass = 'No existe contraseña';
                $errorFlag = true;
            } else {
                $password = trim($_POST['password']);
            }

            if (empty($error_pass)) {
                if (empty($password)) {
                    $error_pass = 'No puede estar vacío';
                    $errorFlag = true;
                } elseif (strlen($password) < 3 || strlen($password) > 10) {
                    $error_pass = 'Debe tener entre 3 y 10 caracteres';
                    $errorFlag = true;
                }
            }

            // --- VERIFICACIÓN CON BD ---
            if (!$errorFlag) {
                try {
                    $usuarioModel = new UsuarioModel();
                    $usuario = $usuarioModel->buscarPorEmail($mail);

                    if ($usuario) {
                        if (
                            password_verify($password, $usuario['password']) || 
                            $password === $usuario['password']
                        ) {
                            session_start();
                            $_SESSION['user_id'] = $usuario['id'];
                            $_SESSION['user_email'] = $usuario['email'];
                            $_SESSION['user_rol'] = $usuario['rol'];

                            // Redirigir según rol
                            $rol = trim(strtolower($usuario['rol']));

                            switch ($rol) {
                                case 'mozo':
                                    header("Location: " . self::$ruta . "/menu/mozo");
                                    break;
                                case 'admin':
                                case 'superadmin':
                                    header("Location: " . self::$ruta . "/admin/gestion");
                                    break;
                                case 'cajero':
                                    header("Location: " . self::$ruta . "/cajero/vistaCajero");
                                    break;
                                case 'cliente':
                                    header("Location: " . self::$ruta . "/menu");
                                    break;
                                default:
                                    header("Location: " . self::$ruta . "/home/index");
                            }
                            exit;
                        } else {
                            $error_pass = 'Contraseña incorrecta';
                        }
                    } else {
                        $error_mail = 'Usuario no encontrado';
                    }
                } catch (\PDOException $e) {
                    $general_error = 'Error de conexión: ' . $e->getMessage();
                }
            }
        }

        // Renderizar vista
        $footer = SiteController::footer();
        $head = SiteController::head();
        $nav = SiteController::nav();

        Response::render($this->viewDir(__NAMESPACE__), "login", [
            "title" => $this->title . "Login",
            "head" => $head,
            'ruta' => self::$ruta,
            "nav" => $nav,
            "footer" => $footer,
            "mail" => $mail,
            "password" => $password,
            "error_mail" => $error_mail,
            "error_pass" => $error_pass,
            "general_error" => $general_error,
        ]);
    }

    public function actionLogout()
    {
        session_start();
        session_unset();
        session_destroy();

        header("Location: " . self::$ruta . "/");
        exit();
    }
}