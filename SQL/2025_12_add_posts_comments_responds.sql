-- Add posts, comments, and responds tables

CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT DEFAULT NULL,
    author VARCHAR(150) DEFAULT NULL,
    message TEXT NOT NULL,
    time DATETIME DEFAULT CURRENT_TIMESTAMP,
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('pending','approved','blocked') DEFAULT 'pending',
    CONSTRAINT fk_post_user FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_post INT NOT NULL,
    author VARCHAR(150) DEFAULT NULL,
    message TEXT NOT NULL,
    time DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comment_post FOREIGN KEY (id_post) REFERENCES posts(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS responds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_post INT DEFAULT NULL,
    id_com INT DEFAULT NULL,
    author VARCHAR(150) DEFAULT NULL,
    message TEXT NOT NULL,
    time DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_respond_post FOREIGN KEY (id_post) REFERENCES posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_respond_comment FOREIGN KEY (id_com) REFERENCES comments(id) ON DELETE CASCADE
);
