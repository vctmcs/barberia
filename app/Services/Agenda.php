<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Cita;
use App\Models\Empleado;

/**
 * Reglas de la agenda:
 *  - la cita dura lo que dura el servicio,
 *  - debe caer completa dentro del horario del estilista ese dia,
 *  - no puede empalmarse con otra cita del estilista ni del cliente,
 *  - una cita nueva no puede quedar en el pasado.
 */
final class Agenda
{
    public const INTERVALO_MIN = 15;

    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Valida una cita y calcula su fin.
     *
     * @param array{cliente_id:int, empleado_id:int, servicio_id:int, inicio:string} $d
     *        inicio en formato 'Y-m-d H:i'
     * @return array{errores: array<string,string>, fecha_hora?: string, fecha_fin?: string, servicio?: array}
     */
    public function validar(array $d, ?int $citaId = null, bool $permitirPasado = false): array
    {
        $errores = [];

        $cliente  = $this->fila('SELECT id, nombre, activo FROM clientes WHERE id = ?', $d['cliente_id']);
        $empleado = $this->fila('SELECT id, nombre, activo FROM empleados WHERE id = ?', $d['empleado_id']);
        $servicio = $this->fila('SELECT id, nombre, precio, duracion_min, activo FROM servicios WHERE id = ?', $d['servicio_id']);

        if (!$cliente || !$cliente['activo'])   { $errores['cliente_id']  = 'Elige un cliente activo.'; }
        if (!$empleado || !$empleado['activo']) { $errores['empleado_id'] = 'Elige un estilista activo.'; }
        if (!$servicio || !$servicio['activo']) { $errores['servicio_id'] = 'Elige un servicio disponible.'; }

        $inicio = \DateTime::createFromFormat('Y-m-d H:i', $d['inicio']);
        if (!$inicio || $inicio->format('Y-m-d H:i') !== $d['inicio']) {
            $errores['fecha'] = 'Indica la fecha y la hora de la cita.';
        }

        if ($errores) {
            return ['errores' => $errores];
        }

        $fin = (clone $inicio)->modify('+' . (int) $servicio['duracion_min'] . ' minutes');
        $fechaHora = $inicio->format('Y-m-d H:i:s');
        $fechaFin  = $fin->format('Y-m-d H:i:s');

        if (!$permitirPasado && $inicio < new \DateTime()) {
            $errores['fecha'] = 'La cita no puede quedar en el pasado.';
        }

        /* Dentro del horario del estilista */
        if (!$errores) {
            $dia = (int) $inicio->format('N');
            $horario = (new Empleado())->horario((int) $empleado['id'])[$dia] ?? null;

            if (!$horario) {
                $errores['fecha'] = "{$empleado['nombre']} no trabaja los " . mb_strtolower(Empleado::DIAS[$dia]) . '.';
            } elseif ($inicio->format('H:i') < $horario['hora_inicio'] || $fin->format('H:i') > $horario['hora_fin']
                      || $fin->format('Y-m-d') !== $inicio->format('Y-m-d')) {
                $errores['fecha'] = sprintf(
                    '%s trabaja de %s a %s ese día y el servicio dura %s; la cita terminaría a las %s.',
                    $empleado['nombre'], $horario['hora_inicio'], $horario['hora_fin'],
                    duracion($servicio['duracion_min']), $fin->format('H:i')
                );
            }
        }

        /* Sin empalmes */
        if (!$errores) {
            $citas = new Cita();

            $choque = $citas->empalmes('empleado_id', (int) $empleado['id'], $fechaHora, $fechaFin, $citaId);
            if ($choque) {
                $c = $choque[0];
                $errores['fecha'] = sprintf(
                    '%s ya tiene cita con %s de %s a %s.',
                    $empleado['nombre'], $c['cliente'], hora($c['fecha_hora']), hora($c['fecha_fin'])
                );
            }

            $choqueCliente = $citas->empalmes('cliente_id', (int) $cliente['id'], $fechaHora, $fechaFin, $citaId);
            if (!$errores && $choqueCliente) {
                $c = $choqueCliente[0];
                $errores['fecha'] = sprintf(
                    '%s ya tiene otra cita con %s de %s a %s.',
                    $cliente['nombre'], $c['empleado'], hora($c['fecha_hora']), hora($c['fecha_fin'])
                );
            }
        }

        return [
            'errores'    => $errores,
            'fecha_hora' => $fechaHora,
            'fecha_fin'  => $fechaFin,
            'servicio'   => $servicio,
        ];
    }

    /**
     * Horarios libres de un estilista en un dia para un servicio,
     * cada INTERVALO_MIN minutos.
     *
     * @return array{trabaja:bool, horario?:array, libres:string[]}
     */
    public function disponibilidad(int $empleadoId, string $fecha, int $duracion, ?int $excepto = null): array
    {
        $dia = (int) date('N', strtotime($fecha));
        $horario = (new Empleado())->horario($empleadoId)[$dia] ?? null;

        if (!$horario) {
            return ['trabaja' => false, 'libres' => []];
        }

        $ocupados = array_map(
            fn ($o) => [strtotime($o['fecha_hora']), strtotime($o['fecha_fin'])],
            (new Cita())->ocupadosDelDia($empleadoId, $fecha, $excepto)
        );

        $abre   = strtotime("{$fecha} {$horario['hora_inicio']}");
        $cierra = strtotime("{$fecha} {$horario['hora_fin']}");
        $ahora  = time();
        $paso   = self::INTERVALO_MIN * 60;
        $libres = [];

        for ($t = $abre; $t + $duracion * 60 <= $cierra; $t += $paso) {
            $finT = $t + $duracion * 60;
            if ($t < $ahora) {
                continue;
            }
            foreach ($ocupados as [$oi, $of]) {
                if ($t < $of && $finT > $oi) {
                    continue 2;
                }
            }
            $libres[] = date('H:i', $t);
        }

        return ['trabaja' => true, 'horario' => $horario, 'libres' => $libres];
    }

    /** Rango de horas para el calendario: de la apertura mas temprana al cierre mas tarde. */
    public function rangoGeneral(): array
    {
        $r = $this->db->query(
            'SELECT MIN(h.hora_inicio) AS desde, MAX(h.hora_fin) AS hasta
               FROM horarios h JOIN empleados e ON e.id = h.empleado_id WHERE e.activo = 1'
        )->fetch();

        return [
            'desde' => $r['desde'] ?: '09:00',
            'hasta' => $r['hasta'] ?: '20:00',
        ];
    }

    private function fila(string $sql, int $id): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
