<?php
session_start();

require_once '../../../config.php';
require_once '../../../controller/helpers.php';
require_once '../../../model/User.php';
require_once '../../../model/SupportRequest.php';

// Vérification des droits d'accès
if (!isLoggedIn() || !in_array($_SESSION['role'], ['counselor', 'admin'])) {
    header('Location: ../../frontoffice/login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Récupération du profil conseiller
$counselorUser = getCounselorByUserId($userId);

if (!$counselorUser) {
    setFlashMessage('Profil conseiller introuvable.', 'error');
    header('Location: ../../frontoffice/dashboard.php');
    exit();
}

// Statistiques
$counselorId = $counselorUser->getId();
$allRequests = findSupportRequestsByCounselor($counselorId);
$activeRequests = array_filter($allRequests, function($req) {
    return in_array($req->getStatut(), ['assignee', 'en_cours']);
});
$completedRequests = array_filter($allRequests, function($req) {
    return $req->getStatut() === 'terminee';
});
$pendingRequests = array_filter($allRequests, function($req) {
    return $req->getStatut() === 'en_attente';
});
$newAssignedRequests = array_filter($allRequests, function($req) {
    return $req->getStatut() === 'assignee';
});

// Message flash
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Conseiller - SAFEProject</title>
    <link rel="stylesheet" href="../../frontoffice/assets/css/main.css">
    <link rel="stylesheet" href="../../frontoffice/assets/css/dashboard-dark.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
<body class="dashboard-dark">

<?php include '../../includes/navbar.php'; ?>

<section class="dashboard-shell">
    <div class="container">
        <div class="glass-panel p-4 p-lg-5 mb-5">
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show mb-4" role="alert">
                    <?php echo secureOutput($flash['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <div class="row align-items-center gy-3">
                <div class="col-lg-8">
                    <div class="section-title">Espace conseiller</div>
                    <h1 class="section-heading mb-2">Bonjour, <?php echo secureOutput($counselorUser->getFullName()); ?> 👋</h1>
                    <p class="text-soft mb-0">
                        Suivez vos demandes assignées, vos actions et mettez à jour votre profil dans une interface harmonisée avec la page d'accueil.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex flex-wrap gap-3 justify-content-lg-end">
                        <a href="my_assigned_requests.php" class="btn btn-primary btn-lg shadow-sm">
                            <i class="fas fa-inbox me-2"></i> Mes demandes
                        </a>
                        <a href="../../frontoffice/profil.php" class="btn btn-ghost btn-lg">
                            <i class="fas fa-user-circle me-2"></i> Mon profil
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row gy-4">
            <div class="col-md-3">
                <div class="card glass-card h-100 hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="section-title text-uppercase">Total</div>
                                <div class="stat-value"><?php echo count($allRequests); ?></div>
                                <div class="stat-label">demandes suivies</div>
                            </div>
                            <div class="stat-icon" style="background: rgba(143,209,255,0.12); color: #8fd1ff;">
                                <i class="fas fa-layer-group"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card glass-card h-100 hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="section-title text-uppercase">Actives</div>
                                <div class="stat-value"><?php echo count($activeRequests); ?></div>
                                <div class="stat-label">assignées ou en cours</div>
                            </div>
                            <div class="stat-icon" style="background: rgba(13,202,240,0.12); color: #0dcaf0;">
                                <i class="fas fa-spinner"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card glass-card h-100 hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="section-title text-uppercase">Terminées</div>
                                <div class="stat-value"><?php echo count($completedRequests); ?></div>
                                <div class="stat-label">demandes clôturées</div>
                            </div>
                            <div class="stat-icon" style="background: rgba(25,135,84,0.12); color: #25d49d;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card glass-card h-100 hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="section-title text-uppercase">Nouvelles</div>
                                <div class="stat-value"><?php echo count($newAssignedRequests); ?></div>
                                <div class="stat-label">assignations à traiter</div>
                            </div>
                            <div class="stat-icon" style="background: rgba(255,193,7,0.12); color: #ffc107;">
                                <i class="fas fa-bell"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row gy-4 mt-1">
            <div class="col-lg-5">
                <div class="card glass-card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <div class="section-title mb-1">Actions</div>
                            <h5 class="mb-0 text-white">Raccourcis</h5>
                        </div>
                        <i class="fas fa-bolt text-warning"></i>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="my_assigned_requests.php" class="list-group-item d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold text-white">Voir mes demandes assignées</div>
                                    <div class="text-soft small">Suivez vos dossiers prioritaires.</div>
                                </div>
                                <span class="badge badge-glass"><i class="fas fa-inbox"></i></span>
                            </a>
                            <a href="../../frontoffice/profil.php" class="list-group-item d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold text-white">Mettre à jour mon profil</div>
                                    <div class="text-soft small">Actualisez vos informations professionnelles.</div>
                                </div>
                                <span class="badge badge-glass"><i class="fas fa-user-edit"></i></span>
                            </a>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="support_requests.php" class="list-group-item d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold text-white">Vue globale (admin)</div>
                                    <div class="text-soft small">Accès à toutes les demandes.</div>
                                </div>
                                <span class="badge badge-glass"><i class="fas fa-list"></i></span>
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="small-note mt-3">
                            Nouveaux dossiers en attente : <?php echo count($pendingRequests); ?> · Assignations non ouvertes : <?php echo count($newAssignedRequests); ?>
                        </div>
                    </div>
                </div>

                <div class="card glass-card h-100 mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <div class="section-title mb-1">Profil</div>
                            <h5 class="mb-0 text-white">Informations</h5>
                        </div>
                        <i class="fas fa-user text-primary"></i>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Nom complet :</strong> <?php echo secureOutput($counselorUser->getFullName()); ?></p>
                        <p class="mb-2"><strong>Email :</strong> <?php echo secureOutput($counselorUser->getEmail()); ?></p>
                        <p class="mb-2"><strong>Spécialité :</strong> <?php echo secureOutput($counselorUser->getSpecialite() ?? 'Non spécifiée'); ?></p>
                        <p class="mb-2">
                            <strong>Statut :</strong>
                            <span class="badge bg-<?php echo $counselorUser->getStatutCounselor() === 'actif' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($counselorUser->getStatutCounselor() ?? 'inactif'); ?>
                            </span>
                        </p>
                        <p class="mb-0"><strong>Inscription :</strong> <?php echo date('d/m/Y', strtotime($counselorUser->getDateInscription())); ?></p>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card glass-card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <div class="section-title mb-1">Suivi</div>
                            <h5 class="mb-0 text-white">Dernières demandes assignées</h5>
                        </div>
                        <i class="fas fa-history text-primary"></i>
                    </div>
                    <div class="card-body">
                        <?php if (empty($allRequests)): ?>
                            <div class="text-center py-4 text-soft">
                                <div class="mb-3">
                                    <span class="stat-icon" style="background: rgba(143,209,255,0.12); color: #8fd1ff;">
                                        <i class="fas fa-inbox"></i>
                                    </span>
                                </div>
                                <p class="mb-3">Aucune demande assignée pour l'instant.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-borderless align-middle table-dashboard mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Titre</th>
                                            <th>Statut</th>
                                            <th>Urgence</th>
                                            <th>Date</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($allRequests, 0, 5) as $req): ?>
                                        <tr>
                                            <td><?php echo $req->getId(); ?></td>
                                            <td><?php echo htmlspecialchars($req->getTitre()); ?></td>
                                            <td>
                                                <?php
                                                $badges = [
                                                    'en_attente' => 'warning',
                                                    'assignee' => 'info',
                                                    'en_cours' => 'primary',
                                                    'terminee' => 'success',
                                                    'annulee' => 'secondary'
                                                ];
                                                $badge = $badges[$req->getStatut()] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $badge; ?>">
                                                    <?php echo htmlspecialchars($req->getStatut()); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $urgency_badges = [
                                                    'basse' => 'secondary',
                                                    'moyenne' => 'warning',
                                                    'haute' => 'danger'
                                                ];
                                                $urgency_badge = $urgency_badges[$req->getUrgence()] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $urgency_badge; ?>">
                                                    <?php echo htmlspecialchars($req->getUrgence()); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($req->getDateCreation())); ?></td>
                                            <td class="text-end">
                                                <a href="request_conversation.php?id=<?php echo $req->getId(); ?>" class="btn btn-sm btn-outline-light">
                                                    <i class="fas fa-eye me-1"></i> Ouvrir
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="my_assigned_requests.php" class="btn btn-ghost">
                                    Voir toutes les demandes <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card glass-card h-100 mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <div class="section-title mb-1">Présentation</div>
                            <h5 class="mb-0 text-white">Biographie</h5>
                        </div>
                        <i class="fas fa-book-open text-primary"></i>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?php echo secureOutput($counselorUser->getBiographie() ?? 'Aucune biographie disponible.'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

