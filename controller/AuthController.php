<?php

require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../controller/usercontroller.php';

class AuthController
{
    private UserController $userController;
    private int $maxAttempts = 5;     // max tentatives
    private int $lockoutTime = 900;   // 15 minutes

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userController = new UserController();
    }

    /* =======================
       GEOLOCALISATION IP
    ======================= */
    private function getIpGeolocation(string $ip): ?array
    {
        try {
            $url = "https://ipapi.co/{$ip}/json/";
            $context = stream_context_create([
                'http' => [
                    'timeout' => 3,
                    'header' => "User-Agent: SafeSpace-App/1.0\r\n"
                ]
            ]);

            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                return null;
            }

            $data = json_decode($response, true);
            if (isset($data['error'])) {
                return null;
            }

            return [
                'ip' => $data['ip'] ?? $ip,
                'city' => $data['city'] ?? 'Inconnu',
                'region' => $data['region'] ?? 'Inconnu',
                'country' => $data['country_name'] ?? 'Inconnu',
                'country_code' => $data['country_code'] ?? 'XX',
                'timezone' => $data['timezone'] ?? 'UTC',
                'isp' => $data['org'] ?? 'Inconnu',
            ];
        } catch (Exception $e) {
            return null;
        }
    }

    /* =======================
       DETECTION BROWSER / OS
    ======================= */
    private function detectBrowser(): string
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return match (true) {
            stripos($ua, 'Edg') !== false => 'Edge',
            stripos($ua, 'Chrome') !== false => 'Chrome',
            stripos($ua, 'Firefox') !== false => 'Firefox',
            stripos($ua, 'Safari') !== false => 'Safari',
            stripos($ua, 'OPR') !== false => 'Opera',
            default => 'Navigateur'
        };
    }

    private function detectOS(): string
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return match (true) {
            stripos($ua, 'Windows') !== false => 'Windows',
            stripos($ua, 'Mac') !== false => 'macOS',
            stripos($ua, 'Linux') !== false => 'Linux',
            stripos($ua, 'Android') !== false => 'Android',
            stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false => 'iOS',
            default => 'OS'
        };
    }

    /* =======================
       REGISTER
    ======================= */
    public function register(string $nom, string $email, string $password, string $role = 'membre')
    {
        if ($nom === '' || $email === '' || $password === '') {
            return "Tous les champs sont obligatoires.";
        }

        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Email invalide.";
        }

        if (strlen($password) < 6) {
            return "Mot de passe minimum 6 caractères.";
        }

        $allowedRoles = ['membre', 'conseilleur', 'admin'];
        if (!in_array($role, $allowedRoles, true)) {
            $role = 'membre';
        }

        if ($this->userController->getUserByEmail($email)) {
            return "Cet email est déjà utilisé.";
        }

        $user = new User($nom, $email, $password, $role, 'en attente');
        return $this->userController->addUser($user)
            ? true
            : "Erreur lors de l'inscription.";
    }

    /* =======================
       LOGIN + RATE LIMIT
    ======================= */
    public function login(string $email, string $password)
    {
        if ($email === '' || $password === '') {
            return "Email et mot de passe requis.";
        }

        $email = strtolower(trim($email));
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = 'login_' . md5($email . $ip);

        $_SESSION[$key] ??= ['count' => 0, 'blocked_until' => 0];

<<<<<<< HEAD
            $attempts = $_SESSION[$attemptKey];

            // Vérifier si bloqué
            if ($attempts['blocked_until'] > time()) {
                $remaining = $attempts['blocked_until'] - time();
                $minutes = ceil($remaining / 60);
                return "Trop de tentatives. Réessayez dans " . $minutes . " minute(s)";
            }

            $userData = $this->userController->getUserByEmail($email);
            
            if (!$userData) {
                // Incrémenter les tentatives
                $_SESSION[$attemptKey]['count']++;
                $_SESSION[$attemptKey]['last_attempt'] = time();
                
                // Bloquer après 5 tentatives
                if ($_SESSION[$attemptKey]['count'] >= $this->maxAttempts) {
                    $_SESSION[$attemptKey]['blocked_until'] = time() + $this->lockoutTime;
                    return "Trop de tentatives échouées. Compte bloqué pour 15 minutes.";
                }
                
                $remainingAttempts = $this->maxAttempts - $_SESSION[$attemptKey]['count'];
                return "Email ou mot de passe incorrect. Tentatives restantes: " . $remainingAttempts;
            }

            if (!password_verify($password, $userData['password'])) {
                // Incrémenter les tentatives
                $_SESSION[$attemptKey]['count']++;
                $_SESSION[$attemptKey]['last_attempt'] = time();
                
                // Bloquer après 5 tentatives
                if ($_SESSION[$attemptKey]['count'] >= $this->maxAttempts) {
                    $_SESSION[$attemptKey]['blocked_until'] = time() + $this->lockoutTime;
                    return "Trop de tentatives échouées. Compte bloqué pour 15 minutes.";
                }
                
                $remainingAttempts = $this->maxAttempts - $_SESSION[$attemptKey]['count'];
                return "Email ou mot de passe incorrect. Tentatives restantes: " . $remainingAttempts;
            }

            // ✅ CONNEXION RÉUSSIE - Récupérer les infos de géolocalisation
            
            // 1. Détecter navigateur et OS
            $browser = $this->detectBrowser();
            $os = $this->detectOS();
            
            // 2. Récupérer la géolocalisation (asynchrone pour ne pas ralentir)
            $geoData = $this->getIpGeolocation($ip);

            // 3. Si succès, réinitialiser les tentatives
            unset($_SESSION[$attemptKey]);

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

            // Stocker les informations de session (avec géolocalisation)
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['user_role'] = $userData['role'];
            $_SESSION['fullname'] = $userData['nom'];
            $_SESSION['user_email'] = $userData['email'];
            $_SESSION['user_status'] = $userData['status'];
            $_SESSION['last_login'] = time();
            $_SESSION['login_ip'] = $ip;
            $_SESSION['login_browser'] = $browser;
            $_SESSION['login_os'] = $os;
            
            // Stocker la géolocalisation si disponible
            if ($geoData) {
                $_SESSION['login_geo'] = $geoData;
            }

            // Redirection selon le rôle
            if ($userData['role'] === 'admin') {
                header('Location: /view/backoffice/index.php');
                exit();
            } elseif ($userData['role'] === 'conseilleur') {
                header('Location: /view/backoffice/adviser_dashboard.php');
                exit();
            } elseif ($userData['role'] === 'membre') {
                header('Location: /view/backoffice/member_dashboard.php');
                exit();
            } else {
                header('Location: /view/backoffice/member_dashboard.php');
                exit();
            }
            
        } catch (PDOException $e) {
            error_log("❌ Erreur PDO login: " . $e->getMessage());
            return "Erreur de connexion: " . $e->getMessage();
        } catch (Exception $e) {
            error_log("❌ Erreur login: " . $e->getMessage());
            return "Erreur lors de la connexion.";
=======
        if ($_SESSION[$key]['blocked_until'] > time()) {
            return "Compte temporairement bloqué. Réessayez plus tard.";
>>>>>>> af8b4baf22b0b6e35827106fed7e959ed54c3093
        }

        $user = $this->userController->getUserByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION[$key]['count']++;
            if ($_SESSION[$key]['count'] >= $this->maxAttempts) {
                $_SESSION[$key]['blocked_until'] = time() + $this->lockoutTime;
            }
            return "Email ou mot de passe incorrect.";
        }

        unset($_SESSION[$key]);

        if ($user['status'] !== 'actif' && $user['role'] !== 'admin') {
            return "Compte en attente de validation.";
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['fullname'] = $user['nom'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['login_ip'] = $ip;
        $_SESSION['login_browser'] = $this->detectBrowser();
        $_SESSION['login_os'] = $this->detectOS();
        $_SESSION['login_geo'] = $this->getIpGeolocation($ip);
        $_SESSION['last_login'] = time();

        return true;
    }

<<<<<<< HEAD
    // 🆕 MÉTHODE POUR RÉCUPÉRER LA DERNIÈRE CONNEXION
    public function getLastLoginInfo() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        // Si la géolocalisation n'est pas en session, on essaie de la récupérer
        if (!isset($_SESSION['login_geo']) && isset($_SESSION['login_ip'])) {
            $_SESSION['login_geo'] = $this->getIpGeolocation($_SESSION['login_ip']);
            
            // Si pas encore détectés, détecter navigateur et OS
            if (!isset($_SESSION['login_browser'])) {
                $_SESSION['login_browser'] = $this->detectBrowser();
            }
            if (!isset($_SESSION['login_os'])) {
                $_SESSION['login_os'] = $this->detectOS();
            }
        }
        
        return [
            'ip' => $_SESSION['login_ip'] ?? null,
            'browser' => $_SESSION['login_browser'] ?? null,
            'os' => $_SESSION['login_os'] ?? null,
            'geo' => $_SESSION['login_geo'] ?? null,
            'time' => $_SESSION['last_login'] ?? null
        ];
    }

    // 🆕 MÉTHODE POUR AFFICHER UN MESSAGE PERSONNALISÉ
    public function getWelcomeMessage() {
        if (!$this->isLoggedIn()) {
            return "Bienvenue sur SafeSpace";
        }
        
        $geo = $_SESSION['login_geo'] ?? null;
        $browser = $_SESSION['login_browser'] ?? null;
        
        if ($geo && $geo['city'] !== 'Inconnu') {
            return "Vous êtes connecté depuis " . $geo['city'] . ", " . $geo['country'] . 
                   " via " . $browser;
        } elseif ($browser) {
            return "Bienvenue sur SafeSpace ! Connexion via " . $browser;
        } else {
            return "Bienvenue sur SafeSpace !";
        }
    }

    // 🆕 MÉTHODE POUR AFFICHER UNE ALERTE DE SÉCURITÉ
    public function getSecurityAlert() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $geo = $_SESSION['login_geo'] ?? null;
        $lastLogin = $_SESSION['last_login'] ?? null;
        
        if ($geo && $lastLogin) {
            // Vérifier si la connexion est récente (< 1 heure)
            $timeDiff = time() - $lastLogin;
            if ($timeDiff < 3600) {
                return [
                    'type' => 'info',
                    'message' => "Nouvelle connexion détectée depuis " . $geo['city'] . ", " . $geo['country'],
                    'time' => date('H:i', $lastLogin)
                ];
            }
        }
        
        return null;
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
        header('Location: /view/frontoffice/login.php');
        exit();
    }

    // MÉTHODES DE VÉRIFICATION
    public function isLoggedIn() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
