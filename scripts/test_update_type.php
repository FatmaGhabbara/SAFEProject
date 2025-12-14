<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/model/Type.php';
require_once dirname(__DIR__) . '/controller/TypeController.php';

if (!isset($db) || !$db) {
    $database = new Database();
    $db = $database->getConnection();
}

$typeController = new TypeController($db);
$types = $typeController->getAllTypes();
if (empty($types)) {
    echo "No types to update" . PHP_EOL;
    exit;
}

$first = $types[0];
echo "Before: "; var_export($first); echo PHP_EOL;

$res = $typeController->updateType($first['id'], $first['nom'], 'Updated description from test script');
echo "Update result: "; var_export($res); echo PHP_EOL;

$after = $typeController->getTypeById($first['id']);
echo "After: "; var_export($after); echo PHP_EOL;

?>
