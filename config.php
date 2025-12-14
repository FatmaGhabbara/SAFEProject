<?php
/**
 * SAFEProject - config.php
 * Gestion centralisée de la connexion PDO (Singleton)
 */

class Config
{
    private static ?PDO $pdo = null;

    /**
     * Retourne une instance PDO unique
     *
     * @return PDO
     */
    public static function getConnexion(): PDO
    {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO(
<<<<<<< HEAD
                    'mysql:host=127.0.0.1;dbname=safespace',
=======
                    'mysql:host=localhost;dbname=safespace;charset=utf8',
>>>>>>> af8b4baf22b0b6e35827106fed7e959ed54c3093
                    'root',
                    '',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_PERSISTENT => false
                    ]
                );
            } catch (PDOException $e) {
                die('Erreur de connexion à la base de données : ' . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}
