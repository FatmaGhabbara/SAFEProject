<?php
/**
 * ============================================
 * SMART DASHBOARD ROUTER
 * Redirige vers le bon dashboard selon le rôle
 * ============================================
 */

session_start();
require_once '../config.php';

// Check if logged in
if (!isLoggedIn()) {
    header('Location: frontoffice/login.php');
    exit();
}

// Redirect based on role
$role = $_SESSION['role'] ?? 'user';

switch ($role) {
    case 'admin':
        header('Location: backoffice/support/support_requests.php');
        break;
        
    case 'counselor':
        header('Location: backoffice/support/dashboard_counselor.php');
        break;
        
    case 'user':
    default:
        header('Location: frontoffice/dashboard.php');
        break;
}

exit();
?>

