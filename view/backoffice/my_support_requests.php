<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/SupportController.php';
require_once __DIR__ . '/../../controller/usercontroller.php';
require_once __DIR__ . '/../../model/SupportRequest.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../frontoffice/login.php');
    exit();
}

$supportController = new SupportController();
$userController = new UserController();
$currentUserId = $_SESSION['user_id'];

// Handle new request creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_request'])) {
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $urgence = $_POST['urgence'];
    
    if (!empty($titre) && !empty($description)) {
        $request = new SupportRequest();
        $request->setUserId($currentUserId);
        $request->setTitre($titre);
        $request->setDescription($description);
        $request->setUrgence($urgence);
        $request->setStatut('en_attente');
        
        if ($request->save()) {
            $_SESSION['success_message'] = 'Demande créée avec succès!';
        } else {
            $_SESSION['error_message'] = 'Erreur lors de la création de la demande.';
        }
    }
    
    header('Location: my_support_requests.php');
    exit();
}

// Handle request cancellation
if (isset($_GET['cancel_request'])) {
    $requestId = intval($_GET['cancel_request']);
    if ($supportController->cancelRequest($requestId, $currentUserId)) {
        $_SESSION['success_message'] = 'Demande annulée.';
    } else {
        $_SESSION['error_message'] = 'Impossible d\'annuler cette demande.';
    }
    header('Location: my_support_requests.php');
    exit();
}

// Handle request deletion
if (isset($_GET['delete_request'])) {
    $requestId = intval($_GET['delete_request']);
    if ($supportController->deleteRequest($requestId, $currentUserId, $_SESSION['user_role'])) {
        $_SESSION['success_message'] = 'Demande supprimée.';
    } else {
        $_SESSION['error_message'] = 'Impossible de supprimer cette demande.';
    }
    header('Location: my_support_requests.php');
    exit();
}

// Get user's requests
$myRequests = $supportController->findRequestsByUser($currentUserId);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Mes Demandes de Support - SafeSpace</title>
    
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        .badge-urgence-basse { background: #28a745; color: white; }
        .badge-urgence-moyenne { background: #ffc107; color: #000; }
        .badge-urgence-haute { background: #dc3545; color: white; }
        .badge-status-en_attente { background: #6c757d; color: white; }
        .badge-status-assignee { background: #17a2b8; color: white; }
        .badge-status-en_cours { background: #007bff; color: white; }
        .badge-status-terminee { background: #28a745; color: white; }
    </style>
</head>
<body id="page-top">

<div id="wrapper">
    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="member_dashboard.php">
            <div class="sidebar-brand-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="sidebar-brand-text mx-3">SafeSpace</div>
        </a>

        <hr class="sidebar-divider my-0">

        <li class="nav-item">
            <a class="nav-link" href="member_dashboard.php">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-item active">
            <a class="nav-link" href="my_support_requests.php">
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
                <h1 class="h3 mb-0 text-gray-800">Mes Demandes de Support</h1>
                <button class="btn btn-primary ml-auto" data-toggle="modal" data-target="#createRequestModal">
                    <i class="fas fa-plus"></i> Nouvelle Demande
                </button>
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

                <!-- Requests List -->
                <div class="row">
                    <?php if (empty($myRequests)): ?>
                        <div class="col-12">
                            <div class="card shadow">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                                    <h5>Aucune demande pour le moment</h5>
                                    <p class="text-muted">Créez votre première demande de support</p>
                                    <button class="btn btn-primary" data-toggle="modal" data-target="#createRequestModal">
                                        <i class="fas fa-plus"></i> Créer une Demande
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($myRequests as $request): ?>
                            <?php 
                            $counselor = $request->getCounselorUserId() ? $userController->getUserById($request->getCounselorUserId()) : null;
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
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-calendar"></i> 
                                            <?= date('d/m/Y H:i', strtotime($request->getDateCreation())) ?>
                                        </p>
                                        
                                        <p class="mb-3"><?= nl2br(htmlspecialchars(substr($request->getDescription(), 0, 150))) ?>...</p>
                                        
                                        <div class="mb-3">
                                            <span class="badge badge-urgence-<?= $request->getUrgence() ?>">
                                                Urgence: <?= ucfirst($request->getUrgence()) ?>
                                            </span>
                                            
                                            <?php if ($counselor): ?>
                                                <span class="badge badge-info ml-2">
                                                    <i class="fas fa-user-md"></i> <?= htmlspecialchars($counselor->getNom()) ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if ($unreadCount > 0): ?>
                                                <span class="badge badge-danger ml-2">
                                                    <?= $unreadCount ?> nouveau<?= $unreadCount > 1 ? 'x' : '' ?> message<?= $unreadCount > 1 ? 's' : '' ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                                            <small class="text-muted mb-2">
                                                <i class="fas fa-comments"></i> <?= count($messages) ?> message<?= count($messages) > 1 ? 's' : '' ?>
                                            </small>
                                            <div>
                                                <a href="support/request_conversation.php?id=<?= $request->getId() ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> Voir
                                                </a>
                                                <?php if ($request->getStatut() !== 'terminee'): ?>
                                                    <a href="?cancel_request=<?= $request->getId() ?>" 
                                                       class="btn btn-sm btn-warning"
                                                       onclick="return confirm('Annuler cette demande?')">
                                                        <i class="fas fa-ban"></i> Annuler
                                                    </a>
                                                <?php endif; ?>
                                                <a href="?delete_request=<?= $request->getId() ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Supprimer définitivement cette demande?')">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </a>
                                            </div>
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

<!-- Create Request Modal -->
<div class="modal fade" id="createRequestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle Demande de Support</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Titre de la demande *</label>
                        <input type="text" name="titre" class="form-control" required 
                               placeholder="Ex: Besoin d'aide pour...">
                    </div>
                    
                    <div class="form-group">
                        <label>Description détaillée *</label>
                        <textarea name="description" class="form-control" rows="6" required
                                  placeholder="Décrivez votre problème ou besoin en détail..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Niveau d'urgence</label>
                        <select name="urgence" class="form-control">
                            <option value="basse">Basse - Peut attendre</option>
                            <option value="moyenne" selected>Moyenne - Dans les prochains jours</option>
                            <option value="haute">Haute - Urgent</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Votre demande sera examinée par notre équipe et un conseiller vous sera assigné dans les plus brefs délais.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" name="create_request" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Envoyer la Demande
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/vendor/jquery/jquery.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="assets/js/sb-admin-2.min.js"></script>

</body>
</html>
