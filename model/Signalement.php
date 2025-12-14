<?php
class Signalement {
    private $lastError = null;
    private $conn;
    private $table_name = "signalements";
    
    private $id;
    private $titre;
    private $description;
    private $type_id;
    private $created_at;
    private $type_nom;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Getters
    public function getId() {
        return $this->id;
    }
    
    public function getTitre() {
        return $this->titre;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    public function getTypeId() {
        return $this->type_id;
    }
    
    public function getCreatedAt() {
        return $this->created_at;
    }
    
    public function getTypeNom() {
        return $this->type_nom ?? null;
    }
    
    // Setters
    public function setId($id) {
        $this->id = $id;
        return $this;
    }
    
    public function setTitre($titre) {
        $this->titre = htmlspecialchars(strip_tags($titre));
        return $this;
    }
    
    public function setDescription($description) {
        $this->description = htmlspecialchars(strip_tags($description));
        return $this;
    }
    
    public function setTypeId($type_id) {
        $this->type_id = htmlspecialchars(strip_tags($type_id));
        return $this;
    }
    
    public function setCreatedAt($created_at) {
        $this->created_at = $created_at;
        return $this;
    }
    
    public function setTypeNom($type_nom) {
        $this->type_nom = $type_nom;
        return $this;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "\n                  SET titre=:titre, description=:description, type_id=:type_id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindValue(":titre", $this->titre, PDO::PARAM_STR);
        $stmt->bindValue(":description", $this->description, PDO::PARAM_STR);
        $stmt->bindValue(":type_id", (int)$this->type_id, PDO::PARAM_INT);
        
        try {
            $ok = $stmt->execute();
            if (!$ok) {
                $this->lastError = $stmt->errorInfo();
            }
            return $ok;
        } catch (PDOException $e) {
            $this->lastError = [$e->getCode(), $e->getMessage()];
            return false;
        }
    }

    public function getLastError() {
        return $this->lastError;
    }
    
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET titre=:titre, description=:description, type_id=:type_id WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':titre', $this->titre, PDO::PARAM_STR);
        $stmt->bindValue(':description', $this->description, PDO::PARAM_STR);
        $stmt->bindValue(':type_id', (int)$this->type_id, PDO::PARAM_INT);
        $stmt->bindValue(':id', (int)$this->id, PDO::PARAM_INT);
        try {
            $ok = $stmt->execute();
            if (!$ok) {
                $this->lastError = $stmt->errorInfo();
            }
            return $ok;
        } catch (PDOException $e) {
            $this->lastError = [$e->getCode(), $e->getMessage()];
            return false;
        }
    }
    
    public function readAll() {
        $query = "SELECT s.id, s.titre, s.description, s.created_at, 
                         t.nom as type_nom 
                  FROM " . $this->table_name . " s 
                  LEFT JOIN types t ON s.type_id = t.id 
                  ORDER BY s.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function readOne() {
        $query = "SELECT s.id, s.titre, s.description, s.type_id, s.created_at,
                         t.nom as type_nom 
                  FROM " . $this->table_name . " s 
                  LEFT JOIN types t ON s.type_id = t.id 
                  WHERE s.id = ? 
                  LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id = $row['id'];
            $this->titre = $row['titre'];
            $this->description = $row['description'];
            $this->type_id = $row['type_id'];
            $this->type_nom = $row['type_nom'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }
}
?>