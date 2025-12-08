<?php
/**
 * ============================================
 * CONTRÔLEUR ADMIN : Mettre à jour un conseiller
 * SAFEProject - Module Support Psychologique
 * ============================================
 */

session_start();

require_once '../../config.php';
require_once '../../model/User.php';
require_once '../helpers.php';

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
    redirect('../../view/backoffice/support/counselors_list.php');
}

// Récupérer les données
$counselorId = isset($_POST['counselor_id']) ? intval($_POST['counselor_id']) : 0;
$nom = isset($_POST['nom']) ? cleanInput($_POST['nom']) : '';
$prenom = isset($_POST['prenom']) ? cleanInput($_POST['prenom']) : '';
$email = isset($_POST['email']) ? cleanInput($_POST['email']) : '';
$specialite = isset($_POST['specialite']) ? cleanInput($_POST['specialite']) : 'Psychologie générale';
$bio = isset($_POST['bio']) ? cleanInput($_POST['bio']) : '';
$statut = isset($_POST['statut']) ? cleanInput($_POST['statut']) : 'actif';

// Validation
$errors = [];

if ($counselorId === 0) {
    $errors[] = 'Conseiller invalide.';
}

if (empty($nom) || empty($prenom)) {
    $errors[] = 'Le nom et le prénom sont obligatoires.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email invalide.';
}

// Vérifier que le conseiller existe
$counselorUser = getCounselorById($counselorId);
if (!$counselorUser) {
    $errors[] = 'Conseiller introuvable.';
} else {
    $user = $counselorUser;
}

// Si des erreurs sont détectées
if (!empty($errors)) {
    $errorMessage = implode('<br>', $errors);
    setFlashMessage($errorMessage, 'error');
    redirect('../../view/backoffice/support/edit_counselor.php?id=' . $counselorId);
}

// Mettre à jour le User
$user->setNom($nom);
$user->setPrenom($prenom);
$user->setEmail($email);
$user->setStatut($statut);

if (!$user->save()) {
    setFlashMessage('Erreur lors de la mise à jour de l\'utilisateur.', 'error');
    redirect('../../view/backoffice/support/edit_counselor.php?id=' . $counselorId);
}

// Mettre à jour le Counselor
$result = updateCounselor($counselorId, $specialite, $bio, $statut);

if ($result) {
    setFlashMessage('Le conseiller a été mis à jour avec succès.', 'success');
    logAction("Conseiller $counselorId mis à jour par admin " . $_SESSION['user_id'], 'info');
    redirect('../../view/backoffice/support/view_counselor.php?id=' . $counselorId);
} else {
    setFlashMessage('Erreur lors de la mise à jour du conseiller.', 'error');
    logAction("Échec de mise à jour du conseiller $counselorId", 'error');
    redirect('../../view/backoffice/support/edit_counselor.php?id=' . $counselorId);
}

?>

