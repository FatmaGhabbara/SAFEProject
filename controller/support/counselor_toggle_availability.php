<?php
/**
 * ============================================
 * CONTROLLER: Counselor Toggle Availability
 * SAFEProject - Toggle counselor availability status
 * ============================================
 */

session_start();

require_once '../../config.php';
require_once '../../model/User.php';
require_once '../helpers.php';

// Check counselor access
if (!isLoggedIn() || !in_array($_SESSION['role'], ['counselor', 'admin'])) {
    setFlashMessage('Accès refusé.', 'error');
    redirect('../../view/frontoffice/login.php');
}

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../../view/backoffice/support/dashboard_counselor.php');
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    setFlashMessage('Erreur de sécurité. Veuillez réessayer.', 'error');
    redirect('../../view/backoffice/support/dashboard_counselor.php');
}

$userId = $_SESSION['user_id'];

// Get counselor profile
$counselorUser = getCounselorByUserId($userId);

if (!$counselorUser) {
    setFlashMessage('Profil conseiller introuvable.', 'error');
    redirect('../../view/backoffice/support/dashboard_counselor.php');
}

// Get availability status from form
$disponibilite = isset($_POST['disponibilite']) ? true : false;

// Update availability
$counselorUser->setDisponibilite($disponibilite);

if ($counselorUser->save()) {
    $status = $disponibilite ? 'disponible' : 'indisponible';
    setFlashMessage("Votre statut de disponibilité a été mis à jour : vous êtes maintenant $status.", 'success');
    logAction("Counselor $userId toggled availability to: " . ($disponibilite ? 'available' : 'unavailable'), 'info');
} else {
    setFlashMessage('Erreur lors de la mise à jour de votre disponibilité.', 'error');
    logAction("Error updating availability for counselor $userId", 'error');
}

// Redirect back to dashboard
redirect('../../view/backoffice/support/dashboard_counselor.php');

?>

