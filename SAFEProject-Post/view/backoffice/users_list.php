<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/AdminController.php';
session_start();

// Vérifier si l'admin est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../frontoffice/login.php');
    exit();
}

$adminController = new AdminController();

// Gestion des actions
if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    switch ($action) {
        case 'approve':
            $adminController->approveUser($id);
            break;
        case 'block':
            $adminController->blockUser($id);
            break;
        case 'delete':
            $adminController->deleteUser($id);
            break;
    }

    header("Location: users_list.php");
    exit();
}

$users = $adminController->getAllUsers();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des utilisateurs - Admin</title>
    <link rel="stylesheet" href="../frontoffice/assets/css/main.css">
</head>
<body>

<header>
    <h1>SafeSpace - Administration</h1>
    <nav>
        <a href="index.php">Dashboard</a> |
        <a href="users_list.php">Gérer les utilisateurs</a> |
        <a href="../frontoffice/index.php">Site public</a> |

    </nav>
</header>

<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <h2>Liste des utilisateurs</h2>

    <?php if (!empty($users)): ?>
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <tr>
            <th style="padding: 10px; background: #34495e; color: white;">ID</th>
            <th style="padding: 10px; background: #34495e; color: white;">Nom complet</th>
            <th style="padding: 10px; background: #34495e; color: white;">Email</th>
            <th style="padding: 10px; background: #34495e; color: white;">Rôle</th>
            <th style="padding: 10px; background: #34495e; color: white;">Statut</th>
            <th style="padding: 10px; background: #34495e; color: white;">Actions</th>
        </tr>
        <?php foreach($users as $user): ?>
        <tr>
            <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($user['id']) ?></td>
            <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($user['fullname']) ?></td>
            <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($user['email']) ?></td>
            <td style="padding: 10px; border: 1px solid #ddd;">
                <?php
                $roleIcons = [
                    'admin' => '👑',
                    'conseilleur' => '💼', 
                    'membre' => '👤'
                ];
                echo ($roleIcons[$user['role']] ?? '👤') . ' ' . htmlspecialchars($user['role']);
                ?>
            </td>
            <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($user['status']) ?></td>
            <td style="padding: 10px; border: 1px solid #ddd;">
                <?php if ($user['status'] !== 'approved'): ?>
                    <a href="users_list.php?action=approve&id=<?= $user['id'] ?>" style="color: blue;">Approuver</a> |
                <?php endif; ?>
                <?php if ($user['status'] !== 'blocked'): ?>
                    <a href="users_list.php?action=block&id=<?= $user['id'] ?>" style="color: orange;">Bloquer</a> |
                <?php endif; ?>
                <a href="users_list.php?action=delete&id=<?= $user['id'] ?>" style="color: red;" onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
        <p>Aucun utilisateur trouvé.</p>
    <?php endif; ?>
</div>

</body>
</html>