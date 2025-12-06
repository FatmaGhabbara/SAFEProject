<?php
// Activer toutes les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

echo "<h1>🔐 Test SafeSpace - Mot de passe oublié</h1>";
echo "<p>PHP fonctionne correctement !</p>";

// Test simple
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<p style='color:green;'>✅ Formulaire POST reçu !</p>";
    echo "<p>Email reçu: " . htmlspecialchars($_POST['email'] ?? '') . "</p>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .container { max-width: 500px; margin: 0 auto; }
        input, button { padding: 10px; margin: 5px 0; width: 100%; }
        button { background: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <form method="post" action="">
            <h3>Test de formulaire</h3>
            <input type="email" name="email" placeholder="test@exemple.com" required>
            <button type="submit">Tester l'envoi</button>
        </form>
        
        <hr>
        <h3>Informations système :</h3>
        <ul>
            <li>PHP Version: <?= phpversion() ?></li>
            <li>Session: <?= session_id() ?></li>
            <li>Chemin: <?= __FILE__ ?></li>
            <li><a href="reset_password.php">Tester reset_password.php</a></li>
        </ul>
    </div>
</body>
</html>