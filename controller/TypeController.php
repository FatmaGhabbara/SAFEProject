<?php
class TypeController {
    private $type;
    private $db;
    private $lastError = null;
    
    public function __construct($db) {
        $this->db = $db;
        $this->type = new Type($db);  // ← "Type" pas "TypeModal"
    }
    
    public function getAllTypes() {
        try {
            $stmt = $this->type->readAll();
            $types = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $types[] = $row;
            }
            return $types;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            error_log('TypeController::getAllTypes failed: ' . $e->getMessage());
            return [];
        }
    }

    public function getLastError() {
        return $this->lastError;
    }
    
    public function getTypeById($id) {
        $this->type->setId($id);
        
        if($this->type->readOne()) {
            return [
                'id' => $this->type->getId(),
                'nom' => $this->type->getNom(),
                'description' => $this->type->getDescription()
            ];
        }
        return null;
    }
    
    public function createType($nom, $description = '') {
        $nom = trim($nom);
        $this->type->setNom($nom);
        $this->type->setDescription($description);

        // Vérifier si un type identique existe déjà (insensible à la casse)
        if ($this->typeExists($nom)) {
            return ['success' => false, 'message' => '❌ Ce type existe déjà.'];
        }
        if ($this->type->create()) {
            return ['success' => true, 'message' => "✅ Type ajouté avec succès !"];
        }
        
        return ['success' => false, 'message' => "❌ Erreur lors de l'ajout du type"];
    }
    
    public function deleteTypeWithCascade($id) {
        try {
            $this->db->beginTransaction();
            
            $countQuery = "SELECT COUNT(*) as count FROM signalements WHERE type_id = :id";
            $countStmt = $this->db->prepare($countQuery);
            $countStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $countStmt->execute();
            $signalementsCount = (int) $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $deleteSignalementsQuery = "DELETE FROM signalements WHERE type_id = :id";
            $deleteSignalementsStmt = $this->db->prepare($deleteSignalementsQuery);
            $deleteSignalementsStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $deleteSignalementsStmt->execute();
            
            $deleteTypeQuery = "DELETE FROM types WHERE id = :id";
            $deleteTypeStmt = $this->db->prepare($deleteTypeQuery);
            $deleteTypeStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $deleteTypeStmt->execute();
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => $signalementsCount > 0
                    ? "✅ Type supprimé et {$signalementsCount} signalement(s) associé(s) supprimé(s)."
                    : "✅ Type supprimé avec succès !"
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => "❌ Erreur lors de la suppression : " . $e->getMessage()];
        }
    }

    public function typeExists($nom) {
        $query = "SELECT COUNT(*) as count FROM types WHERE LOWER(nom) = LOWER(:nom)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nom', $nom);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($res && $res['count'] > 0);
    }

    public function updateType($id, $nom, $description = '') {
        $id = intval($id);
        $nom = htmlspecialchars(strip_tags(trim($nom)));
        $description = htmlspecialchars(strip_tags(trim($description)));
        // Vérifier qu'aucun autre type avec le même nom (insensible à la casse) n'existe
        $dupQuery = "SELECT COUNT(*) as count FROM types WHERE LOWER(nom) = LOWER(:nom) AND id != :id";
        $dupStmt = $this->db->prepare($dupQuery);
        $dupStmt->bindParam(':nom', $nom);
        $dupStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $dupStmt->execute();
        $dupRes = $dupStmt->fetch(PDO::FETCH_ASSOC);
        if ($dupRes && $dupRes['count'] > 0) {
            return ['success' => false, 'message' => '❌ Un autre type porte déjà ce nom.'];
        }

        // Mettre à jour
        $query = "UPDATE types SET nom = :nom, description = :description WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => '✅ Type mis à jour avec succès !'];
        }
        return ['success' => false, 'message' => '❌ Erreur lors de la mise à jour du type'];
    }
}
?>