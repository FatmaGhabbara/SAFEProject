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

<<<<<<< HEAD
require_once __DIR__ . '/../../controller/admincontroller.php';
=======
// ================== CONTROLLER ==================
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/AdminController.php';
>>>>>>> af8b4baf22b0b6e35827106fed7e959ed54c3093

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

<<<<<<< HEAD
      <!-- Sidebar -->
      <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

        <!-- Sidebar Header -->
        <div class="sidebar-brand d-flex align-items-center justify-content-center" style="padding: 1rem;">
          <a class="navbar-brand nav-logo" href="index.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
            <img src="assets/logo.png" alt="SafeSpace Logo" style="height: 40px; width: auto;">
          </a>
          <div class="sidebar-brand-text mx-3 text-white">SafeSpace <sup>Admin</sup></div>
        </div>

        <!-- Divider -->
        <hr class="sidebar-divider my-0">

        <!-- Nav Item - Dashboard -->
        <li class="nav-item active">
            <a class="nav-link" href="index.php">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span></a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Heading -->
        <div class="sidebar-heading">
            Gestion
        </div>

        <!-- Nav Item - Users -->
        <li class="nav-item">
            <a class="nav-link" href="users_list.php">
                <i class="fas fa-fw fa-users"></i>
                <span>Utilisateurs</span></a>
        </li>

        <!-- Nav Item - Support Requests -->
        <li class="nav-item">
            <a class="nav-link" href="admin_support_requests.php">
                <i class="fas fa-fw fa-hands-helping"></i>
                <span>Demandes Support</span></a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Heading -->
        <div class="sidebar-heading">
            Navigation
        </div>

        <!-- Nav Item - Public Site -->
        <li class="nav-item">
            <a class="nav-link" href="../frontoffice/index.php">
                <i class="fas fa-fw fa-globe"></i>
                <span>Site Public</span></a>
        </li>

        <!-- Nav Item - Profile -->
        <li class="nav-item">
            <a class="nav-link" href="../frontoffice/profile.php">
                <i class="fas fa-fw fa-user"></i>
                <span>Mon Profil</span></a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider d-none d-md-block">

        <!-- Sidebar Toggler (Sidebar) -->
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>

      </ul>
      <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggler (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    <?= htmlspecialchars($_SESSION['fullname'] ?? 'Admin') ?>
                                </span>
                                <i class="fas fa-user-shield fa-fw"></i>
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="../frontoffice/profile.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profil
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../frontoffice/logout.php">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Déconnexion
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Tableau de Bord Administrateur</h1>
                        <a href="users_list.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-users fa-sm text-white-50"></i> Gérer les utilisateurs
                        </a>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Total Users Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Utilisateurs</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalUsers ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Approved Users Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Utilisateurs Approuvés</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $approvedUsers ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Users Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                En Attente</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pendingUsers ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Blocked Users Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Bloqués</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $blockedUsers ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-ban fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Quick Actions -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Actions Rapides</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        <a href="users_list.php" class="btn btn-primary btn-icon-split mb-3">
                                            <span class="icon text-white-50">
                                                <i class="fas fa-users"></i>
                                            </span>
                                            <span class="text">Gérer les utilisateurs</span>
                                        </a>
                                        <a href="../frontoffice/profile.php" class="btn btn-info btn-icon-split mb-3">
                                            <span class="icon text-white-50">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            <span class="text">Mon Profil</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Welcome Message -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Bienvenue Administrateur</h6>
                                </div>
                                <div class="card-body">
                                    <p>Bienvenue dans le panel d'administration de SafeSpace, <strong><?= htmlspecialchars($_SESSION['fullname'] ?? 'Admin') ?></strong> !</p>
                                    <p class="mb-0">Vous pouvez gérer les utilisateurs, approuver les inscriptions et superviser la plateforme.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; SafeSpace <?= date('Y') ?></span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->
=======
<!-- ================== SIDEBAR ================== -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
>>>>>>> af8b4baf22b0b6e35827106fed7e959ed54c3093

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
