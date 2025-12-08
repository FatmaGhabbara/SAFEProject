<?php
/**
 * ============================================
 * CONTRÔLEUR : Mise à jour du profil utilisateur
 * SAFEProject - Permet à chaque utilisateur de modifier son propre profil
 * ============================================
 */

session_start();

require_once '../../config.php';
require_once '../../model/User.php';
require_once '../helpers.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    setFlashMessage('Vous devez être connecté pour modifier votre profil.', 'error');
    redirect('../../view/frontoffice/login.php');
}

// Vérifier la méthode de requête
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../../view/frontoffice/profil.php');
}

// Vérifier le token CSRF
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    setFlashMessage('Erreur de sécurité. Veuillez réessayer.', 'error');
    redirect('../../view/frontoffice/profil.php');
}

// Récupérer l'ID de l'utilisateur
$userId = $_SESSION['user_id'];
$currentRole = $_SESSION['role'] ?? 'user';

// Récupérer l'utilisateur
$user = new User($userId);

if (!$user->getId()) {
    setFlashMessage('Utilisateur introuvable.', 'error');
    redirect('../../view/frontoffice/profil.php');
}

// Récupérer les données du formulaire
$nom = isset($_POST['nom']) ? cleanInput($_POST['nom']) : '';
$prenom = isset($_POST['prenom']) ? cleanInput($_POST['prenom']) : '';
$email = isset($_POST['email']) ? cleanInput($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

// Pour les conseillers uniquement
$specialite = null;
$biographie = null;
if ($currentRole === 'counselor') {
    $specialite = isset($_POST['specialite']) ? cleanInput($_POST['specialite']) : null;
    $biographie = isset($_POST['biographie']) ? cleanInput($_POST['biographie']) : null;
}

// Validation
$errors = [];

if (empty($nom) || empty($prenom)) {
    $errors[] = 'Le nom et le prénom sont obligatoires.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email invalide.';
}

// Vérifier si l'email est déjà utilisé par un autre utilisateur
if ($email !== $user->getEmail()) {
    $existingUser = findUserByEmail($email);
    if ($existingUser && $existingUser->getId() != $userId) {
        $errors[] = 'Cet email est déjà utilisé par un autre utilisateur.';
    }
}

// Vérifier le mot de passe si fourni
if (!empty($password)) {
    if (strlen($password) < 6) {
        $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
    }
    if ($password !== $password_confirm) {
        $errors[] = 'Les mots de passe ne correspondent pas.';
    }
}

// Si des erreurs sont détectées
if (!empty($errors)) {
    $errorMessage = implode('<br>', $errors);
    setFlashMessage($errorMessage, 'error');
    redirect('../../view/frontoffice/profil.php');
}

// Mettre à jour les informations de base
$user->setNom($nom);
$user->setPrenom($prenom);
$user->setEmail($email);

// Mettre à jour le mot de passe si fourni
$updatePassword = !empty($password);
if ($updatePassword) {
    $user->setPassword($password);
}

// Mettre à jour les informations spécifiques aux conseillers
if ($currentRole === 'counselor') {
    $user->setSpecialite($specialite);
    $user->setBiographie($biographie);
}

// Sauvegarder les modifications
if ($user->save()) {
    // Mettre à jour la session avec les nouvelles informations
    $_SESSION['nom'] = $user->getNom();
    $_SESSION['prenom'] = $user->getPrenom();
    $_SESSION['email'] = $user->getEmail();
    
    setFlashMessage('Votre profil a été mis à jour avec succès.', 'success');
    logAction("Profil de l'utilisateur $userId mis à jour", 'info');
} else {
    setFlashMessage('Une erreur est survenue lors de la mise à jour de votre profil.', 'error');
    logAction("Échec de mise à jour du profil de l'utilisateur $userId", 'error');
}

// Rediriger selon le rôle
if (in_array($currentRole, ['counselor', 'admin'])) {
    redirect('../../view/backoffice/support/dashboard_counselor.php');
} else {
    redirect('../../view/frontoffice/profil.php');
}

?>

