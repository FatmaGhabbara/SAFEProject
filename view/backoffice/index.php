
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/AdminController.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../frontoffice/login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - SafeSpace</title>
    <link rel="stylesheet" href="../frontoffice/assets/css/main.css">
    <noscript><link rel="stylesheet" href="../frontoffice/assets/css/noscript.css"></noscript>
</head>
<body class="is-preload">

<div id="page-wrapper">

    <!-- Header -->
    <header id="header">
        <h1><a href="index.php">SafeSpace - Administration</a></h1>
        <nav>
            <a href="index.php">Dashboard</a> |
            <a href="users_list.php">Gérer les utilisateurs</a> |
            <a href="../frontoffice/index.php">Site public</a> |
            <a href="../frontoffice/logout.php">Déconnexion</a>
        </nav>
    </header>

    <!-- Wrapper -->
    <section id="wrapper">
        <header>
            <div class="inner">
                <h2>Tableau de Bord Administrateur</h2>
                <p>Bienvenue, <?= htmlspecialchars($_SESSION['username']) ?> !</p>
                <p>Vous êtes connecté en tant qu'administrateur de SafeSpace.</p>
            </div>
        </header>

        <!-- Content -->
        <div class="wrapper">
            <div class="inner">

                <section class="features">
                    <div class="feature">
                        <h3 class="major">👥 Gestion des Utilisateurs</h3>
                        <p>Gérez les membres de la communauté : approuvez, bloquez ou supprimez des comptes.</p>
                        <a href="users_list.php" class="button primary">Accéder à la gestion</a>
                    </div>
                    
                    <div class="feature">
                        <h3 class="major">🌐 Site Public</h3>
                        <p>Retournez sur le site principal pour voir l'expérience utilisateur.</p>
                        <a href="../frontoffice/index.php" class="button">Visiter le site</a>
                    </div>
                    
                    <div class="feature">
                        <h3 class="major">⚙️ Mon Profil</h3>
                        <p>Consultez et modifiez votre profil administrateur.</p>
                        <a href="../frontoffice/profile.php" class="button">Voir mon profil</a>
                    </div>
                </section>

                <section class="main-content">
                    <h3 class="major">Actions Rapides</h3>
                    <div class="quick-actions">
                        <a href="users_list.php?action=list" class="button small">Voir tous les utilisateurs</a>
                        <a href="../frontoffice/profile.php" class="button small">Mon compte</a>
                        <a href="../frontoffice/logout.php" class="button small">Déconnexion</a>
                    </div>
                </section>

            </div>
        </div>
    </section>

    <!-- Footer -->
    <section id="footer">
        <div class="inner">
            <h2 class="major">SafeSpace Administration</h2>
            <p>Plateforme de gestion et modération de la communauté SafeSpace.</p>
            <ul class="contact">
                <li class="icon solid fa-home">Panel d'administration</li>
                <li class="icon solid fa-user">Connecté en tant que : <?= htmlspecialchars($_SESSION['username']) ?></li>
                <li class="icon solid fa-shield-alt">Rôle : Administrateur</li>
            </ul>
            <ul class="copyright">
                <li>&copy; SafeSpace. Tous droits réservés.</li>
                <li>Panel Admin</li>
            </ul>
        </div>
    </section>

</div>

<!-- Scripts -->
<script src="../frontoffice/assets/js/jquery.min.js"></script>
<script src="../frontoffice/assets/js/jquery.scrollex.min.js"></script>
<script src="../frontoffice/assets/js/browser.min.js"></script>
<script src="../frontoffice/assets/js/breakpoints.min.js"></script>
<script src="../frontoffice/assets/js/util.js"></script>
<script src="../frontoffice/assets/js/main.js"></script>

</body>
</html>