=======
    /* =======================
       AUTH HELPERS
    ======================= */
    public function isLoggedIn(): bool
    {
>>>>>>> af8b4baf22b0b6e35827106fed7e959ed54c3093
        return isset($_SESSION['user_id']);
    }

    public function isAdmin(): bool
    {
        return $this->isLoggedIn() && $_SESSION['user_role'] === 'admin';
    }

<<<<<<< HEAD
    public function isConseilleur() {
        return $this->isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'conseilleur';
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
            header('Location: /view/frontoffice/login.php');
            exit();
        }
    }

    public function requireAdmin() {
        $this->requireAuth();
        if (!$this->isAdmin()) {
            header('Location: /view/frontoffice/index.php');
            exit();
        }
    }

    public function requireConseilleur() {
        $this->requireAuth();
        if (!$this->isConseilleur() && !$this->isAdmin()) {
            header('Location: /view/frontoffice/index.php');
            exit();
        }
    }

    // MÉTHODE POUR CRÉER UN ADMIN PAR DÉFAUT
    public function createDefaultAdmin() {
        try {
            $adminEmail = 'admin@safespace.com';
            
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
    
    // MÉTHODE POUR RÉINITIALISER LES TENTATIVES
    public function resetLoginAttempts($email) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $attemptKey = 'login_attempts_' . md5($email . $ip);
        unset($_SESSION[$attemptKey]);
        return true;
=======
    public function logout(): void
    {
        session_destroy();
        header('Location: /SAFEProject/view/frontoffice/login.php');
        exit;
>>>>>>> af8b4baf22b0b6e35827106fed7e959ed54c3093
    }
}
