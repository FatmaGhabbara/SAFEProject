<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Demandes - SAFEProject</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/support-module.css">
    <link rel="stylesheet" href="../assets/css/main.css">
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
require_once '../../../model/support_functions.php';

// Récupérer les demandes de l'utilisateur
$userId = $_SESSION['user_id'];
$requests = getSupportRequestsByUser($userId);

// Récupérer les messages flash
$flash = getFlashMessage();

// Filtres
$filter_statut = isset($_GET['statut']) ? $_GET['statut'] : '';
$filter_urgence = isset($_GET['urgence']) ? $_GET['urgence'] : '';

// Appliquer les filtres
if ($filter_statut || $filter_urgence) {
    $requests = array_filter($requests, function($request) use ($filter_statut, $filter_urgence) {
        $match = true;
        if ($filter_statut && $request['statut'] !== $filter_statut) {
            $match = false;
        }
        if ($filter_urgence && $request['urgence'] !== $filter_urgence) {
            $match = false;
        }
        return $match;
    });
}
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
                    <a href="support_form.php" class="btn btn-support-primary">
                        <i class="fas fa-plus"></i> Nouvelle demande
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Contenu principal -->
    <div class="container my-5">
        
        <!-- Message flash -->
        <?php if ($flash): ?>
        <div class="alert alert-flash alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo secureOutput($flash['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- En-tête de la page -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <h1 class="h2 mb-3">
                    <i class="fas fa-list text-primary me-2"></i>
                    Mes Demandes d'Aide
                </h1>
                <p class="text-muted">
                    Retrouvez ici toutes vos demandes de support psychologique et leur statut.
                </p>
            </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card stat-warning">
                    <p class="stat-label">En Attente</p>
                    <p class="stat-value">
                        <?php echo count(array_filter($requests, fn($r) => $r['statut'] === 'en_attente')); ?>
                    </p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card stat-info">
                    <p class="stat-label">Assignées</p>
                    <p class="stat-value">
                        <?php echo count(array_filter($requests, fn($r) => $r['statut'] === 'assignee')); ?>
                    </p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card stat-primary">
                    <p class="stat-label">En Cours</p>
                    <p class="stat-value">
                        <?php echo count(array_filter($requests, fn($r) => $r['statut'] === 'en_cours')); ?>
                    </p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card stat-success">
                    <p class="stat-label">Terminées</p>
                    <p class="stat-value">
                        <?php echo count(array_filter($requests, fn($r) => $r['statut'] === 'terminee')); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="filter-section">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-4">
                    <label for="statut" class="form-label">
                        <i class="fas fa-filter me-2"></i>Filtrer par statut
                    </label>
                    <select name="statut" id="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="en_attente" <?php echo $filter_statut === 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                        <option value="assignee" <?php echo $filter_statut === 'assignee' ? 'selected' : ''; ?>>Assignée</option>
                        <option value="en_cours" <?php echo $filter_statut === 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                        <option value="terminee" <?php echo $filter_statut === 'terminee' ? 'selected' : ''; ?>>Terminée</option>
                        <option value="annulee" <?php echo $filter_statut === 'annulee' ? 'selected' : ''; ?>>Annulée</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="urgence" class="form-label">
                        <i class="fas fa-exclamation-triangle me-2"></i>Filtrer par urgence
                    </label>
                    <select name="urgence" id="urgence" class="form-select">
                        <option value="">Toutes les urgences</option>
                        <option value="basse" <?php echo $filter_urgence === 'basse' ? 'selected' : ''; ?>>Basse</option>
                        <option value="moyenne" <?php echo $filter_urgence === 'moyenne' ? 'selected' : ''; ?>>Moyenne</option>
                        <option value="haute" <?php echo $filter_urgence === 'haute' ? 'selected' : ''; ?>>Haute</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-support-primary flex-grow-1">
                        <i class="fas fa-search me-2"></i>Filtrer
                    </button>
                    <a href="my_requests.php" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Liste des demandes -->
        <?php if (empty($requests)): ?>
            
            <!-- État vide -->
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <h3 class="empty-state-title">Aucune demande trouvée</h3>
                <p class="empty-state-text">
                    Vous n'avez pas encore créé de demande d'aide.
                </p>
                <a href="support_form.php" class="btn btn-support-primary btn-lg">
                    <i class="fas fa-plus me-2"></i>
                    Créer ma première demande
                </a>
            </div>
            
        <?php else: ?>
            
            <!-- Table responsive pour desktop -->
            <div class="d-none d-lg-block">
                <div class="support-table">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="30%">Titre</th>
                                <th width="15%">Date</th>
                                <th width="12%">Urgence</th>
                                <th width="12%">Statut</th>
                                <th width="16%">Conseiller</th>
                                <th width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?php echo $request['id']; ?></td>
                                <td>
                                    <strong><?php echo secureOutput($request['titre']); ?></strong>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo formatDate($request['date_creation'], 'd/m/Y'); ?>
                                        <br>
                                        <i class="fas fa-clock me-1"></i>
                                        <?php echo timeAgo($request['date_creation']); ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge-urgence badge-urgence-<?php echo $request['urgence']; ?>">
                                        <span class="urgence-icon <?php echo $request['urgence']; ?>"></span>
                                        <?php echo ucfirst($request['urgence']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-support badge-<?php echo str_replace('_', '-', $request['statut']); ?>">
                                        <?php echo str_replace('_', ' ', ucfirst($request['statut'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($request['counselor_nom']): ?>
                                        <small>
                                            <i class="fas fa-user-md text-primary me-1"></i>
                                            <?php echo secureOutput($request['counselor_nom'] . ' ' . $request['counselor_prenom']); ?>
                                        </small>
                                    <?php else: ?>
                                        <small class="text-muted">Non assigné</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="request_details.php?id=<?php echo $request['id']; ?>" 
                                           class="action-btn action-btn-view" 
                                           title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Cards pour mobile -->
            <div class="d-lg-none">
                <?php foreach ($requests as $request): ?>
                <div class="request-card">
                    <div class="request-card-header">
                        <h5 class="request-card-title">
                            <?php echo secureOutput($request['titre']); ?>
                        </h5>
                        <div>
                            <span class="badge-support badge-<?php echo str_replace('_', '-', $request['statut']); ?>">
                                <?php echo str_replace('_', ' ', ucfirst($request['statut'])); ?>
                            </span>
                        </div>
                    </div>
                    <div class="request-card-body">
                        <p class="mb-2">
                            <span class="badge-urgence badge-urgence-<?php echo $request['urgence']; ?>">
                                <span class="urgence-icon <?php echo $request['urgence']; ?>"></span>
                                Urgence : <?php echo ucfirst($request['urgence']); ?>
                            </span>
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-calendar text-muted me-2"></i>
                            <?php echo formatDate($request['date_creation'], 'd/m/Y H:i'); ?>
                        </p>
                        <?php if ($request['counselor_nom']): ?>
                        <p class="mb-0">
                            <i class="fas fa-user-md text-primary me-2"></i>
                            Conseiller : <?php echo secureOutput($request['counselor_nom'] . ' ' . $request['counselor_prenom']); ?>
                        </p>
                        <?php else: ?>
                        <p class="mb-0 text-muted">
                            <i class="fas fa-hourglass-half me-2"></i>
                            En attente d'assignation
                        </p>
                        <?php endif; ?>
                    </div>
                    <div class="request-card-footer">
                        <small class="text-muted">
                            <?php echo timeAgo($request['date_creation']); ?>
                        </small>
                        <a href="request_details.php?id=<?php echo $request['id']; ?>" class="btn btn-support-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>
                            Voir détails
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
        <?php endif; ?>

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

</body>
</html>

