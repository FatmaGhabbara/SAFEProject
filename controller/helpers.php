<?php
/**
 * ============================================
 * CONTROLLER HELPERS
 * SAFEProject - Business Logic Functions
 * All data fetching and business logic goes here
 * ============================================
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/SupportRequest.php';
require_once __DIR__ . '/../model/SupportMessage.php';

// ============================================
// USER FUNCTIONS
// ============================================

/**
 * Find user by email
 * @param string $email
 * @return User|null
 */
function findUserByEmail($email) {
    $db = getDB();
    
    try {
        $sql = "SELECT * FROM utilisateurs WHERE email = :email";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $data = $stmt->fetch();
        if ($data) {
            $user = new User();
            $user->hydrate($data);
            return $user;
        }
        
        return null;
    } catch (PDOException $e) {
        logAction("Error finding user by email: " . $e->getMessage(), 'error');
        return null;
    }
}

/**
 * Authenticate user
 * @param string $email
 * @param string $password
 * @return User|null
 */
function authenticateUser($email, $password) {
    $user = findUserByEmail($email);
    
    if ($user && $user->verifyPassword($password)) {
        logAction("User authenticated: {$user->getEmail()}", 'info');
        return $user;
    }
    
    logAction("Failed authentication attempt for email: $email", 'warning');
    return null;
}

/**
 * Find all users
 * @return array Array of User objects
 */
function findAllUsers() {
    $db = getDB();
    
    try {
        $sql = "SELECT * FROM utilisateurs ORDER BY nom ASC, prenom ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        $users = [];
        while ($data = $stmt->fetch()) {
            $user = new User();
            $user->hydrate($data);
            $users[] = $user;
        }
        
        return $users;
    } catch (PDOException $e) {
        logAction("Error finding all users: " . $e->getMessage(), 'error');
        return [];
    }
}

/**
 * Find users by role
 * @param string $role
 * @return array Array of User objects
 */
function findUsersByRole($role) {
    $db = getDB();
    
    try {
        $sql = "SELECT * FROM utilisateurs WHERE role = :role ORDER BY nom ASC";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        
        $users = [];
        while ($data = $stmt->fetch()) {
            $user = new User();
            $user->hydrate($data);
            $users[] = $user;
        }
        
        return $users;
    } catch (PDOException $e) {
        logAction("Error finding users by role: " . $e->getMessage(), 'error');
        return [];
    }
}

// ============================================
// SUPPORT REQUEST FUNCTIONS
// ============================================

/**
 * Find support requests by user
 * @param int $user_id
 * @return array Array of SupportRequest objects
 */
function findSupportRequestsByUser($user_id) {
    $db = getDB();
    
    try {
        $sql = "SELECT * FROM support_requests WHERE user_id = :user_id ORDER BY date_creation DESC";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $requests = [];
        while ($data = $stmt->fetch()) {
            $request = new SupportRequest();
            $request->hydrate($data);
            $requests[] = $request;
        }
        
        return $requests;
    } catch (PDOException $e) {
        logAction("Error finding requests by user: " . $e->getMessage(), 'error');
        return [];
    }
}

/**
 * Find support requests by counselor (counselor_id is now user_id)
 * @param int $counselor_user_id
 * @return array Array of SupportRequest objects
 */
function findSupportRequestsByCounselor($counselor_user_id) {
    $db = getDB();
    
    try {
        $sql = "SELECT * FROM support_requests WHERE counselor_user_id = :counselor_user_id ORDER BY date_creation DESC";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':counselor_user_id', $counselor_user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $requests = [];
        while ($data = $stmt->fetch()) {
            $request = new SupportRequest();
            $request->hydrate($data);
            $requests[] = $request;
        }
        
        return $requests;
    } catch (PDOException $e) {
        logAction("Error finding requests by counselor: " . $e->getMessage(), 'error');
        return [];
    }
}

