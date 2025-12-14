<?php
/**
 * Controller for counselor to update/edit their own support messages
 */

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/SupportMessage.php';
require_once __DIR__ . '/../../model/SupportRequest.php';

// Check if user is logged in and is a counselor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'conseilleur') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

// Get data from POST
$messageId = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;
$newContent = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validation
if ($messageId === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de message invalide']);
    exit();
}

if (empty($newContent)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le message ne peut pas être vide']);
    exit();
}

try {
    // Load the message
    $message = new SupportMessage($messageId);
    
    if (!$message->getId()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Message non trouvé']);
        exit();
    }
    
    // Verify that the message belongs to the counselor
    if ($message->getUserId() != $_SESSION['user_id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez modifier que vos propres messages']);
        exit();
    }
    
    // Get the support request to verify counselor assignment
    $supportRequest = new SupportRequest($message->getSupportRequestId());
    
    if (!$supportRequest->getId()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Demande de support non trouvée']);
        exit();
    }
    
    // Verify that the counselor is assigned to this request
    if ($supportRequest->getCounselorId() != $_SESSION['user_id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Vous n\'êtes pas assigné à cette demande']);
        exit();
    }
    
    // Update the message
    $message->setMessage($newContent);
    
    if ($message->save()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Message modifié avec succès',
            'new_content' => htmlspecialchars($newContent)
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Erreur lors de la modification du message'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error updating counselor message: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Une erreur est survenue lors de la modification'
    ]);
}
?>
