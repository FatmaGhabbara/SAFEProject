<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/usercontroller.php';
// 🔐 Vérifier si l'utilisateur est connecté
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
</head>
<body class="is-preload">

<div id="page-wrapper">

    <!-- Header -->
    <header id="header">
        <h1><a href="index.php">SafeSpace</a></h1>
        <nav><a href="#menu">Menu</a></nav>
    </header>

    <!-- Menu -->
    <nav id="menu">
        <div class="inner">
            <h2>Menu</h2>
            <ul class="links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="profile.php">Profil</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
            <a href="#" class="close">Fermer</a>
        </div>
    </nav>

    <!-- Wrapper -->
    <section id="wrapper">
        <div class="wrapper">
            <div class="inner">
                <header>
                    <h2>Profil de <?= htmlspecialchars($user['fullname']) ?></h2>
                    <p>Email : <?= htmlspecialchars($user['email']) ?></p>
                    <p>Rôle : <?= htmlspecialchars($user['role']) ?></p>
                    <p>Statut : <?= htmlspecialchars($user['status']) ?></p>
                </header>

             
               

            </div>
        </div>
    </section>

    <!-- Footer -->
    <section id="footer">
        <div class="inner">
            <p>Protégeons ensemble, agissons avec bienveillance.</p>
        </div>
    </section>

</div>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/jquery.scrollex.min.js"></script>
<script src="assets/js/browser.min.js"></script>
<script src="assets/js/breakpoints.min.js"></script>
<script src="assets/js/util.js"></script>
<script src="assets/js/main.js"></script>

</body>
</html>
