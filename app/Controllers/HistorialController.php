<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Historial;
use App\Models\Servicio;
use App\Services\Uploader;

/**
 * Registro de lo que se le hizo a un cliente. Normalmente nace de una
 * cita atendida; tambien se puede registrar suelto desde la ficha del
 * cliente (por ejemplo, un servicio que no se agendo).
 */
final class HistorialController extends Controller
{
    public function create(): void
    {
        $this->requireAuth();

        $citaId = (int) $this->input('cita', 0);

        if ($citaId) {
            $cita = $this->citaAtendida($citaId);
            $existente = (new Historial())->porCita($citaId);
            if ($existente) {
                $this->redirect("/historial/{$existente['id']}/editar");
            }

            $this->view('historial/form', $this->formData([
                'title'    => 'Registrar resultado',
                'registro' => [
                    'cita_id'     => $cita['id'],
                    'cliente_id'  => $cita['cliente_id'],
                    'servicio_id' => $cita['servicio_id'],
                    'empleado_id' => $cita['empleado_id'],
                    'fecha'       => substr($cita['fecha_hora'], 0, 10),
                ],
                'cita'     => $cita,
                'action'   => url('/historial'),
            ]));
            return;
        }

        $cliente = (new Cliente())->find((int) $this->input('cliente', 0));
        if (!$cliente) {
            Session::flash('error', 'Elige primero al cliente.');
            $this->redirect('/clientes');
        }

        $this->view('historial/form', $this->formData([
            'title'    => 'Registrar servicio',
            'registro' => [
                'cliente_id'  => $cliente['id'],
                'empleado_id' => Auth::isAdmin() ? null : Auth::empleadoId(),
                'fecha'       => date('Y-m-d'),
            ],
            'cita'     => null,
            'action'   => url('/historial'),
        ]));
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $citaId = (int) ($_POST['cita_id'] ?? 0);
        $volver = $citaId ? "/historial/nuevo?cita={$citaId}" : '/historial/nuevo?cliente=' . (int) ($_POST['cliente_id'] ?? 0);

        if ($citaId) {
            $cita = $this->citaAtendida($citaId);
            if ((new Historial())->porCita($citaId)) {
                $this->redirect('/clientes/' . $cita['cliente_id']);
            }
            $base = [
                'cita_id'     => $cita['id'],
                'cliente_id'  => $cita['cliente_id'],
                'servicio_id' => $cita['servicio_id'],
                'empleado_id' => $cita['empleado_id'],
                'fecha'       => substr($cita['fecha_hora'], 0, 10),
            ];
        } else {
            $base = $this->validarLibre($volver);
        }

        $base += $this->validarTextos($volver);
        $fotos = $this->subirFotos($volver, null);

        $model = new Historial();
        $model->create($base + $fotos);

        Session::flash('success', 'Resultado guardado en el historial de ' . $this->nombreCliente((int) $base['cliente_id']) . '.');
        $this->redirect('/clientes/' . $base['cliente_id'] . '#historial');
    }

    public function edit(string $id): void
    {
        $this->requireAuth();
        $registro = $this->findOr404((int) $id);

        $this->view('historial/form', $this->formData([
            'title'    => 'Editar registro',
            'registro' => $registro,
            'cita'     => $registro['cita_id'] ? (new Cita())->findFull((int) $registro['cita_id']) : null,
            'action'   => url("/historial/{$registro['id']}/actualizar"),
        ]));
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $registro = $this->findOr404((int) $id);
        $volver = "/historial/{$registro['id']}/editar";

        $datos = $registro['cita_id'] ? [] : $this->validarLibre($volver);
        $datos += $this->validarTextos($volver);
        $datos += $this->subirFotos($volver, $registro);

        (new Historial())->update((int) $registro['id'], $datos);

        Session::flash('success', 'Registro actualizado.');
        $this->redirect('/clientes/' . $registro['cliente_id'] . '#historial');
    }

    public function destroy(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();
        $registro = $this->findOr404((int) $id);

        Uploader::delete($registro['foto_antes'], 'historial');
        Uploader::delete($registro['foto_despues'], 'historial');
        (new Historial())->delete((int) $registro['id']);

        Session::flash('success', 'Registro eliminado del historial.');
        $this->redirect('/clientes/' . $registro['cliente_id'] . '#historial');
    }

    /* ------------------------------------------------------------------ */

    /** Registro suelto: servicio, estilista y fecha vienen del formulario. */
    private function validarLibre(string $volver): array
    {
        $errores = [];
        $cliente  = (new Cliente())->find((int) ($_POST['cliente_id'] ?? 0));
        $servicio = (new Servicio())->find((int) ($_POST['servicio_id'] ?? 0));
        $empleadoId = Auth::isAdmin() ? (int) ($_POST['empleado_id'] ?? 0) : (int) Auth::empleadoId();
        $empleado = $empleadoId ? (new Empleado())->find($empleadoId) : null;
        $fecha = trim((string) ($_POST['fecha'] ?? ''));
        $f = \DateTime::createFromFormat('Y-m-d', $fecha);

        if (!$cliente)  { $errores['cliente_id']  = 'El cliente ya no existe.'; }
        if (!$servicio) { $errores['servicio_id'] = 'Elige el servicio que se realizó.'; }
        if (!$empleado) { $errores['empleado_id'] = 'Elige quién lo realizó.'; }
        if (!$f || $f->format('Y-m-d') !== $fecha) {
            $errores['fecha'] = 'Indica la fecha del servicio.';
        } elseif ($fecha > date('Y-m-d')) {
            $errores['fecha'] = 'La fecha no puede ser futura: el historial registra servicios ya hechos.';
        }

        if ($errores) {
            $this->back($volver, $errores, $_POST);
        }

        return [
            'cliente_id'  => (int) $cliente['id'],
            'servicio_id' => (int) $servicio['id'],
            'empleado_id' => (int) $empleado['id'],
            'fecha'       => $fecha,
        ];
    }

