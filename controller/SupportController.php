<?php
/**
 * ============================================
 * SUPPORT CONTROLLER
 * SAFEProject - Support Request Management
 * ============================================
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/SupportRequest.php';
require_once __DIR__ . '/../model/SupportMessage.php';
require_once __DIR__ . '/usercontroller.php';

class SupportController {
    private $db;
    
    public function __construct() {
        $this->db = config::getConnexion();
    }
    
    /**
     * Find support requests by user
     */
    public function findRequestsByUser($user_id) {
        try {
            $sql = "SELECT * FROM support_requests WHERE user_id = :user_id ORDER BY date_creation DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $requests = [];
            while ($data = $stmt->fetch()) {
                $request = new SupportRequest();
                $request->hydrate($data);
                $requests[] = $request;
            }
            
            return $requests;
        } catch (PDOException $e) {
            error_log("Error finding requests by user: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Find support requests by counselor
     */
    public function findRequestsByCounselor($counselor_id) {
        try {
            $sql = "SELECT * FROM support_requests WHERE counselor_user_id = :counselor_id ORDER BY date_creation DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':counselor_id', $counselor_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $requests = [];
            while ($data = $stmt->fetch()) {
                $request = new SupportRequest();
                $request->hydrate($data);
                $requests[] = $request;
            }
            
            return $requests;
        } catch (PDOException $e) {
            error_log("Error finding requests by counselor: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Find all support requests
     */
    public function findAllRequests() {
        try {
            $sql = "SELECT * FROM support_requests ORDER BY date_creation DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $requests = [];
            while ($data = $stmt->fetch()) {
                $request = new SupportRequest();
                $request->hydrate($data);
                $requests[] = $request;
            }
            
            return $requests;
        } catch (PDOException $e) {
            error_log("Error finding all requests: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Find support requests by status
     */
    public function findRequestsByStatus($status) {
        try {
            $sql = "SELECT * FROM support_requests WHERE statut = :status ORDER BY date_creation DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            
            $requests = [];
            while ($data = $stmt->fetch()) {
                $request = new SupportRequest();
                $request->hydrate($data);
                $requests[] = $request;
            }
            
            return $requests;
        } catch (PDOException $e) {
            error_log("Error finding requests by status: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Find pending support requests
     */
    public function findPendingRequests() {
        return $this->findRequestsByStatus('en_attente');
    }
    
    /**
     * Find messages by support request
     */
    public function findMessagesByRequest($request_id) {
        try {
            $sql = "SELECT * FROM support_messages 
                    WHERE support_request_id = :request_id 
                    ORDER BY date_envoi ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $messages = [];
            while ($data = $stmt->fetch()) {
                $message = new SupportMessage();
                $message->hydrate($data);
                $messages[] = $message;
            }
            
            return $messages;
        } catch (PDOException $e) {
            error_log("Error finding messages by request: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Count unread messages for a request
     */
    public function countUnreadMessages($request_id, $exclude_sender_id) {
        try {
            $sql = "SELECT COUNT(*) as total FROM support_messages 
                    WHERE support_request_id = :request_id 
                      AND sender_id != :sender_id 
                      AND lu = FALSE";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
            $stmt->bindParam(':sender_id', $exclude_sender_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch();
            return (int) $result['total'];
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * Mark messages as read
     */
    public function markMessagesAsRead($request_id, $exclude_sender_id) {
        try {
            $sql = "UPDATE support_messages 
                    SET lu = TRUE 
                    WHERE support_request_id = :request_id 
                      AND sender_id != :sender_id 
                      AND lu = FALSE";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
            $stmt->bindParam(':sender_id', $exclude_sender_id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error marking messages as read: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Assign counselor to request
     */
    public function assignCounselor($request_id, $counselor_id, $admin_note = '') {
        try {
            $request = new SupportRequest($request_id);
            if (!$request->getId()) {
                return false;
            }
            
            $request->setCounselorUserId($counselor_id);
            $request->setStatut('assignee');
            $request->setDateAssignation(date('Y-m-d H:i:s'));
            
            // Save admin note if provided
            if (!empty($admin_note)) {
                $request->setAdminNote($admin_note);
            }
            
            return $request->save();
        } catch (Exception $e) {
            error_log("Error assigning counselor: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get available counselors
     */
    public function getAvailableCounselors() {
        try {
            $sql = "SELECT * FROM users WHERE role = 'conseilleur' AND status = 'actif' ORDER BY nom ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $counselors = [];
            while ($data = $stmt->fetch()) {
                $counselors[] = $data;
            }
            
            return $counselors;
        } catch (PDOException $e) {
            error_log("Error getting available counselors: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Delete message (with permission check)
     */
    public function deleteMessage($message_id, $user_id, $user_role) {
        try {
            $message = new SupportMessage($message_id);
            
            if (!$message->getId()) {
                return false;
            }
            
            // Permission check: only sender or admin can delete
            if ($message->getSenderId() != $user_id && $user_role !== 'admin') {
                return false;
            }
            
            return $message->delete();
        } catch (Exception $e) {
            error_log("Error deleting message: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update message (with permission check)
     */
    public function updateMessage($message_id, $new_message, $user_id, $user_role) {
        try {
            $message = new SupportMessage($message_id);
            
            if (!$message->getId()) {
                return false;
            }
            
            // Permission check: only sender can edit their own message
            if ($message->getSenderId() != $user_id) {
                return false;
            }
            
            $message->setMessage($new_message);
            return $message->save();
        } catch (Exception $e) {
            error_log("Error updating message: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cancel support request (member can cancel their own request)
     */
    public function cancelRequest($request_id, $user_id) {
        try {
            $request = new SupportRequest($request_id);
            
            if (!$request->getId()) {
                return false;
            }
            
            // Permission check: only request owner can cancel
            if ($request->getUserId() != $user_id) {
                return false;
            }
            
            // Can only cancel if not completed
            if ($request->getStatut() === 'terminee') {
                return false;
            }
            
            return $request->cancel();
        } catch (Exception $e) {
            error_log("Error canceling request: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete support request (member can delete their own request)
     */
    public function deleteRequest($request_id, $user_id, $user_role) {
        try {
            $request = new SupportRequest($request_id);
            
            if (!$request->getId()) {
                return false;
            }
            
            // Permission check: only request owner or admin can delete
            if ($request->getUserId() != $user_id && $user_role !== 'admin') {
                return false;
            }
            
            return $request->delete();
        } catch (Exception $e) {
            error_log("Error deleting request: " . $e->getMessage());
            return false;
        }
    }
}
?>
