<!DOCTYPE HTML>
<!--
	Solid State by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>
	<head>
		<title>Safe Space - Partagez vos pensées en toute sécurité</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" href="assets/css/main.css" />
		<noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
		<style>
			#signalements-container {
				margin-top: 2em;
			}
			.signalement-card {
				background: rgba(255, 255, 255, 0.05);
				border: 1px solid rgba(255, 255, 255, 0.1);
				border-radius: 4px;
				padding: 1.5em;
				margin-bottom: 1.5em;
			}
			.signalement-card h3 {
				margin-top: 0;
				color: #fff;
			}
			.signalement-type {
				display: inline-block;
				background: rgba(255, 255, 255, 0.1);
				padding: 0.3em 0.8em;
				border-radius: 3px;
				font-size: 0.9em;
				margin-bottom: 0.5em;
			}
			.signalement-date {
				color: rgba(255, 255, 255, 0.6);
				font-size: 0.9em;
				margin-top: 0.5em;
				margin-bottom: 1em;
			}
			.signalement-header {
				display: flex;
				justify-content: space-between;
				align-items: start;
				margin-bottom: 1em;
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
			}
			.search-box input {
				width: 100%;
				padding: 0.8em;
				background: rgba(255, 255, 255, 0.05);
				border: 1px solid rgba(255, 255, 255, 0.1);
				color: #fff;
			}
			.alert {
				padding: 1em;
				margin-bottom: 1em;
				border-radius: 4px;
			}
			.alert-success {
				background: rgba(76, 175, 80, 0.2);
				border: 1px solid rgba(76, 175, 80, 0.5);
				color: #4caf50;
			}
			.alert-error {
				background: rgba(244, 67, 54, 0.2);
				border: 1px solid rgba(244, 67, 54, 0.5);
				color: #f44336;
			}
			.loading {
				text-align: center;
				padding: 2em;
				color: rgba(255, 255, 255, 0.6);
			}
			.error-message {
				color: #f44336;
				font-size: 0.9em;
				margin-top: 0.3em;
				display: none;
			}
				.field.has-error input,
			.field.has-error select,
			.field.has-error textarea {
				border-color: #f44336;
			}
			.field.has-error .error-message {
				display: block;
			}

			/* Type description card styling */
			#type-description .type-desc-card {
				display: flex;
				align-items: flex-start;
				gap: 12px;
				background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
				border: 1px solid rgba(255,255,255,0.06);
				padding: 10px 12px;
				border-radius: 8px;
				margin-top: 8px;
				color: rgba(255,255,255,0.95);
			}

			#type-description .type-desc-icon {
				min-width: 36px;
				height: 36px;
				border-radius: 8px;
				display: flex;
				align-items: center;
				justify-content: center;
				background: linear-gradient(135deg, #667eea, #764ba2);
				color: #fff;
				font-size: 1.0rem;
			}

			#type-description .type-desc-name {
				font-weight: 700;
				color: #ffffff;
				margin: 0 0 3px; 
				font-size: 0.95rem;
			}

			#type-description .type-desc-text {
				margin: 0;
				color: rgba(255,255,255,0.85);
				font-size: 0.9rem;
				line-height: 1.3;
			}
		</style>
	</head>
	<body class="is-preload">

		<!-- Page Wrapper -->
			<div id="page-wrapper">

				<!-- Header -->
					<header id="header" class="alt">
						<h1><a href="index.php">Safe Space</a></h1>
						<nav>
							<a href="#menu">Menu</a>
						</nav>
					</header>

				<!-- Menu -->
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

				<!-- Banner -->
					<section id="banner">
						<div class="inner">
							<div class="col-md-2">
								<div class="main-logo">
									<a href="index.php"><img src="images/logo.png" alt="logo" width="80"></a>
								</div>
							</div>
							<h2>Safe Space</h2>
							<p>Envie de libérer vos émotions ? Partagez vos pensées en toute sécurité.</p>
						</div>
					</section>

				<!-- Wrapper -->
					<section id="wrapper">

						<!-- Formulaire de Signalement -->
							<section id="form-signalement" class="wrapper spotlight style1">
								<div class="inner">
									<div class="content">
										<h2 class="major">Créer un Signalement</h2>
										<p>Partagez votre expérience ou votre préoccupation de manière anonyme et sécurisée.</p>
										
										<div id="alert-container"></div>
										
										<form id="signalement-form">
											<div class="fields">
												<div class="field">
													<label for="titre">Titre *</label>
													<input type="text" name="titre" id="titre" />
													<span class="error-message" id="titre-error"></span>
												</div>
												<div class="field">
													<label for="type_id">Type de signalement *</label>
													<select name="type_id" id="type_id">
															<option value="">Sélectionnez un type</option>
														</select>
													<div id="type-description" class="small" style="margin-top:6px; color: rgba(255,255,255,0.7); display:none;"></div>
													<span class="error-message" id="type_id-error"></span>
												</div>
												<div class="field">
													<label for="description">Description *</label>
													<textarea name="description" id="description" rows="4"></textarea>
													<span class="error-message" id="description-error"></span>
												</div>
											</div>
											<ul class="actions">
												<li><input type="submit" value="Envoyer le Signalement" class="primary" /></li>
												<li><input type="reset" value="Réinitialiser" /></li>
											</ul>
										</form>
									</div>
								</div>
							</section>

						<!-- Liste des Signalements -->
							<section id="liste-signalements" class="wrapper alt style1">
								<div class="inner">
									<h2 class="major">Signalements Récents</h2>
									<p>Découvrez les signalements partagés par la communauté.</p>
									
									<!-- Barre de recherche -->
									<div class="search-box">
										<input type="text" id="search-input" placeholder="Rechercher un signalement..." />
									</div>
									
									<!-- Container pour les signalements -->
									<div id="signalements-container">
										<div class="loading">Chargement des signalements...</div>
									</div>
								</div>
							</section>

					</section>

				<!-- Footer -->
					<section id="footer">
						<div class="inner">
							<h2 class="major">Contactez-nous</h2>
							<p>Si vous avez des questions ou besoin d'aide, n'hésitez pas à nous contacter.</p>
							<ul class="contact">
								<li class="icon solid fa-envelope"><a href="#">contact@safespace.tld</a></li>
								<li class="icon brands fa-twitter"><a href="#">twitter.com/safespace</a></li>
								<li class="icon brands fa-facebook-f"><a href="#">facebook.com/safespace</a></li>
								<li class="icon brands fa-instagram"><a href="#">instagram.com/safespace</a></li>
							</ul>
							<ul class="copyright">
								<li>&copy; Safe Space. All rights reserved.</li><li>Design: <a href="http://html5up.net">HTML5 UP</a></li>
							</ul>
						</div>
					</section>

			</div>

		<!-- Scripts -->
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/jquery.scrollex.min.js"></script>
			<script src="assets/js/browser.min.js"></script>
			<script src="assets/js/breakpoints.min.js"></script>
			<script src="assets/js/util.js"></script>
			<script src="assets/js/main.js"></script>
			
			<!-- Script pour l'intégration avec l'API -->
			<script>
				function computeApiUrl() {
					const baseMatch = window.location.pathname.match(/(.*\/view\/frontoffice)(?:\/|$)/);
					if (baseMatch) return window.location.origin + baseMatch[1] + '/api.php';
					return window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '') + '/api.php';
				}
				const API_URL = computeApiUrl();
				console.debug('index: API_URL=', API_URL);
				// One-time debug call to verify server found config.php
				(function(){
					$.ajax({url: API_URL + '?action=debug_config', method: 'GET', dataType: 'json'})
						.done(function(resp){ console.debug('index debug_config:', resp); })
						.fail(function(jqXHR, status, err){ console.warn('index debug_config failed', status, err); });
				})();

				// delegated handler for delete (keep AJAX delete behavior)
				$(document).on('click', '.ajax-delete', function(e){
					e.preventDefault(); e.stopImmediatePropagation();
					const id = $(this).data('id');
					console.debug('index: ajax-delete clicked id=', id);
					if (!id) return;
					if (!confirm('Êtes-vous sûr de vouloir supprimer ce signalement ?')) return;
					$.ajax({
						url: API_URL + '?action=deleteSignalement&id=' + encodeURIComponent(id),
						type: 'DELETE',
						dataType: 'json'
					}).done(function(resp){
						console.debug('delete resp', resp);
						if (resp && resp.success) {
							$('.ajax-delete[data-id="'+id+'"]').closest('.signalement-card').remove();
							alert('Signalement supprimé');
						} else {
							alert(resp.message || 'Erreur lors de la suppression');
						}
					}).fail(function(){
						alert('Erreur réseau lors de la suppression');
					});
					return false;
				});

				// Charger les types de signalement au chargement de la page
				$(document).ready(function() {
					loadTypes();
					loadSignalements();
					
					// Gérer la soumission du formulaire
					$('#signalement-form').on('submit', function(e) {
						e.preventDefault();
						createSignalement();
					});
					
					// Effacer les erreurs lors de la saisie
					$('#titre').on('input', function() {
						clearFieldError('titre');
					});
					
								$('#type_id').on('change', function() {
						clearFieldError('type_id');
						displayTypeDescription($(this).val());
					});
					
					$('#description').on('input', function() {
						clearFieldError('description');
					});
					
					// Gérer la recherche
					let searchTimeout;
					$('#search-input').on('input', function() {
						clearTimeout(searchTimeout);
						const keyword = $(this).val();
						
						if (keyword.length >= 2 || keyword.length === 0) {
							searchTimeout = setTimeout(() => {
								if (keyword.length === 0) {
									loadSignalements();
								} else {
									searchSignalements(keyword);
								}
							}, 500);
						}
					});
				});
				
				// Charger les types de signalement
				function loadTypes() {
					var typesUrl = API_URL + '?action=getTypes';
					// If running on localhost, use diagnostic endpoint to get more info
					if (location.hostname === 'localhost' || location.hostname === '127.0.0.1') {
						typesUrl = API_URL + '?action=types_debug';
					}

					$.ajax({
						url: typesUrl,
						method: 'GET',
						dataType: 'json',
						success: function(response) {
							console.debug('loadTypes response:', response);
							const select = $('#type_id');
							select.empty();
							select.append($('<option></option>').val('').text('Sélectionnez un type'));
							if (response && response.success && Array.isArray(response.data) && response.data.length) {
								// Store types map for quick lookup and set description on options
								window.safeSpaceTypes = {};
								response.data.forEach(function(type) {
											window.safeSpaceTypes[type.id] = type;
											const $option = $('<option></option>').val(type.id).text(type.nom);
											if (typeof type.description !== 'undefined' && type.description !== null) {
												$option.attr('data-description', type.description);
											}
											select.append($option);
										});
								// Display description for the currently selected value (none by default)
								displayTypeDescription(select.val());
								} else {
									console.warn('Aucun type reçu depuis l’API', response);
									if (response && response.message) showAlert('Erreur lors du chargement des types: ' + response.message, 'error');
								}
						},
						error: function() {
							console.error('Erreur AJAX lors du chargement des types', arguments);
							var jqXHR = arguments[0];
							var msg = 'Erreur lors du chargement des types';
							try {
								if (jqXHR && jqXHR.responseText) {
									var d = JSON.parse(jqXHR.responseText);
									if (d && d.message) msg += ': ' + d.message;
								}
							} catch (e) { /* ignore parse errors */ }
							showAlert(msg, 'error');
						}
					});
				}
				
				// Charger tous les signalements
				function loadSignalements() {
					$('#signalements-container').html('<div class="loading">Chargement des signalements...</div>');
					
					$.ajax({
						url: API_URL + '?action=getSignalements',
						method: 'GET',
						dataType: 'json',
						success: function(response) {
							if (response.success && response.data) {
								displaySignalements(response.data);
							} else {
								$('#signalements-container').html('<p>Aucun signalement disponible.</p>');
							}
						},
						error: function() {
							$('#signalements-container').html('<p class="alert alert-error">Erreur lors du chargement des signalements.</p>');
						}
					});
				}

				// Display the description for the selected type
				function displayTypeDescription(typeId) {
					const $descEl = $('#type-description');
					if (!typeId) {
						$descEl.empty().hide();
						return;
					}
					const type = (window.safeSpaceTypes && window.safeSpaceTypes[typeId]) || null;
					if (type) {
						const name = type.nom || '';
						const desc = type.description || '';
						const $wrapper = $('<div class="type-desc-card"></div>');
						const $icon = $('<div class="type-desc-icon">🏷️</div>');
						const $content = $('<div class="type-desc-content"></div>');
						const $nameEl = $('<div class="type-desc-name"></div>').text(name);
						$content.append($nameEl);
						if (desc.trim() !== '') {
							const $descElText = $('<p class="type-desc-text"></p>').text(desc);
							$content.append($descElText);
						}
						$wrapper.append($icon).append($content);
						$descEl.empty().append($wrapper).show();
					} else {
						$descEl.empty().hide();
					}
				}

				// Receive updates from other tabs via BroadcastChannel or localStorage
				if (window.BroadcastChannel) {
					const bc = new BroadcastChannel('safeSpace-types');
					bc.onmessage = function(ev) {
						if (ev && ev.data && ev.data.type === 'updated') {
							applyTypeUpdate(ev.data.data);
						}
					};
				} else {
					window.addEventListener('storage', function(e) {
						if (e.key === 'safeSpace-types' && e.newValue) {
							try {
								var obj = JSON.parse(e.newValue);
								if (obj && obj.type === 'updated') {
									applyTypeUpdate(obj.data);
								}
							} catch (err) {}
						}
					});
				}

				function applyTypeUpdate(updated) {
					if (!updated) return;
					// Update local map
					window.safeSpaceTypes = window.safeSpaceTypes || {};
					window.safeSpaceTypes[updated.id] = updated;
					// Update select option text and data-description
					var $opt = $('#type_id option[value="' + updated.id + '"]');
					if ($opt.length) {
						$opt.text(updated.nom);
						$opt.attr('data-description', updated.description || '');
					}
					// If the currently selected matches updated, refresh the display
					if ($('#type_id').val() === String(updated.id)) {
						displayTypeDescription(updated.id);
					}
				}
				
				// Rechercher des signalements
				function searchSignalements(keyword) {
					$('#signalements-container').html('<div class="loading">Recherche en cours...</div>');
					
					// Utiliser l'API de recherche AJAX existante
					$.ajax({
						url: 'signalements/recherche_ajax.php?search=' + encodeURIComponent(keyword),
						method: 'GET',
						dataType: 'json',
						success: function(response) {
							if (response.count > 0) {
								$('#signalements-container').html(response.html);
							} else {
								$('#signalements-container').html('<p>Aucun résultat trouvé pour "' + keyword + '".</p>');
							}
						},
						error: function() {
							// Fallback vers l'API principale
							$.ajax({
								url: API_URL + '?action=search&keyword=' + encodeURIComponent(keyword),
								method: 'GET',
								dataType: 'json',
								success: function(response) {
									if (response.success && response.data) {
										if (response.data.length > 0) {
											displaySignalements(response.data);
										} else {
											$('#signalements-container').html('<p>Aucun résultat trouvé pour "' + keyword + '".</p>');
										}
									} else {
										$('#signalements-container').html('<p>Aucun résultat trouvé.</p>');
									}
								},
								error: function() {
									$('#signalements-container').html('<p class="alert alert-error">Erreur lors de la recherche.</p>');
								}
							});
						}
					});
				}
				
				// Afficher les signalements
				function displaySignalements(signalements) {
					if (signalements.length === 0) {
						$('#signalements-container').html('<p>Aucun signalement disponible.</p>');
						return;
					}

					// Show detail modal (same as mes_signalements.js behavior)
					function createDetailModal() {
						if ($('#detailModal').length) return;
						$('body').append(`
						<div id="detailModal" class="modal" style="display:none;">
							<div class="inner" style="max-width:700px;margin:4em auto;background:#111;padding:1.5em;border-radius:8px;">
								<h3 id="detail-title"></h3>
								<div id="detail-type" style="margin-bottom:.5em;color:#9fb8ff;font-weight:700;"></div>
								<div id="detail-date" style="color:#ccc;margin-bottom:1em;"></div>
								<div id="detail-desc" style="background:#0b0b0b;padding:1em;border-radius:6px;color:#ddd;"></div>
								<div style="margin-top:1em;text-align:right;">
									<button id="detail-close" class="button">Fermer</button>
								</div>
							</div>
						</div>
						`);
						$('#detail-close').on('click', function(){ $('#detailModal').hide(); });
					}

					function showDetail(id) {
						createDetailModal();
						$('#detail-title').text('Chargement...');
						$('#detail-type').text('');
						$('#detail-date').text('');
						$('#detail-desc').text('');
						$('#detailModal').show();
						// Try API first, fallback to HTML page extraction
						$.ajax({
							url: API_URL + '?action=getSignalement&id=' + encodeURIComponent(id),
							method: 'GET',
							dataType: 'json',
							timeout: 5000
						}).done(function(resp){
							console.debug('index showDetail resp', resp);
							if (resp && resp.success && resp.data) {
								const d = resp.data;
								$('#detail-title').text(d.titre);
								$('#detail-type').text(d.type_nom || '—');
								$('#detail-date').text(new Date(d.created_at).toLocaleString('fr-FR'));
								$('#detail-desc').html(escapeHtml(d.description).replace(/\n/g,'<br>'));
							} else {
								$('#detail-title').text('Erreur: détail non disponible');
							}
						}).fail(function(jqXHR, textStatus, errorThrown){
							console.warn('index showDetail API error', textStatus, errorThrown);
							$('#detail-title').text('Erreur réseau ou serveur. Réessayez plus tard.');
						}).always(function(jqXHR, textStatus){
							console.debug('index showDetail AJAX complete, status=', textStatus, 'id=', id);
						});
					}
					
					let html = '';
					signalements.forEach(function(signalement) {
						const date = new Date(signalement.created_at);
						const formattedDate = date.toLocaleDateString('fr-FR', {
							year: 'numeric',
							month: 'long',
							day: 'numeric',
							hour: '2-digit',
							minute: '2-digit'
						});
						
						const description = escapeHtml(signalement.description);
						const shortDescription = description.length > 150 ? description.substring(0, 150) + '...' : description;
						
						html += '<div class="signalement-card">';
						html += '<div class="signalement-header">';
						html += '<h3>' + escapeHtml(signalement.titre) + '</h3>';
						html += '<span class="signalement-type">' + (signalement.type_nom || 'Non spécifié') + '</span>';
						html += '</div>';
						html += '<div class="signalement-date">📅 Publié le ' + formattedDate + '</div>';
						html += '<p>' + shortDescription + '</p>';
						html += '<div class="signalement-actions">';
						html += '<a href="signalements/detail_signalement.php?id=' + signalement.id + '" data-id="' + signalement.id + '" class="button small action-link detail-link">👁️ Voir détails</a>';							html += '<a href="signalements/modifier_signalement.php?id=' + signalement.id + '" class="button small primary action-link edit-link">✏️ Modifier</a>';						html += '<a href="javascript:void(0);" data-id="' + signalement.id + '" class="button small danger action-link delete-link ajax-delete">🗑️ Supprimer</a>';
						html += '</div>';
						html += '</div>';
					});
					
					$('#signalements-container').html(html);
				}
				
				// Créer un nouveau signalement
				function createSignalement() {
					// Réinitialiser les erreurs
					clearErrors();
					
					// Récupérer les valeurs
					const titre = $('#titre').val().trim();
					const typeId = $('#type_id').val();
					const description = $('#description').val().trim();
					
					// Variables pour stocker les erreurs
					let hasErrors = false;
					
					// Validation du titre
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
					if (typeId === '') {
						showError('type_id', 'Veuillez sélectionner un type de signalement');
						hasErrors = true;
					}
					
					// Validation de la description
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
					
					// Si erreurs, arrêter ici
					if (hasErrors) {
						return;
					}
					
					// Préparer les données
					const formData = {
						titre: titre,
						type_id: typeId,
						description: description
					};
					
					// Envoyer la requête AJAX
					$.ajax({
						url: API_URL + '?action=createSignalement',
						method: 'POST',
						contentType: 'application/json',
						data: JSON.stringify(formData),
						dataType: 'json',
						success: function(response) {
							if (response.success) {
								showAlert(response.message || 'Signalement créé avec succès !', 'success');
								$('#signalement-form')[0].reset();
								clearErrors();
								loadSignalements();
							} else {
								let errorMsg = response.message || 'Erreur lors de la création du signalement';
								if (response.errors && response.errors.length > 0) {
									errorMsg = response.errors.join('<br>');
								}
								showAlert(errorMsg, 'error');
							}
						},
						error: function() {
							showAlert('Erreur lors de la création du signalement', 'error');
						}
					});
				}
				
				// Fonction pour afficher une erreur
				function showError(fieldId, message) {
					const field = $('#' + fieldId);
					const errorElement = $('#' + fieldId + '-error');
					const fieldContainer = field.closest('.field');
					
					fieldContainer.addClass('has-error');
					errorElement.text(message);
					errorElement.show();
				}
				
				// Fonction pour effacer toutes les erreurs
				function clearErrors() {
					$('.error-message').hide().text('');
					$('.field').removeClass('has-error');
				}
				
				// Fonction pour effacer l'erreur d'un champ spécifique
				function clearFieldError(fieldId) {
					const field = $('#' + fieldId);
					const errorElement = $('#' + fieldId + '-error');
					const fieldContainer = field.closest('.field');
					
					fieldContainer.removeClass('has-error');
					errorElement.hide().text('');
				}
				
				// Afficher une alerte
				function showAlert(message, type) {
					const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
					const alertHtml = '<div class="alert ' + alertClass + '">' + message + '</div>';
					$('#alert-container').html(alertHtml);
					
					// Faire défiler vers l'alerte
					$('html, body').animate({
						scrollTop: $('#form-signalement').offset().top - 100
					}, 500);
					
					// Supprimer l'alerte après 5 secondes
					setTimeout(function() {
						$('#alert-container').fadeOut(function() {
							$(this).html('').show();
						});
					}, 5000);
				}
				
				// Échapper le HTML pour éviter les injections XSS
				function escapeHtml(text) {
					const map = {
						'&': '&amp;',
						'<': '&lt;',
						'>': '&gt;',
						'"': '&quot;',
						"'": '&#039;'
					};
					return text ? text.replace(/[&<>"']/g, function(m) { return map[m]; }) : '';
				}
			</script>

	</body>
</html>

