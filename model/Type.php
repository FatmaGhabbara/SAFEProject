<?php
class Type {
    private $conn;
    private $table_name = "types";
    
    private $id;
    private $nom;
    private $description;
    private $hasDescriptionColumn;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->hasDescriptionColumn = $this->ensureDescriptionColumn();
    }
    
    // Getters
    public function getId() {
        return $this->id;
    }
    
    public function getNom() {
        return $this->nom;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    // Setters
    public function setId($id) {
        $this->id = $id;
        return $this;
    }
    
    public function setNom($nom) {
        $this->nom = htmlspecialchars(strip_tags($nom));
        return $this;
    }
    
    public function setDescription($description) {
        $this->description = htmlspecialchars(strip_tags($description));
        return $this;
    }
    
    public function readAll() {
        $query = $this->hasDescriptionColumn
            ? "SELECT id, nom, description FROM " . $this->table_name . " ORDER BY nom"
            : "SELECT id, nom FROM " . $this->table_name . " ORDER BY nom";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function readOne() {
        $query = $this->hasDescriptionColumn
            ? "SELECT id, nom, description FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1"
            : "SELECT id, nom FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id = $row['id'];
            $this->nom = $row['nom'];
            if ($this->hasDescriptionColumn) {
                $this->description = $row['description'] ?? null;
            }
            return true;
        }
        return false;
    }
    
    public function create() {
        $query = $this->hasDescriptionColumn
            ? "INSERT INTO " . $this->table_name . " (nom, description) VALUES (:nom, :description)"
            : "INSERT INTO " . $this->table_name . " (nom) VALUES (:nom)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom', $this->nom);
        
        if ($this->hasDescriptionColumn) {
            $stmt->bindParam(':description', $this->description);
        }
        
        return $stmt->execute();
    }
    
    private function ensureDescriptionColumn() {
        try {
            $checkStmt = $this->conn->query("SHOW COLUMNS FROM {$this->table_name} LIKE 'description'");
            $exists = $checkStmt && $checkStmt->fetch();
            
            if (!$exists) {
                $this->conn->exec("ALTER TABLE {$this->table_name} ADD description TEXT NULL");
            }
            return true;
        } catch (Exception $e) {
            // In case of restricted SQL rights, continue without the column
            return false;
        }
    }
}
?>