<?php
// frontoffice/index.php

require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/usercontroller.php';

$userController = new UserController();
$users = $userController->listUsers();
?>

<!DOCTYPE HTML>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <title>Safe Space - FrontOffice</title>
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/main.css" />
    <noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
</head>
<body class="is-preload">

<div id="page-wrapper">

    <!-- Header -->
    <header id="header" class="alt">
        <h1><a href="index.php">Safe Space</a></h1>
        <nav>
            <a href="#menu">Menu</a>
        </nav>
    </header>

    <!-- Menu -->
    <nav id="menu">
        <div class="inner">
            <h2>Menu</h2>
            <ul class="links">
                <li><a href="index.php">Home</a></li>
                <li><a href="backoffice/index.php" target="_blank">Admin</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="login.php">Log In</a></li>
                <li><a href="register.php">Sign Up</a></li>
            </ul>
            <a href="#" class="close">Close</a>
        </div>
    </nav>

    <!-- Banner -->
    <section id="banner">
        <div class="inner">
            <h2>Safe Space</h2>
            <p>Envie de libérer vos émotions ? Partagez vos pensées en toute sécurité.</p>
        </div>
    </section>

    <!-- Wrapper -->
    <section id="wrapper">
        <section id="users" class="wrapper spotlight style1">
            <div class="inner">
                <h2 class="major">Liste des utilisateurs</h2>
                <?php if (!empty($users)): ?>
                <ul>
                    <?php foreach($users as $user): ?>
                        <li>
                            <?= htmlspecialchars($user['fullname']) ?> - 
                            <?= htmlspecialchars($user['email']) ?> - 
                            Status: <?= htmlspecialchars($user['status']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                    <p>Aucun utilisateur trouvé.</p>
                <?php endif; ?>
            </div>
        </section>
    </section>

    <!-- Footer -->
    <section id="footer">
        <div class="inner">
            <h2 class="major">Get in touch</h2>
            <form method="post" action="#">
                <div class="fields">
                    <div class="field">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" />
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" />
                    </div>
                    <div class="field">
                        <label for="message">Message</label>
                        <textarea name="message" id="message" rows="4"></textarea>
                    </div>
                </div>
                <ul class="actions">
                    <li><input type="submit" value="Send Message" /></li>
                </ul>
            </form>
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
