<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/admincontroller.php';
session_start();

$controller = new AdminController();

// Récupération de tous les utilisateurs
$users = $controller->getAllUsers();

// Gestion des actions si on passe via GET
if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    switch ($action) {
        case 'approve':
            $controller->approveUser($id);
            break;
        case 'block':
            $controller->blockUser($id);
            break;
        case 'delete':
            $controller->deleteUser($id);
            break;
    }

    // Redirection pour éviter le repost du formulaire
    header("Location: user_list.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion des utilisateurs | SafeSpace</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="assets/css/main.css" />
    <noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
</head>
<body class="is-preload">

<div id="page-wrapper">

    <!-- Header -->
    <header id="header">
        <h1><a href="frontoffice/index.php">Admin SafeSpace</a></h1>
        <nav>
            <a href="#menu">Menu</a>
        </nav>
    </header>

    <!-- Menu -->
    <nav id="menu">
        <div class="inner">
            <h2>Menu</h2>
            <ul class="links">
                <li><a href="backoffice/index.php">Home</a></li>
                <li><a href="user_list.php">Utilisateurs</a></li>
                <li><a href="frontoffice/profile.php">Profil</a></li>
                <li><a href="frontoffice/logout.php">Déconnexion</a></li>
            </ul>
            <a href="#" class="close">Close</a>
        </div>
    </nav>

    <!-- Contenu -->
    <section id="wrapper">
        <header>
            <div class="inner">
                <h2>Liste des utilisateurs</h2>
                <p>Gérez les comptes, les statuts et les rôles.</p>
            </div>
        </header>

        <div class="wrapper">
            <div class="inner">

                <section>
                    <a href="add_user.php" class="button primary">Ajouter un utilisateur</a>
                    <table>
                        <thead>
                            <tr>
                                <th>Nom complet</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['fullname']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars($user['role']) ?></td>
                                    <td>
                                        <?php if ($user['status'] === 'pending'): ?>
                                            <span style="color: orange;">En attente</span>
                                        <?php elseif ($user['status'] === 'approved'): ?>
                                            <span style="color: green;">Actif</span>
                                        <?php else: ?>
                                            <span style="color: red;">Bloqué</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="view_user.php?id=<?= $user['id'] ?>" class="button small">Voir</a>
                                        <a href="edit_user.php?id=<?= $user['id'] ?>" class="button small">Modifier</a>

                                        <?php if ($user['status'] === 'pending'): ?>
                                            <a href="user_list.php?action=approve&id=<?= $user['id'] ?>" class="button small success">Accepter</a>
                                        <?php elseif ($user['status'] === 'approved'): ?>
                                            <a href="user_list.php?action=block&id=<?= $user['id'] ?>" class="button small alert">Bloquer</a>
                                        <?php elseif ($user['status'] === 'blocked'): ?>
                                            <a href="user_list.php?action=approve&id=<?= $user['id'] ?>" class="button small success">Débloquer</a>
                                        <?php endif; ?>

                                        <a href="user_list.php?action=delete&id=<?= $user['id'] ?>" class="button small alert" onclick="return confirm('Supprimer cet utilisateur ?');">Supprimer</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>

            </div>
        </div>
    </section>

    <!-- Footer -->
    <section id="footer">
        <div class="inner">
            <h2 class="major">SafeSpace</h2>
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
