<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/controller/SignalementController.php';
require_once dirname(__DIR__) . '/model/Signalement.php';

if (!isset($db) || !$db) {
    $database = new Database();
    $db = $database->getConnection();
}

$sc = new SignalementController($db);
$items = $sc->getAllSignalements();
if (empty($items)) {
    echo "No signalements to delete\n";
    exit;
}

$id = $items[0]['id'];
echo "Deleting id: $id\n";
$res = $sc->deleteSignalement($id);
var_export($res);
echo PHP_EOL;
