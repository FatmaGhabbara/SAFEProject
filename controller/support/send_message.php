<?php
/**
 * ============================================
 * CONTRÔLEUR : Envoi d'un message de suivi
 * SAFEProject - Module Support Psychologique
 * ============================================
 */

session_start();

require_once '../../model/config.php';
require_once '../../model/support_functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    setFlashMessage('Vous devez être connecté pour envoyer un message.', 'error');
    redirect('../../view/frontoffice/login.html');
}

// Vérifier la méthode de requête
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../../view/frontoffice/support/my_requests.php');
}

// Vérifier le token CSRF
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    setFlashMessage('Erreur de sécurité. Veuillez réessayer.', 'error');
    redirect('../../view/frontoffice/support/my_requests.php');
}

// Récupérer et valider les données
$requestId = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
$message = isset($_POST['message']) ? cleanInput($_POST['message']) : '';

// Validation
$errors = [];

if ($requestId === 0) {
    $errors[] = 'Demande invalide.';
}

if (empty($message)) {
    $errors[] = 'Le message ne peut pas être vide.';
} elseif (strlen($message) < 10) {
    $errors[] = 'Le message doit contenir au moins 10 caractères.';
}

// Vérifier que la demande existe et appartient à l'utilisateur
$request = getSupportRequestById($requestId);

if (!$request || $request['user_id'] != $_SESSION['user_id']) {
    $errors[] = 'Demande introuvable ou accès non autorisé.';
}

// Vérifier que la demande n'est pas terminée ou annulée
if ($request && in_array($request['statut'], ['terminee', 'annulee'])) {
    $errors[] = 'Vous ne pouvez pas envoyer de message sur une demande terminée ou annulée.';
}

// Si des erreurs sont détectées
if (!empty($errors)) {
    $errorMessage = implode('<br>', $errors);
    setFlashMessage($errorMessage, 'error');
    redirect('../../view/frontoffice/support/request_details.php?id=' . $requestId);
}

// Envoyer le message
$userId = $_SESSION['user_id'];
$messageId = sendSupportMessage($requestId, $userId, $message);

if ($messageId) {
    // Mettre à jour le statut de la demande si elle est "assignée"
    if ($request['statut'] === 'assignee') {
        updateSupportRequest($requestId, ['statut' => 'en_cours']);
    }
    
    // Succès
    setFlashMessage('Votre message a été envoyé avec succès.', 'success');
    logAction("Message envoyé (ID: $messageId) sur la demande $requestId", 'info');
    
} else {
    // Échec
    setFlashMessage('Une erreur est survenue lors de l\'envoi du message.', 'error');
    logAction("Échec d'envoi de message sur la demande $requestId", 'error');
}

// Rediriger vers les détails de la demande
redirect('../../view/frontoffice/support/request_details.php?id=' . $requestId);

?>

