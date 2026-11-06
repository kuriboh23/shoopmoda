<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Plain PHP view renderer (no template engine).
 *
 * The view is rendered into a buffer first, then injected as $content
 * into the layout. All variables must be escaped with e() in templates.
 */
final class View
{
    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        $resolved = self::resolveData($data);
        $content = self::capture($view, $resolved);
        $resolved['content'] = $content;

        self::output($layout, $resolved);
    }

    /**
     * Render a partial without a layout (echoed into the current buffer).
     *
     * @param array<string, mixed> $data
     */
    public static function partial(string $partial, array $data = []): void
    {
        echo self::capture($partial, self::resolveData($data));
    }

    /**
     * Return a rendered partial as a string.
     *
     * @param array<string, mixed> $data
     */
    public static function partialHtml(string $partial, array $data = []): string
    {
        return self::capture($partial, self::resolveData($data));
    }

    /**
     * Compute the final data array for a view, injecting global context.
     *
     * Views call helpers as plain functions (url(), e(), icon(), ...).
     * Those functions live in the global namespace, so we pass them into
     * the view scope as string callables and rely on the autoloader /
     * explicit requires in bootstrap to make them available.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function resolveData(array $data): array
    {
        $context = [
            'url'              => 'url',
            'e'               => 'e',
            'icon'            => 'icon',
            'setting'         => 'setting',
            'settings'        => 'settings',
            'product_image_url' => 'product_image_url',
            'currency'        => 'currency',
            'format_price'    => 'format_price',
        ];

        foreach ($context as $alias => $name) {
            if (!array_key_exists($alias, $data)) {
                $data[$alias] = $name;
            }
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function capture(string $view, array $data): string
    {
        $file = APP_PATH . '/views/' . $view . '.php';

        if (!is_file($file)) {
            throw new \RuntimeException('Vue introuvable : ' . $view);
        }

        // If the helper functions are not already in the global namespace,
        // require them now. The bootstrap usually loads them earlier, but
        // this keeps the renderer safe when boot order changes.
        if (!function_exists('url') || !function_exists('icon')) {
            if (is_file(APP_PATH . '/helpers/functions.php')) {
                require APP_PATH . '/helpers/functions.php';
            }
            if (is_file(APP_PATH . '/helpers/Helpers.php')) {
                require APP_PATH . '/helpers/Helpers.php';
            }
            if (is_file(APP_PATH . '/helpers/icons.php')) {
                require APP_PATH . '/helpers/icons.php';
            }
        }

        extract($data, EXTR_SKIP);

        ob_start();

        try {
            require $file;
        } catch (\Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }

        $buffer = ob_get_clean();

        return $buffer === false ? '' : $buffer;
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function output(string $layout, array $data): void
    {
        $file = APP_PATH . '/views/' . $layout . '.php';

        if (!is_file($file)) {
            throw new \RuntimeException('Layout introuvable : ' . $layout);
        }

        // Make partial() available inside layout templates as a local helper.
        $renderPartial = static function (string $name, array $data = []) {
            self::renderPartial($name, $data);
        };

        extract($data, EXTR_SKIP);
        extract(['partial' => $renderPartial], EXTR_SKIP);

        require $file;
    }

    /**
     * Render a partial inside a layout context.
     *
     * @param array<string, mixed> $data
     */
    private static function renderPartial(string $name, array $data = []): void
    {
        echo self::capture($name, self::resolveData($data));
    }
}
