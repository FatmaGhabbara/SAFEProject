<<<<<<< HEAD
<?php
class config
{
    private static $pdo = null;

    public static function getConnexion()
    {
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
}
?>
=======
<<<<<<< HEAD
<?php
// SAFEProject/config.php

class Config {
    private static $host = "localhost";
    private static $db_name = "safespace";
    private static $username = "root";
    private static $password = "";

    public static function connect(): PDO {
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

=======
<?php
class config
{
  private static $pdo = null;
  public static function getConnexion()
  {
    if (!isset(self::$pdo)) {
      try {
        self::$pdo = new PDO(
          'mysql:host=localhost;dbname=safeproject_db',  // ← ICI
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
}
?>
>>>>>>> 1d4cac9fc0810cddbf202db6e58d309ec65b58c3
>>>>>>> aab829f16e3aa2e6ba701ae4dd16b8c047cec2fa
