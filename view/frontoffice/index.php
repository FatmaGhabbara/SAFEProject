<?php
// Activer les erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrer la session
session_start();

// Inclure l'AuthController pour vérifier l'authentification
$controller_path = $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/AuthController.php';
if (file_exists($controller_path)) {
    require_once $controller_path;
    $authController = new AuthController();
} else {
    die("Erreur: Fichier contrôleur introuvable");
}

//for Post
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/usercontroller.php';

$userController = new UserController();
$users = $userController->listUsers();

include '../../controller/PostC.php';
include '../../controller/CommentC.php';
include '../../controller/RespondC.php';
include '../../controller/ArticleC.php';
include '../../controller/CategorieC.php';
include '../../controller/ReactionC.php';
include '../../controller/CommentArticleC.php';

$pc = new PostC();
$list_Post = $pc->listPostProuver();

$cc = new CommentC();
$rc = new RespondC();
$articleC = new ArticleC();
$categorieC = new CategorieC();
$reactionC = new ReactionC();
$commentArticleC = new CommentArticleC();
$sortBy = $_GET['sort'] ?? 'recent';
$orderBy = null;
if ($sortBy === 'views') {
    $orderBy = 'views';
} elseif ($sortBy === 'title') {
    $orderBy = 'title';
}

$articles = $articleC->listArticles('approved', $orderBy);
$trendingArticles = $articleC->getTopViewedArticles(3);
$popularArticles = $articleC->getPopularArticles(3);
$categories = $categorieC->listCategories();
$list_Post = is_array($list_Post) ? $list_Post : [];
$articles = is_array($articles) ? $articles : [];
$trendingArticles = is_array($trendingArticles) ? $trendingArticles : [];
$popularArticles = is_array($popularArticles) ? $popularArticles : [];
$categories = is_array($categories) ? $categories : [];
$selectedCategory = (int)($_GET['category'] ?? 0);
$articleSearch = trim($_GET['q'] ?? '');

// allow simple filtering from the landing page
if ($selectedCategory) {
    $articles = array_filter($articles, function ($article) use ($selectedCategory) {
        return (int)$article['id_categorie'] === $selectedCategory;
    });
}

if ($articleSearch !== '') {
    $articles = array_filter($articles, function ($article) use ($articleSearch) {
        return stripos($article['titre'], $articleSearch) !== false || stripos($article['contenu'], $articleSearch) !== false;
    });
}

$articleCount = count($articles);

// Create a function to get user fullname by ID
function getUserFullnameById($userId, $userController) {
    $user = $userController->getUser($userId);
    if ($user) {
        return $user->getNom();
    }
    return "Utilisateur inconnu";
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeSpace - Accueil</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <noscript><link rel="stylesheet" href="assets/css/noscript.css"></noscript>
   <style>
    #signalements-container {
				margin-top: 2em;
			}
			.signalement-card {
				background: rgba(255, 255, 255, 0.05);
				border: 1px solid rgba(255, 255, 255, 0.1);
				border-radius: 4px;
				padding: 1.5em;
				margin-bottom: 1.5em;
			}
			.signalement-card h3 {
				margin-top: 0;
				color: #fff;
			}
			.signalement-type {
				display: inline-block;
				background: rgba(255, 255, 255, 0.1);
				padding: 0.3em 0.8em;
				border-radius: 3px;
				font-size: 0.9em;
				margin-bottom: 0.5em;
			}
			.signalement-date {
				color: rgba(255, 255, 255, 0.6);
				font-size: 0.9em;
				margin-top: 0.5em;
				margin-bottom: 1em;
			}
			.signalement-header {
				display: flex;
				justify-content: space-between;
				align-items: start;
				margin-bottom: 1em;
			}
			.signalement-actions {
				margin-top: 1.5em;
				display: flex;
				gap: 1em;
			}
            .action-link {
				color: rgba(255, 255, 255, 0.8);
				text-decoration: none;
				padding: 0.5em 1em;
				border: 1px solid rgba(255, 255, 255, 0.2);
				border-radius: 4px;
				transition: all 0.3s;
			}
			.action-link:hover {
				background: rgba(255, 255, 255, 0.1);
				border-color: rgba(255, 255, 255, 0.4);
			}
			.delete-link {
				color: rgba(244, 67, 54, 0.9);
				border-color: rgba(244, 67, 54, 0.5);
			}
			.delete-link:hover {
				background: rgba(244, 67, 54, 0.2);
				border-color: rgba(244, 67, 54, 0.8);
			}
			.search-box {
				margin-bottom: 2em;
			}
			.search-box input {
				width: 100%;
				padding: 0.8em;
				background: rgba(255, 255, 255, 0.05);
				border: 1px solid rgba(255, 255, 255, 0.1);
				color: #fff;
			}
			.alert {
				padding: 1em;
				margin-bottom: 1em;
				border-radius: 4px;
			}
			.alert-success {
				background: rgba(76, 175, 80, 0.2);
				border: 1px solid rgba(76, 175, 80, 0.5);
				color: #4caf50;
			}
			.alert-error {
				background: rgba(244, 67, 54, 0.2);
				border: 1px solid rgba(244, 67, 54, 0.5);
				color: #f44336;
			}
			.loading {
				text-align: center;
				padding: 2em;
				color: rgba(255, 255, 255, 0.6);
			}
			.error-message {
				color: #f44336;
				font-size: 0.9em;
				margin-top: 0.3em;
				display: none;
			}
				.field.has-error input,
			.field.has-error select,
			.field.has-error textarea {
				border-color: #f44336;
			}
			.field.has-error .error-message {
				display: block;
			}

			/* Type description card styling */
			#type-description .type-desc-card {
				display: flex;
				align-items: flex-start;
				gap: 12px;
				background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
				border: 1px solid rgba(255,255,255,0.06);
				padding: 10px 12px;
				border-radius: 8px;
				margin-top: 8px;
				color: rgba(255,255,255,0.95);
			}

			#type-description .type-desc-icon {
				min-width: 36px;
				height: 36px;
				border-radius: 8px;
				display: flex;
				align-items: center;
				justify-content: center;
				background: linear-gradient(135deg, #667eea, #764ba2);
				color: #fff;
				font-size: 1.0rem;
			}

			#type-description .type-desc-name {
				font-weight: 700;
				color: #ffffff;
				margin: 0 0 3px; 
				font-size: 0.95rem;
			}

			#type-description .type-desc-text {
				margin: 0;
				color: rgba(255,255,255,0.85);
				font-size: 0.9rem;
				line-height: 1.3;
			}
    </style>
