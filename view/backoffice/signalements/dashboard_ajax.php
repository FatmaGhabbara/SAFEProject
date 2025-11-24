<?php
// BACKOFFICE - Recherche AJAX pour dashboard
$root = $_SERVER['DOCUMENT_ROOT'] . '/fedi/SAFEProject/';
include_once $root . 'config.php';
include_once $root . 'model/Signalement.php';
include_once $root . 'model/Type.php';
include_once $root . 'controller/SignalementController.php';
include_once $root . 'controller/TypeController.php';

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
    $html = '<p style="color: #666; font-style: italic;">Aucun signalement trouvé.</p>';
} else {
    foreach ($signalements as $signalement) {
        $html .= '
            <div class="signalement-item">
                <div style="flex: 1;">
                    <strong>' . htmlspecialchars($signalement['titre']) . '</strong><br>
                    <small style="color: #666;">
                        Type: ' . htmlspecialchars($signalement['type_nom']) . ' | 
                        Date: ' . date('d/m/Y H:i', strtotime($signalement['created_at'])) . '
                    </small>
                </div>
                <a href="detail_signalement.php?id=' . $signalement['id'] . '" class="btn" title="Voir détails">
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