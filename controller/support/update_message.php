<?php
/**
 * Controller: Update a support message
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

// Get and validate data
$messageId = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;
$newMessage = isset($_POST['message']) ? trim($_POST['message']) : '';

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

// Retrieve the message
$messageObj = new SupportMessage($messageId);

if (!$messageObj->getId()) {
    $errors[] = 'Message introuvable.';
}

// Check if message belongs to the user
if ($messageObj->getId() && $messageObj->getSenderId() != $_SESSION['user_id']) {
    $errors[] = 'Vous ne pouvez modifier que vos propres messages.';
}

// Check if the associated request is not completed or cancelled
if ($messageObj->getId()) {
    $request = new SupportRequest($messageObj->getSupportRequestId());
    if ($request->getId() && in_array($request->getStatut(), ['terminee', 'annulee'])) {
        $errors[] = 'Vous ne pouvez pas modifier un message d\'une demande terminée ou annulée.';
    }
}

// If errors detected
if (!empty($errors)) {
    $_SESSION['error_message'] = implode('<br>', $errors);
    $requestId = $messageObj->getSupportRequestId();
    header('Location: /view/frontoffice/support_request_details.php?id=' . $requestId);
    exit();
}

// Update the message
$messageObj->setMessage($newMessage);

if ($messageObj->save()) {
    $_SESSION['success_message'] = 'Votre message a été modifié avec succès.';
} else {
    $_SESSION['error_message'] = 'Une erreur est survenue lors de la modification du message.';
}

// Redirect back to request details
$requestId = $messageObj->getSupportRequestId();
header('Location: /view/frontoffice/support_request_details.php?id=' . $requestId);
exit();
?>
