<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/AuthController.php';
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstname = trim($_POST['firstname'] ?? '');
    $lastname  = trim($_POST['lastname'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = trim($_POST['password'] ?? '');
    $role      = trim($_POST['role'] ?? 'membre');

    // ===============================
    // Validation
    // ===============================
    if (empty($firstname) || empty($lastname)) {
        $errors[] = "Nom et prénom requis.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email valide requis.";
    }

    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Mot de passe requis (minimum 6 caractères).";
    }

    // Sécuriser le rôle
    $allowedRoles = ['membre', 'conseilleur'];
    if (!in_array($role, $allowedRoles)) {
        $role = 'membre';
    }

    // ===============================
    // Inscription
    // ===============================
    if (empty($errors)) {
        $authController = new AuthController();
        $fullname = $firstname . ' ' . $lastname;

        $result = $authController->register($fullname, $email, $password, $role);

        if ($result === true) {
            $_SESSION['success'] = "Inscription réussie ! Votre compte est en attente de validation.";
            header('Location: login.php');
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
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="assets/css/main.css">
    <noscript><link rel="stylesheet" href="assets/css/noscript.css"></noscript>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        <div class="wrapper">
            <div class="inner">

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="success">
                        <p><?= htmlspecialchars($_SESSION['success']) ?></p>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="error">
                        <?php foreach ($errors as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="post" id="registerForm">

                    <div class="fields">
                        <div class="field half">
                            <label>Prénom</label>
                            <input type="text" name="firstname" required value="<?= htmlspecialchars($_POST['firstname'] ?? '') ?>">
                        </div>

                        <div class="field half">
                            <label>Nom</label>
                            <input type="text" name="lastname" required value="<?= htmlspecialchars($_POST['lastname'] ?? '') ?>">
                        </div>

                        <div class="field">
                            <label>Email</label>
                            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>

                        <div class="field">
                            <label><i class="fas fa-lock"></i> Mot de passe <span class="ai-badge">IA</span></label>
                            <input type="password" name="password" id="password" minlength="6" required>
                            <button type="button" onclick="togglePassword()">👁</button>
                        </div>

                        <div class="field">
                            <label>Rôle</label>
                            <select name="role">
                                <option value="membre" <?= ($_POST['role'] ?? '') === 'membre' ? 'selected' : '' ?>>Membre</option>
                                <option value="conseilleur" <?= ($_POST['role'] ?? '') === 'conseilleur' ? 'selected' : '' ?>>Conseilleur</option>
                            </select>
                        </div>
                    </div>

                    <ul class="actions">
                        <li><input type="submit" value="S'inscrire" class="primary"></li>
                        <li><a href="login.php" class="button">Déjà un compte ?</a></li>
                    </ul>

                </form>

            </div>
        </div>
    </section>

    <section id="footer">
        <div class="inner">
            <p>&copy; <?= date('Y') ?> SafeSpace</p>
        </div>
    </section>

</div>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/main.js"></script>

<script>
function togglePassword() {
    const pwd = document.getElementById('password');
    pwd.type = pwd.type === 'password' ? 'text' : 'password';
}
</script>

</body>
</html>
