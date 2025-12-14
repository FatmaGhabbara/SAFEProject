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
	$msg = 'detail_signalement.php: config.php non trouvé. Recherché depuis: ' . $currentDir . '. Dernier candidat: ' . (isset($candidate) ? $candidate : '[néant]');
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
require_once $controllerPath . DIRECTORY_SEPARATOR . 'SignalementController.php';
require_once $controllerPath . DIRECTORY_SEPARATOR . 'TypeController.php';

// Utiliser la connexion depuis config.php
if (!isset($db) || !$db) {
    $database = new Database();
    $db = $database->getConnection();
}

if (!isset($_GET['id'])) {
    header('Location: mes_signalements.php');
    exit();
}

$signalementController = new SignalementController($db);
$signalement = $signalementController->getSignalementById($_GET['id']);

if (!$signalement) {
    header('Location: mes_signalements.php');
    exit();
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Détails Signalement - Safe Space</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" href="assets/css/main.css" />
		<noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
		<style>
			/* Card layout: centered, constrained width and comfortable padding */
			.detail-card {
				background: rgba(255, 255, 255, 0.05);
				border: 1px solid rgba(255, 255, 255, 0.08);
				border-radius: 8px;
				padding: 28px 28px;
				max-width: 960px;
				width: 100%;
				box-sizing: border-box;
				margin: 18px auto;
				box-shadow: 0 6px 18px rgba(0,0,0,0.35);
			}

			/* Header: title + type aligned horizontally on wide screens */
			.detail-header {
				display: flex;
				justify-content: space-between;
				align-items: center;
				gap: 18px;
				margin-bottom: 14px;
				padding-bottom: 8px;
				border-bottom: 1px solid rgba(255, 255, 255, 0.04);
			}

			.detail-title {
				font-size: 1.6rem;
				line-height: 1.1;
				font-weight: 700;
				color: #ffffff;
				margin: 0;
			}

			.detail-type {
				background: linear-gradient(135deg, rgba(102,126,234,0.18), rgba(118,75,162,0.18));
				color: #fff;
				padding: 0.45em 0.9em;
				border-radius: 999px;
				font-size: 0.85rem;
				white-space: nowrap;
				border: 1px solid rgba(255,255,255,0.04);
			}

			.detail-meta {
				color: rgba(255, 255, 255, 0.72);
				margin: 12px 0 18px 0;
				font-size: 0.95rem;
			}

			.detail-description {
				background: rgba(255, 255, 255, 0.02);
				padding: 18px;
				border-radius: 6px;
				border-left: 4px solid rgba(102,126,234,0.22);
				margin-bottom: 20px;
				color: rgba(255,255,255,0.92);
				font-size: 1rem;
				line-height: 1.55;
			}

			.detail-description h3 {
				margin-top: 0;
				color: #fff;
				font-size: 1.05rem;
			}

			/* Actions: align to right on desktop, stacked on mobile */
			.action-links {
				margin-top: 8px;
				display: flex;
				gap: 12px;
				justify-content: flex-end;
				flex-wrap: wrap;
			}

			.action-link {
				color: rgba(255, 255, 255, 0.95);
				text-decoration: none;
				padding: 0.62em 1.1em;
				border: 1px solid rgba(255, 255, 255, 0.06);
				border-radius: 6px;
				transition: all 0.18s ease-in-out;
				background: rgba(255,255,255,0.01);
				font-weight: 600;
			}

			.action-link:hover {
				transform: translateY(-1px);
				box-shadow: 0 6px 14px rgba(0,0,0,0.25);
				background: rgba(255,255,255,0.02);
			}

			.delete-link {
				color: #ffdddd;
				border-color: rgba(244,67,54,0.14);
				background: linear-gradient(180deg, rgba(244,67,54,0.03), rgba(244,67,54,0.01));
			}

			.delete-link:hover {
				border-color: rgba(244,67,54,0.28);
				box-shadow: 0 8px 18px rgba(244,67,54,0.06);
			}

			/* Responsive adjustments */
			@media (max-width: 720px) {
				.detail-card { padding: 18px; margin: 12px; }
				.detail-header { flex-direction: column; align-items: flex-start; gap: 8px; }
				.action-links { justify-content: stretch; }
				.action-link { width: 100%; text-align: center; }
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
					<h2>Détails du Signalement</h2>
					<p>Informations complètes sur votre signalement</p>
				</div>
			</section>

			<section id="wrapper">
				<section class="wrapper style1">
					<div class="inner">
						<div class="detail-card">
							<div class="detail-header">
								<h1 class="detail-title"><?= htmlspecialchars($signalement['titre']) ?></h1>
								<span class="detail-type"><?= htmlspecialchars($signalement['type_nom']) ?></span>
							</div>

							<div class="detail-meta">
								<strong>📅 Date de création :</strong> 
								<?= date('d/m/Y à H:i', strtotime($signalement['created_at'])) ?>
							</div>

							<div class="detail-description">
								<h3>📄 Description :</h3>
								<p><?= nl2br(htmlspecialchars($signalement['description'])) ?></p>
							</div>

							<div class="action-links">
								<a href="mes_signalements.php" class="button small action-link">← Retour à la liste</a>
								<a href="index.php" class="button small primary action-link">➕ Nouveau signalement</a>
								<a href="supprimer_signalement.php?id=<?= $signalement['id'] ?>" 
						   class="button small danger action-link delete-link"
								   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce signalement ?')">
									🗑️ Supprimer
								</a>
							</div>
						</div>
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

