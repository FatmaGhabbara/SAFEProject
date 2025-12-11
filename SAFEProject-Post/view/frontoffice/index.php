<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject-Post/controller/UserController.php';

$userController = new UserController();
$users = $userController->listUsers();

include '../../Controller/PostC.php';
include '../../Controller/CommentC.php';
include '../../Controller/RespondC.php';

$pc = new PostC();
$list_Post = $pc->listPostProuver();

$cc = new CommentC();
$rc = new RespondC();

// Create a function to get user fullname by ID
function getUserFullnameById($userId, $userController) {
    $user = $userController->getUser($userId);
    if ($user) {
        return $user->getFullname();
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
</head>
<body class="is-preload">

<div id="page-wrapper">

    <!-- Header -->
    <header id="header">
        <h1><a href="index.php">SafeSpace</a></h1>
        <nav>
            <a href="index.php">Accueil</a> |
            <a href="../backoffice/index.php">Admin</a> |
            <a href="profile.php">Profil</a> |
            <a href="login.php">Connexion</a> |
            <a href="register.php">Inscription</a>
        </nav>
    </header>

    <!-- Wrapper -->
    <section id="wrapper">
        <header>
            <div class="inner">
                <h2>Bienvenue sur SafeSpace</h2>
                <p>Envie de libérer vos émotions ? Partagez vos pensées en toute sécurité.</p>
            </div>
        </header>

        

        <!-- Content -->
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
            </div>
        </div>
    </section>

    <!-- Footer -->
    <section id="footer">
        <div class="inner">
            <p>Protégeons ensemble, agissons avec bienveillance.</p>
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
<script src="script.js"></script>

</body>
</html>