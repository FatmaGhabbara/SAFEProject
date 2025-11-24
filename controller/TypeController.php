<?php
class TypeController {
    private $type;
    
    public function __construct($db) {
        $this->type = new Type($db);  // ← "Type" pas "TypeModal"
    }
    
    public function getAllTypes() {
        $stmt = $this->type->readAll();
        $types = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $types[] = $row;
        }
        
        return $types;
    }
    
    public function getTypeById($id) {
        $this->type->id = $id;
        
        if($this->type->readOne()) {
            return [
                'id' => $id,
                'nom' => $this->type->nom
            ];
        }
        return null;
    }
}
?>