<?php

// SAFEProject/config.php

class Config {
    private static $host = "localhost";
    private static $db_name = "safespace";
    private static $username = "root";
    private static $password = "";

    public static function connect() {
        try {
            $pdo = new PDO(
                "mysql:host=".self::$host.";dbname=".self::$db_name,
                self::$username,
                self::$password
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }
}

class Database {
    private static $pdo = null;
    private $host = 'localhost';
    private $db_name = 'safeproject_db';
    private $username = 'root';
    private $password = '';
    public $conn;
    
    // Méthode statique (ancienne méthode)
    public static function getConnexion() {
        if (!isset(self::$pdo)) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=localhost;dbname=safeproject_db',
                    'root',
                    '',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (Exception $e) {
                die('Erreur: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
    
    // Méthode d'instance (pour compatibilité avec l'application)
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Erreur de connexion: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}

// Créer la connexion (méthode statique - pour compatibilité)
$db = Database::getConnexion();

// Créer aussi une instance pour compatibilité avec l'application
$database = new Database();