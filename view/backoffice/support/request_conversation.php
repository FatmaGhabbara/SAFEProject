<?php
session_start();
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../controller/SupportController.php';
require_once __DIR__ . '/../../../controller/usercontroller.php';
require_once __DIR__ . '/../../../model/SupportRequest.php';
require_once __DIR__ . '/../../../model/SupportMessage.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../frontoffice/login.php');
    exit();
}

$supportController = new SupportController();
$userController = new UserController();
$currentUserId = $_SESSION['user_id'];
$currentRole = $_SESSION['user_role'];

// Get request ID
$requestId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($requestId === 0) {
    $_SESSION['error_message'] = 'Demande invalide.';
    header('Location: support_requests.php');
    exit();
}

// Get the request
$request = new SupportRequest($requestId);

if (!$request->getId()) {
    $_SESSION['error_message'] = 'Demande introuvable.';
    header('Location: support_requests.php');
    exit();
}

// Check access permissions
$user = $userController->getUserById($request->getUserId());
$counselor = $request->getCounselorUserId() ? $userController->getUserById($request->getCounselorUserId()) : null;

// Access control
if ($currentRole === 'membre' && $request->getUserId() != $currentUserId) {
    $_SESSION['error_message'] = 'Accès refusé.';
    header('Location: ../member_dashboard.php');
    exit();
}

if ($currentRole === 'conseilleur' && (!$counselor || $counselor->getId() != $currentUserId)) {
    $_SESSION['error_message'] = 'Cette demande ne vous est pas assignée.';
    header('Location: support_requests.php');
    exit();
}

// Handle new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $messageText = trim($_POST['message']);
    
    if (!empty($messageText)) {
        $message = new SupportMessage();
        $message->setSupportRequestId($requestId);
        $message->setSenderId($currentUserId);
        $message->setMessage($messageText);
        $message->setLu(false);
        
        if ($message->save()) {
            // Update request status to en_cours if it was assignee
            if ($request->getStatut() === 'assignee') {
                $request->setStatut('en_cours');
                $request->save();
            }
            
            $_SESSION['success_message'] = 'Message envoyé.';
        } else {
            $_SESSION['error_message'] = 'Erreur lors de l\'envoi du message.';
        }
    }
    
    header('Location: request_conversation.php?id=' . $requestId);
    exit();
}

// Handle message update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_message'])) {
    $messageId = intval($_POST['message_id']);
    $newMessageText = trim($_POST['new_message']);
    
    if (!empty($newMessageText)) {
        if ($supportController->updateMessage($messageId, $newMessageText, $currentUserId, $currentRole)) {
            $_SESSION['success_message'] = 'Message modifié.';
        } else {
            $_SESSION['error_message'] = 'Impossible de modifier ce message.';
        }
    }
    
    header('Location: request_conversation.php?id=' . $requestId);
    exit();
}

// Handle message deletion
if (isset($_GET['delete_message'])) {
    $messageId = intval($_GET['delete_message']);
    if ($supportController->deleteMessage($messageId, $currentUserId, $currentRole)) {
        $_SESSION['success_message'] = 'Message supprimé.';
    } else {
        $_SESSION['error_message'] = 'Impossible de supprimer ce message.';
    }
    header('Location: request_conversation.php?id=' . $requestId);
    exit();
}

// Get messages
$messages = $supportController->findMessagesByRequest($requestId);

