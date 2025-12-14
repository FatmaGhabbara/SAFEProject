<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/SupportController.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/usercontroller.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/model/SupportRequest.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/model/SupportMessage.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /SAFEProject/view/frontoffice/login.php');
    exit();
}

$request_id = intval($_GET['id'] ?? 0);
if (!$request_id) {
    header('Location: /SAFEProject/view/frontoffice/support_dashboard.php');
    exit();
}

$supportController = new SupportController();
$userController = new UserController();
$request = new SupportRequest($request_id);

if (!$request->getId()) {
    $_SESSION['error_message'] = 'Demande introuvable.';
    header('Location: /SAFEProject/view/frontoffice/support_dashboard.php');
    exit();
}

// Check access rights
$currentUserId = $_SESSION['user_id'];
$currentRole = $_SESSION['user_role'];
$hasAccess = false;

if ($currentRole === 'admin') {
    $hasAccess = true;
} elseif ($currentRole === 'conseilleur' && $request->getCounselorId() == $currentUserId) {
    $hasAccess = true;
} elseif ($currentRole === 'membre' && $request->getUserId() == $currentUserId) {
    $hasAccess = true;
}

if (!$hasAccess) {
    $_SESSION['error_message'] = 'Accès non autorisé.';
    header('Location: /SAFEProject/view/frontoffice/support_dashboard.php');
    exit();
}

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $message_text = trim($_POST['message'] ?? '');
    
    if (!empty($message_text) && strlen($message_text) >= 10) {
        $message = new SupportMessage();
        $message->setSupportRequestId($request_id);
        $message->setSenderId($currentUserId);
        $message->setMessage($message_text);
        
        if ($message->save()) {
            // Update request status to en_cours if it was assignee
            if ($request->getStatut() === 'assignee') {
                $request->setStatut('en_cours');
                $request->save();
            }
            $_SESSION['success_message'] = 'Message envoyé avec succès.';
        } else {
            $_SESSION['error_message'] = 'Erreur lors de l\'envoi du message.';
        }
        header('Location: support_conversation.php?id=' . $request_id);
        exit();
    } else {
        $_SESSION['error_message'] = 'Le message doit contenir au moins 10 caractères.';
    }
}

// Get messages and mark as read
$messages = $supportController->findMessagesByRequest($request_id);
$supportController->markMessagesAsRead($request_id, $currentUserId);