</head>
<body class="is-preload">

<div id="page-wrapper">
    <header id="header">
        <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <a class="navbar-brand nav-logo text-primary" href="index.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
                    <img src="images/logo.png" alt="SafeSpace Logo" style="height: 40px; width: auto;">
                    <h1 style="margin: 0; font-size: 1.5em;">SafeSpace</h1>
                </a>
            </div>
           
            <nav>
                <a href="index.php">Accueil</a> |
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="../backoffice/index.php">Dashboard</a> |
                <?php endif; ?>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'conseilleur'): ?>
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

    <section id="wrapper">
        <header>
            <div class="inner">
                <h2>Bienvenue sur SafeSpace</h2>
                <p>Envie de libérer vos émotions ? Partagez vos pensées en toute sécurité.</p>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="welcome-message">
                        <h3>Bienvenue, <?= htmlspecialchars($_SESSION['fullname'] ?? 'Utilisateur') ?> !</h3>
                        <p>Votre rôle: <?= htmlspecialchars($_SESSION['user_role'] ?? 'Membre') ?></p>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; margin: 20px 0;">
                        <a href="register.php" class="button primary" style="margin-right: 10px;">S'inscrire</a>
                        <a href="login.php" class="button">Se connecter</a>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <div class="wrapper">
            <div class="inner">
                <section class="features">
                    <div class="feature">
                        <h3 class="major">🔒 Sécurisé</h3>
                        <p>Vos données sont protégées et votre anonymat préservé</p>
                    </div>
                    <div class="feature">
                        <h3 class="major">🤝 Bienveillant</h3>
                        <p>Une communauté respectueuse et à l'écoute</p>
                    </div>
                    <div class="feature">
                        <h3 class="major">💬 Libre</h3>
                        <p>Exprimez-vous sans jugement dans un espace safe</p>
                    </div>
                </section>
                <section class="articles-section">
                    <div style="display:flex; align-items:center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <p class="pill" style="display:inline-block; margin-bottom:8px;">📚 <?php echo $articleCount; ?> article<?php echo $articleCount > 1 ? 's' : ''; ?> approuvés</p>
                            <h2 style="margin:0;">Articles approuvés</h2>
                        </div>
                        <div style="display:flex; gap:10px; flex-wrap: wrap; align-items:center;">
                              <form method="GET" style="display:flex; gap:8px; flex-wrap: wrap;">
                                  <input type="text" name="q" placeholder="Rechercher un titre ou une idée" value="<?php echo htmlspecialchars($articleSearch); ?>" style="padding:8px 10px; border-radius: 8px; border: 1px solid #ddd; min-width:220px;">
                                  <select name="category" style="padding:8px 10px; border-radius: 8px; border: 1px solid #ddd; min-width: 180px;">
                                      <option value="0">Toutes les catégories</option>
                                      <?php foreach ($categories as $cat): ?>
                                          <option value="<?php echo $cat['id_categorie']; ?>" <?php echo $selectedCategory === (int)$cat['id_categorie'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['nom_categorie']); ?></option>
                                      <?php endforeach; ?>
                                  </select>
                                  <select name="sort" style="padding:8px 10px; border-radius: 8px; border: 1px solid #ddd; min-width: 180px;">
                                      <option value="recent" <?php echo $sortBy === 'recent' ? 'selected' : ''; ?>>Plus récents</option>
                                      <option value="views" <?php echo $sortBy === 'views' ? 'selected' : ''; ?>>Plus consultés</option>
                                      <option value="title" <?php echo $sortBy === 'title' ? 'selected' : ''; ?>>Titre A → Z</option>
                                  </select>
                                  <button class="button primary" type="submit">Filtrer</button>
                                  <?php if ($selectedCategory || $articleSearch !== ''): ?>
                                      <a class="button" href="index.php">Réinitialiser</a>
                                  <?php endif; ?>
                              </form>
                            <div>
                                <a class="button" href="article_detail.php">Découvrir</a>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <a class="button primary" href="addArticle.php">Proposer un article</a>
                                <?php else: ?>
                                    <a class="button" href="login.php">Connectez-vous pour proposer</a>
                                <?php endif; ?>
                            </div>
                        </div>
                      </div>
                      <?php if ($trendingArticles || $popularArticles): ?>
                          <div class="comments-section" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px;">
                              <?php if ($trendingArticles): ?>
                                  <div class="comment-item">
                                      <div class="comment-header">
                                          <span class="author">Top vues</span>
                                          <span class="time">En temps réel</span>
                                      </div>
                                      <ul>
                                          <?php foreach ($trendingArticles as $trend): ?>
                                              <li style="margin-bottom:6px;">
                                                  <strong><?php echo htmlspecialchars($trend['titre']); ?></strong><br>
                                                  <small>👁️ <?php echo (int)($trend['view_count'] ?? 0); ?> vues</small>
                                              </li>
                                          <?php endforeach; ?>
                                      </ul>
                                  </div>
                              <?php endif; ?>
                              <?php if ($popularArticles): ?>
                                  <div class="comment-item">
                                      <div class="comment-header">
                                          <span class="author">Populaires</span>
                                          <span class="time">Likes & réactions</span>
                                      </div>
                                      <ul>
                                          <?php foreach ($popularArticles as $popular): ?>
                                              <li style="margin-bottom:6px;">
                                                  <strong><?php echo htmlspecialchars($popular['titre']); ?></strong><br>
                                                  <small>👍 <?php echo (int)($popular['likes'] ?? 0); ?> • 👎 <?php echo (int)($popular['dislikes'] ?? 0); ?> • 👁️ <?php echo (int)($popular['view_count'] ?? 0); ?></small>
                                              </li>
                                          <?php endforeach; ?>
                                      </ul>
                                  </div>
                              <?php endif; ?>
                          </div>
                      <?php endif; ?>
                      <div class="comments-section">
                          <?php if (!$articles): ?>
                              <div class="comment-item" style="text-align:center;">Aucun article ne correspond à vos filtres. Essayez une autre recherche.</div>
                          <?php endif; ?>
                          <ul class="comments-list">
                            <?php foreach ($articles as $article): ?>
                                <?php
                                $cat = $categorieC->getCategorie((int)$article['id_categorie']);
                                $categoryName = $cat['nom_categorie'] ?? 'Non classé';
                                $counts = $reactionC->countReactionsByArticle((int)$article['id_article']);
                                ?>
                                <li class="comment-item">
                                    <div class="comment-header">
                                          <span class="author"><?php echo htmlspecialchars($categoryName); ?></span>
                                          <span class="time"><?php echo htmlspecialchars($article['date_creation']); ?> • 👁️ <?php echo (int)($article['view_count'] ?? 0); ?> vues</span>
                                      </div>
                                      <div class="message"><strong><?php echo htmlspecialchars($article['titre']); ?></strong><br><?php echo nl2br(htmlspecialchars(substr($article['contenu'],0,160))); ?>...</div>
                                      <div class="comment-footer">
                                          <div class="comment-actions" style="display:flex; gap:10px; align-items:center; flex-wrap: wrap;">
                                              <a class="button" href="article_detail.php?id=<?php echo $article['id_article']; ?>">Lire l'article</a>
                                              <span class="id">👍 <?php echo $counts['like'] ?? 0; ?> | 👎 <?php echo $counts['dislike'] ?? 0; ?></span>
                                          </div>
                                      </div>
                                  </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>
                <!-- Section Postes approuvees -->
                <div class="comments-section">
                    <h2>les Postes approuvees</h2>
                    <ul class="comments-list">
                        <?php
                        foreach($list_Post as $post){
                            // Get user fullname instead of showing id_user
                            $userFullname = getUserFullnameById($post['id_user'], $userController);
                        ?>
                        <li class="comment-item">
                            <div class="comment-header">
                                <span class="author"><?php echo htmlspecialchars($userFullname); ?></span>
                                <span class="author"><?php echo htmlspecialchars($post['author']); ?></span>
                                <span class="time"><?php echo htmlspecialchars($post['time']); ?></span>
                            </div>
                            
                            <div class="message"><?php echo nl2br(htmlspecialchars($post['message'])); ?></div>
                            
                            <!-- Image Display Section -->
                            <?php if (!empty($post['image']) && file_exists($post['image'])): ?>
                                <div class="image-container">
                                    <img src="<?php echo htmlspecialchars($post['image']); ?>" 
                                        alt="Post image" 
                                        class="post-image"
                                        onerror="this.style.display='none'">
                                </div>
                            <?php elseif (!empty($post['image'])): ?>
                                <div class="no-image">
                                    Image not found: <?php echo htmlspecialchars(basename($post['image'])); ?>
                                </div>
                            <?php else: ?>
                                <div class="no-image">No image</div>
                            <?php endif; ?>
                            
                            <div class="comment-footer">
                                <span class="id">ID: <?php echo $post['id']; ?></span>
                                <div class="comment-actions">
                                    <!-- You can add other actions here -->
                    
                                    <button class="btn-respond" onclick="window.location.href='addCom.php?id=<?=$post['id']; ?>'">
                                        Ajouter Commentaire
                                    </button>
                                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                        <button class="btn-update" onclick="window.location.href='modifierPost.php?id=<?=$post['id']; ?>&author=<?=$post['author']; ?>&message=<?=$post['message']; ?>'">
                                            Modifier Post
                                        </button>
                                        <button class="btn-delete" onclick="window.location.href='deletePost.php?id=<?=$post['id']; ?>'">
                                            Supprimer Post
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <!-- comments-list -->
                    <div class="response-section">
                        <h3>les Commentaires</h3>
                        <ul class="response-list">
                            <?php
                            
                            $list_Com = $cc->listComment($post['id']);
                            foreach($list_Com as $comment){
                            ?>
                            <li class="response-item">
                                <div class="response-header">
                                    <span class="author"><?php echo htmlspecialchars($comment['author']); ?></span>
                                    <span class="time"><?php echo htmlspecialchars($comment['time']); ?></span>
                                </div>
                                <div class="message"><?php echo htmlspecialchars($comment['message']); ?></div>
                                <div class="comment-footer">
                                <span class="id">ID: <?php echo $comment['id']; ?></span>
                                <div class="response-actions">
                                    
                                    <button class="btn-respond" onclick="window.location.href='addRes.php?id_Com=<?=$comment['id']; ?>&id_post=<?=$comment['id_post']; ?>'">
                                        Ajouter Reponse
                                    </button>
                                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                        <button class="btn-delete" onclick="window.location.href='deleteCom.php?id=<?=$comment['id']; ?>'">
                                            Supprimer Commentaire
                                        </button>
                                        <button class="btn-update" onclick="window.location.href='modifierCom.php?id=<?=$comment['id']; ?>&id_post=<?=$comment['id_post']; ?>&author=<?=$comment['author']; ?>&message=<?=$comment['message']; ?>'">
                                            Modifier Commentaire
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            </li>
                    <!-- respond-list -->
                    <div class="response-section">
                        <h3>les Reponses</h3>
                        <ul class="response-list">
                            <?php
                            
                            $list_Res = $rc->listRespond($comment['id']);
                            foreach($list_Res as $respond){
                            ?>
                            <li class="response-item">
                                <div class="response-header">
                                    <span class="author"><?php echo htmlspecialchars($respond['author']); ?></span>
                                    <span class="time"><?php echo htmlspecialchars($respond['time']); ?></span>
                                </div>
                                <div class="message"><?php echo htmlspecialchars($respond['message']); ?></div>
                                <div class="comment-footer">
                                <span class="id">ID: <?php echo $respond['id']; ?></span>
                                <div class="response-actions">
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                    <button class="btn-delete" onclick="window.location.href='deleteRes.php?id=<?=$respond['id']; ?>'">
                                        Supprimer
                                    </button>
                                    <button class="btn-update" onclick="window.location.href='modifierRes.php?id=<?=$respond['id']; ?>&id_post=<?=$respond['id_post']; ?>&id_com=<?=$respond['id_com']; ?>&author=<?=$respond['author']; ?>&message=<?=$respond['message']; ?>'">
                                        Modifier
                                    </button>
                                <?php endif; ?>
                                </div>
                            </div>
                            </li>
                            <?php } ?>    
                        </ul>
                    </div>
                            <?php } ?>    
                        </ul>
                    </div>
                        <?php } ?>    
                    </ul>
                </div>
                <!-- fin section posts -->
                <!-- Formulaire de Signalement -->
                <section id="form-signalement" class="wrapper spotlight style1">
                    <div class="inner">
                        <div class="content">
                            <h2 class="major">Créer un Signalement</h2>
                            <p>Partagez votre expérience ou votre préoccupation de manière anonyme et sécurisée.</p>
                        
                            <div id="alert-container"></div>
                            
                            <form id="signalement-form">
                                <div class="fields">
                                    <div class="field">
                                        <label for="titre">Titre *</label>
                                        <input type="text" name="titre" id="titre" />
                                        <span class="error-message" id="titre-error"></span>
                                    </div>
                                    <div class="field">
                                        <label for="type_id">Type de signalement *</label>
                                        <select name="type_id" id="type_id">
                                            <option value="">Sélectionnez un type</option>
                                        </select>
                                        <div id="type-description" class="small" style="margin-top:6px; color: rgba(255,255,255,0.7); display:none;"></div>
                                        <span class="error-message" id="type_id-error"></span>
                                    </div>
                                    <div class="field">
                                        <label for="description">Description *</label>
                                        <textarea name="description" id="description" rows="4"></textarea>
                                        <span class="error-message" id="description-error"></span>
                                    </div>
                                </div>
                                <ul class="actions">
                                    <li><input type="submit" value="Envoyer le Signalement" class="primary" /></li>
                                    <li><input type="reset" value="Réinitialiser" /></li>
                                </ul>
                            </form>
                        </div>
                    </div>
                </section>

                <!-- Liste des Signalements -->
                <section id="liste-signalements" class="wrapper alt style1">
                    <div class="inner">
                        <h2 class="major">Signalements Récents</h2>
                        <p>Découvrez les signalements partagés par la communauté.</p>
                        
                        <!-- Barre de recherche -->
                        <div class="search-box">
                            <input type="text" id="search-input" placeholder="Rechercher un signalement..." />
                        </div>
                        
                        <!-- Container pour les signalements -->
                        <div id="signalements-container">
                            <div class="loading">Chargement des signalements...</div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>

    <section id="footer">
        <div class="inner">
            <p>Protégeons ensemble, agissons avec bienveillance.</p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <p>Connecté en tant que: <?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></p>
            <?php endif; ?>
        </div>
    </section>
