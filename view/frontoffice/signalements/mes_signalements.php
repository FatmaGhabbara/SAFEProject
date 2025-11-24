<?php
// FRONTOFFICE - Liste des signalements pour les membres
$root = $_SERVER['DOCUMENT_ROOT'] . '/fedi/SAFEProject/';
include_once $root . 'config.php';
include_once $root . 'model/Signalement.php';
include_once $root . 'model/Type.php';
include_once $root . 'controller/TypeController.php';
include_once $root . 'controller/SignalementController.php';

$signalementController = new SignalementController($db);

// Chargement initial des signalements
$signalements = $signalementController->getAllSignalements();

// Message de succès après suppression
$message_suppression = '';
if (isset($_GET['message']) && $_GET['message'] === 'supprime') {
    $message_suppression = '✅ Signalement supprimé avec succès !';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Signalements - SAFEProject</title>
    <style>
        .container { 
            max-width: 1000px; 
            margin: 50px auto; 
            padding: 20px; 
        }
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px; 
        }
        .search-bar { 
            margin-bottom: 30px; 
            display: flex; 
            gap: 10px;
            position: relative;
        }
        .search-input { 
            flex: 1; 
            padding: 12px; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            font-size: 1em;
        }
        .search-btn { 
            background: #007bff; 
            color: white; 
            padding: 12px 25px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer;
            font-size: 1em;
        }
        .loading {
            display: none;
            position: absolute;
            right: 130px;
            top: 50%;
            transform: translateY(-50%);
            color: #007bff;
        }
        .btn { 
            background: #28a745; 
            color: white; 
            padding: 12px 25px; 
            text-decoration: none; 
            border-radius: 8px; 
            display: inline-block;
            font-size: 1em;
            border: none;
            cursor: pointer;
        }
        .signalement-card { 
            border: 1px solid #e0e0e0; 
            border-radius: 10px; 
            padding: 25px; 
            margin-bottom: 20px; 
            background: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .signalement-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: start; 
            margin-bottom: 15px; 
        }
        .signalement-title { 
            font-size: 1.3em; 
            font-weight: bold; 
            color: #2c3e50; 
            margin: 0;
        }
        .signalement-type { 
            background: #007bff; 
            color: white; 
            padding: 5px 12px; 
            border-radius: 20px; 
            font-size: 0.8em;
        }
        .signalement-date { 
            color: #7f8c8d; 
            font-size: 0.9em; 
            margin: 8px 0;
        }
        .signalement-actions { 
            margin-top: 20px; 
            display: flex; 
            gap: 15px;
        }
        .action-link { 
            color: #007bff; 
            text-decoration: none; 
            font-size: 0.9em;
        }
        .delete-link { color: #dc3545; }
        .empty-state { 
            text-align: center; 
            padding: 60px 40px; 
            color: #7f8c8d;
        }
        .results-info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Mes Signalements</h1>
            <a href="ajouter_signalement.php" class="btn">➕ Nouveau Signalement</a>
        </div>

        <?php if ($message_suppression): ?>
            <div class="success-message">
                <?= $message_suppression ?>
            </div>
        <?php endif; ?>

        <!-- BARRE DE RECHERCHE TEMPS RÉEL -->
        <div class="search-bar">
            <input type="text" 
                   id="searchInput" 
                   class="search-input" 
                   placeholder="🔍 Rechercher en temps réel..." 
                   onkeyup="searchSignalements()">
            <span class="loading" id="loadingIndicator">⏳ Recherche...</span>
            <button class="search-btn" onclick="searchSignalements()">Rechercher</button>
        </div>

        <div id="resultsInfo" class="results-info" style="display: none;">
            <strong>🔍 Résultats en temps réel :</strong> 
            <span id="searchTerm"></span>
            <span id="resultsCount" style="color: #666; margin-left: 10px;"></span>
        </div>

        <div id="signalementsContainer">
            <?php if (empty($signalements)): ?>
                <div class="empty-state">
                    <h3>📭 Aucun signalement pour le moment</h3>
                    <p>Soyez le premier à ajouter un signalement !</p>
                    <a href="ajouter_signalement.php" class="btn">Créer un signalement</a>
                </div>
            <?php else: ?>
                <?php foreach ($signalements as $signalement): ?>
                    <div class="signalement-card">
                        <div class="signalement-header">
                            <h3 class="signalement-title"><?= htmlspecialchars($signalement['titre']) ?></h3>
                            <span class="signalement-type"><?= htmlspecialchars($signalement['type_nom']) ?></span>
                        </div>
                        
                        <div class="signalement-date">
                            📅 <?= date('d/m/Y à H:i', strtotime($signalement['created_at'])) ?>
                        </div>

                        <p><?= nl2br(htmlspecialchars(substr($signalement['description'], 0, 150))) ?>...</p>

                        <div class="signalement-actions">
                            <a href="detail_signalement.php?id=<?= $signalement['id'] ?>" class="action-link">
                                👁️ Voir détails
                            </a>
                            <a href="supprimer_signalement.php?id=<?= $signalement['id'] ?>" 
                               class="action-link delete-link" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce signalement ?')">
                                🗑️ Supprimer
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <script>
        function searchSignalements() {
            const searchTerm = document.getElementById('searchInput').value;
            const loadingIndicator = document.getElementById('loadingIndicator');
            const resultsInfo = document.getElementById('resultsInfo');
            const searchTermSpan = document.getElementById('searchTerm');
            const resultsCount = document.getElementById('resultsCount');
            
            if (searchTerm.length === 0) {
                // Si recherche vide, recharger la page pour tout afficher
                window.location.href = 'mes_signalements.php';
                return;
            }
            
            if (searchTerm.length < 2) {
                return; // Attendre au moins 2 caractères
            }
            
            loadingIndicator.style.display = 'block';
            
            // Requête AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'recherche_ajax.php?search=' + encodeURIComponent(searchTerm), true);
            
            xhr.onload = function() {
                loadingIndicator.style.display = 'none';
                
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    
                    // Afficher les informations de recherche
                    searchTermSpan.textContent = '"' + searchTerm + '"';
                    resultsCount.textContent = '(' + response.count + ' résultat(s))';
                    resultsInfo.style.display = 'block';
                    
                    // Mettre à jour l'affichage
                    document.getElementById('signalementsContainer').innerHTML = response.html;
                }
            };
            
            xhr.send();
        }

        // Recherche automatique après 500ms sans frappe
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(searchSignalements, 500);
        });
        </script>
    </div>
</body>
</html>