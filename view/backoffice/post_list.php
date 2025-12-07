<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/AdminController.php';
session_start();

/*// Vérifier si l'admin est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../frontoffice/login.php');
    exit();
}*/

$adminController = new AdminController();

include '../../Controller/PostC.php';

$pc = new PostC();
$list_Post = $pc->listPost();

// Gestion des actions
if (isset($_GET['action2'], $_GET['id'])) {
    $id = intval($_GET['id']);
    $action2 = $_GET['action2'];

    switch ($action2) {
        case 'approve':
            $pc->ProuverPost($id);
            break;
        case 'block':
            $pc->BlockPost($id);
            break;
    }

    header("Location: post_list.php");
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

    <?php if (!empty($list_Post)): ?>
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <tr>
            <th style="padding: 10px; background: #34495e; color: white;">ID</th>
            <th style="padding: 10px; background: #34495e; color: white;">Author</th>
            <th style="padding: 10px; background: #34495e; color: white;">Message</th>
            <th style="padding: 10px; background: #34495e; color: white;">image</th>
            <th style="padding: 10px; background: #34495e; color: white;">Statut</th>
        </tr>
        <?php foreach($list_Post as $post): ?>
        <tr>
            <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($post['id']) ?></td>
            <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($post['author']) ?></td>
            <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($post['message']) ?></td>
            <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($post['image']) ?></td>
            <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($post['status']) ?></td>

            <td style="padding: 10px; border: 1px solid #ddd;">
                <?php if ($post['status'] !== 'approved'): ?>
                    <a href="post_list.php?action2=approve&id=<?= $post['id'] ?>" style="color: blue;">Approuver</a> |
                <?php endif; ?>
                <?php if ($post['status'] !== 'blocked'): ?>
                    <a href="post_list.php?action2=block&id=<?= $post['id'] ?>" style="color: orange;">Bloquer</a> |
                <?php endif; ?>
                <a href="deletePost.php?&id=<?= $post['id'] ?>" style="color: red;" onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</a>

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