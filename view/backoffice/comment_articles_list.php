<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/login.php');
    exit();
}

require_once __DIR__ . '/../../controller/CommentArticleC.php';
require_once __DIR__ . '/../../controller/ArticleC.php';

$commentC = new CommentArticleC();
$articleC = new ArticleC();

if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
    $commentC->deleteComment((int)$_GET['id']);
}

$selectedArticle = (int)($_GET['article'] ?? 0);
$search = trim($_GET['q'] ?? '');
$comments = $commentC->listAllComments();
$articles = $articleC->listArticles();

// Ensure comments and articles are arrays
if (!is_array($comments)) {
    $comments = [];
}
if (!is_array($articles)) {
    $articles = [];
}

$articlesById = [];
foreach ($articles as $article) {
    $articlesById[$article['id_article']] = $article['titre'];
}

if ($selectedArticle) {
    $comments = array_filter($comments, function ($c) use ($selectedArticle) {
        return (int)$c['id_article'] === $selectedArticle;
    });
}

if ($search) {
    $comments = array_filter($comments, function ($comment) use ($search) {
        return stripos($comment['contenu'], $search) !== false || stripos($comment['id_user'], $search) !== false || stripos($comment['titre'] ?? '', $search) !== false;
    });
}

if (!empty($comments)) {
    usort($comments, function ($a, $b) {
        return strcmp($b['date_comment'], $a['date_comment']);
    });
}

$totalComments = count($comments);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commentaires d'articles</title>
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
    <?php include 'includes/admin_sidebar.php'; ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content" class="p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h1 class="h3 mb-0 text-gray-800">Commentaires sur les articles</h1>
                <span class="badge badge-primary p-2"><?php echo $totalComments; ?> commentaire<?php echo $totalComments > 1 ? 's' : ''; ?></span>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Liste des commentaires</h6>
                    <form class="form-inline" method="GET">
                        <select class="form-control mr-2 mb-2" name="article">
                            <option value="0">Tous les articles</option>
                            <?php foreach ($articles as $article): ?>
                                <option value="<?php echo $article['id_article']; ?>" <?php echo $selectedArticle === (int)$article['id_article'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($article['titre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" name="q" placeholder="Rechercher par contenu ou utilisateur" value="<?php echo htmlspecialchars($search); ?>">
                            <div class="input-group-append"><button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button></div>
                        </div>
                    </form>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Article</th><th>Utilisateur</th><th>Contenu</th><th>Date</th><th class="text-right">Actions</th></tr></thead>
                        <tbody>
                        <?php if (!$comments): ?>
                            <tr><td colspan="5" class="text-center text-muted">Aucun commentaire trouvé.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($comments as $comment): ?>
                            <tr>
                                <td>
                                    <div class="font-weight-bold"><?php echo htmlspecialchars($comment['titre'] ?? ($articlesById[$comment['id_article']] ?? '')); ?></div>
                                    <div class="text-muted small">ID article: <?php echo htmlspecialchars($comment['id_article']); ?></div>
                                    <a class="btn btn-sm btn-outline-secondary mt-1" target="_blank" href="../frontoffice/article_detail.php?id=<?php echo $comment['id_article']; ?>"><i class="fas fa-external-link-alt mr-1"></i>Ouvrir</a>
                                </td>
                                <td><?php echo htmlspecialchars($comment['id_user']); ?></td>
                                <td>
                                    <div><?php echo htmlspecialchars($comment['contenu']); ?></div>
                                    <span class="badge badge-light"><i class="fas fa-hashtag mr-1"></i><?php echo strlen($comment['contenu']); ?> caractères</span>
                                </td>
                                <td><span class="text-muted"><i class="far fa-clock mr-1"></i><?php echo htmlspecialchars($comment['date_comment']); ?></span></td>
                                <td class="text-right">
                                    <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?php echo $comment['id_comment']; ?>" onclick="return confirm('Supprimer ce commentaire ?');" title="Supprimer"><i class="fas fa-trash"></i></a>
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
<script src="assets/vendor/jquery/jquery.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sb-admin-2.min.js"></script>
</body>
</html>
