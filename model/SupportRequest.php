<?php
/**
 * ============================================
 * SUPPORT REQUEST ENTITY - Model Class
 * SAFEProject - Pure Entity (MVC Pattern)
 * ============================================
 */

require_once __DIR__ . '/../config.php';

class SupportRequest {
    // Attributes (properties matching database columns)
    private $id;
    private $user_id;
    private $counselor_user_id;
    private $titre;
    private $description;
    private $urgence;
    private $statut;
    private $date_creation;
    private $date_assignation;
    private $date_resolution;
    private $notes_admin;
    
    // Database connection
    private $db;
    
    // Constructor
    public function __construct($id = null) {
        $this->db = config::getConnexion();
        
        if ($id !== null) {
            $this->load($id);
        }
    }
    
    // Load support request from database by ID
    private function load($id) {
        try {
            $sql = "SELECT * FROM support_requests WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $data = $stmt->fetch();
            if ($data) {
                $this->hydrate($data);
            }
        } catch (PDOException $e) {
            error_log("Error loading support request $id: " . $e->getMessage());
        }
    }
    
    // Hydrate object from array
    public function hydrate($data) {
        $this->id = $data['id'] ?? null;
        $this->user_id = $data['user_id'] ?? null;
        $this->counselor_user_id = $data['counselor_user_id'] ?? null;
        $this->titre = $data['titre'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->urgence = $data['urgence'] ?? 'moyenne';
        $this->statut = $data['statut'] ?? 'en_attente';
        $this->date_creation = $data['date_creation'] ?? null;
        $this->date_assignation = $data['date_assignation'] ?? null;
        $this->date_resolution = $data['date_resolution'] ?? null;
        $this->notes_admin = $data['notes_admin'] ?? null;
    }
    
    // Getters
    public function getId() {
        return $this->id;
    }
    
    public function getUserId() {
        return $this->user_id;
    }
    
    public function getCounselorId() {
        return $this->counselor_user_id;
    }
    
    public function getTitre() {
        return $this->titre;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    public function getUrgence() {
        return $this->urgence;
    }
    
    public function getStatut() {
        return $this->statut;
    }
    
    public function getDateCreation() {
        return $this->date_creation;
    }
    
    public function getDateAssignation() {
        return $this->date_assignation;
    }
    
    public function getDateResolution() {
        return $this->date_resolution;
    }
    
    public function getNotesAdmin() {
        return $this->notes_admin;
    }
    
    public function getCreatedAt() {
        return $this->date_creation;
    }
    
    // Setters
    public function setUserId($user_id) {
        $this->user_id = $user_id;
    }
    
    public function setCounselorUserId($counselor_user_id) {
        $this->counselor_user_id = $counselor_user_id ? intval($counselor_user_id) : null;
    }
    
    public function setTitre($titre) {
        $this->titre = htmlspecialchars(trim($titre), ENT_QUOTES, 'UTF-8');
    }
    
    public function setDescription($description) {
        $this->description = htmlspecialchars(trim($description), ENT_QUOTES, 'UTF-8');
    }
    
    public function setUrgence($urgence) {
        $validUrgences = ['basse', 'moyenne', 'haute'];
        $this->urgence = in_array($urgence, $validUrgences) ? $urgence : 'moyenne';
    }
    
    public function setStatut($statut) {
        $validStatuts = ['en_attente', 'assignee', 'en_cours', 'terminee', 'annulee'];
        $this->statut = in_array($statut, $validStatuts) ? $statut : 'en_attente';
    }
    
    public function setDateAssignation($date) {
        $this->date_assignation = $date;
    }
    
    public function setDateResolution($date) {
        $this->date_resolution = $date;
    }
    
    public function setNotesAdmin($notes) {
        $this->notes_admin = htmlspecialchars(trim($notes), ENT_QUOTES, 'UTF-8');
    }
    
    // Complete the request (mark as terminee)
    public function complete($notes = null) {
        if (empty($this->id)) {
            error_log("Cannot complete request: no ID set");
            return false;
        }
        
        try {
            $sql = "UPDATE support_requests 
                    SET statut = 'terminee', 
                        date_resolution = NOW(),
                        notes_admin = :notes
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindParam(':notes', $notes);
            
            if ($stmt->execute()) {
                $this->statut = 'terminee';
                $this->date_resolution = date('Y-m-d H:i:s');
                if ($notes) {
                    $this->notes_admin = $notes;
                }
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error completing request: " . $e->getMessage());
            return false;
        }
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
            error_log("Error saving support request: " . $e->getMessage());
            return false;
        }
    }
    
    // Insert new support request
    private function insert() {
        if (empty($this->user_id) || empty($this->titre) || empty($this->description)) {
            error_log("Cannot insert request: missing required fields");
            return false;
        }
        
        $sql = "INSERT INTO support_requests (user_id, titre, description, urgence, statut, date_creation) 
                VALUES (:user_id, :titre, :description, :urgence, :statut, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
        $stmt->bindParam(':titre', $this->titre);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':urgence', $this->urgence);
        $stmt->bindParam(':statut', $this->statut);
        
        if ($stmt->execute()) {
            $this->id = $this->db->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Update existing support request
    private function update() {
        $sql = "UPDATE support_requests 
                SET titre = :titre, description = :description, urgence = :urgence, 
                    statut = :statut, counselor_user_id = :counselor_user_id, 
                    date_assignation = :date_assignation, date_resolution = :date_resolution, 
                    notes_admin = :notes_admin
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':titre', $this->titre);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':urgence', $this->urgence);
        $stmt->bindParam(':statut', $this->statut);
        
        if ($this->counselor_user_id === null) {
            $stmt->bindValue(':counselor_user_id', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':counselor_user_id', $this->counselor_user_id, PDO::PARAM_INT);
        }
        
        if ($this->date_assignation === null || $this->date_assignation === '') {
            $stmt->bindValue(':date_assignation', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':date_assignation', $this->date_assignation);
        }
        
        if ($this->date_resolution === null || $this->date_resolution === '') {
            $stmt->bindValue(':date_resolution', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':date_resolution', $this->date_resolution);
        }
        
        if ($this->notes_admin === null || $this->notes_admin === '') {
            $stmt->bindValue(':notes_admin', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':notes_admin', $this->notes_admin);
        }
        
        return $stmt->execute();
    }
    
    // Cancel the request (mark as annulee)
    public function cancel() {
        if (empty($this->id)) {
            return false;
        }
        
        try {
            $sql = "UPDATE support_requests SET statut = 'annulee' WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $this->statut = 'annulee';
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error cancelling request: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete support request
    public function delete() {
        try {
            if ($this->id === null) {
                return false;
            }
            
            $sql = "DELETE FROM support_requests WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting support request: " . $e->getMessage());
            return false;
        }
    }
    
    // Helper methods
    public function isPending() {
        return $this->statut === 'en_attente';
    }
    
    public function isAssigned() {
        return $this->counselor_user_id !== null;
    }
    
    public function isCompleted() {
        return $this->statut === 'terminee';
    }
    
    public function isUrgent() {
        return $this->urgence === 'haute';
    }
}
?>
