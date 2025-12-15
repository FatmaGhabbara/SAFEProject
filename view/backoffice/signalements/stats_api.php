<?php
// API pour les statistiques du dashboard
// Détection automatique du chemin vers la racine
$rootPath = dirname(dirname(dirname(__DIR__)));
$configPath = $rootPath . DIRECTORY_SEPARATOR . 'config.php';

// Si config.php n'est pas trouvé, essayer un niveau au-dessus
if (!file_exists($configPath)) {
    $rootPath = dirname($rootPath);
    $configPath = $rootPath . DIRECTORY_SEPARATOR . 'config.php';
}

require_once $configPath;

// Chemins vers model et controller
$modelPath = $rootPath . DIRECTORY_SEPARATOR . 'model';
$controllerPath = $rootPath . DIRECTORY_SEPARATOR . 'controller';

// Si model n'existe pas à cet endroit, essayer un niveau au-dessus
if (!is_dir($modelPath)) {
    $rootPath = dirname($rootPath);
    $modelPath = $rootPath . DIRECTORY_SEPARATOR . 'model';
    $controllerPath = $rootPath . DIRECTORY_SEPARATOR . 'controller';
}

require_once $modelPath . DIRECTORY_SEPARATOR . 'Signalement.php';
require_once $modelPath . DIRECTORY_SEPARATOR . 'Type.php';
require_once $controllerPath . DIRECTORY_SEPARATOR . 'TypeController.php';
require_once $controllerPath . DIRECTORY_SEPARATOR . 'SignalementController.php';

// Utiliser la connexion depuis config.php
if (!isset($db) || !$db) {
    // Use central config DB connection
    $db = config::getConnexion();
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'all';

try {
    switch ($action) {
        case 'by_type':
            // Statistiques par type
            $query = "SELECT t.id, t.nom, COUNT(s.id) as count 
                      FROM types t 
                      LEFT JOIN signalements s ON t.id = s.type_id 
                      GROUP BY t.id, t.nom 
                      ORDER BY count DESC";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $stats = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $stats[] = [
                    'type' => $row['nom'],
                    'count' => (int)$row['count']
                ];
            }
            echo json_encode(['success' => true, 'data' => $stats]);
            break;
            
        case 'by_date':
            // Statistiques par date (7 derniers jours)
            // Récupérer les données existantes
            $query = "SELECT DATE(created_at) as date, COUNT(*) as count 
                      FROM signalements 
                      WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                      GROUP BY DATE(created_at) 
                      ORDER BY date ASC";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $dataByDate = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $dataByDate[$row['date']] = (int)$row['count'];
            }
            
            // Générer les 7 derniers jours même s'il n'y a pas de données
            $stats = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $stats[] = [
                    'date' => $date,
                    'count' => isset($dataByDate[$date]) ? $dataByDate[$date] : 0
                ];
            }
            echo json_encode(['success' => true, 'data' => $stats]);
            break;
            
        case 'recent':
            // Signalements récents (30 derniers jours)
            $query = "SELECT COUNT(*) as count 
                      FROM signalements 
                      WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'count' => (int)$result['count']]);
            break;
            
        case 'today':
            // Signalements d'aujourd'hui
            $query = "SELECT COUNT(*) as count 
                      FROM signalements 
                      WHERE DATE(created_at) = CURDATE()";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'count' => (int)$result['count']]);
            break;
            
        case 'all':
        default:
            // Toutes les statistiques
            $signalementController = new SignalementController($db);
            $typeController = new TypeController($db);
            
            $totalSignalements = count($signalementController->getAllSignalements());
            $totalTypes = count($typeController->getAllTypes());
            
            // Signalements aujourd'hui
            $query = "SELECT COUNT(*) as count FROM signalements WHERE DATE(created_at) = CURDATE()";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $today = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Signalements cette semaine
            $query = "SELECT COUNT(*) as count FROM signalements WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $thisWeek = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Par type
            $query = "SELECT t.nom, COUNT(s.id) as count 
                      FROM types t 
                      LEFT JOIN signalements s ON t.id = s.type_id 
                      GROUP BY t.id, t.nom 
                      ORDER BY count DESC";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $byType = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $byType[] = [
                    'type' => $row['nom'],
                    'count' => (int)$row['count']
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'total' => $totalSignalements,
                    'totalTypes' => $totalTypes,
                    'today' => $today,
                    'thisWeek' => $thisWeek,
                    'byType' => $byType
                ]
            ]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

