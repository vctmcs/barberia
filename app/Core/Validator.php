<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Validacion sencilla por reglas encadenadas con "|".
 *
 *   $v = Validator::make($_POST, [
 *       'nombre' => 'required|max:80',
 *       'precio' => 'required|numeric|min:0',
 *   ]);
 *   if ($v->fails()) { $v->errors(); }
 *
 * Reglas: required, max:n, min:n (numerico), numeric, integer, email,
 * date, time, phone, color, in:a,b,c
 */
final class Validator
{
    private array $errors = [];

    private function __construct(private array $data, private array $labels)
    {
    }

    public static function make(array $data, array $rules, array $labels = []): self
    {
        $v = new self($data, $labels);

        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? null;
            $value = is_string($value) ? trim($value) : $value;

            foreach (explode('|', $ruleString) as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);

                if ($name !== 'required' && ($value === null || $value === '')) {
                    continue; // campo opcional vacio
                }

                $error = $v->check($name, $param, $value, $field);
                if ($error !== null) {
                    $v->errors[$field] = $error;
                    break;
                }
            }
        }

        return $v;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    private function check(string $rule, ?string $param, mixed $value, string $field): ?string
    {
        $label = $this->labels[$field] ?? 'Este campo';

        return match ($rule) {
            'required' => ($value === null || $value === '' || $value === [])
                ? "{$label} es obligatorio." : null,
            'max'      => mb_strlen((string) $value) > (int) $param
                ? "{$label} admite hasta {$param} caracteres." : null,
            'min'      => (float) $value < (float) $param
                ? "{$label} debe ser mayor o igual a {$param}." : null,
            'numeric'  => !is_numeric($value)
                ? "{$label} debe ser un número." : null,
            'integer'  => filter_var($value, FILTER_VALIDATE_INT) === false
                ? "{$label} debe ser un número entero." : null,
            'email'    => !filter_var($value, FILTER_VALIDATE_EMAIL)
                ? 'Escribe un correo válido, por ejemplo nombre@correo.com.' : null,
            'date'     => !self::validDate((string) $value)
                ? "{$label} no es una fecha válida." : null,
            'time'     => !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', (string) $value)
                ? "{$label} debe tener formato HH:MM." : null,
            'phone'    => !preg_match('/^\d{10}$/', preg_replace('/\D/', '', (string) $value) ?? '')
                ? 'El teléfono debe tener 10 dígitos.' : null,
            'color'    => !preg_match('/^#[0-9a-fA-F]{6}$/', (string) $value)
                ? 'Elige un color válido.' : null,
            'in'       => !in_array((string) $value, explode(',', (string) $param), true)
                ? "{$label} tiene un valor no permitido." : null,
            default    => throw new \InvalidArgumentException("Regla desconocida: {$rule}"),
        };
    }

    private static function validDate(string $value): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $value);
        return $d !== false && $d->format('Y-m-d') === $value;
    }
}
