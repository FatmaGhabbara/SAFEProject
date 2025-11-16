-- ============================================
-- PHPMyAdmin-friendly copy of init_complete.sql
-- Replaced DELIMITER blocks so phpMyAdmin can import triggers
-- Path: database/init_complete_phpmyadmin.sql
-- ============================================

-- Créer la table utilisateurs si elle n'existe pas
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin', 'counselor') DEFAULT 'user',
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif',
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insérer des utilisateurs de test
INSERT IGNORE INTO utilisateurs (id, nom, prenom, email, password, role, statut) VALUES
(1, 'Admin', 'Système', 'admin@safeproject.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'actif'),
(2, 'Dupont', 'Jean', 'jean.dupont@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'actif'),
(3, 'Martin', 'Marie', 'marie.martin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'counselor', 'actif'),
(4, 'Bernard', 'Sophie', 'sophie.bernard@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'counselor', 'actif'),
(5, 'Dubois', 'Pierre', 'pierre.dubois@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'actif');

-- ============================================
-- MODULE SUPPORT PSYCHOLOGIQUE
-- ============================================

CREATE TABLE IF NOT EXISTS counselors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    specialite VARCHAR(255) NOT NULL,
    biographie TEXT,
    disponibilite BOOLEAN DEFAULT TRUE,
    nombre_demandes_actives INT DEFAULT 0,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('actif', 'inactif', 'en_pause') DEFAULT 'actif',
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_statut (statut),
    INDEX idx_disponibilite (disponibilite)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    counselor_id INT DEFAULT NULL,
    titre VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    urgence ENUM('basse', 'moyenne', 'haute') DEFAULT 'moyenne',
    statut ENUM('en_attente', 'assignee', 'en_cours', 'terminee', 'annulee') DEFAULT 'en_attente',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_assignation DATETIME DEFAULT NULL,
    date_resolution DATETIME DEFAULT NULL,
    notes_admin TEXT,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (counselor_id) REFERENCES counselors(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_counselor_id (counselor_id),
    INDEX idx_statut (statut),
    INDEX idx_urgence (urgence),
    INDEX idx_date_creation (date_creation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE OR REPLACE VIEW v_counselor_stats AS
SELECT 
    c.id,
    c.user_id,
    u.nom,
    u.prenom,
    c.specialite,
    c.nombre_demandes_actives,
    COUNT(DISTINCT sr.id) as total_demandes,
    COUNT(DISTINCT CASE WHEN sr.statut = 'terminee' THEN sr.id END) as demandes_terminees,
    AVG(CASE 
        WHEN sr.date_resolution IS NOT NULL AND sr.date_assignation IS NOT NULL 
        THEN TIMESTAMPDIFF(HOUR, sr.date_assignation, sr.date_resolution) 
    END) as temps_resolution_moyen_heures
FROM counselors c
LEFT JOIN utilisateurs u ON c.user_id = u.id
LEFT JOIN support_requests sr ON c.id = sr.counselor_id
GROUP BY c.id, c.user_id, u.nom, u.prenom, c.specialite, c.nombre_demandes_actives;

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
    c.id as counselor_id,
    uc.nom as counselor_nom,
    uc.prenom as counselor_prenom,
    c.specialite as counselor_specialite
FROM support_requests sr
INNER JOIN utilisateurs u ON sr.user_id = u.id
LEFT JOIN counselors c ON sr.counselor_id = c.id
LEFT JOIN utilisateurs uc ON c.user_id = uc.id;

-- ============================================
-- TRIGGERS POUR AUTOMATISATION (phpMyAdmin-friendly)
-- Note: phpMyAdmin does not support custom DELIMITER. Triggers must end with a standard semicolon.
-- Trigger: Incrémenter le nombre de demandes actives
DROP TRIGGER IF EXISTS tr_increment_active_requests;
CREATE TRIGGER tr_increment_active_requests
AFTER UPDATE ON support_requests
FOR EACH ROW
BEGIN
    IF NEW.counselor_id IS NOT NULL AND OLD.counselor_id IS NULL THEN
        UPDATE counselors 
        SET nombre_demandes_actives = nombre_demandes_actives + 1
        WHERE id = NEW.counselor_id;
    END IF;
END;

-- Trigger: Décrémenter le nombre de demandes actives
DROP TRIGGER IF EXISTS tr_decrement_active_requests;
CREATE TRIGGER tr_decrement_active_requests
AFTER UPDATE ON support_requests
FOR EACH ROW
BEGIN
    IF NEW.statut IN ('terminee', 'annulee') AND OLD.statut NOT IN ('terminee', 'annulee') THEN
        IF NEW.counselor_id IS NOT NULL THEN
            UPDATE counselors 
            SET nombre_demandes_actives = GREATEST(0, nombre_demandes_actives - 1)
            WHERE id = NEW.counselor_id;
        END IF;
    END IF;
END;

-- Trigger: Mettre à jour date_assignation
DROP TRIGGER IF EXISTS tr_set_date_assignation;
CREATE TRIGGER tr_set_date_assignation
BEFORE UPDATE ON support_requests
FOR EACH ROW
BEGIN
    IF NEW.counselor_id IS NOT NULL AND OLD.counselor_id IS NULL THEN
        SET NEW.date_assignation = NOW();
        SET NEW.statut = 'assignee';
    END IF;
END;

-- Trigger: Mettre à jour date_resolution
DROP TRIGGER IF EXISTS tr_set_date_resolution;
CREATE TRIGGER tr_set_date_resolution
BEFORE UPDATE ON support_requests
FOR EACH ROW
BEGIN
    IF NEW.statut = 'terminee' AND OLD.statut != 'terminee' THEN
        SET NEW.date_resolution = NOW();
    END IF;
END;

-- ============================================
-- DONNÉES DE TEST (Optionnel)
-- ============================================

INSERT IGNORE INTO counselors (user_id, specialite, biographie, statut) VALUES
(3, 'Psychologie clinique', 'Spécialiste en thérapie cognitive-comportementale avec 10 ans d\'expérience.', 'actif'),
(4, 'Gestion du stress', 'Expert en techniques de relaxation et mindfulness.', 'actif');

-- FIN

SELECT '✅ Base de données préparée pour phpMyAdmin.' as Message;
