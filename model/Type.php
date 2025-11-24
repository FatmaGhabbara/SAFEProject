<?php
class Type {
    private $conn;
    private $table_name = "types";
    
    public $id;
    public $nom;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function readAll() {
        $query = "SELECT id, nom FROM " . $this->table_name . " ORDER BY nom";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>