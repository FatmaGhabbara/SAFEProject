<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/UserController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userController = new UserController();
$user = $userController->getUser($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil | SafeSpace</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <noscript><link rel="stylesheet" href="assets/css/noscript.css"></noscript>
    <style>
        .profile-info { 
            margin-bottom: 1.5rem; 
            padding: 1rem; 
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }
        .profile-label { 
            font-weight: bold; 
            color: #ffffff; 
            font-size: 1.1em;
            display: block;
            margin-bottom: 0.5rem;
        }
        .profile-value { 
            color: #ecf0f1; 
            font-size: 1.2em;
            display: block;
            padding: 0.5rem 0;
        }
        .role-admin { color: #e74c3c; font-weight: bold; }
        .role-conseilleur { color: #f39c12; font-weight: bold; }
        .role-membre { color: #2ecc71; font-weight: bold; }
    </style>
</head>
<body class="is-preload">

<div id="page-wrapper">

    <!-- Header -->
    <header id="header">
        <h1><a href="index.php">SafeSpace</a></h1>
        <nav>
            <a href="index.php">Accueil</a> |
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="../backoffice/index.php">Admin</a> |
            <?php endif; ?>
            <a href="profile.php">Profil</a> |
            <a href="logout.php">Déconnexion</a>
        </nav>
    </header>

    <!-- Wrapper -->
    <section id="wrapper">
        <header>
            <div class="inner">
                <h2>Mon Profil</h2>
                <p>Informations de votre compte</p>
            </div>
        </header>

        <!-- Content -->
        <div class="wrapper">
            <div class="inner">

                <?php if ($user): ?>
                <div class="profile-info">
                    <span class="profile-label">👤 Nom complet</span>
                    <span class="profile-value"><?= htmlspecialchars($user->getFullname()) ?></span>
                </div>
                
                <div class="profile-info">
                    <span class="profile-label">📧 Email</span>
                    <span class="profile-value"><?= htmlspecialchars($user->getEmail()) ?></span>
                </div>
                
                <div class="profile-info">
                    <span class="profile-label">🎯 Rôle</span>
                    <?php
                    $role = $user->getRole();
                    $roleClass = 'role-' . $role;
                    $roleIcons = [
                        'admin' => '👑 Administrateur',
                        'conseilleur' => '💼 Conseilleur',
                        'membre' => '👤 Membre'
                    ];
                    $roleDisplay = $roleIcons[$role] ?? '👤 Membre';
                    ?>
                    <span class="profile-value <?= $roleClass ?>">
                        <?= $roleDisplay ?>
                    </span>
                </div>
                
                <div class="profile-info">
                    <span class="profile-label">📊 Statut</span>
                    <span class="profile-value"><?= htmlspecialchars($user->getStatus()) ?></span>
                </div>

                <?php else: ?>
                    <div class="error">
                        <p>Utilisateur non trouvé.</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section>

</div>

<!-- Scripts -->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/jquery.scrollex.min.js"></script>
<script src="assets/js/browser.min.js"></script>
<script src="assets/js/breakpoints.min.js"></script>
<script src="assets/js/util.js"></script>
<script src="assets/js/main.js"></script>

</body>
</html>