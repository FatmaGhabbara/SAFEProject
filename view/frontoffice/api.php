<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Détection automatique du chemin vers config.php (robuste)
$currentDir = __DIR__;
$configPath = null;
$checkDir = $currentDir;
for ($i = 0; $i < 8; $i++) {
    $candidate = $checkDir . DIRECTORY_SEPARATOR . 'config.php';
    if (file_exists($candidate)) {
        $configPath = $candidate;
        break;
    }
    $parent = dirname($checkDir);
    if ($parent === $checkDir) break;
    $checkDir = $parent;
}

if (!$configPath) {
    $msg = 'api.php: config.php non trouvé. Recherché depuis: ' . $currentDir . '. Dernier candidat: ' . (isset($candidate) ? $candidate : '[néant]');
    error_log($msg);
    echo json_encode(['success' => false, 'message' => 'config.php non trouvé. Voir logs sur le serveur (error_log) pour détails.']);
    exit;
}

// Debug endpoint: return computed config path (only from localhost)
if (isset($_GET['action']) && $_GET['action'] === 'debug_config') {
    $remote = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    if (!in_array($remote, ['127.0.0.1','::1'])) {
        echo json_encode(['success' => false, 'message' => 'Accès refusé. Debug only accessible from localhost.']);
        exit;
    }
    echo json_encode(['success' => true, 'configPath' => $configPath, 'searchFrom' => $currentDir]);
    exit;
}

require_once $configPath;

// Chemins vers model et controller
$baseDir = dirname(dirname($currentDir));
$modelPath = $baseDir . DIRECTORY_SEPARATOR . 'model';
$controllerPath = $baseDir . DIRECTORY_SEPARATOR . 'controller';

if (!is_dir($modelPath)) {
    $baseDir = dirname($baseDir);
    $modelPath = $baseDir . DIRECTORY_SEPARATOR . 'model';
    $controllerPath = $baseDir . DIRECTORY_SEPARATOR . 'controller';
}

if (!is_dir($modelPath)) {
    echo json_encode(['success' => false, 'message' => 'Dossier model non trouvé']);
    exit;
}

require_once $modelPath . DIRECTORY_SEPARATOR . 'Signalement.php';
require_once $modelPath . DIRECTORY_SEPARATOR . 'Type.php';
require_once $controllerPath . DIRECTORY_SEPARATOR . 'SignalementController.php';
require_once $controllerPath . DIRECTORY_SEPARATOR . 'TypeController.php';

// Utiliser la connexion depuis config.php
$db = config::getConnexion();

// Initialiser le contrôleur
$controller = new SignalementController($db);

