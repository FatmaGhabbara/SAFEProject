<?php
// Réutiliser la logique d'ajout mais pour la modification
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
    die('Erreur: config.php non trouvé');
}
require_once $configPath;

$baseDir = dirname(dirname($currentDir));
$modelPath = $baseDir . DIRECTORY_SEPARATOR . 'model';
$controllerPath = $baseDir . DIRECTORY_SEPARATOR . 'controller';
if (!is_dir($modelPath)) {
    $baseDir = dirname($baseDir);
    $modelPath = $baseDir . DIRECTORY_SEPARATOR . 'model';
    $controllerPath = $baseDir . DIRECTORY_SEPARATOR . 'controller';
}
require_once $modelPath . DIRECTORY_SEPARATOR . 'Signalement.php';
require_once $modelPath . DIRECTORY_SEPARATOR . 'Type.php';
require_once $controllerPath . DIRECTORY_SEPARATOR . 'TypeController.php';
require_once $controllerPath . DIRECTORY_SEPARATOR . 'SignalementController.php';

// Use central DB connection from config
$db = config::getConnexion();
$controller = new SignalementController($db);
$types = $controller->getTypesForForm();

$message = '';
$success = false;
$errors = [];

// Get id
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$id) {
    die('Id manquant pour modification');
}

$existing = $controller->getSignalementById($id);
if (!$existing) {
    die('Signalement introuvable');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->updateSignalement($id, $_POST);
    if ($result['success']) {
        $message = $result['message'];
        $success = true;
        $existing = $controller->getSignalementById($id);
    } else {
        if (!empty($result['errors'])) $errors = $result['errors'];
        $message = $result['message'] ?? 'Erreur during update';
    }
}
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="stylesheet" href="../assets/css/main.css" />
        <title>Modifier Signalement</title>
    </head>
    <body class="is-preload">
        <div id="page-wrapper">
            <header id="header" class="alt">
                <h1><a href="../index.php">Safe Space</a></h1>
            </header>
            <section id="wrapper">
                <div class="inner">
                    <h2>Modifier Signalement</h2>
                    <?php if ($message): ?>
                        <div class="message <?= $success ? 'success' : 'error' ?>"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($errors)): ?>
                        <div class="error-list"><ul><?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="form-group">
                            <label for="titre">Titre</label>
                            <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($existing['titre']) ?>" />
                        </div>
                        <div class="form-group">
                            <label for="type_id">Type</label>
                            <select id="type_id" name="type_id">
                                <option value="">Sélectionnez</option>
                                <?php foreach ($types as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= ($existing['type_id'] == $t['id']) ? 'selected' : '' ?>><?= htmlspecialchars($t['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description"><?= htmlspecialchars($existing['description']) ?></textarea>
                            <div style="margin-top:8px; display:flex; gap:8px; align-items:center;">
                                <label for="ia-operation" style="font-size:0.9em; color:#555;">Opération:</label>
                                <select id="ia-operation" style="padding:6px;">
                                    <option value="correct">Corriger (orthographe & grammaire)</option>
                                    <option value="paraphrase">Paraphraser</option>
                                </select>
                                <button type="button" id="btn-ia-manipulate" class="button">🤖 Manipuler avec IA</button>
                                <span id="ia-status" style="color:#666; font-size:0.9em; display:none;">Génération en cours…</span>
                            </div>
                            <div id="ia-suggestion" style="display:none; margin-top:10px; background:#f6f6f6; padding:10px; border-radius:6px;">
                                <div style="margin-bottom:8px; color:#333;"><strong>Suggestion IA :</strong></div>
                                <div id="ia-suggestion-text" style="white-space:pre-wrap; color:#111;"></div>
                                <div id="ia-note" style="color:#666; font-size:0.9em; margin-top:8px; display:none;"></div>
                                <div style="margin-top:8px; display:flex; gap:8px;">
                                    <button type="button" id="ia-apply" class="button primary">Appliquer</button>
                                    <button type="button" id="ia-dismiss" class="button">Ignorer</button>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <input type="submit" value="Enregistrer" class="button primary" />
                            <a href="../index.php" class="button">← Retour</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </body>
</html>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const btn = document.getElementById('btn-ia-manipulate');
    const status = document.getElementById('ia-status');
    const box = document.getElementById('ia-suggestion');
    const textEl = document.getElementById('ia-suggestion-text');
    const applyBtn = document.getElementById('ia-apply');
    const dismissBtn = document.getElementById('ia-dismiss');
    const textarea = document.getElementById('description');

    function showStatus(on){ status.style.display = on ? 'inline' : 'none'; }

    btn.addEventListener('click', function(){
        const current = textarea.value || '';
        const operation = (document.getElementById('ia-operation') || {value:'paraphrase'}).value || 'paraphrase';
        showStatus(true);
        box.style.display = 'none';

        fetch((function(){
            // Compute api path relative to this file
            const p = window.location.pathname.match(/(.*\/view\/frontoffice)(?:\/|$)/);
            return (p? window.location.origin + p[1] + '/api.php' : window.location.origin + '/view/frontoffice/api.php') + '?action=ai_manipulate';
        })(), {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ text: current, operation: operation })
        }).then(r=>r.json()).then(function(resp){
            showStatus(false);
            if(!resp || !resp.success){
                alert(resp && resp.message ? resp.message : 'Erreur lors de la génération IA');
                return;
            }
            textEl.textContent = resp.suggestion || '';
            var noteEl = document.getElementById('ia-note');
            if (noteEl) {
                if (resp.note) { noteEl.textContent = resp.note; noteEl.style.display = 'block'; }
                else { noteEl.style.display = 'none'; }
            }
            box.style.display = 'block';
        }).catch(function(err){
            showStatus(false);
            alert('Erreur réseau lors de la requête IA');
            console.error(err);
        });
    });

    applyBtn.addEventListener('click', function(){
        const s = document.getElementById('ia-suggestion-text').textContent || '';
        textarea.value = s;
        document.getElementById('ia-suggestion').style.display = 'none';
    });
    dismissBtn.addEventListener('click', function(){ document.getElementById('ia-suggestion').style.display = 'none'; });
});
</script>
