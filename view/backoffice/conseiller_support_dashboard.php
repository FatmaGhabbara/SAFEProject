<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/SupportController.php';
require_once __DIR__ . '/../../controller/usercontroller.php';

// Check if logged in as conseiller
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'conseilleur') {
    header('Location: ../frontoffice/login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$userController = new UserController();
$user = $userController->getUserById($userId);

$supportController = new SupportController();
$requests = $supportController->findRequestsByCounselor($userId);

// Count by status
$pending = 0;
$in_progress = 0;
$completed = 0;

foreach ($requests as $req) {
    switch ($req->getStatut()) {
        case 'assignee':
            $pending++;
            break;
        case 'en_cours':
            $in_progress++;
            break;
        case 'terminee':
            $completed++;
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dashboard Conseiller - SafeSpace</title>
    
    <link href="../backoffice/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../backoffice/assets/css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f5f7fa;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            border-left: 4px solid;
        }
        .stat-card.pending {
            border-left-color: #f6c23e;
        }
        .stat-card.in-progress {
            border-left-color: #36b9cc;
        }
        .stat-card.completed {
            border-left-color: #1cc88a;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin: 0.5rem 0;
        }
        .stat-label {
            color: #858796;
            font-size: 0.875rem;
            text-transform: uppercase;
            font-weight: 600;
        }
    </style>
</head>
<body id="page-top">

<div id="wrapper">
    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="adviser_dashboard.php">
            <div class="sidebar-brand-icon">
                <i class="fas fa-user-md"></i>
            </div>
            <div class="sidebar-brand-text mx-3">SafeSpace <sup>Conseiller</sup></div>
        </a>

        <hr class="sidebar-divider my-0">

        <li class="nav-item">
            <a class="nav-link" href="adviser_dashboard.php">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard Principal</span>
            </a>
        </li>

        <li class="nav-item active">
            <a class="nav-link" href="conseiller_support_dashboard.php">
                <i class="fas fa-fw fa-hands-helping"></i>
                <span>Mes Demandes Support</span>
            </a>
        </li>

        <hr class="sidebar-divider">

        <li class="nav-item">
            <a class="nav-link" href="../frontoffice/profile.php">
                <i class="fas fa-fw fa-user"></i>
                <span>Mon Profil</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="../frontoffice/logout.php">
                <i class="fas fa-fw fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </li>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <h1 class="h3 mb-0 text-gray-800">Mes Demandes de Support</h1>

                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                <?= htmlspecialchars($user->getNom()) ?>
                            </span>
                            <i class="fas fa-user-circle fa-fw"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
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

            <!-- Page Content -->
            <div class="container-fluid">
                
                <!-- Stats Row -->
                <div class="row">
                    <div class="col-xl-4 col-md-6">
                        <div class="stat-card pending">
                            <div class="stat-label">Nouvelles Assignations</div>
                            <div class="stat-value text-warning"><?= $pending ?></div>
                            <small class="text-muted">Demandes assignées récemment</small>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="stat-card in-progress">
                            <div class="stat-label">En Cours</div>
                            <div class="stat-value text-info"><?= $in_progress ?></div>
                            <small class="text-muted">Demandes actives</small>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="stat-card completed">
                            <div class="stat-label">Terminées</div>
                            <div class="stat-value text-success"><?= $completed ?></div>
                            <small class="text-muted">Demandes clôturées</small>
                        </div>
                    </div>
                </div>

                <!-- Admin Notes Alert -->
                <?php 
                $requestsWithNotes = array_filter($requests, function($req) {
                    return !empty($req->getNotesAdmin()) && $req->getStatut() === 'assignee';
                });
                ?>
                <?php if (!empty($requestsWithNotes)): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading"><i class="fas fa-sticky-note"></i> Notes de l'administrateur</h5>
                    <hr>
                    <?php foreach ($requestsWithNotes as $req): ?>
                    <div class="mb-3 p-3 bg-white rounded border-left border-warning">
                        <strong class="text-primary">Demande #<?= $req->getId() ?>: <?= htmlspecialchars($req->getTitre()) ?></strong>
                        <p class="mb-1 mt-2"><?= nl2br(htmlspecialchars($req->getNotesAdmin())) ?></p>
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> Assignée le <?= date('d/m/Y à H:i', strtotime($req->getDateAssignation())) ?>
                        </small>
                    </div>
                    <?php endforeach; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>

                <!-- Requests Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Toutes mes demandes</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($requests)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                                <p class="text-gray-600">Aucune demande assignée pour le moment</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Titre</th>
                                            <th>Urgence</th>
                                            <th>Statut</th>
                                            <th>Date création</th>
                                            <th>Note Admin</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($requests as $req): ?>
                                        <tr>
                                            <td><?= $req->getId() ?></td>
                                            <td><?= htmlspecialchars($req->getTitre()) ?></td>
                                            <td>
                                                <?php
                                                $urgency_badges = [
                                                    'basse' => 'secondary',
                                                    'moyenne' => 'warning',
                                                    'haute' => 'danger'
                                                ];
                                                $badge = $urgency_badges[$req->getUrgence()] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?= $badge ?>">
                                                    <?= htmlspecialchars($req->getUrgence()) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $status_badges = [
                                                    'assignee' => 'info',
                                                    'en_cours' => 'primary',
                                                    'terminee' => 'success'
                                                ];
                                                $status_badge = $status_badges[$req->getStatut()] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?= $status_badge ?>">
                                                    <?= htmlspecialchars($req->getStatut()) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y H:i', strtotime($req->getDateCreation())) ?></td>
                                            <td>
                                                <?php if (!empty($req->getNotesAdmin())): ?>
                                                <button type="button" class="btn btn-sm btn-warning" 
                                                        data-toggle="tooltip" 
                                                        title="<?= htmlspecialchars($req->getNotesAdmin()) ?>">
                                                    <i class="fas fa-sticky-note"></i>
                                                </button>
                                                <?php else: ?>
                                                <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="support_conversation.php?id=<?= $req->getId() ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-comments"></i> Conversation
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

        <!-- Footer -->
        <footer class="sticky-footer bg-white">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>Copyright &copy; SafeSpace <?= date('Y') ?></span>
                </div>
            </div>
        </footer>
    </div>
</div>

<script src="../backoffice/assets/vendor/jquery/jquery.min.js"></script>
<script src="../backoffice/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../backoffice/assets/js/sb-admin-2.min.js"></script>
<script>
// Enable tooltips for admin note icons
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});
</script>

</body>
</html>