// Récupérer la méthode HTTP et l'action
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'getSignalements') {
                // Récupérer tous les signalements
                $signalements = $controller->getAllSignalements();
                echo json_encode(['success' => true, 'data' => $signalements]);
            } elseif ($action === 'getTypes') {
                // Récupérer tous les types (robuste vis-à-vis d'erreurs de schéma)
                try {
                    $types = $controller->getTypesForForm();
                    echo json_encode(['success' => true, 'data' => $types]);
                } catch (Exception $e) {
                    error_log('API getTypes failed: ' . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Impossible de charger les types. Vérifiez la base de données ou exécutez `php ensure_schema.php`.']);
                }
            } elseif ($action === 'types_debug') {
                // Retourne les types + informations de debug (accessible uniquement depuis localhost)
                $remote = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
                if (!in_array($remote, ['127.0.0.1','::1'])) {
                    echo json_encode(['success' => false, 'message' => 'Accès refusé. Debug only accessible from localhost.']);
                    exit;
                }

                try {
                    $debug = $controller->getTypesDebugInfo();
                    $types = $debug['types'];
                    $debugInfo = [
                        'configPath' => $configPath,
                        'types_count' => is_array($types) ? count($types) : 0,
                        'last_error' => $debug['last_error'] ?? null,
                    ];
                    echo json_encode(['success' => true, 'data' => $types, 'debug' => $debugInfo]);
                } catch (Exception $e) {
                    error_log('API types_debug failed: ' . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Erreur lors du chargement des types (debug).', 'debug' => ['exception' => $e->getMessage(), 'configPath' => $configPath]]);
                }
            } elseif ($action === 'getSignalement' && isset($_GET['id'])) {
                // Récupérer un signalement par ID
                $signalement = $controller->getSignalementById($_GET['id']);
                if ($signalement) {
                    echo json_encode(['success' => true, 'data' => $signalement]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Signalement non trouvé']);
                }
            } elseif ($action === 'search' && isset($_GET['keyword'])) {
                // Rechercher des signalements
                $keyword = $_GET['keyword'];
                $signalements = $controller->searchSignalements($keyword);
                echo json_encode(['success' => true, 'data' => $signalements]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Action non valide']);
            }
            break;
            
        case 'POST':
            if ($action === 'createSignalement') {
                // Créer un nouveau signalement
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (!$data) {
                    $data = $_POST;
                }
                
                $result = $controller->createSignalement($data);
                // Add debug info if failure and request is from localhost
                $remote = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
                if (!$result['success'] && in_array($remote, ['127.0.0.1', '::1'])) {
                    $result['debug'] = $controller->getLastError();
                }
                echo json_encode($result);
            } elseif ($action === 'updateSignalement' && isset($_GET['id'])) {
                $data = json_decode(file_get_contents('php://input'), true);
                if (!$data) $data = $_POST;
                $id = intval($_GET['id']);
                $result = $controller->updateSignalement($id, $data);
                $remote = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
                if (!$result['success'] && in_array($remote, ['127.0.0.1', '::1'])) {
                    $result['debug'] = $controller->getLastError();
                }
                echo json_encode($result);
            } elseif ($action === 'ai_manipulate') {
                // Simple local 'IA' placeholder: returns a suggested manipulated text
                $data = json_decode(file_get_contents('php://input'), true);
                if (!$data) $data = $_POST;
                $text = isset($data['text']) ? trim((string)$data['text']) : '';
                $operation = isset($data['operation']) ? $data['operation'] : 'paraphrase';

                if ($text === '') {
                    echo json_encode(['success' => false, 'message' => 'Texte manquant']);
                    exit;
                }

                // Basic transformations and operations (paraphrase | correct)
                $suggestion = $text;
                // redact emails and phones
                $suggestion = preg_replace('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', '[REDACTED EMAIL]', $suggestion);
                $suggestion = preg_replace('/\+?\d[\d\s\-().]{5,}\d/', '[REDACTED PHONE]', $suggestion);
                // collapse spaces
                $suggestion = preg_replace('/\s+/', ' ', $suggestion);

                // Paraphrase: small synonym map + sentence casing
                if ($operation === 'paraphrase') {
                    $syn = [
                        '/\bmauvais\b/i' => 'préoccupant',
                        '/\bprobl[eè]me\b/i' => 'préoccupation',
                        '/\burgent\b/i' => 'important',
                    ];
                    $suggestion = preg_replace(array_keys($syn), array_values($syn), $suggestion);
                    // ensure sentences start with uppercase
                    $sentences = preg_split('/([.!?]+)/', $suggestion, -1, PREG_SPLIT_DELIM_CAPTURE);
                    $out = '';
                    for ($i = 0; $i < count($sentences); $i+=2) {
                        $s = trim($sentences[$i]);
                        $p = isset($sentences[$i+1]) ? $sentences[$i+1] : '';
                        if ($s !== '') $s = ucfirst(mb_strtolower($s));
                        $out .= $s . $p . ' ';
                    }
                    $suggestion = trim($out);
                }

                // Correction orthographique/grammaticale: try enchant/pspell, else basic cleanup
                $note = null;
                if ($operation === 'correct') {
                    $orig = $suggestion;
                    $dictAvailable = false;

                    // Tokenize keeping separators
                    $parts = preg_split('/(\b[\p{L}\']+\b)/u', $suggestion, -1, PREG_SPLIT_DELIM_CAPTURE);

                    // Try Enchant first
                    if (function_exists('enchant_broker_init')) {
                        $broker = enchant_broker_init();
                        $langs = ['fr_FR','fr','fr_FR.UTF-8','en_US','en'];
                        $dict = null;
                        foreach ($langs as $l) {
                            $d = @enchant_broker_request_dict($broker, $l);
                            if ($d) { $dict = $d; break; }
                        }
                        if ($dict) {
                            $dictAvailable = true;
                            for ($i = 0; $i < count($parts); $i++) {
                                $tok = $parts[$i];
                                if (preg_match('/^\b[\p{L}\']+\b$/u', $tok)) {
                                    if (!enchant_dict_check($dict, $tok)) {
                                        $sugg = enchant_dict_suggest($dict, $tok);
                                        if (!empty($sugg)) $parts[$i] = $sugg[0];
                                    }
                                }
                            }
                        }
                    }

                    // Fallback to Pspell
                    if (!$dictAvailable && function_exists('pspell_new')) {
                        $ps = @pspell_new('fr');
                        if ($ps) {
                            $dictAvailable = true;
                            for ($i = 0; $i < count($parts); $i++) {
                                $tok = $parts[$i];
                                if (preg_match('/^\b[\p{L}\']+\b$/u', $tok)) {
                                    if (!pspell_check($ps, $tok)) {
                                        $s = pspell_suggest($ps, $tok);
                                        if (!empty($s)) $parts[$i] = $s[0];
                                    }
                                }
                            }
                        }
                    }

                    // Reconstruct and do small grammar cleanups
                    $suggestion = implode('', $parts);
                    // Ensure sentences start with uppercase and single spaces after punctuation
                    $suggestion = preg_replace('/\s+/', ' ', $suggestion);
                    $sentences = preg_split('/([.!?]+)/', $suggestion, -1, PREG_SPLIT_DELIM_CAPTURE);
                    $out = '';
                    for ($i = 0; $i < count($sentences); $i+=2) {
                        $s = trim($sentences[$i]);
                        $p = isset($sentences[$i+1]) ? $sentences[$i+1] : '';
                        if ($s !== '') $s = ucfirst(mb_strtolower($s));
                        $out .= $s . $p . ' ';
                    }
                    $suggestion = trim($out);

                    if (!$dictAvailable) $note = 'Aucun dictionnaire local détecté: correction limitée aux nettoyages et capitalisation.';
                    elseif ($suggestion === $orig) $note = 'Aucune correction trouvée.';
                    else $note = 'Corrections appliquées (moteur local).';
                }

                echo json_encode(['success' => true, 'suggestion' => $suggestion, 'original' => $text, 'note' => $note]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Action non valide']);
            }
            break;
            
        case 'DELETE':
            if ($action === 'deleteSignalement' && isset($_GET['id'])) {
                // Supprimer un signalement
                $result = $controller->deleteSignalement($_GET['id']);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'message' => 'Action non valide']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Méthode non supportée']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>

