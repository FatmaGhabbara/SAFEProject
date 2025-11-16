<?php
/**
 * ============================================
 * CONTRÔLEUR ADMIN : Supprimer une demande
 * SAFEProject - Module Support Psychologique
 * ============================================
 */

session_start();

require_once '../../model/config.php';
require_once '../../model/support_functions.php';

// Vérifier si l'utilisateur est admin
if (!isAdmin()) {
    setFlashMessage('Accès refusé. Vous devez être administrateur.', 'error');
    redirect('../../view/frontoffice/support/support_info.php');
}

// Récupérer l'ID de la demande
$requestId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($requestId === 0) {
    setFlashMessage('Demande invalide.', 'error');
    redirect('../../view/backoffice/support/support_requests.php');
}

// Vérifier que la demande existe
$request = getSupportRequestById($requestId);

if (!$request) {
    setFlashMessage('Demande introuvable.', 'error');
    redirect('../../view/backoffice/support/support_requests.php');
}

// Supprimer la demande
$result = deleteSupportRequest($requestId);

if ($result) {
    setFlashMessage('La demande a été supprimée avec succès.', 'success');
    logAction("Demande $requestId supprimée par admin " . $_SESSION['user_id'], 'warning');
} else {
    setFlashMessage('Une erreur est survenue lors de la suppression de la demande.', 'error');
    logAction("Échec de suppression de la demande $requestId", 'error');
}

// Rediriger vers la liste des demandes
redirect('../../view/backoffice/support/support_requests.php');

?>

