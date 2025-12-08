<?php
/**
 * ============================================
 * CONFIGURATION DE LA BASE DE DONNÉES
 * SAFEProject - Module Support Psychologique
 * ============================================
 */

// Définir le fuseau horaire (Tunisie)
date_default_timezone_set('Africa/Tunis');

// Afficher les erreurs en développement (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration de la base de données - Docker MySQL Container
define('DB_HOST', '127.0.0.1');      // Docker container mapped to localhost
define('DB_PORT', '3306');
define('DB_NAME', 'safeproject_db11');  // Database name
define('DB_USER', 'root');
define('DB_PASS', '');               // Empty password
define('DB_CHARSET', 'utf8mb4');

/**
 * Classe de connexion à la base de données (Singleton)
 */
class Database {
    private static $instance = null;
    private $connection;
    
    /**
     * Constructeur privé pour empêcher l'instanciation directe
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
    
    /**
     * Empêcher le clonage de l'instance
     */
    private function __clone() {}
    
    /**
     * Empêcher la désérialisation de l'instance
     */
    public function __wakeup() {
        throw new Exception("Impossible de désérialiser un singleton");
    }
    
    /**
     * Récupérer l'instance unique de la connexion
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Récupérer l'objet PDO
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }
}

/**
 * Fonction helper pour obtenir la connexion PDO
 * @return PDO
 */
function getDB() {
    return Database::getInstance()->getConnection();
}

/**
 * Démarrer la session si elle n'est pas déjà démarrée
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifier si l'utilisateur est connecté
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Vérifier si l'utilisateur est administrateur
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Vérifier si l'utilisateur est conseiller
 * @return bool
 */
function isCounselor() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'counselor';
}

/**
 * Vérifier si l'utilisateur est un client normal
 * @return bool
 */
function isUser() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

/**
 * Rediriger l'utilisateur connecté vers sa page principale selon son rôle
 * @return void
 */
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        $role = $_SESSION['role'] ?? 'user';
        
        if ($role === 'admin') {
            header('Location: view/backoffice/support/support_requests.php');
            exit();
        } elseif ($role === 'counselor') {
            header('Location: view/backoffice/support/my_assigned_requests.php');
            exit();
        } else {
            header('Location: view/frontoffice/dashboard.php');
            exit();
        }
    }
}

/**
 * Rediriger vers une page
 * @param string $url
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Sécuriser une chaîne pour l'affichage HTML
 * @param string $string
 * @return string
 */
function secureOutput($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Nettoyer une chaîne (supprimer les tags HTML)
 * @param string $string
 * @return string
 */
function cleanInput($string) {
    return strip_tags(trim($string));
}

/**
 * Générer un token CSRF
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifier le token CSRF
 * @param string $token
 * @return bool
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Formater une date en français
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    if (empty($date)) return '-';
    $dateTime = new DateTime($date);
    return $dateTime->format($format);
}

/**
 * Calculer le temps écoulé depuis une date
 * @param string $date
 * @return string
 */
function timeAgo($date) {
    if (empty($date)) return '-';
    
    $timestamp = strtotime($date);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'À l\'instant';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '');
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' heure' . ($hours > 1 ? 's' : '');
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' jour' . ($days > 1 ? 's' : '');
    } else {
        return formatDate($date);
    }
}

/**
 * Logger une action dans un fichier
 * @param string $message
 * @param string $type (info, warning, error)
 */
function logAction($message, $type = 'info') {
    $logDir = __DIR__ . '/logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $logFile = $logDir . '/support_module_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $userId = $_SESSION['user_id'] ?? 'guest';
    $logMessage = "[$timestamp] [$type] [User: $userId] $message" . PHP_EOL;
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * Définir un message flash
 * @param string $message
 * @param string $type (success, error, warning, info)
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Récupérer et supprimer le message flash
 * @return array|null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Configuration des chemins du projet
 */
define('ROOT_PATH', __DIR__);
define('MODEL_PATH', ROOT_PATH . '/model');
define('VIEW_PATH', ROOT_PATH . '/view');
define('CONTROLLER_PATH', ROOT_PATH . '/controller');
define('LOGS_PATH', ROOT_PATH . '/logs');

/**
 * URL de base du projet (à adapter selon votre configuration)
 */
define('BASE_URL', '/SAFEProject');
define('FRONTEND_URL', BASE_URL . '/view/frontoffice');
define('BACKEND_URL', BASE_URL . '/view/backoffice');

/**
 * Get the login page path - always returns correct path to login.php
 * @return string Path to login page
 */
function getLoginPath() {
    // From controller/auth/: ../../view/frontoffice/login.php
    // From view/backoffice/: ../frontoffice/login.php
    // From view/frontoffice/: login.php
    // From project root: view/frontoffice/login.php
    
    $script_path = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
    $project_root = str_replace('\\', '/', __DIR__);
    
    if (strpos($script_path, '/controller/') !== false) {
        return '../../view/frontoffice/login.php';
    } elseif (strpos($script_path, '/view/backoffice/') !== false) {
        return '../frontoffice/login.php';
    } elseif (strpos($script_path, '/view/frontoffice/') !== false) {
        return 'login.php';
    } else {
        return 'view/frontoffice/login.php';
    }
}

/**
 * Redirect to login page - always works from anywhere
 */
function redirectToLogin() {
    $login_path = getLoginPath();
    header('Location: ' . $login_path);
    exit();
}

/**
 * Get base path to project root from current script location
 * @return string Base path (e.g., "../" or "../../")
 */
function getBasePath() {
    $current_script = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
    $project_root = str_replace('\\', '/', __DIR__); // SAFEProject/
    
    // Get the directory of the current script
    $current_dir = dirname($current_script);
    
    // Calculate how many levels up we need to go
    $relative_to_root = str_replace($project_root . '/', '', $current_dir);
    $depth = substr_count($relative_to_root, '/');
    
    // Build base_path: go up (depth) levels to reach project root
    // From view/frontoffice/: depth=1, base_path="../"
    // From view/backoffice/support/: depth=2, base_path="../../"
    // From view/: depth=0, base_path="" (but we'll use "../" to be safe)
    return $depth > 0 ? str_repeat('../', $depth) : '../';
}

/**
 * Get path to a view file from project root
 * @param string $path Path from project root (e.g., "view/frontoffice/login.php")
 * @return string Full relative path
 */
function getViewPath($path) {
    $base_path = getBasePath();
    return $base_path . $path;
}

?>

