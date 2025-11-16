<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/AuthController.php';
session_start();


// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $password_repeat = trim($_POST['password_repeat']);

    // --- Contrôles de saisie côté serveur ---
    if (empty($firstname) || empty($lastname)) {
        $errors[] = "Le prénom et le nom sont obligatoires.";
    }

    if (empty($email)) {
        $errors[] = "L’adresse e-mail est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L’adresse e-mail n’est pas valide.";
    }

    if (empty($password) || empty($password_repeat)) {
        $errors[] = "Veuillez entrer et confirmer votre mot de passe.";
    } elseif ($password !== $password_repeat) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    }

    // Si tout est bon, on tente l'inscription via le contrôleur
    if (empty($errors)) {
        $auth = new AuthController();
        $result = $auth->register($firstname, $lastname, $email, $password);

        if ($result === true) {
            $success = "Votre demande d’inscription a été envoyée. Vous recevrez un e-mail après validation par l’administrateur.";
        } else {
            $errors[] = $result;
        }
    }
}
?>

<!DOCTYPE HTML>
<html lang="fr">
<head>
    <title>Inscription | SafeSpace</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
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
                <li><a href="backoffice/user_list.php" target="_blank">Admin</a></li>
                <li><a href="frontoffice/profile.php">Profil</a></li>
                <li><a href="login.php">Connexion</a></li>
                <li><a href="register.php">Inscription</a></li>
            </ul>
            <a href="#" class="close">Fermer</a>
        </div>
    </nav>

    <!-- Wrapper -->
    <section id="wrapper">
        <header>
            <div class="inner">
                <h2>Créer un compte</h2>
                <p>Remplissez le formulaire pour créer un compte SafeSpace.</p>
            </div>
        </header>

        <div class="wrapper">
            <div class="inner">

                <!-- Messages d'erreur -->
                <?php if (!empty($errors)) : ?>
                    <div class="alert alert-danger" style="color:red; border:1px solid red; padding:10px; border-radius:8px;">
                        <ul>
                            <?php foreach ($errors as $error) : ?>
                                <li><?= htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Message de succès -->
                <?php if ($success) : ?>
                    <div class="alert alert-success" style="color:green; border:1px solid green; padding:10px; border-radius:8px;">
                        <?= htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <!-- Formulaire -->
                <section>
                    <form method="post" action="">
                        <div class="fields">
                            <div class="field half">
                                <label for="firstname">Prénom</label>
                                <input type="text" name="firstname" id="firstname" placeholder="Prénom">
                            </div>
                            <div class="field half">
                                <label for="lastname">Nom</label>
                                <input type="text" name="lastname" id="lastname" placeholder="Nom">
                            </div>
                            <div class="field">
                                <label for="email">Adresse e-mail</label>
                                <input type="text" name="email" id="email" placeholder="exemple@email.com">
                            </div>
                            <div class="field half">
                                <label for="password">Mot de passe</label>
                                <input type="password" name="password" id="password" placeholder="••••••••">
                            </div>
                            <div class="field half">
                                <label for="password_repeat">Confirmer le mot de passe</label>
                                <input type="password" name="password_repeat" id="password_repeat" placeholder="••••••••">
                            </div>
                        </div>
                        <ul class="actions">
                            <li><input type="submit" value="Créer un compte" class="primary" /></li>
                        </ul>
                    </form>

                    <p style="margin-top: 1em;">
                        <a href="login.php">Vous avez déjà un compte ? Connectez-vous</a>
                    </p>
                </section>

            </div>
        </div>
    </section>

    <!-- Footer -->
    <section id="footer">
        <div class="inner">
            <h2 class="major">SafeSpace</h2>
            <p>Protégeons ensemble, agissons avec bienveillance.</p>
            <ul class="contact">
                <li class="icon solid fa-envelope"><a href="#">contact@safespace.tn</a></li>
                <li class="icon brands fa-facebook-f"><a href="#">facebook.com/safespace</a></li>
                <li class="icon brands fa-instagram"><a href="#">instagram.com/safespace</a></li>
            </ul>
            <ul class="copyright">
                <li>&copy; 2025 SafeSpace. Tous droits réservés.</li>
                <li>Design basé sur : <a href="http://html5up.net">HTML5 UP</a></li>
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
