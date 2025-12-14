<?php
/**
 * Controller: Delete a support message
 * SAFEProject - Support Module
 */

session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/SupportMessage.php';
require_once __DIR__ . '/../../model/SupportRequest.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /view/frontoffice/login.php');
    exit();
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /view/frontoffice/support_dashboard.php');
    exit();
}

// Get message ID
$messageId = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;

if ($messageId === 0) {
    $_SESSION['error_message'] = 'Message invalide.';
    header('Location: /view/frontoffice/support_dashboard.php');
    exit();
}

// Retrieve the message
$messageObj = new SupportMessage($messageId);

// Save request ID before deletion
$requestId = $messageObj->getSupportRequestId();

// Check if message exists
if (!$messageObj->getId()) {
    $_SESSION['error_message'] = 'Message introuvable.';
    header('Location: /view/frontoffice/support_dashboard.php');
    exit();
}

// Check if message belongs to the user
if ($messageObj->getSenderId() != $_SESSION['user_id']) {
    $_SESSION['error_message'] = 'Vous ne pouvez supprimer que vos propres messages.';
    header('Location: /view/frontoffice/support_request_details.php?id=' . $requestId);
    exit();
}

// Check if the associated request is not completed or cancelled
$request = new SupportRequest($requestId);
if ($request->getId() && in_array($request->getStatut(), ['terminee', 'annulee'])) {
    $_SESSION['error_message'] = 'Vous ne pouvez pas supprimer un message d\'une demande terminée ou annulée.';
    header('Location: /view/frontoffice/support_request_details.php?id=' . $requestId);
    exit();
}

// Delete the message
$result = $messageObj->delete();

if ($result) {
    $_SESSION['success_message'] = 'Votre message a été supprimé avec succès.';
} else {
    $_SESSION['error_message'] = 'Une erreur est survenue lors de la suppression du message.';
}

// Redirect back to request details
header('Location: /view/frontoffice/support_request_details.php?id=' . $requestId);
exit();
?>
