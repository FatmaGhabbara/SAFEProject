<?php
/**
 * Controller: Cancel a support request
 * SAFEProject - Support Module
 */

session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/SupportRequest.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /view/frontoffice/login.php');
    exit();
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /view/frontoffice/support_dashboard.php');
    exit();
}

// Get request ID
$requestId = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;

if ($requestId === 0) {
    $_SESSION['error_message'] = 'Demande invalide.';
    header('Location: /view/frontoffice/support_dashboard.php');
    exit();
}

// Retrieve the request
$request = new SupportRequest($requestId);

// Check if request exists and belongs to the user
if (!$request->getId() || $request->getUserId() != $_SESSION['user_id']) {
    $_SESSION['error_message'] = 'Demande introuvable ou accès non autorisé.';
    header('Location: /view/frontoffice/support_dashboard.php');
    exit();
}

// Check if request is pending
if ($request->getStatut() !== 'en_attente') {
    $_SESSION['error_message'] = 'Vous ne pouvez annuler que les demandes en attente.';
    header('Location: /view/frontoffice/support_request_details.php?id=' . $requestId);
    exit();
}

// Cancel the request
$result = $request->cancel();

if ($result) {
    $_SESSION['success_message'] = 'Votre demande a été annulée avec succès.';
} else {
    $_SESSION['error_message'] = 'Une erreur est survenue lors de l\'annulation de la demande.';
}

// Redirect to dashboard
header('Location: /view/frontoffice/support_dashboard.php');
exit();
?>
