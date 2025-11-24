<?php
require_once __DIR__ . '/../config.php';

class User {
    private $id = null;
    private $fullname = '';
    private $email = '';
    private $password = '';
    private $role = 'membre'; // Rôle par défaut
    private $status = 'en attente';
    private $pdo;

    public function __construct() {
        $this->pdo = Config::connect();
    }

    // GETTERS
    public function getId() { return $this->id; }
    public function getFullname() { return $this->fullname; }
    public function getEmail() { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getRole() { return $this->role; }
    public function getStatus() { return $this->status; }

    // SETTERS
    public function setFullname($fullname) { $this->fullname = $fullname; }
    public function setEmail($email) { $this->email = $email; }
    public function setPassword($password) { 
        $this->password = password_hash($password, PASSWORD_BCRYPT); 
    }
    
    // ✅ CORRECTION : Seulement 'membre' ou 'conseilleur' pour les utilisateurs normaux
    public function setRole($role) { 
        $allowedRoles = ['membre', 'conseilleur']; // Admin se fait manuellement
        if (in_array($role, $allowedRoles)) {
            $this->role = $role;
        } else {
            $this->role = 'membre'; // Défaut sécurisé
        }
    }
    
    public function setStatus($status) { 
        $this->status = $status; 
    }

    // Hydratation
    public function hydrate($data) {
        $this->id = $data['id'] ?? null;
        $this->fullname = $data['fullname'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->password = $data['PASSWORD'] ?? '';
        $this->role = $data['role'] ?? 'membre';
        $this->status = $data['status'] ?? 'en attente';
    }

    // ... le reste des méthodes CRUD reste identique ...

    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT * FROM users ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            $user = new User();
            $user->hydrate($data);
            return $user;
        }
        return null;
    }

    public function getUserByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            $user = new User();
            $user->hydrate($data);
            return $user;
        }
        return null;
    }

    public function save() {
        if ($this->id) {
            $stmt = $this->pdo->prepare("UPDATE users SET fullname=?, email=?, PASSWORD=?, role=?, status=? WHERE id=?");
            return $stmt->execute([
                $this->fullname, 
                $this->email, 
                $this->password, 
                $this->role,  // ✅ Soit 'membre' soit 'conseilleur'
                $this->status, 
                $this->id
            ]);
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO users (fullname, email, PASSWORD, role, status) VALUES (?, ?, ?, ?, ?)");
            $result = $stmt->execute([
                $this->fullname, 
                $this->email, 
                $this->password, 
                $this->role,  // ✅ Soit 'membre' soit 'conseilleur'
                $this->status
            ]);
            if ($result) {
                $this->id = $this->pdo->lastInsertId();
            }
            return $result;
        }
    }

    public function deleteUser($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id=?");
        return $stmt->execute([$id]);
    }

    public function approveUser($id) {
        $stmt = $this->pdo->prepare("UPDATE users SET status='approved' WHERE id=?");
        return $stmt->execute([$id]);
    }

    public function blockUser($id) {
        $stmt = $this->pdo->prepare("UPDATE users SET status='blocked' WHERE id=?");
        return $stmt->execute([$id]);
    }

    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }
}
?>