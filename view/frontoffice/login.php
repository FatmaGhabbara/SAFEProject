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
    } elseif ($_SESSION['user_role'] === 'conseilleur') {
        header('Location: ../backoffice/adviser_dashboard.php');
    } else {
        header('Location: ../backoffice/member_dashboard.php');
    }
    exit();
}

<<<<<<< HEAD
// Chemin vers le contrôleur
$controller_path = $_SERVER['DOCUMENT_ROOT'] . '/controller/AuthController.php';
if (file_exists($controller_path)) {
    require_once $controller_path;
} else {
    // Fallback au chemin relatif
    require_once '../../controller/AuthController.php';
=======
// ===============================
// AuthController
// ===============================
$controllerPath = $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/AuthController.php';
if (!file_exists($controllerPath)) {
    die('Erreur : AuthController introuvable');
>>>>>>> af8b4baf22b0b6e35827106fed7e959ed54c3093
}
require_once $controllerPath;

$authController = new AuthController();
$error = '';
<<<<<<< HEAD
$captcha_enabled = true; // reCAPTCHA activé
=======

// ===============================
// reCAPTCHA (⚠️ استبدل بالمفاتيح متاعك)
// ===============================
$recaptcha_site_key   = '6LeXWCgsAAAAANEGd1QzF3TFKjqWWGrIOyLYPkfa';
$recaptcha_secret_key = '6LeXWCgsAAAAALngdk9wHBfBBZCogaCNHtqNXzuO';
>>>>>>> af8b4baf22b0b6e35827106fed7e959ed54c3093

// ===============================
// Traitement formulaire
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
<<<<<<< HEAD
    
    if ($captcha_enabled) {
        $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
        
        if (empty($recaptcha_response)) {
            $error = "Veuillez valider le CAPTCHA.";
        } else {
            $recaptcha_secret_key = "6LeXWCgsAAAAALngdk9wHBfBBZCogaCNHtqNXzuO";
            $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
            $recaptcha_data = [
                'secret' => $recaptcha_secret_key,
                'response' => $recaptcha_response,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            ];
            
            $recaptcha_options = [
                'http' => [
                    'method' => 'POST',
                    'content' => http_build_query($recaptcha_data),
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
                ]
            ];
            
            $recaptcha_context = stream_context_create($recaptcha_options);
            $recaptcha_result = file_get_contents($recaptcha_url, false, $recaptcha_context);
            $recaptcha_json = json_decode($recaptcha_result);
            
            if (!$recaptcha_json->success) {
                $error = "Échec de la vérification CAPTCHA. Veuillez réessayer.";
=======
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
>>>>>>> af8b4baf22b0b6e35827106fed7e959ed54c3093
            }
        }
    }
    
    if (empty($error)) {
        $result = $authController->login($email, $password);
        if ($result === true) {
            exit();
        } else {
            $error = $result;
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
<<<<<<< HEAD
    <?php if ($captcha_enabled): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
    
    <style>
        /* Styles spécifiques pour la page de login */
        .recaptcha-container {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fc;
            border-radius: 8px;
            border: 1px solid #e3e6f0;
            text-align: center;
        }
        
        .recaptcha-info {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .recaptcha-info i {
            color: #4e73df;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .alert-success {
            background-color: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        
        .admin-only {
            background: #4e73df;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            margin-left: 10px;
            vertical-align: middle;
        }
        
        .windows-section {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
            border: 1px solid #e3e6f0;
            text-align: center;
        }
        
        .windows-icon {
            font-size: 2.5rem;
            color: #0078d4;
            margin-bottom: 15px;
        }
        
        .biometric-info {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-windows {
            background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
            text-decoration: none;
        }
        
        .btn-windows:hover {
            background: linear-gradient(135deg, #106ebe 0%, #005a9e 100%);
            transform: translateY(-2px);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.3);
            color: white;
        }
        
        .security-tips {
            margin-top: 30px;
            padding: 20px;
            background: #f0f7ff;
            border-radius: 8px;
            border-left: 4px solid #4e73df;
        }
        
        .security-tips h4 {
            color: #224abe !important;
            margin-bottom: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .security-tips ul {
            color: #5a5c69 !important;
            font-size: 0.9rem;
            line-height: 1.6;
            padding-left: 20px;
        }
        
        .security-tips li {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .security-tips i {
            color: #4e73df;
        }
        
        .links {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e3e6f0;
        }
        
        .links a {
            color: #4e73df;
            text-decoration: none;
            margin: 0 10px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .links a:hover {
            text-decoration: underline;
            color: #224abe;
        }
        
        .divider {
            height: 1px;
            background: #e3e6f0;
            margin: 25px 0;
            position: relative;
        }
        
        .divider-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 0 15px;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .password-field {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            font-size: 1.1rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .windows-section {
                padding: 15px;
            }
            
            .btn-windows {
                width: 100%;
                justify-content: center;
            }
            
            .links {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            
            .links a {
                margin: 5px 0;
            }
        }
    </style>
=======

    <!-- reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
>>>>>>> af8b4baf22b0b6e35827106fed7e959ed54c3093
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

<<<<<<< HEAD
                <form method="post" action="" id="loginForm">
                    <div class="fields">
                        <div class="field">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Adresse email
                            </label>
                            <input type="email" name="email" id="email" 
                                   placeholder="exemple@safespace.com" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   required />
                        </div>
                        
                        <div class="field">
                            <label for="password">
                                <i class="fas fa-lock"></i> Mot de passe
                            </label>
                            <div class="password-field">
                                <input type="password" name="password" id="password" 
                                       placeholder="Votre mot de passe" 
                                       required />
                                <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <?php if ($captcha_enabled): ?>
                        <div class="field">
                            <div class="recaptcha-container">
                                <div class="g-recaptcha" data-sitekey="6LeXWCgsAAAAANEGd1QzF3TFKjqWWGrIOyLYPkfa"></div>
                                <div class="recaptcha-info">
                                    <i class="fas fa-robot"></i> Cette vérification permet de protéger votre compte contre les robots.
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
=======
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
>>>>>>> af8b4baf22b0b6e35827106fed7e959ed54c3093
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
<<<<<<< HEAD

<?php if ($captcha_enabled): ?>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const recaptchaResponse = document.querySelector('[name="g-recaptcha-response"]');
            if (!recaptchaResponse || recaptchaResponse.value === '') {
                e.preventDefault();
                alert('Veuillez valider le CAPTCHA avant de continuer.');
                return false;
            }
        });
    }
});
<?php endif; ?>

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('email').focus();
});

// Animation de chargement pour le bouton de soumission
document.getElementById('loginForm')?.addEventListener('submit', function() {
    const submitBtn = this.querySelector('input[type="submit"]');
    if (submitBtn) {
        submitBtn.value = 'Connexion en cours...';
        submitBtn.disabled = true;
    }
});
=======
>>>>>>> af8b4baf22b0b6e35827106fed7e959ed54c3093
</script>

</body>
</html>
