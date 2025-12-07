<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/AdminController.php';
session_start();
$adminController = new AdminController();
include '../../Controller/PostC.php';
include '../../Controller/CommentC.php';
include '../../Controller/RespondC.php';
$cc = new CommentC();
$rc = new RespondC();
$pc = new PostC();
$list_Post = $pc->listPost();
// Gestion des actions pour users
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
            $pc->deleteUserPost($id);
            $cc-> deleteComPost($id);
            $rc-> deleteResComPost($id);
            break;
    }
    header("Location: users_list.php");
    exit();
}
// Gestion des actions pour posts
if (isset($_GET['post_action'], $_GET['post_id'])) {
    $id = intval($_GET['post_id']);
    $action = $_GET['post_action'];
    switch ($action) {
        case 'approve':
            $pc->ProuverPost($id); // Assuming this is the approve method; adjust if named differently
            break;
        case 'block':
            $pc->BlockPost($id);
            break;
    }
    header("Location: users_list.php");
    exit();
}
$users = $adminController->getAllUsers();

// Precompute posts by user to avoid repeated loops
$postsByUser = [];
foreach ($users as $user) {
    $uid = $user['id'];
    $uemail = $user['email'];
    $postsByUser[$uid] = [];
    foreach ($list_Post as $post) {
        if ($uid == ($post['id_user'] ?? '') || $uemail == ($post['author'] ?? '')) {
            $postsByUser[$uid][] = $post;
        }
    }
}
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
    <h2>Liste des utilisateurs avec leur Posts</h2>
   
    <?php if (!empty($users)): ?>
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <tr>
            <th style="padding: 10px; background: #34495e; color: white;">ID</th>
            <th style="padding: 10px; background: #34495e; color: white;">Nom complet</th>
            <th style="padding: 10px; background: #34495e; color: white;">Email</th>
            <th style="padding: 10px; background: #34495e; color: white;">Rôle</th>
            <th style="padding: 10px; background: #34495e; color: white;">Statut</th>
            <th style="padding: 10px; background: #34495e; color: white;">Actions (Utilisateur)</th>
            <th style="padding: 10px; background: #34495e; color: white;">ID Post</th>
            <th style="padding: 10px; background: #34495e; color: white;">Title</th>
            <th style="padding: 10px; background: #34495e; color: white;">Message</th>
            <th style="padding: 10px; background: #34495e; color: white;">Image</th>
            <th style="padding: 10px; background: #34495e; color: white;">Statut</th>
            <th style="padding: 10px; background: #34495e; color: white;">Actions (Post)</th>
        </tr>
        <?php foreach($users as $user): ?>
            <?php
            $uid = $user['id'];
            $userPosts = $postsByUser[$uid] ?? [];
            $numPosts = count($userPosts);
            $rowspan = $numPosts > 0 ? $numPosts : 1;
            $first = true;
            if ($numPosts == 0) {
                // User with no posts
                echo '<tr>';
                echo '<td>' . htmlspecialchars($user['id']) . '</td>';
                echo '<td>' . htmlspecialchars($user['fullname']) . '</td>';
                echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                echo '<td>';
                $roleIcons = [
                    'admin' => '👑',
                    'conseilleur' => '💼',
                    'membre' => '👤'
                ];
                echo ($roleIcons[$user['role']] ?? '👤') . ' ' . htmlspecialchars($user['role']);
                echo '</td>';
                echo '<td>' . htmlspecialchars($user['status']) . '</td>';
                echo '<td>';
                if ($user['status'] !== 'approved') {
                    echo '<a href="users_list.php?action=approve&id=' . $user['id'] . '" style="color: blue;">Approuver</a> |';
                }
                if ($user['status'] !== 'blocked') {
                    echo '<a href="users_list.php?action=block&id=' . $user['id'] . '" style="color: orange;">Bloquer</a> |';
                }
                echo '<a href="users_list.php?action=delete&id=' . $user['id'] . '" style="color: red;" onclick="return confirm(\'Supprimer cet utilisateur ?\')">Supprimer</a>';
                echo '</td>';
                echo '<td colspan="6" style="text-align: center;">Aucun post</td>';
                echo '</tr>';
            } else {
                // User with posts
                foreach ($userPosts as $post) {
                    echo '<tr>';
                    if ($first) {
                        echo '<td rowspan="' . $rowspan . '">' . htmlspecialchars($user['id']) . '</td>';
                        echo '<td rowspan="' . $rowspan . '">' . htmlspecialchars($user['fullname']) . '</td>';
                        echo '<td rowspan="' . $rowspan . '">' . htmlspecialchars($user['email']) . '</td>';
                        echo '<td rowspan="' . $rowspan . '">';
                        $roleIcons = [
                            'admin' => '👑',
                            'conseilleur' => '💼',
                            'membre' => '👤'
                        ];
                        echo ($roleIcons[$user['role']] ?? '👤') . ' ' . htmlspecialchars($user['role']);
                        echo '</td>';
                        echo '<td rowspan="' . $rowspan . '">' . htmlspecialchars($user['status']) . '</td>';
                        echo '<td rowspan="' . $rowspan . '">';
                        if ($user['status'] !== 'approved') {
                            echo '<a href="users_list.php?action=approve&id=' . $user['id'] . '" style="color: blue;">Approuver</a> |';
                        }
                        if ($user['status'] !== 'blocked') {
                            echo '<a href="users_list.php?action=block&id=' . $user['id'] . '" style="color: orange;">Bloquer</a> |';
                        }
                        echo '<a href="users_list.php?action=delete&id=' . $user['id'] . '" style="color: red;" onclick="return confirm(\'Supprimer cet utilisateur ?\')">Supprimer</a>';
                        echo '</td>';
                        $first = false;
                    }
                    echo '<td>' . htmlspecialchars($post['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($post['author'] ?? 'N/A') . '</td>';
                    echo '<td>' . htmlspecialchars(substr($post['message'] ?? '', 0, 100)) . (strlen($post['message'] ?? '') > 100 ? '...' : '') . '</td>';
                    echo '<td>';
                    if (!empty($post['image'])) {
                        echo '<img src="' . htmlspecialchars($post['image']) . '" alt="Post image" style="max-width: 100px; max-height: 100px;">';
                    } else {
                        echo 'Aucune image';
                    }
                    echo '</td>';
                    echo '<td>' . htmlspecialchars($post['status'] ?? 'N/A') . '</td>';
                    echo '<td>';
                    if (isset($post['status']) && $post['status'] !== 'approved') {
                        echo '<a href="users_list.php?post_action=approve&post_id=' . $post['id'] . '" style="color: blue;">Approuver</a> |';
                    }
                    if (isset($post['status']) && $post['status'] !== 'blocked') {
                        echo '<a href="users_list.php?post_action=block&post_id=' . $post['id'] . '" style="color: orange;">Bloquer</a> |';
                    }
                    echo '<a href="deletePost.php?id=' . $post['id'] . '" style="color: red;" onclick="return confirm(\'Supprimer ce post ?\')">Supprimer</a>';
                    echo '</td>';
                    echo '</tr>';
                }
            }
            ?>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
        <p>Aucun utilisateur trouvé.</p>
    <?php endif; ?>
</div>
</body>
</html>