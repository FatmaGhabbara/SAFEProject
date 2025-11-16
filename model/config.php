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

// Configuration de la base de données
define('DB_HOST', '127.0.0.1');      // Utiliser 127.0.0.1 au lieu de localhost pour TCP/IP
define('DB_PORT', '3306');
define('DB_NAME', 'safeproject_db'); // Base de données Docker
define('DB_USER', 'root');           // Utilisateur Docker
define('DB_PASS', '');               // Pas de mot de passe
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
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Vérifier si l'utilisateur est administrateur
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
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
    $logDir = __DIR__ . '/../logs';
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
define('ROOT_PATH', dirname(__DIR__));
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

?>

