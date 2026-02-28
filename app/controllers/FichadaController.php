<?php
namespace app\controllers;

use app\models\FichadaModel;

class FichadaController extends \Controller
{
    public function __construct() {}

    /**
     * POST /fichada/registrar
     * Recibe JSON: { numero_empleado: "007", tipo: "entrada" | "salida" }
     * Devuelve JSON con leyenda completa de tiempo trabajado.
     */
    public function actionRegistrar(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();

        $negocioId = $_SESSION['negocio_id'] ?? 1;
        $usuarioId = $_SESSION['user_id']    ?? null;

        $data           = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $numeroEmpleado = trim($data['numero_empleado'] ?? '');
        $tipo           = trim($data['tipo'] ?? '');

        if ($numeroEmpleado === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Número de empleado requerido']);
            return;
        }
        if (!in_array($tipo, ['entrada', 'salida'])) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Tipo inválido. Debe ser "entrada" o "salida"']);
            return;
        }

        $model    = new FichadaModel();
        $empleado = $model->buscarPorNumero($numeroEmpleado, $negocioId);

        if (!$empleado) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Número de empleado no encontrado']);
            return;
        }

        $entradaAbierta = $model->buscarEntradaAbierta($empleado['id']);

        if ($tipo === 'entrada' && $entradaAbierta) {
            echo json_encode([
                'ok'    => false,
                'error' => 'Ya tenés una entrada registrada hoy. ¿Querías registrar tu salida?'
            ]);
            return;
        }
        if ($tipo === 'salida' && !$entradaAbierta) {
            echo json_encode([
                'ok'    => false,
                'error' => 'No hay una entrada registrada hoy para cerrar.'
            ]);
            return;
        }

        if ($tipo === 'entrada') {
            $resultado = $model->registrarEntrada($empleado['id'], $negocioId, $usuarioId);
            $leyenda   = "Ingreso: " . $resultado['hora'];
            if (!empty($resultado['es_tardanza']) && $resultado['tardanza_min'] > 0) {
                $leyenda .= " ⚠️ Tardanza: " . $resultado['tardanza_min'] . " min";
            }
        } else {
            $resultado = $model->registrarSalida($empleado['id'], $negocioId, $entradaAbierta, $usuarioId);
            $leyenda   = "Ingreso: " . $resultado['hora_entrada']
                       . " | Salida: " . $resultado['hora_salida']
                       . " | Tiempo trabajado: " . $resultado['trabajado_texto'];
        }

        echo json_encode([
            'ok'      => true,
            'leyenda' => $leyenda,
            'empleado' => [
                'nombre' => trim($empleado['nombre'] . ' ' . ($empleado['apellido'] ?? '')),
                'cargo'  => $empleado['cargo'] ?? '',
                'numero' => $numeroEmpleado,
            ],
            'fichada' => $resultado,
        ]);
    }
}
