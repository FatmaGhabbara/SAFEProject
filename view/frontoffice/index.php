<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/usercontroller.php';

$userController = new UserController();
$users = $userController->listUsers();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeSpace - Accueil</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

<header>
    <h1><a href="index.php">SafeSpace</a></h1>
    <nav>
        <a href="index.php">Accueil</a> |
        <a href="../backoffice/index.php">Admin</a> |
        <a href="profile.php">Profil</a> |
        <a href="login.php">Connexion</a> |
        <a href="register.php">Inscription</a>
    </nav>
</header>

<section>
    <h2>Bienvenue sur SafeSpace</h2>
    <p>Envie de libérer vos émotions ? Partagez vos pensées en toute sécurité.</p>
</section>



</body>
</html>
