<?php
require_once '../../config.php';

// Récupérer et nettoyer les messages/valeurs de formulaire
$loginErrors = $_SESSION['login_errors'] ?? [];
unset($_SESSION['login_errors']);

$oldLoginEmail = $_SESSION['old_email'] ?? '';
unset($_SESSION['old_email']);

$registerErrors = $_SESSION['register_errors'] ?? [];
$oldRegister = $_SESSION['old_data'] ?? [];
unset($_SESSION['register_errors'], $_SESSION['old_data']);

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE HTML>
<!--
	Solid State by HTML5 UP
	html5up.net | @ajlkn
	Adapté pour SAFEProject (page principale Login/Signup)
-->
<html>
	<head>
		<title>SAFEProject - Accueil</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" href="assets/css/main.css" />
		<noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
	</head>
	<body class="is-preload">

		<!-- Page Wrapper -->
			<div id="page-wrapper">

				<!-- Header -->
					<header id="header">
						<h1><a href="profil.php">SAFEProject</a></h1>
						<nav>
							<a href="#menu">Menu</a>
						</nav>
					</header>

				<!-- Menu -->
					<nav id="menu">
						<div class="inner">
							<h2>Menu</h2>
							<ul class="links">
								<li><a href="#login-section">Se connecter</a></li>
								<li><a href="#signup-section">Créer un compte</a></li>
								<li><a href="index.html">Accueil (site)</a></li>
								<li><a href="elements.html">Elements</a></li>
							</ul>
							<a href="#" class="close">Close</a>
						</div>
					</nav>

				<!-- Wrapper -->
					<section id="wrapper">
						<header>
							<div class="inner">
								<h2>Bienvenue sur SAFEProject</h2>
								<p>Accédez à votre espace en quelques secondes.</p>
								
								<!-- User Guide Download Button -->
								<div style="margin-top: 2rem;">
									<a href="../../controller/generate_user_guide.php" 
									   class="button primary icon solid fa-download" 
									   target="_blank"
									   style="background: #dc3545; border-color: #dc3545; font-weight: bold;">
										📚 Télécharger le Guide Utilisateur (PDF)
									</a>
									<p style="margin-top: 0.5rem; font-size: 0.9em; opacity: 0.8;">
										Découvrez comment utiliser la plateforme avant de vous inscrire
									</p>
								</div>
							</div>
						</header>

						<!-- Content -->
							<div class="wrapper">
								<div class="inner">
									<div class="split style1">
										<section id="login-section">
											<h3 class="major">Se connecter</h3>
											<?php if (!empty($loginErrors)): ?>
												<div class="alert">
													<ul>
														<?php foreach ($loginErrors as $err): ?>
															<li><?php echo secureOutput($err); ?></li>
														<?php endforeach; ?>
													</ul>
												</div>
											<?php endif; ?>

											<form method="post" action="../../controller/auth/login.php">
												<div class="fields">
													<div class="field">
														<label for="login-email">Email</label>
														<input type="email" name="email" id="login-email" value="<?php echo secureOutput($oldLoginEmail); ?>" required />
													</div>
													<div class="field">
														<label for="login-password">Mot de passe</label>
														<input type="password" name="password" id="login-password" required />
													</div>
												</div>
												<ul class="actions">
													<li><input type="submit" value="Connexion" class="primary" /></li>
												</ul>
											</form>
										</section>

										<section id="signup-section">
											<h3 class="major">Créer un compte</h3>
											<?php if (!empty($registerErrors)): ?>
												<div class="alert">
													<ul>
														<?php foreach ($registerErrors as $err): ?>
															<li><?php echo secureOutput($err); ?></li>
														<?php endforeach; ?>
													</ul>
												</div>
											<?php endif; ?>

											<form method="post" action="../../controller/auth/register.php">
												<input type="hidden" name="csrf_token" value="<?php echo secureOutput($csrfToken); ?>" />
												<input type="hidden" name="role" value="user" />
												<div class="fields">
													<div class="field half">
														<label for="signup-nom">Nom</label>
														<input type="text" name="nom" id="signup-nom" value="<?php echo secureOutput($oldRegister['nom'] ?? ''); ?>" required />
													</div>
													<div class="field half">
														<label for="signup-prenom">Prénom</label>
														<input type="text" name="prenom" id="signup-prenom" value="<?php echo secureOutput($oldRegister['prenom'] ?? ''); ?>" required />
													</div>
													<div class="field">
														<label for="signup-email">Email</label>
														<input type="email" name="email" id="signup-email" value="<?php echo secureOutput($oldRegister['email'] ?? ''); ?>" required />
													</div>
													<div class="field half">
														<label for="signup-password">Mot de passe</label>
														<input type="password" name="password" id="signup-password" required />
													</div>
													<div class="field half">
														<label for="signup-password-confirm">Confirmer le mot de passe</label>
														<input type="password" name="password_confirm" id="signup-password-confirm" required />
													</div>
												</div>
												<ul class="actions">
													<li><input type="submit" value="Créer mon compte" class="primary" /></li>
												</ul>
											</form>
										</section>
									</div>
								</div>
							</div>

					</section>

				<!-- Footer -->
					<section id="footer">
						<div class="inner">
							<h2 class="major">Besoin d'aide ?</h2>
							<p>Contactez notre équipe pour toute question.</p>
							<ul class="contact">
								<li class="icon solid fa-envelope"><a href="mailto:info@safeproject.com">info@safeproject.com</a></li>
							</ul>
							<ul class="copyright">
								<li>&copy; SAFEProject. All rights reserved.</li><li>Design: HTML5 UP</li>
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

	</body>
