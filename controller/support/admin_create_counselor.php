<?php
/**
 * ============================================
 * CONTRÔLEUR ADMIN : Créer un conseiller
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
    redirect('../../view/backoffice/support/counselors_list.php');
}

// Vérifier le token CSRF
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    setFlashMessage('Erreur de sécurité. Veuillez réessayer.', 'error');
    redirect('../../view/backoffice/support/add_counselor.php');
}

// Récupérer les données
$userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$specialite = isset($_POST['specialite']) ? cleanInput($_POST['specialite']) : '';
$biographie = isset($_POST['biographie']) ? cleanInput($_POST['biographie']) : '';
$statut = isset($_POST['statut']) ? $_POST['statut'] : 'actif';

// Validation
$errors = [];

if ($userId === 0) {
    $errors[] = 'Veuillez sélectionner un utilisateur.';
}

if (empty($specialite)) {
    $errors[] = 'La spécialité est obligatoire.';
}

if (!in_array($statut, ['actif', 'inactif', 'en_pause'])) {
    $errors[] = 'Statut invalide.';
}

// Vérifier si l'utilisateur n'est pas déjà conseiller
if (isCounselor($userId)) {
    $errors[] = 'Cet utilisateur est déjà enregistré comme conseiller.';
}

// Si des erreurs sont détectées
if (!empty($errors)) {
    $errorMessage = implode('<br>', $errors);
    setFlashMessage($errorMessage, 'error');
    redirect('../../view/backoffice/support/add_counselor.php');
}

// Créer le conseiller
$counselorId = createCounselor($userId, $specialite, $biographie, $statut);

if ($counselorId) {
    setFlashMessage('Le conseiller a été créé avec succès !', 'success');
    logAction("Nouveau conseiller créé (ID: $counselorId) pour utilisateur $userId", 'info');
    redirect('../../view/backoffice/support/counselors_list.php');
} else {
    setFlashMessage('Une erreur est survenue lors de la création du conseiller.', 'error');
    logAction("Échec de création d'un conseiller pour utilisateur $userId", 'error');
    redirect('../../view/backoffice/support/add_counselor.php');
}

?>

