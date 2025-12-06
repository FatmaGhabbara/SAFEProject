<?php
require_once __DIR__ . '/../controller/usercontroller.php';

class AdminController {
    private $userController;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier si admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ../frontoffice/login.php');
            exit();
        }

        $this->userController = new UserController();
    }

    // RETOURNE DES OBJETS USER
    public function getAllUsers() {
        return $this->userController->listUsers();
    }

    // RETOURNE DES TABLEAUX
    public function getAllUsersArray() {
        return $this->userController->listUsersAsArray();
    }

    public function approveUser($id) {
        return $this->userController->approveUser($id);
    }

    public function blockUser($id) {
        return $this->userController->blockUser($id);
    }

    public function deleteUser($id) {
        return $this->userController->deleteUser($id);
    }

    public function getUser($id) {
        return $this->userController->getUser($id);
    }

    public function getRatingStats() {
        try {
            $conn = $this->userController->getConnection();
            
            $stmt = $conn->query("SHOW TABLES LIKE 'ratings'");
            if (!$stmt->fetch()) {
                return $this->getDemoRatingStats();
            }
            
            $stats = [];
            
            $sql1 = "SELECT COUNT(*) as total_ratings FROM ratings";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->execute();
            $totalRatings = $stmt1->fetch(PDO::FETCH_ASSOC);
            $stats['total_ratings'] = $totalRatings['total_ratings'] ?? 0;
            
            $sql2 = "SELECT AVG(rating) as average_rating FROM ratings";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->execute();
            $averageRating = $stmt2->fetch(PDO::FETCH_ASSOC);
            $stats['average_rating'] = round($averageRating['average_rating'] ?? 0, 1);
            
            $sql3 = "SELECT rating, COUNT(*) as count FROM ratings GROUP BY rating ORDER BY rating";
            $stmt3 = $conn->prepare($sql3);
            $stmt3->execute();
            $stats['distribution'] = $stmt3->fetchAll(PDO::FETCH_ASSOC);
            
            $sql4 = "SELECT COUNT(DISTINCT user_id) as users_with_ratings FROM ratings";
            $stmt4 = $conn->prepare($sql4);
            $stmt4->execute();
            $usersWithRatings = $stmt4->fetch(PDO::FETCH_ASSOC);
            $stats['users_with_ratings'] = $usersWithRatings['users_with_ratings'] ?? 0;
            
            $stats['demo_data'] = false;
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("AdminController::getRatingStats() - Erreur: " . $e->getMessage());
            return $this->getDemoRatingStats();
        }
    }

    private function getDemoRatingStats() {
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
}
?>