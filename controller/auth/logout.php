<?php
/**
 * ============================================
 * LOGOUT CONTROLLER - SIMPLIFIED
 * SAFEProject - User Logout
 * Clears session and redirects to login
 * ============================================
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Try to require config for logging
$config_path = __DIR__ . '/../../config.php';
if (file_exists($config_path)) {
    require_once $config_path;
    
    // Log the logout if logging is available (BEFORE clearing session)
    if (function_exists('logAction') && isset($_SESSION['user_id'])) {
        $user_email = $_SESSION['email'] ?? 'unknown';
        logAction("User logged out: " . $user_email, 'info');
    }
}

// Clear ALL session data
$_SESSION = array();

// Get session cookie parameters
$cookieParams = session_get_cookie_params();

// Delete the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(
        session_name(),
        '',
        time() - 3600,
        $cookieParams['path'],
        $cookieParams['domain'],
        $cookieParams['secure'],
        $cookieParams['httponly']
    );
}

// Destroy the session
session_unset();
session_destroy();

// Clear any other cookies
$cookies_to_clear = [session_name(), 'PHPSESSID', 'remember_token', 'user_token'];
foreach ($cookies_to_clear as $cookie_name) {
    if (isset($_COOKIE[$cookie_name])) {
        setcookie($cookie_name, '', time() - 3600, '/');
        unset($_COOKIE[$cookie_name]);
    }
}

// Start a fresh session for flash message
session_start();
$_SESSION = array();

// Set flash message
if (function_exists('setFlashMessage')) {
    setFlashMessage('Vous avez été déconnecté avec succès.', 'success');
}

// Regenerate session ID
session_regenerate_id(true);

// ALWAYS redirect to profil page - Use relative path from controller/auth/
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Location: ../../view/frontoffice/profil.php');
exit();

?>
