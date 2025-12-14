<?php
/**
 * Script de vérification de la structure
 * Placez ce fichier dans C:\xampp\htdocs\SAFEProject\ et accédez-y via le navigateur
 */

echo "<h1>🔍 Vérification de la Structure</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .ok { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
</style>";

$baseDir = __DIR__;
echo "<h2>Dossier actuel :</h2>";
echo "<pre>" . htmlspecialchars($baseDir) . "</pre>";

// Vérifier config.php
echo "<h2>1. Vérification de config.php</h2>";
$configPath = $baseDir . DIRECTORY_SEPARATOR . 'config.php';
if (file_exists($configPath)) {
    echo "<p class='ok'>✅ config.php trouvé : " . htmlspecialchars($configPath) . "</p>";
} else {
    echo "<p class='error'>❌ config.php NON TROUVÉ à : " . htmlspecialchars($configPath) . "</p>";
    echo "<p class='warning'>⚠️ Copiez le fichier depuis : C:\\Users\\fedib\\Downloads\\SAFEProject\\config.php</p>";
}

// Vérifier la structure
echo "<h2>2. Vérification de la structure</h2>";
$requiredDirs = [
    'controller',
    'model',
    'view',
    'view/frontoffice'
];

$requiredFiles = [
    'test_connection.php',
    'controller/SignalementController.php',
    'controller/TypeController.php',
    'model/Signalement.php',
    'model/Type.php',
    'view/frontoffice/index.php',
    'view/frontoffice/api.php'
];

echo "<h3>Dossiers requis :</h3>";
foreach ($requiredDirs as $dir) {
    $path = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dir);
    if (is_dir($path)) {
        echo "<p class='ok'>✅ " . htmlspecialchars($dir) . "</p>";
    } else {
        echo "<p class='error'>❌ " . htmlspecialchars($dir) . " - MANQUANT</p>";
    }
}

echo "<h3>Fichiers requis :</h3>";
foreach ($requiredFiles as $file) {
    $path = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    if (file_exists($path)) {
        echo "<p class='ok'>✅ " . htmlspecialchars($file) . "</p>";
    } else {
        echo "<p class='error'>❌ " . htmlspecialchars($file) . " - MANQUANT</p>";
    }
}

// Vérifier si on est dans un double dossier
echo "<h2>3. Détection de double dossier</h2>";
$parentDir = dirname($baseDir);
$parentParentDir = dirname($parentDir);
$parentName = basename($parentDir);
$currentName = basename($baseDir);

if ($parentName === 'SAFEProject' && $currentName === 'SAFEProject') {
    echo "<p class='error'>❌ PROBLÈME DÉTECTÉ : Vous êtes dans un double dossier SAFEProject/SAFEProject</p>";
    echo "<p class='warning'>⚠️ Le chemin actuel est : " . htmlspecialchars($baseDir) . "</p>";
    echo "<p class='warning'>⚠️ Vous devriez être dans : " . htmlspecialchars($parentDir) . "</p>";
    echo "<p><strong>Solution :</strong> Déplacez le contenu d'un niveau vers le haut.</p>";
} else {
    echo "<p class='ok'>✅ Structure de dossiers correcte</p>";
}

// URLs de test
echo "<h2>4. URLs de Test</h2>";
echo "<p>Essayez ces URLs :</p>";
echo "<ul>";
echo "<li><a href='test_connection.php' target='_blank'>test_connection.php</a></li>";
echo "<li><a href='view/frontoffice/index.php' target='_blank'>view/frontoffice/index.php</a></li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Si tous les éléments sont verts ✅, votre structure est correcte !</strong></p>";
?>

