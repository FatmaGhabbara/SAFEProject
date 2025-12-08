<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Utilisateur - SAFEProject Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/support-module.css">
    <link rel="stylesheet" href="../css/sb-admin-2.min.css">
</head>
<body id="page-top">

<?php
session_start();

require_once '../../../config.php';
require_once '../../../controller/helpers.php';
require_once '../../../model/User.php';

// Check admin access
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../frontoffice/login.php');
    exit();
}

// Get user ID from URL
$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($userId === 0) {
    setFlashMessage('Utilisateur invalide.', 'error');
    header('Location: users_list.php');
    exit();
}

// Load user
$user = new User($userId);

if (!$user->getId()) {
    setFlashMessage('Utilisateur introuvable.', 'error');
    header('Location: users_list.php');
    exit();
}

// Get user's support requests
$userRequests = findSupportRequestsByUser($userId);

// Get flash message
$flash = getFlashMessage();
?>

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="support_requests.php">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="sidebar-brand-text mx-3">SAFEProject</div>
            </a>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">Support Psychologique</div>

            <li class="nav-item">
                <a class="nav-link" href="support_requests.php">
                    <i class="fas fa-fw fa-inbox"></i>
                    <span>Demandes de Support</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="counselors_list.php">
                    <i class="fas fa-fw fa-user-md"></i>
                    <span>Conseillers</span>
                </a>
            </li>

            <li class="nav-item active">
                <a class="nav-link" href="users_list.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Utilisateurs</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="counselor_stats.php">
                    <i class="fas fa-fw fa-chart-area"></i>
                    <span>Statistiques</span>
                </a>
            </li>

            <hr class="sidebar-divider d-none d-md-block">
        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <h1 class="h3 mb-0 text-gray-800">Profil de l'utilisateur</h1>

                    <ul class="navbar-nav ml-auto">
                        <div class="topbar-divider d-none d-sm-block"></div>
                        <li class="nav-item">
                            <a class="nav-link" href="users_list.php">
                                <i class="fas fa-arrow-left"></i>
                                <span class="d-none d-lg-inline">Retour à la liste</span>
                            </a>
                        </li>
                        <div class="topbar-divider d-none d-sm-block"></div>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-user"></i>
                                <span class="d-none d-lg-inline"><?php echo isset($_SESSION['role']) && $_SESSION['role'] === 'admin' ? 'admin' : (isset($_SESSION['prenom']) ? secureOutput($_SESSION['prenom'] . ' ' . $_SESSION['nom']) : 'Admin'); ?></span>
                            </a>
                        </li>
                        <div class="topbar-divider d-none d-sm-block"></div>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="../../../controller/auth/logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                <span class="d-none d-lg-inline">Logout</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Message flash -->
                    <?php if ($flash): ?>
                    <div class="alert alert-flash alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo secureOutput($flash['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <!-- User Profile Card -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-primary text-white">
                                    <h6 class="m-0 font-weight-bold">Informations de l'utilisateur</h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="counselor-avatar mb-3" style="width: 100px; height: 100px; font-size: 2.5rem; margin: 0 auto;">
                                        <?php echo strtoupper(substr($user->getNom(), 0, 1) . substr($user->getPrenom(), 0, 1)); ?>
                                    </div>
                                    <h4 class="mb-1"><?php echo secureOutput($user->getFullName()); ?></h4>
                                    <p class="text-muted mb-3"><?php echo secureOutput($user->getEmail()); ?></p>
                                    
                                    <div class="mb-3">
                                        <span class="badge bg-<?php 
                                            echo $user->getRole() === 'admin' ? 'danger' : 
                                                ($user->getRole() === 'counselor' ? 'success' : 'primary'); 
                                        ?> fs-6">
                                            <?php echo $user->getRole() === 'admin' ? 'admin' : ucfirst($user->getRole()); ?>
                                        </span>
                                        <span class="badge bg-<?php 
                                            echo $user->getStatut() === 'actif' ? 'success' : 'secondary'; 
                                        ?> fs-6">
                                            <?php echo ucfirst($user->getStatut()); ?>
                                        </span>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="text-start">
                                        <p class="mb-2">
                                            <strong>ID:</strong> <?php echo $user->getId(); ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Date d'inscription:</strong><br>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y H:i', strtotime($user->getDateInscription())); ?>
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Requests -->
                        <div class="col-md-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-inbox me-2"></i>
                                        Demandes de support (<?php echo count($userRequests); ?>)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($userRequests)): ?>
                                        <p class="text-muted text-center py-4">Aucune demande de support</p>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Titre</th>
                                                        <th>Statut</th>
                                                        <th>Urgence</th>
                                                        <th>Date</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($userRequests as $req): ?>
                                                    <tr>
                                                        <td><?php echo $req->getId(); ?></td>
                                                        <td><?php echo secureOutput(substr($req->getTitre(), 0, 40)); ?>...</td>
                                                        <td>
                                                            <span class="badge bg-<?php 
                                                                $statut = $req->getStatut();
                                                                echo $statut === 'en_attente' ? 'warning' : 
                                                                     ($statut === 'terminee' ? 'success' : 'primary'); 
                                                            ?>">
                                                                <?php echo ucfirst(str_replace('_', ' ', $statut)); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-<?php 
                                                                $urgence = $req->getUrgence();
                                                                echo $urgence === 'haute' ? 'danger' : 
                                                                     ($urgence === 'basse' ? 'info' : 'warning'); 
                                                            ?>">
                                                                <?php echo ucfirst($urgence); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <small><?php echo date('d/m/Y', strtotime($req->getDateCreation())); ?></small>
                                                        </td>
                                                        <td>
                                                            <a href="request_conversation.php?id=<?php echo $req->getId(); ?>" 
                                                               class="btn btn-sm btn-primary">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="row">
                        <div class="col-12">
                            <a href="users_list.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour à la liste
                            </a>
                            <button class="btn btn-danger float-end" onclick="confirmDelete()">
                                <i class="fas fa-trash"></i> Supprimer cet utilisateur
                            </button>
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
                        <span>Copyright &copy; SAFEProject 2025 - Module Support Psychologique</span>
                    </div>
                </div>
            </footer>

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function confirmDelete() {
            if (confirm('Êtes-vous sûr de vouloir supprimer l\'utilisateur "<?php echo addslashes($user->getFullName()); ?>" ?\n\nCette action est irréversible et supprimera également toutes les demandes associées.')) {
                window.location.href = '../../../controller/support/admin_delete_user.php?id=<?php echo $user->getId(); ?>';
            }
        }
    </script>

</body>
</html>