// Mark messages as read
$supportController->markMessagesAsRead($requestId, $currentUserId);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Conversation - <?= htmlspecialchars($request->getTitre()) ?></title>
    
    <link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        .messages-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
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
            background: #4e73df;
        }
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
            position: relative;
        }
        .message.own-message .message-bubble {
            background: #4e73df;
            color: white;
        }
        .message-meta {
            font-size: 0.75rem;
            color: #868e96;
            margin-top: 5px;
        }
        .message-actions {
            margin-top: 5px;
        }
        .message-actions a {
            font-size: 0.75rem;
            color: #dc3545;
            text-decoration: none;
        }
        .send-message-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
        }
        .badge-urgence-basse { background: #28a745; color: white; }
        .badge-urgence-moyenne { background: #ffc107; color: #000; }
        .badge-urgence-haute { background: #dc3545; color: white; }
    </style>
</head>
<body id="page-top">

<div id="wrapper">
    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../index.php">
            <div class="sidebar-brand-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="sidebar-brand-text mx-3">SafeSpace</div>
        </a>

        <hr class="sidebar-divider my-0">

        <li class="nav-item">
            <a class="nav-link" href="<?= $currentRole === 'admin' ? '../index.php' : '../member_dashboard.php' ?>">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <?php if ($currentRole === 'admin'): ?>
        <li class="nav-item active">
            <a class="nav-link" href="support_requests.php">
                <i class="fas fa-fw fa-headset"></i>
                <span>Demandes de Support</span>
            </a>
        </li>
        <?php endif; ?>

        <hr class="sidebar-divider">

        <li class="nav-item">
            <a class="nav-link" href="../../../controller/AuthController.php?action=logout">
                <i class="fas fa-fw fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </li>
    </ul>

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <a href="<?= $currentRole === 'admin' ? 'support_requests.php' : '../member_dashboard.php' ?>" class="btn btn-sm btn-secondary mr-3">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($request->getTitre()) ?></h1>
            </nav>

            <!-- Page Content -->
            <div class="container-fluid">
                
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= htmlspecialchars($_SESSION['success_message']) ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= htmlspecialchars($_SESSION['error_message']) ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <!-- Request Info Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Informations de la Demande</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Utilisateur:</strong> <?= htmlspecialchars($user->getNom()) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($user->getEmail()) ?></p>
                                <p><strong>Urgence:</strong> 
                                    <span class="badge badge-urgence-<?= $request->getUrgence() ?>">
                                        <?= ucfirst($request->getUrgence()) ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Statut:</strong> <?= ucfirst(str_replace('_', ' ', $request->getStatut())) ?></p>
                                <p><strong>Date de création:</strong> <?= date('d/m/Y H:i', strtotime($request->getDateCreation())) ?></p>
                                <?php if ($counselor): ?>
                                    <p><strong>Conseiller assigné:</strong> <?= htmlspecialchars($counselor->getNom()) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <hr>
                        <p><strong>Description:</strong></p>
                        <p><?= nl2br(htmlspecialchars($request->getDescription())) ?></p>
                    </div>
                </div>

                <!-- Messages -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Conversation (<?= count($messages) ?> messages)</h6>
                    </div>
                    <div class="card-body">
                        <div class="messages-container" id="messagesContainer">
                            <?php if (empty($messages)): ?>
                                <p class="text-center text-muted">Aucun message pour le moment.</p>
                            <?php else: ?>
                                <?php foreach ($messages as $msg): ?>
                                    <?php 
                                    $sender = $userController->getUserById($msg->getSenderId());
                                    $isOwnMessage = $msg->getSenderId() == $currentUserId;
                                    ?>
                                    <div class="message <?= $isOwnMessage ? 'own-message' : '' ?>">
                                        <div class="message-avatar">
                                            <?= strtoupper(substr($sender->getNom(), 0, 1)) ?>
                                        </div>
                                        <div class="message-content">
                                            <div class="message-bubble" id="message-<?= $msg->getId() ?>">
                                                <span class="message-text"><?= nl2br(htmlspecialchars($msg->getMessage())) ?></span>
                                            </div>
                                            <div class="message-meta">
                                                <strong><?= htmlspecialchars($sender->getNom()) ?></strong> • 
                                                <?= date('d/m/Y H:i', strtotime($msg->getDateEnvoi())) ?>
                                            </div>
                                            <?php if ($isOwnMessage): ?>
                                                <div class="message-actions">
                                                    <button class="btn-edit-message" onclick="editMessage(<?= $msg->getId() ?>, '<?= htmlspecialchars(addslashes($msg->getMessage())) ?>')">
                                                        <i class="fas fa-edit"></i> Modifier
                                                    </button>
                                                    <a href="?id=<?= $requestId ?>&delete_message=<?= $msg->getId() ?>" 
                                                       onclick="return confirm('Supprimer ce message?')">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </a>
                                                </div>
                                            <?php elseif ($currentRole === 'admin'): ?>
                                                <div class="message-actions">
                                                    <a href="?id=<?= $requestId ?>&delete_message=<?= $msg->getId() ?>" 
                                                       onclick="return confirm('Supprimer ce message?')">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Send Message Form -->
                <?php if (in_array($request->getStatut(), ['assignee', 'en_cours'])): ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Envoyer un Message</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <textarea name="message" class="form-control" rows="4" 
                                          placeholder="Écrivez votre message..." required></textarea>
                            </div>
                            <button type="submit" name="send_message" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Envoyer
                            </button>
                        </form>
                    </div>
                </div>
                <?php elseif ($request->getStatut() === 'terminee'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Cette demande est terminée.
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script src="../assets/vendor/jquery/jquery.min.js"></script>
<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../assets/js/sb-admin-2.min.js"></script>

<!-- Modal pour modifier un message -->
<div class="modal fade" id="editMessageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier le message</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="message_id" id="edit_message_id">
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="new_message" id="edit_message_text" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" name="update_message" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../assets/vendor/jquery/jquery.min.js"></script>
<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../assets/js/sb-admin-2.min.js"></script>

<script>
// Auto-scroll to bottom of messages
$(document).ready(function() {
    var container = $('#messagesContainer');
    if (container.length) {
        container.scrollTop(container[0].scrollHeight);
    }
});

// Function to edit message
function editMessage(messageId, messageText) {
    $('#edit_message_id').val(messageId);
    $('#edit_message_text').val(messageText);
    $('#editMessageModal').modal('show');
}
</script>

<style>
.btn-edit-message {
    background: none;
    border: none;
    color: #007bff;
    cursor: pointer;
    font-size: 0.75rem;
    padding: 0;
    margin-right: 10px;
}
.btn-edit-message:hover {
    color: #0056b3;
    text-decoration: underline;
}
</style>

</body>
</html>
