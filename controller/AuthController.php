<?php
require_once 'C:/xampp/htdocs/SAFEProject/model/user.php';
require_once 'C:/xampp/htdocs/SAFEProject/config.php';

class AuthController {
    private $db;
    private $userModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new User($this->db);
    }

    // 🔹 Connexion
    public function login(string $email, string $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return "Aucun compte trouvé avec cette adresse e-mail.";
        }

        if ($user['status'] === 'en attente') {
            return "Votre compte n’a pas encore été validé par l’administrateur.";
        }

        if ($user['status'] === 'bloqué') {
            return "Votre compte est bloqué. Contactez l’administrateur.";
        }

        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return true;
        } else {
            return "Mot de passe incorrect.";
        }
    }

    // 🔹 Inscription
    public function register(string $firstname, string $lastname, string $email, string $password) {
        // Vérifie si l'utilisateur existe déjà
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return "Cette adresse e-mail est déjà utilisée.";
        }

        // Hash du mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insertion avec statut "en attente"
        $query = "INSERT INTO users (username, email, password, role, status) 
                  VALUES (:username, :email, :password, 'membre', 'en attente')";
        $stmt = $this->db->prepare($query);
        $username = $firstname . ' ' . $lastname;
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $hashed_password);

        if ($stmt->execute()) {
            // Envoi d’un mail de confirmation
            $subject = "Demande d’inscription reçue | SafeSpace";
            $message = "
                Bonjour $firstname,<br><br>
                Merci pour votre inscription sur <strong>SafeSpace</strong>.<br>
                Votre compte est actuellement en attente de validation par un administrateur.<br>
                Vous recevrez un e-mail dès qu’il sera activé.<br><br>
                Cordialement,<br>L’équipe SafeSpace.
            ";

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: SafeSpace <no-reply@safespace.com>\r\n";

            mail($email, $subject, $message, $headers);

            return true;
        } else {
            return "Une erreur est survenue lors de l’inscription.";
        }
    }
}
?>

?>
