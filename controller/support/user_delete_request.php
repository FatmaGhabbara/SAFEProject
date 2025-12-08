<?php
/**
 * ============================================
 * CONTRÔLEUR : Suppression d'une demande de support par l'utilisateur
 * SAFEProject - Module Support Psychologique (OOP)
 * ============================================
 */

session_start();

require_once '../../config.php';
require_once '../../model/SupportRequest.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    setFlashMessage('Vous devez être connecté pour supprimer une demande.', 'error');
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

// Récupérer la demande (OOP)
$request = new SupportRequest($requestId);

// Vérifier que la demande existe et appartient à l'utilisateur
if (!$request->getId() || $request->getUserId() != $_SESSION['user_id']) {
    setFlashMessage('Demande introuvable ou accès non autorisé.', 'error');
    redirect('../../view/frontoffice/support/my_requests.php');
}

// L'utilisateur peut supprimer sa demande à tout moment (même après acceptation et début de conversation)
// La suppression supprimera également tous les messages associés grâce à ON DELETE CASCADE
// et la demande disparaîtra également pour le conseiller

// Supprimer la demande (OOP) - cela supprimera aussi les messages associés grâce à ON DELETE CASCADE
$result = $request->delete();

if ($result) {
    setFlashMessage('Votre demande a été supprimée avec succès.', 'success');
    logAction("Demande $requestId supprimée par l'utilisateur " . $_SESSION['user_id'], 'info');
} else {
    setFlashMessage('Une erreur est survenue lors de la suppression de la demande.', 'error');
    logAction("Échec de suppression de la demande $requestId", 'error');
}

// Rediriger vers mes demandes
redirect('../../view/frontoffice/support/my_requests.php');

?>

