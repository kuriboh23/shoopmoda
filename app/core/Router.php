<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Tiny regex router.
 *
 * Routes are declared in app/routes.php with `{name}` placeholders, e.g.
 *   $router->get('/produit/{slug}', [ProductController::class, 'show']);
 * The placeholder value is passed to the controller as a string argument.
 */
final class Router
{
    /** @var array<int, array{method: string, regex: string, names: array<int, string>, handler: array|callable}> */
    private array $routes = [];

    /** @var array<int, array{method: string, regex: string, names: array<int, string>, handler: array|callable}> */
    private array $compiled = [];

    /** @var array|callable|null */
    private $notFoundHandler = null;

    public function get(string $path, array|callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array|callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function any(string $path, array|callable $handler): void
    {
        $this->add('GET', $path, $handler);
        $this->add('POST', $path, $handler);
    }

    public function notFound(array|callable $handler, bool $return404 = true): void
    {
        $this->notFoundHandler = $handler;
    }

    private function add(string $method, string $path, array|callable $handler): void
    {
        [$regex, $names] = self::compile($path);

        $route = [
            'method'  => $method,
            'regex'   => $regex,
            'names'   => $names,
            'handler' => $handler,
        ];

        $this->routes[] = $route;
        $this->compiled[] = $route;
    }

    /**
     * Turn "/produit/{slug}" into a regex plus the ordered placeholder names.
     *
     * @return array{0: string, 1: array<int, string>}
     */
    private static function compile(string $path): array
    {
        $path     = '/' . trim($path, '/');
        $segments = explode('/', $path);
        $names    = [];
        $parts    = [];

        foreach ($segments as $segment) {
            if (preg_match('/^\\{([a-zA-Z_][a-zA-Z0-9_]*)\\}$/', $segment, $match) === 1) {
                $names[] = $match[1];
                $parts[] = '(?P<' . $match[1] . '>[^/]+)';
                continue;
            }

            $parts[] = preg_quote($segment, '#');
        }

        return ['#^' . implode('/', $parts) . '$#u', $names];
    }

    /**
     * Normalized path of any request URI (used by the router and by current_path()).
     */
    public static function pathFromUri(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            $path = '/';
        }

        return self::normalize(self::stripBasePath(rawurldecode($path)));
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = self::pathFromUri($uri);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['regex'], $path, $matches) !== 1) {
                continue;
            }

            $args = [];

            foreach ($route['names'] as $name) {
                $args[] = $matches[$name] ?? '';
            }

            self::invoke($route['handler'], $args);

            return;
        }

        $this->handleNotFound();
    }

    private static function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    /**
     * Support installation in a sub-directory (e.g. /shoopmoda/public).
     */
    private static function stripBasePath(string $path): string
    {
        $script = str_replace('\\\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $base   = rtrim(str_replace('\\\\', '/', dirname($script)), '/');

        if ($base === '' || $base === '.') {
            return $path;
        }

        if (str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        return $path === '' ? '/' : $path;
    }

    /**
     * @param array|callable $handler
     * @param array<int, string> $args
     */
    private static function invoke(array|callable $handler, array $args): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            $controller->{$method}(...$args);

            return;
        }

        $handler(...$args);
    }

    private function handleNotFound(): void
    {
        if ($this->notFoundHandler !== null) {
            self::invoke($this->notFoundHandler, []);

            return;
        }

        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo '404 - Page introuvable';
    }

    /**
     * @return array<int, array{method: string, handler: array|callable}>
     */
    public function all(): array
    {
        $out = [];

        foreach ($this->compiled as $route) {
            $out[] = [
                'method'  => $route['method'],
                'handler' => $route['handler'],
            ];
        }

        return $out;
    }
}
