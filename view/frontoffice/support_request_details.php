<?php
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/SupportRequest.php';
require_once __DIR__ . '/../../model/SupportMessage.php';
require_once __DIR__ . '/../../controller/SupportController.php';
require_once __DIR__ . '/../../controller/usercontroller.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$requestId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($requestId === 0) {
    header('Location: support_dashboard.php');
    exit();
}

$request = new SupportRequest($requestId);

if (!$request->getId() || $request->getUserId() != $_SESSION['user_id']) {
    header('Location: support_dashboard.php');
    exit();
}

$supportController = new SupportController();
$userController = new UserController();

$messages = $supportController->findMessagesByRequest($requestId);

$counselorId = $request->getCounselorId();
$counselorUser = $counselorId ? $userController->getUserById($counselorId) : null;

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $messageText = trim($_POST['message']);
    
    if (!empty($messageText) && strlen($messageText) >= 10) {
        $newMessage = new SupportMessage();
        $newMessage->setSupportRequestId($requestId);
        $newMessage->setSenderId($_SESSION['user_id']);
        $newMessage->setMessage($messageText);
        
        if ($newMessage->save()) {
            header('Location: support_request_details.php?id=' . $requestId);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la demande - SafeSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            min-height: 100vh;
            padding: 20px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            max-width: 900px;
        }
        
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
            background: white;
        }
        
        .card-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            border-radius: 20px 20px 0 0 !important;
            padding: 1.5rem;
            border: none;
        }
        
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .status-en_attente {
            background-color: #ffc107;
            color: #000;
        }
        
        .status-assignee {
            background-color: #17a2b8;
            color: white;
        }
        
        .status-en_cours {
            background-color: #007bff;
            color: white;
        }
        
        .status-terminee {
            background-color: #28a745;
            color: white;
        }
        
        .status-annulee {
            background-color: #dc3545;
            color: white;
        }
        
        .urgency-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
        }
        
        .urgency-faible {
            background-color: #d4edda;
            color: #155724;
        }
        
        .urgency-moyenne {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .urgency-haute {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .message-container {
            max-height: 500px;
            overflow-y: auto;
            padding: 20px;
            background: linear-gradient(to bottom, #f8f9fc 0%, #ffffff 100%);
            border-radius: 15px;
        }
        
        .message-bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 15px;
            margin-bottom: 10px;
            word-wrap: break-word;
        }
        
        .message-sent {
            background-color: #007bff;
            color: white;
            margin-left: auto;
        }
        
        .message-received {
            background-color: #e9ecef;
            color: #333;
        }
        
        .message-sender {
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 5px;
            color: #495057;
        }
        
        .message-content {
            margin-bottom: 5px;
            line-height: 1.4;
        }
        
        .message-time {
            font-size: 0.7rem;
            opacity: 0.7;
            text-align: right;
        }
        
        .message-sent .message-time {
            color: rgba(255,255,255,0.8);
        }
        
        .btn-back {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            padding: 10px 25px;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: #667eea;
            color: white;
        }
        
        .btn-send {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            font-weight: 500;
        }
        
        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #6c757d;
        }
        
        .info-value {
            color: #333;
        }
        
        .message-actions {
            display: flex;
            gap: 5px;
            margin-top: 5px;
        }
        
        .btn-message-action {
            background: none;
            border: none;
            color: rgba(255,255,255,0.7);
            cursor: pointer;
            padding: 2px 8px;
            font-size: 0.75rem;
            transition: all 0.2s;
        }
        
        .btn-message-action:hover {
            color: white;
        }
        
        .message-received .btn-message-action {
            color: #6c757d;
        }
        
        .message-received .btn-message-action:hover {
            color: #333;
        }
        
        .btn-action {
            margin-bottom: 10px;
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
            color: #667eea;
        }
        .navbar-custom .nav-link {
            color: #495057;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: all 0.3s;
        }
        .navbar-custom .nav-link:hover {
            color: #667eea;
        }
        .navbar-custom .btn-logout {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
                        <a class="nav-link" href="support_dashboard.php">
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

    <div class="container">
        <div class="mb-4">
            <a href="support_dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Retour au tableau de bord
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-ticket-alt"></i> Demande #<?= $request->getId() ?>
                    </h4>
                    <span class="status-badge status-<?= htmlspecialchars($request->getStatut()) ?>">
                        <?php
                        $statusLabels = [
                            'en_attente' => 'En attente',
                            'assignee' => 'Assignée',
                            'en_cours' => 'En cours',
                            'terminee' => 'Terminée',
                            'annulee' => 'Annulée'
                        ];
                        echo $statusLabels[$request->getStatut()] ?? $request->getStatut();
                        ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <h5 class="mb-3"><?= htmlspecialchars($request->getTitre()) ?></h5>
                
                <div class="info-row">
                    <span class="info-label">Urgence:</span>
                    <span class="urgency-badge urgency-<?= htmlspecialchars($request->getUrgence()) ?>">
                        <?= ucfirst(htmlspecialchars($request->getUrgence())) ?>
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Date de création:</span>
                    <span class="info-value"><?= date('d/m/Y H:i', strtotime($request->getDateCreation())) ?></span>
                </div>
                
                <?php if ($counselor): ?>
                <div class="info-row">
                    <span class="info-label">Conseiller assigné:</span>
                    <span class="info-value">
                        <i class="fas fa-user-tie"></i> <?= htmlspecialchars($counselor->getNom()) ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if ($request->getDateAssignation()): ?>
                <div class="info-row">
                    <span class="info-label">Date d'assignation:</span>
                    <span class="info-value"><?= date('d/m/Y H:i', strtotime($request->getDateAssignation())) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($request->getDateResolution()): ?>
                <div class="info-row">
                    <span class="info-label">Date de résolution:</span>
                    <span class="info-value"><?= date('d/m/Y H:i', strtotime($request->getDateResolution())) ?></span>
                </div>
                <?php endif; ?>
                
                <div class="mt-3">
                    <strong class="info-label">Description:</strong>
                    <p class="mt-2"><?= nl2br(htmlspecialchars($request->getDescription())) ?></p>
                </div>
            </div>
        </div>

        <?php if ($request->getStatut() !== 'annulee'): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-comments"></i> Conversation</h5>
            </div>
            <div class="card-body">
                <div class="message-container" id="messageContainer">
                    <?php if (empty($messages)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Aucun message pour le moment. Commencez la conversation !</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                            <?php 
                            $isSent = ($message->getSenderId() == $_SESSION['user_id']);
                            $bubbleClass = $isSent ? 'message-sent' : 'message-received';
                            $senderUser = $userController->getUserById($message->getSenderId());
                            ?>
                            <div class="d-flex <?= $isSent ? 'justify-content-end' : '' ?> mb-3">
                                <div class="message-bubble <?= $bubbleClass ?>">
                                    <?php if (!$isSent && $senderUser): ?>
                                    <div class="message-sender">
                                        <i class="fas fa-user-md me-1"></i>
                                        <?= htmlspecialchars($senderUser->getNom()) ?>
                                    </div>
                                    <?php endif; ?>
                                    <div class="message-content">
                                        <?= nl2br(htmlspecialchars($message->getMessage())) ?>
                                    </div>
                                    <div class="message-time">
                                        <?= date('d/m/Y H:i', strtotime($message->getDateEnvoi())) ?>
                                    </div>
                                    <?php if ($isSent && $request->getStatut() !== 'terminee' && $request->getStatut() !== 'annulee'): ?>
                                    <div class="message-actions">
                                        <button type="button" class="btn-message-action" 
                                                onclick="editMessage(<?= $message->getId() ?>, '<?= htmlspecialchars(str_replace(["\r\n", "\n", "\r"], ' ', $message->getMessage()), ENT_QUOTES) ?>')">
                                            <i class="fas fa-edit"></i> Modifier
                                        </button>
                                        <button type="button" class="btn-message-action" 
                                                onclick="deleteMessage(<?= $message->getId() ?>)">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if ($request->getStatut() !== 'terminee' && $request->getStatut() !== 'annulee' && $request->getCounselorId()): ?>
                <hr>
                <form method="POST" action="" class="mt-3">
                    <div class="mb-3">
                        <label for="message" class="form-label">
                            <i class="fas fa-pen me-2"></i>Votre message
                        </label>
                        <textarea class="form-control" 
                                  id="message" 
                                  name="message" 
                                  rows="4" 
                                  placeholder="Écrivez votre message ici..."
                                  required
                                  minlength="10"></textarea>
                        <small class="text-muted">Minimum 10 caractères</small>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-send">
                            <i class="fas fa-paper-plane me-2"></i>
                            Envoyer le message
                        </button>
                    </div>
                </form>
                <?php elseif ($request->getStatut() === 'en_attente'): ?>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i> Votre demande est en attente d'assignation à un conseiller.
                </div>
                <?php else: ?>
                <div class="alert alert-info mb-0 mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Cette conversation est terminée. Vous ne pouvez plus envoyer de messages.
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Action Buttons -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cog"></i> Actions</h5>
            </div>
            <div class="card-body">
                <?php if ($request->getStatut() === 'en_attente'): ?>
                <button class="btn btn-warning btn-action w-100" 
                        data-bs-toggle="modal" 
                        data-bs-target="#cancelModal">
                    <i class="fas fa-times me-2"></i>
                    Annuler la demande
                </button>
                <p class="small text-muted mb-2">
                    Vous pouvez annuler cette demande tant qu'elle n'est pas assignée.
                </p>
                <?php endif; ?>
                
                <button class="btn btn-danger btn-action w-100" 
                        data-bs-toggle="modal" 
                        data-bs-target="#deleteModal">
                    <i class="fas fa-trash me-2"></i>
                    Supprimer la demande
                </button>
                <p class="small text-muted mb-0">
                    <?php if ($request->getStatut() === 'en_attente'): ?>
                    Supprimer définitivement cette demande. Tous les messages associés seront également supprimés.
                    <?php elseif ($request->getStatut() === 'annulee'): ?>
                    Supprimer définitivement cette demande annulée.
                    <?php else: ?>
                    <strong>Attention :</strong> Supprimer cette demande supprimera également toute la conversation avec le conseiller. Cette action est irréversible.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Modal: Cancel Request -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Confirmer l'annulation
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir annuler cette demande ?</p>
                    <p class="text-muted small mb-0">
                        Cette action est irréversible. Vous pourrez toujours créer une nouvelle demande par la suite.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Non, conserver
                    </button>
                    <form action="../../controller/support/cancel_request.php" method="POST" class="d-inline">
                        <input type="hidden" name="request_id" value="<?= $requestId ?>">
                        <button type="submit" class="btn btn-warning">
                            Oui, annuler
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal: Delete Request -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer définitivement cette demande ?</p>
                    <p class="text-muted small mb-0">
                        <strong>Cette action est irréversible.</strong><br>
                        Tous les messages associés seront également supprimés.<br>
                        <?php if ($request->getCounselorId()): ?>
                        <span class="text-danger">La conversation avec le conseiller sera également supprimée de son côté.</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Non, conserver
                    </button>
                    <form action="../../controller/support/user_delete_request.php" method="POST" class="d-inline">
                        <input type="hidden" name="request_id" value="<?= $requestId ?>">
                        <button type="submit" class="btn btn-danger">
                            Oui, supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal: Edit Message -->
    <div class="modal fade" id="editMessageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit text-primary me-2"></i>
                        Modifier le message
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editMessageForm" method="POST" action="../../controller/support/update_message.php">
                    <div class="modal-body">
                        <input type="hidden" name="message_id" id="edit_message_id">
                        <div class="mb-3">
                            <label for="edit_message" class="form-label">Message</label>
                            <textarea class="form-control" 
                                      id="edit_message" 
                                      name="message" 
                                      rows="4" 
                                      required
                                      minlength="10"></textarea>
                            <small class="text-muted">Minimum 10 caractères</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Annuler
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal: Delete Message -->
    <div class="modal fade" id="deleteMessageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer ce message ?</p>
                    <p class="text-muted small mb-0">
                        Cette action est irréversible.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <form id="deleteMessageForm" method="POST" action="../../controller/support/delete_message.php" class="d-inline">
                        <input type="hidden" name="message_id" id="delete_message_id">
                        <button type="submit" class="btn btn-danger">
                            Oui, supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('load', function() {
            const messageContainer = document.getElementById('messageContainer');
            if (messageContainer) {
                messageContainer.scrollTop = messageContainer.scrollHeight;
            }
        });
        
        const messageForm = document.querySelector('form');
        if (messageForm) {
            messageForm.addEventListener('submit', function(event) {
                const messageField = document.getElementById('message');
                if (messageField && messageField.value.trim().length < 10) {
                    event.preventDefault();
                    alert('Le message doit contenir au moins 10 caractères.');
                    messageField.focus();
                }
            });
        }
        
        // Function to edit a message
        function editMessage(messageId, currentMessage) {
            document.getElementById('edit_message_id').value = messageId;
            document.getElementById('edit_message').value = currentMessage;
            const editModal = new bootstrap.Modal(document.getElementById('editMessageModal'));
            editModal.show();
        }
        
        // Function to delete a message
        function deleteMessage(messageId) {
            document.getElementById('delete_message_id').value = messageId;
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteMessageModal'));
            deleteModal.show();
        }
        
        // Validation for edit message form
        const editMessageForm = document.getElementById('editMessageForm');
        if (editMessageForm) {
            editMessageForm.addEventListener('submit', function(event) {
                const messageField = document.getElementById('edit_message');
                if (messageField.value.trim().length < 10) {
                    event.preventDefault();
                    alert('Le message doit contenir au moins 10 caractères.');
                    messageField.focus();
                }
            });
        }
    </script>
</body>
</html>
