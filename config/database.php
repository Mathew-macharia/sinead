<?php
/**
 * Database Configuration and Connection Manager
 * 
 * Implements the Singleton design pattern to ensure only one
 * database connection instance exists throughout the application lifecycle.
 * Uses PDO (PHP Data Objects) for database abstraction and security.
 * 
 * @package    Sinead
 * @subpackage Config
 * @author     Sinead Development Team
 * @version    1.0.0
 */

class Database
{
    /** @var string Database host address */
    private static $host = '127.0.0.1';
    private static $port = 3307;

    /** @var string Database name */
    private static $dbName = 'sinead_hotel';

    /** @var string Database username */
    private static $username = 'root';

    /** @var string Database password */
    private static $password = '';

    /** @var string Character set for the connection */
    private static $charset = 'utf8mb4';

    /** @var PDO|null Singleton PDO instance */
    private static $instance = null;

    /**
     * Private constructor to prevent direct instantiation.
     * Enforces the Singleton pattern.
     */
    private function __construct() {}

    /**
     * Prevent cloning of the Singleton instance.
     */
    private function __clone() {}

    /**
     * Get the singleton database connection instance.
     * 
     * Creates a new PDO connection if one doesn't exist,
     * otherwise returns the existing connection.
     * 
     * PDO Configuration:
     * - ERRMODE_EXCEPTION: Throws exceptions on database errors
     * - FETCH_ASSOC: Returns associative arrays by default
     * - EMULATE_PREPARES disabled: Uses native prepared statements for security
     * 
     * @return PDO The database connection instance
     * @throws PDOException If the connection fails
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                self::$host,
                self::$port,
                self::$dbName,
                self::$charset
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                // Fix for PHP 8.4+ deprecation warning
                (defined('Pdo\Mysql::ATTR_INIT_COMMAND') ? \Pdo\Mysql::ATTR_INIT_COMMAND : 1002) => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$instance = new PDO($dsn, self::$username, self::$password, $options);
            } catch (PDOException $e) {
                error_log('Database connection failed: ' . $e->getMessage());
                throw new PDOException(
                    'DB Error: ' . $e->getMessage(),
                    (int) $e->getCode()
                );
            }
        }

        return self::$instance;
    }

    /**
     * Close the database connection.
     * Useful for long-running scripts or explicit resource cleanup.
     * 
     * @return void
     */
    public static function close(): void
    {
        self::$instance = null;
    }
}
