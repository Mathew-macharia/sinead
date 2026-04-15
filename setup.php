<?php
/**
 * SINEAD Setup Helper
 * 
 * Run this script once to:
 *   1. Generate a valid bcrypt hash for the default password
 *   2. Display the SQL needed to update seed users
 *   3. Verify database connectivity
 * 
 * Usage: php setup.php
 * 
 * @package    Sinead
 * @version    1.0.0
 */

echo "========================================\n";
echo " SINEAD Hotel Management System Setup\n";
echo "========================================\n\n";

// Step 1: Generate default password hash
$defaultPassword = 'sinead2024';
$hash = password_hash($defaultPassword, PASSWORD_BCRYPT, ['cost' => 12]);

echo "1. Default Password Hash\n";
echo "   Password: {$defaultPassword}\n";
echo "   Bcrypt Hash: {$hash}\n\n";

echo "   UPDATE SQL (run in MySQL Workbench if seed data hashes don't work):\n";
echo "   UPDATE users SET password_hash = '{$hash}' WHERE username IN ('admin','receptionist','housekeeper');\n\n";

// Step 2: Test database connection
echo "2. Testing Database Connection...\n";
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance();
    echo "   SUCCESS: Connected to database '{$db->query('SELECT DATABASE()')->fetchColumn()}'.\n\n";

    // Check tables
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "3. Database Tables Found: " . count($tables) . "\n";
    foreach ($tables as $table) {
        $count = $db->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
        echo "   - {$table}: {$count} rows\n";
    }
    echo "\n";

    // Test login
    $user = $db->query("SELECT username, password_hash FROM users WHERE username = 'admin' LIMIT 1")->fetch();
    if ($user) {
        $verified = password_verify($defaultPassword, $user['password_hash']);
        echo "4. Login Test (admin/{$defaultPassword}): " . ($verified ? "PASS" : "FAIL") . "\n";
        if (!$verified) {
            echo "   Updating hash...\n";
            $db->prepare("UPDATE users SET password_hash = :hash WHERE username = 'admin'")
               ->execute([':hash' => $hash]);
            $db->prepare("UPDATE users SET password_hash = :hash WHERE username = 'receptionist'")
               ->execute([':hash' => $hash]);
            $db->prepare("UPDATE users SET password_hash = :hash WHERE username = 'housekeeper'")
               ->execute([':hash' => $hash]);
            echo "   All user passwords updated to '{$defaultPassword}'.\n";
        }
    }

} catch (PDOException $e) {
    echo "   FAILED: " . $e->getMessage() . "\n";
    echo "   Make sure:\n";
    echo "   - MySQL is running\n";
    echo "   - You've run database/schema.sql in MySQL Workbench\n";
    echo "   - The credentials in config/database.php are correct\n";
}

echo "\n========================================\n";
echo " Setup Complete\n";
echo "========================================\n";
echo "\nTo start the server:\n";
echo "  php -S localhost:8000\n\n";
echo "Then open http://localhost:8000 in your browser.\n\n";
