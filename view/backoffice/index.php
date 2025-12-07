
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/AdminController.php';
session_start();

/*if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../frontoffice/login.php');
    exit();
}*/
include '../../Controller/PostC.php';
include '../../Controller/CommentC.php';
include '../../Controller/RespondC.php';

$pc = new PostC();
$list_Post = $pc->listPost();

$cc = new CommentC();
$rc = new RespondC();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - SafeSpace</title>
    <link rel="stylesheet" href="../frontoffice/assets/css/main.css">
    <noscript><link rel="stylesheet" href="../frontoffice/assets/css/noscript.css"></noscript>
</head>
<body class="is-preload">

<div id="page-wrapper">

    <!-- Header -->
    <header id="header">
        <h1><a href="index.php">SafeSpace - Administration</a></h1>
        <nav>
            <a href="index.php">Dashboard</a> |
            <a href="users_list.php">Gérer les utilisateurs</a> |
            <a href="../frontoffice/index.php">Site public</a> |
            <a href="../frontoffice/logout.php">Déconnexion</a>
        </nav>
    </header>

    <!-- Wrapper -->
    <section id="wrapper">
        <header>
            <div class="inner">
                <h2>Tableau de Bord Administrateur</h2>
                <p>Bienvenue, <?/*= htmlspecialchars($_SESSION['username']) */?> !</p>
                <p>Vous êtes connecté en tant qu'administrateur de SafeSpace.</p>
            </div>
        </header>

        <!-- Content -->
        <div class="wrapper">
            <div class="inner">

                <section class="features">
                    <div class="feature">
                        <h3 class="major">👥 Gestion des Utilisateurs</h3>
                        <p>Gérez les membres de la communauté : approuvez, bloquez ou supprimez des comptes.</p>
                        <a href="users_list.php" class="button primary">Accéder à la gestion</a>
                    </div>
                    
                    <div class="feature">
                        <h3 class="major">🌐 Site Public</h3>
                        <p>Retournez sur le site principal pour voir l'expérience utilisateur.</p>
                        <a href="../frontoffice/index.php" class="button">Visiter le site</a>
                    </div>
                    
                    <div class="feature">
                        <h3 class="major">⚙️ Mon Profil</h3>
                        <p>Consultez et modifiez votre profil administrateur.</p>
                        <a href="../frontoffice/profile.php" class="button">Voir mon profil</a>
                    </div>
                </section>

                <section class="main-content">
                    <h3 class="major">Actions Rapides</h3>
                    <div class="quick-actions">
                        <a href="users_list.php?action=list" class="button small">Voir tous les utilisateurs</a>
                        <a href="../frontoffice/profile.php" class="button small">Mon compte</a>
                        <a href="../frontoffice/logout.php" class="button small">Déconnexion</a>
                    </div>
                </section>
                        
                        <!-- Section Posts -->
                        <div class="posts-container">
                            <button class="btn-add-post" onclick="window.location.href='addPost.php'">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                Add Post
                            </button>
                        </div>
                        <!-- Section Postes existants -->
                        <div class="comments-section">
                            <h2>les Postes</h2>
                            <ul class="comments-list">
                                <?php
                                foreach($list_Post as $post){
                                ?>
                                <li class="comment-item">
                                    <div class="comment-header">
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
                                            <button class="btn-delete" onclick="window.location.href='deletePost.php?id=<?=$post['id']; ?>'">
                                                Supprimer Post
                                            </button>
                                            <button class="btn-update" onclick="window.location.href='modifierPost.php?id=<?=$post['id']; ?>&author=<?=$post['author']; ?>&message=<?=$post['message']; ?>'">
                                                Modifier Post
                                            </button>
                                            <button class="btn-respond" onclick="window.location.href='addCom.php?id=<?=$post['id']; ?>'">
                                                Ajouter Commentaire
                                            </button>
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
                                            <button class="btn-delete" onclick="window.location.href='deleteCom.php?id=<?=$comment['id']; ?>'">
                                                Supprimer Commentaire
                                            </button>
                                            <button class="btn-update" onclick="window.location.href='modifierCom.php?id=<?=$comment['id']; ?>&id_post=<?=$comment['id_post']; ?>&author=<?=$comment['author']; ?>&message=<?=$comment['message']; ?>'">
                                                Modifier Commentaire
                                            </button>
                                            <button class="btn-respond" onclick="window.location.href='addRes.php?id_Com=<?=$comment['id']; ?>&id_post=<?=$comment['id_post']; ?>'">
                                                Ajouter Reponse
                                            </button>
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
                                            <button class="btn-delete" onclick="window.location.href='deleteRes.php?id=<?=$respond['id']; ?>'">
                                                Supprimer
                                            </button>
                                            <button class="btn-update" onclick="window.location.href='modifierRes.php?id=<?=$respond['id']; ?>&id_post=<?=$respond['id_post']; ?>&id_com=<?=$respond['id_com']; ?>&author=<?=$respond['author']; ?>&message=<?=$respond['message']; ?>'">
                                                Modifier
                                            </button>
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

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Footer -->
    <section id="footer">
        <div class="inner">
            <h2 class="major">SafeSpace Administration</h2>
            <p>Plateforme de gestion et modération de la communauté SafeSpace.</p>
            <ul class="contact">
                <li class="icon solid fa-home">Panel d'administration</li>
                <li class="icon solid fa-user">Connecté en tant que : <?/*= htmlspecialchars($_SESSION['username']) */?></li>
                <li class="icon solid fa-shield-alt">Rôle : Administrateur</li>
            </ul>
            <ul class="copyright">
                <li>&copy; SafeSpace. Tous droits réservés.</li>
                <li>Panel Admin</li>
            </ul>
        </div>
    </section>

</div>

<!-- Scripts -->
<script src="../frontoffice/assets/js/jquery.min.js"></script>
<script src="../frontoffice/assets/js/jquery.scrollex.min.js"></script>
<script src="../frontoffice/assets/js/browser.min.js"></script>
<script src="../frontoffice/assets/js/breakpoints.min.js"></script>
<script src="../frontoffice/assets/js/util.js"></script>
<script src="../frontoffice/assets/js/main.js"></script>

</body>
</html>