<?php
// ================== SESSION ==================
session_start();

// ================== AUTH ==================
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../frontoffice/login.php');
    exit();
}

// ================== INCLUDES ==================
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/AdminController.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/PostC.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/CommentC.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/RespondC.php';

// ================== CONTROLLERS ==================
$adminController = new AdminController();
$pc = new PostC();
$cc = new CommentC();
$rc = new RespondC();

// ================== DATA ==================
$list_Post = $pc->listPost();
$allUsers  = $adminController->getAllUsers();

// ================== USER ACTIONS ==================
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int) $_GET['id'];

    switch ($_GET['action']) {
        case 'approve':
            $adminController->approveUser($id);
            break;

        case 'block':
            $adminController->blockUser($id);
            break;

        case 'delete':
            $adminController->deleteUser($id);
            $pc->deleteUserPost($id);
            $cc->deleteComPost($id);
            $rc->deleteResComPost($id);
            break;
    }

    header('Location: posts_list.php');
    exit();
}

// ================== POST ACTIONS ==================
if (isset($_GET['post_action'], $_GET['post_id'])) {
    $postId = (int) $_GET['post_id'];

    switch ($_GET['post_action']) {
        case 'approve':
            $pc->ProuverPost($postId);
            break;

        case 'block':
            $pc->BlockPost($postId);
            break;
    }

    header('Location: posts_list.php');
    exit();
}

// ================== SEARCH ==================
$search = $_GET['search'] ?? '';
$users = $allUsers;

if ($search !== '') {
    $users = array_filter($allUsers, function ($u) use ($search) {
        return stripos($u->getNom(), $search) !== false
            || stripos($u->getEmail(), $search) !== false
            || stripos($u->getRole(), $search) !== false
            || stripos($u->getStatus(), $search) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>SafeSpace – Gestion Utilisateurs & Posts</title>

    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="assets/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body id="page-top">

<div id="wrapper">

<!-- ================== SIDEBAR ================== -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
        <div class="sidebar-brand-icon"><i class="fas fa-shield-alt"></i></div>
        <div class="sidebar-brand-text mx-3">SafeSpace <sup>Admin</sup></div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item">
        <a class="nav-link" href="index.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Gestion</div>

    <li class="nav-item active">
        <a class="nav-link" href="posts_list.php">
            <i class="fas fa-fw fa-comment"></i>
            <span>Utilisateurs & Posts</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Navigation</div>

    <li class="nav-item">
        <a class="nav-link" href="../frontoffice/index.php">
            <i class="fas fa-globe"></i>
            <span>Site public</span>
        </a>
    </li>

</ul>
<!-- ================== END SIDEBAR ================== -->

<div id="content-wrapper" class="d-flex flex-column">
<div id="content">

<!-- ================== TOPBAR ================== -->
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow">
    <span class="ml-auto mr-3 text-gray-600">
        <?= htmlspecialchars($_SESSION['fullname'] ?? 'Admin') ?>
    </span>
    <a href="../frontoffice/logout.php" class="btn btn-sm btn-danger">
        <i class="fas fa-sign-out-alt"></i>
    </a>
</nav>

<!-- ================== CONTENT ================== -->
<div class="container-fluid">

<h1 class="h3 mb-4 text-gray-800">Gestion des utilisateurs & posts</h1>

<table class="table table-bordered" id="dataTable">
<thead>
<tr>
    <th>ID</th>
    <th>Utilisateur</th>
    <th>Email</th>
    <th>Rôle</th>
    <th>Statut</th>
    <th>Actions utilisateur</th>
    <th>ID Post</th>
    <th>Message</th>
    <th>Statut Post</th>
    <th>Actions Post</th>
</tr>
</thead>

<tbody>
<?php foreach ($users as $user): ?>
<?php
$userPosts = array_filter($list_Post, fn($p) =>
    ($p['id_user'] ?? null) == $user->getId()
);
$rowspan = max(1, count($userPosts));
$first = true;
?>
<?php if (empty($userPosts)): ?>
<tr>
    <td><?= $user->getId() ?></td>
    <td><?= htmlspecialchars($user->getNom()) ?></td>
    <td><?= htmlspecialchars($user->getEmail()) ?></td>
    <td><?= htmlspecialchars($user->getRole()) ?></td>
    <td><?= htmlspecialchars($user->getStatus()) ?></td>
    <td>
        <a href="?action=approve&id=<?= $user->getId() ?>" class="btn btn-success btn-sm">✔</a>
        <a href="?action=block&id=<?= $user->getId() ?>" class="btn btn-warning btn-sm">🚫</a>
        <a href="?action=delete&id=<?= $user->getId() ?>" class="btn btn-danger btn-sm">🗑</a>
    </td>
    <td colspan="4">Aucun post</td>
</tr>
<?php else: ?>
<?php foreach ($userPosts as $post): ?>
<tr>
<?php if ($first): ?>
    <td rowspan="<?= $rowspan ?>"><?= $user->getId() ?></td>
    <td rowspan="<?= $rowspan ?>"><?= htmlspecialchars($user->getNom()) ?></td>
    <td rowspan="<?= $rowspan ?>"><?= htmlspecialchars($user->getEmail()) ?></td>
    <td rowspan="<?= $rowspan ?>"><?= htmlspecialchars($user->getRole()) ?></td>
    <td rowspan="<?= $rowspan ?>"><?= htmlspecialchars($user->getStatus()) ?></td>
    <td rowspan="<?= $rowspan ?>">
        <a href="?action=approve&id=<?= $user->getId() ?>" class="btn btn-success btn-sm">✔</a>
        <a href="?action=block&id=<?= $user->getId() ?>" class="btn btn-warning btn-sm">🚫</a>
        <a href="?action=delete&id=<?= $user->getId() ?>" class="btn btn-danger btn-sm">🗑</a>
    </td>
<?php $first = false; endif; ?>

<td><?= $post['id'] ?></td>
<td><?= htmlspecialchars(substr($post['message'] ?? '', 0, 60)) ?></td>
<td><?= htmlspecialchars($post['status'] ?? '') ?></td>
<td>
    <a href="?post_action=approve&post_id=<?= $post['id'] ?>" class="btn btn-success btn-sm">✔</a>
    <a href="?post_action=block&post_id=<?= $post['id'] ?>" class="btn btn-warning btn-sm">🚫</a>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
<?php endforeach; ?>
</tbody>
</table>

</div>
</div>
</div>

</div>

<!-- ================== SCRIPTS ================== -->
<script src="assets/vendor/jquery/jquery.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="assets/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="assets/js/sb-admin-2.min.js"></script>

<script>
$(function () {
    $('#dataTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/French.json' }
    });
});
</script>

</body>
</html>