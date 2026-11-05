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
 */
final class Database
{
    private static ?PDO $connection = null;

    public static function pdo(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            (string) config('db_host'),
            (int) config('db_port'),
            (string) config('db_name'),
            (string) config('db_charset')
        );

        try {
            self::$connection = new PDO(
                $dsn,
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
