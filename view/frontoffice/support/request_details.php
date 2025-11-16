<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la demande - SAFEProject</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/support-module.css">
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body class="bg-light">

<?php
session_start();

// MODE TEST : Simuler un utilisateur connecté (Jean Dupont - user)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 2;  // Jean Dupont
    $_SESSION['user_role'] = 'user';
    $_SESSION['user_name'] = 'Jean Dupont';
}

require_once '../../../model/config.php';
require_once '../../../model/support_functions.php';

// Récupérer l'ID de la demande
$requestId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($requestId === 0) {
    header('Location: my_requests.php');
    exit();
}

// Récupérer la demande
$request = getSupportRequestById($requestId);

// Vérifier que la demande existe et appartient à l'utilisateur
if (!$request || $request['user_id'] != $_SESSION['user_id']) {
    setFlashMessage('Demande introuvable ou accès non autorisé.', 'error');
    header('Location: my_requests.php');
    exit();
}

// Récupérer les messages
$messages = getMessagesByRequest($requestId);

// Récupérer les messages flash
$flash = getFlashMessage();
?>

    <!-- En-tête -->
    <header class="bg-white shadow-sm py-3 mb-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-heart text-danger"></i>
                    SAFEProject
                </h2>
                <nav>
                    <a href="my_requests.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Mes demandes
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Contenu principal -->
    <div class="container my-5">
        
        <!-- Message flash -->
        <?php if ($flash): ?>
        <div class="alert alert-flash alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo secureOutput($flash['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <div class="row">
            
            <!-- Colonne principale : Détails et Messages -->
            <div class="col-lg-8 mb-4">
                
                <!-- Détails de la demande -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h3 class="h4 mb-0">
                                <?php echo secureOutput($request['titre']); ?>
                            </h3>
                            <span class="badge-support badge-<?php echo str_replace('_', '-', $request['statut']); ?>">
                                <?php echo str_replace('_', ' ', ucfirst($request['statut'])); ?>
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <span class="badge-urgence badge-urgence-<?php echo $request['urgence']; ?> me-2">
                                <span class="urgence-icon <?php echo $request['urgence']; ?>"></span>
                                Urgence : <?php echo ucfirst($request['urgence']); ?>
                            </span>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo formatDate($request['date_creation'], 'd/m/Y à H:i'); ?>
                            </small>
                        </div>
                        
                        <hr>
                        
                        <h5 class="mb-3">Description</h5>
                        <p class="text-muted" style="white-space: pre-wrap;">
                            <?php echo secureOutput($request['description']); ?>
                        </p>
                        
                        <?php if ($request['statut'] === 'en_attente'): ?>
                        <hr>
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-hourglass-half me-2"></i>
                            <strong>En attente</strong> - Un conseiller sera bientôt assigné à votre demande.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Section Messages -->
                <?php if ($request['counselor_id']): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-comments text-primary me-2"></i>
                            Conversation avec votre conseiller
                        </h5>
                    </div>
                    <div class="card-body">
                        
                        <!-- Liste des messages -->
                        <div class="message-container" id="messageContainer">
                            <?php if (empty($messages)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Aucun message pour le moment. Commencez la conversation !</p>
                            </div>
                            <?php else: ?>
                                <?php foreach ($messages as $message): ?>
                                    <?php 
                                    $isSent = ($message['sender_id'] == $_SESSION['user_id']);
                                    $bubbleClass = $isSent ? 'message-sent' : 'message-received';
                                    ?>
                                    <div class="d-flex <?php echo $isSent ? 'justify-content-end' : ''; ?> mb-3">
                                        <div class="message-bubble <?php echo $bubbleClass; ?>">
                                            <?php if (!$isSent): ?>
                                            <div class="message-sender">
                                                <i class="fas fa-user-md me-1"></i>
                                                <?php echo secureOutput($message['nom'] . ' ' . $message['prenom']); ?>
                                            </div>
                                            <?php endif; ?>
                                            <div class="message-content">
                                                <?php echo nl2br(secureOutput($message['message'])); ?>
                                            </div>
                                            <div class="message-time">
                                                <?php echo formatDate($message['date_envoi'], 'd/m/Y H:i'); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Formulaire d'envoi de message -->
                        <?php if ($request['statut'] !== 'terminee' && $request['statut'] !== 'annulee'): ?>
                        <hr>
                        <form id="messageForm" action="../../../controller/support/send_message.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="request_id" value="<?php echo $requestId; ?>">
                            
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
                                <button type="submit" class="btn btn-support-primary">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Envoyer le message
                                </button>
                            </div>
                        </form>
                        <?php else: ?>
                        <div class="alert alert-info mb-0 mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Cette conversation est terminée. Vous ne pouvez plus envoyer de messages.
                        </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
            
            <!-- Colonne latérale : Conseiller et Actions -->
            <div class="col-lg-4">
                
                <!-- Profil du conseiller -->
                <?php if ($request['counselor_id']): ?>
                <div class="counselor-profile mb-4">
                    <div class="counselor-avatar">
                        <?php echo strtoupper(substr($request['counselor_nom'], 0, 1) . substr($request['counselor_prenom'], 0, 1)); ?>
                    </div>
                    <h4 class="counselor-name">
                        <?php echo secureOutput($request['counselor_nom'] . ' ' . $request['counselor_prenom']); ?>
                    </h4>
                    <p class="counselor-specialite">
                        <i class="fas fa-graduation-cap me-2"></i>
                        <?php echo secureOutput($request['counselor_specialite']); ?>
                    </p>
                    <hr>
                    <div class="text-center">
                        <p class="small text-muted mb-2">
                            <i class="fas fa-user-check me-2"></i>
                            Votre conseiller
                        </p>
                        <?php if ($request['date_assignation']): ?>
                        <p class="small text-muted">
                            Assigné le <?php echo formatDate($request['date_assignation'], 'd/m/Y'); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center">
                        <i class="fas fa-hourglass-half fa-3x text-muted mb-3"></i>
                        <h5>En attente d'assignation</h5>
                        <p class="text-muted small">
                            Un conseiller sera bientôt assigné à votre demande.
                        </p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Informations sur la demande -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Informations
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Demande n°</small>
                            <strong><?php echo $request['id']; ?></strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Date de création</small>
                            <strong><?php echo formatDate($request['date_creation'], 'd/m/Y H:i'); ?></strong>
                        </div>
                        <?php if ($request['date_assignation']): ?>
                        <div class="mb-3">
                            <small class="text-muted d-block">Date d'assignation</small>
                            <strong><?php echo formatDate($request['date_assignation'], 'd/m/Y H:i'); ?></strong>
                        </div>
                        <?php endif; ?>
                        <?php if ($request['date_resolution']): ?>
                        <div class="mb-0">
                            <small class="text-muted d-block">Date de résolution</small>
                            <strong><?php echo formatDate($request['date_resolution'], 'd/m/Y H:i'); ?></strong>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Actions -->
                <?php if ($request['statut'] === 'en_attente'): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            <i class="fas fa-cog text-primary me-2"></i>
                            Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-danger w-100" 
                                data-bs-toggle="modal" 
                                data-bs-target="#cancelModal">
                            <i class="fas fa-times me-2"></i>
                            Annuler la demande
                        </button>
                        <p class="small text-muted mt-2 mb-0">
                            Vous pouvez annuler cette demande tant qu'elle n'est pas assignée.
                        </p>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
            
        </div>

    </div>

    <!-- Modal de confirmation d'annulation -->
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
                    <form action="../../../controller/support/cancel_request.php" method="POST" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="request_id" value="<?php echo $requestId; ?>">
                        <button type="submit" class="btn btn-danger">
                            Oui, annuler
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">© 2025 SAFEProject - Tous droits réservés</p>
            <p class="mb-0 small text-muted">Module Support Psychologique</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script pour auto-scroll vers le dernier message -->
    <script>
        window.addEventListener('load', function() {
            const messageContainer = document.getElementById('messageContainer');
            if (messageContainer) {
                messageContainer.scrollTop = messageContainer.scrollHeight;
            }
        });
        
        // Validation du formulaire de message
        const messageForm = document.getElementById('messageForm');
        if (messageForm) {
            messageForm.addEventListener('submit', function(event) {
                const messageField = document.getElementById('message');
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

