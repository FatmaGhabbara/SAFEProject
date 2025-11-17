<?php
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
