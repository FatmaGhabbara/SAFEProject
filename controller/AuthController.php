<?php
require_once __DIR__ . '/../model/user.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login($email, $password) {
        $user = $this->userModel->getUserByEmail($email);
        
        if (!$user) {
            return "Email non trouvé.";
        }

        if ($user->getStatus() === 'en attente') {
            return "Compte non validé.";
        }

        if ($user->getStatus() === 'blocked') {
            return "Compte bloqué.";
        }

        if (!$user->verifyPassword($password)) {
            return "Mot de passe incorrect.";
        }

        // Connexion réussie
        session_start();
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['username'] = $user->getFullname();
        $_SESSION['role'] = $user->getRole();
        
        return true;
    }

    // ✅ CORRECTION : Ajout du paramètre $role avec valeur par défaut
    public function register($firstname, $lastname, $email, $password, $role = 'membre') {
        $fullname = trim("$firstname $lastname");
        
        // Vérifier si l'email existe déjà
        if ($this->userModel->getUserByEmail($email)) {
            return "Email déjà utilisé.";
        }

        // ✅ CORRECTION : Validation du rôle
        $allowedRoles = ['membre', 'conseilleur'];
        if (!in_array($role, $allowedRoles)) {
            $role = 'membre'; // Valeur par défaut sécurisée
        }

        // Créer nouvel utilisateur
        $newUser = new User();
        $newUser->setFullname($fullname);
        $newUser->setEmail($email);
        $newUser->setPassword($password);
        $newUser->setRole($role); // ✅ Utilise le rôle choisi ou 'membre' par défaut
        $newUser->setStatus('en attente');

        if ($newUser->save()) {
            return true;
        } else {
            return "Erreur lors de l'inscription.";
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: frontoffice/login.php');
        exit();
    }
}
?>