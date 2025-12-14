<?php
// ================== SESSION ==================
session_start();

<<<<<<< HEAD
require_once __DIR__ . '/../../controller/admincontroller.php';

// Vérifier si l'admin est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
=======
// ================== AUTH ==================
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
>>>>>>> af8b4baf22b0b6e35827106fed7e959ed54c3093
    header('Location: ../frontoffice/login.php');
    exit();
}

// ================== INCLUDE ==================
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/AdminController.php';

// ================== CONTROLLER ==================
$adminController = new AdminController();

// ================== ACTIONS ==================
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
            break;
    }

    header('Location: users_list.php' . (!empty($_GET['search']) ? '?search=' . urlencode($_GET['search']) : ''));
    exit();
}

// ================== EXPORT EXCEL ==================
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $users = $adminController->getAllUsers();

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=utilisateurs_safespace_" . date('Y-m-d') . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "<table border='1'>
            <tr>
                <th>ID</th>
                <th>Nom complet</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Statut</th>
            </tr>";

    foreach ($users as $u) {
        echo "<tr>
                <td>{$u->getId()}</td>
                <td>{$u->getNom()}</td>
                <td>{$u->getEmail()}</td>
                <td>{$u->getRole()}</td>
                <td>{$u->getStatus()}</td>
              </tr>";
    }

    echo "</table>";
    exit();
}

// ================== SEARCH ==================
$search = $_GET['search'] ?? '';
$allUsers = $adminController->getAllUsers();

$users = $allUsers;
if ($search !== '') {
    $users = array_filter($allUsers, function ($u) use ($search) {
        return stripos($u->getNom(), $search) !== false ||
               stripos($u->getEmail(), $search) !== false ||
               stripos($u->getRole(), $search) !== false ||
               stripos($u->getStatus(), $search) !== false;
    });
}

$totalAllUsers = count($allUsers);
$filteredCount = count($users);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>SafeSpace – Gestion des Utilisateurs</title>

    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="assets/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        .role-admin { color:#e74a3b;font-weight:bold }
        .role-conseilleur { color:#36b9cc;font-weight:bold }
        .role-membre { color:#2e59d9;font-weight:bold }
        .status-approved { color:#1cc88a;font-weight:bold }
        .status-pending { color:#f6c23e;font-weight:bold }
        .status-blocked { color:#e74a3b;font-weight:bold }
    </style>
</head>

<body id="page-top">
<div id="wrapper">

<!-- SIDEBAR -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion">
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
        <i class="fas fa-shield-alt"></i>
        <span class="mx-2">SafeSpace <sup>Admin</sup></span>
    </a>

    <hr class="sidebar-divider">

    <li class="nav-item active">
        <a class="nav-link" href="users_list.php">
            <i class="fas fa-users"></i>
            <span>Utilisateurs</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="posts_list.php">
            <i class="fas fa-comments"></i>
            <span>Posts</span>
        </a>
    </li>
</ul>

<div id="content-wrapper" class="d-flex flex-column">
<div id="content">

<nav class="navbar navbar-light bg-white shadow mb-4">
    <span class="ml-auto mr-3"><?= htmlspecialchars($_SESSION['fullname'] ?? 'Admin') ?></span>
    <a href="../frontoffice/logout.php" class="btn btn-sm btn-danger">Déconnexion</a>
</nav>

<div class="container-fluid">

<h1 class="h3 mb-4 text-gray-800">
    Utilisateurs
    <span class="badge badge-primary"><?= $totalAllUsers ?> total</span>
    <?php if ($search): ?>
        <span class="badge badge-success"><?= $filteredCount ?> résultat(s)</span>
    <?php endif; ?>
</h1>

<table class="table table-bordered" id="dataTable">
<thead>
<tr>
    <th>ID</th>
    <th>Nom</th>
    <th>Email</th>
    <th>Rôle</th>
    <th>Statut</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>
<?php foreach ($users as $u): ?>
<tr>
    <td><?= $u->getId() ?></td>
    <td><?= htmlspecialchars($u->getNom()) ?></td>
    <td><?= htmlspecialchars($u->getEmail()) ?></td>
    <td><?= htmlspecialchars($u->getRole()) ?></td>
    <td><?= htmlspecialchars($u->getStatus()) ?></td>
    <td>
        <a href="?action=approve&id=<?= $u->getId() ?>" class="btn btn-success btn-sm">✔</a>
        <a href="?action=block&id=<?= $u->getId() ?>" class="btn btn-warning btn-sm">🚫</a>
        <a href="?action=delete&id=<?= $u->getId() ?>" class="btn btn-danger btn-sm">🗑</a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

</div>
</div>
</div>
</div>

<script src="assets/vendor/jquery/jquery.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="assets/vendor/datatables/dataTables.bootstrap4.min.js"></script>

<script>
$(function(){
    $('#dataTable').DataTable({
        language:{ url:'//cdn.datatables.net/plug-ins/1.10.25/i18n/French.json' }
    });
});
</script>

</body>
</html>
