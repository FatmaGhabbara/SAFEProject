<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/user.php';
require_once __DIR__ . '/../../model/SupportRequest.php';
require_once __DIR__ . '/../../model/SupportMessage.php';
require_once __DIR__ . '/../../controller/SupportController.php';
require_once __DIR__ . '/../../controller/usercontroller.php';

// Check if logged in and is counselor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'conseilleur') {
    header('Location: ../frontoffice/login.php');
    exit();
}

$clientId = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

if ($clientId === 0) {
    header('Location: my_clients.php');
    exit();
}

$userController = new UserController();
$user = $userController->getUserById($_SESSION['user_id']);
$clientUser = $userController->getUserById($clientId);
$supportController = new SupportController();

// Get all requests for this client assigned to this counselor
$allRequests = $supportController->findRequestsByCounselor($_SESSION['user_id']);
$clientRequests = array_filter($allRequests, function($request) use ($clientId) {
    return $request->getUserId() == $clientId;
});

// Helper function for profile picture
function getProfilePictureUrl($user) {
    $photo = $user->getProfilePicture();
    if (!empty($photo) && $photo !== 'assets/images/default-avatar.png' && file_exists(__DIR__ . '/../frontoffice/assets/images/uploads/' . $photo)) {
        return '../frontoffice/assets/images/uploads/' . htmlspecialchars($photo);
    }
    return '../frontoffice/assets/images/default-avatar.png';
}

function getStatusBadge($statut) {
    $badges = [
        'en_attente' => ['class' => 'badge-warning', 'icon' => 'clock', 'text' => 'En attente'],
        'assignee' => ['class' => 'badge-info', 'icon' => 'user-check', 'text' => 'Assignée'],
        'en_cours' => ['class' => 'badge-primary', 'icon' => 'spinner', 'text' => 'En cours'],
        'terminee' => ['class' => 'badge-success', 'icon' => 'check-circle', 'text' => 'Terminée'],
        'annulee' => ['class' => 'badge-danger', 'icon' => 'times-circle', 'text' => 'Annulée']
    ];
    
    $badge = $badges[$statut] ?? $badges['en_attente'];
    return '<span class="badge-status ' . $badge['class'] . '"><i class="fas fa-' . $badge['icon'] . '"></i> ' . $badge['text'] . '</span>';
}

