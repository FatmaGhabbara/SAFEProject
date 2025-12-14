<?php
<<<<<<< HEAD
// Prevent redeclaration issues when the file is included multiple times
if (!class_exists('User')) {
class User {
    private $id;
    private $nom;
    private $email;
    private $password;
    private $role;
    private $status;
    private $profile_picture;
    private $date_naissance;
    private $telephone;
    private $adresse;
    private $bio;
    private $specialite;
    private $created_at;
    private $updated_at;

    public function __construct($nom = "", $email = "", $password = "", $role = "membre", $status = "en attente") {
        $this->nom = $nom;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->status = $status;
        $this->profile_picture = 'assets/images/default-avatar.png';
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getEmail() { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getRole() { return $this->role; }
    public function getStatus() { return $this->status; }
    public function getProfilePicture() { return $this->profile_picture; }
    public function getDateNaissance() { return $this->date_naissance; }
    public function getTelephone() { return $this->telephone; }
    public function getAdresse() { return $this->adresse; }
    public function getBio() { return $this->bio; }
    public function getSpecialite() { return $this->specialite; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    // Setters
    public function setId($id) { $this->id = $id; return $this; }
    public function setNom($nom) { $this->nom = $nom; return $this; }
    public function setEmail($email) { $this->email = $email; return $this; }
    public function setPassword($password) { $this->password = $password; return $this; }
    public function setRole($role) { $this->role = $role; return $this; }
    public function setStatus($status) { $this->status = $status; return $this; }
    public function setProfilePicture($profile_picture) { $this->profile_picture = $profile_picture; return $this; }
    public function setDateNaissance($date_naissance) { $this->date_naissance = $date_naissance; return $this; }
    public function setTelephone($telephone) { $this->telephone = $telephone; return $this; }
    public function setAdresse($adresse) { $this->adresse = $adresse; return $this; }
    public function setBio($bio) { $this->bio = $bio; return $this; }
    public function setSpecialite($specialite) { $this->specialite = $specialite; return $this; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; return $this; }
    public function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; return $this; }
}
}
?>
=======
require_once __DIR__ . '/../config.php';

class User {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Config::connect();
    }

    // Récupérer tous les utilisateurs
    public function getAllUsers(): array {
        $stmt = $this->pdo->query("SELECT * FROM users ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer un utilisateur par ID
    public function getUserById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Ajouter un utilisateur
    public function addUser(string $fullname, string $email, string $password, string $role = 'membre', string $status = 'en attente'): bool {
        $stmt = $this->pdo->prepare("INSERT INTO users (fullname, email, PASSWORD, role, status) VALUES (?, ?, ?, ?, ?)");
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        return $stmt->execute([$fullname, $email, $hashed_password, $role, $status]);
    }

    // Approuver utilisateur
    public function approveUser(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE users SET status='approved' WHERE id=?");
        return $stmt->execute([$id]);
    }

    // Bloquer utilisateur
    public function blockUser(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE users SET status='blocked' WHERE id=?");
        return $stmt->execute([$id]);
    }

    // Supprimer utilisateur
    public function deleteUser(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id=?");
        return $stmt->execute([$id]);
    }
}
?>
>>>>>>> aab829f16e3aa2e6ba701ae4dd16b8c047cec2fa