    private function validarTextos(string $volver): array
    {
        $formula = trim((string) ($_POST['formula_tinte'] ?? ''));
        $obs     = trim((string) ($_POST['observaciones'] ?? ''));
        $errores = [];

        if (mb_strlen($formula) > 1000) { $errores['formula_tinte'] = 'La fórmula admite hasta 1000 caracteres.'; }
        if (mb_strlen($obs) > 1000)     { $errores['observaciones'] = 'Las observaciones admiten hasta 1000 caracteres.'; }

        if ($errores) {
            $this->back($volver, $errores, $_POST);
        }

        return [
            'formula_tinte' => $formula ?: null,
            'observaciones' => $obs ?: null,
        ];
    }

    /**
     * Sube las fotos de antes y despues. Si una falla se descarta lo que
     * ya se subio en esta peticion para no dejar archivos huerfanos.
     */
    private function subirFotos(string $volver, ?array $actual): array
    {
        $datos = [];
        $nuevas = [];

        foreach (['foto_antes', 'foto_despues'] as $campo) {
            $r = Uploader::image($_FILES[$campo] ?? null, 'historial');

            if (!$r['ok']) {
                foreach ($nuevas as $archivo) {
                    Uploader::delete($archivo, 'historial');
                }
                $this->back($volver, [$campo => $r['error']], $_POST);
            }

            if (isset($r['file'])) {
                $nuevas[] = $r['file'];
                $datos[$campo] = $r['file'];
            } elseif ($actual && isset($_POST["quitar_{$campo}"])) {
                $datos[$campo] = null;
            }
        }

        /* Borra del disco las fotos reemplazadas o quitadas */
        if ($actual) {
            foreach (['foto_antes', 'foto_despues'] as $campo) {
                if (array_key_exists($campo, $datos) && $actual[$campo]) {
                    Uploader::delete($actual[$campo], 'historial');
                }
            }
        }

        return $datos;
    }

    private function formData(array $data): array
    {
        $old = Session::get('_old', []);
        $r = $data['registro'];
        $val = fn (string $k) => (string) ($old ? ($old[$k] ?? '') : ($r[$k] ?? ''));

        $servicios = (new Servicio())->activos();
        $tipoServicio = null;
        foreach ($servicios as $s) {
            if ((string) $s['id'] === $val('servicio_id')) {
                $tipoServicio = $s['tipo'];
            }
        }
        if ($data['cita']) {
            $tipoServicio = $data['cita']['servicio_tipo'];
        }

        $porTipo = [];
        foreach ($servicios as $s) {
            $porTipo[Servicio::TIPOS[$s['tipo']] ?? $s['tipo']][] = $s;
        }

        $clienteId = (int) $r['cliente_id'];

        return $data + [
            'tour'             => 'historial-form',
            'cliente'          => (new Cliente())->find($clienteId),
            'ultimaFormula'    => (new Historial())->ultimaFormula($clienteId),
            'serviciosPorTipo' => $porTipo,
            'empleados'        => (new Empleado())->activos(),
            'esAdmin'          => Auth::isAdmin(),
            'valServicioId'    => $val('servicio_id'),
            'valEmpleadoId'    => $val('empleado_id'),
            'valFecha'         => $val('fecha'),
            'valFormula'       => $val('formula_tinte'),
            'valObservaciones' => $val('observaciones'),
            'formulaDestacada' => $tipoServicio === null || in_array($tipoServicio, Historial::TIPOS_CON_FORMULA, true),
            'tiposConFormula'  => implode(',', Historial::TIPOS_CON_FORMULA),
            'fotoAntesUrl'     => foto_url($r['foto_antes'] ?? null, 'historial'),
            'fotoDespuesUrl'   => foto_url($r['foto_despues'] ?? null, 'historial'),
            'hoy'              => date('Y-m-d'),
            'registroId'       => $r['id'] ?? null,
            'scripts'          => [asset('js/fotos.js'), asset('js/historial-form.js')],
        ];
    }

    private function citaAtendida(int $citaId): array
    {
        $cita = (new Cita())->findFull($citaId);
        if (!$cita || (!Auth::isAdmin() && (int) $cita['empleado_id'] !== Auth::empleadoId())) {
            Session::flash('error', 'Esa cita no existe o no es de tu agenda.');
            $this->redirect('/citas');
        }
        if ($cita['estado'] !== 'atendida') {
            Session::flash('error', 'Primero marca la cita como atendida.');
            $this->redirect("/citas/{$citaId}");
        }
        return $cita;
    }

    private function findOr404(int $id): array
    {
        $registro = (new Historial())->findFull($id);
        $propio = $registro && (Auth::isAdmin() || (int) $registro['empleado_id'] === Auth::empleadoId());

        if (!$propio) {
            Session::flash('error', 'Ese registro no existe o lo hizo otra persona.');
            $this->redirect($registro ? '/clientes/' . $registro['cliente_id'] : '/clientes');
        }
        return $registro;
    }

    private function nombreCliente(int $id): string
    {
        return (string) ((new Cliente())->find($id)['nombre'] ?? 'el cliente');
    }
}
