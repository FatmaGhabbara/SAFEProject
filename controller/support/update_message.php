<?php
/**
 * ============================================
 * CONTRÔLEUR : Modification d'un message
 * SAFEProject - Module Support Psychologique (OOP)
 * ============================================
 */

session_start();

require_once '../../config.php';
require_once '../../model/SupportMessage.php';
require_once '../../model/SupportRequest.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    setFlashMessage('Vous devez être connecté pour modifier un message.', 'error');
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

// Récupérer et valider les données
$messageId = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;
$newMessage = isset($_POST['message']) ? cleanInput($_POST['message']) : '';

// Validation
$errors = [];

if ($messageId === 0) {
    $errors[] = 'Message invalide.';
}

if (empty($newMessage)) {
    $errors[] = 'Le message ne peut pas être vide.';
} elseif (strlen($newMessage) < 10) {
    $errors[] = 'Le message doit contenir au moins 10 caractères.';
}

// Récupérer le message (OOP)
$messageObj = new SupportMessage($messageId);

if (!$messageObj->getId()) {
    $errors[] = 'Message introuvable.';
}

// Vérifier que le message appartient à l'utilisateur ou au conseiller
if ($messageObj->getId() && $messageObj->getSenderId() != $_SESSION['user_id']) {
    $errors[] = 'Vous ne pouvez modifier que vos propres messages.';
}

// Vérifier que la demande associée n'est pas terminée ou annulée
if ($messageObj->getId()) {
    $request = new SupportRequest($messageObj->getSupportRequestId());
    if ($request->getId() && in_array($request->getStatut(), ['terminee', 'annulee'])) {
        $errors[] = 'Vous ne pouvez pas modifier un message d\'une demande terminée ou annulée.';
    }
}

// Si des erreurs sont détectées
if (!empty($errors)) {
    $errorMessage = implode('<br>', $errors);
    setFlashMessage($errorMessage, 'error');
    
    $requestId = $messageObj->getSupportRequestId();
    if (in_array($currentRole, ['counselor', 'admin'])) {
        redirect('../../view/backoffice/support/request_conversation.php?id=' . $requestId);
    } else {
        redirect('../../view/frontoffice/support/request_details.php?id=' . $requestId);
    }
}

// Mettre à jour le message (OOP)
$messageObj->setMessage($newMessage);

if ($messageObj->save()) {
    setFlashMessage('Votre message a été modifié avec succès.', 'success');
    logAction("Message $messageId modifié par l'utilisateur " . $_SESSION['user_id'], 'info');
} else {
    setFlashMessage('Une erreur est survenue lors de la modification du message.', 'error');
    logAction("Échec de modification du message $messageId", 'error');
}

// Rediriger selon le rôle
$requestId = $messageObj->getSupportRequestId();
if (in_array($currentRole, ['counselor', 'admin'])) {
    redirect('../../view/backoffice/support/request_conversation.php?id=' . $requestId);
} else {
    redirect('../../view/frontoffice/support/request_details.php?id=' . $requestId);
}

?>

