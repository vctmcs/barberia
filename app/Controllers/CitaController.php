<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Servicio;
use App\Services\Agenda;

/**
 * Agenda de citas. El administrador ve y agenda para todo el equipo;
 * cada estilista ve y agenda solo sus propias citas.
 */
final class CitaController extends Controller
{
    /* ------------------------------------------------------------------
       Calendario
       ------------------------------------------------------------------ */

    public function index(): void
    {
        $this->requireAuth();

        $empleados  = (new Empleado())->activos();
        $empleadoId = $this->empleadoFiltro();
        $agenda     = new Agenda();
        $rango      = $agenda->rangoGeneral();

        $config = [
            'eventos'     => url('/citas/eventos'),
            'nueva'       => url('/citas/nueva'),
            'detalle'     => url('/citas/'),
            'mover'       => url('/citas/'),
            'csrf'        => Session::csrfToken(),
            'empleado'    => $empleadoId,
            'desde'       => substr($rango['desde'], 0, 5) . ':00',
            'hasta'       => substr($rango['hasta'], 0, 5) . ':00',
            'horario'     => $this->businessHours($empleadoId, $empleados),
            'fecha'       => $this->fechaValida((string) $this->input('fecha', '')) ?? date('Y-m-d'),
        ];

        $this->view('citas/index', [
            'title'          => 'Agenda',
            'tour'           => 'agenda',
            'empleados'      => $empleados,
            'empleadoId'     => $empleadoId,
            'puedeFiltrar'   => Auth::isAdmin(),
            'pendientes'     => (new Cita())->pendientesDeCerrar(Auth::isAdmin() ? null : Auth::empleadoId()),
            'configJson'     => json_encode($config, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
            'scripts'        => [asset('vendor/fullcalendar/index.global.min.js'), asset('vendor/fullcalendar/es.global.min.js'), asset('js/agenda.js')],
        ]);
    }

    /** Fuente de eventos para FullCalendar. */
    public function eventos(): void
    {
        $this->requireAuth();

        $desde = $this->isoLocal((string) $this->input('start', ''));
        $hasta = $this->isoLocal((string) $this->input('end', ''));
        if (!$desde || !$hasta) {
            $this->json([], 400);
        }

        $conCanceladas = $this->input('canceladas') === '1';
        $citas = (new Cita())->enRango($desde, $hasta, $this->empleadoFiltro(), $conCanceladas);
        $ahora = date('Y-m-d H:i:s');

        $eventos = array_map(fn ($c) => [
            'id'              => (string) $c['id'],
            'title'           => $c['cliente'],
            'start'           => str_replace(' ', 'T', $c['fecha_hora']),
            'end'             => str_replace(' ', 'T', $c['fecha_fin']),
            'backgroundColor' => $c['color'],
            'borderColor'     => $c['color'],
            'classNames'      => ['ev', 'ev--' . $c['estado']],
            // Solo se arrastran las programadas que aun no empiezan
            'editable'        => $c['estado'] === 'programada' && $c['fecha_hora'] > $ahora,
            'extendedProps'   => [
                'servicio' => $c['servicio'],
                'empleado' => $c['empleado'],
                'estado'   => $c['estado'],
                'estadoTexto' => Cita::ESTADOS[$c['estado']],
            ],
        ], $citas);

        $this->json($eventos);
    }

    /* ------------------------------------------------------------------
       Alta y edicion
       ------------------------------------------------------------------ */

    public function create(): void
    {
        $this->requireAuth();

        $inicio = (string) $this->input('inicio', '');
        $fecha  = $this->fechaValida(substr($inicio, 0, 10)) ?? date('Y-m-d');
        $hora   = preg_match('/T(\d{2}:\d{2})/', $inicio, $m) ? $m[1] : '';

        $this->view('citas/form', $this->formData([
            'title'  => 'Nueva cita',
            'tour'   => 'cita-form',
            'cita'   => [
                'cliente_id'  => (int) $this->input('cliente', 0) ?: null,
                'empleado_id' => Auth::isAdmin() ? ((int) $this->input('empleado', 0) ?: null) : Auth::empleadoId(),
                'servicio_id' => null,
                'fecha'       => $fecha,
                'hora'        => $hora,
                'precio'      => '',
                'notas'       => '',
            ],
            'action' => url('/citas'),
        ]));
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        [$data, $errores] = $this->validated(null);
        if ($errores) {
            $this->back('/citas/nueva', $errores, $_POST);
        }

        $id = (new Cita())->create($data + ['estado' => 'programada']);

        Session::flash('success', 'Cita agendada para el ' . fecha_larga($data['fecha_hora']) . ' a las ' . hora($data['fecha_hora']) . '.');
        $this->redirect("/citas/{$id}");
    }

    public function show(string $id): void
    {
        $this->requireAuth();
        $cita = $this->findOr404((int) $id);
        $model = new Cita();

        $sitio = (array) Config::get('sitio', []);
        $mensaje = sprintf(
            'Hola %s, te recordamos tu cita en %s el %s a las %s para %s con %s.%s',
            explode(' ', $cita['cliente'])[0],
            Config::get('app.name'),
            fecha_larga($cita['fecha_hora']),
            hora($cita['fecha_hora']),
            mb_strtolower($cita['servicio']),
            explode(' ', $cita['empleado'])[0],
            !empty($sitio['direccion']) ? ' Te esperamos en ' . $sitio['direccion'] . '.' : ''
        );

        $recordatorios = [];
        foreach ($model->recordatorios((int) $cita['id']) as $r) {
            $recordatorios[$r['canal']] = $r;
        }

        $ahora = date('Y-m-d H:i:s');

        $this->view('citas/show', [
            'title'          => 'Cita de ' . $cita['cliente'],
            'tour'           => 'cita-detalle',
            'cita'           => $cita,
            'estados'        => Cita::ESTADOS,
            'yaEmpezo'       => $cita['fecha_hora'] <= $ahora,
            'editable'       => $cita['estado'] === 'programada',
            'mensajeWa'      => $mensaje,
            'tieneTelefono'  => !empty($cita['cliente_telefono']),
            'recordatorios'  => $recordatorios,
            'fotoCliente'    => foto_url($cita['cliente_foto'], 'clientes'),
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireAuth();
        $cita = $this->findOr404((int) $id);

        if ($cita['estado'] !== 'programada') {
            Session::flash('error', 'Solo se pueden editar citas programadas. Vuelve a programarla primero si necesitas cambiarla.');
            $this->redirect("/citas/{$cita['id']}");
        }

        $cita['fecha'] = substr($cita['fecha_hora'], 0, 10);
        $cita['hora']  = substr($cita['fecha_hora'], 11, 5);

        $this->view('citas/form', $this->formData([
            'title'  => 'Editar cita',
            'tour'   => 'cita-form',
            'cita'   => $cita,
            'action' => url("/citas/{$cita['id']}/actualizar"),
        ]));
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $cita = $this->findOr404((int) $id);

        if ($cita['estado'] !== 'programada') {
            $this->redirect("/citas/{$cita['id']}");
        }

        [$data, $errores] = $this->validated((int) $cita['id']);
        if ($errores) {
            $this->back("/citas/{$cita['id']}/editar", $errores, $_POST);
        }

        $model = new Cita();
        $model->update((int) $cita['id'], $data + ['actualizado_en' => date('Y-m-d H:i:s')]);
        if ($data['fecha_hora'] !== $cita['fecha_hora']) {
            $model->reiniciarRecordatorios((int) $cita['id']);
        }

        Session::flash('success', 'Cita actualizada.');
        $this->redirect("/citas/{$cita['id']}");
    }

    /* ------------------------------------------------------------------
       Cambios rapidos
       ------------------------------------------------------------------ */

    public function estado(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $cita = $this->findOr404((int) $id);
        $nuevo = (string) $this->input('estado', '');
        $volver = "/citas/{$cita['id']}";

        if (!isset(Cita::ESTADOS[$nuevo]) || $nuevo === $cita['estado']) {
            $this->redirect($volver);
        }

        $yaEmpezo = $cita['fecha_hora'] <= date('Y-m-d H:i:s');
        if (in_array($nuevo, ['atendida', 'no_asistio'], true) && !$yaEmpezo) {
            Session::flash('error', 'La cita aún no empieza: solo puedes cancelarla o editarla.');
            $this->redirect($volver);
        }

        /* Reabrir una cita: su horario debe seguir libre */
        if ($nuevo === 'programada') {
            $model = new Cita();
            $choque = $model->empalmes('empleado_id', (int) $cita['empleado_id'], $cita['fecha_hora'], $cita['fecha_fin'], (int) $cita['id']);
            if ($choque) {
                Session::flash('error', "Ese horario ya lo ocupa la cita de {$choque[0]['cliente']}. Edita la fecha después de reabrirla en otro horario.");
                $this->redirect($volver);
            }
        }

        (new Cita())->update((int) $cita['id'], ['estado' => $nuevo, 'actualizado_en' => date('Y-m-d H:i:s')]);

        $mensajes = [
            'atendida'   => 'Cita marcada como atendida.',
            'cancelada'  => 'Cita cancelada. El horario quedó libre.',
            'no_asistio' => 'Se registró que el cliente no asistió.',
            'programada' => 'La cita vuelve a estar programada.',
        ];
        Session::flash('success', $mensajes[$nuevo]);
        $this->redirect($volver);
    }

    /** Reprogramar arrastrando en el calendario (JSON). */
    public function mover(string $id): void
    {
        $this->requireAuth();
        if (!Session::validCsrf($_POST['_csrf'] ?? null)) {
            $this->json(['ok' => false, 'error' => 'Tu sesión expiró. Recarga la página.'], 419);
        }

        $cita = (new Cita())->findFull((int) $id);
        if (!$cita || !$this->puedeVer($cita)) {
            $this->json(['ok' => false, 'error' => 'La cita ya no existe.'], 404);
        }
        if ($cita['estado'] !== 'programada') {
            $this->json(['ok' => false, 'error' => 'Solo se pueden mover citas programadas.'], 422);
        }

        $inicio = str_replace('T', ' ', substr((string) ($_POST['inicio'] ?? ''), 0, 16));
        $r = (new Agenda())->validar([
            'cliente_id'  => (int) $cita['cliente_id'],
            'empleado_id' => (int) $cita['empleado_id'],
            'servicio_id' => (int) $cita['servicio_id'],
            'inicio'      => $inicio,
        ], (int) $cita['id']);

        if ($r['errores']) {
            $this->json(['ok' => false, 'error' => reset($r['errores'])], 422);
        }

        $model = new Cita();
        $model->update((int) $cita['id'], [
            'fecha_hora'     => $r['fecha_hora'],
            'fecha_fin'      => $r['fecha_fin'],
            'actualizado_en' => date('Y-m-d H:i:s'),
        ]);
        $model->reiniciarRecordatorios((int) $cita['id']);

        $this->json([
            'ok'      => true,
            'mensaje' => "Cita de {$cita['cliente']} movida al " . fecha_larga($r['fecha_hora']) . ' a las ' . hora($r['fecha_hora']) . '.',
        ]);
    }

    /** Horarios libres para el formulario (JSON). */
    public function disponibilidad(): void
    {
        $this->requireAuth();

        $empleadoId = Auth::isAdmin() ? (int) $this->input('empleado', 0) : (int) Auth::empleadoId();
        $fecha      = $this->fechaValida((string) $this->input('fecha', ''));
        $servicio   = (new Servicio())->find((int) $this->input('servicio', 0));
        $excepto    = (int) $this->input('excepto', 0) ?: null;

        if (!$empleadoId || !$fecha || !$servicio) {
            $this->json(['ok' => false]);
        }

        $d = (new Agenda())->disponibilidad($empleadoId, $fecha, (int) $servicio['duracion_min'], $excepto);
        $this->json(['ok' => true] + $d);
    }

    /** Registra el recordatorio por WhatsApp y abre el chat con el mensaje. */
    public function whatsapp(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $cita = $this->findOr404((int) $id);

        if (empty($cita['cliente_telefono'])) {
            Session::flash('error', "{$cita['cliente']} no tiene teléfono registrado.");
            $this->redirect("/citas/{$cita['id']}");
        }

        (new Cita())->registrarRecordatorio((int) $cita['id'], 'whatsapp', 'enviado', 'Abierto desde la agenda');

        header('Location: ' . whatsapp_url($cita['cliente_telefono'], (string) ($_POST['mensaje'] ?? '')));
        exit;
    }

    /* ------------------------------------------------------------------
       Apoyo
       ------------------------------------------------------------------ */

    /** @return array{0: array, 1: array<string,string>} */
    private function validated(?int $citaId): array
    {
        $empleadoId = Auth::isAdmin() ? (int) ($_POST['empleado_id'] ?? 0) : (int) Auth::empleadoId();
        $fecha = trim((string) ($_POST['fecha'] ?? ''));
        $hora  = trim((string) ($_POST['hora'] ?? ''));

        $r = (new Agenda())->validar([
            'cliente_id'  => (int) ($_POST['cliente_id'] ?? 0),
            'empleado_id' => $empleadoId,
            'servicio_id' => (int) ($_POST['servicio_id'] ?? 0),
            'inicio'      => "{$fecha} {$hora}",
        ], $citaId);

        $errores = $r['errores'];

        $precioTxt = trim((string) ($_POST['precio'] ?? ''));
        if ($precioTxt !== '' && (!is_numeric($precioTxt) || (float) $precioTxt < 0)) {
            $errores['precio'] = 'El precio debe ser un número mayor o igual a 0.';
        }
        $notas = trim((string) ($_POST['notas'] ?? ''));
        if (mb_strlen($notas) > 500) {
            $errores['notas'] = 'Las notas admiten hasta 500 caracteres.';
        }

        if ($errores) {
            return [[], $errores];
        }

        return [[
            'cliente_id'  => (int) $_POST['cliente_id'],
            'empleado_id' => $empleadoId,
            'servicio_id' => (int) $_POST['servicio_id'],
            'fecha_hora'  => $r['fecha_hora'],
            'fecha_fin'   => $r['fecha_fin'],
            'precio'      => $precioTxt === '' ? (float) $r['servicio']['precio'] : round((float) $precioTxt, 2),
            'notas'       => $notas ?: null,
        ], []];
    }

    private function formData(array $data): array
    {
        $old  = Session::get('_old', []);
        $cita = $data['cita'];

        $actual = fn (string $k) => $old ? ($old[$k] ?? '') : ($cita[$k] ?? '');

        $servicios = (new Servicio())->activos();
        $porTipo = [];
        foreach ($servicios as $s) {
            $porTipo[Servicio::TIPOS[$s['tipo']] ?? $s['tipo']][] = $s;
        }

        return $data + [
            'clientes'        => (new Cliente())->activos(),
            'empleados'       => (new Empleado())->activos(),
            'serviciosPorTipo'=> $porTipo,
            'esAdmin'         => Auth::isAdmin(),
            'valClienteId'    => (string) $actual('cliente_id'),
            'valEmpleadoId'   => (string) ($actual('empleado_id') ?: (Auth::isAdmin() ? '' : Auth::empleadoId())),
            'valServicioId'   => (string) $actual('servicio_id'),
            'valFecha'        => (string) $actual('fecha'),
            'valHora'         => (string) $actual('hora'),
            'valPrecio'       => (string) $actual('precio'),
            'valNotas'        => (string) $actual('notas'),
            'hoy'             => date('Y-m-d'),
            'citaId'          => $cita['id'] ?? null,
            'urlDisponibilidad' => url('/citas/disponibilidad'),
            'scripts'         => [asset('js/cita-form.js')],
        ];
    }

    /** Admin puede filtrar por estilista; el estilista solo ve lo suyo. */
    private function empleadoFiltro(): ?int
    {
        if (!Auth::isAdmin()) {
            return Auth::empleadoId() ?? -1;
        }
        $id = (int) $this->input('empleado', 0);
        return $id > 0 ? $id : null;
    }

    /** Horario laboral para sombrear el calendario (dias FullCalendar: 0 = domingo). */
    private function businessHours(?int $empleadoId, array $empleados): array
    {
        $ids = $empleadoId !== null ? [$empleadoId] : array_column($empleados, 'id');
        $model = new Empleado();
        $bloques = [];

        foreach ($ids as $id) {
            foreach ($model->horario((int) $id) as $dia => $h) {
                $bloques[] = [
                    'daysOfWeek' => [$dia % 7],
                    'startTime'  => substr($h['hora_inicio'], 0, 5),
                    'endTime'    => substr($h['hora_fin'], 0, 5),
                ];
            }
        }
        return $bloques;
    }

    private function puedeVer(array $cita): bool
    {
        return Auth::isAdmin() || (int) $cita['empleado_id'] === Auth::empleadoId();
    }

    private function findOr404(int $id): array
    {
        $cita = (new Cita())->findFull($id);
        if (!$cita || !$this->puedeVer($cita)) {
            Session::flash('error', 'Esa cita no existe o no es de tu agenda.');
            $this->redirect('/citas');
        }
        return $cita;
    }

    private function fechaValida(string $fecha): ?string
    {
        $d = \DateTime::createFromFormat('Y-m-d', $fecha);
        return ($d && $d->format('Y-m-d') === $fecha) ? $fecha : null;
    }

    /** "2026-11-23T00:00:00-06:00" -> "2026-11-23 00:00:00" */
    private function isoLocal(string $iso): ?string
    {
        return preg_match('/^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2}:\d{2})/', $iso, $m) ? "{$m[1]} {$m[2]}" : null;
    }
}
