<?php
require_once 'C:/xampp/htdocs/SAFEProject/model/user.php';

class AdminController {
    private UserModel $userModel;

    public function __construct() {
        $this->userModel = new UserModel(); // Utiliser UserModel pour gérer les utilisateurs
    }

    // 🔹 Récupérer tous les utilisateurs
    public function getAllUsers(): array {
        return $this->userModel->getAllUsers();
    }

    // 🔹 Approuver un utilisateur
    public function approveUser(int $id): bool {
        if ($id > 0) {
            return $this->userModel->approveUser($id);
        }
        return false;
    }

    // 🔹 Bloquer un utilisateur
    public function blockUser(int $id): bool {
        if ($id > 0) {
            return $this->userModel->blockUser($id);
        }
        return false;
    }

    // 🔹 Supprimer un utilisateur
    public function deleteUser(int $id): bool {
        if ($id > 0) {
            return $this->userModel->deleteUser($id);
        }
        return false;
    }

    // 🔹 Récupérer un utilisateur par son ID
    public function getUser(int $id): ?array {
        return $this->userModel->getUserById($id);
    }
}
?>
