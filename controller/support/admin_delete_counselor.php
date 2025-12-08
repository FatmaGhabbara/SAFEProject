<?php
/**
 * ============================================
 * CONTRÔLEUR ADMIN : Supprimer un conseiller
 * SAFEProject - Module Support Psychologique (OOP)
 * ============================================
 */

session_start();

require_once '../../config.php';
require_once '../helpers.php';

// Vérifier si l'utilisateur est admin
if (!isAdmin()) {
    setFlashMessage('Accès refusé. Vous devez être administrateur.', 'error');
    redirect('../../view/frontoffice/support/support_info.php');
}

// Récupérer l'ID du conseiller
$counselorId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($counselorId === 0) {
    setFlashMessage('Conseiller invalide.', 'error');
    redirect('../../view/backoffice/support/counselors_list.php');
}

// Vérifier que le conseiller existe
$counselorUser = getCounselorById($counselorId);

if (!$counselorUser) {
    setFlashMessage('Conseiller introuvable.', 'error');
    redirect('../../view/backoffice/support/counselors_list.php');
}

// Vérifier si le conseiller a des demandes actives
if ($counselorUser->getNombreDemandesActives() > 0) {
    setFlashMessage('Impossible de supprimer ce conseiller car il a des demandes actives.', 'error');
    redirect('../../view/backoffice/support/counselors_list.php');
}

// Supprimer le conseiller
$result = deleteCounselor($counselorId);

if ($result) {
    setFlashMessage('Le conseiller a été supprimé avec succès.', 'success');
    logAction("Conseiller $counselorId supprimé par admin " . $_SESSION['user_id'], 'warning');
} else {
    setFlashMessage('Une erreur est survenue lors de la suppression du conseiller.', 'error');
    logAction("Échec de suppression du conseiller $counselorId", 'error');
}

// Rediriger vers la liste des conseillers
redirect('../../view/backoffice/support/counselors_list.php');

?>
