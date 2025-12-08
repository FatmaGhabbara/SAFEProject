-- ============================================
-- INITIALISATION COMPLÈTE - SAFEProject
-- Création de la table utilisateurs + Module Support
-- ============================================

-- Créer la table utilisateurs si elle n'existe pas
-- Cette table contient TOUS les utilisateurs (user, admin, counselor)
-- Les colonnes counselor_* sont NULL pour les non-conseillers
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin', 'counselor') DEFAULT 'user',
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif',
    -- Colonnes spécifiques aux conseillers (NULL si role != 'counselor')
    specialite VARCHAR(255) DEFAULT NULL,
    biographie TEXT DEFAULT NULL,
    disponibilite BOOLEAN DEFAULT NULL,
    nombre_demandes_actives INT DEFAULT 0,
    statut_counselor ENUM('actif', 'inactif', 'en_pause') DEFAULT NULL,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_statut_counselor (statut_counselor),
    INDEX idx_disponibilite (disponibilite)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insérer des utilisateurs de test
-- Mot de passe pour TOUS les utilisateurs : dddd
INSERT IGNORE INTO utilisateurs (id, nom, prenom, email, password, role, statut, specialite, biographie, disponibilite, statut_counselor) VALUES
(1, 'Admin', 'Système', 'admin@safeproject.com', '$2y$10$pJpHEzguq4INFPnqZn6rCeEB2.fFVwEs/gaSTKxa5ajyug6wZ.y9i', 'admin', 'actif', NULL, NULL, NULL, NULL),
(2, 'Dupont', 'Jean', 'jean.dupont@example.com', '$2y$10$pJpHEzguq4INFPnqZn6rCeEB2.fFVwEs/gaSTKxa5ajyug6wZ.y9i', 'user', 'actif', NULL, NULL, NULL, NULL),
(3, 'Martin', 'Marie', 'marie.martin@example.com', '$2y$10$pJpHEzguq4INFPnqZn6rCeEB2.fFVwEs/gaSTKxa5ajyug6wZ.y9i', 'counselor', 'actif', 'Psychologie clinique', 'Spécialiste en thérapie cognitive-comportementale avec 10 ans d\'expérience.', TRUE, 'actif'),
(4, 'Bernard', 'Sophie', 'sophie.bernard@example.com', '$2y$10$pJpHEzguq4INFPnqZn6rCeEB2.fFVwEs/gaSTKxa5ajyug6wZ.y9i', 'counselor', 'actif', 'Gestion du stress', 'Expert en techniques de relaxation et mindfulness.', TRUE, 'actif'),
(5, 'Dubois', 'Pierre', 'pierre.dubois@example.com', '$2y$10$pJpHEzguq4INFPnqZn6rCeEB2.fFVwEs/gaSTKxa5ajyug6wZ.y9i', 'user', 'actif', NULL, NULL, NULL, NULL);

-- ============================================
-- MODULE SUPPORT PSYCHOLOGIQUE
-- ============================================
-- Note: La table counselors a été supprimée, les données sont maintenant dans utilisateurs

