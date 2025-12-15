<?php
session_start();
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../controller/SupportController.php';
require_once __DIR__ . '/../../../controller/usercontroller.php';
require_once __DIR__ . '/../../../model/SupportRequest.php';

// Check if logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../frontoffice/login.php');
    exit();
}

$supportController = new SupportController();
$userController = new UserController();

// Handle counselor assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_counselor'])) {
    $request_id = intval($_POST['request_id']);
    $counselor_id = intval($_POST['counselor_id']);
    $admin_note = isset($_POST['admin_note']) ? trim($_POST['admin_note']) : '';
    
    if ($supportController->assignCounselor($request_id, $counselor_id, $admin_note)) {
        $_SESSION['success_message'] = 'Conseiller assigné avec succès.';
    } else {
        $_SESSION['error_message'] = 'Erreur lors de l\'assignation du conseiller.';
    }
    header('Location: support_requests.php');
    exit();
}

// Get all requests
$all_requests = $supportController->findAllRequests();
$pending_requests = $supportController->findPendingRequests();
$available_counselors = $supportController->getAvailableCounselors();

// Count by status
$stats = [
    'pending' => 0,
    'assigned' => 0,
    'in_progress' => 0,
    'completed' => 0
];

foreach ($all_requests as $req) {
    switch ($req->getStatut()) {
        case 'en_attente':
            $stats['pending']++;
            break;
        case 'assignee':
            $stats['assigned']++;
            break;
        case 'en_cours':
            $stats['in_progress']++;
            break;
        case 'terminee':
            $stats['completed']++;
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Gestion des Demandes de Support - SafeSpace Admin</title>
    
    <link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        .badge-urgence-basse { background: #28a745; color: white; }
        .badge-urgence-moyenne { background: #ffc107; color: #000; }
        .badge-urgence-haute { background: #dc3545; color: white; }
        .badge-status-en_attente { background: #6c757d; color: white; }
        .badge-status-assignee { background: #17a2b8; color: white; }
        .badge-status-en_cours { background: #007bff; color: white; }
        .badge-status-terminee { background: #28a745; color: white; }
        .badge-status-annulee { background: #dc3545; color: white; }
    </style>
</head>
<body id="page-top">

<div id="wrapper">
    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../index.php">
            <div class="sidebar-brand-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="sidebar-brand-text mx-3">SafeSpace <sup>Admin</sup></div>
        </a>

        <hr class="sidebar-divider my-0">

        <li class="nav-item">
            <a class="nav-link" href="../index.php">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="../users_list.php">
                <i class="fas fa-fw fa-users"></i>
                <span>Utilisateurs</span>
            </a>
        </li>

        <li class="nav-item active">
            <a class="nav-link" href="support_requests.php">
                <i class="fas fa-fw fa-headset"></i>
                <span>Demandes de Support</span>
            </a>
        </li>

        <hr class="sidebar-divider">

        <li class="nav-item">
            <a class="nav-link" href="../../../controller/AuthController.php?action=logout">
                <i class="fas fa-fw fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </li>
    </ul>

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <h1 class="h3 mb-0 text-gray-800">Gestion des Demandes de Support</h1>
            </nav>

            <!-- Page Content -->
            <div class="container-fluid">
                
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= htmlspecialchars($_SESSION['success_message']) ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= htmlspecialchars($_SESSION['error_message']) ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">En Attente</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['pending'] ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Assignées</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['assigned'] ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-check fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">En Cours</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['in_progress'] ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-spinner fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Terminées</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['completed'] ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Requests Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Toutes les Demandes</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Utilisateur</th>
                                        <th>Titre</th>
                                        <th>Urgence</th>
                                        <th>Statut</th>
                                        <th>Conseiller</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_requests as $request): ?>
                                        <?php 
                                        $user = $userController->getUserById($request->getUserId());
                                        $counselor = $request->getCounselorUserId() ? $userController->getUserById($request->getCounselorUserId()) : null;
                                        ?>
                                        <tr>
                                            <td><?= $request->getId() ?></td>
                                            <td><?= htmlspecialchars($user->getNom()) ?></td>
                                            <td><?= htmlspecialchars($request->getTitre()) ?></td>
                                            <td>
                                                <span class="badge badge-urgence-<?= $request->getUrgence() ?>">
                                                    <?= ucfirst($request->getUrgence()) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-status-<?= $request->getStatut() ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $request->getStatut())) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($counselor): ?>
                                                    <?= htmlspecialchars($counselor->getNom()) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Non assigné</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('d/m/Y H:i', strtotime($request->getDateCreation())) ?></td>
                                            <td>
                                                <a href="request_conversation.php?id=<?= $request->getId() ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($request->getStatut() === 'en_attente'): ?>
                                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#assignModal<?= $request->getId() ?>">
                                                        <i class="fas fa-user-plus"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                        <!-- Assign Modal -->
                                        <div class="modal fade" id="assignModal<?= $request->getId() ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Assigner un Conseiller</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="request_id" value="<?= $request->getId() ?>">
                                                            
                                                            <div class="form-group">
                                                                <label>Conseiller</label>
                                                                <select name="counselor_id" class="form-control" required>
                                                                    <option value="">Sélectionner un conseiller</option>
                                                                    <?php foreach ($available_counselors as $counselor): ?>
                                                                        <option value="<?= $counselor['id'] ?>">
                                                                            <?= htmlspecialchars($counselor['nom']) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label>Note (optionnel)</label>
                                                                <textarea name="admin_note" class="form-control" rows="3"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                            <button type="submit" name="assign_counselor" class="btn btn-primary">Assigner</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="../assets/vendor/jquery/jquery.min.js"></script>
<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../assets/js/sb-admin-2.min.js"></script>

</body>
</html>
