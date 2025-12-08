<?php
session_start();
require_once '../../../config.php';
require_once '../../../model/SupportRequest.php';
require_once '../../../model/SupportMessage.php';
require_once '../../../model/User.php';
require_once '../../../controller/helpers.php';

// Check if logged in
if (!isLoggedIn()) {
    header('Location: ../../frontoffice/login.php');
    exit();
}

// Get request ID
$requestId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($requestId === 0) {
    setFlashMessage('Demande invalide.', 'error');
    header('Location: support_requests.php');
    exit();
}

// Get the request
try {
    $request = new SupportRequest($requestId);
    $user = $request->getUser();
    $counselorId = $request->getCounselorId();
    $counselorUser = $counselorId ? getCounselorById($counselorId) : null;
    $messages = findMessagesByRequest($requestId);
} catch (Exception $e) {
    setFlashMessage('Demande introuvable.', 'error');
    header('Location: support_requests.php');
    exit();
}

// Check access: user can only see their own requests, counselor can see assigned requests, admin can see all
$currentUserId = $_SESSION['user_id'];
$currentRole = $_SESSION['role'];

if ($currentRole === 'user' && $request->getUserId() != $currentUserId) {
    setFlashMessage('Accès refusé.', 'error');
    header('Location: ../../frontoffice/support/my_requests.php');
    exit();
}

if ($currentRole === 'counselor' && $counselorUser && $counselorUser->getId() != $currentUserId) {
    setFlashMessage('Cette demande ne vous est pas assignée.', 'error');
    header('Location: my_assigned_requests.php');
    exit();
}

