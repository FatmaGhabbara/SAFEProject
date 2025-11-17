<?php
$root = $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/';
include_once $root . 'config.php';

try {
    $db = config::getConnexion();
    
    // Vérifier si la table existe
    $result = $db->query("SHOW TABLES LIKE 'article_reactions'");
    if ($result->rowCount() > 0) {
        echo "✅ Table 'article_reactions' existe<br>";
        
        // Vérifier la structure
        $structure = $db->query("DESCRIBE article_reactions")->fetchAll();
        echo "Structure de la table :<br>";
        foreach ($structure as $col) {
            echo "- {$col['Field']} ({$col['Type']})<br>";
        }
        
        // Vérifier les données
        $data = $db->query("SELECT COUNT(*) as total FROM article_reactions")->fetch();
        echo "Nombre de réactions : " . $data['total'] . "<br>";
    } else {
        echo "❌ Table 'article_reactions' n'existe pas<br>";
        
        // Créer la table
        echo "Tentative de création...<br>";
        $sql = "CREATE TABLE article_reactions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            article_id INT,
            user_ip VARCHAR(45),
            reaction_type ENUM('like') DEFAULT 'like',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (article_id) REFERENCES article(idArticle) ON DELETE CASCADE,
            UNIQUE KEY unique_reaction (article_id, user_ip)
        )";
        
        $db->exec($sql);
        echo "✅ Table créée avec succès !";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
    echo "<br>Stack trace: " . $e->getTraceAsString();
}
?>