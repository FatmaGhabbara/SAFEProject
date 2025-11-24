<?php
// BACKOFFICE - Dashboard Admin
$root = $_SERVER['DOCUMENT_ROOT'] . '/fedi/SAFEProject/';
include_once $root . 'config.php';
include_once $root . 'model/Signalement.php';
include_once $root . 'model/Type.php';
include_once $root . 'controller/SignalementController.php';
include_once $root . 'controller/TypeController.php';

$signalementController = new SignalementController($db);
$typeController = new TypeController($db);

$message = '';

// AJOUTER UN TYPE
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'add_type') {
    if (!empty($_POST['nom'])) {
        $query = "INSERT INTO types (nom) VALUES (:nom)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nom', $_POST['nom']);
        if ($stmt->execute()) {
            $message = "✅ Type ajouté avec succès !";
        } else {
            $message = "❌ Erreur lors de l'ajout du type";
        }
    }
}

// SUPPRIMER UN TYPE
if (isset($_GET['delete_type'])) {
    $query = "DELETE FROM types WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['delete_type']);
    if ($stmt->execute()) {
        $message = "✅ Type supprimé avec succès !";
    }
}

// Chargement initial
$signalements = $signalementController->getAllSignalements();
$types = $typeController->getAllTypes();
$totalSignalements = count($signalements);
$totalTypes = count($types);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SAFEProject</title>
    <style>
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { 
            background: #2c3e50; 
            color: white; 
            padding: 20px; 
            border-radius: 8px; 
            margin-bottom: 30px; 
        }
        .stats { 
            display: flex; 
            gap: 20px; 
            margin-bottom: 30px; 
        }
        .stat-card { 
            background: #f8f9fa; 
            padding: 20px; 
            border-radius: 8px; 
            flex: 1; 
            text-align: center; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .sections { 
            display: flex; 
            gap: 30px; 
        }
        .section { 
            flex: 1; 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
        }
        .message { 
            padding: 10px; 
            margin-bottom: 20px; 
            border-radius: 5px; 
        }
        .success { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb; 
        }
        .form-group { 
            margin-bottom: 15px; 
        }
        input, select, textarea { 
            width: 100%; 
            padding: 8px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            box-sizing: border-box;
        }
        .btn { 
            background: #007bff; 
            color: white; 
            padding: 10px 15px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            text-decoration: none;
            display: inline-block;
            font-size: 0.9em;
        }
        .btn-danger { 
            background: #dc3545; 
        }
        .btn-success { 
            background: #28a745; 
        }
        .btn-secondary {
            background: #6c757d;
        }
        .type-list, .signalement-list { 
            margin-top: 15px; 
        }
        .type-item, .signalement-item { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            padding: 10px; 
            border-bottom: 1px solid #eee; 
        }
        .search-container {
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
            position: relative;
        }
        .search-input {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .loading {
            display: none;
            position: absolute;
            right: 100px;
            top: 50%;
            transform: translateY(-50%);
            color: #007bff;
            font-size: 0.8em;
        }
        .results-info {
            background: #e7f3ff;
            padding: 8px 12px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 0.9em;
        }
        .admin-badge {
            background: #dc3545;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.7em;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- EN-TÊTE -->
        <div class="header">
            <h1>🛠️ Dashboard Administrateur - SAFEProject</h1>
            <p>Gestion des types de signalements et supervision</p>
        </div>

        <!-- MESSAGE DE SUCCÈS/ERREUR -->
        <?php if ($message): ?>
            <div class="message success"><?= $message ?></div>
        <?php endif; ?>

        <!-- STATISTIQUES -->
        <div class="stats">
            <div class="stat-card">
                <h3>📈 Total Signalements</h3>
                <p style="font-size: 2em; margin: 10px 0;"><?= $totalSignalements ?></p>
                <small>Tous les signalements confondus</small>
            </div>
            <div class="stat-card">
                <h3>🏷️ Types Disponibles</h3>
                <p style="font-size: 2em; margin: 10px 0;"><?= $totalTypes ?></p>
                <small>Catégories de signalements</small>
            </div>
            <div class="stat-card">
                <h3>⚡ Recherche</h3>
                <p style="font-size: 2em; margin: 10px 0;">🔍</p>
                <small>Temps réel active</small>
            </div>
        </div>

        <div class="sections">
            <!-- SECTION GESTION DES TYPES -->
            <div class="section">
                <h2>🏷️ Gestion des Types</h2>
                
                <form method="POST">
                    <input type="hidden" name="action" value="add_type">
                    <div class="form-group">
                        <label><strong>Nouveau type :</strong></label>
                        <input type="text" name="nom" placeholder="Ex: Problème technique, Bug, Suggestion..." required>
                    </div>
                    <button type="submit" class="btn btn-success">➕ Ajouter Type</button>
                </form>

                <div class="type-list">
                    <h3>Types existants :</h3>
                    <?php if (empty($types)): ?>
                        <p style="color: #666; font-style: italic;">Aucun type créé.</p>
                    <?php else: ?>
                        <?php foreach ($types as $type): ?>
                            <div class="type-item">
                                <span>
                                    <?= htmlspecialchars($type['nom']) ?> 
                                    <span style="color: #666; font-size: 0.8em;">(ID: <?= $type['id'] ?>)</span>
                                </span>
                                <a href="?delete_type=<?= $type['id'] ?>" class="btn btn-danger" 
                                   onclick="return confirm('Supprimer le type \"<?= $type['nom'] ?>\" ?')"
                                   style="padding: 5px 10px; font-size: 0.8em;">
                                    🗑️
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SECTION SIGNALEMENTS -->
            <div class="section">
                <h2>📋 Signalements <span class="admin-badge">ADMIN</span></h2>
                
                <!-- BARRE DE RECHERCHE TEMPS RÉEL -->
                <div class="search-container">
                    <input type="text" 
                           id="searchInput" 
                           class="search-input" 
                           placeholder="🔍 Recherche temps réel...">
                    <span class="loading" id="loadingIndicator">⏳ Recherche...</span>
                    <button type="button" class="btn" onclick="searchSignalements()">Rechercher</button>
                </div>

                <div id="resultsInfo" class="results-info" style="display: none;">
                    <strong>🔍 Résultats en temps réel :</strong> 
                    <span id="searchTerm"></span>
                    <span id="resultsCount" style="color: #666; margin-left: 10px;"></span>
                </div>

                <div id="signalementsContainer" class="signalement-list">
                    <?php if (empty($signalements)): ?>
                        <p style="color: #666; font-style: italic;">Aucun signalement pour le moment.</p>
                    <?php else: ?>
                        <?php foreach (array_slice($signalements, 0, 10) as $signalement): ?>
                            <div class="signalement-item">
                                <div style="flex: 1;">
                                    <strong><?= htmlspecialchars($signalement['titre']) ?></strong><br>
                                    <small style="color: #666;">
                                        Type: <?= htmlspecialchars($signalement['type_nom']) ?> | 
                                        Date: <?= date('d/m/Y H:i', strtotime($signalement['created_at'])) ?>
                                    </small>
                                </div>
                                <a href="detail_signalement.php?id=<?= $signalement['id'] ?>" class="btn" title="Voir détails">
                                    👁️
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if (count($signalements) > 10): ?>
                    <p style="text-align: center; margin-top: 15px; color: #666;">
                        <em>+ <?= count($signalements) - 10 ?> autres signalements...</em>
                    </p>
                <?php endif; ?>
            </div>
        </div>
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
            window.location.href = 'dashboard.php';
            return;
        }
        
        if (searchTerm.length < 2) {
            return; // Attendre au moins 2 caractères
        }
        
        loadingIndicator.style.display = 'block';
        
        // Requête AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'dashboard_ajax.php?search=' + encodeURIComponent(searchTerm), true);
        
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
                
                // Mettre à jour le compteur de statistiques
                document.querySelector('.stat-card:nth-child(1) p').textContent = response.count;
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
</body>
</html>