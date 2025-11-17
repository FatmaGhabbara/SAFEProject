<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/amincontroller.php';
session_start();

// 🔐 Vérifier si l'admin est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: frontoffice/login.php');
    exit();
}

$admin = new AdminController();

// 🔄 Gestion des actions : approuver, bloquer, supprimer
if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    switch ($action) {
        case 'approve':
            $admin->approveUser($id);
            break;
        case 'block':
            $admin->blockUser($id);
            break;
        case 'delete':
            $admin->deleteUser($id);
            break;
    }

    header("Location: users_list.php");
    exit();
}

// 📋 Récupérer tous les utilisateurs
$users = $admin->getAllUsers();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des utilisateurs - Admin</title>
    <link rel="stylesheet" href="frontoffice/assets/css/main.css">
</head>
<body>

<header>
    <h1>Admin SafeSpace - Utilisateurs</h1>
    <nav>
        <a href="backoffice/index.php">Dashboard</a> |
        <a href="frontoffice/index.php">Retour au site</a> |
    
    </nav>
</header>

<section>
    <h2>Liste des utilisateurs</h2>
    <?php if (!empty($users)): ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Nom complet</th>
            <th>Email</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php foreach($users as $user): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['fullname']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['status']) ?></td>
            <td>
                <a href="user_list.php?action=approve&id=<?= $user['id'] ?>">Approuver</a> |
                <a href="user_list.php?action=block&id=<?= $user['id'] ?>">Bloquer</a> |
                <a href="user_list.php?action=delete&id=<?= $user['id'] ?>" onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
        <p>Aucun utilisateur trouvé.</p>
    <?php endif; ?>
</section>

</body>
</html>
