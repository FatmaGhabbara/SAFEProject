<?php
/**
 * ============================================
 * CONTRÔLEUR : Envoi d'un message de suivi
 * SAFEProject - Module Support Psychologique
 * ============================================
 */

session_start();

require_once '../../config.php';
require_once '../../model/SupportMessage.php';
require_once '../../model/SupportRequest.php';
require_once '../helpers.php';

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

// Vérifier que la demande existe (OOP)
$request = new SupportRequest($requestId);

if (!$request->getId()) {
    $errors[] = 'Demande introuvable.';
}

// Vérifier l'accès selon le rôle
$currentRole = $_SESSION['role'] ?? 'user';
$currentUserId = $_SESSION['user_id'];

if ($currentRole === 'user') {
    // L'utilisateur ne peut envoyer des messages que sur ses propres demandes
    if ($request->getUserId() != $currentUserId) {
        $errors[] = 'Accès non autorisé à cette demande.';
    }
} elseif ($currentRole === 'counselor') {
    // Le conseiller ne peut envoyer des messages que sur les demandes qui lui sont assignées
    $counselorId = $request->getCounselorId();
    
    if (!$counselorId) {
        $errors[] = 'Aucun conseiller n\'est assigné à cette demande.';
        logAction("ERROR: No counselor assigned to request $requestId", 'error');
    } else {
        $counselorUser = getCounselorById($counselorId);
        if (!$counselorUser) {
            $errors[] = 'Conseiller introuvable.';
            logAction("ERROR: Counselor not found for ID: $counselorId", 'error');
        } elseif ($counselorUser->getId() != $currentUserId) {
            $errors[] = 'Cette demande ne vous est pas assignée. Elle est assignée à un autre conseiller.';
            logAction("ERROR: Counselor mismatch - Expected: $currentUserId, Got: " . $counselorUser->getId(), 'error');
        } else {
            logAction("SUCCESS: Counselor access granted for user $currentUserId", 'info');
        }
    }
}
// Les admins peuvent envoyer des messages sur toutes les demandes

// Vérifier que la demande n'est pas terminée ou annulée
if ($request->getId() && in_array($request->getStatut(), ['terminee', 'annulee'])) {
    $errors[] = 'Vous ne pouvez pas envoyer de message sur une demande terminée ou annulée.';
}

// Si des erreurs sont détectées
if (!empty($errors)) {
    $errorMessage = implode('<br>', $errors);
    setFlashMessage($errorMessage, 'error');
    
    // Redirection selon le rôle
    if (in_array($currentRole, ['counselor', 'admin'])) {
        redirect('../../view/backoffice/support/request_conversation.php?id=' . $requestId);
    } else {
        redirect('../../view/frontoffice/support/request_details.php?id=' . $requestId);
    }
}

// Envoyer le message (OOP)
$userId = $_SESSION['user_id'];

$messageObj = new SupportMessage();
$messageObj->setSupportRequestId($requestId);
$messageObj->setSenderId($userId);
$messageObj->setMessage($message);

if ($messageObj->save()) {
    // Mettre à jour le statut de la demande si elle est "assignée"
    if ($request->getStatut() === 'assignee') {
        $request->setStatut('en_cours');
        $request->save();
    }
    
    // Succès
    setFlashMessage('Votre message a été envoyé avec succès.', 'success');
    logAction("Message envoyé (ID: {$messageObj->getId()}) sur la demande $requestId par " . $currentRole, 'info');
    
} else {
    // Échec
    setFlashMessage('Une erreur est survenue lors de l\'envoi du message.', 'error');
    logAction("Échec d'envoi de message sur la demande $requestId", 'error');
}

// Rediriger selon le rôle
if (in_array($currentRole, ['counselor', 'admin'])) {
    redirect('../../view/backoffice/support/request_conversation.php?id=' . $requestId);
} else {
    redirect('../../view/frontoffice/support/request_details.php?id=' . $requestId);
}

?>

