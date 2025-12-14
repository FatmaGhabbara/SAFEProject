<?php
// ===============================
// Debug (à désactiver en prod)
// ===============================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ===============================
// Session
// ===============================
session_start();

// ===============================
// AuthController
// ===============================
$authControllerPath = $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/AuthController.php';
if (!file_exists($authControllerPath)) {
    die("Erreur : AuthController introuvable");
}
require_once $authControllerPath;
$authController = new AuthController();

// ===============================
// UserController
// ===============================
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/UserController.php';
$userController = new UserController();
$users = $userController->listUsers();

// ===============================
// Autres controllers
// ===============================
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/PostC.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/CommentC.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/RespondC.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/ArticleC.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/CategorieC.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/ReactionC.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/CommentArticleC.php';

// ===============================
// Instances
// ===============================
$pc = new PostC();
$cc = new CommentC();
$rc = new RespondC();
$articleC = new ArticleC();
$categorieC = new CategorieC();
$reactionC = new ReactionC();
$commentArticleC = new CommentArticleC();

// ===============================
// Posts approuvés
// ===============================
$list_Post = $pc->listPostProuver();

// ===============================
// Articles (filtres)
// ===============================
$sortBy = $_GET['sort'] ?? 'recent';
$orderBy = null;

if ($sortBy === 'views') {
    $orderBy = 'views';
} elseif ($sortBy === 'title') {
    $orderBy = 'title';
}

$articles = $articleC->listArticles('approved', $orderBy);
$trendingArticles = $articleC->getTopViewedArticles(3);
$popularArticles  = $articleC->getPopularArticles(3);
$categories       = $categorieC->listCategories();

$selectedCategory = (int)($_GET['category'] ?? 0);
$articleSearch    = trim($_GET['q'] ?? '');

// Filtrage catégorie
if ($selectedCategory) {
    $articles = array_filter($articles, function ($article) use ($selectedCategory) {
        return (int)$article['id_categorie'] === $selectedCategory;
    });
}

// Recherche texte
if ($articleSearch !== '') {
    $articles = array_filter($articles, function ($article) use ($articleSearch) {
        return stripos($article['titre'], $articleSearch) !== false
            || stripos($article['contenu'], $articleSearch) !== false;
    });
}

$articleCount = count($articles);

// ===============================
// Helper
// ===============================
function getUserFullnameById($userId, $userController) {
    $user = $userController->getUser($userId);
    return $user ? $user->getNom() : 'Utilisateur inconnu';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SafeSpace - Accueil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/main.css">
    <noscript><link rel="stylesheet" href="assets/css/noscript.css"></noscript>
</head>

<body class="is-preload">

<div id="page-wrapper">

    <!-- HEADER -->
    <header id="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <a href="index.php" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
                <img src="images/logo.png" alt="SafeSpace" style="height:40px;">
                <h1>SafeSpace</h1>
            </a>

            <nav>
                <a href="index.php">Accueil</a> |
                <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                    <a href="../backoffice/index.php">Dashboard</a> |
                <?php endif; ?>
                <?php if (($_SESSION['user_role'] ?? '') === 'conseilleur'): ?>
                    <a href="../backoffice/adviser_dashboard.php">Tableau de bord</a> |
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php">Profil</a> |
                    <a href="logout.php">Déconnexion</a>
                <?php else: ?>
                    <a href="login.php">Connexion</a> |
                    <a href="register.php">Inscription</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- HERO -->
    <section id="wrapper">
        <header>
            <div class="inner">
                <h2>Bienvenue sur SafeSpace</h2>
                <p>Exprimez vos pensées dans un espace sécurisé et bienveillant.</p>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <h3>Bienvenue, <?= htmlspecialchars($_SESSION['fullname'] ?? 'Utilisateur') ?></h3>
                    <p>Rôle : <?= htmlspecialchars($_SESSION['user_role'] ?? 'Membre') ?></p>
                <?php else: ?>
                    <a href="register.php" class="button primary">S'inscrire</a>
                    <a href="login.php" class="button">Se connecter</a>
                <?php endif; ?>
            </div>
        </header>

        <!-- FEATURES -->
        <div class="wrapper">
            <div class="inner">
                <section class="features">
                    <div class="feature"><h3>🔒 Sécurisé</h3><p>Données protégées</p></div>
                    <div class="feature"><h3>🤝 Bienveillant</h3><p>Communauté respectueuse</p></div>
                    <div class="feature"><h3>💬 Libre</h3><p>Expression sans jugement</p></div>
                </section>

                <!-- ARTICLES -->
                <section>
                    <h2>Articles approuvés (<?= $articleCount ?>)</h2>

                    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;">
                        <input type="text" name="q" placeholder="Recherche"
                               value="<?= htmlspecialchars($articleSearch) ?>">
                        <select name="category">
                            <option value="0">Toutes catégories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id_categorie'] ?>"
                                    <?= $selectedCategory === (int)$cat['id_categorie'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nom_categorie']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select name="sort">
                            <option value="recent">Plus récents</option>
                            <option value="views">Plus consultés</option>
                            <option value="title">Titre A-Z</option>
                        </select>
                        <button class="button primary">Filtrer</button>
                    </form>

                    <ul class="comments-list">
                        <?php foreach ($articles as $article): ?>
                            <?php
                            $cat = $categorieC->getCategorie((int)$article['id_categorie']);
                            $counts = $reactionC->countReactionsByArticle((int)$article['id_article']);
                            ?>
                            <li class="comment-item">
                                <strong><?= htmlspecialchars($article['titre']) ?></strong><br>
                                <?= nl2br(htmlspecialchars(substr($article['contenu'], 0, 150))) ?>...
                                <br>
                                👁️ <?= (int)($article['view_count'] ?? 0) ?>
                                👍 <?= $counts['like'] ?? 0 ?>
                                👎 <?= $counts['dislike'] ?? 0 ?>
                                <br>
                                <a href="article_detail.php?id=<?= $article['id_article'] ?>" class="button">
                                    Lire
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>

                <!-- POSTS -->
                <section>
                    <h2>Postes approuvés</h2>

                    <?php foreach ($list_Post as $post): ?>
                        <?php $fullname = getUserFullnameById($post['id_user'], $userController); ?>
                        <div class="comment-item">
                            <strong><?= htmlspecialchars($fullname) ?></strong>
                            <p><?= nl2br(htmlspecialchars($post['message'])) ?></p>

                            <?php if (!empty($post['image']) && file_exists($post['image'])): ?>
                                <img src="<?= htmlspecialchars($post['image']) ?>" style="max-width:100%;">
                            <?php endif; ?>

                            <a href="addCom.php?id=<?= $post['id'] ?>" class="button">
                                Commenter
                            </a>
                        </div>
                    <?php endforeach; ?>
                </section>

            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <section id="footer">
        <div class="inner">
            <p>SafeSpace © <?= date('Y') ?></p>
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
