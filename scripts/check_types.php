<?php
// Script rapide pour lister les types via SignalementController
$rootPath = dirname(__DIR__);
require_once $rootPath . DIRECTORY_SEPARATOR . 'config.php';
require_once $rootPath . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'Type.php';
require_once $rootPath . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'Signalement.php';
require_once $rootPath . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'TypeController.php';
require_once $rootPath . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'SignalementController.php';

// Connexion
if (!isset($db) || !$db) {
    $database = new Database();
    $db = $database->getConnection();
}

$signalementController = new SignalementController($db);
$types = $signalementController->getTypesForForm();

header('Content-Type: text/plain');
if (empty($types)) {
    echo "Aucun type trouvé\n";
} else {
    foreach ($types as $t) {
        echo "ID: {$t['id']} - NOM: {$t['nom']}" . PHP_EOL;
        if (isset($t['description'])) echo "  Desc: {$t['description']}" . PHP_EOL;
    }
}
