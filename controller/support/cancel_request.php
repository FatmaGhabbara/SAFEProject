<?php
/**
 * ============================================
 * CONTRÔLEUR : Annulation d'une demande de support
 * SAFEProject - Module Support Psychologique
 * ============================================
 */

session_start();

require_once '../../model/config.php';
require_once '../../model/support_functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    setFlashMessage('Vous devez être connecté pour annuler une demande.', 'error');
    redirect('../../view/frontoffice/login.html');
}

// Vérifier la méthode de requête
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../../view/frontoffice/support/my_requests.php');
}

// Vérifier le token CSRF
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    setFlashMessage('Erreur de sécurité. Veuillez réessayer.', 'error');
    redirect('../../view/frontoffice/support/my_requests.php');
}

// Récupérer l'ID de la demande
$requestId = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;

if ($requestId === 0) {
    setFlashMessage('Demande invalide.', 'error');
    redirect('../../view/frontoffice/support/my_requests.php');
}

// Récupérer la demande
$request = getSupportRequestById($requestId);

// Vérifier que la demande existe et appartient à l'utilisateur
if (!$request || $request['user_id'] != $_SESSION['user_id']) {
    setFlashMessage('Demande introuvable ou accès non autorisé.', 'error');
    redirect('../../view/frontoffice/support/my_requests.php');
}

// Vérifier que la demande est en attente
if ($request['statut'] !== 'en_attente') {
    setFlashMessage('Vous ne pouvez annuler que les demandes en attente.', 'error');
    redirect('../../view/frontoffice/support/request_details.php?id=' . $requestId);
}

// Annuler la demande
$result = updateSupportRequest($requestId, ['statut' => 'annulee']);

if ($result) {
    setFlashMessage('Votre demande a été annulée avec succès.', 'success');
    logAction("Demande $requestId annulée par l'utilisateur " . $_SESSION['user_id'], 'info');
} else {
    setFlashMessage('Une erreur est survenue lors de l\'annulation de la demande.', 'error');
    logAction("Échec d'annulation de la demande $requestId", 'error');
}

// Rediriger vers mes demandes
redirect('../../view/frontoffice/support/my_requests.php');

?>

