<?php
namespace App\Controllers;

use App\Core\Controller;

class ErrorController extends Controller
{
    public function action404(string $message = ''): void
    {
        http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Error 404</title>
        </head>
        <body>
            <h1>Error 404 - Página no encontrada</h1>
            <p>{$message}</p>
        </body>
        </html>";
    }
}
