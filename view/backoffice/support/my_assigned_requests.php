<?php
session_start();
require_once '../../../config.php';
require_once '../../../model/SupportRequest.php';
require_once '../../../model/User.php';
require_once '../../../controller/helpers.php';

// Check counselor access
if (!isLoggedIn() || !in_array($_SESSION['role'], ['counselor', 'admin'])) {
    header('Location: ../../frontoffice/login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Get counselor profile
$counselorUser = getCounselorByUserId($userId);

if (!$counselorUser) {
    setFlashMessage('Profil conseiller introuvable.', 'error');
    header('Location: ../../frontoffice/dashboard.php');
    exit();
}

// Get assigned requests
$counselorId = $counselorUser->getId();
$allRequests = findSupportRequestsByCounselor($counselorId);

$assignedRequests = array_filter($allRequests, function($req) {
    return in_array($req->getStatut(), ['assignee', 'en_cours']);
});

// Get flash message
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Demandes Assignées - SAFEProject</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #2e3141;
            background-image: linear-gradient(to top, rgba(46, 49, 65, 0.8), rgba(46, 49, 65, 0.8)), url("../../frontoffice/images/bg.jpg");
            background-size: auto, cover;
            background-attachment: fixed;
            background-position: center;
            min-height: 100vh;
        }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .request-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .request-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .badge-custom {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }
        .badge-en_attente { background: #ffc107; color: #000; }
        .badge-assignee { background: #17a2b8; color: #fff; }
        .badge-en_cours { background: #007bff; color: #fff; }
        .badge-terminee { background: #28a745; color: #fff; }
        .badge-annulee { background: #6c757d; color: #fff; }
        .badge-basse { background: #e9ecef; color: #495057; }
        .badge-moyenne { background: #ffc107; color: #000; }
        .badge-haute { background: #dc3545; color: #fff; }
        .stat-box {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-box h3 {
            font-size: 2.5rem;
            color: #667eea;
            margin: 0;
        }
    </style>
</head>
<body>

<?php include '../../includes/navbar.php'; ?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-tasks"></i> Mes Demandes Assignées</h1>
        <p class="mb-0">Gérez les demandes de support qui vous sont assignées</p>
    </div>
</div>

<div class="container mb-5">
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $flash['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-box">
                <h3><?php echo count($assignedRequests); ?></h3>
                <p class="mb-0">Demandes Actives</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box">
                <h3><?php echo count($allRequests); ?></h3>
                <p class="mb-0">Total Demandes</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box">
                <h3><?php echo count($allRequests) - count($assignedRequests); ?></h3>
                <p class="mb-0">Terminées</p>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="requestTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab">
                <i class="fas fa-tasks"></i> Actives (<?php echo count($assignedRequests); ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                <i class="fas fa-history"></i> Toutes (<?php echo count($allRequests); ?>)
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="requestTabsContent">
        <!-- Active Requests -->
        <div class="tab-pane fade show active" id="active" role="tabpanel">
            <?php if (empty($assignedRequests)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h4>Aucune demande active</h4>
                    <p class="text-muted">Vous n'avez pas de demandes assignées pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($assignedRequests as $requestObj): 
                        $user = $requestObj->getUser();
                        $messages = $requestObj->getMessages();
                    ?>
                    <div class="col-md-6 mb-4">
                        <div class="request-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="mb-0" style="color: #333;">
                                    <?php echo secureOutput($requestObj->getTitre()); ?>
                                </h5>
                                <span class="badge badge-custom badge-<?php echo $requestObj->getStatut() ?? 'assignee'; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $requestObj->getStatut() ?? 'Assignée')); ?>
                                </span>
                            </div>

                            <p class="text-muted mb-3" style="color: #666;">
                                <i class="fas fa-user"></i> <strong>Patient:</strong> <?php echo secureOutput($user->getFullName()); ?>
                            </p>

                            <p class="text-muted small mb-3" style="color: #666;">
                                <?php echo secureOutput(substr($requestObj->getDescription(), 0, 100)); ?>...
                            </p>

                            <div class="mb-3">
                                <span class="badge badge-custom badge-<?php echo $requestObj->getUrgence(); ?>">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo ucfirst($requestObj->getUrgence()); ?>
                                </span>
                            </div>

                            <div class="mb-3" style="color: #666;">
                                <small>
                                    <i class="fas fa-calendar"></i>
                                    Assignée le: <?php echo date('d/m/Y H:i', strtotime($requestObj->getDateAssignation())); ?>
                                </small>
                            </div>

                            <div class="mb-3" style="color: #666;">
                                <small>
                                    <i class="fas fa-comments"></i>
                                    <?php echo count($messages); ?> message(s)
                                </small>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="request_conversation.php?id=<?php echo $requestObj->getId(); ?>" class="btn btn-primary flex-grow-1">
                                    <i class="fas fa-comments"></i> Ouvrir Discussion
                                </a>
                                <?php if ($requestObj->getStatut() === 'assignee'): ?>
                                <button onclick="startRequest(<?php echo $requestObj->getId(); ?>)" class="btn btn-success">
                                    <i class="fas fa-play"></i> Commencer
                                </button>
                                <?php elseif ($requestObj->getStatut() === 'en_cours'): ?>
                                <button onclick="completeRequest(<?php echo $requestObj->getId(); ?>)" class="btn btn-success">
                                    <i class="fas fa-check"></i> Terminer
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- All Requests -->
        <div class="tab-pane fade" id="all" role="tabpanel">
            <?php if (empty($allRequests)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h4>Aucune demande</h4>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Patient</th>
                                <th>Titre</th>
                                <th>Urgence</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allRequests as $requestObj): 
                                $user = $requestObj->getUser();
                            ?>
                            <tr>
                                <td><?php echo $requestObj->getId(); ?></td>
                                <td><?php echo secureOutput($user->getFullName()); ?></td>
                                <td><?php echo secureOutput(substr($requestObj->getTitre(), 0, 40)); ?></td>
                                <td>
                                    <span class="badge badge-custom badge-<?php echo $requestObj->getUrgence(); ?>">
                                        <?php echo ucfirst($requestObj->getUrgence()); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-custom badge-<?php echo $requestObj->getStatut(); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $requestObj->getStatut())); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($requestObj->getDateCreation())); ?></td>
                                <td>
                                    <a href="request_conversation.php?id=<?php echo $requestObj->getId(); ?>" class="btn btn-sm btn-primary">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function startRequest(requestId) {
    if (confirm('Voulez-vous commencer à travailler sur cette demande?')) {
        window.location.href = '../../../controller/support/counselor_start_request.php?id=' + requestId;
    }
}

function completeRequest(requestId) {
    if (confirm('Voulez-vous marquer cette demande comme terminée?')) {
        window.location.href = '../../../controller/support/counselor_complete_request.php?id=' + requestId;
    }
}
</script>
</body>
</html>

