<?php
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../controller/usercontroller.php';

class AuthController {
    private $userController;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->userController = new UserController();
    }

    // MÉTHODE REGISTER
    public function register($nom, $email, $password, $role = 'membre') {
        if (empty($nom) || empty($email) || empty($password)) {
            return "Tous les champs sont obligatoires.";
        }

        $email = trim($email);
        $email = strtolower($email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Format d'email invalide.";
        }

        if (strlen($password) < 6) {
            return "Le mot de passe doit contenir au moins 6 caractères.";
        }

        $allowedRoles = ['membre', 'conseilleur', 'admin'];
        if (!in_array($role, $allowedRoles)) {
            $role = 'membre';
        }

        try {
            $existing = $this->userController->getUserByEmail($email);
            if ($existing) {
                return "Cet email est déjà utilisé.";
            }

            $user = new User($nom, $email, $password, $role, "en attente");

            if ($this->userController->addUser($user)) {
                return true;
            } else {
                return "Erreur lors de l'inscription.";
            }

        } catch (PDOException $e) {
            error_log("❌ Erreur PDO register: " . $e->getMessage());
            return "Erreur de base de données.";
        } catch (Exception $e) {
            error_log("❌ Erreur register: " . $e->getMessage());
            return "Erreur lors de l'inscription.";
        }
    }

    // MÉTHODE LOGIN - VERSION CORRIGÉE
 // Dans AuthController.php - modifier la méthode login
public function login($email, $password) {
    if (empty($email) || empty($password)) {
        return "Email et mot de passe requis.";
    }

    try {
        $email = trim($email);
        $email = strtolower($email);

        $userData = $this->userController->getUserByEmail($email);
        
        if (!$userData) {
            return "Email ou mot de passe incorrect.";
        }

        if (!password_verify($password, $userData['password'])) {
            return "Email ou mot de passe incorrect.";
        }

        // Vérifier le statut - standardisé sur 'actif'
        if ($userData['status'] !== 'actif') {
            // Pour l'admin, on autorise même si en attente
            if ($userData['role'] === 'admin') {
                // Activer automatiquement l'admin
                $this->userController->updateUserStatus($userData['id'], 'actif');
                $userData['status'] = 'actif';
            } else {
                return "Votre compte est en attente d'approbation.";
            }
        }

        // Démarrer la session si ce n'est pas déjà fait
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Stocker les informations de session
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_role'] = $userData['role'];
        $_SESSION['fullname'] = $userData['nom'];
        $_SESSION['user_email'] = $userData['email'];
        $_SESSION['user_status'] = $userData['status'];
        
        // DEBUG: Vérifier les données de session
        error_log("✅ Connexion réussie pour: " . $userData['email']);
        error_log("✅ Rôle: " . $userData['role']);
        error_log("✅ Session ID: " . session_id());
        
        // Redirection selon le rôle - CORRECTION ICI
        if ($userData['role'] === 'admin') {
            header('Location: /SAFEProject/view/backoffice/index.php');
            exit();
        } elseif ($userData['role'] === 'conseilleur') {
            header('Location: /SAFEProject/view/backoffice/adviser_dashboard.php');
            exit();
        } elseif ($userData['role'] === 'membre') {
            header('Location: /SAFEProject/view/backoffice/member_dashboard.php');
            exit();
        } else {
            // Par défaut, rediriger vers le dashboard membre
            header('Location: /SAFEProject/view/backoffice/member_dashboard.php');
            exit();
        }
        
    } catch (PDOException $e) {
        error_log("❌ Erreur PDO login: " . $e->getMessage());
        return "Erreur de connexion: " . $e->getMessage();
    } catch (Exception $e) {
        error_log("❌ Erreur login: " . $e->getMessage());
        return "Erreur lors de la connexion.";
    }
}

// Supprimer ou modifier la méthode redirectUser si elle existe
private function redirectUser($role) {
    switch ($role) {
        case 'admin':
            header('Location: /SAFEProject/view/backoffice/index.php');
            break;
        case 'conseilleur':
            header('Location: /SAFEProject/view/backoffice/adviser_dashboard.php');
            break;
        case 'membre':
            header('Location: /SAFEProject/view/backoffice/member_dashboard.php');
            break;
        default:
            header('Location: /SAFEProject/view/backoffice/member_dashboard.php');
            break;
    }
    exit();
}
    // MÉTHODE LOGOUT
    public function logout() {
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        header('Location: /SAFEProject/view/frontoffice/login.php');
        exit();
    }

    // MÉTHODES DE VÉRIFICATION
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    public function isConseilleur() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'conseilleur';
    }

    public function getCurrentUserRole() {
        return $_SESSION['user_role'] ?? null;
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        return $this->userController->getUserById($_SESSION['user_id']);
    }

    public function getCurrentUserInfo() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'fullname' => $_SESSION['fullname'] ?? 'Utilisateur',
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role']
        ];
    }

    // MÉTHODES DE SÉCURITÉ
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            header('Location: /SAFEProject/view/frontoffice/login.php');
            exit();
        }
    }

    public function requireAdmin() {
        $this->requireAuth();
        if (!$this->isAdmin()) {
            header('Location: /SAFEProject/view/frontoffice/index.php');
            exit();
        }
    }

    public function requireConseilleur() {
        $this->requireAuth();
        if (!$this->isConseilleur() && !$this->isAdmin()) {
            header('Location: /SAFEProject/view/frontoffice/index.php');
            exit();
        }
    }

    // MÉTHODE POUR CRÉER UN ADMIN PAR DÉFAUT
    public function createDefaultAdmin() {
        try {
            $adminEmail = 'admin@safespace.com';
            
            // Vérifier si l'admin existe déjà
            $existing = $this->userController->getUserByEmail($adminEmail);
            
            if (!$existing) {
                $adminPassword = 'admin123';
                $user = new User('Administrateur', $adminEmail, $adminPassword, 'admin', 'actif');
                
                if ($this->userController->addUser($user)) {
                    return "Admin créé avec succès. Email: $adminEmail, Mot de passe: $adminPassword";
                } else {
                    return "Erreur lors de la création de l'admin.";
                }
            }
            
            return "L'administrateur existe déjà.";
            
        } catch (Exception $e) {
            error_log("❌ Erreur createDefaultAdmin: " . $e->getMessage());
            return "Erreur: " . $e->getMessage();
        }
    }
}
?>