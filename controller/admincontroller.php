<?php
require_once __DIR__ . '/../model/user.php';

class AdminController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function getAllUsers(): array {
        return $this->userModel->getAllUsers();
    }

    public function approveUser(int $id): bool {
        return $this->userModel->approveUser($id);
    }

    public function blockUser(int $id): bool {
        return $this->userModel->blockUser($id);
    }

    public function deleteUser(int $id): bool {
        return $this->userModel->deleteUser($id);
    }

    // Stats simples
    public function getStats(): array {
        $stats['users'] = count($this->userModel->getAllUsers());
        return $stats;
    }
}
?>
