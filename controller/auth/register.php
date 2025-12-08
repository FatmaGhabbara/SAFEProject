<?php
/**
 * ============================================
 * REGISTER CONTROLLER
 * SAFEProject - User Registration with Role Selection
 * ============================================
 */

session_start();

require_once '../../config.php';
require_once '../../model/User.php';
require_once '../helpers.php';

// If already logged in, redirect based on role
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('../../view/backoffice/support/support_requests.php');
    } elseif (isCounselor()) {
        redirect('../../view/backoffice/support/dashboard_counselor.php');
    } else {
        redirect('../../view/frontoffice/dashboard.php');
    }
}

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../../view/frontoffice/profil.php');
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    setFlashMessage('Security error. Please try again.', 'error');
    redirect('../../view/frontoffice/profil.php');
}

// Get form data
$nom = isset($_POST['nom']) ? trim(cleanInput($_POST['nom'])) : '';
$prenom = isset($_POST['prenom']) ? trim(cleanInput($_POST['prenom'])) : '';
$email = isset($_POST['email']) ? trim(strtolower($_POST['email'])) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
$role = isset($_POST['role']) ? cleanInput($_POST['role']) : 'user';

// For counselors
$specialite = isset($_POST['specialite']) ? trim(cleanInput($_POST['specialite'])) : '';
$biographie = isset($_POST['biographie']) ? trim(cleanInput($_POST['biographie'])) : '';

// Validation
$errors = [];

// Validation du nom
if (empty($nom)) {
    $errors[] = 'Le nom est obligatoire.';
} elseif (strlen($nom) < 2) {
    $errors[] = 'Le nom doit contenir au moins 2 caractères.';
} elseif (strlen($nom) > 100) {
    $errors[] = 'Le nom ne peut pas dépasser 100 caractères.';
} elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-\']+$/u', $nom)) {
    $errors[] = 'Le nom ne peut contenir que des lettres, espaces, tirets et apostrophes.';
}

// Validation du prénom
if (empty($prenom)) {
    $errors[] = 'Le prénom est obligatoire.';
} elseif (strlen($prenom) < 2) {
    $errors[] = 'Le prénom doit contenir au moins 2 caractères.';
} elseif (strlen($prenom) > 100) {
    $errors[] = 'Le prénom ne peut pas dépasser 100 caractères.';
} elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-\']+$/u', $prenom)) {
    $errors[] = 'Le prénom ne peut contenir que des lettres, espaces, tirets et apostrophes.';
}

// Validation de l'email
if (empty($email)) {
    $errors[] = 'L\'email est obligatoire.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format d\'email invalide.';
} elseif (strlen($email) > 255) {
    $errors[] = 'L\'email ne peut pas dépasser 255 caractères.';
} else {
    // Vérifier si l'email existe déjà
    $existingUser = findUserByEmail($email);
    if ($existingUser) {
        $errors[] = 'Cet email est déjà enregistré.';
    }
}

// Validation du mot de passe (validation simple - pas de complexité requise)
if (empty($password)) {
    $errors[] = 'Le mot de passe est obligatoire.';
} elseif (strlen($password) < 6) {
    $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
} elseif (strlen($password) > 72) {
    $errors[] = 'Le mot de passe ne peut pas dépasser 72 caractères.';
}

// Vérification de la confirmation du mot de passe
if ($password !== $password_confirm) {
    $errors[] = 'Les mots de passe ne correspondent pas.';
}

// Validation du rôle
if (!in_array($role, ['user', 'counselor'])) {
    $errors[] = 'Rôle invalide sélectionné.';
}

