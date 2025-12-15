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
    // Use central config DB connection
    $db = config::getConnexion();
}

$signalementController = new SignalementController($db);
$types = $signalementController->getTypesForForm();

$message = '';
$success = false;
$errors = [];

// Traitement du formulaire avec validation PHP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['titre'])) {
        $errors[] = "Le titre est obligatoire";
    }
    
    if (empty($_POST['type_id'])) {
        $errors[] = "Le type est obligatoire";
    }
    
    if (empty($_POST['description'])) {
        $errors[] = "La description est obligatoire";
    }
    
    // Si pas d'erreurs, créer le signalement
    if (empty($errors)) {
        $result = $signalementController->createSignalement($_POST);
        if ($result['success']) {
            $message = $result['message'];
            $success = true;
            $_POST = []; // Reset du formulaire
        } else {
            $message = $result['message'];
        }
    } else {
        $message = "Veuillez corriger les erreurs suivantes :";
    }
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Ajouter un Signalement - Safe Space</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" href="../assets/css/main.css" />
		<noscript><link rel="stylesheet" href="../assets/css/noscript.css" /></noscript>
		<style>
			.form-card {
				background: rgba(255, 255, 255, 0.05);
				border: 1px solid rgba(255, 255, 255, 0.1);
				border-radius: 4px;
				padding: 2em;
				max-width: 600px;
				margin: 0 auto;
			}
			.message {
				padding: 1em;
				margin-bottom: 1.5em;
				border-radius: 4px;
			}
			.success {
				background: rgba(76, 175, 80, 0.2);
				border: 1px solid rgba(76, 175, 80, 0.5);
				color: #4caf50;
			}
			.error {
				background: rgba(244, 67, 54, 0.2);
				border: 1px solid rgba(244, 67, 54, 0.5);
				color: #f44336;
			}
			.error-list {
				background: rgba(244, 67, 54, 0.2);
				border: 1px solid rgba(244, 67, 54, 0.5);
				color: #f44336;
				padding: 1em;
				border-radius: 4px;
				margin-bottom: 1.5em;
			}
			.error-list ul {
				margin: 0.5em 0 0 0;
				padding-left: 1.5em;
			}
			.form-group {
				margin-bottom: 1.5em;
			}
			.form-group label {
				display: block;
				margin-bottom: 0.5em;
				font-weight: bold;
				color: #fff;
			}
			.form-group input,
			.form-group select,
			.form-group textarea {
				width: 100%;
				padding: 0.8em;
				background: rgba(255, 255, 255, 0.05);
				border: 1px solid rgba(255, 255, 255, 0.1);
				border-radius: 4px;
				color: #fff;
				box-sizing: border-box;
			}
			.form-group textarea {
				height: 150px;
				resize: vertical;
			}
			.form-actions {
				display: flex;
				gap: 1em;
				margin-top: 2em;
			}
			.error-message {
				color: #f44336;
				font-size: 0.9em;
				margin-top: 0.3em;
				display: none;
			}
			.form-group.has-error input,
			.form-group.has-error select,
			.form-group.has-error textarea {
				border-color: #f44336;
			}
			.form-group.has-error .error-message {
				display: block;
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
					<h2>📝 Ajouter un Signalement</h2>
					<p>Partagez votre expérience ou votre préoccupation</p>
				</div>
			</section>

			<section id="wrapper">
				<section class="wrapper style1">
					<div class="inner">
						<div class="form-card">
							<?php if ($message): ?>
								<div class="message <?= $success ? 'success' : 'error' ?>">
									<?= htmlspecialchars($message) ?>
								</div>
							<?php endif; ?>

							<?php if (!empty($errors)): ?>
								<div class="error-list">
									<ul>
										<?php foreach ($errors as $error): ?>
											<li><?= htmlspecialchars($error) ?></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>

							<form method="POST" id="signalement-form">
								<div class="form-group">
									<label for="titre">Titre du signalement *</label>
									<input type="text" id="titre" name="titre" 
										   value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>">
									<span class="error-message" id="titre-error"></span>
								</div>

								<div class="form-group">
									<label for="type_id">Type de signalement *</label>
									<select id="type_id" name="type_id">
										<option value="">Sélectionnez un type</option>
										<?php if (!empty($types) && is_array($types)): ?>
											<?php foreach ($types as $type): ?>
												<option value="<?= $type['id'] ?>" 
													<?= (isset($_POST['type_id']) && $_POST['type_id'] == $type['id']) ? 'selected' : '' ?>>
													<?= htmlspecialchars($type['nom']) ?>
												</option>
											<?php endforeach; ?>
										<?php else: ?>
											<option value="" disabled>Aucun type disponible</option>
										<?php endif; ?>
									</select>
									<span class="error-message" id="type_id-error"></span>
								</div>

								<div class="form-group">
									<label for="description">Description détaillée *</label>
									<textarea id="description" name="description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
									<span class="error-message" id="description-error"></span>
								</div>

								<div class="form-actions">
									<input type="submit" value="➕ Ajouter le Signalement" class="button primary" />
									<a href="<?= dirname(dirname($_SERVER['SCRIPT_NAME'])) . '/mes_signalements.php' ?>" class="button">← Retour à la liste</a>
								</div>
							</form>
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
			// Validation JavaScript du formulaire
			document.getElementById('signalement-form').addEventListener('submit', function(e) {
				e.preventDefault();
				
				// Réinitialiser les erreurs
				clearErrors();
				
				// Variables pour stocker les erreurs
				let hasErrors = false;
				
				// Validation du titre
				const titre = document.getElementById('titre').value.trim();
				if (titre === '') {
					showError('titre', 'Le titre est obligatoire');
					hasErrors = true;
				} else if (titre.length < 3) {
					showError('titre', 'Le titre doit contenir au moins 3 caractères');
					hasErrors = true;
				} else if (titre.length > 200) {
					showError('titre', 'Le titre ne doit pas dépasser 200 caractères');
					hasErrors = true;
				}
				
				// Validation du type
				const typeId = document.getElementById('type_id').value;
				if (typeId === '') {
					showError('type_id', 'Veuillez sélectionner un type de signalement');
					hasErrors = true;
				}
				
				// Validation de la description
				const description = document.getElementById('description').value.trim();
				if (description === '') {
					showError('description', 'La description est obligatoire');
					hasErrors = true;
				} else if (description.length < 10) {
					showError('description', 'La description doit contenir au moins 10 caractères');
					hasErrors = true;
				} else if (description.length > 2000) {
					showError('description', 'La description ne doit pas dépasser 2000 caractères');
					hasErrors = true;
				}
				
				// Si pas d'erreurs, soumettre le formulaire
				if (!hasErrors) {
					this.submit();
				}
			});
			
			// Fonction pour afficher une erreur
			function showError(fieldId, message) {
				const field = document.getElementById(fieldId);
				const errorElement = document.getElementById(fieldId + '-error');
				const formGroup = field.closest('.form-group');
				
				formGroup.classList.add('has-error');
				errorElement.textContent = message;
				errorElement.style.display = 'block';
			}
			
			// Fonction pour effacer toutes les erreurs
			function clearErrors() {
				const errorMessages = document.querySelectorAll('.error-message');
				const formGroups = document.querySelectorAll('.form-group');
				
				errorMessages.forEach(function(error) {
					error.style.display = 'none';
					error.textContent = '';
				});
				
				formGroups.forEach(function(group) {
					group.classList.remove('has-error');
				});
			}
			
			// Effacer les erreurs lors de la saisie
			document.getElementById('titre').addEventListener('input', function() {
				clearFieldError('titre');
			});
			
			document.getElementById('type_id').addEventListener('change', function() {
				clearFieldError('type_id');
			});
			
			document.getElementById('description').addEventListener('input', function() {
				clearFieldError('description');
			});
			
			// Fonction pour effacer l'erreur d'un champ spécifique
			function clearFieldError(fieldId) {
				const field = document.getElementById(fieldId);
				const errorElement = document.getElementById(fieldId + '-error');
				const formGroup = field.closest('.form-group');
				
				formGroup.classList.remove('has-error');
				errorElement.style.display = 'none';
				errorElement.textContent = '';
			}
		</script>
	</body>
</html>
