<?php
// FRONTOFFICE - Recherche AJAX
$root = $_SERVER['DOCUMENT_ROOT'] . '/fedi/SAFEProject/';
include_once $root . 'config.php';
include_once $root . 'model/Signalement.php';
include_once $root . 'model/Type.php';
include_once $root . 'controller/TypeController.php';
include_once $root . 'controller/SignalementController.php';

header('Content-Type: application/json');

$signalementController = new SignalementController($db);

$search = $_GET['search'] ?? '';
$signalements = [];

if (!empty($search) && strlen($search) >= 2) {
    $signalements = $signalementController->searchSignalements($search);
}

// Générer le HTML des résultats
$html = '';

if (empty($signalements)) {
    $html = '
        <div class="empty-state">
            <h3>🔍 Aucun résultat trouvé</h3>
            <p>Essayez avec d\'autres termes de recherche.</p>
            <a href="ajouter_signalement.php" class="btn">Créer un signalement</a>
        </div>
    ';
} else {
    foreach ($signalements as $signalement) {
        $html .= '
            <div class="signalement-card">
                <div class="signalement-header">
                    <h3 class="signalement-title">' . htmlspecialchars($signalement['titre']) . '</h3>
                    <span class="signalement-type">' . htmlspecialchars($signalement['type_nom']) . '</span>
                </div>
                
                <div class="signalement-date">
                    📅 ' . date('d/m/Y à H:i', strtotime($signalement['created_at'])) . '
                </div>

                <p>' . nl2br(htmlspecialchars(substr($signalement['description'], 0, 150))) . '...</p>

                <div class="signalement-actions">
                    <a href="detail_signalement.php?id=' . $signalement['id'] . '" class="action-link">
                        👁️ Voir détails
                    </a>
                    <a href="supprimer_signalement.php?id=' . $signalement['id'] . '" 
                       class="action-link delete-link" 
                       onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer ce signalement ?\')">
                        🗑️ Supprimer
                    </a>
                </div>
            </div>
        ';
    }
}

echo json_encode([
    'count' => count($signalements),
    'html' => $html
]);
?>