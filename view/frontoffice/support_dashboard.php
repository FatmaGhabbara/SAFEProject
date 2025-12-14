<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/SupportController.php';
require_once __DIR__ . '/../../controller/usercontroller.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$userController = new UserController();
$user = $userController->getUserById($userId);

$supportController = new SupportController();
$requests = $supportController->findRequestsByUser($userId);

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
    <title>Mes Demandes de Support - SafeSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .dashboard-container {
            padding: 2rem 0;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 0.5rem 0;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .table-dashboard {
            background: white;
        }
        .table-dashboard th {
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }
        .btn-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        .navbar-custom .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #4e73df;
        }
        .navbar-custom .nav-link {
            color: #495057;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: all 0.3s;
        }
        .navbar-custom .nav-link:hover {
            color: #4e73df;
        }
        .navbar-custom .btn-logout {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 500;
        }
        .navbar-custom .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>

<!-- Navigation Header -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-heart text-danger me-2"></i>SAFEProject
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="../backoffice/member_dashboard.php">
                        <i class="fas fa-home me-1"></i> Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="support_dashboard.php">
                        <i class="fas fa-hands-helping me-1"></i> Mes demandes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../backoffice/edit_profile.php">
                        <i class="fas fa-user-circle me-1"></i> Mon profil
                    </a>
                </li>
                <li class="nav-item ms-2">
                    <a href="logout.php" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt me-1"></i> Déconnexion
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="dashboard-container">
    <div class="container">
        <div class="glass-panel">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="mb-2">Bonjour, <?php echo htmlspecialchars($user->getNom()); ?> 👋</h1>
                    <p class="text-muted mb-0">Gérez vos demandes de support psychologique</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="create_support_request.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus-circle me-2"></i> Nouvelle demande
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label">EN ATTENTE</div>
                            <div class="stat-value text-warning"><?php echo $pending; ?></div>
                            <div class="stat-label">demandes à traiter</div>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,193,7,0.12); color: #ffc107;">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label">EN COURS</div>
                            <div class="stat-value text-info"><?php echo $in_progress; ?></div>
                            <div class="stat-label">demandes suivies</div>
                        </div>
                        <div class="stat-icon" style="background: rgba(13,202,240,0.12); color: #0dcaf0;">
                            <i class="fas fa-spinner"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label">TERMINÉES</div>
                            <div class="stat-value text-success"><?php echo $completed; ?></div>
                            <div class="stat-label">demandes clôturées</div>
                        </div>
                        <div class="stat-icon" style="background: rgba(25,135,84,0.12); color: #198754;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-panel">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">Mes Demandes</h3>
                <a href="../backoffice/member_dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Retour au dashboard
                </a>
            </div>

            <?php if (empty($requests)): ?>
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-inbox fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted mb-3">Aucune demande pour le moment</h4>
                    <p class="text-muted mb-4">Créez votre première demande de support pour commencer</p>
                    <a href="create_support_request.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus me-2"></i> Créer ma première demande
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Titre</th>
                                <th>Statut</th>
                                <th>Urgence</th>
                                <th>Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $req): ?>
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
                                    <a href="support_request_details.php?id=<?php echo $req->getId(); ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i> Voir
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
</body>
</html>
