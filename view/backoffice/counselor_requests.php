<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/SupportController.php';
require_once __DIR__ . '/../../controller/usercontroller.php';
require_once __DIR__ . '/../../model/SupportRequest.php';

// Check if logged in as counselor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'conseilleur') {
    header('Location: ../frontoffice/login.php');
    exit();
}

$supportController = new SupportController();
$userController = new UserController();
$currentUserId = $_SESSION['user_id'];

// Get counselor's assigned requests
$myRequests = $supportController->findRequestsByCounselor($currentUserId);

// Count by status
$stats = [
    'new' => 0,
    'in_progress' => 0,
    'completed' => 0
];

foreach ($myRequests as $req) {
    switch ($req->getStatut()) {
        case 'assignee':
            $stats['new']++;
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
    <title>Mes Demandes Assignées - SafeSpace Conseiller</title>
    
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        .badge-urgence-basse { background: #28a745; color: white; }
        .badge-urgence-moyenne { background: #ffc107; color: #000; }
        .badge-urgence-haute { background: #dc3545; color: white; }
        .badge-status-assignee { background: #17a2b8; color: white; }
        .badge-status-en_cours { background: #007bff; color: white; }
        .badge-status-terminee { background: #28a745; color: white; }
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
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-item active">
            <a class="nav-link" href="counselor_requests.php">
                <i class="fas fa-fw fa-headset"></i>
                <span>Mes Demandes</span>
            </a>
        </li>

        <hr class="sidebar-divider">

        <li class="nav-item">
            <a class="nav-link" href="../../controller/AuthController.php?action=logout">
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
                <h1 class="h3 mb-0 text-gray-800">Mes Demandes Assignées</h1>
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
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Nouvelles Assignations</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['new'] ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-bell fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">En Cours</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['in_progress'] ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-comments fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-4">
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

                <!-- Requests List -->
                <div class="row">
                    <?php if (empty($myRequests)): ?>
                        <div class="col-12">
                            <div class="card shadow">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                                    <h5>Aucune demande assignée</h5>
                                    <p class="text-muted">Les demandes qui vous seront assignées apparaîtront ici</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($myRequests as $request): ?>
                            <?php 
                            $user = $userController->getUserById($request->getUserId());
                            $messages = $supportController->findMessagesByRequest($request->getId());
                            $unreadCount = $supportController->countUnreadMessages($request->getId(), $currentUserId);
                            ?>
                            <div class="col-md-6 mb-4">
                                <div class="card shadow h-100">
                                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                        <h6 class="m-0 font-weight-bold text-primary">
                                            <?= htmlspecialchars($request->getTitre()) ?>
                                        </h6>
                                        <span class="badge badge-status-<?= $request->getStatut() ?>">
                                            <?= ucfirst(str_replace('_', ' ', $request->getStatut())) ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-2">
                                            <strong><i class="fas fa-user"></i> Patient:</strong> 
                                            <?= htmlspecialchars($user->getNom()) ?>
                                        </div>
                                        
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-calendar"></i> 
                                            Assignée le: <?= date('d/m/Y H:i', strtotime($request->getDateAssignation() ?: $request->getDateCreation())) ?>
                                        </p>
                                        
                                        <p class="mb-3"><?= nl2br(htmlspecialchars(substr($request->getDescription(), 0, 150))) ?>...</p>
                                        
                                        <div class="mb-3">
                                            <span class="badge badge-urgence-<?= $request->getUrgence() ?>">
                                                Urgence: <?= ucfirst($request->getUrgence()) ?>
                                            </span>
                                            
                                            <?php if ($unreadCount > 0): ?>
                                                <span class="badge badge-danger ml-2">
                                                    <?= $unreadCount ?> nouveau<?= $unreadCount > 1 ? 'x' : '' ?> message<?= $unreadCount > 1 ? 's' : '' ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if (!empty($request->getAdminNote())): ?>
                                            <div class="alert alert-warning mb-3">
                                                <strong><i class="fas fa-sticky-note"></i> Note Admin:</strong><br>
                                                <?= nl2br(htmlspecialchars($request->getAdminNote())) ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-comments"></i> <?= count($messages) ?> message<?= count($messages) > 1 ? 's' : '' ?>
                                            </small>
                                            <a href="support/request_conversation.php?id=<?= $request->getId() ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-comments"></i> Ouvrir la Conversation
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="assets/vendor/jquery/jquery.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="assets/js/sb-admin-2.min.js"></script>

</body>
</html>
