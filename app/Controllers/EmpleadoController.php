<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Empleado;
use App\Models\Usuario;

final class EmpleadoController extends Controller
{
    private const LABELS = [
        'nombre'       => 'El nombre',
        'telefono'     => 'El teléfono',
        'email'        => 'El correo',
        'especialidad' => 'La especialidad',
        'color'        => 'El color',
    ];

    public function index(): void
    {
        $this->requireAdmin();

        $q      = (string) $this->input('q', '');
        $estado = (string) $this->input('estado', 'activos');

        $this->view('empleados/index', [
            'title'          => 'Empleados',
            'tour'           => 'empleados',
            'empleados'      => (new Empleado())->search($q, $estado),
            'especialidades' => Empleado::ESPECIALIDADES,
            'filtros'        => compact('q', 'estado'),
        ]);
    }

    public function create(): void
    {
        $this->requireAdmin();

        $this->view('empleados/form', $this->formData([
            'title'    => 'Nuevo empleado',
            'tour'     => 'empleado-form',
            'empleado' => [
                'especialidad' => 'barbero',
                'color'        => '#2de2e6',
                'activo'       => 1,
                'horario'      => $this->horarioBase(),
            ],
            'action'   => url('/empleados'),
        ]));
    }

    public function store(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        [$data, $horario, $acceso] = $this->validated('/empleados/nuevo', null);
        $model = new Empleado();

        $model->transaction(function () use ($model, $data, $horario, $acceso) {
            if ($acceso['crear']) {
                $data['usuario_id'] = (new Usuario())->create([
                    'nombre'        => $data['nombre'],
                    'email'         => $acceso['email'],
                    'password_hash' => password_hash($acceso['password'], PASSWORD_DEFAULT),
                    'rol'           => 'barbero',
                    'activo'        => $data['activo'],
                ]);
            }
            $id = $model->create($data);
            $model->guardarHorario($id, $horario);
        });

        Session::flash('success', "{$data['nombre']} quedó registrado.");
        $this->redirect('/empleados');
    }

    public function edit(string $id): void
    {
        $this->requireAdmin();
        $empleado = $this->findOr404((int) $id);

        $this->view('empleados/form', $this->formData([
            'title'    => 'Editar empleado',
            'tour'     => 'empleado-form',
            'empleado' => $empleado,
            'action'   => url("/empleados/{$empleado['id']}/actualizar"),
        ]));
    }

    public function update(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();
        $empleado = $this->findOr404((int) $id);
        $usuarioId = $empleado['usuario_id'] !== null ? (int) $empleado['usuario_id'] : null;

        [$data, $horario, $acceso] = $this->validated("/empleados/{$empleado['id']}/editar", $usuarioId);
        $model = new Empleado();

        $model->transaction(function () use ($model, $empleado, $usuarioId, $data, $horario, $acceso) {
            $usuarios = new Usuario();

            if ($usuarioId !== null) {
                $cambios = ['nombre' => $data['nombre'], 'email' => $acceso['email'], 'activo' => $data['activo']];
                if ($acceso['password'] !== '') {
                    $cambios['password_hash'] = password_hash($acceso['password'], PASSWORD_DEFAULT);
                }
                $usuarios->update($usuarioId, $cambios);
            } elseif ($acceso['crear']) {
                $data['usuario_id'] = $usuarios->create([
                    'nombre'        => $data['nombre'],
                    'email'         => $acceso['email'],
                    'password_hash' => password_hash($acceso['password'], PASSWORD_DEFAULT),
                    'rol'           => 'barbero',
                    'activo'        => $data['activo'],
                ]);
            }

            $model->update((int) $empleado['id'], $data);
            $model->guardarHorario((int) $empleado['id'], $horario);
        });

        Session::flash('success', "Datos de {$data['nombre']} actualizados.");
        $this->redirect('/empleados');
    }

    public function toggle(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();
        $empleado = $this->findOr404((int) $id);
        $activo = (int) !$empleado['activo'];

        (new Empleado())->update((int) $empleado['id'], ['activo' => $activo]);
        if ($empleado['usuario_id'] !== null && (int) $empleado['usuario_id'] !== Auth::id()) {
            (new Usuario())->update((int) $empleado['usuario_id'], ['activo' => $activo]);
        }

        Session::flash('success', $activo
            ? "{$empleado['nombre']} está activo de nuevo y puede iniciar sesión."
            : "{$empleado['nombre']} quedó inactivo: ya no aparece en la agenda ni puede iniciar sesión.");
        $this->redirect('/empleados');
    }

    public function destroy(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();
        $empleado = $this->findOr404((int) $id);
        $model = new Empleado();

        if ($model->enUso((int) $empleado['id'])) {
            Session::flash('error', "{$empleado['nombre']} tiene citas registradas. Desactívalo en lugar de eliminarlo.");
            $this->redirect('/empleados');
        }

        $model->transaction(function () use ($model, $empleado) {
            $model->delete((int) $empleado['id']);
            if ($empleado['usuario_id'] !== null && (int) $empleado['usuario_id'] !== Auth::id()) {
                (new Usuario())->delete((int) $empleado['usuario_id']);
            }
        });

        Session::flash('success', "{$empleado['nombre']} fue eliminado.");
        $this->redirect('/empleados');
    }

