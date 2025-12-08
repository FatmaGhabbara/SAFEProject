<?php
/**
 * ============================================
 * SAFEProject - Home Page
 * Always redirects to login page or dashboard
 * ============================================
 */

session_start();
require_once __DIR__ . '/config.php';

// If already logged in, redirect based on role to their dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('view/backoffice/support/support_requests.php');
    } elseif (isCounselor()) {
        redirect('view/backoffice/support/dashboard_counselor.php');
    } else {
        redirect('view/frontoffice/dashboard.php');
    }
}

// Not logged in - afficher la page principale profil (login/signup)
redirect('view/frontoffice/profil.php');
?>

