<?php
// Chemin absolu
$root = $_SERVER['DOCUMENT_ROOT'] . '/fedi/SAFEProject/';

// Inclure les fichiers nécessaires
include_once $root . 'config.php';
include_once $root . 'model/Signalement.php';
include_once $root . 'model/Type.php';
include_once $root . 'controller/TypeController.php';
include_once $root . 'controller/SignalementController.php';

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$signalementController = new SignalementController($db);
$signalement = $signalementController->getSignalementById($_GET['id']);

if (!$signalement) {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail Signalement - Admin SAFEProject</title>
    <style>
        .container { max-width: 800px; margin: 50px auto; padding: 20px; }
        .detail-card { 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            padding: 30px; 
            background: #f9f9f9;
        }
        .detail-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: start; 
            margin-bottom: 20px; 
            border-bottom: 1px solid #eee; 
            padding-bottom: 15px;
        }
        .detail-title { 
            font-size: 1.5em; 
            font-weight: bold; 
            color: #333; 
            margin: 0;
        }
        .detail-type { 
            background: #007bff; 
            color: white; 
            padding: 5px 12px; 
            border-radius: 15px; 
            font-size: 0.9em;
        }
        .detail-meta { 
            color: #666; 
            margin-bottom: 20px;
        }
        .detail-description { 
            background: white; 
            padding: 20px; 
            border-radius: 5px; 
            border-left: 4px solid #007bff;
        }
        .action-links { 
            margin-top: 30px; 
            display: flex; 
            gap: 15px;
        }
        .action-link { 
            color: #007bff; 
            text-decoration: none; 
            padding: 8px 15px;
            border: 1px solid #007bff;
            border-radius: 5px;
        }
        .action-link:hover { 
            background: #007bff; 
            color: white;
        }
        .admin-badge {
            background: #dc3545;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="detail-card">
            <div class="detail-header">
                <h1 class="detail-title"><?= htmlspecialchars($signalement['titre']) ?>
                    <span class="admin-badge">VUE ADMIN</span>
                </h1>
                <span class="detail-type"><?= htmlspecialchars($signalement['type_nom']) ?></span>
            </div>

            <div class="detail-meta">
                <strong>📅 Date de création :</strong> 
                <?= date('d/m/Y à H:i', strtotime($signalement['created_at'])) ?>
            </div>

            <div class="detail-description">
                <h3>📄 Description :</h3>
                <p><?= nl2br(htmlspecialchars($signalement['description'])) ?></p>
            </div>

            <div class="action-links">
                <a href="dashboard.php" class="action-link">← Retour au Dashboard</a>
                <a href="supprimer_signalement.php?id=<?= $signalement['id'] ?>" 
                   class="action-link"
                   style="color: #dc3545; border-color: #dc3545;"
                   onclick="return confirm('Supprimer ce signalement ?')">
                    🗑️ Supprimer (Admin)
                </a>
            </div>
        </div>
    </div>
</body>
</html>