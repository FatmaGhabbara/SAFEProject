<?php
require_once 'C:/xampp/htdocs/SAFEProject/model/user.php';

class UserController {
    private UserModel $model;

    public function __construct() {
        $this->model = new UserModel(); // ← utiliser UserModel, pas User
    }

    // 🔹 Récupérer tous les utilisateurs
    public function listUsers(): array {
        return $this->model->getAllUsers();
    }

    // 🔹 Afficher un profil
    public function showProfile(int $id): ?array {
        return $this->model->getUserById($id);
    }

    // 🔹 Mettre à jour un profil
    public function updateProfile(int $id, string $fullname, string $email, ?string $password = null): string {
        $user = $this->model->getUserById($id);
        if (!$user) {
            return "Utilisateur non trouvé.";
        }

        if (empty($fullname) || empty($email)) {
            return "Tous les champs sont obligatoires.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Adresse e-mail invalide.";
        }
        if ($password !== null && strlen($password) < 6) {
            return "Le mot de passe doit contenir au moins 6 caractères.";
        }

        $success = $this->model->updateUser($id, $fullname, $email, $password);
        return $success ? "Profil mis à jour avec succès." : "Erreur lors de la mise à jour du profil.";
    }
}
?>
