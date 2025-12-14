<?php
// ================== DEBUG (يمكن تعطيله في production) ==================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ================== SESSION ==================
session_start();

// ================== AUTH CHECK ==================
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../frontoffice/login.php');
    exit();
}

// ================== CONTROLLER ==================
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/AdminController.php';

try {
    $adminController = new AdminController();

    // Retourne un tableau
    $users = $adminController->getAllUsersArray();

    $totalUsers   = count($users);
    $pendingUsers = 0;
    $approvedUsers = 0;
    $blockedUsers = 0;

    foreach ($users as $user) {
        switch ($user['status'] ?? '') {
            case 'en attente':
                $pendingUsers++;
                break;
            case 'actif':
                $approvedUsers++;
                break;
            case 'suspendu':
                $blockedUsers++;
                break;
        }
    }
} catch (Exception $e) {
    die("Erreur: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SafeSpace - Tableau de Bord Admin</title>

    <!-- Fonts -->
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">

    <!-- Styles -->
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">

<div id="wrapper">

<!-- ================== SIDEBAR ================== -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <div class="sidebar-brand d-flex align-items-center justify-content-center">
        <img src="assets/logo.png" alt="SafeSpace" style="height:40px">
        <div class="sidebar-brand-text mx-3">SafeSpace <sup>Admin</sup></div>
    </div>

    <hr class="sidebar-divider my-0">

    <li class="nav-item active">
        <a class="nav-link" href="index.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Gestion</div>

    <li class="nav-item">
        <a class="nav-link" href="users_list.php">
            <i class="fas fa-fw fa-users"></i>
            <span>Utilisateurs</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Navigation</div>

    <li class="nav-item">
        <a class="nav-link" href="../frontoffice/index.php">
            <i class="fas fa-fw fa-globe"></i>
            <span>Site Public</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="../frontoffice/profile.php">
            <i class="fas fa-fw fa-user"></i>
            <span>Mon Profil</span>
        </a>
    </li>

</ul>
<!-- ================== END SIDEBAR ================== -->

<div id="content-wrapper" class="d-flex flex-column">
<div id="content">

<!-- ================== TOPBAR ================== -->
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow">
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                    <?= htmlspecialchars($_SESSION['fullname'] ?? 'Admin') ?>
                </span>
                <i class="fas fa-user-shield"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow">
                <a class="dropdown-item" href="../frontoffice/profile.php">
                    <i class="fas fa-user mr-2"></i> Profil
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="../frontoffice/logout.php">
                    <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                </a>
            </div>
        </li>
    </ul>
</nav>

<!-- ================== CONTENT ================== -->
<div class="container-fluid">

<h1 class="h3 mb-4 text-gray-800">Tableau de Bord Administrateur</h1>

<div class="row">

<!-- TOTAL -->
<div class="col-xl-3 col-md-6 mb-4">
<div class="card border-left-primary shadow h-100 py-2">
<div class="card-body">
<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Utilisateurs</div>
<div class="h5 mb-0 font-weight-bold"><?= $totalUsers ?></div>
</div>
</div>
</div>

<!-- APPROVED -->
<div class="col-xl-3 col-md-6 mb-4">
<div class="card border-left-success shadow h-100 py-2">
<div class="card-body">
<div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approuvés</div>
<div class="h5 mb-0 font-weight-bold"><?= $approvedUsers ?></div>
</div>
</div>
</div>

<!-- PENDING -->
<div class="col-xl-3 col-md-6 mb-4">
<div class="card border-left-warning shadow h-100 py-2">
<div class="card-body">
<div class="text-xs font-weight-bold text-warning text-uppercase mb-1">En attente</div>
<div class="h5 mb-0 font-weight-bold"><?= $pendingUsers ?></div>
</div>
</div>
</div>

<!-- BLOCKED -->
<div class="col-xl-3 col-md-6 mb-4">
<div class="card border-left-danger shadow h-100 py-2">
<div class="card-body">
<div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Bloqués</div>
<div class="h5 mb-0 font-weight-bold"><?= $blockedUsers ?></div>
</div>
</div>
</div>

</div>
</div>
</div>

<!-- FOOTER -->
<footer class="sticky-footer bg-white">
<div class="container my-auto text-center">
<span>© SafeSpace <?= date('Y') ?></span>
</div>
</footer>

</div>
</div>

<script src="assets/vendor/jquery/jquery.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sb-admin-2.min.js"></script>

</body>
</html>
