<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Renderiza plantillas de app/Views. Las vistas solo contienen HTML
 * con impresiones minimas de variables; nada de logica ni consultas.
 */
final class View
{
    public function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        $content = $this->capture($view, $data);

        if ($layout === '') {
            echo $content;
            return;
        }

        echo $this->capture($layout, array_merge($data, ['content' => $content]));
    }

    private function capture(string $view, array $data): string
    {
        $file = BASE_PATH . '/app/Views/' . $view . '.php';

        if (!is_file($file)) {
            throw new \RuntimeException("Vista no encontrada: {$view}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $file;
        return (string) ob_get_clean();
    }
}