    /**
     * @return array{0: array, 1: array, 2: array{crear:bool, email:string, password:string}}
     */
    private function validated(string $volverA, ?int $usuarioId): array
    {
        $v = Validator::make($_POST, [
            'nombre'       => 'required|max:80',
            'telefono'     => 'phone',
            'email'        => 'email|max:120',
            'especialidad' => 'required|in:' . implode(',', array_keys(Empleado::ESPECIALIDADES)),
            'color'        => 'required|color',
        ], self::LABELS);

        /* Horario: solo los dias marcados */
        $horario = [];
        foreach (Empleado::DIAS as $dia => $nombreDia) {
            $h = $_POST['horario'][$dia] ?? [];
            if (empty($h['trabaja'])) {
                continue;
            }
            $inicio = (string) ($h['inicio'] ?? '');
            $fin    = (string) ($h['fin'] ?? '');

            if (!preg_match('/^\d{2}:\d{2}$/', $inicio) || !preg_match('/^\d{2}:\d{2}$/', $fin)) {
                $v->addError("horario_{$dia}", "{$nombreDia}: indica la hora de entrada y de salida.");
            } elseif ($fin <= $inicio) {
                $v->addError("horario_{$dia}", "{$nombreDia}: la salida debe ser después de la entrada.");
            } else {
                $horario[$dia] = ['inicio' => $inicio, 'fin' => $fin];
            }
        }

        /* Acceso al sistema */
        $tieneCuenta = $usuarioId !== null;
        $crear       = !$tieneCuenta && isset($_POST['acceso']);
        $accesoEmail = mb_strtolower(trim((string) ($_POST['acceso_email'] ?? '')));
        $password    = (string) ($_POST['acceso_password'] ?? '');

        if ($tieneCuenta || $crear) {
            $model = new Empleado();
            if (!filter_var($accesoEmail, FILTER_VALIDATE_EMAIL)) {
                $v->addError('acceso_email', 'Escribe el correo con el que iniciará sesión.');
            } elseif ($model->emailUsado($accesoEmail, $usuarioId)) {
                $v->addError('acceso_email', 'Ese correo ya lo usa otra cuenta.');
            }

            $passwordObligatoria = $crear;
            if (($passwordObligatoria || $password !== '') && mb_strlen($password) < 8) {
                $v->addError('acceso_password', 'La contraseña debe tener al menos 8 caracteres.');
            }
        }

        if ($v->fails()) {
            $old = $_POST;
            unset($old['acceso_password']);
            $this->back($volverA, $v->errors(), $old);
        }

        $telefono = preg_replace('/\D/', '', (string) ($_POST['telefono'] ?? ''));

        $data = [
            'nombre'       => trim($_POST['nombre']),
            'telefono'     => $telefono ?: null,
            'email'        => mb_strtolower(trim($_POST['email'] ?? '')) ?: null,
            'especialidad' => $_POST['especialidad'],
            'color'        => strtolower($_POST['color']),
            'activo'       => isset($_POST['activo']) ? 1 : 0,
        ];

        return [$data, $horario, ['crear' => $crear, 'email' => $accesoEmail, 'password' => $password]];
    }

    private function formData(array $data): array
    {
        $old = Session::get('_old', []);

        /* Si hubo error, reconstruir el horario desde lo capturado */
        if ($old) {
            $horario = [];
            foreach ($old['horario'] ?? [] as $dia => $h) {
                if (!empty($h['trabaja'])) {
                    $horario[(int) $dia] = ['hora_inicio' => $h['inicio'] ?? '', 'hora_fin' => $h['fin'] ?? ''];
                }
            }
            $data['empleado']['horario'] = $horario;
        }

        $empleado    = $data['empleado'];
        $tieneCuenta = !empty($empleado['usuario_id']);

        /* Una fila por dia de la semana, lista para la vista */
        $horarioForm = [];
        foreach (Empleado::DIAS as $num => $nombreDia) {
            $h = $empleado['horario'][$num] ?? null;
            $horarioForm[$num] = [
                'dia'     => $nombreDia,
                'trabaja' => $h !== null,
                'inicio'  => substr($h['hora_inicio'] ?? '10:00', 0, 5),
                'fin'     => substr($h['hora_fin'] ?? '19:00', 0, 5),
            ];
        }

        return $data + [
            'tieneCuenta'    => $tieneCuenta,
            'horarioForm'    => $horarioForm,
            'conAcceso'      => $tieneCuenta || isset($old['acceso']),
            'colorActual'    => strtolower($old['color'] ?? $empleado['color'] ?? '#2de2e6'),
            'espActual'      => $old['especialidad'] ?? $empleado['especialidad'] ?? 'barbero',
            'especialidades' => Empleado::ESPECIALIDADES,
            'dias'           => Empleado::DIAS,
            'colores'        => ['#ff3d9a', '#2de2e6', '#ff9e3d', '#ffd23f', '#5cf2a9', '#b388ff'],
        ];
    }

    /** Horario sugerido para un empleado nuevo: martes a sabado 10 a 19. */
    private function horarioBase(): array
    {
        $h = [];
        foreach ([2, 3, 4, 5, 6] as $dia) {
            $h[$dia] = ['hora_inicio' => '10:00', 'hora_fin' => '19:00'];
        }
        return $h;
    }

    private function findOr404(int $id): array
    {
        $empleado = (new Empleado())->findFull($id);
        if (!$empleado) {
            Session::flash('error', 'Ese empleado ya no existe.');
            $this->redirect('/empleados');
        }
        return $empleado;
    }
}
