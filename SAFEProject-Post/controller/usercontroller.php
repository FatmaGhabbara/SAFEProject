<?php

require_once __DIR__ . '/../model/user.php';

class UserController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    // ==================== USER MANAGEMENT ====================
    
    public function listUsers() {
        try {
            return $this->userModel->getAllUsers();
        } catch (Exception $e) {
            error_log("Erreur listUsers: " . $e->getMessage());
            return [];
        }
    }

    public function getUser($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new Exception("ID utilisateur invalide");
            }
            return $this->userModel->getUserById($id);
        } catch (Exception $e) {
            error_log("Erreur getUser: " . $e->getMessage());
            return null;
        }
    }

    public function getUserByEmail($email) {
        try {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Email invalide");
            }
            return $this->userModel->getUserByEmail($email);
        } catch (Exception $e) {
            error_log("Erreur getUserByEmail: " . $e->getMessage());
            return null;
        }
    }

    // ==================== USER ACTIONS ====================
    
    public function deleteUser($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new Exception("ID utilisateur invalide pour suppression");
            }
            return $this->userModel->deleteUser($id);
        } catch (Exception $e) {
            error_log("Erreur deleteUser: " . $e->getMessage());
            return false;
        }
    }

    public function blockUser($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new Exception("ID utilisateur invalide pour blocage");
            }
            return $this->userModel->blockUser($id);
        } catch (Exception $e) {
            error_log("Erreur blockUser: " . $e->getMessage());
            return false;
        }
    }

    public function approveUser($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new Exception("ID utilisateur invalide pour approbation");
            }
            return $this->userModel->approveUser($id);
        } catch (Exception $e) {
            error_log("Erreur approveUser: " . $e->getMessage());
            return false;
        }
    }

    public function pendUser($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new Exception("ID utilisateur invalide pour mise en attente");
            }
            return $this->userModel->pendUser($id);
        } catch (Exception $e) {
            error_log("Erreur pendUser: " . $e->getMessage());
            return false;
        }
    }

    // ==================== USER CREATION ====================
    
    public function createUser($fullname, $email, $password, $role = 'membre') {
        try {
            // Validation des données
            if (empty($fullname) || empty($email) || empty($password)) {
                throw new Exception("Tous les champs sont obligatoires");
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Format d'email invalide");
            }

            if (strlen($password) < 6) {
                throw new Exception("Le mot de passe doit contenir au moins 6 caractères");
            }

            // Vérifier si l'email existe déjà
            if ($this->userModel->emailExists($email)) {
                throw new Exception("Cet email est déjà utilisé");
            }

            // Créer l'utilisateur
            $user = new User();
            $result = $user->create($fullname, $email, $password, $role);

            if ($result) {
                return [
                    'success' => true,
                    'user_id' => $user->getId(),
                    'message' => 'Utilisateur créé avec succès'
                ];
            } else {
                throw new Exception("Erreur lors de la création de l'utilisateur");
            }

        } catch (Exception $e) {
            error_log("Erreur createUser: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // ==================== STATISTICS ====================
    
    public function getUsersCount() {
        try {
            return $this->userModel->getUsersCount();
        } catch (Exception $e) {
            error_log("Erreur getUsersCount: " . $e->getMessage());
            return 0;
        }
    }

    public function getUsersByStatus($status) {
        try {
            $allowedStatus = ['en attente', 'approved', 'blocked'];
            if (!in_array($status, $allowedStatus)) {
                throw new Exception("Statut invalide");
            }
            return $this->userModel->getUsersByStatus($status);
        } catch (Exception $e) {
            error_log("Erreur getUsersByStatus: " . $e->getMessage());
            return [];
        }
    }

    public function getUsersByRole($role) {
        try {
            $allowedRoles = ['membre', 'conseilleur', 'admin'];
            if (!in_array($role, $allowedRoles)) {
                throw new Exception("Rôle invalide");
            }
            return $this->userModel->getUsersByRole($role);
        } catch (Exception $e) {
            error_log("Erreur getUsersByRole: " . $e->getMessage());
            return [];
        }
    }

    // ==================== AUTHENTICATION ====================
    
    public function authenticate($email, $password) {
        try {
            $user = $this->getUserByEmail($email);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ];
            }

            if (!$user->verifyPassword($password)) {
                return [
                    'success' => false,
                    'message' => 'Mot de passe incorrect'
                ];
            }

            if ($user->getStatus() !== 'approved') {
                return [
                    'success' => false,
                    'message' => 'Compte non approuvé'
                ];
            }

            return [
                'success' => true,
                'user' => $user,
                'message' => 'Authentification réussie'
            ];

        } catch (Exception $e) {
            error_log("Erreur authenticate: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur d\'authentification'
            ];
        }
    }
}
?>