<?php
/**
 * ============================================
 * CONTRÔLEUR COUNSELOR : Terminer une demande
 * SAFEProject - Module Support Psychologique
 * ============================================
 */

session_start();

require_once '../../config.php';
require_once '../../model/SupportRequest.php';

// Vérifier si l'utilisateur est conseiller ou admin
if (!in_array($_SESSION['role'] ?? '', ['counselor', 'admin'])) {
    setFlashMessage('Accès refusé.', 'error');
    redirect('../../view/frontoffice/login.php');
}

// Récupérer l'ID de la demande
$requestId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($requestId === 0) {
    setFlashMessage('Demande invalide.', 'error');
    redirect('../../view/backoffice/support/my_assigned_requests.php');
}

// Récupérer la demande
try {
    $request = new SupportRequest($requestId);
    
    if (!$request->getId()) {
        setFlashMessage('Demande introuvable.', 'error');
        redirect('../../view/backoffice/support/my_assigned_requests.php');
    }
    
    // Utiliser la méthode complete() qui existe déjà
    if ($request->complete()) {
        setFlashMessage('La demande a été marquée comme terminée avec succès.', 'success');
        logAction("Demande {$requestId} terminée par conseiller " . $_SESSION['user_id'], 'info');
    } else {
        setFlashMessage('Erreur lors de la mise à jour du statut.', 'error');
    }
    
} catch (Exception $e) {
    setFlashMessage('Erreur: ' . $e->getMessage(), 'error');
}

// Rediriger vers mes demandes assignées
redirect('../../view/backoffice/support/my_assigned_requests.php');

?>

