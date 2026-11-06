<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

/**
 * Lazy PDO singleton.
 *
 * - Prepared statements only (emulation disabled).
 * - Exceptions on error, associative fetch by default.
 * - No persistent connections: shared hosting kills them abruptly.
 *
 * The MySQL host is used EXACTLY as configured in config.php. Shared hosts
 * (InfinityFree and others) expose the database on a domain name such as
 * sql123.infinityfree.com: rewriting it to 127.0.0.1 breaks the connection.
 */
final class Database
{
    private static ?PDO $connection = null;

    /**
     * MySQL DSN built from config.php (also displayed by check.php).
     */
    public static function dsn(): string
    {
        [$host, $port] = self::endpoint();

        $charset = trim((string) config('db_charset'));

        if ($charset === '') {
            $charset = 'utf8mb4';
        }

        return sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $host,
            $port,
            (string) config('db_name'),
            $charset
        );
    }

    /**
     * Host and port, straight from the configuration.
     *
     * Accepts a domain name, an IPv4 address or a "host:port" value copied
     * from the hosting panel. No lookup, no substitution.
     *
     * @return array{0: string, 1: int}
     */
    private static function endpoint(): array
    {
        $host = trim((string) config('db_host'), " \t\n\r\0\x0B\"'");
        $port = (int) config('db_port');

        // "sql123.infinityfree.com:3306" pasted into db_host.
        if ($host !== '' && $host[0] !== '[' && substr_count($host, ':') === 1) {
            [$hostPart, $portPart] = explode(':', $host, 2);

            if ($portPart !== '' && preg_match('/^\d+$/', $portPart) === 1) {
                $host = $hostPart;
                $port = (int) $portPart;
            }
        }

        if ($host === '') {
            $host = '127.0.0.1';
        }

        if ($port < 1 || $port > 65535) {
            $port = 3306;
        }

        return [$host, $port];
    }

    public static function pdo(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        try {
            self::$connection = new PDO(
                self::dsn(),
                (string) config('db_user'),
                (string) config('db_pass'),
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_STRINGIFY_FETCHES  => false,
                ]
            );
        } catch (PDOException $exception) {
            // Never leak credentials or SQL details to the visitor.
            throw new PDOException('Connexion à la base de données impossible.', 0, $exception);
        }

        return self::$connection;
    }

    /**
     * Run a prepared statement and return every row.
     *
     * @param array<string|int, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    public static function select(string $sql, array $params = []): array
    {
        $statement = self::pdo()->prepare($sql);
        $statement->execute($params);

        $rows = $statement->fetchAll();
        $statement->closeCursor();

        return $rows;
    }

    /**
     * Run a prepared statement and return the first row (or null).
     *
     * @param array<string|int, mixed> $params
     * @return array<string, mixed>|null
     */
    public static function first(string $sql, array $params = []): ?array
    {
        $statement = self::pdo()->prepare($sql);
        $statement->execute($params);

        $row = $statement->fetch();
        $statement->closeCursor();

        return $row === false ? null : $row;
    }

    /**
     * Run a prepared write statement and return the affected row count.
     *
     * @param array<string|int, mixed> $params
     */
    public static function execute(string $sql, array $params = []): int
    {
        $statement = self::pdo()->prepare($sql);
        $statement->execute($params);

        return $statement->rowCount();
    }

}