-- Table 1: support_requests (Demandes de support)
CREATE TABLE IF NOT EXISTS support_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    counselor_user_id INT DEFAULT NULL,
    titre VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    urgence ENUM('basse', 'moyenne', 'haute') DEFAULT 'moyenne',
    statut ENUM('en_attente', 'assignee', 'en_cours', 'terminee', 'annulee') DEFAULT 'en_attente',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_assignation DATETIME DEFAULT NULL,
    date_resolution DATETIME DEFAULT NULL,
    notes_admin TEXT,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (counselor_user_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_counselor_user_id (counselor_user_id),
    INDEX idx_statut (statut),
    INDEX idx_urgence (urgence),
    INDEX idx_date_creation (date_creation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 2: support_messages (Messages de suivi)
CREATE TABLE IF NOT EXISTS support_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    support_request_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    lu BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (support_request_id) REFERENCES support_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_support_request_id (support_request_id),
    INDEX idx_sender_id (sender_id),
    INDEX idx_lu (lu),
    INDEX idx_date_envoi (date_envoi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- VUES POUR LES STATISTIQUES
-- ============================================

-- Vue pour les statistiques des conseillers
CREATE OR REPLACE VIEW v_counselor_stats AS
SELECT 
    u.id,
    u.nom,
    u.prenom,
    u.specialite,
    u.nombre_demandes_actives,
    COUNT(DISTINCT sr.id) as total_demandes,
    COUNT(DISTINCT CASE WHEN sr.statut = 'terminee' THEN sr.id END) as demandes_terminees,
    AVG(CASE 
        WHEN sr.date_resolution IS NOT NULL AND sr.date_assignation IS NOT NULL 
        THEN TIMESTAMPDIFF(HOUR, sr.date_assignation, sr.date_resolution) 
    END) as temps_resolution_moyen_heures
FROM utilisateurs u
LEFT JOIN support_requests sr ON u.id = sr.counselor_user_id
WHERE u.role = 'counselor'
GROUP BY u.id, u.nom, u.prenom, u.specialite, u.nombre_demandes_actives;

-- Vue pour les demandes avec informations complètes
CREATE OR REPLACE VIEW v_support_requests_full AS
SELECT 
    sr.id,
    sr.titre,
    sr.description,
    sr.urgence,
    sr.statut,
    sr.date_creation,
    sr.date_assignation,
    sr.date_resolution,
    sr.notes_admin,
    u.id as user_id,
    u.nom as user_nom,
    u.prenom as user_prenom,
    u.email as user_email,
    uc.id as counselor_user_id,
    uc.nom as counselor_nom,
    uc.prenom as counselor_prenom,
    uc.specialite as counselor_specialite
FROM support_requests sr
INNER JOIN utilisateurs u ON sr.user_id = u.id
LEFT JOIN utilisateurs uc ON sr.counselor_user_id = uc.id AND uc.role = 'counselor';

-- ============================================
-- TRIGGERS POUR AUTOMATISATION
-- ============================================

DELIMITER $$

-- Trigger: Incrémenter le nombre de demandes actives
DROP TRIGGER IF EXISTS tr_increment_active_requests$$
CREATE TRIGGER tr_increment_active_requests
AFTER UPDATE ON support_requests
FOR EACH ROW
BEGIN
    IF NEW.counselor_user_id IS NOT NULL AND OLD.counselor_user_id IS NULL THEN
        UPDATE utilisateurs 
        SET nombre_demandes_actives = nombre_demandes_actives + 1
        WHERE id = NEW.counselor_user_id AND role = 'counselor';
    END IF;
END$$

-- Trigger: Décrémenter le nombre de demandes actives
DROP TRIGGER IF EXISTS tr_decrement_active_requests$$
CREATE TRIGGER tr_decrement_active_requests
AFTER UPDATE ON support_requests
FOR EACH ROW
BEGIN
    IF NEW.statut IN ('terminee', 'annulee') AND OLD.statut NOT IN ('terminee', 'annulee') THEN
        IF NEW.counselor_user_id IS NOT NULL THEN
            UPDATE utilisateurs 
            SET nombre_demandes_actives = GREATEST(0, nombre_demandes_actives - 1)
            WHERE id = NEW.counselor_user_id AND role = 'counselor';
        END IF;
    END IF;
END$$

-- Trigger: Mettre à jour date_assignation
DROP TRIGGER IF EXISTS tr_set_date_assignation$$
CREATE TRIGGER tr_set_date_assignation
BEFORE UPDATE ON support_requests
FOR EACH ROW
BEGIN
    IF NEW.counselor_user_id IS NOT NULL AND OLD.counselor_user_id IS NULL THEN
        SET NEW.date_assignation = NOW();
        SET NEW.statut = 'assignee';
    END IF;
END$$

-- Trigger: Mettre à jour date_resolution
DROP TRIGGER IF EXISTS tr_set_date_resolution$$
CREATE TRIGGER tr_set_date_resolution
BEFORE UPDATE ON support_requests
FOR EACH ROW
BEGIN
    IF NEW.statut = 'terminee' AND OLD.statut != 'terminee' THEN
        SET NEW.date_resolution = NOW();
    END IF;
END$$

DELIMITER ;

-- ============================================
-- DONNÉES DE TEST (Optionnel)
-- ============================================
-- Les données des conseillers sont déjà insérées dans la table utilisateurs ci-dessus

-- ============================================
-- FIN DU SCRIPT
-- ============================================

-- Base de données initialisée avec succès !

