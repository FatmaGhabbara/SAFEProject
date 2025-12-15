<?php
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
	$msg = 'supprimer_signalement.php: config.php non trouvé. Recherché depuis: ' . $currentDir . '. Dernier candidat: ' . (isset($candidate) ? $candidate : '[néant]');
	error_log($msg);
	die('Erreur: config.php non trouvé. Vérifiez la présence du fichier config.php dans le projet. (Détails dans logs)');
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
    die('Erreur: Dossier model non trouvé.');
}

require_once $modelPath . DIRECTORY_SEPARATOR . 'Signalement.php';
require_once $modelPath . DIRECTORY_SEPARATOR . 'Type.php';
require_once $controllerPath . DIRECTORY_SEPARATOR . 'TypeController.php';
require_once $controllerPath . DIRECTORY_SEPARATOR . 'SignalementController.php';

// Utiliser la connexion depuis config.php
$db = config::getConnexion();

if (!isset($_GET['id'])) {
    header('Location: mes_signalements.php');
    exit();
}

$signalementController = new SignalementController($db);
$signalement_id = $_GET['id'];

// Vérifier si le signalement existe
$signalement = $signalementController->getSignalementById($signalement_id);
if (!$signalement) {
    header('Location: mes_signalements.php');
    exit();
}

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $signalementController->deleteSignalement($signalement_id);
    
    if ($result['success']) {
        header('Location: mes_signalements.php?message=supprime');
        exit();
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Supprimer le Signalement - Safe Space</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" href="assets/css/main.css" />
		<noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
		<style>
			.warning-box {
				background: rgba(255, 193, 7, 0.2);
				border: 1px solid rgba(255, 193, 7, 0.5);
				border-radius: 4px;
				padding: 2em;
				margin-bottom: 2em;
				text-align: center;
			}
			.warning-box h2 {
				color: #ffc107;
				margin-top: 0;
			}
			.signalement-info {
				background: rgba(255, 255, 255, 0.05);
				border: 1px solid rgba(255, 255, 255, 0.1);
				padding: 1.5em;
				border-radius: 4px;
				margin: 2em 0;
				text-align: left;
			}
			.signalement-info h3 {
				margin-top: 0;
				color: #fff;
			}
			.signalement-info p {
				margin: 0.5em 0;
				color: rgba(255, 255, 255, 0.8);
			}
			.error-message {
				background: rgba(244, 67, 54, 0.2);
				border: 1px solid rgba(244, 67, 54, 0.5);
				color: #f44336;
				padding: 1em;
				border-radius: 4px;
				margin-bottom: 2em;
			}
			.form-actions {
				display: flex;
				gap: 1em;
				justify-content: center;
				margin-top: 2em;
			}
		</style>
	</head>
	<body class="is-preload">
		<div id="page-wrapper">
			<header id="header" class="alt">
				<h1><a href="index.php">Safe Space</a></h1>
				<nav>
					<a href="#menu">Menu</a>
				</nav>
			</header>

			<nav id="menu">
				<div class="inner">
					<h2>Menu</h2>
					<ul class="links">
						<li><a href="index.php">Home</a></li>
						<li><a href="mes_signalements.php">Mes Signalements</a></li>
						<li><a href="index.php">Nouveau Signalement</a></li>
						<li><a href="../backoffice/index.php" target="_blank">Admin</a></li>
						<li><a href="elements.html">Profile</a></li>
						<li><a href="login.html">Log In</a></li>
						<li><a href="register.html">Sign Up</a></li>
					</ul>
					<a href="#" class="close">Close</a>
				</div>
			</nav>

			<section id="banner">
				<div class="inner">
					<h2>🗑️ Supprimer le Signalement</h2>
					<p>Confirmez la suppression de votre signalement</p>
				</div>
			</section>

			<section id="wrapper">
				<section class="wrapper style1">
					<div class="inner">
						<div class="warning-box">
							<h2>⚠️ Attention !</h2>
							<p>Vous êtes sur le point de supprimer définitivement ce signalement.</p>
							<p><strong>Cette action est irréversible.</strong></p>
						</div>

						<div class="signalement-info">
							<h3>Signalement à supprimer :</h3>
							<p><strong>Titre :</strong> <?= htmlspecialchars($signalement['titre']) ?></p>
							<p><strong>Type :</strong> <?= htmlspecialchars($signalement['type_nom']) ?></p>
							<p><strong>Date :</strong> <?= date('d/m/Y à H:i', strtotime($signalement['created_at'])) ?></p>
						</div>

						<?php if (isset($error)): ?>
							<div class="error-message">
								❌ <?= htmlspecialchars($error) ?>
							</div>
						<?php endif; ?>

						<form method="POST">
							<div class="form-actions">
								<input type="submit" value="🗑️ Confirmer la suppression" class="button" onclick="return confirm('Êtes-vous ABSOLUMENT sûr ?')" />
								<a href="mes_signalements.php" class="button">← Annuler</a>
							</div>
						</form>
					</div>
				</section>
			</section>

			<section id="footer">
				<div class="inner">
					<h2 class="major">Contactez-nous</h2>
					<p>Si vous avez des questions ou besoin d'aide, n'hésitez pas à nous contacter.</p>
					<ul class="copyright">
						<li>&copy; Safe Space. All rights reserved.</li><li>Design: <a href="http://html5up.net">HTML5 UP</a></li>
					</ul>
				</div>
			</section>
		</div>

		<script src="assets/js/jquery.min.js"></script>
		<script src="assets/js/jquery.scrollex.min.js"></script>
		<script src="assets/js/browser.min.js"></script>
		<script src="assets/js/breakpoints.min.js"></script>
		<script src="assets/js/util.js"></script>
		<script src="assets/js/main.js"></script>
	</body>
</html>

