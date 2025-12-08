<?php
/**
 * ============================================
 * CONTROLLER: Admin Delete User
 * SAFEProject - Delete a user account
 * ============================================
 */

session_start();

require_once '../../config.php';
require_once '../../model/User.php';

// Check admin access
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('Accès refusé.', 'error');
    redirect('../../view/frontoffice/login.php');
}

// Get user ID from URL
$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($userId === 0) {
    setFlashMessage('Utilisateur invalide.', 'error');
    redirect('../../view/backoffice/support/users_list.php');
}

// Prevent admin from deleting themselves
if ($userId === $_SESSION['user_id']) {
    setFlashMessage('Vous ne pouvez pas supprimer votre propre compte.', 'error');
    redirect('../../view/backoffice/support/users_list.php');
}

// Load user to get details for logging
$user = new User($userId);

if (!$user->getId()) {
    setFlashMessage('Utilisateur introuvable.', 'error');
    redirect('../../view/backoffice/support/users_list.php');
}

$userName = $user->getFullName();
$userEmail = $user->getEmail();

try {
    $db = getDB();
    
    // Start transaction
    $db->beginTransaction();
    
    // Delete user's messages first (using correct column name: support_request_id)
    // Get all request IDs for this user first
    $sql = "SELECT id FROM support_requests WHERE user_id = :user_id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $requestIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($requestIds)) {
        // Delete messages for these requests
        $placeholders = implode(',', array_fill(0, count($requestIds), '?'));
        $sql = "DELETE FROM support_messages WHERE support_request_id IN ($placeholders)";
        $stmt = $db->prepare($sql);
        $stmt->execute($requestIds);
    }
    
    // Delete user's support requests
    $sql = "DELETE FROM support_requests WHERE user_id = :user_id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    
    // If user is a counselor, we need to handle reassignment of their assigned requests
    if ($user->getRole() === 'counselor') {
        // Unassign requests assigned to this counselor (set counselor_user_id to NULL)
        $sql = "UPDATE support_requests SET counselor_user_id = NULL WHERE counselor_user_id = :user_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    // Delete the user account
    $sql = "DELETE FROM utilisateurs WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    
    // Commit transaction
    $db->commit();
    
    logAction("User deleted by admin: $userName ($userEmail) - ID: $userId", 'warning');
    setFlashMessage("L'utilisateur $userName a été supprimé avec succès.", 'success');
    
} catch (PDOException $e) {
    // Rollback on error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    logAction("Error deleting user $userId: " . $e->getMessage(), 'error');
    setFlashMessage('Erreur lors de la suppression: ' . $e->getMessage(), 'error');
}

// Redirect back to users list
redirect('../../view/backoffice/support/users_list.php');

?>

