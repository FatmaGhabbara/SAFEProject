<?php
require_once 'C:/xampp/htdocs/SAFEProject/config.php';

class UserModel {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Config::connect();
    }

    // 🔹 Récupérer un utilisateur par son ID
    public function getUserById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // 🔹 Récupérer tous les utilisateurs
    public function getAllUsers(): array {
        $stmt = $this->pdo->query("SELECT * FROM users ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔹 Mettre à jour un utilisateur
    public function updateUser(int $id, string $fullname, string $email, ?string $password = null): bool {
        if ($password) {
            $stmt = $this->pdo->prepare("UPDATE users SET fullname = ?, email = ?, password = ? WHERE id = ?");
            return $stmt->execute([$fullname, $email, password_hash($password, PASSWORD_DEFAULT), $id]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE users SET fullname = ?, email = ? WHERE id = ?");
            return $stmt->execute([$fullname, $email, $id]);
        }
    }

    // 🔹 Accepter un utilisateur
    public function approveUser(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // 🔹 Bloquer un utilisateur
    public function blockUser(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE users SET status = 'blocked' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // 🔹 Supprimer un utilisateur
    public function deleteUser(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
