<?php
require_once __DIR__ . '/../model/user.php';

class UserController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    // 🔹 Lister tous les utilisateurs
    public function listUsers(): array {
        return $this->userModel->getAllUsers();
    }

    // 🔹 Récupérer un utilisateur par ID
    public function getUser(int $id): ?array {
        return $this->userModel->getUserById($id);
    }

    // 🔹 Supprimer un utilisateur (backend/admin)
    public function deleteUser(int $id): bool {
        return $this->userModel->deleteUser($id);
    }

    // 🔹 Bloquer un utilisateur
    public function blockUser(int $id): bool {
        return $this->userModel->blockUser($id);
    }

    // 🔹 Approuver un utilisateur
    public function approveUser(int $id): bool {
        return $this->userModel->approveUser($id);
    }
}
?>
