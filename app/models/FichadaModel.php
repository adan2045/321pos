<?php
namespace app\models;

use \DataBase;

class FichadaModel
{
    private $db;

    public function __construct()
    {
        $this->db = DataBase::getInstance()->getConnection();
    }

    /**
     * Busca un empleado por su número en el negocio actual.
     */
    public function buscarPorNumero(string $numero, int $negocioId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, nombre, apellido, cargo, horario_entrada, tolerancia_min
             FROM empleados
             WHERE numero_empleado = :numero
               AND negocio_id = :negocio
               AND activo = 1
             LIMIT 1"
        );
        $stmt->execute(['numero' => $numero, 'negocio' => $negocioId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Busca la última entrada del día sin salida registrada.
     * Si existe → el empleado está "dentro" → hay que registrar salida.
     * Si no existe → hay que registrar entrada.
     */
    public function buscarEntradaAbierta(int $empleadoId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT f.id, f.fecha_hora, f.turno
             FROM fichadas f
             WHERE f.empleado_id = :empleado
               AND f.tipo  = 'entrada'
               AND f.fecha = CURDATE()
               AND NOT EXISTS (
                   SELECT 1 FROM fichadas f2
                   WHERE f2.empleado_id = f.empleado_id
                     AND f2.tipo  = 'salida'
                     AND f2.turno = f.turno
                     AND f2.fecha = f.fecha
               )
             ORDER BY f.fecha_hora DESC
             LIMIT 1"
        );
        $stmt->execute(['empleado' => $empleadoId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Registra una ENTRADA.
     * Calcula automáticamente tardanza comparando con horario_entrada del empleado.
     * Retorna el id insertado y los datos calculados.
     */
    public function registrarEntrada(int $empleadoId, int $negocioId, ?int $registradoPor = null): array
    {
        // Obtener horario esperado y tolerancia del empleado
        $stmt = $this->db->prepare(
            "SELECT horario_entrada, tolerancia_min FROM empleados WHERE id = :id"
        );
        $stmt->execute(['id' => $empleadoId]);
        $empleado = $stmt->fetch(\PDO::FETCH_ASSOC);

        $ahora        = new \DateTime();
        $tardanzaMin  = null;
        $esTardanza   = 0;

        // Calcular tardanza si tiene horario configurado
        if (!empty($empleado['horario_entrada'])) {
            $esperada   = new \DateTime(date('Y-m-d') . ' ' . $empleado['horario_entrada']);
            $tolerancia = (int)($empleado['tolerancia_min'] ?? 10);
            $esperadaConTolerancia = clone $esperada;
            $esperadaConTolerancia->modify("+{$tolerancia} minutes");

            if ($ahora > $esperadaConTolerancia) {
                $diff        = $ahora->getTimestamp() - $esperada->getTimestamp();
                $tardanzaMin = (int)round($diff / 60);
                $esTardanza  = 1;
            }
        }

        // Determinar número de turno (cuántas entradas tiene hoy + 1)
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM fichadas
             WHERE empleado_id = :id AND tipo = 'entrada' AND fecha = CURDATE()"
        );
        $stmt->execute(['id' => $empleadoId]);
        $turno = (int)$stmt->fetchColumn() + 1;

        $stmt = $this->db->prepare(
            "INSERT INTO fichadas
               (negocio_id, empleado_id, tipo, fecha_hora, fecha, tardanza_min, es_tardanza, turno, registrado_por)
             VALUES
               (:negocio, :empleado, 'entrada', NOW(), CURDATE(), :tardanza, :esTardanza, :turno, :registrado)"
        );
        $stmt->execute([
            'negocio'     => $negocioId,
            'empleado'    => $empleadoId,
            'tardanza'    => $tardanzaMin,
            'esTardanza'  => $esTardanza,
            'turno'       => $turno,
            'registrado'  => $registradoPor,
        ]);

        return [
            'tipo'        => 'entrada',
            'turno'       => $turno,
            'es_tardanza' => (bool)$esTardanza,
            'tardanza_min'=> $tardanzaMin,
            'hora'        => $ahora->format('H:i'),
        ];
    }

    /**
     * Registra una SALIDA.
     * Busca la entrada abierta del mismo turno y calcula tiempo_trabajado_min.
     * Retorna los datos calculados para mostrar en pantalla.
     */
    public function registrarSalida(int $empleadoId, int $negocioId, array $entradaAbierta, ?int $registradoPor = null): array
    {
        $ahora        = new \DateTime();
        $entrada      = new \DateTime($entradaAbierta['fecha_hora']);
        $trabajadoMin = (int)round(($ahora->getTimestamp() - $entrada->getTimestamp()) / 60);

        $stmt = $this->db->prepare(
            "INSERT INTO fichadas
               (negocio_id, empleado_id, tipo, fecha_hora, fecha, tiempo_trabajado_min, turno, registrado_por)
             VALUES
               (:negocio, :empleado, 'salida', NOW(), CURDATE(), :trabajado, :turno, :registrado)"
        );
        $stmt->execute([
            'negocio'    => $negocioId,
            'empleado'   => $empleadoId,
            'trabajado'  => $trabajadoMin,
            'turno'      => $entradaAbierta['turno'],
            'registrado' => $registradoPor,
        ]);

        // Formatear para mostrar en la UI
        $horas   = intdiv($trabajadoMin, 60);
        $minutos = $trabajadoMin % 60;

        return [
            'tipo'             => 'salida',
            'turno'            => $entradaAbierta['turno'],
            'trabajado_min'    => $trabajadoMin,
            'trabajado_texto'  => "{$horas}h {$minutos}m",
            'hora_entrada'     => $entrada->format('H:i'),
            'hora_salida'      => $ahora->format('H:i'),
        ];
    }
}