// Validation des champs spécifiques aux conseillers
if ($role === 'counselor') {
    // Validation de la spécialité
    if (empty($specialite)) {
        $errors[] = 'La spécialité est obligatoire pour les conseillers.';
    } elseif (strlen(trim($specialite)) < 3) {
        $errors[] = 'La spécialité doit contenir au moins 3 caractères.';
    } elseif (strlen($specialite) > 255) {
        $errors[] = 'La spécialité ne peut pas dépasser 255 caractères.';
    } elseif (!preg_match('/^[a-zA-ZÀ-ÿ0-9\s\-\'\.\(\)]+$/u', $specialite)) {
        $errors[] = 'La spécialité contient des caractères non autorisés.';
    }
    
    // Validation de la biographie
    if (empty($biographie)) {
        $errors[] = 'La biographie est obligatoire pour les conseillers.';
    } else {
        $biographieTrimmed = trim($biographie);
        if (strlen($biographieTrimmed) < 50) {
            $errors[] = 'La biographie doit contenir au moins 50 caractères.';
        } elseif (strlen($biographie) > 2000) {
            $errors[] = 'La biographie ne peut pas dépasser 2000 caractères.';
        } elseif (strlen($biographieTrimmed) < 50) {
            $errors[] = 'La biographie doit contenir au moins 50 caractères (espaces en début/fin exclus).';
        }
        
        // Vérifier que la biographie contient des mots réels (pas juste des caractères)
        $wordCount = str_word_count($biographieTrimmed);
        if ($wordCount < 10) {
            $errors[] = 'La biographie doit contenir au moins 10 mots.';
        }
    }
}

// If errors, return to form
if (!empty($errors)) {
    $_SESSION['register_errors'] = $errors;
    $_SESSION['old_data'] = [
        'nom' => $nom,
        'prenom' => $prenom,
        'email' => $email,
        'role' => $role,
        'specialite' => $specialite,
        'biographie' => $biographie
    ];
    redirect('../../view/frontoffice/profil.php');
}

// Create user with OOP
$user = new User();
$user->setNom($nom);
$user->setPrenom($prenom);
$user->setEmail($email);
$user->setPassword($password);
$user->setRole($role);
$user->setStatut('actif');

// Set counselor-specific fields if role is counselor
if ($role === 'counselor') {
    $user->setSpecialite($specialite);
    $user->setBiographie($biographie);
    $user->setStatutCounselor('actif');
    $user->setNombreDemandesActives(0);
}

// Debug: Log what we're trying to save
if ($role === 'counselor') {
    logAction("Attempting to register counselor - Email: $email, Specialite: $specialite, Biographie length: " . strlen($biographie), 'info');
}

try {
    $saveResult = $user->save();
} catch (Exception $e) {
    logAction("Exception during user save: " . $e->getMessage() . " - Email: $email, Role: $role", 'error');
    $saveResult = false;
}

if ($saveResult) {
    $userId = $user->getId();
    logAction("New user registered (ID: $userId) - Email: $email - Role: $role", 'info');
    
    if ($role === 'counselor') {
        logAction("Counselor profile created for user $userId - Specialite: " . $user->getSpecialite() . ", Statut: " . $user->getStatutCounselor(), 'info');
    }
    
    // Auto-login after registration
    $_SESSION['user_id'] = $user->getId();
    $_SESSION['email'] = $user->getEmail();
    $_SESSION['nom'] = $user->getNom();
    $_SESSION['prenom'] = $user->getPrenom();
    $_SESSION['role'] = $user->getRole();
    $_SESSION['logged_in'] = true;
    
    setFlashMessage('Registration successful! Welcome to SAFEProject.', 'success');
    
    // Redirect based on role - use the actual role from the user object
    $actualRole = $user->getRole();
    if ($actualRole === 'counselor') {
        redirect('../../view/backoffice/support/dashboard_counselor.php');
    } else {
        redirect('../../view/frontoffice/dashboard.php');
    }
    
} else {
    // Enhanced error logging
    $errorDetails = "Failed registration for email: $email, role: $role";
    if ($role === 'counselor') {
        $errorDetails .= ", specialite: $specialite, biographie length: " . strlen($biographie);
    }
    logAction($errorDetails, 'error');
    setFlashMessage('Registration failed. Please try again.', 'error');
    redirect('../../view/frontoffice/profil.php');
}

?>