/**
 * Find all support requests
 * @return array Array of SupportRequest objects
 */
function findAllSupportRequests() {
    $db = getDB();
    
    try {
        $sql = "SELECT * FROM support_requests ORDER BY date_creation DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        $requests = [];
        while ($data = $stmt->fetch()) {
            $request = new SupportRequest();
            $request->hydrate($data);
            $requests[] = $request;
        }
        
        return $requests;
    } catch (PDOException $e) {
        logAction("Error finding all requests: " . $e->getMessage(), 'error');
        return [];
    }
}

/**
 * Find support requests by status
 * @param string $status
 * @return array Array of SupportRequest objects
 */
function findSupportRequestsByStatus($status) {
    $db = getDB();
    
    try {
        $sql = "SELECT * FROM support_requests WHERE statut = :status ORDER BY date_creation DESC";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        
        $requests = [];
        while ($data = $stmt->fetch()) {
            $request = new SupportRequest();
            $request->hydrate($data);
            $requests[] = $request;
        }
        
        return $requests;
    } catch (PDOException $e) {
        logAction("Error finding requests by status: " . $e->getMessage(), 'error');
        return [];
    }
}

/**
 * Find pending support requests
 * @return array Array of SupportRequest objects
 */
function findPendingSupportRequests() {
    return findSupportRequestsByStatus('en_attente');
}

// ============================================
// SUPPORT MESSAGE FUNCTIONS
// ============================================

/**
 * Find messages by support request
 * @param int $request_id
 * @return array Array of SupportMessage objects
 */
function findMessagesByRequest($request_id) {
    $db = getDB();
    
    try {
        $sql = "SELECT * FROM support_messages 
                WHERE support_request_id = :request_id 
                ORDER BY date_envoi ASC";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $messages = [];
        while ($data = $stmt->fetch()) {
            $message = new SupportMessage();
            $message->hydrate($data);
            $messages[] = $message;
        }
        
        return $messages;
    } catch (PDOException $e) {
        logAction("Error finding messages by request: " . $e->getMessage(), 'error');
        return [];
    }
}

/**
 * Count unread messages for a request
 * @param int $request_id
 * @param int $exclude_sender_id
 * @return int
 */
function countUnreadMessages($request_id, $exclude_sender_id) {
    $db = getDB();
    
    try {
        $sql = "SELECT COUNT(*) as total FROM support_messages 
                WHERE support_request_id = :request_id 
                  AND sender_id != :sender_id 
                  AND lu = FALSE";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        $stmt->bindParam(':sender_id', $exclude_sender_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return (int) $result['total'];
    } catch (PDOException $e) {
        return 0;
    }
}

// ============================================
// COUNSELOR FUNCTIONS (using counselors table)
// ============================================

/**
 * Check if user is already a counselor
 * @param int $user_id
 * @return bool
 */
