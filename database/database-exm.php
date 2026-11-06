<?php
/**
 * FILE: config/database.php
 * PURPOSE: Establish secure PDO connection to MySQL database.
 * VARIABLE: $db_connection — the global PDO object used everywhere.
 * SECURITY: Uses prepared statements, disables emulated prepares.
 * NOTE: Update credentials before deploying to production.
 */

/* ----------------------------------------------------------------
 * DATABASE CREDENTIALS
 * In production, consider moving these to environment variables
 * or a file outside the webroot.
 * ---------------------------------------------------------------- */
$db_host     = 'localhost';          // Database server hostname
$db_name     = 'matjfvbp_matjar_el_kotobia_db'; // Database name (permanent)
$db_user     = 'matjfvbp_matjar';              // DB username — CHANGE IN PRODUCTION
$db_password = 'matjar-db-pswd';                   // DB password — CHANGE IN PRODUCTION
$db_charset  = 'utf8mb4';           // Full Unicode support (Arabic)

/* ----------------------------------------------------------------
 * CREATE PDO CONNECTION
 * ---------------------------------------------------------------- */
try {
    // Build DSN (Data Source Name) string
    $db_dsn = "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}";

    // PDO options for security and convenience
    $db_options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Throw on error
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                    // Real prepared stmts
        PDO::ATTR_PERSISTENT         => false,                    // No persistent conn
    ];

    // Create PDO instance
    $db_connection = new PDO($db_dsn, $db_user, $db_password, $db_options);

} catch (PDOException $e) {
    /* ----------------------------------------------------------------
     * IMPORTANT: Never expose database errors to the end user.
     * Log the error internally and show a generic message.
     * ---------------------------------------------------------------- */
    error_log('[HRI_DB_ERROR] ' . date('Y-m-d H:i:s') . ' — ' . $e->getMessage());
    die('Service temporarily unavailable. Please try again later.');
}
