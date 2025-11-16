<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/admincontroller.php';
session_start();
// 🔐 Vérifier si l'admin est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location:frontoffice/login.php');
    exit();
}

$adminController = new AdminController();

// 📊 Récupération des statistiques du dashboard
$stats = $adminController->getStats();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin SafeSpace - Dashboard</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <noscript><link rel="stylesheet" href="assets/css/noscript.css"></noscript>
</head>

<body class="is-preload">
    <div id="page-wrapper">

        <!-- Header -->
        <header id="header">
            <h1><a href="index.php">Admin SafeSpace</a></h1>
            <nav><a href="#menu">Menu</a></nav>
        </header>

        <!-- Menu -->
       <nav id="menu">
    <div class="inner">
        <h2>Menu Admin</h2>
        <ul class="links">
            <li><a href="backoffice/index.php">Dashboard Admin</a></li>
            <li><a href="backoffice/user_list.php">Utilisateurs</a></li>
            <li><a href="backoffice/reports_list.php">Signalements</a></li>
            <li><a href="backoffice/events_list.php">Événements</a></li>
            <li><a href="frontoffice/index.php">Retour au site</a></li>
            <li><a href="frontoffice/logout.php">Déconnexion</a></li>
        </ul>
        <a href="#" class="close">Fermer</a>
    </div>
</nav>


        <!-- Contenu -->
        <section id="wrapper">
            <div class="wrapper">
                <div class="inner">
                    <header>
                        <h2>Dashboard Admin</h2>
                        <p>Bienvenue dans le panneau d'administration SafeSpace.</p>
                    </header>

                    <!-- Statistiques rapides -->
                    <section class="stats">
                        <div class="stat">
                            <h3>Utilisateurs</h3>
                            <p><?= $stats['users'] ?? 0 ?></p>
                        </div>

                        <div class="stat">
                            <h3>Signalements</h3>
                            <p><?= $stats['reports'] ?? 0 ?></p>
                        </div>

                        <div class="stat">
                            <h3>Événements</h3>
                            <p><?= $stats['events'] ?? 0 ?></p>
                        </div>
                    </section>

                    <!-- Liens rapides -->
                    <section>
                        <h3>Actions rapides</h3>
                        <ul class="actions">
                            <li><a href="user_list.php" class="button primary">Gérer les utilisateurs</a></li>
                            <li><a href="reports_list.php" class="button">Voir les signalements</a></li>
                            <li><a href="events_list.php" class="button">Gérer les événements</a></li>
                        </ul>
                    </section>

                </div>
            </div>
        </section>

        <!-- Footer -->
        <section id="footer">
            <div class="inner">
                <p>Protégeons ensemble, agissons avec bienveillance.</p>
                <ul class="contact">
                    <li class="icon solid fa-envelope"><a href="#">contact@safespace.tn</a></li>
                    <li class="icon brands fa-facebook-f"><a href="#">facebook.com/safespace</a></li>
                    <li class="icon brands fa-instagram"><a href="#">instagram.com/safespace</a></li>
                </ul>
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