function isUserCounselor($user_id) {
    $db = getDB();
    
    try {
        $sql = "SELECT COUNT(*) as count FROM utilisateurs WHERE id = :user_id AND role = 'counselor'";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return (int) $result['count'] > 0;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get counselor data by user_id (returns User object with counselor data)
 * @param int $user_id
 * @return User|null
 */
function getCounselorByUserId($user_id) {
    try {
        $user = new User($user_id);
        if ($user->getId() && $user->getRole() === 'counselor') {
            return $user;
        }
        return null;
    } catch (Exception $e) {
        logAction("Error getting counselor by user_id: " . $e->getMessage(), 'error');
        return null;
    }
}

/**
 * Get counselor data by user_id (counselor_id is now user_id)
 * @param int $counselor_user_id
 * @return User|null
 */
function getCounselorById($counselor_user_id) {
    try {
        $user = new User($counselor_user_id);
        if ($user->getId() && $user->getRole() === 'counselor') {
            return $user;
        }
        return null;
    } catch (Exception $e) {
        logAction("Error getting counselor by id: " . $e->getMessage(), 'error');
        return null;
    }
}

/**
 * Get all counselors (returns array of User objects)
 * @return array Array of User objects
 */
function getAllCounselors() {
    return findUsersByRole('counselor');
}

/**
 * Get available counselors (returns array of User objects)
 * @return array Array of User objects
 */
function getAvailableCounselors() {
    $counselors = findUsersByRole('counselor');
    return array_filter($counselors, function($user) {
        return $user->getStatutCounselor() === 'actif';
    });
}

/**
 * Create counselor profile (updates user with counselor data)
 * @param int $user_id
 * @param string $specialite
 * @param string $biographie
 * @param string $statut
 * @return bool
 */
function createCounselor($user_id, $specialite, $biographie, $statut = 'actif') {
    try {
        // Check if already exists
        if (isUserCounselor($user_id)) {
            return false;
        }
        
        $user = new User($user_id);
        if (!$user->getId()) {
            return false;
        }
        
        // Update user with counselor data
        $user->setRole('counselor');
        $user->setSpecialite($specialite);
        $user->setBiographie($biographie);
        $user->setStatutCounselor($statut);
        $user->setNombreDemandesActives(0);
        
        if ($user->save()) {
            logAction("Counselor profile created for user $user_id", 'info');
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        logAction("Error creating counselor: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Update counselor profile (counselor_id is now user_id)
 * @param int $counselor_user_id
 * @param string $specialite
 * @param string $biographie
 * @param string $statut
 * @return bool
 */
function updateCounselor($counselor_user_id, $specialite, $biographie, $statut) {
    try {
        $user = new User($counselor_user_id);
        if (!$user->getId() || $user->getRole() !== 'counselor') {
            return false;
        }
        
        $user->setSpecialite($specialite);
        $user->setBiographie($biographie);
        $user->setStatutCounselor($statut);
        
        if ($user->save()) {
            logAction("Counselor $counselor_user_id updated", 'info');
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        logAction("Error updating counselor: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Delete counselor (removes counselor data, changes role to 'user')
 * @param int $counselor_user_id
 * @return bool
 */
function deleteCounselor($counselor_user_id) {
    try {
        $user = new User($counselor_user_id);
        if (!$user->getId() || $user->getRole() !== 'counselor') {
            return false;
        }
        
        // Remove counselor data
        $user->setRole('user');
        $user->setSpecialite(null);
        $user->setBiographie(null);
        $user->setStatutCounselor(null);
        $user->setNombreDemandesActives(0);
        
        if ($user->save()) {
            logAction("Counselor $counselor_user_id deleted (role changed to user)", 'warning');
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        logAction("Error deleting counselor: " . $e->getMessage(), 'error');
        return false;
    }
}

// ============================================
// STATISTICS FUNCTIONS
// ============================================

/**
 * Get global statistics
 * @return array
 */
function getGlobalStats() {
    $db = getDB();
    
    try {
        $sql = "SELECT 
                    COUNT(*) as total_demandes,
                    COUNT(CASE WHEN statut = 'en_attente' THEN 1 END) as demandes_en_attente,
                    COUNT(CASE WHEN statut = 'assignee' THEN 1 END) as demandes_assignees,
                    COUNT(CASE WHEN statut = 'en_cours' THEN 1 END) as demandes_en_cours,
                    COUNT(CASE WHEN statut = 'terminee' THEN 1 END) as demandes_terminees,
                    COUNT(CASE WHEN statut = 'annulee' THEN 1 END) as demandes_annulees
                FROM support_requests";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetch();
    } catch (PDOException $e) {
        logAction("Error getting global stats: " . $e->getMessage(), 'error');
        return [
            'total_demandes' => 0,
            'demandes_en_attente' => 0,
            'demandes_assignees' => 0,
            'demandes_en_cours' => 0,
            'demandes_terminees' => 0,
            'demandes_annulees' => 0
        ];
    }
}

/**
 * Get top counselors
 * @param int $limit
 * @return array
 */
function getTopCounselors($limit = 5) {
    $db = getDB();
    
    try {
        $sql = "SELECT 
                    u.id,
                    u.nom,
                    u.prenom,
                    u.specialite,
                    COUNT(sr.id) as total_demandes,
                    COUNT(CASE WHEN sr.statut = 'terminee' THEN 1 END) as demandes_terminees,
                    AVG(CASE 
                        WHEN sr.date_resolution IS NOT NULL AND sr.date_assignation IS NOT NULL 
                        THEN TIMESTAMPDIFF(HOUR, sr.date_assignation, sr.date_resolution) 
                    END) as temps_moyen
                FROM utilisateurs u
                LEFT JOIN support_requests sr ON u.id = sr.counselor_user_id
                WHERE u.role = 'counselor' AND u.statut_counselor = 'actif'
                GROUP BY u.id, u.nom, u.prenom, u.specialite
                ORDER BY demandes_terminees DESC, total_demandes DESC
                LIMIT :limit";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        logAction("Error getting top counselors: " . $e->getMessage(), 'error');
        return [];
    }
}

/**
 * Get monthly statistics
 * @param int $months
 * @return array
 */
function getMonthlyStats($months = 6) {
    $db = getDB();
    
    try {
        $sql = "SELECT 
                    DATE_FORMAT(date_creation, '%Y-%m') as mois,
                    DATE_FORMAT(date_creation, '%M %Y') as mois_label,
                    COUNT(*) as total,
                    COUNT(CASE WHEN statut = 'terminee' THEN 1 END) as terminees
                FROM support_requests
                WHERE date_creation >= DATE_SUB(NOW(), INTERVAL :months MONTH)
                GROUP BY DATE_FORMAT(date_creation, '%Y-%m'), DATE_FORMAT(date_creation, '%M %Y')
                ORDER BY mois ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':months', $months, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        logAction("Error getting monthly stats: " . $e->getMessage(), 'error');
        return [];
    }
}

/**
 * Get average response time
 * @return float
 */
function getAverageResponseTime() {
    $db = getDB();
    
    try {
        $sql = "SELECT AVG(TIMESTAMPDIFF(HOUR, date_assignation, date_resolution)) as temps_moyen
                FROM support_requests
                WHERE date_resolution IS NOT NULL 
                  AND date_assignation IS NOT NULL
                  AND statut = 'terminee'";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['temps_moyen'] ? (float) $result['temps_moyen'] : 0;
    } catch (PDOException $e) {
        logAction("Error getting average response time: " . $e->getMessage(), 'error');
        return 0;
    }
}

/**
 * Get counselor statistics (counselor_id is now user_id)
 * @param int $counselor_user_id
 * @return array
 */
function getCounselorStats($counselor_user_id) {
    $db = getDB();
    
    try {
        $sql = "SELECT 
                    COUNT(*) as total_demandes,
                    COUNT(CASE WHEN statut = 'terminee' THEN 1 END) as demandes_terminees,
                    COUNT(CASE WHEN statut IN ('assignee', 'en_cours') THEN 1 END) as demandes_actives,
                    AVG(CASE 
                        WHEN date_resolution IS NOT NULL AND date_assignation IS NOT NULL 
                        THEN TIMESTAMPDIFF(HOUR, date_assignation, date_resolution) 
                    END) as temps_resolution_moyen
                FROM support_requests
                WHERE counselor_user_id = :counselor_user_id";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':counselor_user_id', $counselor_user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    } catch (PDOException $e) {
        logAction("Error getting counselor stats: " . $e->getMessage(), 'error');
        return [
            'total_demandes' => 0,
            'demandes_terminees' => 0,
            'demandes_actives' => 0,
            'temps_resolution_moyen' => 0
        ];
    }
}

?>


