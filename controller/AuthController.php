<?php
require_once __DIR__ . '/../model/user.php';

class AuthController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login(string $email, string $password): bool|string {
        $users = $this->userModel->getAllUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                if ($user['status'] === 'en attente') return "Compte non validé.";
                if ($user['status'] === 'blocked') return "Compte bloqué.";

                if (password_verify($password, $user['PASSWORD'])) {
                    session_start();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['fullname'];
                    $_SESSION['role'] = $user['role'];
                    return true;
                } else {
                    return "Mot de passe incorrect.";
                }
            }
        }
        return "Email non trouvé.";
    }

    public function register(string $firstname, string $lastname, string $email, string $password): bool|string {
        $fullname = trim("$firstname $lastname");
        $users = $this->userModel->getAllUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email) return "Email déjà utilisé.";
        }
        return $this->userModel->addUser($fullname, $email, $password);
    }
}
?>
