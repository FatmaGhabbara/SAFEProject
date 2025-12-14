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

        if ($_SESSION[$key]['blocked_until'] > time()) {
            return "Compte temporairement bloqué. Réessayez plus tard.";
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

    /* =======================
       AUTH HELPERS
    ======================= */
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public function isAdmin(): bool
    {
        return $this->isLoggedIn() && $_SESSION['user_role'] === 'admin';
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: /SAFEProject/view/frontoffice/login.php');
        exit;
    }
}
