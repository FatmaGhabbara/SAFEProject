<?php
session_start();
require_once '../../config.php';
require_once '../../model/User.php';
require_once '../../model/SupportRequest.php';
require_once '../../controller/helpers.php';

// Check if logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$user = new User($userId);

// Get user's requests
$requests = findSupportRequestsByUser($userId);

// Count by status
$pending = 0;
$in_progress = 0;
$completed = 0;

foreach ($requests as $req) {
    switch ($req->getStatut()) {
        case 'en_attente':
            $pending++;
            break;
        case 'assignee':
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SAFEProject</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/dashboard-dark.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-dark">

<?php include '../includes/navbar.php'; ?>

<section class="dashboard-shell">
    <div class="container">
        <div class="glass-panel p-4 p-lg-5 mb-5">
            <div class="row align-items-center gy-3">
                <div class="col-lg-8">
                    <div class="section-title">Tableau de bord</div>
                    <h1 class="section-heading mb-2">Bonjour, <?php echo htmlspecialchars($user->getFullName()); ?> 👋</h1>
                    <p class="text-soft mb-0">Retrouvez vos demandes et vos actions en un coup d'œil.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex flex-wrap gap-3 justify-content-lg-end">
                        <a href="support/support_form.php" class="btn btn-primary btn-lg shadow-sm">
                            <i class="fas fa-plus-circle me-2"></i> Nouvelle demande
                        </a>
                        <a href="support/my_requests.php" class="btn btn-ghost btn-lg">
                            <i class="fas fa-inbox me-2"></i> Mes demandes
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row gy-4">
            <div class="col-md-4">
                <div class="card glass-card h-100 hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="section-title text-uppercase">En attente</div>
                                <div class="stat-value"><?php echo $pending; ?></div>
                                <div class="stat-label">demandes à traiter</div>
                            </div>
                            <div class="stat-icon" style="background: rgba(255,193,7,0.12); color: #ffc107;">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card glass-card h-100 hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="section-title text-uppercase">En cours</div>
                                <div class="stat-value"><?php echo $in_progress; ?></div>
                                <div class="stat-label">demandes suivies</div>
                            </div>
                            <div class="stat-icon" style="background: rgba(13,202,240,0.12); color: #0dcaf0;">
                                <i class="fas fa-spinner"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card glass-card h-100 hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="section-title text-uppercase">Terminées</div>
                                <div class="stat-value"><?php echo $completed; ?></div>
                                <div class="stat-label">demandes clôturées</div>
                            </div>
                            <div class="stat-icon" style="background: rgba(25,135,84,0.12); color: #25d49d;">
                                <i class="fas fa-check-circle"></i>
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
                            <div class="section-title mb-1">Organisation</div>
                            <h5 class="mb-0 text-white">Actions rapides</h5>
                        </div>
                        <i class="fas fa-bolt text-warning"></i>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="support/support_form.php" class="list-group-item d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold text-white">Créer une nouvelle demande</div>
                                    <div class="text-soft small">Décrivez votre besoin et trouvez un conseiller.</div>
                                </div>
                                <span class="badge badge-glass"><i class="fas fa-plus"></i></span>
                            </a>
                            <a href="support/my_requests.php" class="list-group-item d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold text-white">Suivre mes demandes</div>
                                    <div class="text-soft small">Consultez l'état et échangez avec votre conseiller.</div>
                                </div>
                                <span class="badge badge-glass"><i class="fas fa-inbox"></i></span>
                            </a>
                            <a href="profil.php" class="list-group-item d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold text-white">Mettre à jour mon profil</div>
                                    <div class="text-soft small">Actualisez vos coordonnées et préférences.</div>
                                </div>
                                <span class="badge badge-glass"><i class="fas fa-user-circle"></i></span>
                            </a>
                        </div>
                        <div class="small-note mt-3">
                            Besoin d'aide ? Contactez-nous à <a href="mailto:info@safeproject.com" class="text-decoration-none text-soft">info@safeproject.com</a>.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card glass-card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <div class="section-title mb-1">Suivi</div>
                            <h5 class="mb-0 text-white">Dernières demandes</h5>
                        </div>
                        <i class="fas fa-history text-primary"></i>
                    </div>
                    <div class="card-body">
                        <?php if (empty($requests)): ?>
                            <div class="text-center py-4 text-soft">
                                <div class="mb-3">
                                    <span class="stat-icon" style="background: rgba(143,209,255,0.12); color: #8fd1ff;">
                                        <i class="fas fa-inbox"></i>
                                    </span>
                                </div>
                                <p class="mb-3">Aucune demande pour le moment.</p>
                                <a href="support/support_form.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i> Créer ma première demande
                                </a>
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
                                        <?php foreach (array_slice($requests, 0, 5) as $req): ?>
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
                                                <a href="support/request_details.php?id=<?php echo $req->getId(); ?>" 
                                                   class="btn btn-sm btn-outline-light">
                                                    <i class="fas fa-eye me-1"></i> Voir
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="support/my_requests.php" class="btn btn-ghost">
                                    Voir toutes les demandes <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

