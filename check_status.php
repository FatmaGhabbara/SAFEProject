<?php
include_once 'config.php';

echo "<h2>Vérification cohérence 'status'</h2>";

// Vérifier la table
try {
    $pdo = config::getConnexion();
    
    // Vérifier colonnes de la table articles
    $columns = $pdo->query("SHOW COLUMNS FROM articles")->fetchAll();
    echo "<h3>Colonnes de la table 'articles':</h3>";
    $hasStatus = false;
    foreach ($columns as $col) {
        echo $col['Field'] . "<br>";a
        if ($col['Field'] == 'status') {
            $hasStatus = true;
        }
    }
    
    if ($hasStatus) {
        echo "✅ Colonne 'status' trouvée<br>";
    } else {
        echo "❌ Colonne 'status' NON trouvée<br>";
    }
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}

// Test classe Article
echo "<h3>Test classe Article:</h3>";
include_once 'model/Article.php';
$article = new Article("Test", "Test", "Test", "2025-01-01", 1, "test");
echo "getStatus(): " . $article->getStatus() . " ✅<br>";
?>
