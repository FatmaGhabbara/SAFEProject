<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Conseillers - SAFEProject Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/support-module.css">
    <link rel="stylesheet" href="../css/sb-admin-2.min.css">
    
    <style>
        body {
            background-color: #2e3141 !important;
            background-image: linear-gradient(to top, rgba(46, 49, 65, 0.8), rgba(46, 49, 65, 0.8)), url("../../frontoffice/images/bg.jpg") !important;
            background-size: auto, cover !important;
            background-attachment: fixed !important;
            background-position: center !important;
            min-height: 100vh;
        }
    </style>
</head>
<body id="page-top">

<?php
session_start();

require_once '../../../config.php';
require_once '../../../controller/helpers.php';

// Check admin access
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../frontoffice/login.php');
    exit();
}

// Récupérer tous les conseillers (returns array of User objects)
$counselors = getAllCounselors();

// Récupérer les messages flash
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

            <li class="nav-item active">
                <a class="nav-link" href="counselors_list.php">
                    <i class="fas fa-fw fa-user-md"></i>
                    <span>Conseillers</span>
                </a>
            </li>

            <li class="nav-item">
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
                    <h1 class="h3 mb-0 text-gray-800">Gestion des Conseillers</h1>

                    <ul class="navbar-nav ml-auto">
                        <!-- Dashboard link removed - using sidebar brand -->
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

                    <!-- En-tête avec bouton d'ajout -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="mb-1">Liste des conseillers psychologiques</h4>
                            <p class="text-muted mb-0">Gérez les profils des conseillers du module de support</p>
                        </div>
                        <a href="add_counselor.php" class="btn btn-support-primary">
                            <i class="fas fa-plus me-2"></i>
                            Ajouter un conseiller
                        </a>
                    </div>

                    <!-- Statistiques rapides -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card stat-success">
                                <div class="text-center">
                                    <p class="stat-label">Conseillers Actifs</p>
                                    <p class="stat-value">
                                        <?php echo count(array_filter($counselors, function($counselorUser) { return $counselorUser->getStatutCounselor() === 'actif'; })); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card stat-warning">
                                <div class="text-center">
                                    <p class="stat-label">En Pause</p>
                                    <p class="stat-value">
                                        <?php echo count(array_filter($counselors, function($counselorUser) { return $counselorUser->getStatutCounselor() === 'en_pause'; })); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card stat-danger">
                                <div class="text-center">
                                    <p class="stat-label">Inactifs</p>
                                    <p class="stat-value">
                                        <?php echo count(array_filter($counselors, function($counselorUser) { return $counselorUser->getStatutCounselor() === 'inactif'; })); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card stat-info">
                                <div class="text-center">
                                    <p class="stat-label">Total</p>
                                    <p class="stat-value"><?php echo count($counselors); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau des conseillers -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-users me-2"></i>
                                Liste complète des conseillers
                            </h6>
                        </div>
                        <div class="card-body">
                            
                            <?php if (empty($counselors)): ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                    <h3 class="empty-state-title">Aucun conseiller enregistré</h3>
                                    <p class="empty-state-text">
                                        Commencez par ajouter votre premier conseiller psychologique.
                                    </p>
                                    <a href="add_counselor.php" class="btn btn-support-primary btn-lg">
                                        <i class="fas fa-plus me-2"></i>
                                        Ajouter un conseiller
                                    </a>
                                </div>
                            <?php else: ?>
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="18%">Nom</th>
                                            <th width="20%">Spécialité</th>
                                            <th width="12%">Demandes actives</th>
                                            <th width="12%">Statut</th>
                                            <th width="20%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($counselors as $counselorUser): ?>
                                        <tr>
                                            <td><?php echo $counselorUser->getId(); ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="counselor-avatar" style="width: 40px; height: 40px; font-size: 1rem; margin-right: 10px;">
                                                        <?php echo strtoupper(substr($counselorUser->getNom(), 0, 1) . substr($counselorUser->getPrenom(), 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <strong><?php echo secureOutput($counselorUser->getFullName()); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo secureOutput($counselorUser->getEmail()); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo secureOutput($counselorUser->getSpecialite() ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge bg-primary fs-6">
                                                    <?php echo $counselorUser->getNombreDemandesActives(); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = '';
                                                $statut = $counselorUser->getStatutCounselor();
                                                switch($statut) {
                                                    case 'actif': $statusClass = 'bg-success'; break;
                                                    case 'en_pause': $statusClass = 'bg-warning'; break;
                                                    case 'inactif': $statusClass = 'bg-danger'; break;
                                                    default: $statusClass = 'bg-secondary'; break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $statut ?? 'N/A')); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="view_counselor.php?id=<?php echo $counselorUser->getId(); ?>" 
                                                       class="action-btn action-btn-view" 
                                                       title="Voir détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit_counselor.php?id=<?php echo $counselorUser->getId(); ?>" 
                                                       class="action-btn action-btn-edit" 
                                                       title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="action-btn action-btn-delete" 
                                                            title="Supprimer"
                                                            onclick="confirmDelete(<?php echo $counselorUser->getId(); ?>, '<?php echo addslashes($counselorUser->getFullName()); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
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
        function confirmDelete(counselorId, counselorName) {
            if (confirm('Êtes-vous sûr de vouloir supprimer le conseiller "' + counselorName + '" ?\n\nCette action est irréversible.')) {
                window.location.href = '../../../controller/support/admin_delete_counselor.php?id=' + counselorId;
            }
        }
    </script>

</body>
</html>

