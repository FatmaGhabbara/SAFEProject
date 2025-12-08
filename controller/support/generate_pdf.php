<?php
/**
 * ============================================
 * PDF GENERATION CONTROLLER
 * SAFEProject - Support Request PDF Export
 * ============================================
 */

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/SupportRequest.php';
require_once __DIR__ . '/../../model/SupportMessage.php';
require_once __DIR__ . '/../../model/User.php';
require_once __DIR__ . '/../helpers.php';

// Check if logged in
if (!isLoggedIn()) {
    setFlashMessage('Vous devez être connecté pour accéder à cette fonctionnalité.', 'error');
    redirect('/view/frontoffice/login.php');
    exit();
}

// Get and validate request ID
$requestId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($requestId === 0) {
    setFlashMessage('ID de demande invalide.', 'error');
    redirect('/view/frontoffice/dashboard.php');
    exit();
}

try {
    // Load the support request
    $request = new SupportRequest($requestId);
    
    // Verify request exists and belongs to current user
    if (!$request->getId() || $request->getUserId() != $_SESSION['user_id']) {
        setFlashMessage('Demande introuvable ou accès non autorisé.', 'error');
        redirect('/view/frontoffice/dashboard.php');
        exit();
    }
    
    // Generate PDF (available for ongoing and completed requests)
    $htmlContent = $request->generatePDF();
    
    // Set headers for HTML download (acting as PDF)
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="demande_support_' . $requestId . '_' . date('Y-m-d') . '.html"');
    header('Content-Length: ' . strlen($htmlContent));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Output HTML content
    echo $htmlContent;
    exit();
    
} catch (Exception $e) {
    // Log error
    error_log('PDF Generation Error: ' . $e->getMessage());
    
    setFlashMessage('Une erreur est survenue lors de la génération du PDF. Veuillez réessayer plus tard.', 'error');
    redirect('/view/frontoffice/support/request_details.php?id=' . $requestId);
    exit();
}
?>
