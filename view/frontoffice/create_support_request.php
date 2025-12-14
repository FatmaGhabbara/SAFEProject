<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/model/SupportRequest.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $urgence = $_POST['urgence'] ?? 'moyenne';
    
    // Validation
    if (empty($titre)) {
        $error = 'Le titre est requis.';
    } elseif (empty($description)) {
        $error = 'La description est requise.';
    } elseif (strlen($description) < 20) {
        $error = 'La description doit contenir au moins 20 caractères.';
    } else {
        // Create new support request
        $request = new SupportRequest();
        $request->setUserId($_SESSION['user_id']);
        $request->setTitre($titre);
        $request->setDescription($description);
        $request->setUrgence($urgence);
        $request->setStatut('en_attente');
        
        if ($request->save()) {
            $_SESSION['success_message'] = 'Votre demande a été créée avec succès. Un conseiller vous sera assigné prochainement.';
            header('Location: support_dashboard.php');
            exit();
        } else {
            $error = 'Erreur lors de la création de la demande. Veuillez réessayer.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Demande de Support - SafeSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem 0;
        }
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .form-header h1 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        .form-header p {
            color: #6c757d;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            width: 100%;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .urgence-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .urgence-info h6 {
            color: #495057;
            margin-bottom: 0.5rem;
        }
        .urgence-info ul {
            margin-bottom: 0;
            padding-left: 1.5rem;
        }
        .urgence-info li {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        .navbar-custom .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #4e73df;
        }
        .navbar-custom .nav-link {
            color: #495057;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: all 0.3s;
        }
        .navbar-custom .nav-link:hover {
            color: #4e73df;
        }
        .navbar-custom .btn-logout {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 500;
        }
        .navbar-custom .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>

<!-- Navigation Header -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-heart text-danger me-2"></i>SAFEProject
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="../backoffice/member_dashboard.php">
                        <i class="fas fa-home me-1"></i> Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="support_dashboard.php">
                        <i class="fas fa-hands-helping me-1"></i> Mes demandes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../backoffice/edit_profile.php">
                        <i class="fas fa-user-circle me-1"></i> Mon profil
                    </a>
                </li>
                <li class="nav-item ms-2">
                    <a href="logout.php" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt me-1"></i> Déconnexion
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="form-container">
        <div class="form-header">
            <h1><i class="fas fa-hands-helping text-primary"></i> Nouvelle Demande de Support</h1>
            <p>Décrivez votre besoin et nous vous mettrons en contact avec un conseiller qualifié</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label for="titre" class="form-label">
                    <i class="fas fa-heading me-2"></i>Titre de votre demande
                </label>
                <input type="text" 
                       class="form-control" 
                       id="titre" 
                       name="titre" 
                       placeholder="Ex: Besoin d'aide pour gérer mon stress"
                       value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>"
                       required>
                <small class="text-muted">Résumez votre besoin en quelques mots</small>
            </div>

            <div class="mb-4">
                <label for="description" class="form-label">
                    <i class="fas fa-align-left me-2"></i>Description détaillée
                </label>
                <textarea class="form-control" 
                          id="description" 
                          name="description" 
                          rows="6" 
                          placeholder="Décrivez votre situation en détail. Plus vous fournirez d'informations, mieux nous pourrons vous aider."
                          required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                <small class="text-muted">Minimum 20 caractères</small>
            </div>

            <div class="mb-4">
                <label for="urgence" class="form-label">
                    <i class="fas fa-exclamation-triangle me-2"></i>Niveau d'urgence
                </label>
                <select class="form-select" id="urgence" name="urgence" required>
                    <option value="basse" <?= (($_POST['urgence'] ?? '') === 'basse') ? 'selected' : '' ?>>
                        Basse - Peut attendre quelques jours
                    </option>
                    <option value="moyenne" <?= (($_POST['urgence'] ?? 'moyenne') === 'moyenne') ? 'selected' : '' ?>>
                        Moyenne - Besoin d'aide dans les prochains jours
                    </option>
                    <option value="haute" <?= (($_POST['urgence'] ?? '') === 'haute') ? 'selected' : '' ?>>
                        Haute - Besoin d'aide rapidement
                    </option>
                </select>
                
                <div class="urgence-info">
                    <h6><i class="fas fa-info-circle me-2"></i>Informations importantes</h6>
                    <ul>
                        <li><strong>Basse:</strong> Réponse sous 3-5 jours ouvrables</li>
                        <li><strong>Moyenne:</strong> Réponse sous 1-2 jours ouvrables</li>
                        <li><strong>Haute:</strong> Réponse prioritaire sous 24h</li>
                    </ul>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-2"></i>Envoyer ma demande
                </button>
                <a href="support_dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </form>

        <div class="text-center mt-4">
            <small class="text-muted">
                <i class="fas fa-lock me-1"></i>
                Vos informations sont confidentielles et protégées
            </small>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
