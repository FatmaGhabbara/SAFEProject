<?php
/**
 * ============================================
 * FONCTIONS DU MODULE SUPPORT PSYCHOLOGIQUE
 * SAFEProject - Toutes les fonctions CRUD
 * ============================================
 */

require_once __DIR__ . '/config.php';

// ============================================
// FONCTIONS POUR LES DEMANDES DE SUPPORT
// ============================================

/**
 * Créer une nouvelle demande de support
 * @param int $user_id
 * @param string $titre
 * @param string $description
 * @param string $urgence (basse, moyenne, haute)
 * @return int|false ID de la demande créée ou false
 */
function createSupportRequest($user_id, $titre, $description, $urgence = 'moyenne') {
    try {
        $db = getDB();
        
        // Validation
        if (empty($titre) || empty($description)) {
            return false;
        }
        
        $titre = cleanInput($titre);
        $description = cleanInput($description);
        
        if (!in_array($urgence, ['basse', 'moyenne', 'haute'])) {
            $urgence = 'moyenne';
        }
        
        $sql = "INSERT INTO support_requests (user_id, titre, description, urgence, statut, date_creation) 
                VALUES (:user_id, :titre, :description, :urgence, 'en_attente', NOW())";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':titre', $titre, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':urgence', $urgence, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $requestId = $db->lastInsertId();
            logAction("Nouvelle demande de support créée (ID: $requestId) par utilisateur $user_id", 'info');
            return $requestId;
        }
        
        return false;
        
    } catch (PDOException $e) {
        logAction("Erreur lors de la création d'une demande: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Récupérer une demande de support par son ID
 * @param int $id
 * @return array|false
 */
function getSupportRequestById($id) {
    try {
        $db = getDB();
        
        $sql = "SELECT sr.*, 
                       u.nom as user_nom, u.prenom as user_prenom, u.email as user_email,
                       c.id as counselor_id, uc.nom as counselor_nom, uc.prenom as counselor_prenom,
                       c.specialite as counselor_specialite
                FROM support_requests sr
                INNER JOIN utilisateurs u ON sr.user_id = u.id
                LEFT JOIN counselors c ON sr.counselor_id = c.id
                LEFT JOIN utilisateurs uc ON c.user_id = uc.id
                WHERE sr.id = :id";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        logAction("Erreur lors de la récupération de la demande $id: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Récupérer toutes les demandes d'un utilisateur
 * @param int $user_id
 * @return array
 */
function getSupportRequestsByUser($user_id) {
    try {
        $db = getDB();
        
        $sql = "SELECT sr.*, 
                       c.id as counselor_id, uc.nom as counselor_nom, uc.prenom as counselor_prenom,
                       c.specialite as counselor_specialite
                FROM support_requests sr
                LEFT JOIN counselors c ON sr.counselor_id = c.id
                LEFT JOIN utilisateurs uc ON c.user_id = uc.id
                WHERE sr.user_id = :user_id
                ORDER BY sr.date_creation DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        logAction("Erreur lors de la récupération des demandes de l'utilisateur $user_id: " . $e->getMessage(), 'error');
        return [];
    }
}

/**
 * Récupérer toutes les demandes de support avec filtres
 * @param array $filters (statut, urgence, counselor_id, date_debut, date_fin, search)
 * @return array
 */
function getAllSupportRequests($filters = []) {
    try {
        $db = getDB();
        
        $sql = "SELECT sr.*, 
                       u.nom as user_nom, u.prenom as user_prenom, u.email as user_email,
                       c.id as counselor_id, uc.nom as counselor_nom, uc.prenom as counselor_prenom,
                       c.specialite as counselor_specialite
                FROM support_requests sr
                INNER JOIN utilisateurs u ON sr.user_id = u.id
                LEFT JOIN counselors c ON sr.counselor_id = c.id
                LEFT JOIN utilisateurs uc ON c.user_id = uc.id
                WHERE 1=1";
        
        $params = [];
        
        // Filtrer par statut
        if (!empty($filters['statut'])) {
            $sql .= " AND sr.statut = :statut";
            $params[':statut'] = $filters['statut'];
        }
        
        // Filtrer par urgence
        if (!empty($filters['urgence'])) {
            $sql .= " AND sr.urgence = :urgence";
            $params[':urgence'] = $filters['urgence'];
        }
        
        // Filtrer par conseiller
        if (!empty($filters['counselor_id'])) {
            $sql .= " AND sr.counselor_id = :counselor_id";
            $params[':counselor_id'] = $filters['counselor_id'];
        }
        
        // Filtrer par date de début
        if (!empty($filters['date_debut'])) {
            $sql .= " AND sr.date_creation >= :date_debut";
            $params[':date_debut'] = $filters['date_debut'];
        }
        
        // Filtrer par date de fin
        if (!empty($filters['date_fin'])) {
            $sql .= " AND sr.date_creation <= :date_fin";
            $params[':date_fin'] = $filters['date_fin'];
        }
        
        // Recherche dans le titre ou la description
        if (!empty($filters['search'])) {
            $sql .= " AND (sr.titre LIKE :search OR sr.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        $sql .= " ORDER BY sr.date_creation DESC";
        
        $stmt = $db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        logAction("Erreur lors de la récupération de toutes les demandes: " . $e->getMessage(), 'error');
        return [];
    }
}

/**
 * Mettre à jour une demande de support
 * @param int $id
 * @param array $data
 * @return bool
 */
function updateSupportRequest($id, $data) {
    try {
        $db = getDB();
        
        $allowedFields = ['titre', 'description', 'urgence', 'statut', 'notes_admin', 'counselor_id'];
        $updates = [];
        $params = [':id' => $id];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updates[] = "$field = :$field";
                $params[":$field"] = $value;
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $sql = "UPDATE support_requests SET " . implode(', ', $updates) . " WHERE id = :id";
        
        $stmt = $db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $result = $stmt->execute();
        
        if ($result) {
            logAction("Demande de support $id mise à jour", 'info');
        }
        
        return $result;
        
    } catch (PDOException $e) {
        logAction("Erreur lors de la mise à jour de la demande $id: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Supprimer une demande de support
 * @param int $id
 * @return bool
 */
function deleteSupportRequest($id) {
    try {
        $db = getDB();
        
        $sql = "DELETE FROM support_requests WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        $result = $stmt->execute();
        
        if ($result) {
            logAction("Demande de support $id supprimée", 'warning');
        }
        
        return $result;
        
    } catch (PDOException $e) {
        logAction("Erreur lors de la suppression de la demande $id: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Assigner un conseiller à une demande
 * @param int $request_id
 * @param int $counselor_id
 * @return bool
 */
function assignCounselor($request_id, $counselor_id) {
    try {
        $db = getDB();
        
        $sql = "UPDATE support_requests 
                SET counselor_id = :counselor_id, 
                    statut = 'assignee',
                    date_assignation = NOW()
                WHERE id = :request_id";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':counselor_id', $counselor_id, PDO::PARAM_INT);
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        
        $result = $stmt->execute();
        
        if ($result) {
            logAction("Conseiller $counselor_id assigné à la demande $request_id", 'info');
        }
        
        return $result;
        
    } catch (PDOException $e) {
        logAction("Erreur lors de l'assignation du conseiller: " . $e->getMessage(), 'error');
        return false;
    }
}

// ============================================
// FONCTIONS POUR LES CONSEILLERS
// ============================================

/**
 * Créer un nouveau conseiller
 * @param int $user_id
 * @param string $specialite
 * @param string $biographie
 * @param string $statut
 * @return int|false ID du conseiller créé ou false
 */
function createCounselor($user_id, $specialite, $biographie = '', $statut = 'actif') {
    try {
        $db = getDB();
        
        // Vérifier si l'utilisateur n'est pas déjà conseiller
        if (isCounselor($user_id)) {
            return false;
        }
        
        $specialite = cleanInput($specialite);
        $biographie = cleanInput($biographie);
        
        if (!in_array($statut, ['actif', 'inactif', 'en_pause'])) {
            $statut = 'actif';
        }
        
        $sql = "INSERT INTO counselors (user_id, specialite, biographie, statut, date_inscription) 
                VALUES (:user_id, :specialite, :biographie, :statut, NOW())";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':specialite', $specialite, PDO::PARAM_STR);
        $stmt->bindParam(':biographie', $biographie, PDO::PARAM_STR);
        $stmt->bindParam(':statut', $statut, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $counselorId = $db->lastInsertId();
            logAction("Nouveau conseiller créé (ID: $counselorId) pour utilisateur $user_id", 'info');
            return $counselorId;
        }
        
        return false;
        
    } catch (PDOException $e) {
        logAction("Erreur lors de la création d'un conseiller: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Vérifier si un utilisateur est déjà conseiller
 * @param int $user_id
 * @return bool
 */
function isCounselor($user_id) {
    try {
        $db = getDB();
        
        $sql = "SELECT COUNT(*) FROM counselors WHERE user_id = :user_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
        
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Récupérer un conseiller par son ID
 * @param int $id
 * @return array|false
 */
function getCounselorById($id) {
    try {
        $db = getDB();
        
        $sql = "SELECT c.*, u.nom, u.prenom, u.email
                FROM counselors c
                INNER JOIN utilisateurs u ON c.user_id = u.id
                WHERE c.id = :id";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        logAction("Erreur lors de la récupération du conseiller $id: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Récupérer tous les conseillers
 * @param bool $active_only Récupérer uniquement les conseillers actifs
 * @return array
 */
function getAllCounselors($active_only = false) {
    try {
        $db = getDB();
        
        $sql = "SELECT c.*, u.nom, u.prenom, u.email
                FROM counselors c
                INNER JOIN utilisateurs u ON c.user_id = u.id";
        
        if ($active_only) {
            $sql .= " WHERE c.statut = 'actif'";
        }
        
        $sql .= " ORDER BY c.nombre_demandes_actives ASC, u.nom ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        logAction("Erreur lors de la récupération des conseillers: " . $e->getMessage(), 'error');
        return [];
    }
}

/**
 * Mettre à jour un conseiller
 * @param int $id
 * @param array $data
 * @return bool
 */
function updateCounselor($id, $data) {
    try {
        $db = getDB();
        
        $allowedFields = ['specialite', 'biographie', 'disponibilite', 'statut'];
        $updates = [];
        $params = [':id' => $id];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updates[] = "$field = :$field";
                $params[":$field"] = $value;
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $sql = "UPDATE counselors SET " . implode(', ', $updates) . " WHERE id = :id";
        
        $stmt = $db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $result = $stmt->execute();
        
        if ($result) {
            logAction("Conseiller $id mis à jour", 'info');
        }
        
        return $result;
        
    } catch (PDOException $e) {
        logAction("Erreur lors de la mise à jour du conseiller $id: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Supprimer un conseiller
 * @param int $id
 * @return bool
 */
function deleteCounselor($id) {
    try {
        $db = getDB();
        
        $sql = "DELETE FROM counselors WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        $result = $stmt->execute();
        
        if ($result) {
            logAction("Conseiller $id supprimé", 'warning');
        }
        
        return $result;
        
    } catch (PDOException $e) {
        logAction("Erreur lors de la suppression du conseiller $id: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Récupérer les statistiques d'un conseiller
 * @param int $counselor_id
 * @return array
 */
function getCounselorStats($counselor_id) {
    try {
        $db = getDB();
        
        $sql = "SELECT 
                    COUNT(*) as total_demandes,
                    COUNT(CASE WHEN statut = 'terminee' THEN 1 END) as demandes_terminees,
                    COUNT(CASE WHEN statut IN ('assignee', 'en_cours') THEN 1 END) as demandes_actives,
                    AVG(CASE 
                        WHEN date_resolution IS NOT NULL AND date_assignation IS NOT NULL 
                        THEN TIMESTAMPDIFF(HOUR, date_assignation, date_resolution) 
                    END) as temps_resolution_moyen
                FROM support_requests
                WHERE counselor_id = :counselor_id";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':counselor_id', $counselor_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        logAction("Erreur lors de la récupération des stats du conseiller $counselor_id: " . $e->getMessage(), 'error');
        return [];
    }
}

// ============================================
// FONCTIONS POUR LES MESSAGES
// ============================================

/**
 * Envoyer un message de suivi
 * @param int $request_id
 * @param int $sender_id
 * @param string $message
 * @return int|false ID du message créé ou false
 */
function sendSupportMessage($request_id, $sender_id, $message) {
    try {
        $db = getDB();
        
        $message = cleanInput($message);
        
        if (empty($message)) {
            return false;
        }
        
        $sql = "INSERT INTO support_messages (support_request_id, sender_id, message, date_envoi) 
                VALUES (:request_id, :sender_id, :message, NOW())";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        $stmt->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $messageId = $db->lastInsertId();
            logAction("Nouveau message envoyé (ID: $messageId) pour la demande $request_id", 'info');
            return $messageId;
        }
        
        return false;
        
    } catch (PDOException $e) {
        logAction("Erreur lors de l'envoi du message: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Récupérer tous les messages d'une demande
 * @param int $request_id
 * @return array
 */
function getMessagesByRequest($request_id) {
    try {
        $db = getDB();
        
        $sql = "SELECT m.*, u.nom, u.prenom
                FROM support_messages m
                INNER JOIN utilisateurs u ON m.sender_id = u.id
                WHERE m.support_request_id = :request_id
                ORDER BY m.date_envoi ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        logAction("Erreur lors de la récupération des messages: " . $e->getMessage(), 'error');
        return [];
    }
}

/**
 * Marquer un message comme lu
 * @param int $message_id
 * @return bool
 */
function markMessageAsRead($message_id) {
    try {
        $db = getDB();
        
        $sql = "UPDATE support_messages SET lu = TRUE WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $message_id, PDO::PARAM_INT);
        
        return $stmt->execute();
        
    } catch (PDOException $e) {
        logAction("Erreur lors du marquage du message comme lu: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Compter les messages non lus pour une demande
 * @param int $request_id
 * @param int $user_id
 * @return int
 */
function getUnreadMessagesCount($request_id, $user_id) {
    try {
        $db = getDB();
        
        $sql = "SELECT COUNT(*) FROM support_messages 
                WHERE support_request_id = :request_id 
                AND sender_id != :user_id 
                AND lu = FALSE";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchColumn();
        
    } catch (PDOException $e) {
        return 0;
    }
}

// ============================================
// FONCTIONS POUR LES STATISTIQUES
// ============================================

/**
 * Récupérer les statistiques globales
 * @return array
 */
function getGlobalStats() {
    try {
        $db = getDB();
        
        $sql = "SELECT 
                    COUNT(*) as total_demandes,
                    COUNT(CASE WHEN statut = 'en_attente' THEN 1 END) as demandes_en_attente,
                    COUNT(CASE WHEN statut = 'assignee' THEN 1 END) as demandes_assignees,
                    COUNT(CASE WHEN statut = 'en_cours' THEN 1 END) as demandes_en_cours,
                    COUNT(CASE WHEN statut = 'terminee' THEN 1 END) as demandes_terminees,
                    COUNT(CASE WHEN statut = 'annulee' THEN 1 END) as demandes_annulees,
                    COUNT(CASE WHEN urgence = 'haute' AND statut NOT IN ('terminee', 'annulee') THEN 1 END) as demandes_urgentes
                FROM support_requests";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        logAction("Erreur lors de la récupération des statistiques globales: " . $e->getMessage(), 'error');
        return [];
    }
}

/**
 * Calculer le temps de réponse moyen (en heures)
 * @return float
 */
function getAverageResponseTime() {
    try {
        $db = getDB();
        
        $sql = "SELECT AVG(TIMESTAMPDIFF(HOUR, date_creation, date_assignation)) as moyenne
                FROM support_requests
                WHERE date_assignation IS NOT NULL";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return round($result['moyenne'] ?? 0, 2);
        
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Compter les demandes en attente
 * @return int
 */
function getPendingRequestsCount() {
    try {
        $db = getDB();
        
        $sql = "SELECT COUNT(*) FROM support_requests WHERE statut = 'en_attente'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchColumn();
        
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Récupérer les statistiques par mois
 * @param int $months Nombre de mois à récupérer
 * @return array
 */
function getMonthlyStats($months = 6) {
    try {
        $db = getDB();
        
        $sql = "SELECT 
                    DATE_FORMAT(date_creation, '%Y-%m') as mois,
                    COUNT(*) as total,
                    COUNT(CASE WHEN statut = 'terminee' THEN 1 END) as terminees
                FROM support_requests
                WHERE date_creation >= DATE_SUB(NOW(), INTERVAL :months MONTH)
                GROUP BY DATE_FORMAT(date_creation, '%Y-%m')
                ORDER BY mois ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':months', $months, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        logAction("Erreur lors de la récupération des statistiques mensuelles: " . $e->getMessage(), 'error');
        return [];
    }
}

/**
 * Récupérer les conseillers les plus performants
 * @param int $limit Nombre de conseillers à récupérer
 * @return array
 */
function getTopCounselors($limit = 5) {
    try {
        $db = getDB();
        
        $sql = "SELECT 
                    c.id, u.nom, u.prenom, c.specialite,
                    COUNT(sr.id) as total_demandes,
                    COUNT(CASE WHEN sr.statut = 'terminee' THEN 1 END) as demandes_terminees,
                    AVG(CASE 
                        WHEN sr.date_resolution IS NOT NULL AND sr.date_assignation IS NOT NULL 
                        THEN TIMESTAMPDIFF(HOUR, sr.date_assignation, sr.date_resolution) 
                    END) as temps_moyen
                FROM counselors c
                INNER JOIN utilisateurs u ON c.user_id = u.id
                LEFT JOIN support_requests sr ON c.id = sr.counselor_id
                WHERE c.statut = 'actif'
                GROUP BY c.id, u.nom, u.prenom, c.specialite
                ORDER BY demandes_terminees DESC
                LIMIT :limit";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        logAction("Erreur lors de la récupération des meilleurs conseillers: " . $e->getMessage(), 'error');
        return [];
    }
}

?>

