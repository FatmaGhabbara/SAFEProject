<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Demandes - SAFEProject Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/support-module.css">
    <link rel="stylesheet" href="../css/sb-admin-2.min.css">
</head>
<body id="page-top">

<?php
session_start();

// MODE TEST : FORCER la session administrateur
$_SESSION['user_id'] = 1;  // Admin
$_SESSION['role'] = 'admin';
$_SESSION['user_name'] = 'Administrateur';

require_once '../../../model/config.php';
require_once '../../../model/support_functions.php';

// Récupérer les filtres
$filters = [];
if (isset($_GET['statut']) && !empty($_GET['statut'])) {
    $filters['statut'] = $_GET['statut'];
}
if (isset($_GET['urgence']) && !empty($_GET['urgence'])) {
    $filters['urgence'] = $_GET['urgence'];
}
if (isset($_GET['counselor_id']) && !empty($_GET['counselor_id'])) {
    $filters['counselor_id'] = $_GET['counselor_id'];
}

// Récupérer toutes les demandes
$requests = getAllSupportRequests($filters);

// Récupérer les statistiques
$stats = getGlobalStats();
$averageResponseTime = getAverageResponseTime();

// Récupérer tous les conseillers pour le filtre
$counselors = getAllCounselors();

// Récupérer les messages flash
$flash = getFlashMessage();
?>

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../index.html">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="sidebar-brand-text mx-3">SAFEProject</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="../index.html">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Support Psychologique
            </div>

            <!-- Nav Item - Demandes -->
            <li class="nav-item active">
                <a class="nav-link" href="support_requests.php">
                    <i class="fas fa-fw fa-inbox"></i>
                    <span>Demandes de Support</span>
                </a>
            </li>

            <!-- Nav Item - Conseillers -->
            <li class="nav-item">
                <a class="nav-link" href="counselors_list.php">
                    <i class="fas fa-fw fa-user-md"></i>
                    <span>Conseillers</span>
                </a>
            </li>

            <!-- Nav Item - Statistiques -->
            <li class="nav-item">
                <a class="nav-link" href="counselor_stats.php">
                    <i class="fas fa-fw fa-chart-area"></i>
                    <span>Statistiques</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <h1 class="h3 mb-0 text-gray-800">Gestion des Demandes de Support</h1>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="../../frontoffice/index.html">
                                <i class="fas fa-home"></i>
                                <span class="d-none d-lg-inline">Site Public</span>
                            </a>
                        </li>
                        <div class="topbar-divider d-none d-sm-block"></div>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-user"></i>
                                <span class="d-none d-lg-inline">Admin</span>
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

                    <!-- Statistiques -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card stat-warning">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="stat-label">En Attente</p>
                                        <p class="stat-value"><?php echo $stats['demandes_en_attente']; ?></p>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-hourglass-half"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card stat-primary">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="stat-label">En Cours</p>
                                        <p class="stat-value"><?php echo $stats['demandes_en_cours']; ?></p>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-tasks"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card stat-success">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="stat-label">Terminées</p>
                                        <p class="stat-value"><?php echo $stats['demandes_terminees']; ?></p>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card stat-info">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="stat-label">Temps Moyen</p>
                                        <p class="stat-value"><?php echo number_format($averageResponseTime, 1); ?>h</p>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres -->
                    <div class="filter-section mb-4">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-3">
                                <label for="statut" class="form-label">
                                    <i class="fas fa-filter me-2"></i>Statut
                                </label>
                                <select name="statut" id="statut" class="form-select">
                                    <option value="">Tous</option>
                                    <option value="en_attente" <?php echo isset($_GET['statut']) && $_GET['statut'] === 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                                    <option value="assignee" <?php echo isset($_GET['statut']) && $_GET['statut'] === 'assignee' ? 'selected' : ''; ?>>Assignée</option>
                                    <option value="en_cours" <?php echo isset($_GET['statut']) && $_GET['statut'] === 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                                    <option value="terminee" <?php echo isset($_GET['statut']) && $_GET['statut'] === 'terminee' ? 'selected' : ''; ?>>Terminée</option>
                                    <option value="annulee" <?php echo isset($_GET['statut']) && $_GET['statut'] === 'annulee' ? 'selected' : ''; ?>>Annulée</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="urgence" class="form-label">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Urgence
                                </label>
                                <select name="urgence" id="urgence" class="form-select">
                                    <option value="">Toutes</option>
                                    <option value="haute" <?php echo isset($_GET['urgence']) && $_GET['urgence'] === 'haute' ? 'selected' : ''; ?>>Haute</option>
                                    <option value="moyenne" <?php echo isset($_GET['urgence']) && $_GET['urgence'] === 'moyenne' ? 'selected' : ''; ?>>Moyenne</option>
                                    <option value="basse" <?php echo isset($_GET['urgence']) && $_GET['urgence'] === 'basse' ? 'selected' : ''; ?>>Basse</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="counselor_id" class="form-label">
                                    <i class="fas fa-user-md me-2"></i>Conseiller
                                </label>
                                <select name="counselor_id" id="counselor_id" class="form-select">
                                    <option value="">Tous</option>
                                    <?php foreach ($counselors as $counselor): ?>
                                    <option value="<?php echo $counselor['id']; ?>" 
                                            <?php echo isset($_GET['counselor_id']) && $_GET['counselor_id'] == $counselor['id'] ? 'selected' : ''; ?>>
                                        <?php echo secureOutput($counselor['nom'] . ' ' . $counselor['prenom']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-support-primary flex-grow-1">
                                    <i class="fas fa-search me-2"></i>Filtrer
                                </button>
                                <a href="support_requests.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Tableau des demandes -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-list me-2"></i>
                                Liste des demandes (<?php echo count($requests); ?>)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="requestsTable">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="15%">Utilisateur</th>
                                            <th width="20%">Titre</th>
                                            <th width="10%">Date</th>
                                            <th width="10%">Urgence</th>
                                            <th width="10%">Statut</th>
                                            <th width="15%">Conseiller</th>
                                            <th width="15%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($requests as $request): ?>
                                        <tr>
                                            <td><?php echo $request['id']; ?></td>
                                            <td>
                                                <small>
                                                    <i class="fas fa-user text-primary me-1"></i>
                                                    <?php echo secureOutput($request['user_nom'] . ' ' . $request['user_prenom']); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <strong><?php echo secureOutput($request['titre']); ?></strong>
                                            </td>
                                            <td>
                                                <small><?php echo formatDate($request['date_creation'], 'd/m/Y'); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge-urgence badge-urgence-<?php echo $request['urgence']; ?>">
                                                    <?php echo ucfirst($request['urgence']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge-support badge-<?php echo str_replace('_', '-', $request['statut']); ?>">
                                                    <?php echo str_replace('_', ' ', ucfirst($request['statut'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($request['counselor_nom']): ?>
                                                    <small><?php echo secureOutput($request['counselor_nom'] . ' ' . $request['counselor_prenom']); ?></small>
                                                <?php else: ?>
                                                    <small class="text-muted">Non assigné</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="view_request.php?id=<?php echo $request['id']; ?>" 
                                                       class="action-btn action-btn-view" 
                                                       title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($request['statut'] === 'en_attente'): ?>
                                                    <a href="assign_counselor.php?id=<?php echo $request['id']; ?>" 
                                                       class="action-btn action-btn-assign" 
                                                       title="Assigner">
                                                        <i class="fas fa-user-plus"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                    <button class="action-btn action-btn-delete" 
                                                            title="Supprimer"
                                                            onclick="confirmDelete(<?php echo $request['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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
                        <span>Copyright &copy; SAFEProject 2025 - Module Support Psychologique</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Initialiser DataTables
        $(document).ready(function() {
            $('#requestsTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
                },
                order: [[0, 'desc']],
                pageLength: 25
            });
        });
        
        // Confirmation de suppression
        function confirmDelete(requestId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette demande ? Cette action est irréversible.')) {
                window.location.href = '../../../controller/support/admin_delete_request.php?id=' + requestId;
            }
        }
    </script>

</body>
</html>

