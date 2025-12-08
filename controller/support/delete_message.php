<?php
/**
 * ============================================
 * CONTRÔLEUR : Suppression d'un message
 * SAFEProject - Module Support Psychologique (OOP)
 * ============================================
 */

session_start();

require_once '../../config.php';
require_once '../../model/SupportMessage.php';
require_once '../../model/SupportRequest.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    setFlashMessage('Vous devez être connecté pour supprimer un message.', 'error');
    redirect('../../view/frontoffice/login.html');
}

// Récupérer le rôle pour les redirections
$currentRole = $_SESSION['role'] ?? 'user';

// Vérifier la méthode de requête
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (in_array($currentRole, ['counselor', 'admin'])) {
        redirect('../../view/backoffice/support/my_assigned_requests.php');
    } else {
        redirect('../../view/frontoffice/support/my_requests.php');
    }
}

// Vérifier le token CSRF
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    setFlashMessage('Erreur de sécurité. Veuillez réessayer.', 'error');
    if (in_array($currentRole, ['counselor', 'admin'])) {
        redirect('../../view/backoffice/support/my_assigned_requests.php');
    } else {
        redirect('../../view/frontoffice/support/my_requests.php');
    }
}

// Récupérer l'ID du message
$messageId = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;

if ($messageId === 0) {
    setFlashMessage('Message invalide.', 'error');
    if (in_array($currentRole, ['counselor', 'admin'])) {
        redirect('../../view/backoffice/support/my_assigned_requests.php');
    } else {
        redirect('../../view/frontoffice/support/my_requests.php');
    }
}

// Récupérer le message (OOP)
$messageObj = new SupportMessage($messageId);

// Sauvegarder l'ID de la demande avant suppression
$requestId = $messageObj->getSupportRequestId();

// Vérifier que le message existe
if (!$messageObj->getId()) {
    setFlashMessage('Message introuvable.', 'error');
    if (in_array($currentRole, ['counselor', 'admin'])) {
        redirect('../../view/backoffice/support/my_assigned_requests.php');
    } else {
        redirect('../../view/frontoffice/support/my_requests.php');
    }
}

// Vérifier que le message appartient à l'utilisateur ou au conseiller
$currentRole = $_SESSION['role'] ?? 'user';
if ($messageObj->getSenderId() != $_SESSION['user_id']) {
    setFlashMessage('Vous ne pouvez supprimer que vos propres messages.', 'error');
    if (in_array($currentRole, ['counselor', 'admin'])) {
        redirect('../../view/backoffice/support/request_conversation.php?id=' . $requestId);
    } else {
        redirect('../../view/frontoffice/support/request_details.php?id=' . $requestId);
    }
}

// Vérifier que la demande associée n'est pas terminée ou annulée
$request = new SupportRequest($requestId);
if ($request->getId() && in_array($request->getStatut(), ['terminee', 'annulee'])) {
    setFlashMessage('Vous ne pouvez pas supprimer un message d\'une demande terminée ou annulée.', 'error');
    if (in_array($currentRole, ['counselor', 'admin'])) {
        redirect('../../view/backoffice/support/request_conversation.php?id=' . $requestId);
    } else {
        redirect('../../view/frontoffice/support/request_details.php?id=' . $requestId);
    }
}

// Supprimer le message (OOP)
$result = $messageObj->delete();

if ($result) {
    setFlashMessage('Votre message a été supprimé avec succès.', 'success');
    logAction("Message $messageId supprimé par l'utilisateur " . $_SESSION['user_id'], 'info');
} else {
    setFlashMessage('Une erreur est survenue lors de la suppression du message.', 'error');
    logAction("Échec de suppression du message $messageId", 'error');
}

// Rediriger selon le rôle
if (in_array($currentRole, ['counselor', 'admin'])) {
    redirect('../../view/backoffice/support/request_conversation.php?id=' . $requestId);
} else {
    redirect('../../view/frontoffice/support/request_details.php?id=' . $requestId);
}

?>

