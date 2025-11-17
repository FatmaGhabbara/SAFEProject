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
        $auth = new AuthController();
        $result = $auth->login($email, $password);
        if ($result === true) {
            if ($_SESSION['role'] === 'admin') header("Location: ../backoffice/index.php");
            else header("Location: profile.php");
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
</head>
<body>

<h2>Connexion</h2>

<?php if(!empty($errors)): ?>
    <ul style="color:red;">
        <?php foreach($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="">
    <label>Email</label>
    <input type="text" name="email" placeholder="exemple@mail.com">
    <br>
    <label>Mot de passe</label>
    <input type="password" name="password" placeholder="••••••">
    <br>
    <input type="submit" value="Connexion">
</form>

<p><a href="register.php">Créer un compte</a></p>

</body>
</html>
