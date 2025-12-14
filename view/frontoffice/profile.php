<?php
// ===============================
// Session & sécurité
// ===============================
session_start();

// 🔐 Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// ===============================
// Controller
// ===============================
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/UserController.php';

$userController = new UserController();

// ===============================
// Profil à afficher (le sien ou autre)
// ===============================
$profileUserId = $_GET['id'] ?? $_SESSION['user_id'];

try {
    $user = $userController->getUserById($profileUserId);
    if (!$user) {
        throw new Exception('Utilisateur introuvable');
    }
} catch (Exception $e) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// ===============================
// Vérifier si c’est son propre profil
// ===============================
$isOwnProfile = ($_SESSION['user_id'] == $user->getId());

// ===============================
// Photo de profil
// ===============================
function getProfilePictureUrl($user)
{
    $baseUrl = 'assets/images/uploads/';
    $default = 'assets/images/default-avatar.png';

    $picture = $user->getProfilePicture();

    if (!empty($picture) && $picture !== 'default-avatar.png') {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/view/frontoffice/' . $baseUrl . $picture;
        if (file_exists($path)) {
            return $baseUrl . $picture . '?v=' . filemtime($path);
        }
    }
    return $default;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil de <?= htmlspecialchars($user->getNom()) ?> | SafeSpace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/main.css">
</head>

<body>

<!-- =============================== -->
<!-- Navbar -->
<!-- =============================== -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <img src="images/logo.png" height="32" class="me-2"> SafeSpace
        </a>

        <div class="dropdown ms-auto">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                <img src="<?= getProfilePictureUrl($user) ?>" class="rounded-circle me-2" width="32" height="32">
                <?= htmlspecialchars($_SESSION['fullname'] ?? 'Utilisateur') ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Mon profil</a></li>
                <?php if ($isOwnProfile): ?>
                    <li><a class="dropdown-item" href="../backoffice/edit_profile.php"><i class="fas fa-edit me-2"></i>Modifier</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- =============================== -->
<!-- Header Profil -->
<!-- =============================== -->
<div class="bg-primary text-white py-5">
    <div class="container d-flex align-items-center">
        <img src="<?= getProfilePictureUrl($user) ?>" class="rounded-circle me-4" width="140" height="140">
        <div>
            <h2><?= htmlspecialchars($user->getNom()) ?></h2>
            <span class="badge bg-light text-primary"><?= ucfirst($user->getRole()) ?></span>
            <p class="mt-2"><?= htmlspecialchars($user->getBio() ?: 'Membre SafeSpace') ?></p>
        </div>
    </div>
</div>

<!-- =============================== -->
<!-- Contenu -->
<!-- =============================== -->
<div class="container my-5">
    <div class="row">

        <!-- Infos personnelles -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header fw-bold">
                    <i class="fas fa-user me-2"></i>Informations personnelles
                </div>
                <div class="card-body">

                    <p><strong>Email :</strong> <?= htmlspecialchars($user->getEmail()) ?></p>

                    <?php if ($user->getTelephone()): ?>
                        <p><strong>Téléphone :</strong> <?= htmlspecialchars($user->getTelephone()) ?></p>
                    <?php endif; ?>

                    <?php if ($user->getAdresse()): ?>
                        <p><strong>Adresse :</strong> <?= htmlspecialchars($user->getAdresse()) ?></p>
                    <?php endif; ?>

                    <?php if ($user->getSpecialite()): ?>
                        <p><strong>Spécialité :</strong>
                            <span class="badge bg-primary"><?= htmlspecialchars($user->getSpecialite()) ?></span>
                        </p>
                    <?php endif; ?>

                    <?php if ($user->getBio()): ?>
                        <hr>
                        <p><?= nl2br(htmlspecialchars($user->getBio())) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Infos compte -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header fw-bold">
                    <i class="fas fa-cog me-2"></i>Compte
                </div>
                <div class="card-body">
                    <p><strong>ID :</strong> #<?= $user->getId() ?></p>
                    <p><strong>Membre depuis :</strong> <?= date('d/m/Y', strtotime($user->getCreatedAt())) ?></p>

                    <p>
                        <strong>Statut :</strong>
                        <span class="badge <?= $user->getStatus() === 'actif' ? 'bg-success' : 'bg-warning' ?>">
                            <?= ucfirst($user->getStatus()) ?>
                        </span>
                    </p>

                    <?php if ($isOwnProfile): ?>
                        <a href="../backoffice/edit_profile.php" class="btn btn-primary w-100 mt-3">
                            <i class="fas fa-edit me-2"></i>Modifier mon profil
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- =============================== -->
<!-- Footer -->
<!-- =============================== -->
<footer class="bg-dark text-white text-center py-4">
    <p class="mb-0">&copy; <?= date('Y') ?> SafeSpace</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