</div>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/jquery.scrollex.min.js"></script>
<script src="assets/js/browser.min.js"></script>
<script src="assets/js/breakpoints.min.js"></script>
<script src="assets/js/util.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/register.js"></script>
<script src="assets/js/script_post.js"></script>

<!-- Script pour l'intégration avec l'API -->
<script>
    function computeApiUrl() {
        const baseMatch = window.location.pathname.match(/(.*\/view\/frontoffice)(?:\/|$)/);
        if (baseMatch) return window.location.origin + baseMatch[1] + '/api.php';
        return window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '') + '/api.php';
    }
    const API_URL = computeApiUrl();
    console.debug('index: API_URL=', API_URL);
    // One-time debug call to verify server found config.php
    (function(){
        $.ajax({url: API_URL + '?action=debug_config', method: 'GET', dataType: 'json'})
            .done(function(resp){ console.debug('index debug_config:', resp); })
            .fail(function(jqXHR, status, err){ console.warn('index debug_config failed', status, err); });
    })();

    // delegated handler for delete (keep AJAX delete behavior)
    $(document).on('click', '.ajax-delete', function(e){
        e.preventDefault(); e.stopImmediatePropagation();
        const id = $(this).data('id');
        console.debug('index: ajax-delete clicked id=', id);
        if (!id) return;
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce signalement ?')) return;
        $.ajax({
            url: API_URL + '?action=deleteSignalement&id=' + encodeURIComponent(id),
            type: 'DELETE',
            dataType: 'json'
        }).done(function(resp){
            console.debug('delete resp', resp);
            if (resp && resp.success) {
                $('.ajax-delete[data-id="'+id+'"]').closest('.signalement-card').remove();
                alert('Signalement supprimé');
            } else {
                alert(resp.message || 'Erreur lors de la suppression');
            }
        }).fail(function(){
            alert('Erreur réseau lors de la suppression');
        });
        return false;
    });

    // Charger les types de signalement au chargement de la page
    $(document).ready(function() {
        loadTypes();
        loadSignalements();
        
        // Gérer la soumission du formulaire
        $('#signalement-form').on('submit', function(e) {
            e.preventDefault();
            createSignalement();
        });
        
        // Effacer les erreurs lors de la saisie
        $('#titre').on('input', function() {
            clearFieldError('titre');
        });
        
        $('#type_id').on('change', function() {
            clearFieldError('type_id');
            displayTypeDescription($(this).val());
        });
        
        $('#description').on('input', function() {
            clearFieldError('description');
        });
        
        // Gérer la recherche
        let searchTimeout;
        $('#search-input').on('input', function() {
            clearTimeout(searchTimeout);
            const keyword = $(this).val();
            
            if (keyword.length >= 2 || keyword.length === 0) {
                searchTimeout = setTimeout(() => {
                    if (keyword.length === 0) {
                        loadSignalements();
                    } else {
                        searchSignalements(keyword);
                    }
                }, 500);
            }
        });
    });
    
    // Charger les types de signalement
    function loadTypes() {
        var typesUrl = API_URL + '?action=getTypes';
        // If running on localhost, use diagnostic endpoint to get more info
        if (location.hostname === 'localhost' || location.hostname === '127.0.0.1') {
            typesUrl = API_URL + '?action=types_debug';
        }

        $.ajax({
            url: typesUrl,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                console.debug('loadTypes response:', response);
                const select = $('#type_id');
                select.empty();
                select.append($('<option></option>').val('').text('Sélectionnez un type'));
                if (response && response.success && Array.isArray(response.data) && response.data.length) {
                    // Store types map for quick lookup and set description on options
                    window.safeSpaceTypes = {};
                    response.data.forEach(function(type) {
                        window.safeSpaceTypes[type.id] = type;
                        const $option = $('<option></option>').val(type.id).text(type.nom);
                        if (typeof type.description !== 'undefined' && type.description !== null) {
                            $option.attr('data-description', type.description);
                        }
                        select.append($option);
                    });
                    // Display description for the currently selected value (none by default)
                    displayTypeDescription(select.val());
                } else {
                    console.warn('Aucun type reçu depuis l’API', response);
                    if (response && response.message) showAlert('Erreur lors du chargement des types: ' + response.message, 'error');
                }
            },
            error: function() {
                var jqXHR = arguments[0];
                var textStatus = arguments[1];
                var errorThrown = arguments[2];
                console.error('Erreur AJAX lors du chargement des types', jqXHR.status, textStatus, errorThrown);
                var msg = 'Erreur lors du chargement des types (HTTP ' + jqXHR.status + ')';
                try {
                    if (jqXHR && jqXHR.responseText) {
                        var d = JSON.parse(jqXHR.responseText);
                        if (d && d.message) msg += ': ' + d.message;
                    }
                } catch (e) { /* ignore parse errors */ }
                showAlert(msg, 'error');

                // If running on localhost, attempt debug endpoint to give more info
                if (location.hostname === 'localhost' || location.hostname === '127.0.0.1') {
                    $.ajax({ url: API_URL + '?action=types_debug', method: 'GET', dataType: 'json', timeout: 3000 })
                        .done(function(resp) {
                            console.debug('types_debug:', resp);
                            if (resp && resp.debug) {
                                var dbg = resp.debug;
                                var detail = 'Debug info:\nconfigPath: ' + (dbg.configPath || '[?]') + '\ntypes_count: ' + (dbg.types_count || 0);
                                if (resp.message) detail += '\nmessage: ' + resp.message;
                                showAlert('Détails debug (localhost):\n' + detail, 'error');
                            } else {
                                showAlert('Aucun détail de debug disponible', 'error');
                            }
                        })
                        .fail(function() {
                            console.warn('types_debug request failed');
                        });
                }
            }
        });
    }
    
    // Charger tous les signalements
    function loadSignalements() {
        $('#signalements-container').html('<div class="loading">Chargement des signalements...</div>');
        
        $.ajax({
            url: API_URL + '?action=getSignalements',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    displaySignalements(response.data);
                } else {
                    $('#signalements-container').html('<p>Aucun signalement disponible.</p>');
                }
            },
            error: function() {
                $('#signalements-container').html('<p class="alert alert-error">Erreur lors du chargement des signalements.</p>');
            }
        });
    }

    // Display the description for the selected type
    function displayTypeDescription(typeId) {
        const $descEl = $('#type-description');
        if (!typeId) {
            $descEl.empty().hide();
            return;
        }
        const type = (window.safeSpaceTypes && window.safeSpaceTypes[typeId]) || null;
        if (type) {
            const name = type.nom || '';
            const desc = type.description || '';
            const $wrapper = $('<div class="type-desc-card"></div>');
            const $icon = $('<div class="type-desc-icon">🏷️</div>');
            const $content = $('<div class="type-desc-content"></div>');
            const $nameEl = $('<div class="type-desc-name"></div>').text(name);
            $content.append($nameEl);
            if (desc.trim() !== '') {
                const $descElText = $('<p class="type-desc-text"></p>').text(desc);
                $content.append($descElText);
            }
            $wrapper.append($icon).append($content);
            $descEl.empty().append($wrapper).show();
        } else {
            $descEl.empty().hide();
        }
    }

    // Receive updates from other tabs via BroadcastChannel or localStorage
    if (window.BroadcastChannel) {
        const bc = new BroadcastChannel('safeSpace-types');
        bc.onmessage = function(ev) {
            if (ev && ev.data && ev.data.type === 'updated') {
                applyTypeUpdate(ev.data.data);
            }
        };
    } else {
        window.addEventListener('storage', function(e) {
            if (e.key === 'safeSpace-types' && e.newValue) {
                try {
                    var obj = JSON.parse(e.newValue);
                    if (obj && obj.type === 'updated') {
                        applyTypeUpdate(obj.data);
                    }
                } catch (err) {}
            }
        });
    }

    function applyTypeUpdate(updated) {
        if (!updated) return;
        // Update local map
        window.safeSpaceTypes = window.safeSpaceTypes || {};
        window.safeSpaceTypes[updated.id] = updated;
        // Update select option text and data-description
        var $opt = $('#type_id option[value="' + updated.id + '"]');
        if ($opt.length) {
            $opt.text(updated.nom);
            $opt.attr('data-description', updated.description || '');
        }
        // If the currently selected matches updated, refresh the display
        if ($('#type_id').val() === String(updated.id)) {
            displayTypeDescription(updated.id);
        }
    }
    
    // Rechercher des signalements
    function searchSignalements(keyword) {
        $('#signalements-container').html('<div class="loading">Recherche en cours...</div>');
        
        // Utiliser l'API de recherche AJAX existante
        $.ajax({
            url: 'signalements/recherche_ajax.php?search=' + encodeURIComponent(keyword),
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.count > 0) {
                    $('#signalements-container').html(response.html);
                } else {
                    $('#signalements-container').html('<p>Aucun résultat trouvé pour "' + keyword + '".</p>');
                }
            },
            error: function() {
                // Fallback vers l'API principale
                $.ajax({
                    url: API_URL + '?action=search&keyword=' + encodeURIComponent(keyword),
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            if (response.data.length > 0) {
                                displaySignalements(response.data);
                            } else {
                                $('#signalements-container').html('<p>Aucun résultat trouvé pour "' + keyword + '".</p>');
                            }
                        } else {
                            $('#signalements-container').html('<p>Aucun résultat trouvé.</p>');
                        }
                    },
                    error: function() {
                        $('#signalements-container').html('<p class="alert alert-error">Erreur lors de la recherche.</p>');
                    }
                });
            }
        });
    }
    
    // Afficher les signalements
    function displaySignalements(signalements) {
        if (signalements.length === 0) {
            $('#signalements-container').html('<p>Aucun signalement disponible.</p>');
            return;
        }

        // Show detail modal (same as mes_signalements.js behavior)
        function createDetailModal() {
            if ($('#detailModal').length) return;
            $('body').append(`
            <div id="detailModal" class="modal" style="display:none;">
                <div class="inner" style="max-width:700px;margin:4em auto;background:#111;padding:1.5em;border-radius:8px;">
                    <h3 id="detail-title"></h3>
                    <div id="detail-type" style="margin-bottom:.5em;color:#9fb8ff;font-weight:700;"></div>
                    <div id="detail-date" style="color:#ccc;margin-bottom:1em;"></div>
                    <div id="detail-desc" style="background:#0b0b0b;padding:1em;border-radius:6px;color:#ddd;"></div>
                    <div style="margin-top:1em;text-align:right;">
                        <button id="detail-close" class="button">Fermer</button>
                    </div>
                </div>
            </div>
            `);
            $('#detail-close').on('click', function(){ $('#detailModal').hide(); });
        }

        function showDetail(id) {
            createDetailModal();
            $('#detail-title').text('Chargement...');
            $('#detail-type').text('');
            $('#detail-date').text('');
            $('#detail-desc').text('');
            $('#detailModal').show();
            // Try API first, fallback to HTML page extraction
            $.ajax({
                url: API_URL + '?action=getSignalement&id=' + encodeURIComponent(id),
                method: 'GET',
                dataType: 'json',
                timeout: 5000
            }).done(function(resp){
                console.debug('index showDetail resp', resp);
                if (resp && resp.success && resp.data) {
                    const d = resp.data;
                    $('#detail-title').text(d.titre);
                    $('#detail-type').text(d.type_nom || '—');
                    $('#detail-date').text(new Date(d.created_at).toLocaleString('fr-FR'));
                    $('#detail-desc').html(escapeHtml(d.description).replace(/\n/g,'<br>'));
                } else {
                    $('#detail-title').text('Erreur: détail non disponible');
                }
            }).fail(function(jqXHR, textStatus, errorThrown){
                console.warn('index showDetail API error', textStatus, errorThrown);
                $('#detail-title').text('Erreur réseau ou serveur. Réessayez plus tard.');
            }).always(function(jqXHR, textStatus){
                console.debug('index showDetail AJAX complete, status=', textStatus, 'id=', id);
            });
        }
        
        let html = '';
        signalements.forEach(function(signalement) {
            const date = new Date(signalement.created_at);
            const formattedDate = date.toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            const description = escapeHtml(signalement.description);
            const shortDescription = description.length > 150 ? description.substring(0, 150) + '...' : description;
            
            html += '<div class="signalement-card">';
            html += '<div class="signalement-header">';
            html += '<h3>' + escapeHtml(signalement.titre) + '</h3>';
            html += '<span class="signalement-type">' + (signalement.type_nom || 'Non spécifié') + '</span>';
            html += '</div>';
            html += '<div class="signalement-date">📅 Publié le ' + formattedDate + '</div>';
            html += '<p>' + shortDescription + '</p>';
            html += '<div class="signalement-actions">';
            html += '<a href="signalements/detail_signalement.php?id=' + signalement.id + '" data-id="' + signalement.id + '" class="button small action-link detail-link">👁️ Voir détails</a>';			
            html += '<a href="signalements/modifier_signalement.php?id=' + signalement.id + '" class="button small primary action-link edit-link">✏️ Modifier</a>';			
            html += '<a href="javascript:void(0);" data-id="' + signalement.id + '" class="button small danger action-link delete-link ajax-delete">🗑️ Supprimer</a>';
            html += '</div>';
            html += '</div>';
        });
        
        $('#signalements-container').html(html);
    }
    
    // Créer un nouveau signalement
    function createSignalement() {
        // Réinitialiser les erreurs
        clearErrors();
        
        // Récupérer les valeurs
        const titre = $('#titre').val().trim();
        const typeId = $('#type_id').val();
        const description = $('#description').val().trim();
        
        // Variables pour stocker les erreurs
        let hasErrors = false;
        
        // Validation du titre
        if (titre === '') {
            showError('titre', 'Le titre est obligatoire');
            hasErrors = true;
        } else if (titre.length < 3) {
            showError('titre', 'Le titre doit contenir au moins 3 caractères');
            hasErrors = true;
        } else if (titre.length > 200) {
            showError('titre', 'Le titre ne doit pas dépasser 200 caractères');
            hasErrors = true;
        }
        
        // Validation du type
        if (typeId === '') {
            showError('type_id', 'Veuillez sélectionner un type de signalement');
            hasErrors = true;
        }
        
        // Validation de la description
        if (description === '') {
            showError('description', 'La description est obligatoire');
            hasErrors = true;
        } else if (description.length < 10) {
            showError('description', 'La description doit contenir au moins 10 caractères');
            hasErrors = true;
        } else if (description.length > 2000) {
            showError('description', 'La description ne doit pas dépasser 2000 caractères');
            hasErrors = true;
        }
        
        // Si erreurs, arrêter ici
        if (hasErrors) {
            return;
        }
        
        // Préparer les données
        const formData = {
            titre: titre,
            type_id: typeId,
            description: description
        };
        
        // Envoyer la requête AJAX
        $.ajax({
            url: API_URL + '?action=createSignalement',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert(response.message || 'Signalement créé avec succès !', 'success');
                    $('#signalement-form')[0].reset();
                    clearErrors();
                    loadSignalements();
                } else {
                    let errorMsg = response.message || 'Erreur lors de la création du signalement';
                    if (response.errors && response.errors.length > 0) {
                        errorMsg = response.errors.join('<br>');
                    }
                    showAlert(errorMsg, 'error');
                }
            },
            error: function() {
                showAlert('Erreur lors de la création du signalement', 'error');
            }
        });
    }
    
    // Fonction pour afficher une erreur
    function showError(fieldId, message) {
        const field = $('#' + fieldId);
        const errorElement = $('#' + fieldId + '-error');
        const fieldContainer = field.closest('.field');
        
        fieldContainer.addClass('has-error');
        errorElement.text(message);
        errorElement.show();
    }
    
    // Fonction pour effacer toutes les erreurs
    function clearErrors() {
        $('.error-message').hide().text('');
        $('.field').removeClass('has-error');
    }
    
    // Fonction pour effacer l'erreur d'un champ spécifique
    function clearFieldError(fieldId) {
        const field = $('#' + fieldId);
        const errorElement = $('#' + fieldId + '-error');
        const fieldContainer = field.closest('.field');
        
        fieldContainer.removeClass('has-error');
        errorElement.hide().text('');
    }
    
    // Afficher une alerte
    function showAlert(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
        const alertHtml = '<div class="alert ' + alertClass + '">' + message + '</div>';
        $('#alert-container').html(alertHtml);
        
        // Faire défiler vers l'alerte
        $('html, body').animate({
            scrollTop: $('#form-signalement').offset().top - 100
        }, 500);
        
        // Supprimer l'alerte après 5 secondes
        setTimeout(function() {
            $('#alert-container').fadeOut(function() {
                $(this).html('').show();
            });
        }, 5000);
    }
    
    // Échapper le HTML pour éviter les injections XSS
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text ? text.replace(/[&<>"']/g, function(m) { return map[m]; }) : '';
    }
</script>

</body>
</html>