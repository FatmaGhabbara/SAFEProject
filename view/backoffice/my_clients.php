<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/user.php';
require_once __DIR__ . '/../../model/SupportRequest.php';
require_once __DIR__ . '/../../controller/SupportController.php';

// Check if logged in and is counselor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'conseilleur') {
    header('Location: ../frontoffice/login.php');
    exit();
}

require_once __DIR__ . '/../../controller/usercontroller.php';

$user = new User($_SESSION['user_id']);
$supportController = new SupportController();
$userController = new UserController();

// Get all requests assigned to this counselor
$assignedRequests = $supportController->findRequestsByCounselor($_SESSION['user_id']);

// Group by user to get unique clients
$clients = [];
foreach ($assignedRequests as $request) {
    $userId = $request->getUserId();
    if (!isset($clients[$userId])) {
        $clientUser = $userController->getUserById($userId);
        $clients[$userId] = [
            'user' => $clientUser,
            'requests' => [],
            'total_requests' => 0,
            'active_requests' => 0,
            'last_activity' => null
        ];
    }
    
    $clients[$userId]['requests'][] = $request;
    $clients[$userId]['total_requests']++;
    
    if (in_array($request->getStatut(), ['assignee', 'en_cours'])) {
        $clients[$userId]['active_requests']++;
    }
    
    $requestDate = strtotime($request->getCreatedAt());
    if ($clients[$userId]['last_activity'] === null || $requestDate > $clients[$userId]['last_activity']) {
        $clients[$userId]['last_activity'] = $requestDate;
    }
}

// Helper function for profile picture
function getProfilePictureUrl($user) {
    $photo = $user->getProfilePicture();
    if (!empty($photo) && $photo !== 'assets/images/default-avatar.png' && file_exists(__DIR__ . '/../frontoffice/assets/images/uploads/' . $photo)) {
        return '../frontoffice/assets/images/uploads/' . htmlspecialchars($photo);
    }
    return '../frontoffice/assets/images/default-avatar.png';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Clients - SafeSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .page-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .page-title {
            color: #2c3e50;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .page-title i {
            color: #4e73df;
        }
        
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .client-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            display: block;
            width: 100%;
            min-height: 200px;
        }
        
        .client-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .client-header {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .client-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #4e73df;
            flex-shrink: 0;
        }
        
        .client-info {
            flex: 1;
        }
        
        .client-info h3 {
            color: #2c3e50;
            margin: 0 0 0.5rem 0;
            font-weight: 600;
            font-size: 1.5rem;
            display: block;
        }
        
        .client-email {
            color: #6c757d;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .client-stats {
            display: flex;
            gap: 2rem;
            margin: 1rem 0;
            padding: 1rem;
            background: #f8f9fc;
            border-radius: 10px;
        }
        
        .client-stat {
            text-align: center;
        }
        
        .client-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #4e73df;
        }
        
        .client-stat-label {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .client-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 0.6rem 1.5rem;
            border-radius: 25px;
            border: none;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.4);
            color: white;
        }
        
        .btn-secondary-custom {
            background: #f8f9fc;
            color: #4e73df;
            border: 2px solid #4e73df;
        }
        
        .btn-secondary-custom:hover {
            background: #4e73df;
            color: white;
        }
        
        .badge-status {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .badge-active {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .last-activity {
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }
        
        .back-btn {
            background: rgba(255, 255, 255, 0.95);
            color: #4e73df;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .back-btn:hover {
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            color: #4e73df;
        }
        
        .empty-state {
            background: white;
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #e3e6f0;
            margin-bottom: 1rem;
        }
        
        .empty-state h3 {
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        
        .empty-state p {
            color: #a0aec0;
        }
        
        .clients-container {
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="page-title">
                    <i class="fas fa-users"></i>
                    Mes Clients
                </h1>
                <a href="adviser_dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Retour au tableau de bord
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Statistics Overview -->
        <div class="stats-overview">
            <div class="stat-card">
                <div class="stat-icon" style="color: #667eea;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?= count($clients) ?></div>
                <div class="stat-label">Total Clients</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: #38b2ac;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value">
                    <?php 
                    $activeCount = 0;
                    foreach ($clients as $client) {
                        if ($client['active_requests'] > 0) $activeCount++;
                    }
                    echo $activeCount;
                    ?>
                </div>
                <div class="stat-label">Clients Actifs</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: #ed8936;">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="stat-value">
                    <?php 
                    $totalRequests = 0;
                    foreach ($clients as $client) {
                        $totalRequests += $client['total_requests'];
                    }
                    echo $totalRequests;
                    ?>
                </div>
                <div class="stat-label">Total Demandes</div>
            </div>
        </div>

        <!-- Client List -->
        <div class="clients-container">
        <?php 
        // Debug: Check if we have clients
        error_log("Total clients found: " . count($clients));
        if (empty($clients)): 
        ?>
            <div class="empty-state">
                <i class="fas fa-user-friends"></i>
                <h3>Aucun client pour le moment</h3>
                <p>Les clients apparaîtront ici lorsque des demandes vous seront assignées.</p>
            </div>
        <?php else: ?>
            <h4 class="mb-4" style="color: white; font-weight: 600;">
                <i class="fas fa-address-book"></i> Liste de mes clients (<?= count($clients) ?>)
            </h4>
            <?php foreach ($clients as $clientData): ?>
                <?php $clientUser = $clientData['user']; ?>
                <div class="client-card">
                    <div class="client-header">
                        <img src="<?= getProfilePictureUrl($clientUser) ?>" 
                             alt="Photo de profil" 
                             class="client-avatar"
                             onerror="this.src='../frontoffice/assets/images/default-avatar.png'">
                        
                        <div class="client-info flex-grow-1">
                            <h3><?= htmlspecialchars($clientUser->getNom()) ?></h3>
                            <div class="client-email">
                                <i class="fas fa-envelope"></i>
                                <?= htmlspecialchars($clientUser->getEmail()) ?>
                            </div>
                            <?php if ($clientData['active_requests'] > 0): ?>
                                <span class="badge-status badge-active mt-2">
                                    <i class="fas fa-circle"></i> Actif
                                </span>
                            <?php else: ?>
                                <span class="badge-status badge-inactive mt-2">
                                    <i class="fas fa-circle"></i> Inactif
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="client-stats">
                        <div class="client-stat">
                            <div class="client-stat-value"><?= $clientData['total_requests'] ?></div>
                            <div class="client-stat-label">Demandes totales</div>
                        </div>
                        <div class="client-stat">
                            <div class="client-stat-value"><?= $clientData['active_requests'] ?></div>
                            <div class="client-stat-label">Demandes actives</div>
                        </div>
                        <div class="client-stat">
                            <div class="client-stat-value">
                                <?= count($clientData['requests']) - $clientData['active_requests'] ?>
                            </div>
                            <div class="client-stat-label">Terminées</div>
                        </div>
                    </div>
                    
                    <?php if ($clientData['last_activity']): ?>
                        <div class="last-activity">
                            <i class="fas fa-clock"></i>
                            Dernière activité: <?= date('d/m/Y à H:i', $clientData['last_activity']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="client-actions mt-3">
                        <a href="client_conversations.php?client_id=<?= $clientUser->getId() ?>" 
                           class="btn-action btn-primary-custom">
                            <i class="fas fa-comments"></i>
                            Voir les conversations
                        </a>
                        <a href="conseiller_support_dashboard.php" 
                           class="btn-action btn-secondary-custom">
                            <i class="fas fa-list"></i>
                            Toutes les demandes
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
