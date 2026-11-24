<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Cliente;
use App\Models\Historial;
use App\Services\Uploader;

/**
 * Clientes: ambos roles pueden consultar, crear y editar; solo el
 * administrador puede eliminar.
 */
final class ClienteController extends Controller
{
    private const LABELS = [
        'nombre'           => 'El nombre',
        'telefono'         => 'El teléfono',
        'email'            => 'El correo',
        'fecha_nacimiento' => 'La fecha de nacimiento',
        'preferencias'     => 'Las preferencias',
        'notas'            => 'Las notas',
    ];

    public function index(): void
    {
        $this->requireAuth();

        $q      = (string) $this->input('q', '');
        $estado = (string) $this->input('estado', 'activos');
        $pagina = (int) $this->input('pagina', 1);

        $resultado = (new Cliente())->paginate($q, $estado, $pagina);

        $this->view('clientes/index', [
            'title'     => 'Clientes',
            'tour'      => 'clientes',
            'clientes'  => $resultado['items'],
            'total'     => $resultado['total'],
            'pagina'    => $resultado['pagina'],
            'paginas'   => $resultado['paginas'],
            'filtros'   => compact('q', 'estado'),
        ]);
    }

    public function show(string $id): void
    {
        $this->requireAuth();
        $cliente = $this->findOr404((int) $id);
        $model = new Cliente();
        $historial = new Historial();

        $this->view('clientes/show', [
            'title'   => $cliente['nombre'],
            'tour'    => 'cliente-ficha',
            'cliente' => $cliente,
            'resumen' => $model->resumen((int) $cliente['id']),
            'citas'   => $model->citas((int) $cliente['id']),
            'historial'     => array_map(fn ($h) => $h + [
                'antesUrl'   => foto_url($h['foto_antes'], 'historial'),
                'despuesUrl' => foto_url($h['foto_despues'], 'historial'),
                'editable'   => Auth::isAdmin() || (int) $h['empleado_id'] === Auth::empleadoId(),
            ], $historial->porCliente((int) $cliente['id'])),
            'ultimaFormula' => $historial->ultimaFormula((int) $cliente['id']),
            'scripts'       => [asset('js/comparador.js')],
            'edad'    => $cliente['fecha_nacimiento']
                ? (new \DateTime($cliente['fecha_nacimiento']))->diff(new \DateTime('today'))->y
                : null,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $this->view('clientes/form', [
            'title'   => 'Nuevo cliente',
            'tour'    => 'cliente-form',
            'cliente' => ['activo' => 1],
            'fotoUrl' => null,
            'action'  => url('/clientes'),
            'scripts' => [asset('js/fotos.js')],
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $data = $this->validated('/clientes/nuevo');

        $foto = Uploader::image($_FILES['foto'] ?? null, 'clientes');
        if (!$foto['ok']) {
            $this->back('/clientes/nuevo', ['foto' => $foto['error']], $_POST);
        }
        $data['foto'] = $foto['file'] ?? null;

        $id = (new Cliente())->create($data);

        Session::flash('success', "{$data['nombre']} quedó registrado.");
        $this->redirect("/clientes/{$id}");
    }

    public function edit(string $id): void
    {
        $this->requireAuth();
        $cliente = $this->findOr404((int) $id);

        $this->view('clientes/form', [
            'title'   => 'Editar cliente',
            'tour'    => 'cliente-form',
            'cliente' => $cliente,
            'fotoUrl' => foto_url($cliente['foto'], 'clientes'),
            'action'  => url("/clientes/{$cliente['id']}/actualizar"),
            'scripts' => [asset('js/fotos.js')],
        ]);
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $cliente = $this->findOr404((int) $id);
        $volverA = "/clientes/{$cliente['id']}/editar";

        $data = $this->validated($volverA);

        $foto = Uploader::image($_FILES['foto'] ?? null, 'clientes');
        if (!$foto['ok']) {
            $this->back($volverA, ['foto' => $foto['error']], $_POST);
        }

        if (isset($foto['file'])) {
            Uploader::delete($cliente['foto'], 'clientes');
            $data['foto'] = $foto['file'];
        } elseif (isset($_POST['quitar_foto'])) {
            Uploader::delete($cliente['foto'], 'clientes');
            $data['foto'] = null;
        }

        (new Cliente())->update((int) $cliente['id'], $data);

        Session::flash('success', "Datos de {$data['nombre']} actualizados.");
        $this->redirect("/clientes/{$cliente['id']}");
    }

    public function toggle(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $cliente = $this->findOr404((int) $id);
        $activo = (int) !$cliente['activo'];

        (new Cliente())->update((int) $cliente['id'], ['activo' => $activo]);

        Session::flash('success', $activo
            ? "{$cliente['nombre']} está activo de nuevo."
            : "{$cliente['nombre']} pasó a inactivos. Su historial se conserva.");
        $this->redirect("/clientes/{$cliente['id']}");
    }

    public function destroy(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();
        $cliente = $this->findOr404((int) $id);
        $model = new Cliente();

        if ($model->tieneCitas((int) $cliente['id'])) {
            Session::flash('error', "{$cliente['nombre']} tiene citas registradas. Desactívalo para conservar su historial.");
            $this->redirect("/clientes/{$cliente['id']}");
        }

        Uploader::delete($cliente['foto'], 'clientes');
        $model->delete((int) $cliente['id']);

        Session::flash('success', "{$cliente['nombre']} fue eliminado.");
        $this->redirect('/clientes');
    }

    private function validated(string $volverA): array
    {
        $v = Validator::make($_POST, [
            'nombre'           => 'required|max:80',
            'telefono'         => 'phone',
            'email'            => 'email|max:120',
            'fecha_nacimiento' => 'date',
            'preferencias'     => 'max:500',
            'notas'            => 'max:1000',
        ], self::LABELS);

        $nacimiento = trim((string) ($_POST['fecha_nacimiento'] ?? ''));
        if ($nacimiento !== '' && !$v->fails() && $nacimiento > date('Y-m-d')) {
            $v->addError('fecha_nacimiento', 'La fecha de nacimiento no puede ser futura.');
        }

        if (trim((string) ($_POST['telefono'] ?? '')) === '' && trim((string) ($_POST['email'] ?? '')) === '') {
            $v->addError('telefono', 'Registra al menos un teléfono o un correo para poder enviar recordatorios.');
        }

        if ($v->fails()) {
            $this->back($volverA, $v->errors(), $_POST);
        }

        $telefono = preg_replace('/\D/', '', (string) ($_POST['telefono'] ?? ''));

        return [
            'nombre'           => trim($_POST['nombre']),
            'telefono'         => $telefono ?: null,
            'email'            => mb_strtolower(trim($_POST['email'] ?? '')) ?: null,
            'fecha_nacimiento' => $nacimiento ?: null,
            'preferencias'     => trim($_POST['preferencias'] ?? '') ?: null,
            'notas'            => trim($_POST['notas'] ?? '') ?: null,
            'activo'           => isset($_POST['activo']) ? 1 : 0,
        ];
    }

    private function findOr404(int $id): array
    {
        $cliente = (new Cliente())->find($id);
        if (!$cliente) {
            Session::flash('error', 'Ese cliente ya no existe.');
            $this->redirect('/clientes');
        }
        return $cliente;
    }
}