// Get user info
$requester = $userController->getUserById($request->getUserId());
$counselor = $request->getCounselorId() ? $userController->getUserById($request->getCounselorId()) : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Conversation - <?= htmlspecialchars($request->getTitre()) ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: #f5f7fa;
        }
        .conversation-container {
            max-width: 1200px;
            margin: 2rem auto;
        }
        .request-header {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .messages-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            max-height: 600px;
            overflow-y: auto;
        }
        .message {
            margin-bottom: 1.5rem;
            display: flex;
            gap: 1rem;
        }
        .message.sent {
            flex-direction: row-reverse;
        }
        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            flex-shrink: 0;
        }
        .message-content {
            flex: 1;
            max-width: 70%;
        }
        .message.sent .message-content {
            text-align: right;
        }
        .message-bubble {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 15px;
            display: inline-block;
            text-align: left;
        }
        .message.sent .message-bubble {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .message-meta {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        .message.sent .message-meta {
            color: #495057;
        }
        .message-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }
        .message-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        .message-form {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<div class="conversation-container">
    <div class="container">
        
        <!-- Back Button -->
        <div class="mb-3">
            <?php if ($currentRole === 'admin'): ?>
                <a href="admin_support_requests.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                </a>
            <?php elseif ($currentRole === 'conseilleur'): ?>
                <a href="conseiller_support_dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour à mes demandes
                </a>
            <?php else: ?>
                <a href="/SAFEProject/view/frontoffice/support_dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour à mes demandes
                </a>
            <?php endif; ?>
        </div>

        <!-- Request Header -->
        <div class="request-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-2"><?= htmlspecialchars($request->getTitre()) ?></h3>
                    <p class="text-muted mb-2"><?= htmlspecialchars($request->getDescription()) ?></p>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-<?= $request->getUrgence() === 'haute' ? 'danger' : ($request->getUrgence() === 'moyenne' ? 'warning' : 'secondary') ?>">
                            <?= htmlspecialchars($request->getUrgence()) ?>
                        </span>
                        <span class="badge bg-<?= $request->getStatut() === 'terminee' ? 'success' : 'primary' ?>">
                            <?= htmlspecialchars($request->getStatut()) ?>
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="small text-muted">
                        <div><strong>Demandeur:</strong> <?= htmlspecialchars($requester->getNom()) ?></div>
                        <?php if ($counselor): ?>
                            <div><strong>Conseiller:</strong> <?= htmlspecialchars($counselor->getNom()) ?></div>
                        <?php endif; ?>
                        <div><strong>Créée le:</strong> <?= date('d/m/Y H:i', strtotime($request->getDateCreation())) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Notes for Counselor -->
        <?php if ($currentRole === 'conseilleur' && !empty($request->getNotesAdmin())): ?>
        <div class="alert alert-warning mt-3" role="alert">
            <h5 class="alert-heading">
                <i class="fas fa-sticky-note"></i> Note de l'administrateur
            </h5>
            <hr>
            <p class="mb-0"><?= nl2br(htmlspecialchars($request->getNotesAdmin())) ?></p>
            <?php if ($request->getDateAssignation()): ?>
            <small class="text-muted d-block mt-2">
                <i class="fas fa-clock"></i> Assignée le <?= date('d/m/Y à H:i', strtotime($request->getDateAssignation())) ?>
            </small>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Messages -->
        <div class="messages-container" id="messagesContainer">
            <?php if (empty($messages)): ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-comments fa-3x mb-3"></i>
                    <p>Aucun message pour le moment. Commencez la conversation!</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <?php
                    $sender = $userController->getUserById($msg->getSenderId());
                    $isSent = ($msg->getSenderId() == $currentUserId);
                    $initials = strtoupper(substr($sender->getNom(), 0, 1));
                    ?>
                    <div class="message <?= $isSent ? 'sent' : '' ?>" id="message-<?= $msg->getId() ?>">
                        <div class="message-avatar"><?= $initials ?></div>
                        <div class="message-content">
                            <div class="message-bubble">
                                <div class="message-text"><?= nl2br(htmlspecialchars($msg->getMessage())) ?></div>
                                <?php if ($isSent && $currentRole === 'conseilleur'): ?>
                                <div class="message-actions mt-2">
                                    <button class="btn btn-sm btn-light" onclick="editMessage(<?= $msg->getId() ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteMessage(<?= $msg->getId() ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="message-meta">
                                <?= htmlspecialchars($sender->getNom()) ?> • 
                                <?= date('d/m/Y H:i', strtotime($msg->getDateEnvoi())) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Message Form -->
        <?php if ($request->getStatut() !== 'terminee' && $request->getStatut() !== 'annulee'): ?>
        <div class="message-form">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="message" class="form-label fw-bold">
                        <i class="fas fa-comment me-2"></i>Votre message
                    </label>
                    <textarea class="form-control" 
                              id="message" 
                              name="message" 
                              rows="4" 
                              placeholder="Écrivez votre message ici (minimum 10 caractères)..."
                              required></textarea>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="fas fa-lock me-1"></i>Conversation confidentielle et sécurisée
                    </small>
                    <button type="submit" name="send_message" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Envoyer
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Cette demande est <?= $request->getStatut() === 'terminee' ? 'terminée' : 'annulée' ?>. 
            Vous ne pouvez plus envoyer de messages.
        </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-scroll to bottom of messages
const messagesContainer = document.getElementById('messagesContainer');
if (messagesContainer) {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Edit message function
function editMessage(messageId) {
    const messageDiv = document.getElementById('message-' + messageId);
    const messageText = messageDiv.querySelector('.message-text');
    const currentText = messageText.innerText;
    
    const newText = prompt('Modifier le message:', currentText);
    
    if (newText && newText.trim() !== '' && newText !== currentText) {
        const formData = new FormData();
        formData.append('message_id', messageId);
        formData.append('message', newText);
        
        fetch('../../controller/support/conseiller_update_message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageText.innerHTML = data.new_content.replace(/\n/g, '<br>');
                alert('Message modifié avec succès');
            } else {
                alert('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Une erreur est survenue');
        });
    }
}

// Delete message function
function deleteMessage(messageId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce message?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('message_id', messageId);
    
    fetch('../../controller/support/conseiller_delete_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const messageDiv = document.getElementById('message-' + messageId);
            messageDiv.style.opacity = '0';
            setTimeout(() => {
                messageDiv.remove();
            }, 300);
            alert('Message supprimé avec succès');
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue');
    });
}
</script>

</body>
</html>
