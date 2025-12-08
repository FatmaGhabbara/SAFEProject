<?php
session_start();
require_once '../../../config.php';
require_once '../../../model/User.php';
require_once '../../../model/SupportRequest.php';
require_once '../../../controller/helpers.php';

// Check admin access
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../frontoffice/login.php');
    exit();
}

// Get counselor ID
$counselorId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($counselorId === 0) {
    setFlashMessage('Conseiller invalide.', 'error');
    header('Location: counselors_list.php');
    exit();
}

// Get the counselor
$counselorUser = getCounselorById($counselorId);
if (!$counselorUser) {
    setFlashMessage('Conseiller introuvable.', 'error');
    header('Location: counselors_list.php');
    exit();
}

$user = $counselorUser;
$allRequests = findSupportRequestsByCounselor($counselorId);
$activeRequests = array_filter($allRequests, function($req) {
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
    <title>Détails Conseiller - SAFEProject</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
        }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .info-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-box {
            text-align: center;
            padding: 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .stat-box h3 {
            font-size: 2.5rem;
            margin: 0;
        }
        .stat-box p {
            margin: 0;
            opacity: 0.9;
        }
        .badge-custom {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }
    </style>
</head>
<body>

<?php include '../../includes/navbar.php'; ?>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-user-md"></i> Détails du Conseiller</h1>
                <p class="mb-0">Informations complètes et statistiques</p>
            </div>
            <div>
                <a href="counselors_list.php" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <a href="edit_counselor.php?id=<?php echo $counselorUser->getId(); ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Modifier
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $flash['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Left Column: Info -->
        <div class="col-md-4">
            <div class="info-card text-center">
                <div class="mb-3">
                    <i class="fas fa-user-circle fa-5x text-primary"></i>
                </div>
                <h3><?php echo secureOutput($user->getFullName()); ?></h3>
                <p class="text-muted"><?php echo secureOutput($user->getEmail()); ?></p>
                <span class="badge bg-primary">Conseiller</span>
                
                <hr>
                
                <div class="text-start">
                    <p><strong><i class="fas fa-id-badge"></i> ID:</strong> <?php echo $counselorUser->getId(); ?></p>
                    <p><strong><i class="fas fa-briefcase"></i> Spécialité:</strong> <?php echo secureOutput($counselorUser->getSpecialite() ?? 'Psychologie'); ?></p>
                    <p><strong><i class="fas fa-calendar"></i> Inscription:</strong> <?php echo date('d/m/Y', strtotime($user->getDateInscription())); ?></p>
                    <p><strong><i class="fas fa-info-circle"></i> Statut:</strong> 
                        <span class="badge bg-success"><?php echo ucfirst($user->getStatut()); ?></span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Column: Stats & Requests -->
        <div class="col-md-8">
            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stat-box">
                        <h3><?php echo count($activeRequests); ?></h3>
                        <p>Demandes Actives</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <h3><?php echo count($allRequests); ?></h3>
                        <p>Total Demandes</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <h3><?php echo count($allRequests) - count($activeRequests); ?></h3>
                        <p>Terminées</p>
                    </div>
                </div>
            </div>

            <!-- Active Requests -->
            <div class="info-card">
                <h4 class="mb-3"><i class="fas fa-tasks text-primary"></i> Demandes Actives</h4>
                
                <?php if (empty($activeRequests)): ?>
                    <p class="text-muted">Aucune demande active pour le moment.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Utilisateur</th>
                                    <th>Titre</th>
                                    <th>Urgence</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activeRequests as $requestObj): 
                                    $requestUser = $requestObj->getUser();
                                ?>
                                <tr>
                                    <td><?php echo $requestObj->getId(); ?></td>
                                    <td><?php echo secureOutput($requestUser->getFullName()); ?></td>
                                    <td><?php echo secureOutput(substr($requestObj->getTitre(), 0, 30)); ?>...</td>
                                    <td>
                                        <span class="badge bg-<?php echo $requestObj->getUrgence() === 'haute' ? 'danger' : ($requestObj->getUrgence() === 'moyenne' ? 'warning' : 'info'); ?>">
                                            <?php echo ucfirst($requestObj->getUrgence()); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo ucfirst(str_replace('_', ' ', $requestObj->getStatut())); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($requestObj->getDateCreation())); ?></td>
                                    <td>
                                        <a href="request_conversation.php?id=<?php echo $requestObj->getId(); ?>" class="btn btn-sm btn-outline-primary">
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

            <!-- All Requests History -->
            <div class="info-card">
                <h4 class="mb-3"><i class="fas fa-history text-primary"></i> Historique Complet</h4>
                
                <?php if (empty($allRequests)): ?>
                    <p class="text-muted">Aucune demande trouvée.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Titre</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allRequests as $requestObj): ?>
                                <tr>
                                    <td><?php echo $requestObj->getId(); ?></td>
                                    <td><?php echo secureOutput(substr($requestObj->getTitre(), 0, 40)); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $requestObj->getStatut() === 'terminee' ? 'success' : 'primary'; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $requestObj->getStatut())); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($requestObj->getDateCreation())); ?></td>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

