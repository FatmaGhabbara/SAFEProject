<?php
// Activer l'affichage des erreurs pour debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Si déjà connecté, rediriger
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: ../backoffice/index.php');
    } else {
        header('Location: meme.php');
    }
    exit();
}

// Chemin absolu vers le contrôleur
$controller_path = $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/AuthController.php';
if (file_exists($controller_path)) {
    require_once $controller_path;
} else {
    // Fallback au chemin relatif
    require_once '../../controller/AuthController.php';
}

$authController = new AuthController();
$error = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = $authController->login($email, $password);
    
    if ($result === true) {
        // La redirection est gérée par AuthController
        exit();
    } else {
        $error = $result;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - SafeSpace</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* CSS de votre site original */
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --dark-color: #5a5c69;
            --light-color: #f8f9fc;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-color);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background-image: linear-gradient(180deg, var(--primary-color) 10%, #224abe 100%);
            background-size: cover;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }
        
        .login-header {
            background: white;
            color: var(--primary-color);
            padding: 25px 30px;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .nav-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .logo-img {
            height: 40px;
            width: auto;
            object-fit: contain;
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .site-subtitle {
            color: var(--dark-color);
            font-size: 14px;
            opacity: 0.8;
            margin-top: 5px;
            text-align: center;
            padding: 0 20px;
        }
        
        .login-body {
            padding: 40px;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
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
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark-color);
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #d1d3e2;
            border-radius: 5px;
            font-size: 15px;
            transition: all 0.3s;
            background-color: white;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
            outline: 0;
        }
        
        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            transform: translateY(-1px);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .btn-windows {
            background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);
            color: white;
            margin-top: 15px;
        }
        
        .btn-windows:hover {
            background: linear-gradient(135deg, #106ebe 0%, #005a9e 100%);
            transform: translateY(-1px);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }
        
        .divider::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e3e6f0;
        }
        
        .divider-text {
            background: white;
            padding: 0 15px;
            color: var(--dark-color);
            font-size: 14px;
            position: relative;
            z-index: 1;
        }
        
        .links {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e3e6f0;
        }
        
        .links a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            margin: 0 10px;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
        .windows-section {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 20px;
            margin-top: 25px;
            border: 1px solid #e3e6f0;
            text-align: center;
        }
        
        .windows-icon {
            font-size: 30px;
            color: #0078d4;
            margin-bottom: 10px;
        }
        
        .biometric-info {
            font-size: 13px;
            color: #6c757d;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .admin-only {
            display: inline-block;
            background: #fff3cd;
            color: #856404;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
        }
        
        .test-accounts {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fc;
            border-radius: 5px;
            border-left: 4px solid var(--primary-color);
        }
        
        .test-accounts h4 {
            color: var(--dark-color);
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .test-accounts ul {
            list-style: none;
            font-size: 13px;
        }
        
        .test-accounts li {
            padding: 5px 0;
            color: #6c757d;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 13px;
            border-top: 1px solid #e3e6f0;
            background: #f8f9fc;
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .login-body {
                padding: 30px 20px;
            }
            
            .login-header {
                padding: 20px;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .logo-section {
                justify-content: center;
            }
            
            .logo-img {
                height: 35px;
            }
            
            .logo-text {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="header-content">
                <div class="logo-section">
                    <a class="nav-logo text-primary" href="index.php">
                        <!-- VOTRE LOGO - ajustez le src si nécessaire -->
                        <img src="images/logo.png" alt="SafeSpace Logo" class="logo-img">
                        <span class="logo-text">SafeSpace</span>
                    </a>
                </div>
                
                <div style="color: var(--dark-color); font-size: 14px; opacity: 0.8;">
                    <i class="fas fa-shield-alt"></i> Sécurisé
                </div>
            </div>
            
            <div class="site-subtitle">
                Plateforme sécurisée de gestion et de collaboration
            </div>
        </div>
        
        <div class="login-body">
            <h2 style="color: var(--dark-color); margin-bottom: 25px; text-align: center; font-size: 20px;">
                <i class="fas fa-sign-in-alt"></i> Connexion à votre compte
            </h2>
            
            <?php if (isset($_GET['registered']) && $_GET['registered'] == '1'): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Inscription réussie ! Votre compte est en attente d'approbation.
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i> Adresse email
                    </label>
                    <input type="email" id="email" name="email" class="form-control" required 
                           placeholder="exemple@safespace.com" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Mot de passe
                    </label>
                    <input type="password" id="password" name="password" class="form-control" required 
                           placeholder="Votre mot de passe">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
            </form>
            
            <div class="divider">
                <span class="divider-text">OU</span>
            </div>
            
            <div class="windows-section">
                <div class="windows-icon">
                    <i class="fab fa-windows"></i>
                </div>
                <h3 style="color: #005a9e; margin-bottom: 15px; font-size: 18px;">
                    Connexion Admin sécurisée
                    <span class="admin-only">Admin only</span>
                </h3>
                <p style="color: #666; margin-bottom: 15px; font-size: 14px;">
                    Windows Hello réservé à l'administrateur pour une authentification biométrique.
                </p>
                
                <a href="fingerprint-login.php" class="btn btn-windows">
                    <i class="fas fa-fingerprint"></i> Windows Hello (Admin)
                </a>
                
                <div class="biometric-info">
                    <i class="fas fa-info-circle"></i>
                    <span>Réservé à l'administrateur • Empreinte • Reconnaissance faciale • PIN</span>
                </div>
            </div>
            
            <div class="links">
                <a href="forgot_password.php">
                    <i class="fas fa-key"></i> Mot de passe oublié ?
                </a>
                <span style="color: #dee2e6">|</span>
                <a href="register.php" style="color: var(--secondary-color); font-weight: 600;">
                    <i class="fas fa-user-plus"></i> Créer un compte
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p>© 2024 SafeSpace. Tous droits réservés.</p>
            <p style="font-size: 12px; margin-top: 5px; opacity: 0.7;">
                <i class="fas fa-shield-alt"></i> Sécurité et confidentialité garanties
            </p>
        </div>
    </div>
    
    <script>
        // Fallback si le logo n'existe pas
        document.addEventListener('DOMContentLoaded', function() {
            const logoImg = document.querySelector('.logo-img');
            if (logoImg) {
                logoImg.onerror = function() {
                    this.style.display = 'none';
                    const logoText = document.querySelector('.logo-text');
                    if (logoText) {
                        logoText.innerHTML = '<i class="fas fa-shield-alt"></i> SafeSpace';
                        logoText.style.fontSize = '24px';
                    }
                };
            }
            
            // Focus sur l'email
            document.getElementById('email').focus();
        });
    </script>
</body>
</html>