<?php
/**
 * ============================================
 * SUPPORT MESSAGE ENTITY - Model Class
 * SAFEProject - Pure Entity (MVC Pattern)
 * ============================================
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/SupportRequest.php';

class SupportMessage {
    // Attributes (properties matching database columns)
    private $id;
    private $support_request_id;
    private $sender_id;
    private $message;
    private $date_envoi;
    private $lu;
    
    // Related objects (lazy loaded)
    private $sender;
    private $supportRequest;
    
    // Database connection
    private $db;
    
    // Constructor
    public function __construct($id = null) {
        $this->db = getDB();
        
        if ($id !== null) {
            $this->load($id);
        }
    }
    
    // Destructor
    public function __destruct() {
        $this->db = null;
        $this->sender = null;
        $this->supportRequest = null;
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
            logAction("Error loading support message $id: " . $e->getMessage(), 'error');
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
    
    // Lazy load related objects
    public function getSender() {
        if ($this->sender === null && $this->sender_id) {
            $this->sender = new User($this->sender_id);
        }
        return $this->sender;
    }
    
    public function getUser() {
        return $this->getSender();
    }
    
    public function getSupportRequest() {
        if ($this->supportRequest === null && $this->support_request_id) {
            $this->supportRequest = new SupportRequest($this->support_request_id);
        }
        return $this->supportRequest;
    }
    
    // Setters
    public function setSupportRequestId($support_request_id) {
        $this->support_request_id = $support_request_id;
        $this->supportRequest = null; // Reset lazy loaded object
    }
    
    public function setSenderId($sender_id) {
        $this->sender_id = $sender_id;
        $this->sender = null; // Reset lazy loaded object
    }
    
    public function setMessage($message) {
        $this->message = cleanInput($message);
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
            logAction("Error saving support message: " . $e->getMessage(), 'error');
            return false;
        }
    }
    
    // Insert new message
    private function insert() {
        if (empty($this->support_request_id) || empty($this->sender_id) || empty($this->message)) {
            logAction("Cannot insert message: missing required fields", 'error');
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
            logAction("New message sent (ID: {$this->id}) for request {$this->support_request_id}", 'info');
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
        
        if ($stmt->execute()) {
            logAction("Support message {$this->id} updated", 'info');
            return true;
        }
        
        return false;
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
            
            if ($stmt->execute()) {
                logAction("Support message {$this->id} deleted", 'warning');
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            logAction("Error deleting support message: " . $e->getMessage(), 'error');
            return false;
        }
    }
    
    // Convert to array
    public function toArray() {
        return [
            'id' => $this->id,
            'support_request_id' => $this->support_request_id,
            'sender_id' => $this->sender_id,
            'message' => $this->message,
            'date_envoi' => $this->date_envoi,
            'lu' => $this->lu,
            'sender' => $this->getSender() ? $this->getSender()->toArray() : null
        ];
    }
}

?>