</html>
<?php
session_start();
require_once '../../config.php';
require_once '../../model/User.php';
require_once '../../controller/helpers.php';

// Check if logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$user = new User($userId);
$currentRole = $_SESSION['role'] ?? 'user';
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - SAFEProject</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

<?php include '../includes/navbar.php'; ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-user-circle"></i> My Profile
                    </h3>
                </div>
                <div class="card-body">
                    <?php if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo $flash['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <form action="../../controller/auth/update_profile.php" method="POST" id="profileForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="text-center mb-4">
                            <div class="display-1 text-primary">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <h4 class="mt-2">Mon Profil</h4>
                        </div>

                        <!-- Informations de base -->
                        <h5 class="mb-3"><i class="fas fa-info-circle text-primary"></i> Informations personnelles</h5>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="prenom" class="form-label">
                                    <i class="fas fa-user text-primary"></i> Prénom <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="prenom" name="prenom" 
                                       value="<?php echo htmlspecialchars($user->getPrenom()); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="nom" class="form-label">
                                    <i class="fas fa-user text-primary"></i> Nom <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="nom" name="nom" 
                                       value="<?php echo htmlspecialchars($user->getNom()); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope text-primary"></i> Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user->getEmail()); ?>" required>
                        </div>

                        <!-- Informations spécifiques aux conseillers -->
                        <?php if ($currentRole === 'counselor'): ?>
                        <hr class="my-4">
                        <h5 class="mb-3"><i class="fas fa-user-md text-success"></i> Informations professionnelles</h5>
                        
                        <div class="mb-3">
                            <label for="specialite" class="form-label">
                                <i class="fas fa-graduation-cap text-success"></i> Spécialité
                            </label>
                            <input type="text" class="form-control" id="specialite" name="specialite" 
                                   value="<?php echo htmlspecialchars($user->getSpecialite() ?? ''); ?>"
                                   placeholder="Ex: Psychologie clinique, Gestion du stress...">
                        </div>

                        <div class="mb-3">
                            <label for="biographie" class="form-label">
                                <i class="fas fa-file-alt text-success"></i> Biographie
                            </label>
                            <textarea class="form-control" id="biographie" name="biographie" rows="5"
                                      placeholder="Décrivez votre parcours professionnel, vos compétences..."><?php echo htmlspecialchars($user->getBiographie() ?? ''); ?></textarea>
                        </div>
                        <?php endif; ?>

                        <!-- Changement de mot de passe -->
                        <hr class="my-4">
                        <h5 class="mb-3"><i class="fas fa-lock text-warning"></i> Changer le mot de passe</h5>
                        <p class="text-muted small">Laissez vide si vous ne souhaitez pas changer votre mot de passe.</p>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-key text-warning"></i> Nouveau mot de passe
                            </label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   minlength="6" placeholder="Minimum 6 caractères">
                        </div>

                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">
                                <i class="fas fa-key text-warning"></i> Confirmer le mot de passe
                            </label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                                   minlength="6" placeholder="Répétez le mot de passe">
                        </div>

                        <!-- Informations en lecture seule -->
                        <hr class="my-4">
                        <h5 class="mb-3"><i class="fas fa-info-circle text-info"></i> Informations système</h5>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted">ID:</label>
                                <p class="form-control-plaintext"><?php echo $user->getId(); ?></p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted">Rôle:</label>
                                <p>
                                    <span class="badge bg-<?php echo $user->getRole() === 'admin' ? 'danger' : ($user->getRole() === 'counselor' ? 'success' : 'info'); ?> fs-6">
                                        <?php echo $user->getRole() === 'admin' ? 'admin' : ucfirst($user->getRole()); ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted">Statut:</label>
                                <p>
                                    <span class="badge bg-<?php echo $user->getStatut() === 'actif' ? 'success' : 'secondary'; ?> fs-6">
                                        <?php echo ucfirst($user->getStatut()); ?>
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted">Membre depuis:</label>
                            <p class="form-control-plaintext"><?php echo date('d/m/Y', strtotime($user->getDateInscription())); ?></p>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="<?php echo $currentRole === 'counselor' || $currentRole === 'admin' ? '../backoffice/support/dashboard_counselor.php' : 'dashboard.php'; ?>" 
                               class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Validation du formulaire
document.getElementById('profileForm').addEventListener('submit', function(event) {
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    
    if (password || passwordConfirm) {
        if (password.length < 6) {
            event.preventDefault();
            alert('Le mot de passe doit contenir au moins 6 caractères.');
            document.getElementById('password').focus();
            return false;
        }
        
        if (password !== passwordConfirm) {
            event.preventDefault();
            alert('Les mots de passe ne correspondent pas.');
            document.getElementById('password_confirm').focus();
            return false;
        }
    }
});
</script>
</body>
</html>

