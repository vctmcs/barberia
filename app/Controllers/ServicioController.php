<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Servicio;

final class ServicioController extends Controller
{
    private const LABELS = [
        'nombre'       => 'El nombre',
        'tipo'         => 'El tipo',
        'descripcion'  => 'La descripción',
        'precio'       => 'El precio',
        'duracion_min' => 'La duración',
    ];

    public function index(): void
    {
        $this->requireAdmin();

        $q      = (string) $this->input('q', '');
        $tipo   = (string) $this->input('tipo', '');
        $estado = (string) $this->input('estado', 'activos');

        $this->view('servicios/index', [
            'title'     => 'Servicios',
            'tour'      => 'servicios',
            'servicios' => (new Servicio())->search($q, $tipo, $estado),
            'tipos'     => Servicio::TIPOS,
            'tiposCortos' => Servicio::TIPOS_CORTOS,
            'filtros'   => compact('q', 'tipo', 'estado'),
        ]);
    }

    public function create(): void
    {
        $this->requireAdmin();

        $this->view('servicios/form', [
            'title'    => 'Nuevo servicio',
            'tour'     => 'servicio-form',
            'servicio' => ['tipo' => 'corte', 'duracion_min' => 30, 'activo' => 1],
            'tipoActual' => Session::get('_old')['tipo'] ?? 'corte',
            'tipos'    => Servicio::TIPOS,
            'action'   => url('/servicios'),
        ]);
    }

    public function store(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $data = $this->validated('/servicios/nuevo');
        (new Servicio())->create($data);

        Session::flash('success', "Servicio \"{$data['nombre']}\" creado.");
        $this->redirect('/servicios');
    }

    public function edit(string $id): void
    {
        $this->requireAdmin();
        $servicio = $this->findOr404((int) $id);

        $this->view('servicios/form', [
            'title'    => 'Editar servicio',
            'tour'     => 'servicio-form',
            'servicio' => $servicio,
            'tipoActual' => Session::get('_old')['tipo'] ?? $servicio['tipo'],
            'tipos'    => Servicio::TIPOS,
            'action'   => url("/servicios/{$servicio['id']}/actualizar"),
        ]);
    }

    public function update(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();
        $servicio = $this->findOr404((int) $id);

        $data = $this->validated("/servicios/{$servicio['id']}/editar");
        (new Servicio())->update((int) $servicio['id'], $data);

        Session::flash('success', "Servicio \"{$data['nombre']}\" actualizado.");
        $this->redirect('/servicios');
    }

    public function toggle(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();
        $servicio = $this->findOr404((int) $id);

        $activo = (int) !$servicio['activo'];
        (new Servicio())->update((int) $servicio['id'], ['activo' => $activo]);

        Session::flash('success', $activo
            ? "\"{$servicio['nombre']}\" vuelve a estar disponible para agendar."
            : "\"{$servicio['nombre']}\" ya no aparece al agendar citas.");
        $this->redirect('/servicios');
    }

    public function destroy(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();
        $servicio = $this->findOr404((int) $id);
        $model = new Servicio();

        if ($model->enUso((int) $servicio['id'])) {
            Session::flash('error', "\"{$servicio['nombre']}\" tiene citas o historial registrados. Desactívalo en lugar de eliminarlo.");
            $this->redirect('/servicios');
        }

        $model->delete((int) $servicio['id']);
        Session::flash('success', "Servicio \"{$servicio['nombre']}\" eliminado.");
        $this->redirect('/servicios');
    }

    private function validated(string $volverA): array
    {
        $v = Validator::make($_POST, [
            'nombre'       => 'required|max:80',
            'tipo'         => 'required|in:' . implode(',', array_keys(Servicio::TIPOS)),
            'descripcion'  => 'max:255',
            'precio'       => 'required|numeric|min:0',
            'duracion_min' => 'required|integer|min:5',
        ], self::LABELS);

        if ($v->fails()) {
            $this->back($volverA, $v->errors(), $_POST);
        }

        return [
            'nombre'       => trim($_POST['nombre']),
            'tipo'         => $_POST['tipo'],
            'descripcion'  => trim($_POST['descripcion'] ?? '') ?: null,
            'precio'       => round((float) $_POST['precio'], 2),
            'duracion_min' => (int) $_POST['duracion_min'],
            'activo'       => isset($_POST['activo']) ? 1 : 0,
        ];
    }

    private function findOr404(int $id): array
    {
        $servicio = (new Servicio())->find($id);
        if (!$servicio) {
            Session::flash('error', 'Ese servicio ya no existe.');
            $this->redirect('/servicios');
        }
        return $servicio;
    }
}
