<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/AuthController.php';
session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email)) $errors[] = "Veuillez entrer votre e-mail.";
    if (empty($password)) $errors[] = "Veuillez entrer votre mot de passe.";

    if (empty($errors)) {
        $authController = new AuthController();
        $result = $authController->login($email, $password);
        if ($result === true) {
            if ($_SESSION['role'] === 'admin') {
                header("Location: ../backoffice/index.php");
            } else {
                header("Location: profile.php");
            }
            exit;
        } else {
            $errors[] = $result;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - SafeSpace</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <noscript><link rel="stylesheet" href="assets/css/noscript.css"></noscript>
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

        <!-- Content -->
        <div class="wrapper">
            <div class="inner">

                <?php if(!empty($errors)): ?>
                    <div class="error">
                        <?php foreach($errors as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="fields">
                        <div class="field">
                            <label for="email">Email</label>
                            <input type="text" name="email" id="email" placeholder="exemple@mail.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
                        </div>
                        <div class="field">
                            <label for="password">Mot de passe</label>
                            <input type="password" name="password" id="password" placeholder="Votre mot de passe" />
                        </div>
                    </div>
                    <ul class="actions">
                        <li><input type="submit" value="Se connecter" class="primary" /></li>
                        <li><a href="register.php" class="button">Créer un compte</a></li>
                    </ul>
                </form>

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

<!-- Scripts -->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/jquery.scrollex.min.js"></script>
<script src="assets/js/browser.min.js"></script>
<script src="assets/js/breakpoints.min.js"></script>
<script src="assets/js/util.js"></script>
<script src="assets/js/main.js"></script>

</body>
</html>