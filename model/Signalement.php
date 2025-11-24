<?php
class Signalement {
    private $conn;
    private $table_name = "signalements";
    
    public $id;
    public $titre;
    public $description;
    public $type_id;
    public $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET titre=:titre, description=:description, type_id=:type_id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->titre = htmlspecialchars(strip_tags($this->titre));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->type_id = htmlspecialchars(strip_tags($this->type_id));
        
        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":type_id", $this->type_id);
        
        return $stmt->execute();
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
    $query = "SELECT s.titre, s.description, s.type_id, s.created_at,
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