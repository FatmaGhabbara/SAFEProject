<?php
/**
 * ============================================
 * SUPPORT MESSAGE ENTITY - Model Class
 * SAFEProject - Pure Entity (MVC Pattern)
 * ============================================
 */

require_once __DIR__ . '/../config.php';

class SupportMessage {
    // Attributes (properties matching database columns)
    private $id;
    private $support_request_id;
    private $sender_id;
    private $message;
    private $date_envoi;
    private $lu;
    
    // Database connection
    private $db;
    
    // Constructor
    public function __construct($id = null) {
        $this->db = config::getConnexion();
        
        if ($id !== null) {
            $this->load($id);
        }
    }
    
    // Load message from database by ID
    private function load($id) {
        try {
            $sql = "SELECT * FROM support_messages WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $data = $stmt->fetch();
            if ($data) {
                $this->hydrate($data);
            }
        } catch (PDOException $e) {
            error_log("Error loading support message $id: " . $e->getMessage());
        }
    }
    
    // Hydrate object from array
    public function hydrate($data) {
        $this->id = $data['id'] ?? null;
        $this->support_request_id = $data['support_request_id'] ?? null;
        $this->sender_id = $data['sender_id'] ?? null;
        $this->message = $data['message'] ?? null;
        $this->date_envoi = $data['date_envoi'] ?? null;
        $this->lu = $data['lu'] ?? false;
    }
    
    // Getters
    public function getId() {
        return $this->id;
    }
    
    public function getSupportRequestId() {
        return $this->support_request_id;
    }
    
    public function getSenderId() {
        return $this->sender_id;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    public function getDateEnvoi() {
        return $this->date_envoi;
    }
    
    public function getLu() {
        return $this->lu;
    }
    
    public function isRead() {
        return (bool) $this->lu;
    }
    
    public function getUserId() {
        return $this->sender_id;
    }
    
    // Setters
    public function setSupportRequestId($support_request_id) {
        $this->support_request_id = $support_request_id;
    }
    
    public function setSenderId($sender_id) {
        $this->sender_id = $sender_id;
    }
    
    public function setMessage($message) {
        $this->message = htmlspecialchars(trim($message), ENT_QUOTES, 'UTF-8');
    }
    
    public function setLu($lu) {
        $this->lu = (bool) $lu;
    }
    
    // Save to database (INSERT or UPDATE)
    public function save() {
        try {
            if ($this->id === null) {
                return $this->insert();
            } else {
                return $this->update();
            }
        } catch (PDOException $e) {
            error_log("Error saving support message: " . $e->getMessage());
            return false;
        }
    }
    
    // Insert new message
    private function insert() {
        if (empty($this->support_request_id) || empty($this->sender_id) || empty($this->message)) {
            error_log("Cannot insert message: missing required fields");
            return false;
        }
        
        $sql = "INSERT INTO support_messages (support_request_id, sender_id, message, date_envoi, lu) 
                VALUES (:support_request_id, :sender_id, :message, NOW(), :lu)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':support_request_id', $this->support_request_id, PDO::PARAM_INT);
        $stmt->bindParam(':sender_id', $this->sender_id, PDO::PARAM_INT);
        $stmt->bindParam(':message', $this->message);
        $stmt->bindParam(':lu', $this->lu, PDO::PARAM_BOOL);
        
        if ($stmt->execute()) {
            $this->id = $this->db->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Update existing message
    private function update() {
        $sql = "UPDATE support_messages 
                SET message = :message, lu = :lu
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':message', $this->message);
        $stmt->bindParam(':lu', $this->lu, PDO::PARAM_BOOL);
        
        return $stmt->execute();
    }
    
    // Delete message
    public function delete() {
        try {
            if ($this->id === null) {
                return false;
            }
            
            $sql = "DELETE FROM support_messages WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting support message: " . $e->getMessage());
            return false;
        }
    }
    
    // Mark message as read
    public function markAsRead() {
        $this->lu = true;
        return $this->update();
    }
}
?>
