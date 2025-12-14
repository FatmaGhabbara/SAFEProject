<?php

require_once __DIR__ . '/../controller/usercontroller.php';
require_once __DIR__ . '/MailController.php';

class AdminController
{
    private UserController $userController;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vérification admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ../frontoffice/login.php');
            exit();
        }

        $this->userController = new UserController();
    }

    /* =======================
       USERS
    ======================= */
    public function getAllUsers()
    {
        return $this->userController->listUsers();
    }

    public function getAllUsersArray()
    {
        return $this->userController->listUsersAsArray();
    }

    public function getUser($id)
    {
        return $this->userController->getUserById($id);
    }

    public function deleteUser($id)
    {
        return $this->userController->deleteUser($id);
    }

    /* =======================
       APPROVE USER
    ======================= */
    public function approveUser($user_id): bool
    {
        if (!$this->userController->approveUser($user_id)) {
            return false;
        }

        $user = $this->userController->getUserById($user_id);
        if (!$user) {
            return false;
        }

        $this->sendApprovalEmail($user);
        $this->notifyAdminApproval($user);

        return true;
    }

    private function sendApprovalEmail($user): bool
    {
        $mail = new MailController();

        [$fullname, $email] = $this->extractUserIdentity($user);
        if (!$email) {
            return false;
        }

        $subject = "✅ Votre compte SafeSpace a été approuvé !";

        $body = $this->buildApprovalTemplate($fullname, $email);

        return $mail->sendEmail($email, $subject, $body, $fullname);
    }

    private function notifyAdminApproval($user): void
    {
        $mail = new MailController();
        [$fullname, $email, $id] = $this->extractUserIdentity($user, true);

        $subject = "📋 Compte approuvé - SafeSpace Admin";
        $body = "
            <h2>Compte approuvé</h2>
            <p><strong>Utilisateur :</strong> {$fullname}</p>
            <p><strong>Email :</strong> {$email}</p>
            <p><strong>ID :</strong> {$id}</p>
            <p><strong>Date :</strong> " . date('d/m/Y H:i') . "</p>
        ";

        $mail->sendEmail("admin@safespace.com", $subject, $body, "Admin SafeSpace");
    }

    /* =======================
       BLOCK USER
    ======================= */
    public function blockUser($user_id): bool
    {
        if (!$this->userController->blockUser($user_id)) {
            return false;
        }

        $user = $this->userController->getUserById($user_id);
        if (!$user) {
            return false;
        }

        $this->sendBlockEmail($user);
        $this->notifyAdminBlock($user);

        return true;
    }

    private function sendBlockEmail($user): bool
    {
        $mail = new MailController();

        [$fullname, $email] = $this->extractUserIdentity($user);
        if (!$email) {
            return false;
        }

        $subject = "⚠️ Votre compte SafeSpace a été bloqué";
        $body = $this->buildBlockTemplate($fullname, $email);

        return $mail->sendEmail($email, $subject, $body, $fullname);
    }

    private function notifyAdminBlock($user): void
    {
        $mail = new MailController();
        [$fullname, $email, $id] = $this->extractUserIdentity($user, true);

        $subject = "🚫 Compte bloqué - SafeSpace Admin";
        $body = "
            <h2>Compte bloqué</h2>
            <p><strong>Utilisateur :</strong> {$fullname}</p>
            <p><strong>Email :</strong> {$email}</p>
            <p><strong>ID :</strong> {$id}</p>
            <p><strong>Date :</strong> " . date('d/m/Y H:i') . "</p>
        ";

        $mail->sendEmail("admin@safespace.com", $subject, $body, "Admin SafeSpace");
    }

    /* =======================
       STATS
    ======================= */
    public function getRatingStats(): array
    {
        try {
            $conn = $this->userController->getConnection();

            $check = $conn->query("SHOW TABLES LIKE 'ratings'");
            if (!$check->fetch()) {
                return $this->getDemoRatingStats();
            }

            return [
                'total_ratings' => (int)$conn->query("SELECT COUNT(*) FROM ratings")->fetchColumn(),
                'average_rating' => round((float)$conn->query("SELECT AVG(rating) FROM ratings")->fetchColumn(), 1),
                'distribution' => $conn->query("SELECT rating, COUNT(*) count FROM ratings GROUP BY rating ORDER BY rating")->fetchAll(PDO::FETCH_ASSOC),
                'users_with_ratings' => (int)$conn->query("SELECT COUNT(DISTINCT user_id) FROM ratings")->fetchColumn(),
                'demo_data' => false
            ];
        } catch (Exception $e) {
            return $this->getDemoRatingStats();
        }
    }

    private function getDemoRatingStats(): array
    {
        return [
            'total_ratings' => 42,
            'average_rating' => 4.2,
            'distribution' => [
                ['rating' => 5, 'count' => 20],
                ['rating' => 4, 'count' => 15],
                ['rating' => 3, 'count' => 5],
                ['rating' => 2, 'count' => 1],
                ['rating' => 1, 'count' => 1]
            ],
            'users_with_ratings' => 38,
            'demo_data' => true
        ];
    }

    /* =======================
       HELPERS
    ======================= */
    private function extractUserIdentity($user, bool $withId = false): array
    {
        $fullname = $user->getFullname() ?? $user->getNom() ?? 'Utilisateur';
        $email = $user->getEmail() ?? '';
        $id = method_exists($user, 'getId') ? $user->getId() : null;

        return $withId ? [$fullname, $email, $id] : [$fullname, $email];
    }

    private function buildApprovalTemplate(string $name, string $email): string
    {
        return "<h2>Bonjour {$name}</h2>
                <p>Votre compte SafeSpace est maintenant <strong>actif</strong>.</p>
                <a href='http://localhost/SAFEProject/view/frontoffice/login.php'>Se connecter</a>";
    }

    private function buildBlockTemplate(string $name, string $email): string
    {
        return "<h2>Bonjour {$name}</h2>
                <p>Votre compte SafeSpace a été <strong>bloqué</strong>.</p>
                <p>Contactez support@safespace.com</p>";
    }
}