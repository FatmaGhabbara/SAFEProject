<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services d'aide psychologique - SAFEProject</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/support-module.css">
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>

    <!-- En-tête -->
    <header class="bg-white shadow-sm py-3 mb-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-heart text-danger"></i>
                    SAFEProject
                </h2>
                <nav>
                    <a href="../index.html" class="btn btn-outline-primary me-2">
                        <i class="fas fa-home"></i> Accueil
                    </a>
                    <a href="my_requests.php" class="btn btn-outline-secondary">
                        <i class="fas fa-list"></i> Mes demandes
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Contenu principal -->
    <div class="container my-5">
        
        <!-- Hero Section -->
        <div class="row mb-5">
            <div class="col-lg-12">
                <div class="text-center py-5" style="background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%); border-radius: 15px; color: white;">
                    <i class="fas fa-hands-helping fa-4x mb-4"></i>
                    <h1 class="display-4 fw-bold mb-3">Services d'Aide Psychologique</h1>
                    <p class="lead mb-4">Votre bien-être mental est notre priorité. Nous sommes là pour vous écouter et vous accompagner.</p>
                    <a href="support_form.php" class="btn btn-light btn-lg px-5 py-3 shadow">
                        <i class="fas fa-paper-plane me-2"></i>
                        Demander de l'aide maintenant
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-5">
            <div class="col-lg-12">
                <h2 class="text-center mb-4">Nos Résultats</h2>
            </div>
            
            <?php
            require_once '../../../model/config.php';
            require_once '../../../model/support_functions.php';
            
            $stats = getGlobalStats();
            $averageResponseTime = getAverageResponseTime();
            ?>
            
            <div class="col-md-4 mb-4">
                <div class="stat-card stat-success text-center">
                    <i class="fas fa-users stat-icon"></i>
                    <p class="stat-value"><?php echo $stats['demandes_terminees'] ?? 0; ?></p>
                    <p class="stat-label">Personnes Aidées</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="stat-card stat-info text-center">
                    <i class="fas fa-clock stat-icon"></i>
                    <p class="stat-value"><?php echo number_format($averageResponseTime, 1); ?>h</p>
                    <p class="stat-label">Temps de Réponse Moyen</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="stat-card stat-primary text-center">
                    <i class="fas fa-user-md stat-icon"></i>
                    <p class="stat-value"><?php echo count(getAllCounselors(true)); ?></p>
                    <p class="stat-label">Conseillers Disponibles</p>
                </div>
            </div>
        </div>

        <!-- Services disponibles -->
        <div class="row mb-5">
            <div class="col-lg-12 mb-4">
                <h2 class="text-center">Services Disponibles</h2>
                <p class="text-center text-muted">Nous offrons différents types de soutien adaptés à vos besoins</p>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body">
                        <div class="mb-3" style="font-size: 3rem; color: #4A90E2;">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h5 class="card-title">Psychologie Clinique</h5>
                        <p class="card-text text-muted">Thérapie cognitive-comportementale et soutien psychologique</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body">
                        <div class="mb-3" style="font-size: 3rem; color: #27AE60;">
                            <i class="fas fa-home"></i>
                        </div>
                        <h5 class="card-title">Conseil Familial</h5>
                        <p class="card-text text-muted">Médiation familiale et gestion des conflits</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body">
                        <div class="mb-3" style="font-size: 3rem; color: #F39C12;">
                            <i class="fas fa-spa"></i>
                        </div>
                        <h5 class="card-title">Gestion du Stress</h5>
                        <p class="card-text text-muted">Techniques de relaxation et mindfulness</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body">
                        <div class="mb-3" style="font-size: 3rem; color: #9B59B6;">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h5 class="card-title">Écoute Active</h5>
                        <p class="card-text text-muted">Soutien émotionnel et accompagnement personnalisé</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Procédure -->
        <div class="row mb-5">
            <div class="col-lg-12 mb-4">
                <h2 class="text-center">Comment ça marche ?</h2>
                <p class="text-center text-muted">Un processus simple en 4 étapes</p>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="text-center">
                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                        1
                    </div>
                    <h5>Créez votre demande</h5>
                    <p class="text-muted">Remplissez un formulaire simple décrivant votre situation</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="text-center">
                    <div class="rounded-circle bg-info text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                        2
                    </div>
                    <h5>Assignation d'un conseiller</h5>
                    <p class="text-muted">Un conseiller qualifié sera assigné à votre demande</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="text-center">
                    <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                        3
                    </div>
                    <h5>Communication</h5>
                    <p class="text-muted">Échangez avec votre conseiller via notre plateforme</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="text-center">
                    <div class="rounded-circle bg-warning text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                        4
                    </div>
                    <h5>Suivi et résolution</h5>
                    <p class="text-muted">Un accompagnement jusqu'à la résolution de votre demande</p>
                </div>
            </div>
        </div>

        <!-- Pourquoi nous choisir -->
        <div class="row mb-5">
            <div class="col-lg-12 mb-4">
                <h2 class="text-center">Pourquoi nous choisir ?</h2>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-lock"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5>Confidentialité</h5>
                        <p class="text-muted">Vos informations sont strictement confidentielles et sécurisées</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-certificate"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5>Professionnalisme</h5>
                        <p class="text-muted">Conseillers qualifiés et expérimentés</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5>Disponibilité</h5>
                        <p class="text-muted">Réponse rapide et suivi personnalisé</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="row">
            <div class="col-lg-12">
                <div class="text-center py-5 bg-light rounded-3">
                    <h3 class="mb-3">Besoin d'aide ? N'attendez plus !</h3>
                    <p class="mb-4 text-muted">Notre équipe de conseillers est prête à vous accompagner</p>
                    <a href="support_form.php" class="btn btn-support-primary btn-lg px-5">
                        <i class="fas fa-paper-plane me-2"></i>
                        Faire une demande d'aide
                    </a>
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
</body>
</html>

