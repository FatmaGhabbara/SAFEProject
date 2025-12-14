<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/model/Type.php';
require_once dirname(__DIR__) . '/controller/TypeController.php';

if (!isset($db) || !$db) {
    $database = new Database();
    $db = $database->getConnection();
}

$typeController = new TypeController($db);

// Test insert
$name = 'test_duplicate';
$description = 'Test description for duplicate checks';
$result1 = $typeController->createType($name, $description);
echo "First attempt: "; var_export($result1);
echo PHP_EOL;
$result2 = $typeController->createType($name, $description);
echo "Second attempt: "; var_export($result2);
echo PHP_EOL;
