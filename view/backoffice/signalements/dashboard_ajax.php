<?php
// BACKOFFICE - Recherche AJAX pour dashboard
// Détection automatique du chemin vers la racine
$rootPath = dirname(dirname(dirname(__DIR__)));
$configPath = $rootPath . DIRECTORY_SEPARATOR . 'config.php';

// Si config.php n'est pas trouvé, essayer un niveau au-dessus
if (!file_exists($configPath)) {
    $rootPath = dirname($rootPath);
    $configPath = $rootPath . DIRECTORY_SEPARATOR . 'config.php';
}

require_once $configPath;

// Chemins vers model et controller (utiliser $rootPath déjà calculé)
$modelPath = $rootPath . DIRECTORY_SEPARATOR . 'model';
$controllerPath = $rootPath . DIRECTORY_SEPARATOR . 'controller';

// Si model n'existe pas à cet endroit, essayer un niveau au-dessus
if (!is_dir($modelPath)) {
    $rootPath = dirname($rootPath);
    $modelPath = $rootPath . DIRECTORY_SEPARATOR . 'model';
    $controllerPath = $rootPath . DIRECTORY_SEPARATOR . 'controller';
}

require_once $modelPath . DIRECTORY_SEPARATOR . 'Signalement.php';
require_once $modelPath . DIRECTORY_SEPARATOR . 'Type.php';
require_once $controllerPath . DIRECTORY_SEPARATOR . 'TypeController.php';
require_once $controllerPath . DIRECTORY_SEPARATOR . 'SignalementController.php';

// Utiliser la connexion depuis config.php
if (!isset($db) || !$db) {
    $database = new Database();
    $db = $database->getConnection();
}

header('Content-Type: application/json');

$signalementController = new SignalementController($db);

$search = $_GET['search'] ?? '';
$signalements = [];

if (!empty($search) && strlen($search) >= 2) {
    $signalements = $signalementController->searchSignalements($search);
} else {
    $signalements = $signalementController->getAllSignalements();
}

// Générer le HTML des résultats
$html = '';

if (empty($signalements)) {
    $html = '<p class="text-muted font-italic">Aucun signalement trouvé.</p>';
} else {
    foreach ($signalements as $signalement) {
        $html .= '
            <div class="signalement-item">
                <div style="flex: 1;">
                    <strong>' . htmlspecialchars($signalement['titre']) . '</strong><br>
                    <small class="text-muted">
                        Type: ' . htmlspecialchars($signalement['type_nom']) . ' | 
                        Date: ' . date('d/m/Y H:i', strtotime($signalement['created_at'])) . '
                    </small>
                </div>
                <a href="detail_signalement.php?id=' . $signalement['id'] . '" class="btn btn-primary btn-sm" title="Voir détails">
                    👁️
                </a>
            </div>
        ';
    }
}

echo json_encode([
    'count' => count($signalements),
    'html' => $html
]);
?>