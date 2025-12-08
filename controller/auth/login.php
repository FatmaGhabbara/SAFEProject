<?php
/**
 * ============================================
 * LOGIN CONTROLLER
 * SAFEProject - User Authentication
 * ============================================
 */

session_start();

require_once '../../config.php';
require_once '../../model/User.php';
require_once '../helpers.php';

// CRITICAL: Clean up any residual session data from previous logout
// This ensures a fresh login even if logout didn't fully clear everything
if (!isLoggedIn()) {
    // If not logged in, ensure session is clean
    if (isset($_SESSION['user_id']) || isset($_SESSION['logged_in'])) {
        // Clear any residual user data
        unset($_SESSION['user_id'], $_SESSION['email'], $_SESSION['nom'], 
              $_SESSION['prenom'], $_SESSION['role'], $_SESSION['logged_in']);
    }
}

// If already logged in, redirect based on role
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('../../view/backoffice/support/support_requests.php');
    } elseif (isCounselor()) {
        redirect('../../view/backoffice/support/my_assigned_requests.php');
    } else {
        redirect('../../view/frontoffice/dashboard.php');
    }
}

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../../view/frontoffice/profil.php');
}

// Get form data
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// Validation
$errors = [];

if (empty($email)) {
    $errors[] = 'Email is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format.';
}

if (empty($password)) {
    $errors[] = 'Password is required.';
}

if (!empty($errors)) {
    $_SESSION['login_errors'] = $errors;
    $_SESSION['old_email'] = $email;
    redirect('../../view/frontoffice/profil.php');
}

// Authenticate using helper function
$user = authenticateUser($email, $password);

if ($user) {
    // Check if user is active
    if (!$user->isActive()) {
        $_SESSION['login_errors'] = ['Your account is not active. Please contact support.'];
        redirect('../../view/frontoffice/profil.php');
    }
    
    // Set session variables
    $_SESSION['user_id'] = $user->getId();
    $_SESSION['email'] = $user->getEmail();
    $_SESSION['nom'] = $user->getNom();
    $_SESSION['prenom'] = $user->getPrenom();
    $_SESSION['role'] = $user->getRole();
    $_SESSION['logged_in'] = true;
    
    logAction("User logged in: " . $user->getEmail(), 'info');
    
    // Redirect based on role
    if (isAdmin()) {
        redirect('../../view/backoffice/support/support_requests.php');
    } elseif (isCounselor()) {
        redirect('../../view/backoffice/support/dashboard_counselor.php');
    } else {
        redirect('../../view/frontoffice/dashboard.php');
    }
    
} else {
    // Authentication failed
    $_SESSION['login_errors'] = ['Invalid email or password.'];
    $_SESSION['old_email'] = $email;
    logAction("Failed login attempt for: $email", 'warning');
    redirect('../../view/frontoffice/profil.php');
}

?>

