<?php

declare(strict_types=1);

use App\Core\Env;

/**
 * Database configuration (read by App\Core\Database).
 */
return [
    'db_host'    => Env::get('DB_HOST', 'localhost'),
    'db_port'    => Env::int('DB_PORT', 3306),
    'db_name'    => Env::get('DB_NAME', 'shoopmoda'),
    'db_user'    => Env::get('DB_USER', 'root'),
    'db_pass'    => Env::get('DB_PASS', ''),
    'db_charset' => Env::get('DB_CHARSET', 'utf8mb4'),
];
