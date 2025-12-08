<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assigner un Conseiller - SAFEProject Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/support-module.css">
    <link rel="stylesheet" href="../css/sb-admin-2.min.css">
</head>
<body class="bg-light">

<?php
session_start();
require_once '../../../config.php';
require_once '../../../model/SupportRequest.php';
require_once '../../../model/User.php';
require_once '../../../controller/helpers.php';

// Check admin access
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../frontoffice/login.php');
    exit();
}

// Get request ID
$requestId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($requestId === 0) {
    setFlashMessage('Demande invalide.', 'error');
    header('Location: support_requests.php');
    exit();
}

// Get the request using OOP
try {
    $requestObj = new SupportRequest($requestId);
    $user = $requestObj->getUser();
} catch (Exception $e) {
    setFlashMessage('Demande introuvable.', 'error');
    header('Location: support_requests.php');
    exit();
}

// Get all active counselors
$counselors = getAllCounselors();

// Debug: Log counselors count
error_log("DEBUG: Found " . count($counselors) . " counselors");

// Get flash message
$flash = getFlashMessage();
?>

<?php include '../../includes/navbar.php'; ?>

<div class="container my-5">
        
        <!-- Message flash -->
        <?php if ($flash): ?>
        <div class="alert alert-flash alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo secureOutput($flash['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <!-- En-tête -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <i class="fas fa-user-plus text-primary me-2"></i>
                        Assigner un Conseiller
                    </h2>
                    <a href="support_requests.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Retour
                    </a>
                </div>
                
                <div class="row">
                    
                    <!-- Informations sur la demande -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Informations sur la demande
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="text-muted small">Demande n°</label>
                                    <p class="fw-bold mb-2"><?php echo $requestObj->getId(); ?></p>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="text-muted small">Utilisateur</label>
                                    <p class="fw-bold mb-2">
                                        <i class="fas fa-user me-1"></i>
                                        <?php echo secureOutput($user->getFullName()); ?>
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="text-muted small">Titre</label>
                                    <p class="fw-bold mb-2"><?php echo secureOutput($requestObj->getTitre()); ?></p>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="text-muted small">Description</label>
                                    <p class="text-muted small" style="white-space: pre-wrap; max-height: 150px; overflow-y: auto;">
                                        <?php echo secureOutput($requestObj->getDescription()); ?>
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="text-muted small">Urgence</label>
                                    <p class="mb-2">
                                        <span class="badge-urgence badge-urgence-<?php echo $requestObj->getUrgence(); ?>">
                                            <?php echo ucfirst($requestObj->getUrgence()); ?>
                                        </span>
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="text-muted small">Statut actuel</label>
                                    <p class="mb-2">
                                        <span class="badge-support badge-<?php echo str_replace('_', '-', $requestObj->getStatut() ?? 'en_attente'); ?>">
                                            <?php echo str_replace('_', ' ', ucfirst($requestObj->getStatut() ?? 'En attente')); ?>
                                        </span>
                                    </p>
                                </div>
                                
                                <div class="mb-0">
                                    <label class="text-muted small">Date de création</label>
                                    <p class="mb-0">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('d/m/Y à H:i', strtotime($requestObj->getDateCreation())); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Formulaire d'assignation -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-check me-2"></i>
                                    Sélectionner un conseiller
                                </h5>
                            </div>
                            <div class="card-body">
                                
                                <?php if (empty($counselors)): ?>
                                    <div class="alert alert-warning">
                                        <h5><i class="fas fa-exclamation-triangle me-2"></i> Aucun conseiller disponible</h5>
                                        <p>Il n'y a actuellement aucun conseiller dans le système.</p>
                                        <hr>
                                        <p class="mb-2"><strong>Solutions:</strong></p>
                                        <ul>
                                            <li>Créez un compte avec le rôle "Counselor" via la page d'inscription</li>
                                            <li>Ou modifiez un utilisateur existant pour lui donner le rôle "counselor"</li>
                                            <li>Assurez-vous que la base de données contient les données de test</li>
                                        </ul>
                                        <a href="../../../view/frontoffice/register.php" class="btn btn-primary mt-2">
                                            <i class="fas fa-user-plus"></i> Créer un Conseiller
                                        </a>
                                        <a href="counselors_list.php" class="btn btn-secondary mt-2">
                                            <i class="fas fa-list"></i> Voir les Conseillers
                                        </a>
                                    </div>
                                <?php else: ?>
                                
                                <form action="../../../controller/support/admin_assign_counselor.php" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="request_id" value="<?php echo $requestId; ?>">
                                    
                                    <div class="mb-4">
                                        <label for="counselor_id" class="form-label fw-bold">
                                            Choisir un conseiller <span class="text-danger">*</span>
                                        </label>
                                        <select name="counselor_id" id="counselor_id" class="form-select form-select-lg" required>
                                            <option value="">-- Sélectionnez un conseiller --</option>
                                            <?php foreach ($counselors as $counselorUser): 
                                                try {
                                                    $activeDemands = $counselorUser->getNombreDemandesActives();
                                                    $specialite = $counselorUser->getSpecialite() ?? 'Psychologie';
                                            ?>
                                            <option value="<?php echo $counselorUser->getId(); ?>" 
                                                    data-specialite="<?php echo secureOutput($specialite); ?>"
                                                    data-demandes="<?php echo $activeDemands; ?>">
                                                <?php echo secureOutput($counselorUser->getFullName()); ?>
                                                - <?php echo $activeDemands; ?> demande(s) active(s)
                                            </option>
                                            <?php 
                                                } catch (Exception $e) {
                                                    error_log("Error displaying counselor: " . $e->getMessage());
                                                }
                                            endforeach; ?>
                                        </select>
                                        <small class="text-muted">Les conseillers sont triés par nombre de demandes actives</small>
                                    </div>
                                    
                                    <div id="counselor-info" class="mb-4" style="display: none;">
                                        <div class="alert alert-info">
                                            <strong><i class="fas fa-user-md me-2"></i>Spécialité :</strong>
                                            <span id="counselor-specialite"></span>
                                            <br>
                                            <strong><i class="fas fa-tasks me-2"></i>Demandes actives :</strong>
                                            <span id="counselor-demandes"></span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="notes_admin" class="form-label">
                                            Notes administratives (optionnel)
                                        </label>
                                        <textarea name="notes_admin" 
                                                  id="notes_admin" 
                                                  class="form-control" 
                                                  rows="3"
                                                  placeholder="Ajoutez des notes internes si nécessaire..."></textarea>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-support-success btn-lg">
                                            <i class="fas fa-check me-2"></i>
                                            Assigner ce conseiller
                                        </button>
                                        <a href="support_requests.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>
                                            Annuler
                                        </a>
                                    </div>
                                </form>
                                
                                <?php endif; ?>
                                
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                <!-- Statistiques des conseillers disponibles -->
                <?php if (!empty($counselors)): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-bar text-primary me-2"></i>
                                    Aperçu des conseillers disponibles
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Conseiller</th>
                                                <th>Spécialité</th>
                                                <th>Demandes actives</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($counselors as $counselorUser): 
                                                try {
                                                    $activeDemands = $counselorUser->getNombreDemandesActives();
                                            ?>
                                            <tr>
                                                <td>
                                                    <i class="fas fa-user-md text-primary me-2"></i>
                                                    <strong><?php echo secureOutput($counselorUser->getFullName()); ?></strong>
                                                </td>
                                                <td><?php echo secureOutput($counselorUser->getSpecialite() ?? 'Psychologie'); ?></td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?php echo $activeDemands; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php 
                                                } catch (Exception $e) {
                                                    error_log("Error displaying counselor in table: " . $e->getMessage());
                                                }
                                            endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
        
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Afficher les infos du conseiller sélectionné
        const counselorSelect = document.getElementById('counselor_id');
        const counselorInfo = document.getElementById('counselor-info');
        const counselorSpecialite = document.getElementById('counselor-specialite');
        const counselorDemandes = document.getElementById('counselor-demandes');
        
        counselorSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedOption.value) {
                const specialite = selectedOption.getAttribute('data-specialite');
                const demandes = selectedOption.getAttribute('data-demandes');
                
                counselorSpecialite.textContent = specialite;
                counselorDemandes.textContent = demandes;
                counselorInfo.style.display = 'block';
            } else {
                counselorInfo.style.display = 'none';
            }
        });
    </script>

</body>
</html>

