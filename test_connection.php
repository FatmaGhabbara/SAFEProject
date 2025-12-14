<?php
/**
 * Script de test de connexion à la base de données
 * Utilisez ce fichier pour vérifier que votre configuration fonctionne
 */

require_once 'config.php';

// Utiliser la connexion depuis config.php à la racine
if (!isset($db) || !$db) {
    $database = new Database();
    $db = $database->getConnection();
}

echo "<h1>Test de Connexion - Safe Space</h1>";

// Test 1: Connexion à la base de données
echo "<h2>1. Test de Connexion</h2>";
if ($db) {
    echo "✅ <strong>Connexion réussie !</strong><br>";
    echo "Base de données : safeproject_db<br>";
} else {
    echo "❌ <strong>Erreur de connexion</strong><br>";
    echo "Vérifiez votre configuration dans config.php<br>";
    exit;
}

// Test 2: Vérification des tables
echo "<h2>2. Vérification des Tables</h2>";
try {
    $tables = ['types', 'signalements'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' existe<br>";
        } else {
            echo "❌ Table '$table' n'existe pas<br>";
            echo "Exécutez le fichier database.sql pour créer les tables<br>";
        }
    }
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "<br>";
}

// Test 3: Vérification des types
echo "<h2>3. Vérification des Types</h2>";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM types");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result['count'] > 0) {
        echo "✅ " . $result['count'] . " type(s) trouvé(s)<br>";
        $stmt = $db->query("SELECT * FROM types");
        echo "<ul>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<li>" . htmlspecialchars($row['nom']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "⚠️ Aucun type trouvé. Exécutez le fichier database.sql<br>";
    }
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "<br>";
}

// Test 4: Vérification des signalements
echo "<h2>4. Vérification des Signalements</h2>";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM signalements");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "📊 " . $result['count'] . " signalement(s) dans la base de données<br>";
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "<br>";
}

// Test 5: Test des controllers
echo "<h2>5. Test des Controllers</h2>";
try {
    require_once 'model/Signalement.php';
    require_once 'model/Type.php';
    require_once 'controller/TypeController.php';
    require_once 'controller/SignalementController.php';
    
    $typeController = new TypeController($db);
    $types = $typeController->getAllTypes();
    echo "✅ TypeController fonctionne (" . count($types) . " types)<br>";
    
    $signalementController = new SignalementController($db);
    $signalements = $signalementController->getAllSignalements();
    echo "✅ SignalementController fonctionne (" . count($signalements) . " signalements)<br>";
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "<br>";
}

// Test 6: Vérification des fichiers
echo "<h2>6. Vérification des Fichiers Frontend</h2>";
$files = [
    'view/frontoffice/index.php',
    'view/frontoffice/mes_signalements.php',
    'view/frontoffice/ajouter_signalement.php',
    'view/frontoffice/detail_signalement.php',
    'view/frontoffice/supprimer_signalement.php',
    'view/frontoffice/api.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file existe<br>";
    } else {
        echo "❌ $file manquant<br>";
    }
}

echo "<hr>";
echo "<h2>✅ Tests Terminés</h2>";
echo "<p>Si tous les tests sont verts, vous pouvez accéder à l'application :</p>";
echo "<ul>";
echo "<li><a href='view/frontoffice/index.php'>Page d'accueil</a></li>";
echo "<li><a href='view/frontoffice/mes_signalements.php'>Mes Signalements</a></li>";
echo "<li><a href='view/frontoffice/ajouter_signalement.php'>Ajouter un Signalement</a></li>";
echo "</ul>";
?>

