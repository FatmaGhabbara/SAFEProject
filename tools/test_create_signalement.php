<?php
// Script de test local pour simuler la création d'un signalement via la couche controller
// Usage (cli): php tools/test_create_signalement.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controller/SignalementController.php';
require_once __DIR__ . '/../model/Signalement.php';
require_once __DIR__ . '/../controller/TypeController.php';
require_once __DIR__ . '/../model/Type.php';

$database = new Database();
$db = $database->getConnection();
$controller = new SignalementController($db);

// Example payload (modify as needed)
$payload = [
    'titre' => 'Test création via script',
    'type_id' => 6,
    'description' => 'Ceci est un test de signalement.'
];

$result = $controller->createSignalement($payload);

echo "Result: \n";
print_r($result);

if (!$result['success']) {
    // Try to pull last SQL error from the model
    $reflection = new ReflectionClass($controller);
    $prop = $reflection->getProperty('signalement');
    $prop->setAccessible(true);
    $signalementModel = $prop->getValue($controller);
    if (method_exists($signalementModel, 'getLastError')) {
        echo "Model last error: \n";
        print_r($signalementModel->getLastError());
    }
}

?>