function getUrgenceBadge($urgence) {
    $badges = [
        'basse' => ['class' => 'badge-urgence-low', 'text' => 'Basse'],
        'moyenne' => ['class' => 'badge-urgence-medium', 'text' => 'Moyenne'],
        'haute' => ['class' => 'badge-urgence-high', 'text' => 'Haute']
    ];
    
    $badge = $badges[$urgence] ?? $badges['moyenne'];
    return '<span class="' . $badge['class'] . '">' . $badge['text'] . '</span>';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversations - <?= htmlspecialchars($clientUser->getNom()) ?> - SafeSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 3rem;
        }
        
        .page-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .client-profile {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .client-avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #4e73df;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .client-details h1 {
            color: #2c3e50;
            margin: 0 0 0.5rem 0;
            font-weight: 700;
        }
        
        .client-details p {
            color: #6c757d;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .back-btn {
            background: rgba(255, 255, 255, 0.95);
            color: #667eea;
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
            color: #667eea;
        }
        
        .conversation-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }
        
        .conversation-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .conversation-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f8f9fc;
        }
        
        .conversation-title {
            flex: 1;
        }
        
        .conversation-title h3 {
            color: #2c3e50;
            margin: 0 0 0.5rem 0;
            font-weight: 600;
        }
        
        .conversation-meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .conversation-meta span {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .conversation-description {
            background: #f8f9fc;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #667eea;
        }
        
        .conversation-description p {
            margin: 0;
            color: #5a5c69;
            line-height: 1.6;
        }
        
        .message-count {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #667eea;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .conversation-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 0.7rem 1.5rem;
            border-radius: 25px;
            border: none;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            cursor: pointer;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .btn-success-custom {
            background: linear-gradient(135deg, #38b2ac 0%, #2c7a7b 100%);
            color: white;
        }
        
        .btn-success-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(56, 178, 172, 0.4);
            color: white;
        }
        
        .btn-secondary-custom {
            background: #f8f9fc;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-secondary-custom:hover {
            background: #667eea;
            color: white;
        }
        
        .badge-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }
        
        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .badge-primary {
            background: #e0e7ff;
            color: #4338ca;
        }
        
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .badge-urgence-low {
            background: #d1fae5;
            color: #065f46;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-urgence-medium {
            background: #fef3c7;
            color: #92400e;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-urgence-high {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
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
        
        .admin-note {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        
        .admin-note-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: #856404;
            margin-bottom: 0.5rem;
        }
        
        .admin-note-content {
            color: #856404;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="my_clients.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Retour aux clients
                </a>
            </div>
            
            <div class="client-profile">
                <img src="<?= getProfilePictureUrl($clientUser) ?>" 
                     alt="Photo de profil" 
                     class="client-avatar-large"
                     onerror="this.src='../frontoffice/assets/images/default-avatar.png'">
                
                <div class="client-details">
                    <h1><?= htmlspecialchars($clientUser->getNom()) ?></h1>
                    <p>
                        <i class="fas fa-envelope"></i>
                        <?= htmlspecialchars($clientUser->getEmail()) ?>
                    </p>
                    <p class="mt-2">
                        <i class="fas fa-comments"></i>
                        <?= count($clientRequests) ?> conversation(s)
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (empty($clientRequests)): ?>
            <div class="empty-state">
                <i class="fas fa-comments"></i>
                <h3>Aucune conversation</h3>
                <p>Aucune demande de support n'a été trouvée pour ce client.</p>
            </div>
        <?php else: ?>
            <?php foreach ($clientRequests as $request): ?>
                <?php 
                $messages = $supportController->findMessagesByRequest($request->getId());
                $messageCount = count($messages);
                ?>
                <div class="conversation-card">
                    <div class="conversation-header">
                        <div class="conversation-title">
                            <h3><?= htmlspecialchars($request->getTitre()) ?></h3>
                            <div class="conversation-meta">
                                <span>
                                    <i class="fas fa-calendar"></i>
                                    <?= date('d/m/Y', strtotime($request->getCreatedAt())) ?>
                                </span>
                                <span>
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Urgence: <?= getUrgenceBadge($request->getUrgence()) ?>
                                </span>
                                <?= getStatusBadge($request->getStatut()) ?>
                            </div>
                        </div>
                        <div class="message-count">
                            <i class="fas fa-comment-dots"></i>
                            <?= $messageCount ?> message<?= $messageCount > 1 ? 's' : '' ?>
                        </div>
                    </div>
                    
                    <div class="conversation-description">
                        <p><?= nl2br(htmlspecialchars($request->getDescription())) ?></p>
                    </div>
                    
                    <?php if (!empty($request->getAdminNote())): ?>
                        <div class="admin-note">
                            <div class="admin-note-header">
                                <i class="fas fa-sticky-note"></i>
                                Note de l'administrateur
                            </div>
                            <p class="admin-note-content"><?= nl2br(htmlspecialchars($request->getAdminNote())) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="conversation-actions mt-3">
                        <a href="conseiller_support_request_details.php?id=<?= $request->getId() ?>" 
                           class="btn-action btn-primary-custom">
                            <i class="fas fa-eye"></i>
                            Voir la conversation
                        </a>
                        
                        <?php if (in_array($request->getStatut(), ['assignee', 'en_cours'])): ?>
                            <a href="conseiller_support_request_details.php?id=<?= $request->getId() ?>#reply" 
                               class="btn-action btn-success-custom">
                                <i class="fas fa-reply"></i>
                                Répondre
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
