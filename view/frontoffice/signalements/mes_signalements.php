<?php
// Détection automatique du chemin vers la racine
$rootPath = dirname(dirname(dirname(__DIR__)));
$configPath = $rootPath . DIRECTORY_SEPARATOR . 'config.php';

// Si config.php n'est pas trouvé, essayer un niveau au-dessus (pour double dossier)
if (!file_exists($configPath)) {
    $rootPath = dirname($rootPath);
    $configPath = $rootPath . DIRECTORY_SEPARATOR . 'config.php';
}

require_once $configPath;

// Chemins vers model et controller
$modelPath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'model';
$controllerPath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'controller';

// Si model n'existe pas à cet endroit, essayer un niveau au-dessus
if (!is_dir($modelPath)) {
    $modelPath = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'model';
    $controllerPath = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'controller';
}

require_once $modelPath . DIRECTORY_SEPARATOR . 'Signalement.php';
require_once $modelPath . DIRECTORY_SEPARATOR . 'Type.php';
require_once $controllerPath . DIRECTORY_SEPARATOR . 'TypeController.php';
require_once $controllerPath . DIRECTORY_SEPARATOR . 'SignalementController.php';

// Utiliser la connexion depuis config.php à la racine
if (!isset($db) || !$db) {
    $database = new Database();
    $db = $database->getConnection();
}

$signalementController = new SignalementController($db);

// Chargement initial des signalements
$signalements = $signalementController->getAllSignalements();

