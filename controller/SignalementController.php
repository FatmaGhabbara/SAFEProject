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
        
        // Assigner les données au modèle
        $this->signalement->titre = $data['titre'];
        $this->signalement->description = $data['description'];
        $this->signalement->type_id = $data['type_id'];
        
        // Créer le signalement
        if($this->signalement->create()) {
            return ['success' => true, 'message' => 'Signalement créé avec succès'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la création du signalement'];
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
        $this->signalement->id = $id;
        
        if($this->signalement->readOne()) {
            return [
                'id' => $id,
                'titre' => $this->signalement->titre,
                'description' => $this->signalement->description,
                'type_id' => $this->signalement->type_id,
                'type_nom' => $this->signalement->type_nom,
                'created_at' => $this->signalement->created_at
            ];
        }
        return null;
    }
    
    public function getTypesForForm() {
        return $this->typeController->getAllTypes();
    }
    
    private function validateSignalement($data) {
        $errors = [];
        
        if (empty($data['titre'])) {
            $errors[] = "Le titre est obligatoire";
        }
        
        if (empty($data['type_id'])) {
            $errors[] = "Le type est obligatoire";
        }
        
        if (empty($data['description'])) {
            $errors[] = "La description est obligatoire";
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