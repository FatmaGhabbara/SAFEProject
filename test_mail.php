<?php
// Activer les erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Test du système de mailing SafeSpace</h1>";

// Inclure le MailController
require_once 'controller/mailcontroller.php';

try {
    $mailController = new MailController();
    
    echo "<h2>1. Test de connexion SMTP</h2>";
    $testResult = $mailController->testSMTPConnection();
    echo "<p>" . $testResult['message'] . "</p>";
    
    if ($testResult['success']) {
        echo "<h2>2. Test d'envoi d'email</h2>";
        
        // Changez cette adresse par la vôtre
        $testEmail = "VOTRE_EMAIL@exemple.com";
        $testName = "Test User";
        
        echo "<p>Envoi d'un email de test à : <strong>$testEmail</strong></p>";
        
        if ($mailController->sendWelcomeEmail($testEmail, $testName)) {
            echo "<p style='color: green; font-weight: bold;'>✅ Email envoyé avec succès !</p>";
            echo "<p>Vérifiez votre boîte de réception (et les spams).</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ Échec de l'envoi de l'email.</p>";
            echo "<p>Vérifiez :</p>";
            echo "<ul>";
            echo "<li>Les identifiants SMTP dans MailController.php</li>";
            echo "<li>Que le mot de passe Gmail est correct (mot de passe d'application si 2FA activé)</li>";
            echo "<li>Que les ports ne sont pas bloqués par le pare-feu</li>";
            echo "</ul>";
        }
    }
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ ERREUR FATALE</h2>";
    echo "<p><strong>Message :</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Fichier :</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Ligne :</strong> " . $e->getLine() . "</p>";
    
    echo "<h3>Solutions possibles :</h3>";
    echo "<ol>";
    echo "<li>Vérifiez que PHPMailer est bien installé dans vendor/PHPMailer-master/</li>";
    echo "<li>Vérifiez les chemins dans require_once du MailController</li>";
    echo "<li>Assurez-vous que PHP a les permissions de lecture</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<h2>📋 Configuration actuelle :</h2>";
echo "<pre>";
echo "Chemin du projet : " . $_SERVER['DOCUMENT_ROOT'] . "/SAFEProject/\n";
echo "PHPMailer attendu : " . $_SERVER['DOCUMENT_ROOT'] . "/SAFEProject/vendor/PHPMailer-master/src/PHPMailer.php\n";
echo "Version PHP : " . phpversion() . "\n";
echo "</pre>";
?>