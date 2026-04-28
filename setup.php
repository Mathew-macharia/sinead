<?php
/**
 * SINEAD Setup Helper
 *
 * Run this script once from the command line to verify your installation:
 *   1. Generate a valid bcrypt hash for the default password
 *   2. Test database connectivity
 *   3. Confirm all expected tables exist and show row counts
 *   4. Confirm all stored routines (functions, procedures, triggers) are installed
 *   5. Test that the admin login works
 *
 * Usage:
 *   php setup.php
 *
 * NOTE: This script only verifies the setup — it does NOT run schema.sql.
 * Stored routines use DELIMITER $$ syntax which PDO cannot execute.
 * Run schema.sql via MySQL CLI or phpMyAdmin first:
 *   mysql -u root -p < database/schema.sql
 *
 * @package    Sinead
 * @version    2.0.0
 */

echo "========================================\n";
echo " SINEAD Hotel Management System Setup\n";
echo " v2.0.0\n";
echo "========================================\n\n";

// ─── Step 1: Generate default password hash ───────────────────────────────────
$defaultPassword = 'sinead2024';
$hash = password_hash($defaultPassword, PASSWORD_BCRYPT, ['cost' => 12]);

echo "1. Default Password Hash\n";
echo "   Password : {$defaultPassword}\n";
echo "   Hash     : {$hash}\n\n";
echo "   Run this in MySQL if seed user logins fail:\n";
echo "   UPDATE users SET password_hash = '{$hash}'\n";
echo "   WHERE username IN ('admin', 'receptionist', 'housekeeper');\n\n";

// ─── Step 2: Database connection ─────────────────────────────────────────────
echo "2. Testing Database Connection...\n";

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance();
    $dbName = $db->query('SELECT DATABASE()')->fetchColumn();
    echo "   OK  Connected to '{$dbName}'.\n\n";

    // ─── Step 3: Table check ──────────────────────────────────────────────────
    $expectedTables = [
        'users', 'rooms', 'guests', 'reservations',
        'invoices', 'invoice_items', 'housekeeping_tasks',
        'activity_log', 'password_resets', 'notification_log',
    ];

    $existingTables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $missingTables  = array_diff($expectedTables, $existingTables);

    echo "3. Tables (" . count($existingTables) . " found, " . count($expectedTables) . " expected)\n";
    foreach ($expectedTables as $table) {
        if (in_array($table, $existingTables)) {
            $count = $db->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            echo "   OK  {$table}: {$count} row(s)\n";
        } else {
            echo "   MISSING  {$table}\n";
        }
    }
    if (!empty($missingTables)) {
        echo "\n   Some tables are missing. Run schema.sql via MySQL CLI:\n";
        echo "   mysql -u root -p < database/schema.sql\n";
    }
    echo "\n";

    // ─── Step 4: Stored routines check ───────────────────────────────────────
    // IMPORTANT: Stored routines (functions, procedures, triggers) require
    // DELIMITER $$ syntax which only MySQL CLI / phpMyAdmin understand.
    // If any are missing, run schema.sql via MySQL CLI — NOT via PDO.

    $expectedFunctions = ['fn_nights', 'fn_is_room_available'];
    $expectedProcedures = [
        'sp_check_in', 'sp_check_out',
        'sp_cancel_reservation', 'sp_flag_overdue_reservations',
    ];
    $expectedTriggers = [
        'trg_reservation_status_change',
        'trg_block_room_delete',
    ];

    // Functions
    $installedFunctions = $db->query(
        "SELECT ROUTINE_NAME FROM information_schema.ROUTINES
         WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = 'FUNCTION'"
    )->fetchAll(PDO::FETCH_COLUMN);

    echo "4. Stored Routines\n";
    echo "   Functions:\n";
    foreach ($expectedFunctions as $fn) {
        $ok = in_array($fn, $installedFunctions);
        echo "   " . ($ok ? "OK  " : "MISSING  ") . $fn . "\n";
    }

    // Procedures
    $installedProcedures = $db->query(
        "SELECT ROUTINE_NAME FROM information_schema.ROUTINES
         WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = 'PROCEDURE'"
    )->fetchAll(PDO::FETCH_COLUMN);

    echo "   Procedures:\n";
    foreach ($expectedProcedures as $proc) {
        $ok = in_array($proc, $installedProcedures);
        echo "   " . ($ok ? "OK  " : "MISSING  ") . $proc . "\n";
    }

    // Triggers
    $installedTriggers = $db->query(
        "SELECT TRIGGER_NAME FROM information_schema.TRIGGERS
         WHERE TRIGGER_SCHEMA = DATABASE()"
    )->fetchAll(PDO::FETCH_COLUMN);

    echo "   Triggers:\n";
    foreach ($expectedTriggers as $trg) {
        $ok = in_array($trg, $installedTriggers);
        echo "   " . ($ok ? "OK  " : "MISSING  ") . $trg . "\n";
    }

    $missingRoutines = array_merge(
        array_diff($expectedFunctions,  $installedFunctions),
        array_diff($expectedProcedures, $installedProcedures),
        array_diff($expectedTriggers,   $installedTriggers)
    );
    if (!empty($missingRoutines)) {
        echo "\n   Some routines are missing. They use DELIMITER \$\$ syntax\n";
        echo "   and CANNOT be loaded via PDO. Run schema.sql via MySQL CLI:\n";
        echo "   mysql -u root -p < database/schema.sql\n";
    }
    echo "\n";

    // ─── Step 5: Login test ───────────────────────────────────────────────────
    echo "5. Login Test (admin / {$defaultPassword})\n";
    $user = $db->query(
        "SELECT username, password_hash FROM users WHERE username = 'admin' LIMIT 1"
    )->fetch();

    if ($user) {
        $verified = password_verify($defaultPassword, $user['password_hash']);
        echo "   " . ($verified ? "PASS" : "FAIL") . "\n";

        if (!$verified) {
            echo "   Hash mismatch — auto-updating all seed user passwords...\n";
            $db->prepare(
                "UPDATE users SET password_hash = :hash
                 WHERE username IN ('admin', 'receptionist', 'housekeeper')"
            )->execute([':hash' => $hash]);
            echo "   Done. All passwords reset to '{$defaultPassword}'.\n";
        }
    } else {
        echo "   FAIL — admin user not found. Seed data may not have been inserted.\n";
    }

} catch (PDOException $e) {
    echo "   FAILED: " . $e->getMessage() . "\n\n";
    echo "   Checklist:\n";
    echo "   [ ] MySQL is running\n";
    echo "   [ ] schema.sql was executed via MySQL CLI or phpMyAdmin\n";
    echo "   [ ] Credentials in config/database.php are correct\n";
    echo "   [ ] The database 'sinead_hotel' exists\n";
}

echo "\n========================================\n";
echo " Next step: start the server\n";
echo "   php -S localhost:8000\n";
echo " Then open: http://localhost:8000\n";
echo "========================================\n\n";
