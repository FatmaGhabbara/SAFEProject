<?php
// Chemin absolu
$root = $_SERVER['DOCUMENT_ROOT'] . '/fedi/SAFEProject/';

// Inclure les fichiers nécessaires
include_once $root . 'config.php';
include_once $root . 'model/Signalement.php';
include_once $root . 'model/Type.php';
include_once $root . 'controller/TypeController.php';
include_once $root . 'controller/SignalementController.php';

$signalementController = new SignalementController($db);
$types = $signalementController->getTypesForForm();

$message = '';
$success = false;
$errors = [];

// Traitement du formulaire avec validation PHP
if ($_POST) {
    // Validation manuelle (sans HTML5)
    if (empty($_POST['titre'])) {
        $errors[] = "Le titre est obligatoire";
    }
    
    if (empty($_POST['type_id'])) {
        $errors[] = "Le type est obligatoire";
    }
    
    if (empty($_POST['description'])) {
        $errors[] = "La description est obligatoire";
    }
    
    // Si pas d'erreurs, créer le signalement
    if (empty($errors)) {
        $result = $signalementController->createSignalement($_POST);
        if ($result['success']) {
            $message = $result['message'];
            $success = true;
            $_POST = []; // Reset du formulaire
        } else {
            $message = $result['message'];
        }
    } else {
        $message = "Veuillez corriger les erreurs suivantes :";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Signalement - SAFEProject</title>
    <style>
        .container { max-width: 600px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            box-sizing: border-box;
        }
        textarea { height: 150px; resize: vertical; }
        .btn { 
            background: #007bff; 
            color: white; 
            padding: 12px 30px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
        }
        .btn:hover { background: #0056b3; }
        .message { 
            padding: 10px; 
            margin-bottom: 20px; 
            border-radius: 5px; 
        }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .error-list { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 10px; 
            border-radius: 5px; 
            margin-bottom: 20px;
        }
        .error-list ul { margin: 0; padding-left: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📝 Ajouter un Signalement</h1>
        
        <?php if ($message): ?>
            <div class="message <?= $success ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error-list">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="titre">Titre du signalement</label>
                <input type="text" id="titre" name="titre" 
                       value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="type_id">Type de signalement</label>
                <select id="type_id" name="type_id">
                    <option value="">Sélectionnez un type</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?= $type['id'] ?>" 
                            <?= (isset($_POST['type_id']) && $_POST['type_id'] == $type['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Description détaillée</label>
                <textarea id="description" name="description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn">➕ Ajouter le Signalement</button>
        </form>

        <p style="margin-top: 20px;">
            <a href="mes_signalements.php">← Retour à la liste des signalements</a>
        </p>
    </div>
</body>
</html>