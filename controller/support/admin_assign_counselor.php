<?php
/**
 * ============================================
 * CONTRÔLEUR ADMIN : Assigner un conseiller
 * SAFEProject - Module Support Psychologique
 * ============================================
 */

session_start();

require_once '../../config.php';
require_once '../../model/SupportRequest.php';
require_once '../../model/User.php';
require_once '../helpers.php';

// Vérifier si l'utilisateur est admin
if (!isAdmin()) {
    setFlashMessage('Accès refusé. Vous devez être administrateur.', 'error');
    redirect('../../view/frontoffice/support/support_info.php');
}

// Vérifier la méthode de requête
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../../view/backoffice/support/support_requests.php');
}

// Vérifier le token CSRF
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    setFlashMessage('Erreur de sécurité. Veuillez réessayer.', 'error');
    redirect('../../view/backoffice/support/support_requests.php');
}

// Récupérer les données
$requestId = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
$counselorId = isset($_POST['counselor_id']) ? intval($_POST['counselor_id']) : 0;
$notesAdmin = isset($_POST['notes_admin']) ? cleanInput($_POST['notes_admin']) : '';

// Validation
$errors = [];

if ($requestId === 0) {
    $errors[] = 'Demande invalide.';
}

if ($counselorId === 0) {
    $errors[] = 'Veuillez sélectionner un conseiller.';
}

// Vérifier que la demande existe
$request = new SupportRequest($requestId);
if (!$request->getId()) {
    $errors[] = 'Demande introuvable.';
    logAction("Request $requestId not found", 'error');
}

// Vérifier que le conseiller existe
$counselorUser = getCounselorById($counselorId);
if (!$counselorUser) {
    $errors[] = 'Conseiller introuvable.';
    logAction("Counselor $counselorId not found", 'error');
}

// Si des erreurs sont détectées
if (!empty($errors)) {
    $errorMessage = implode('<br>', $errors);
    setFlashMessage($errorMessage, 'error');
    redirect('../../view/backoffice/support/assign_counselor.php?id=' . $requestId);
}

// Assigner le conseiller
$request->setCounselorId($counselorId);
$request->setStatut('assignee');
$request->setDateAssignation(date('Y-m-d H:i:s'));
$request->setNotesAdmin($notesAdmin);

$result = $request->save();

if ($result) {
    setFlashMessage('Le conseiller a été assigné avec succès à la demande.', 'success');
    logAction("Conseiller $counselorId assigné à la demande $requestId par admin " . $_SESSION['user_id'], 'info');
    
    // Récupérer les informations de l'utilisateur
    $requestUser = $request->getUser();
    
    // Créer un message système automatique pour l'utilisateur
    require_once '../../model/SupportMessage.php';
    $systemMessage = new SupportMessage();
    $systemMessage->setSupportRequestId($requestId);
    $systemMessage->setSenderId($_SESSION['user_id']); // Admin qui assigne
    $systemMessage->setMessage("🔔 Le conseiller {$counselorUser->getFullName()} a été assigné à cette demande. Vous serez contacté prochainement.");
    $systemMessage->save();
    
    // Créer un message système automatique pour le conseiller avec les notes de l'admin
    $counselorMessage = new SupportMessage();
    $counselorMessage->setSupportRequestId($requestId);
    $counselorMessage->setSenderId($_SESSION['user_id']); // Admin qui assigne
    $messageText = "📋 Vous avez été assigné à cette demande de support.\n\n";
    $messageText .= "**Patient:** {$requestUser->getFullName()}\n";
    $messageText .= "**Titre:** {$request->getTitre()}\n";
    $messageText .= "**Urgence:** " . ucfirst($request->getUrgence()) . "\n\n";
    if (!empty($notesAdmin)) {
        $messageText .= "**Notes de l'administrateur:**\n{$notesAdmin}\n\n";
    }
    $messageText .= "Vous pouvez maintenant commencer la conversation avec le patient.";
    $counselorMessage->setMessage($messageText);
    $counselorMessage->save();
} else {
    setFlashMessage('Une erreur est survenue lors de l\'assignation du conseiller.', 'error');
    logAction("Échec d'assignation du conseiller $counselorId à la demande $requestId", 'error');
}

// Rediriger vers la liste des demandes
redirect('../../view/backoffice/support/support_requests.php');

?>

