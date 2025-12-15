<?php
// SUPPRESSION BACKOFFICE - Pour l'admin
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
    // Use central config DB connection
    $db = config::getConnexion();
}

if (!isset($_GET['id'])) {
    header('Location: ../index.php');
    exit();
}

$signalementController = new SignalementController($db);
$signalement_id = $_GET['id'];

// Vérifier si le signalement existe
$signalement = $signalementController->getSignalementById($signalement_id);
if (!$signalement) {
    header('Location: ../index.php');
    exit();
}

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $signalementController->deleteSignalement($signalement_id);
    
    if ($result['success']) {
        header('Location: ../index.php?message=supprime');
        exit();
    } else {
        $error = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer le Signalement - Admin SAFEProject</title>
    <!-- Use SB Admin 2 styles to match backoffice -->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .kb-confirm-card { max-width: 800px; margin: 30px auto; }
        .kb-warning { background: linear-gradient(180deg,#fff4e6,#fff9f0); border-left: 4px solid #ffc107; }
        .kb-admin-badge { background: #e74a3b; color: #fff; padding: 4px 8px; border-radius: 4px; font-weight: 700; }
        .kb-signalement-info { background: #f8f9fc; padding: 16px; border-radius: 6px; border: 1px solid rgba(0,0,0,0.05); }
        .kb-actions { display:flex; gap:10px; margin-top:16px; }
    </style>
</head>
<body id="page-top">
    <div class="container-fluid kb-confirm-card">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">🗑️ Supprimer le Signalement</h6>
                <span class="kb-admin-badge">ADMIN</span>
            </div>
            <div class="card-body">
                <div class="alert alert-warning kb-warning">
                    <strong>⚠️ Attention !</strong>
                    <p>Suppression définitive du signalement. Cette action est irréversible.</p>
                </div>

                <div class="kb-signalement-info">
                    <h5>Signalement à supprimer :</h5>
                    <p><strong>Titre :</strong> <?= htmlspecialchars($signalement['titre']) ?></p>
                    <p><strong>Type :</strong> <?= htmlspecialchars($signalement['type_nom']) ?></p>
                    <p><strong>Date :</strong> <?= date('d/m/Y à H:i', strtotime($signalement['created_at'])) ?></p>
                    <p><strong>Description :</strong> <?= nl2br(htmlspecialchars(substr($signalement['description'], 0, 400))) ?></p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger mt-3">❌ <?= $error ?></div>
                <?php endif; ?>

                <form method="POST" class="mt-3">
                    <div class="kb-actions">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Suppression ADMIN - Êtes-vous ABSOLUMENT sûr ?')">
                            <i class="fas fa-trash"></i> Confirmer la suppression (Admin)
                        </button>
                        <a href="../index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour au Dashboard</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts similar to backoffice pages -->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
</body>
</html>