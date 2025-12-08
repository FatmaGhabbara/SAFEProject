<?php
session_start();
require_once '../../../config.php';
require_once '../../../model/User.php';
require_once '../../../controller/helpers.php';

// Check admin access
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../frontoffice/login.php');
    exit();
}

// Get counselor ID
$counselorId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($counselorId === 0) {
    setFlashMessage('Conseiller invalide.', 'error');
    header('Location: counselors_list.php');
    exit();
}

// Get the counselor
$counselorUser = getCounselorById($counselorId);
if (!$counselorUser) {
    setFlashMessage('Conseiller introuvable.', 'error');
    header('Location: counselors_list.php');
    exit();
}

$user = $counselorUser;

// Get flash message
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Conseiller - SAFEProject</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
        }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<?php include '../../includes/navbar.php'; ?>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-edit"></i> Modifier le Conseiller</h1>
                <p class="mb-0">Mettre à jour les informations</p>
            </div>
            <a href="counselors_list.php" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>
</div>

<div class="container mb-5">
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $flash['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="form-card">
                <form action="../../../controller/support/update_counselor.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="counselor_id" value="<?php echo $counselorUser->getId(); ?>">
                    
                    <h4 class="mb-4">Informations du Conseiller</h4>
                    
                    <!-- Nom -->
                    <div class="mb-3">
                        <label for="nom" class="form-label">
                            <i class="fas fa-user"></i> Nom <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="nom" 
                               name="nom" 
                               value="<?php echo secureOutput($user->getNom()); ?>"
                               required>
                    </div>

                    <!-- Prénom -->
                    <div class="mb-3">
                        <label for="prenom" class="form-label">
                            <i class="fas fa-user"></i> Prénom <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="prenom" 
                               name="prenom" 
                               value="<?php echo secureOutput($user->getPrenom()); ?>"
                               required>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               value="<?php echo secureOutput($user->getEmail()); ?>"
                               required>
                    </div>

                    <!-- Spécialité -->
                    <div class="mb-3">
                        <label for="specialite" class="form-label">
                            <i class="fas fa-briefcase"></i> Spécialité
                        </label>
                        <select class="form-select" id="specialite" name="specialite">
                            <option value="Psychologie générale" <?php echo ($counselorUser->getSpecialite() === 'Psychologie générale') ? 'selected' : ''; ?>>Psychologie générale</option>
                            <option value="Anxiété et stress" <?php echo ($counselorUser->getSpecialite() === 'Anxiété et stress') ? 'selected' : ''; ?>>Anxiété et stress</option>
                            <option value="Dépression" <?php echo ($counselorUser->getSpecialite() === 'Dépression') ? 'selected' : ''; ?>>Dépression</option>
                            <option value="Relations et famille" <?php echo ($counselorUser->getSpecialite() === 'Relations et famille') ? 'selected' : ''; ?>>Relations et famille</option>
                            <option value="Traumatisme" <?php echo ($counselorUser->getSpecialite() === 'Traumatisme') ? 'selected' : ''; ?>>Traumatisme</option>
                            <option value="Addictions" <?php echo ($counselorUser->getSpecialite() === 'Addictions') ? 'selected' : ''; ?>>Addictions</option>
                        </select>
                    </div>


                    <!-- Bio -->
                    <div class="mb-3">
                        <label for="bio" class="form-label">
                            <i class="fas fa-info-circle"></i> Biographie
                        </label>
                        <textarea class="form-control" 
                                  id="bio" 
                                  name="bio" 
                                  rows="4"
                                  placeholder="Brève présentation du conseiller..."><?php echo secureOutput($counselorUser->getBiographie() ?? ''); ?></textarea>
                    </div>

                    <!-- Statut User -->
                    <div class="mb-4">
                        <label for="statut" class="form-label">
                            <i class="fas fa-toggle-on"></i> Statut du compte
                        </label>
                        <select class="form-select" id="statut" name="statut">
                            <option value="actif" <?php echo ($user->getStatut() === 'actif') ? 'selected' : ''; ?>>Actif</option>
                            <option value="inactif" <?php echo ($user->getStatut() === 'inactif') ? 'selected' : ''; ?>>Inactif</option>
                            <option value="suspendu" <?php echo ($user->getStatut() === 'suspendu') ? 'selected' : ''; ?>>Suspendu</option>
                        </select>
                    </div>

                    <hr class="my-4">

                    <!-- Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="counselors_list.php" class="btn btn-secondary">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

