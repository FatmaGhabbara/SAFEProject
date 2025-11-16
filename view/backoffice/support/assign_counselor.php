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

// MODE TEST : FORCER la session administrateur
$_SESSION['user_id'] = 1;  // Admin
$_SESSION['role'] = 'admin';
$_SESSION['user_name'] = 'Administrateur';

require_once '../../../model/config.php';
require_once '../../../model/support_functions.php';

// Récupérer l'ID de la demande
$requestId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($requestId === 0) {
    setFlashMessage('Demande invalide.', 'error');
    header('Location: support_requests.php');
    exit();
}

// Récupérer la demande
$request = getSupportRequestById($requestId);

if (!$request) {
    setFlashMessage('Demande introuvable.', 'error');
    header('Location: support_requests.php');
    exit();
}

// Récupérer tous les conseillers actifs
$counselors = getAllCounselors(true);

// Récupérer les messages flash
$flash = getFlashMessage();
?>

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
                                    <p class="fw-bold mb-2"><?php echo $request['id']; ?></p>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="text-muted small">Utilisateur</label>
                                    <p class="fw-bold mb-2">
                                        <i class="fas fa-user me-1"></i>
                                        <?php echo secureOutput($request['user_nom'] . ' ' . $request['user_prenom']); ?>
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="text-muted small">Titre</label>
                                    <p class="fw-bold mb-2"><?php echo secureOutput($request['titre']); ?></p>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="text-muted small">Description</label>
                                    <p class="text-muted small" style="white-space: pre-wrap; max-height: 150px; overflow-y: auto;">
                                        <?php echo secureOutput($request['description']); ?>
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="text-muted small">Urgence</label>
                                    <p class="mb-2">
                                        <span class="badge-urgence badge-urgence-<?php echo $request['urgence']; ?>">
                                            <?php echo ucfirst($request['urgence']); ?>
                                        </span>
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="text-muted small">Statut actuel</label>
                                    <p class="mb-2">
                                        <span class="badge-support badge-<?php echo str_replace('_', '-', $request['statut']); ?>">
                                            <?php echo str_replace('_', ' ', ucfirst($request['statut'])); ?>
                                        </span>
                                    </p>
                                </div>
                                
                                <div class="mb-0">
                                    <label class="text-muted small">Date de création</label>
                                    <p class="mb-0">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo formatDate($request['date_creation'], 'd/m/Y à H:i'); ?>
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
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Aucun conseiller disponible pour le moment.
                                        <br>
                                        <a href="add_counselor.php" class="alert-link">Ajouter un conseiller</a>
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
                                            <?php foreach ($counselors as $counselor): ?>
                                            <option value="<?php echo $counselor['id']; ?>" 
                                                    data-specialite="<?php echo secureOutput($counselor['specialite']); ?>"
                                                    data-demandes="<?php echo $counselor['nombre_demandes_actives']; ?>">
                                                <?php echo secureOutput($counselor['nom'] . ' ' . $counselor['prenom']); ?>
                                                - <?php echo $counselor['nombre_demandes_actives']; ?> demande(s) active(s)
                                            </option>
                                            <?php endforeach; ?>
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
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($counselors as $counselor): ?>
                                            <tr>
                                                <td>
                                                    <i class="fas fa-user-md text-primary me-2"></i>
                                                    <strong><?php echo secureOutput($counselor['nom'] . ' ' . $counselor['prenom']); ?></strong>
                                                </td>
                                                <td><?php echo secureOutput($counselor['specialite']); ?></td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?php echo $counselor['nombre_demandes_actives']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($counselor['disponibilite']): ?>
                                                        <span class="badge bg-success">Disponible</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Non disponible</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
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

