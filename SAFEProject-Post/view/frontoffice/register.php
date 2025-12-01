<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/AuthController.php';
session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']); // ✅ AJOUT: Récupérer le rôle du formulaire

    if (!$firstname || !$lastname) $errors[] = "Nom et prénom requis.";
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email valide requis.";
    if (!$password || strlen($password) < 6) $errors[] = "Mot de passe requis (minimum 6 caractères).";
    
    // ✅ AJOUT: Validation du rôle
    $allowedRoles = ['membre', 'conseilleur'];
    if (!in_array($role, $allowedRoles)) {
        $role = 'membre'; // Valeur par défaut sécurisée
    }

    if (empty($errors)) {
        $authController = new AuthController();
        // ✅ MODIFICATION: Ajouter le rôle en paramètre
        $result = $authController->register($firstname, $lastname, $email, $password, $role);
        if ($result === true) {
            $_SESSION['success'] = "Inscription réussie ! Votre compte est en attente de validation.";
            header("Location: login.php");
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
    <title>Inscription - SafeSpace</title>
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
                <h2>Inscription</h2>
                <p>Rejoignez la communauté SafeSpace</p>
            </div>
        </header>

        <!-- Content -->
        <div class="wrapper">
            <div class="inner">

                <?php if(isset($_SESSION['success'])): ?>
                    <div class="success">
                        <p><?= htmlspecialchars($_SESSION['success']) ?></p>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if(!empty($errors)): ?>
                    <div class="error">
                        <?php foreach($errors as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="fields">
                        <div class="field half">
                            <label for="firstname">Prénom</label>
                            <input type="text" name="firstname" id="firstname" placeholder="Votre prénom" value="<?= htmlspecialchars($_POST['firstname'] ?? '') ?>" />
                        </div>
                        <div class="field half">
                            <label for="lastname">Nom</label>
                            <input type="text" name="lastname" id="lastname" placeholder="Votre nom" value="<?= htmlspecialchars($_POST['lastname'] ?? '') ?>" />
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" placeholder="exemple@mail.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
                        </div>
                        <div class="field">
                            <label for="password">Mot de passe</label>
                            <input type="password" name="password" id="password" placeholder="Minimum 6 caractères" />
                        </div>
                      
                        <!-- ✅ CHAMP RÔLE -->
                        <div class="field">
                            <label for="role">Rôle</label>
                            <select name="role" id="role" required>
                                <option value="membre" <?= ($_POST['role'] ?? '') == 'membre' ? 'selected' : '' ?>>Membre</option>
                                <option value="conseilleur" <?= ($_POST['role'] ?? '') == 'conseilleur' ? 'selected' : '' ?>>Conseilleur</option>
                            </select>
                        </div>
                    </div>
                    <ul class="actions">
                        <li><input type="submit" value="S'inscrire" class="primary" /></li>
                        <li><a href="login.php" class="button">J'ai déjà un compte</a></li>
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