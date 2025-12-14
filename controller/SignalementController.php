<?php
class SignalementController {
    private $signalement;
    private $typeController;
      private $db;
    
     public function __construct($db) {
        $this->db = $db;  // ← SAUVEGARDER la connexion
        $this->signalement = new Signalement($db);
        $this->typeController = new TypeController($db);
    }
    
    public function createSignalement($data) {
        // Validation des données
        $errors = $this->validateSignalement($data);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Assigner les données au modèle en utilisant les setters
        $this->signalement->setTitre($data['titre']);
        $this->signalement->setDescription($data['description']);
        $this->signalement->setTypeId($data['type_id']);
        
        // Créer le signalement
        if($this->signalement->create()) {
            return ['success' => true, 'message' => 'Signalement créé avec succès'];
        } else {
            $lastErr = $this->signalement->getLastError();
            // Log a useful message server-side for debugging
            if ($lastErr) {
                // $lastErr could be either PDO::errorInfo() array or [code, message]
                $errMsg = is_array($lastErr) ? implode(' | ', $lastErr) : (string)$lastErr;
                error_log('Signalement::create SQL Error: ' . $errMsg);
            }
            // Log some input data for debugging (non-sensitive)
            $title = isset($data['titre']) ? substr($data['titre'], 0, 100) : '(vide)';
            $typeId = isset($data['type_id']) ? intval($data['type_id']) : '(vide)';
            $descSnippet = isset($data['description']) ? substr($data['description'], 0, 100) : '(vide)';
            error_log("Signalement::create failed for titre='" . $title . "', type_id=" . $typeId . ", desc_len=" . strlen($descSnippet));
            // Return friendly message
            return ['success' => false, 'message' => 'Erreur lors de la création du signalement. Veuillez réessayer ou contacter l\'administrateur.'];
        }
    }
    
    public function getAllSignalements() {
        $stmt = $this->signalement->readAll();
        $signalements = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $signalements[] = $row;
        }
        
        return $signalements;
    }
    
    public function getSignalementById($id) {
        $this->signalement->setId($id);
        
        if($this->signalement->readOne()) {
            return [
                'id' => $this->signalement->getId(),
                'titre' => $this->signalement->getTitre(),
                'description' => $this->signalement->getDescription(),
                'type_id' => $this->signalement->getTypeId(),
                'type_nom' => $this->signalement->getTypeNom(),
                'created_at' => $this->signalement->getCreatedAt()
            ];
        }
        return null;
    }
    
    public function getTypesForForm() {
        return $this->typeController->getAllTypes();
    }
    
    private function validateSignalement($data) {
        $errors = [];
        
        if (!isset($data['titre']) || trim($data['titre']) === '') {
            $errors[] = "Le titre est obligatoire";
        }
        else {
            $len = mb_strlen(trim($data['titre']));
            if ($len < 3) $errors[] = "Le titre doit contenir au moins 3 caractères";
            if ($len > 200) $errors[] = "Le titre ne doit pas dépasser 200 caractères";
        }
        
        if (!isset($data['type_id']) || trim($data['type_id']) === '') {
            $errors[] = "Le type est obligatoire";
        }
        else {
            if (!is_numeric($data['type_id'])) {
                $errors[] = "Type invalide";
            }
        }
        
        if (!isset($data['description']) || trim($data['description']) === '') {
            $errors[] = "La description est obligatoire";
        }
        else {
            $descLen = mb_strlen(trim($data['description']));
            if ($descLen < 10) $errors[] = "La description doit contenir au moins 10 caractères";
            if ($descLen > 2000) $errors[] = "La description ne doit pas dépasser 2000 caractères";
        }
        // Vérifier l'existence du type si fourni
        if (!empty($data['type_id'])) {
            $typeExists = $this->typeController->getTypeById($data['type_id']);
            if (!$typeExists) {
                $errors[] = "Le type sélectionné est invalide";
            }
        }
        
        return $errors;
    }
    public function deleteSignalement($id) {
    // Vérifier d'abord si le signalement existe
    $signalement = $this->getSignalementById($id);
    if (!$signalement) {
        return ['success' => false, 'message' => 'Signalement non trouvé'];
    }
    
    // Supprimer le signalement - CORRECTION ICI
    $query = "DELETE FROM signalements WHERE id = :id";
    $stmt = $this->db->prepare($query);  // ← Utiliser $this->db au lieu de $this->signalement->conn
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if($stmt->execute()) {
        return ['success' => true, 'message' => 'Signalement supprimé avec succès'];
    } else {
        return ['success' => false, 'message' => 'Erreur lors de la suppression'];
    }
}
    public function updateSignalement($id, $data) {
        $errors = $this->validateSignalement($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->signalement->setId($id);
        $this->signalement->setTitre($data['titre']);
        $this->signalement->setDescription($data['description']);
        $this->signalement->setTypeId($data['type_id']);

        if ($this->signalement->update()) {
            return ['success' => true, 'message' => 'Signalement mis à jour avec succès'];
        }
        $lastErr = $this->signalement->getLastError();
        if ($lastErr) {
            error_log('Signalement::update SQL Error: ' . (is_array($lastErr) ? implode('|', $lastErr) : $lastErr));
        }
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour du signalement'];
    }
    public function getLastError() {
        return $this->signalement->getLastError();
    }
public function searchSignalements($keyword) {
    $query = "SELECT s.id, s.titre, s.description, s.created_at, 
                     t.nom as type_nom 
              FROM signalements s 
              LEFT JOIN types t ON s.type_id = t.id 
              WHERE s.titre LIKE :keyword OR s.description LIKE :keyword 
              ORDER BY s.created_at DESC";
    
    $stmt = $this->db->prepare($query);
    $searchKeyword = "%{$keyword}%";
    $stmt->bindParam(':keyword', $searchKeyword);
    $stmt->execute();
    
    $signalements = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $signalements[] = $row;
    }
    
    return $signalements;
}
}
?>