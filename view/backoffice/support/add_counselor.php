<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Conseiller - SAFEProject Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/support-module.css">
</head>
<body class="bg-light">

<?php
session_start();

// MODE TEST : FORCER la session administrateur
$_SESSION['user_id'] = 1;  // Admin
$_SESSION['role'] = 'admin';
$_SESSION['user_name'] = 'Administrateur';

require_once '../../../config.php';
require_once '../../../controller/helpers.php';
require_once '../../../model/User.php';

// Récupérer les messages flash
$flash = getFlashMessage();

// Récupérer les utilisateurs qui ne sont pas déjà conseillers
$allUsers = findAllUsers();
$availableUsers = array_filter($allUsers, function($user) {
    return !isUserCounselor($user->getId());
});
?>

    <div class="container my-5">
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- En-tête -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <i class="fas fa-user-plus text-primary me-2"></i>
                        Ajouter un Conseiller
                    </h2>
                    <a href="counselors_list.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Retour
                    </a>
                </div>
                
                <!-- Message flash -->
                <?php if ($flash): ?>
                <div class="alert alert-flash alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo secureOutput($flash['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- Formulaire -->
                <div class="form-support">
                    <form id="counselorForm" action="../../../controller/support/admin_create_counselor.php" method="POST" novalidate>
                        
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <!-- Utilisateur -->
                        <div class="mb-4">
                            <label for="user_id" class="form-label">
                                <i class="fas fa-user text-primary me-2"></i>
                                Sélectionner un utilisateur <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="">-- Choisissez un utilisateur --</option>
                                <?php foreach ($availableUsers as $user): ?>
                                <option value="<?php echo $user->getId(); ?>">
                                    <?php echo secureOutput($user->getFullName()); ?> (<?php echo secureOutput($user->getEmail()); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Veuillez sélectionner un utilisateur.
                            </div>
                            <small class="text-muted">
                                L'utilisateur doit avoir un compte existant dans le système.
                            </small>
                        </div>
                        
                        <!-- Spécialité -->
                        <div class="mb-4">
                            <label for="specialite" class="form-label">
                                <i class="fas fa-graduation-cap text-primary me-2"></i>
                                Spécialité <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="specialite" 
                                   name="specialite" 
                                   placeholder="Ex: Psychologie clinique, Gestion du stress, Conseil familial..."
                                   required
                                   maxlength="255">
                            <div class="invalid-feedback">
                                Veuillez entrer une spécialité.
                            </div>
                        </div>
                        
                        <!-- Biographie -->
                        <div class="mb-4">
                            <label for="biographie" class="form-label">
                                <i class="fas fa-align-left text-primary me-2"></i>
                                Biographie
                            </label>
                            <textarea class="form-control" 
                                      id="biographie" 
                                      name="biographie" 
                                      rows="5"
                                      placeholder="Décrivez l'expérience, les qualifications et les domaines d'expertise du conseiller..."></textarea>
                            <small class="text-muted">
                                <span id="bioCharCount">0</span> caractères
                            </small>
                        </div>
                        
                        <!-- Statut -->
                        <div class="mb-4">
                            <label for="statut" class="form-label">
                                <i class="fas fa-toggle-on text-primary me-2"></i>
                                Statut <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="statut" name="statut" required>
                                <option value="actif" selected>Actif</option>
                                <option value="en_pause">En pause</option>
                                <option value="inactif">Inactif</option>
                            </select>
                            <div class="invalid-feedback">
                                Veuillez sélectionner un statut.
                            </div>
                            <small class="text-muted">
                                Seuls les conseillers "actifs" peuvent recevoir de nouvelles demandes.
                            </small>
                        </div>
                        
                        <!-- Informations importantes -->
                        <div class="alert alert-info mb-4">
                            <h6><i class="fas fa-info-circle me-2"></i>Informations importantes</h6>
                            <ul class="mb-0 small">
                                <li>L'utilisateur sélectionné ne doit pas être déjà conseiller</li>
                                <li>Assurez-vous que le conseiller a accepté son rôle</li>
                                <li>La biographie sera visible par les utilisateurs</li>
                                <li>Vous pourrez modifier ces informations ultérieurement</li>
                            </ul>
                        </div>
                        
                        <!-- Boutons -->
                        <div class="d-grid gap-3 d-md-flex justify-content-md-between">
                            <a href="counselors_list.php" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-support-primary btn-lg">
                                <i class="fas fa-save me-2"></i>
                                Enregistrer le conseiller
                            </button>
                        </div>
                        
                    </form>
                </div>
                
            </div>
        </div>
        
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Validation JavaScript -->
    <script>
        // Compteur de caractères pour la biographie
        const bioField = document.getElementById('biographie');
        const bioCharCount = document.getElementById('bioCharCount');
        
        bioField.addEventListener('input', function() {
            bioCharCount.textContent = this.value.length;
        });
        
        // Validation du formulaire
        (function() {
            'use strict';
            
            const form = document.getElementById('counselorForm');
            
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
        })();
    </script>

</body>
</html>

