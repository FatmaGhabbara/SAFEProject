<?php
// SUPPRESSION FRONTOFFICE - Pour les membres
$root = $_SERVER['DOCUMENT_ROOT'] . '/fedi/SAFEProject/';
include_once $root . 'config.php';
include_once $root . 'model/Signalement.php';
include_once $root . 'model/Type.php';
include_once $root . 'controller/TypeController.php';
include_once $root . 'controller/SignalementController.php';

if (!isset($_GET['id'])) {
    header('Location: mes_signalements.php');
    exit();
}

$signalementController = new SignalementController($db);
$signalement_id = $_GET['id'];

// Vérifier si le signalement existe
$signalement = $signalementController->getSignalementById($signalement_id);
if (!$signalement) {
    header('Location: mes_signalements.php');
    exit();
}

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $signalementController->deleteSignalement($signalement_id);
    
    if ($result['success']) {
        header('Location: mes_signalements.php?message=supprime');
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
    <title>Supprimer le Signalement - SAFEProject</title>
    <style>
        .container { max-width: 600px; margin: 50px auto; padding: 20px; text-align: center; }
        .warning-box { 
            background: #fff3cd; 
            border: 1px solid #ffeaa7; 
            border-radius: 8px; 
            padding: 30px; 
            margin-bottom: 20px;
        }
        .btn { 
            padding: 10px 20px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            text-decoration: none;
            display: inline-block;
            margin: 0 10px;
        }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .signalement-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗑️ Supprimer le Signalement</h1>
        
        <div class="warning-box">
            <h2>⚠️ Attention !</h2>
            <p>Vous êtes sur le point de supprimer définitivement ce signalement.</p>
            <p><strong>Cette action est irréversible.</strong></p>
        </div>

        <div class="signalement-info">
            <h3>Signalement à supprimer :</h3>
            <p><strong>Titre :</strong> <?= htmlspecialchars($signalement['titre']) ?></p>
            <p><strong>Type :</strong> <?= htmlspecialchars($signalement['type_nom']) ?></p>
            <p><strong>Date :</strong> <?= date('d/m/Y à H:i', strtotime($signalement['created_at'])) ?></p>
        </div>

        <?php if (isset($error)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                ❌ <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous ABSOLUMENT sûr ?')">
                🗑️ Confirmer la suppression
            </button>
            <a href="mes_signalements.php" class="btn btn-secondary">← Annuler</a>
        </form>
    </div>
</body>
</html>