<?php
// BACKOFFICE - Détail Signalement
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
    // `Database` class may not exist in this codebase; use central `config::getConnexion()` instead
    $db = config::getConnexion();
}

if (!isset($_GET['id'])) {
    header('Location: ../index.php');
    exit();
}

$signalementController = new SignalementController($db);
$signalement = $signalementController->getSignalementById($_GET['id']);

if (!$signalement) {
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail Signalement - Admin SAFEProject</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
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
                <a href="../index.php" class="btn btn-secondary btn-sm">← Retour au Dashboard</a>
                <a href="supprimer_signalement.php?id=<?= $signalement['id'] ?>" 
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Supprimer ce signalement ?')">
                    <i class="fas fa-trash"></i> Supprimer (Admin)
                </a>
            </div>
        </div>
    </div>
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../js/sb-admin-2.min.js"></script>
</body>
</html>