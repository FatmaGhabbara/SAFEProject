<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/userController.php';
session_start();
$controller = new UserController();

// Récupération de l'utilisateur connecté (simulé ou depuis session)
$userId = $_SESSION['user_id'] ?? 1;

$message = "";
$user = $controller->showProfile($userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $message = $controller->updateProfile($userId, $fullname, $email, $password);
    $user = $controller->showProfile($userId);
}
?>

<!DOCTYPE HTML>
<html>
<head>
    <title>Mon Profil | SafeSpace</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="assets/css/main.css" />
</head>

<body class="is-preload">
<div id="page-wrapper">

    <!-- Header -->
    <header id="header">
        <h1><a href="frontoffice/index.php">SafeSpace</a></h1>
        <nav><a href="#menu">Menu</a></nav>
    </header>

    <!-- Menu -->
    <nav id="menu">
        <div class="inner">
            <h2>Menu</h2>
            <ul class="links">
                <li><a href="frontoffice/index.php">Home</a></li>
                <li><a href="backoffice/index.php">Admin</a></li>
                <li><a href="frontoffice/profile.php">Profil</a></li>
                <li><a href="frontoffice/logout.php">Déconnexion</a></li>
            </ul>
            <a href="#" class="close">Close</a>
        </div>
    </nav>

    <!-- Wrapper -->
    <section id="wrapper">
        <header>
            <div class="inner">
                <h2>Mon Profil</h2>
                <p>Gérez vos informations personnelles et mettez à jour votre compte.</p>
                <?php if ($message): ?>
                    <p style="color: green;"><?= htmlspecialchars($message) ?></p>
                <?php endif; ?>
            </div>
        </header>

        <div class="wrapper">
            <div class="inner">
                <section>
                    <h3 class="major">Informations actuelles</h3>
                    <p><strong>Nom complet :</strong> <?= htmlspecialchars($user['fullname']) ?></p>
                    <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <p><strong>Rôle :</strong> <?= htmlspecialchars($user['role']) ?></p>
                </section>

                <section>
                    <h3 class="major">Modifier mes informations</h3>
                    <form method="post" action="">
                        <div class="fields">
                            <div class="field">
                                <label for="fullname">Nom complet</label>
                                <input type="text" name="fullname" id="fullname" value="<?= htmlspecialchars($user['fullname']) ?>">
                            </div>
                            <div class="field">
                                <label for="email">Adresse e-mail</label>
                                <input type="text" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>">
                            </div>
                            <div class="field">
                                <label for="password">Nouveau mot de passe</label>
                                <input type="password" name="password" id="password" placeholder="Laisser vide si inchangé">
                            </div>
                        </div>
                        <ul class="actions">
                            <li><input type="submit" value="Mettre à jour" class="primary" /></li>
                        </ul>
                    </form>
                </section>
            </div>
        </div>
    </section>
</div>
</body>
</html>
