<?php
/**
 * ============================================
 * SUPPORT REQUEST ENTITY - Model Class
 * SAFEProject - Pure Entity (MVC Pattern)
 * ============================================
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/User.php';

class SupportRequest {
    // Attributes (properties matching database columns)
    private $id;
    private $user_id;
    private $counselor_user_id; // Changed from counselor_id to counselor_user_id (references utilisateurs.id)
    private $titre;
    private $description;
    private $urgence;
    private $statut;
    private $date_creation;
    private $date_assignation;
    private $date_resolution;
    private $notes_admin;
    
    // Related objects (lazy loaded)
    private $user;
    
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
        $this->user = null;
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
            logAction("Error loading support request $id: " . $e->getMessage(), 'error');
        }
    }
    
    // Hydrate object from array
    public function hydrate($data) {
        $this->id = $data['id'] ?? null;
        $this->user_id = $data['user_id'] ?? null;
        // Support both old and new column names for migration
        $this->counselor_user_id = $data['counselor_user_id'] ?? $data['counselor_id'] ?? null;
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
    
    // Lazy load related objects
    public function getUser() {
        if ($this->user === null && $this->user_id) {
            $this->user = new User($this->user_id);
        }
        return $this->user;
    }
    
    public function getCounselor() {
        if ($this->counselor_user_id) {
            return new User($this->counselor_user_id);
        }
        return null;
    }
    
    public function getMessages() {
        // Use helper function in controller instead
        return [];
    }
    
    // Setters
    public function setUserId($user_id) {
        $this->user_id = $user_id;
        $this->user = null; // Reset lazy loaded object
    }
    
    public function setCounselorUserId($counselor_user_id) {
        $this->counselor_user_id = $counselor_user_id ? intval($counselor_user_id) : null;
    }
    
    /**
     * @deprecated Use setCounselorUserId() instead. This method is kept for backward compatibility.
     */
    public function setCounselorId($counselor_id) {
        $this->counselor_user_id = $counselor_id ? intval($counselor_id) : null;
    }
    
    public function setTitre($titre) {
        $this->titre = cleanInput($titre);
    }
    
    public function setDescription($description) {
        $this->description = cleanInput($description);
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
        $this->notes_admin = cleanInput($notes);
    }
    
    // Complete the request (mark as terminee)
    public function complete($notes = null) {
        if (empty($this->id)) {
            logAction("Cannot complete request: no ID set", 'error');
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
                logAction("Support request {$this->id} marked as completed", 'info');
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            logAction("Error completing request: " . $e->getMessage(), 'error');
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
            logAction("Error saving support request: " . $e->getMessage(), 'error');
            return false;
        }
    }
    
    // Insert new support request
    private function insert() {
        if (empty($this->user_id) || empty($this->titre) || empty($this->description)) {
            logAction("Cannot insert request: missing required fields", 'error');
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
            logAction("New support request created (ID: {$this->id}) by user {$this->user_id}", 'info');
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
        
        // Handle NULL counselor_user_id properly
        if ($this->counselor_user_id === null) {
            $stmt->bindValue(':counselor_user_id', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':counselor_user_id', $this->counselor_user_id, PDO::PARAM_INT);
        }
        
        // Handle NULL date_assignation properly
        if ($this->date_assignation === null || $this->date_assignation === '') {
            $stmt->bindValue(':date_assignation', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':date_assignation', $this->date_assignation);
        }
        
        // Handle NULL date_resolution properly
        if ($this->date_resolution === null || $this->date_resolution === '') {
            $stmt->bindValue(':date_resolution', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':date_resolution', $this->date_resolution);
        }
        
        // Handle NULL notes_admin properly
        if ($this->notes_admin === null || $this->notes_admin === '') {
            $stmt->bindValue(':notes_admin', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':notes_admin', $this->notes_admin);
        }
        
        if ($stmt->execute()) {
            logAction("Support request {$this->id} updated - counselor_user_id: " . ($this->counselor_user_id ?? 'NULL') . ", statut: {$this->statut}", 'info');
            return true;
        } else {
            $errorInfo = $stmt->errorInfo();
            logAction("Error updating support request {$this->id}: " . print_r($errorInfo, true), 'error');
        }
        
        return false;
    }
    
    // Cancel the request (mark as annulee)
    public function cancel() {
        if (empty($this->id)) {
            logAction("Cannot cancel request: no ID set", 'error');
            return false;
        }
        
        try {
            $sql = "UPDATE support_requests 
                    SET statut = 'annulee'
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $this->statut = 'annulee';
                logAction("Support request {$this->id} marked as cancelled", 'info');
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            logAction("Error cancelling request: " . $e->getMessage(), 'error');
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
            
            if ($stmt->execute()) {
                logAction("Support request {$this->id} deleted", 'warning');
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            logAction("Error deleting support request: " . $e->getMessage(), 'error');
            return false;
        }
    }
    
    // Helper methods (simple checks only)
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
    
    // Convert to array
    public function toArray() {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'counselor_user_id' => $this->counselor_user_id,
            'titre' => $this->titre,
            'description' => $this->description,
            'urgence' => $this->urgence,
            'statut' => $this->statut,
            'date_creation' => $this->date_creation,
            'date_assignation' => $this->date_assignation,
            'date_resolution' => $this->date_resolution,
            'notes_admin' => $this->notes_admin,
            'user' => $this->getUser() ? $this->getUser()->toArray() : null
        ];
    }
    
    /**
     * Generate PDF for support request
     * @return string PDF content as string
     */
    public function generatePDF() {
        // Get messages for this request
        $messages = findMessagesByRequest($this->id);
        
        // Get user and counselor info
        $user = $this->getUser();
        $counselor = $this->getCounselor();
        
        // Start output buffering
        ob_start();
        
        // Set content type to PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="support_request_' . $this->id . '.pdf"');
        
        // Create simple HTML to PDF conversion
        $html = $this->generatePDFHTML($user, $counselor, $messages);
        
        // Use a simple PDF generation method
        return $this->HTMLtoPDF($html);
    }
    
    /**
     * Generate HTML content for PDF
     */
    private function generatePDFHTML($user, $counselor, $messages) {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Demande de Support #' . $this->id . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.4; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .section { margin-bottom: 25px; }
        .info-row { margin-bottom: 10px; }
        .label { font-weight: bold; width: 120px; display: inline-block; }
        .message { margin-bottom: 15px; padding: 10px; border-left: 3px solid #ddd; background: #f9f9f9; }
        .message-sent { border-left-color: #007bff; }
        .message-received { border-left-color: #28a745; }
        .sender { font-weight: bold; margin-bottom: 5px; }
        .timestamp { color: #666; font-size: 0.9em; margin-bottom: 8px; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SAFEProject - Demande de Support Psychologique</h1>
        <h2>Résumé de la Demande #' . $this->id . '</h2>
        <p>Généré le ' . date('d/m/Y H:i') . '</p>
    </div>
    
    <div class="section">
        <h3>Informations de la Demande</h3>
        <div class="info-row">
            <span class="label">Titre:</span> ' . htmlspecialchars($this->titre) . '
        </div>
        <div class="info-row">
            <span class="label">Description:</span> ' . nl2br(htmlspecialchars($this->description)) . '
        </div>
        <div class="info-row">
            <span class="label">Urgence:</span> ' . htmlspecialchars($this->urgence) . '
        </div>
        <div class="info-row">
            <span class="label">Statut:</span> ' . htmlspecialchars($this->statut) . '
        </div>
        <div class="info-row">
            <span class="label">Date création:</span> ' . date('d/m/Y H:i', strtotime($this->date_creation)) . '
        </div>';
        
        if ($this->date_assignation) {
            $html .= '<div class="info-row">
                <span class="label">Date assignation:</span> ' . date('d/m/Y H:i', strtotime($this->date_assignation)) . '
            </div>';
        }
        
        if ($this->date_resolution) {
            $html .= '<div class="info-row">
                <span class="label">Date résolution:</span> ' . date('d/m/Y H:i', strtotime($this->date_resolution)) . '
            </div>';
        }
        
        $html .= '</div>';
        
        // User information
        if ($user) {
            $html .= '<div class="section">
                <h3>Informations du Demandeur</h3>
                <div class="info-row">
                    <span class="label">Nom:</span> ' . htmlspecialchars($user->getNom() . ' ' . $user->getPrenom()) . '
                </div>
                <div class="info-row">
                    <span class="label">Email:</span> ' . htmlspecialchars($user->getEmail()) . '
                </div>
            </div>';
        }
        
        // Counselor information
        if ($counselor) {
            $html .= '<div class="section">
                <h3>Informations du Conseiller</h3>
                <div class="info-row">
                    <span class="label">Nom:</span> ' . htmlspecialchars($counselor->getNom() . ' ' . $counselor->getPrenom()) . '
                </div>
                <div class="info-row">
                    <span class="label">Email:</span> ' . htmlspecialchars($counselor->getEmail()) . '
                </div>
            </div>';
        }
        
        // Messages
        $html .= '<div class="section">
            <h3>Historique des Messages</h3>';
        
        foreach ($messages as $message) {
            $sender = $message->getUser();
            $isSent = ($message->getSenderId() == $this->user_id);
            $messageClass = $isSent ? 'message-sent' : 'message-received';
            
            $html .= '<div class="message ' . $messageClass . '">
                <div class="sender">' . htmlspecialchars($sender ? $sender->getNom() . ' ' . $sender->getPrenom() : 'Utilisateur inconnu') . '</div>
                <div class="timestamp">' . date('d/m/Y H:i', strtotime($message->getDateEnvoi())) . '</div>
                <div class="content">' . nl2br(htmlspecialchars($message->getMessage())) . '</div>
            </div>';
        }
        
        $html .= '</div>
        
    <div class="footer">
        <p>Ce document est un résumé officiel de votre demande de support psychologique.</p>
        <p>SAFEProject - Module Support Psychologique</p>
        <p>Confidentiel et protégé par le secret professionnel.</p>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Convert HTML to PDF using simple method
     */
    private function HTMLtoPDF($html) {
        // For now, we'll return the HTML as a downloadable file
        // In a real implementation, you would use a proper PDF library
        // This is a simplified version that creates a downloadable HTML file
        
        // Create a simple PDF-like format
        $pdf_content = "%PDF-1.4\n";
        $pdf_content .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $pdf_content .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $pdf_content .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /Resources << /Font << /F1 4 0 R >> >> /MediaBox [0 0 612 792] /Contents 5 0 R >>\nendobj\n";
        $pdf_content .= "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $pdf_content .= "5 0 obj\n<< /Length " . strlen($html) . " >>\nstream\n" . $html . "\nendstream\nendobj\n";
        $pdf_content .= "xref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000054 00000 n \n0000000110 00000 n \n0000000260 00000 n \n0000000330 00000 n \n";
        $pdf_content .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n" . strlen($pdf_content) . "\n%%EOF";
        
        // For simplicity, return the HTML content that can be saved as a file
        // The browser will handle it as a download
        return $html;
    }
}

?>
