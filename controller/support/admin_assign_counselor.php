<?php
/**
 * ============================================
 * CONTRÔLEUR ADMIN : Assigner un conseiller
 * SAFEProject - Module Support Psychologique
 * ============================================
 */

session_start();

require_once '../../model/config.php';
require_once '../../model/support_functions.php';

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
$request = getSupportRequestById($requestId);
if (!$request) {
    $errors[] = 'Demande introuvable.';
}

// Vérifier que le conseiller existe
$counselor = getCounselorById($counselorId);
if (!$counselor) {
    $errors[] = 'Conseiller introuvable.';
}

// Si des erreurs sont détectées
if (!empty($errors)) {
    $errorMessage = implode('<br>', $errors);
    setFlashMessage($errorMessage, 'error');
    redirect('../../view/backoffice/support/assign_counselor.php?id=' . $requestId);
}

// Assigner le conseiller
$result = assignCounselor($requestId, $counselorId);

// Mettre à jour les notes admin si fournies
if ($result && !empty($notesAdmin)) {
    updateSupportRequest($requestId, ['notes_admin' => $notesAdmin]);
}

if ($result) {
    setFlashMessage('Le conseiller a été assigné avec succès à la demande.', 'success');
    logAction("Conseiller $counselorId assigné à la demande $requestId par admin " . $_SESSION['user_id'], 'info');
} else {
    setFlashMessage('Une erreur est survenue lors de l\'assignation du conseiller.', 'error');
    logAction("Échec d'assignation du conseiller $counselorId à la demande $requestId", 'error');
}

// Rediriger vers la liste des demandes
redirect('../../view/backoffice/support/support_requests.php');

?>

