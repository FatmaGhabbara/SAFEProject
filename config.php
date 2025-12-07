<?php
// SAFEProject/config.php

class Config {
    private static $host = "localhost";
    private static $db_name = "safespace";
    private static $username = "root";
    private static $password = "";

    public static function getConnexion() {
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
?>