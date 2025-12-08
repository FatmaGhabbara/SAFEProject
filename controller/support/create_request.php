<?php
/**
 * ============================================
 * CONTRÔLEUR : Création d'une demande de support
 * SAFEProject - Module Support Psychologique
 * ============================================
 */

session_start();

require_once '../../config.php';
require_once '../../model/SupportRequest.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    setFlashMessage('Vous devez être connecté pour créer une demande.', 'error');
    redirect('../../view/frontoffice/login.html');
}

// Vérifier la méthode de requête
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../../view/frontoffice/support/support_form.php');
}

// Vérifier le token CSRF
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    setFlashMessage('Erreur de sécurité. Veuillez réessayer.', 'error');
    redirect('../../view/frontoffice/support/support_form.php');
}

// Récupérer et valider les données
$titre = isset($_POST['titre']) ? cleanInput($_POST['titre']) : '';
$description = isset($_POST['description']) ? cleanInput($_POST['description']) : '';
$urgence = isset($_POST['urgence']) ? $_POST['urgence'] : 'moyenne';

// Validation des données
$errors = [];

if (empty($titre)) {
    $errors[] = 'Le titre est obligatoire.';
} elseif (strlen($titre) < 5) {
    $errors[] = 'Le titre doit contenir au moins 5 caractères.';
} elseif (strlen($titre) > 255) {
    $errors[] = 'Le titre ne peut pas dépasser 255 caractères.';
}

if (empty($description)) {
    $errors[] = 'La description est obligatoire.';
} elseif (strlen($description) < 50) {
    $errors[] = 'La description doit contenir au moins 50 caractères.';
}

if (!in_array($urgence, ['basse', 'moyenne', 'haute'])) {
    $errors[] = 'Niveau d\'urgence invalide.';
}

// Si des erreurs sont détectées
if (!empty($errors)) {
    $errorMessage = implode('<br>', $errors);
    setFlashMessage($errorMessage, 'error');
    redirect('../../view/frontoffice/support/support_form.php');
}

// Créer la demande avec OOP
$userId = $_SESSION['user_id'];

$request = new SupportRequest();
$request->setUserId($userId);
$request->setTitre($titre);
$request->setDescription($description);
$request->setUrgence($urgence);
$request->setStatut('en_attente');  // Explicitly set default status

if ($request->save()) {
    // Succès
    setFlashMessage('Votre demande a été créée avec succès ! Un conseiller vous sera assigné prochainement.', 'success');
    logAction("Demande de support créée (ID: {$request->getId()}) par utilisateur $userId", 'info');
    
    // Rediriger vers mes demandes
    redirect('../../view/frontoffice/support/my_requests.php');
    
} else {
    // Échec
    setFlashMessage('Une erreur est survenue lors de la création de votre demande. Veuillez réessayer.', 'error');
    logAction("Échec de création d'une demande de support par utilisateur $userId", 'error');
    redirect('../../view/frontoffice/support/support_form.php');
}

?>