// Get flash message
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversation - SAFEProject</title>
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
            padding: 1.5rem 0;
        }
        .conversation-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .info-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .messages-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-height: 500px;
            overflow-y: auto;
        }
        .message {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
        }
        .message.own-message {
            flex-direction: row-reverse;
        }
        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            flex-shrink: 0;
        }
        .message-avatar.user { background: #667eea; }
        .message-avatar.counselor { background: #28a745; }
        .message-content {
            max-width: 70%;
            margin: 0 15px;
        }
        .message.own-message .message-content {
            text-align: right;
        }
        .message-bubble {
            background: #f1f3f5;
            padding: 12px 18px;
            border-radius: 18px;
            display: inline-block;
            text-align: left;
        }
        .message.own-message .message-bubble {
            background: #667eea;
            color: white;
        }
        .message-meta {
            font-size: 0.75rem;
            color: #868e96;
            margin-top: 5px;
        }
        .send-message-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .badge-custom {
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
        .message-actions {
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
        }
        .message.own-message .message-actions .btn {
            color: white;
            border-color: rgba(255, 255, 255, 0.5);
        }
        .message.own-message .message-actions .btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: white;
            color: white;
        }
        .message:not(.own-message) .message-actions {
            border-top-color: rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<?php include '../../includes/navbar.php'; ?>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fas fa-comments"></i> Conversation</h1>
            <a href="javascript:history.back()" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>
</div>

<div class="conversation-container p-4">
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $flash['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Request Info -->
    <div class="info-card">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-2" style="color: #333;"><?php echo secureOutput($request->getTitre()); ?></h4>
                <p class="mb-2"><strong>Patient:</strong> <?php echo secureOutput($user->getFullName()); ?></p>
                <?php if ($counselorUser): ?>
                    <p class="mb-2"><strong>Conseiller:</strong> <?php echo secureOutput($counselorUser->getFullName()); ?></p>
                <?php endif; ?>
                <p class="mb-0 text-muted"><small>Créée le: <?php echo date('d/m/Y à H:i', strtotime($request->getDateCreation())); ?></small></p>
            </div>
            <div class="col-md-4 text-end">
                <span class="badge badge-custom bg-<?php echo $request->getUrgence() === 'haute' ? 'danger' : ($request->getUrgence() === 'moyenne' ? 'warning' : 'info'); ?>">
                    <i class="fas fa-exclamation-circle"></i> <?php echo ucfirst($request->getUrgence()); ?>
                </span>
                <br><br>
                <span class="badge badge-custom bg-primary">
                    <?php echo ucfirst(str_replace('_', ' ', $request->getStatut())); ?>
                </span>
            </div>
        </div>
        
        <?php if ($currentRole === 'counselor' && $counselorUser && $counselorUser->getId() == $currentUserId): ?>
        <hr>
        <div class="btn-group" role="group">
            <?php if ($request->getStatut() === 'assignee'): ?>
                <a href="../../../controller/support/counselor_start_request.php?id=<?php echo $requestId; ?>" class="btn btn-success btn-sm">
                    <i class="fas fa-play"></i> Commencer la conversation
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Messages -->
    <div class="messages-container" id="messagesContainer">
        <?php if (empty($messages)): ?>
            <div class="text-center text-muted py-5">
                <i class="fas fa-comments fa-3x mb-3" style="opacity: 0.3;"></i>
                <p>Aucun message pour le moment. Commencez la conversation ci-dessous.</p>
            </div>
        <?php else: ?>
            <?php foreach ($messages as $msgObj): 
                $msgUser = $msgObj->getUser();
                if (!$msgUser || !$msgUser->getId()) continue; // Skip if user not found
                
                $isOwnMessage = ($msgUser->getId() == $currentUserId);
                $isCounselor = ($msgUser->getRole() === 'counselor' || $msgUser->getRole() === 'admin');
                $canEditDelete = $isOwnMessage && $request->getStatut() !== 'terminee' && $request->getStatut() !== 'annulee';
            ?>
            <div class="message <?php echo $isOwnMessage ? 'own-message' : ''; ?>">
                <div class="message-avatar <?php echo $isCounselor ? 'counselor' : 'user'; ?>">
                    <i class="fas fa-<?php echo $isCounselor ? 'user-md' : 'user'; ?>"></i>
                </div>
                <div class="message-content">
                    <div class="message-bubble" style="position: relative;">
                        <strong><?php echo secureOutput($msgUser->getFullName()); ?></strong>
                        <br>
                        <div id="message-content-<?php echo $msgObj->getId(); ?>">
                            <?php echo nl2br(secureOutput($msgObj->getMessage())); ?>
                        </div>
                        <?php if ($canEditDelete): ?>
                        <div class="message-actions" style="text-align: <?php echo $isOwnMessage ? 'right' : 'left'; ?>;">
                            <button type="button" 
                                    class="btn btn-sm <?php echo $isOwnMessage ? 'btn-outline-light' : 'btn-outline-primary'; ?> me-1" 
                                    onclick="editMessage(<?php echo $msgObj->getId(); ?>, '<?php echo addslashes($msgObj->getMessage()); ?>')"
                                    title="Modifier le message">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" 
                                    class="btn btn-sm <?php echo $isOwnMessage ? 'btn-outline-light' : 'btn-outline-danger'; ?>" 
                                    onclick="deleteMessage(<?php echo $msgObj->getId(); ?>)"
                                    title="Supprimer le message">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="message-meta">
                        <i class="fas fa-clock"></i> <?php echo date('d/m/Y à H:i', strtotime($msgObj->getDateEnvoi())); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Send Message Form -->
    <div class="send-message-box">
        <form action="../../../controller/support/send_message.php" method="POST" id="messageForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="request_id" value="<?php echo $requestId; ?>">
            
            <div class="mb-3">
                <label for="message" class="form-label">
                    <i class="fas fa-comment"></i> Votre message
                </label>
                <textarea class="form-control" 
                          id="message" 
                          name="message" 
                          rows="4" 
                          placeholder="Écrivez votre message ici..."
                          required></textarea>
            </div>
            
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="fas fa-lock"></i> Conversation confidentielle et sécurisée
                </small>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-paper-plane"></i> Envoyer
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-scroll to bottom of messages
const messagesContainer = document.getElementById('messagesContainer');
if (messagesContainer) {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Clear form after submit
document.getElementById('messageForm').addEventListener('submit', function() {
    setTimeout(function() {
        document.getElementById('message').value = '';
    }, 100);
});

// Auto-refresh messages every 10 seconds
setInterval(function() {
    location.reload();
}, 30000); // 30 seconds

// Fonction pour modifier un message
function editMessage(messageId, currentMessage) {
    document.getElementById('edit_message_id').value = messageId;
    document.getElementById('edit_message').value = currentMessage;
    const editModal = new bootstrap.Modal(document.getElementById('editMessageModal'));
    editModal.show();
}

// Fonction pour supprimer un message
function deleteMessage(messageId) {
    document.getElementById('delete_message_id').value = messageId;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteMessageModal'));
    deleteModal.show();
}

// Validation du formulaire de modification de message
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

<!-- Modal de modification de message -->
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
            <form id="editMessageForm" method="POST" action="../../../controller/support/update_message.php">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
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

<!-- Modal de confirmation de suppression de message -->
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
                <form id="deleteMessageForm" method="POST" action="../../../controller/support/delete_message.php" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="message_id" id="delete_message_id">
                    <button type="submit" class="btn btn-danger">
                        Oui, supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>

