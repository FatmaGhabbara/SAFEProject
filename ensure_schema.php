<?php
require_once __DIR__ . '/config.php';

$db = config::getConnexion();
$neededTables = [
    'users' => "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin','moderateur','expert','membre') DEFAULT 'membre',
        status ENUM('actif','en attente','suspendu') DEFAULT 'en attente',
        profile_picture VARCHAR(255) DEFAULT 'assets/images/default-avatar.png',
        date_naissance DATE DEFAULT NULL,
        telephone VARCHAR(20) DEFAULT NULL,
        adresse TEXT DEFAULT NULL,
        bio TEXT DEFAULT NULL,
        specialite VARCHAR(100) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        faceid_enabled TINYINT(1) DEFAULT 0,
        fingerprint_enabled TINYINT(1) DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'categories' => "CREATE TABLE IF NOT EXISTS categories (
        id_categorie INT AUTO_INCREMENT PRIMARY KEY,
        nom_categorie VARCHAR(150) NOT NULL,
        description TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'articles' => "CREATE TABLE IF NOT EXISTS articles (
        id_article INT AUTO_INCREMENT PRIMARY KEY,
        titre VARCHAR(255) NOT NULL,
        contenu TEXT NOT NULL,
        id_categorie INT NOT NULL,
        image_path VARCHAR(255) DEFAULT NULL,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending','approved','rejected') DEFAULT 'pending',
        view_count INT UNSIGNED NOT NULL DEFAULT 0,
        author_id INT DEFAULT NULL,
        CONSTRAINT fk_article_categorie FOREIGN KEY (id_categorie) REFERENCES categories(id_categorie) ON DELETE CASCADE,
        CONSTRAINT fk_article_user FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'comment_articles' => "CREATE TABLE IF NOT EXISTS comment_articles (
        id_comment INT AUTO_INCREMENT PRIMARY KEY,
        id_article INT NOT NULL,
        id_user INT NOT NULL,
        contenu TEXT NOT NULL,
        date_comment DATETIME DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_comment_article FOREIGN KEY (id_article) REFERENCES articles(id_article) ON DELETE CASCADE,
        CONSTRAINT fk_comment_user FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'reactions' => "CREATE TABLE IF NOT EXISTS reactions (
        id_reaction INT AUTO_INCREMENT PRIMARY KEY,
        id_article INT NOT NULL,
        user_id INT NOT NULL,
        reaction ENUM('like','dislike') NOT NULL,
        date_reaction DATETIME DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_reaction_article FOREIGN KEY (id_article) REFERENCES articles(id_article) ON DELETE CASCADE,
        CONSTRAINT fk_reaction_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT uq_user_reaction UNIQUE (id_article, user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'types' => "CREATE TABLE IF NOT EXISTS types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(150) NOT NULL,
        description TEXT DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'signalements' => "CREATE TABLE IF NOT EXISTS signalements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titre VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        type_id INT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_signalement_type FOREIGN KEY (type_id) REFERENCES types(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'posts' => "CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_user INT DEFAULT NULL,
        author VARCHAR(150) DEFAULT NULL,
        message TEXT NOT NULL,
        time DATETIME DEFAULT CURRENT_TIMESTAMP,
        image VARCHAR(255) DEFAULT NULL,
        status ENUM('pending','approved','blocked') DEFAULT 'pending',
        CONSTRAINT fk_post_user FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'comments' => "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_post INT NOT NULL,
        author VARCHAR(150) DEFAULT NULL,
        message TEXT NOT NULL,
        time DATETIME DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_comment_post FOREIGN KEY (id_post) REFERENCES posts(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    'responds' => "CREATE TABLE IF NOT EXISTS responds (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_post INT DEFAULT NULL,
        id_com INT DEFAULT NULL,
        author VARCHAR(150) DEFAULT NULL,
        message TEXT NOT NULL,
        time DATETIME DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_respond_post FOREIGN KEY (id_post) REFERENCES posts(id) ON DELETE CASCADE,
        CONSTRAINT fk_respond_comment FOREIGN KEY (id_com) REFERENCES comments(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
];

foreach ($neededTables as $table => $createSql) {
    try {
        $res = $db->query("SHOW TABLES LIKE '" . $table . "'");
        if ($res && $res->rowCount() > 0) {
            echo "✅ Table '$table' existe.\n";
            continue;
        }

        echo "Création de la table '$table'...\n";
        $db->exec($createSql);
        echo "Table '$table' créée ou déjà existante.\n";
    } catch (Exception $e) {
        echo "Erreur lors de la création de la table '$table': " . $e->getMessage() . "\n";
    }
}

echo "Vérification terminée.\n";
