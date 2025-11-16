<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande d'aide - SAFEProject</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/support-module.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <!-- Small inline override to ensure form inputs are readable (takes precedence) -->
    <style>
        .form-support .form-control,
        .form-support .form-select,
        .form-support textarea {
            color: #2C3E50 !important;
            background-color: #ffffff !important;
        }
        .form-support ::placeholder {
            color: #6c757d !important;
            opacity: 1 !important;
        }
        .form-support .form-select,
        .form-support .form-select option {
            color: #2C3E50 !important;
        }
    </style>
</head>
<body class="bg-light">

<?php
session_start();

// MODE TEST : Simuler un utilisateur connecté (Jean Dupont - user)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 2;  // Jean Dupont
    $_SESSION['user_role'] = 'user';
    $_SESSION['user_name'] = 'Jean Dupont';
}

require_once '../../../model/config.php';

// Récupérer les messages flash
$flash = getFlashMessage();
?>

    <!-- En-tête -->
    <header class="bg-white shadow-sm py-3 mb-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-heart text-danger"></i>
                    SAFEProject
                </h2>
                <nav>
                    <a href="support_info.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                    <a href="my_requests.php" class="btn btn-outline-primary">
                        <i class="fas fa-list"></i> Mes demandes
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Contenu principal -->
    <div class="container my-5">
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- Message flash -->
                <?php if ($flash): ?>
                <div class="alert alert-flash alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo secureOutput($flash['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- En-tête du formulaire -->
                <div class="text-center mb-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <h1 class="h2">Demande d'Aide Psychologique</h1>
                    <p class="text-muted">Remplissez le formulaire ci-dessous. Un conseiller vous sera assigné rapidement.</p>
                </div>

                <!-- Formulaire -->
                <div class="form-support">
                    <form id="supportRequestForm" action="../../../controller/support/create_request.php" method="POST" novalidate>
                        
                        <!-- Token CSRF -->
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <!-- Titre de la demande -->
                        <div class="mb-4">
                            <label for="titre" class="form-label">
                                <i class="fas fa-heading text-primary me-2"></i>
                                Titre de votre demande <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="titre" 
                                   name="titre" 
                                   placeholder="Ex: Besoin d'aide pour gérer mon anxiété"
                                   required
                                   maxlength="255">
                            <div class="invalid-feedback">
                                Veuillez entrer un titre pour votre demande.
                            </div>
                            <small class="form-text text-muted">
                                Résumez votre demande en quelques mots
                            </small>
                        </div>

                        <!-- Description détaillée -->
                        <div class="mb-4">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left text-primary me-2"></i>
                                Description détaillée <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="6"
                                      placeholder="Décrivez votre situation en détail. Plus vous fournirez d'informations, mieux nous pourrons vous aider."
                                      required
                                      minlength="50"></textarea>
                            <div class="invalid-feedback">
                                Veuillez décrire votre situation (minimum 50 caractères).
                            </div>
                            <small class="form-text text-muted">
                                <span id="charCount">0</span> caractères (minimum 50)
                            </small>
                        </div>

                        <!-- Niveau d'urgence -->
                        <div class="mb-4">
                            <label for="urgence" class="form-label">
                                <i class="fas fa-exclamation-triangle text-primary me-2"></i>
                                Niveau d'urgence <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="urgence" name="urgence" required>
                                <option value="">-- Sélectionnez un niveau --</option>
                                <option value="basse">
                                    <span class="urgence-icon basse"></span>
                                    Basse - Je peux attendre quelques jours
                                </option>
                                <option value="moyenne" selected>
                                    <span class="urgence-icon moyenne"></span>
                                    Moyenne - J'aimerais une réponse dans les 24-48h
                                </option>
                                <option value="haute">
                                    <span class="urgence-icon haute"></span>
                                    Haute - J'ai besoin d'aide rapidement
                                </option>
                            </select>
                            <div class="invalid-feedback">
                                Veuillez sélectionner un niveau d'urgence.
                            </div>
                        </div>

                        <!-- Informations importantes -->
                        <div class="alert alert-info mb-4">
                            <h6><i class="fas fa-info-circle me-2"></i>Informations importantes</h6>
                            <ul class="mb-0 small">
                                <li>Vos informations sont strictement confidentielles</li>
                                <li>Un conseiller qualifié sera assigné à votre demande</li>
                                <li>Vous recevrez une notification dès qu'un conseiller vous sera assigné</li>
                                <li>Vous pourrez suivre l'évolution de votre demande dans la section "Mes demandes"</li>
                            </ul>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-grid gap-3 d-md-flex justify-content-md-between">
                            <a href="support_info.php" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-support-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>
                                Envoyer ma demande
                            </button>
                        </div>

                    </form>
                </div>

                <!-- Section d'aide -->
                <div class="mt-4 p-4 bg-white rounded-3 shadow-sm">
                    <h5 class="mb-3">
                        <i class="fas fa-question-circle text-primary me-2"></i>
                        Besoin d'aide pour remplir ce formulaire ?
                    </h5>
                    <p class="text-muted mb-3">
                        Si vous avez des difficultés à remplir ce formulaire ou si vous ne savez pas quoi écrire, 
                        voici quelques conseils :
                    </p>
                    <ul class="text-muted">
                        <li>Soyez honnête et authentique dans votre description</li>
                        <li>N'hésitez pas à mentionner vos émotions et ressentis</li>
                        <li>Indiquez si vous avez déjà consulté un professionnel</li>
                        <li>Précisez depuis combien de temps vous rencontrez ces difficultés</li>
                    </ul>
                    <div class="alert alert-warning mb-0">
                        <strong>⚠️ Urgence vitale :</strong> Si vous êtes en danger immédiat, 
                        veuillez contacter immédiatement les services d'urgence au <strong>190</strong> ou 
                        le numéro vert de soutien psychologique au <strong>80 10 10 10</strong>.
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">© 2025 SAFEProject - Tous droits réservés</p>
            <p class="mb-0 small text-muted">Module Support Psychologique</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Validation JavaScript -->
    <script>
        // Compteur de caractères
        const descriptionField = document.getElementById('description');
        const charCount = document.getElementById('charCount');
        
        descriptionField.addEventListener('input', function() {
            charCount.textContent = this.value.length;
            
            if (this.value.length >= 50) {
                charCount.classList.remove('text-danger');
                charCount.classList.add('text-success');
            } else {
                charCount.classList.remove('text-success');
                charCount.classList.add('text-danger');
            }
        });
        
        // Validation du formulaire
        (function() {
            'use strict';
            
            const form = document.getElementById('supportRequestForm');
            
            form.addEventListener('submit', function(event) {
                // Vérifications personnalisées
                let isValid = true;
                
                // Vérifier le titre
                const titre = document.getElementById('titre');
                if (titre.value.trim().length < 5) {
                    isValid = false;
                    titre.setCustomValidity('Le titre doit contenir au moins 5 caractères');
                } else {
                    titre.setCustomValidity('');
                }
                
                // Vérifier la description
                const description = document.getElementById('description');
                if (description.value.trim().length < 50) {
                    isValid = false;
                    description.setCustomValidity('La description doit contenir au moins 50 caractères');
                } else {
                    description.setCustomValidity('');
                }
                
                // Vérifier l'urgence
                const urgence = document.getElementById('urgence');
                if (!urgence.value) {
                    isValid = false;
                    urgence.setCustomValidity('Veuillez sélectionner un niveau d\'urgence');
                } else {
                    urgence.setCustomValidity('');
                }
                
                if (!form.checkValidity() || !isValid) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
            
            // Réinitialiser les messages d'erreur personnalisés lors de la modification
            document.getElementById('titre').addEventListener('input', function() {
                this.setCustomValidity('');
            });
            
            document.getElementById('description').addEventListener('input', function() {
                this.setCustomValidity('');
            });
            
            document.getElementById('urgence').addEventListener('change', function() {
                this.setCustomValidity('');
            });
        })();
        
        // Confirmation avant de quitter la page si le formulaire est rempli
        let formModified = false;
        const formInputs = document.querySelectorAll('#supportRequestForm input, #supportRequestForm textarea, #supportRequestForm select');
        
        formInputs.forEach(input => {
            input.addEventListener('change', () => {
                formModified = true;
            });
        });
        
        window.addEventListener('beforeunload', (event) => {
            if (formModified) {
                event.preventDefault();
                event.returnValue = '';
            }
        });
        
        // Ne pas afficher la confirmation si le formulaire est soumis
        document.getElementById('supportRequestForm').addEventListener('submit', () => {
            formModified = false;
        });
    </script>

</body>
</html>

