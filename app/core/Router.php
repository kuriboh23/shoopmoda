<?php

declare(strict_types=1);

namespace App\Core;

/**
 * The smallest router this shop needs.
 *
 * Routes are declared in app/routes.php:
 *
 *     $router->get('/produit/{slug}', [ProductController::class, 'product']);
 *
 * A {name} segment matches any single value and is passed to the controller
 * method as a string argument, in the order it appears in the path.
 *
 * Paths are compared WITHOUT the folder the site is installed in: if the
 * shop runs at https://example.com/shop/, the request /shop/produit/robe is
 * matched against /produit/robe. This is why the same files work at the
 * domain root (htdocs/) and inside a sub-folder (htdocs/shop/) with no
 * configuration at all.
 */
final class Router
{
    /**
     * @var array<int, array{method: string, segments: array<int, string>, handler: array|callable}>
     */
    private array $routes = [];

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

    public function notFound(array|callable $handler): void
    {
        $this->notFoundHandler = $handler;
    }

    private function add(string $method, string $path, array|callable $handler): void
    {
        $this->routes[] = [
            'method'   => $method,
            'segments' => self::segments($path),
            'handler'  => $handler,
        ];
    }

    /**
     * Path of the current request, without the query string and without the
     * install folder. "/shop/produit/robe/?x=1" becomes "/produit/robe".
     */
    public static function path(): string
    {
        // Normal mode: the page arrives in the query string, as "/?p=/panier".
        // This is what url() builds when 'pretty_urls' is false, because it
        // works on every host with no rewrite rule and no .htaccess at all.
        $target = $_GET['p'] ?? null;

        if (is_string($target) && $target !== '') {
            $path = '/' . trim(rawurldecode($target), '/');

            return $path === '/' ? '/' : rtrim($path, '/');
        }

        // Clean URLs: the page is in the path, "/produit/robe".
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = is_string($path) ? rawurldecode($path) : '/';

        $base = base_path(); // "" at the domain root, "/shop" inside a folder

        if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) {
            $path = substr($path, strlen($base));
        }

        // "/index.php" and "/index.php/produits" mean the home page and
        // "/produits": the file name is never part of a page address.
        if (str_ends_with($path, '/index.php')) {
            $path = substr($path, 0, -strlen('/index.php')) . '/';
        }

        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    /**
     * Find the route matching the current request and call it.
     */
    public function dispatch(): void
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $path   = self::path();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $arguments = self::match($route['segments'], $path);

            if ($arguments === null) {
                continue;
            }

            self::run($route['handler'], $arguments);

            return;
        }

        $this->runNotFound();
    }

    /**
     * Compare one route with one path, segment by segment.
     *
     * @param array<int, string> $route
     * @return array<int, string>|null Arguments, or null when it does not match.
     */
    private static function match(array $route, string $path): ?array
    {
        $path = trim($path, '/');
        $given = $path === '' ? [] : explode('/', $path);

        if (count($given) !== count($route)) {
            return null;
        }

        $arguments = [];

        foreach ($route as $index => $segment) {
            if (preg_match('/^\{[a-zA-Z_][a-zA-Z0-9_]*\}$/', $segment) === 1) {
                $arguments[] = $given[$index];

                continue;
            }

            if ($segment !== $given[$index]) {
                return null;
            }
        }

        return $arguments;
    }

    /**
     * @return array<int, string>
     */
    private static function segments(string $path): array
    {
        $path = trim($path, '/');

        return $path === '' ? [] : explode('/', $path);
    }

    /**
     * @param array|callable $handler
     * @param array<int, string> $arguments
     */
    private static function run(array|callable $handler, array $arguments): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;

            (new $class())->{$method}(...$arguments);

            return;
        }

        $handler(...$arguments);
    }

    private function runNotFound(): void
    {
        if ($this->notFoundHandler !== null) {
            self::run($this->notFoundHandler, []);

            return;
        }

        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo '404 - Page introuvable';
    }
}
