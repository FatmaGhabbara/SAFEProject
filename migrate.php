<?php
require_once __DIR__ . '/config.php';

// Simple migration script to execute .sql files found in SQL/ directory
try {
    $db = config::getConnexion();

    $sqlDir = __DIR__ . '/SQL';
    $files = array_filter(scandir($sqlDir), function ($f) {
        return is_file(__DIR__ . '/SQL/' . $f) && pathinfo($f, PATHINFO_EXTENSION) === 'sql';
    });

    if (empty($files)) {
        echo "No SQL files found in SQL/\n";
        exit(0);
    }

    foreach ($files as $file) {
        $path = $sqlDir . DIRECTORY_SEPARATOR . $file;
        echo "Applying $file...\n";
        $sql = file_get_contents($path);
        if ($sql === false) {
            echo "Failed to read $file\n";
            continue;
        }

        // Execute the SQL; some dumps may contain multiple statements
        try {
            $db->exec($sql);
            echo "$file applied successfully.\n";
        } catch (PDOException $e) {
            echo "Error applying $file: " . $e->getMessage() . "\n";
        }
    }

    echo "Done. Verify your database 'safespace'.\n";
} catch (Exception $e) {
    echo 'Migration failed: ' . $e->getMessage() . "\n";
}
