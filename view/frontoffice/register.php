<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/AuthController.php';
session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!$firstname || !$lastname) $errors[] = "Nom et prénom requis.";
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email valide requis.";
    if (!$password) $errors[] = "Mot de passe requis.";

    if (empty($errors)) {
        $auth = new AuthController();
        $result = $auth->register($firstname, $lastname, $email, $password);
        if ($result === true) {
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
</head>
<body>

<h2>Inscription</h2>

<?php if(!empty($errors)): ?>
    <ul style="color:red;">
        <?php foreach($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="">
    <label>Prénom</label>
    <input type="text" name="firstname" placeholder="Prénom">
    <br>
    <label>Nom</label>
    <input type="text" name="lastname" placeholder="Nom">
    <br>
    <label>Email</label>
    <input type="text" name="email" placeholder="exemple@mail.com">
    <br>
    <label>Mot de passe</label>
    <input type="password" name="password" placeholder="••••••">
    <br>
    <input type="submit" value="S'inscrire">
</form>

<p><a href="login.php">J'ai déjà un compte</a></p>

</body>
</html>
