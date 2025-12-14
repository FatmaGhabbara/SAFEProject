<?php
// ===============================
// Debug (عطّلها في الإنتاج)
// ===============================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ===============================
// Session
// ===============================
session_start();

// ===============================
// Redirection si déjà connecté
// ===============================
if (isset($_SESSION['user_id'])) {
    if (($_SESSION['user_role'] ?? '') === 'admin') {
        header('Location: ../backoffice/index.php');
    } else {
        header('Location: meme.php');
    }
    exit();
}

// ===============================
// AuthController
// ===============================
$controllerPath = $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/AuthController.php';
if (!file_exists($controllerPath)) {
    die('Erreur : AuthController introuvable');
}
require_once $controllerPath;

$authController = new AuthController();
$error = '';

// ===============================
// reCAPTCHA (⚠️ استبدل بالمفاتيح متاعك)
// ===============================
$recaptcha_site_key   = '6LeXWCgsAAAAANEGd1QzF3TFKjqWWGrIOyLYPkfa';
$recaptcha_secret_key = '6LeXWCgsAAAAALngdk9wHBfBBZCogaCNHtqNXzuO';

// ===============================
// Traitement formulaire
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $captcha  = $_POST['g-recaptcha-response'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Veuillez remplir tous les champs.';
    } elseif ($captcha === '') {
        $error = 'Veuillez valider le CAPTCHA.';
    } else {

        // Vérification reCAPTCHA
        $verify = file_get_contents(
            'https://www.google.com/recaptcha/api/siteverify',
            false,
            stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'content' => http_build_query([
                        'secret'   => $recaptcha_secret_key,
                        'response' => $captcha,
                        'remoteip' => $_SERVER['REMOTE_ADDR']
                    ])
                ]
            ])
        );

        $captchaResult = json_decode($verify);

        if (!$captchaResult || !$captchaResult->success) {
            $error = 'Échec de la vérification CAPTCHA.';
        } else {
            // Login
            $result = $authController->login($email, $password);

            if ($result === true) {
                // redirection gérée par AuthController
                exit();
            } else {
                $error = $result;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - SafeSpace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/main.css">
    <noscript><link rel="stylesheet" href="assets/css/noscript.css"></noscript>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body class="is-preload">

<div id="page-wrapper">

    <!-- Header -->
    <header id="header">
        <h1><a href="index.php">SafeSpace</a></h1>
        <nav>
            <a href="index.php">Accueil</a> |
            <a href="login.php">Connexion</a> |
            <a href="register.php">Inscription</a>
        </nav>
    </header>

    <!-- Wrapper -->
    <section id="wrapper">
        <header>
            <div class="inner">
                <h2>Connexion</h2>
                <p>Accédez à votre compte SafeSpace</p>
            </div>
        </header>

        <div class="wrapper">
            <div class="inner">

                <?php if (isset($_GET['registered']) && $_GET['registered'] == '1'): ?>
                    <div class="alert alert-success">
                        Inscription réussie. Compte en attente d’approbation.
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="post" id="loginForm">

                    <div class="field">
                        <label>Email</label>
                        <input type="email" name="email" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="field password-field">
                        <label>Mot de passe</label>
                        <input type="password" name="password" id="password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>

                    <div class="recaptcha-container">
                        <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($recaptcha_site_key) ?>"></div>
                    </div>

                    <ul class="actions">
                        <li><input type="submit" class="primary" value="Se connecter"></li>
                        <li><a href="register.php" class="button">Créer un compte</a></li>
                    </ul>
                </form>

                <!-- Windows Hello -->
                <div class="windows-section">
                    <h3>Connexion Admin sécurisée</h3>
                    <a href="fingerprint-login.php" class="btn-windows">
                        <i class="fas fa-fingerprint"></i> Windows Hello
                    </a>
                </div>

                <div class="links">
                    <a href="forgot_password.php">Mot de passe oublié ?</a> |
                    <a href="index.php">Accueil</a>
                </div>

            </div>
        </div>
    </section>

    <section id="footer">
        <div class="inner">
            <p>SafeSpace © <?= date('Y') ?></p>
        </div>
    </section>

</div>

<!-- Scripts -->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/main.js"></script>

<script>
function togglePassword() {
    const p = document.getElementById('password');
    const i = document.getElementById('eyeIcon');
    if (p.type === 'password') {
        p.type = 'text';
        i.className = 'fas fa-eye-slash';
    } else {
        p.type = 'password';
        i.className = 'fas fa-eye';
    }
}
</script>

</body>
</html>