// Message de succès après suppression
$message_suppression = '';
if (isset($_GET['message']) && $_GET['message'] === 'supprime') {
    $message_suppression = '✅ Signalement supprimé avec succès !';
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Mes Signalements - Safe Space</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" href="../assets/css/main.css" />
		<noscript><link rel="stylesheet" href="../assets/css/noscript.css" /></noscript>
		<style>
			.signalement-card {
				background: rgba(255, 255, 255, 0.05);
				border: 1px solid rgba(255, 255, 255, 0.1);
				border-radius: 4px;
				padding: 1.5em;
				margin-bottom: 1.5em;
			}
			.signalement-header {
				display: flex;
				justify-content: space-between;
				align-items: start;
				margin-bottom: 1em;
			}
			.signalement-title {
				margin: 0;
				color: #fff;
			}
			.signalement-type {
				display: inline-block;
				background: rgba(255, 255, 255, 0.1);
				padding: 0.3em 0.8em;
				border-radius: 3px;
				font-size: 0.9em;
			}
			.signalement-date {
				color: rgba(255, 255, 255, 0.6);
				font-size: 0.9em;
				margin: 0.5em 0 1em 0;
			}
			.signalement-actions {
				margin-top: 1.5em;
				display: flex;
				gap: 1em;
			}
			.action-link {
				color: rgba(255, 255, 255, 0.8);
				text-decoration: none;
				padding: 0.5em 1em;
				border: 1px solid rgba(255, 255, 255, 0.2);
				border-radius: 4px;
				transition: all 0.3s;
			}
			.action-link:hover {
				background: rgba(255, 255, 255, 0.1);
				border-color: rgba(255, 255, 255, 0.4);
			}
			.delete-link {
				color: rgba(244, 67, 54, 0.9);
				border-color: rgba(244, 67, 54, 0.5);
			}
			.delete-link:hover {
				background: rgba(244, 67, 54, 0.2);
				border-color: rgba(244, 67, 54, 0.8);
			}
			.search-box {
				margin-bottom: 2em;
				position: relative;
			}
			.search-box input {
				width: 100%;
				padding: 0.8em;
				background: rgba(255, 255, 255, 0.05);
				border: 1px solid rgba(255, 255, 255, 0.1);
				color: #fff;
			}
			.loading {
				display: none;
				position: absolute;
				right: 10px;
				top: 50%;
				transform: translateY(-50%);
				color: rgba(255, 255, 255, 0.6);
			}
			.success-message {
				background: rgba(76, 175, 80, 0.2);
				border: 1px solid rgba(76, 175, 80, 0.5);
				color: #4caf50;
				padding: 1em;
				border-radius: 4px;
				margin-bottom: 2em;
			}
			.empty-state {
				text-align: center;
				padding: 3em 2em;
				color: rgba(255, 255, 255, 0.6);
			}
			.results-info {
				background: rgba(33, 150, 243, 0.2);
				border: 1px solid rgba(33, 150, 243, 0.5);
				padding: 1em;
				border-radius: 4px;
				margin-bottom: 2em;
			}
		</style>
	</head>
	<body class="is-preload">
		<div id="page-wrapper">
			<header id="header" class="alt">
					<h1><a href="../index.php">Safe Space</a></h1>
					<a href="#menu">Menu</a>
				</nav>
			</header>

			<nav id="menu">
				<div class="inner">
					<h2>Menu</h2>
					<ul class="links">
						<li><a href="../index.php">Home</a></li>
					<li><a href="../mes_signalements.php">Mes Signalements</a></li>
					<li><a href="../index.php">Nouveau Signalement</a></li>
						<li><a href="../../backoffice/index.php" target="_blank">Admin</a></li>
					<li><a href="../elements.html">Profile</a></li>
					<li><a href="../login.html">Log In</a></li>
					<li><a href="../register.html">Sign Up</a></li>
					</ul>
					<a href="#" class="close">Close</a>
				</div>
			</nav>

			<section id="banner">
				<div class="inner">
					<h2>📋 Mes Signalements</h2>
					<p>Gérez tous vos signalements en un seul endroit</p>
				</div>
			</section>

			<section id="wrapper">
				<section class="wrapper style1">
					<div class="inner">
						<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2em;">
						<div style="display:flex;align-items:center;gap:1em;">
							<a href="../index.php" class="button">← Retour</a>
							<h2 class="major">Mes Signalements</h2>
						</div>
						</div>

						<?php if ($message_suppression): ?>
							<div class="success-message">
								<?= htmlspecialchars($message_suppression) ?>
							</div>
						<?php endif; ?>

						<div class="search-box">
							<input type="text" 
								   id="searchInput" 
								   placeholder="🔍 Rechercher en temps réel..." 
								   onkeyup="searchSignalements()">
							<span class="loading" id="loadingIndicator">⏳ Recherche...</span>
						</div>

						<div id="resultsInfo" class="results-info" style="display: none;">
							<strong>🔍 Résultats en temps réel :</strong> 
							<span id="searchTerm"></span>
							<span id="resultsCount" style="color: rgba(255, 255, 255, 0.6); margin-left: 10px;"></span>
						</div>

						<div id="signalementsContainer">
							<?php if (empty($signalements)): ?>
								<div class="empty-state">
									<h3>📭 Aucun signalement pour le moment</h3>
									<p>Soyez le premier à ajouter un signalement !</p>
									<a href="../index.php" class="button primary">Créer un signalement</a>
								</div>
							<?php else: ?>
								<?php foreach ($signalements as $signalement): ?>
									<div class="signalement-card">
										<div class="signalement-header">
											<h3 class="signalement-title"><?= htmlspecialchars($signalement['titre']) ?></h3>
											<span class="signalement-type"><?= htmlspecialchars($signalement['type_nom']) ?></span>
										</div>
										
										<div class="signalement-date">
											📅 <?= date('d/m/Y à H:i', strtotime($signalement['created_at'])) ?>
										</div>

										<p><?= nl2br(htmlspecialchars(substr($signalement['description'], 0, 150))) ?>...</p>

										<div class="signalement-actions">
											<a href="detail_signalement.php?id=<?= $signalement['id'] ?>" data-id="<?= $signalement['id'] ?>" class="button small action-link detail-link">
												👁️ Voir détails
											</a>
												<a href="modifier_signalement.php?id=<?= $signalement['id'] ?>" class="button small primary action-link edit-link">✏️ Modifier</a>
											<noscript><a href="detail_signalement.php?id=<?= $signalement['id'] ?>" class="action-link">👁️ Voir détails</a></noscript>
											<a href="javascript:void(0);" data-id="<?= $signalement['id'] ?>" class="button small danger action-link delete-link ajax-delete">
												🗑️ Supprimer
											</a>
											<noscript><a href="supprimer_signalement.php?id=<?= $signalement['id'] ?>" class="action-link delete-link">🗑️ Supprimer</a></noscript>
										</div>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
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

		<script src="../assets/js/jquery.min.js"></script>
		<script src="../assets/js/jquery.scrollex.min.js"></script>
		<script src="../assets/js/browser.min.js"></script>
		<script src="../assets/js/breakpoints.min.js"></script>
		<script src="../assets/js/util.js"></script>
		<script src="../assets/js/main.js"></script>
		
		<script>
		function computeApiUrl() {
			const baseMatch = window.location.pathname.match(/(.*\/view\/frontoffice)(?:\/|$)/);
			if (baseMatch) return window.location.origin + baseMatch[1] + '/api.php';
			return window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '') + '/api.php';
		}
		const API_URL = computeApiUrl();
		console.debug('signalements/mes_signalements: API_URL=', API_URL);
		// One-time debug call to verify server found config.php
		(function(){
			$.ajax({url: API_URL + '?action=debug_config', method: 'GET', dataType: 'json'})
				.done(function(resp){ console.debug('signalements/mes debug_config:', resp); })
				.fail(function(jqXHR, status, err){ console.warn('signalements/mes debug_config failed', status, err); });
		})();

		// Delegated handler for delete (AJAX)
		$(document).on('click', '.ajax-delete', function(e){
			e.preventDefault(); e.stopImmediatePropagation();
			var id = $(this).data('id');
			if (!id) return;
			if (!confirm('Êtes-vous sûr de vouloir supprimer ce signalement ?')) return;
			$.ajax({ url: API_URL + '?action=deleteSignalement&id=' + encodeURIComponent(id), type: 'DELETE', dataType: 'json'})
			.done(function(resp){ if (resp && resp.success) { $('.ajax-delete[data-id="'+id+'"]').closest('.signalement-card').remove(); alert('Signalement supprimé'); } else { alert(resp.message || 'Erreur'); } })
			.fail(function(){ alert('Erreur réseau'); });
			return false;
		});

		function searchSignalements() {
			const searchTerm = document.getElementById('searchInput').value;
			const loadingIndicator = document.getElementById('loadingIndicator');
			const resultsInfo = document.getElementById('resultsInfo');
			const searchTermSpan = document.getElementById('searchTerm');
			const resultsCount = document.getElementById('resultsCount');
			
			if (searchTerm.length === 0) {
                window.location.href = '../mes_signalements.php';
			}
			
			if (searchTerm.length < 2) {
				return;
			}
			
			loadingIndicator.style.display = 'block';
			
			const xhr = new XMLHttpRequest();
			xhr.open('GET', 'signalements/recherche_ajax.php?search=' + encodeURIComponent(searchTerm), true);
			
			xhr.onload = function() {
				loadingIndicator.style.display = 'none';
				
				if (xhr.status === 200) {
					const response = JSON.parse(xhr.responseText);
					
					searchTermSpan.textContent = '"' + searchTerm + '"';
					resultsCount.textContent = '(' + response.count + ' résultat(s))';
					resultsInfo.style.display = 'block';
					
					document.getElementById('signalementsContainer').innerHTML = response.html;
				}
			};
			
			xhr.send();
		}

		let searchTimeout;
		document.getElementById('searchInput').addEventListener('input', function() {
			clearTimeout(searchTimeout);
			searchTimeout = setTimeout(searchSignalements, 500);
		});
		</script>
	</body>
</html>
