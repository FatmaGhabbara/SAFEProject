<?php
include '../../Controller/CommentC.php';
include '../../Controller/RespondC.php';
$pc = new CommentC(); 
$list = $pc->listComment();
$rc = new RespondC();


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    
    <h1>Back-Office</h1>
    
    <!-- Section Posts -->
    <div class="posts-container">

            <button class="btn-add-comment" onclick="window.location.href='add.php'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                Add Comment
            </button>
        </div>

    </div>

    <!-- Section Commentaires existants -->
    <div class="comments-section">
        <h2>Tous les commentaires</h2>
        <ul class="comments-list">
            <?php
            foreach($list as $comment){
            ?>
            <li class="comment-item">
                <div class="comment-header">
                    <span class="author"><?php echo htmlspecialchars($comment['author']); ?></span>
                    <span class="time"><?php echo htmlspecialchars($comment['time']); ?></span>
                </div>
                <div class="message"><?php echo htmlspecialchars($comment['message']); ?></div>
                <div class="comment-footer">
                    <span class="id">ID: <?php echo $comment['id']; ?></span>
                    <div class="comment-actions">
                        <button class="btn-delete" onclick="window.location.href='delete.php?id=<?=$comment['id']; ?>'">
                            Supprimer
                        </button>
                        <button class="btn-update" onclick="window.location.href='modifier.php?id=<?=$comment['id']; ?>&author=<?=$comment['author']; ?>&message=<?=$comment['message']; ?>'">
                            Modifier
                        </button>
                        <button class="btn-respond" onclick="window.location.href='addRes.php?id=<?=$comment['id']; ?>'">
                            Respond
                        </button>
                    </div>
                </div>
            </li>
            
        <div class="response-section">
            <h3>Tous les reponses</h3>
            <ul class="response-list">
                <?php
                
                $listres = $rc->listRespond($comment['id']);
                foreach($listres as $respond){
                ?>
                <li class="response-item">
                    <div class="response-header">
                        <span class="author"><?php echo htmlspecialchars($respond['author']); ?></span>
                        <span class="time"><?php echo htmlspecialchars($respond['time']); ?></span>
                    </div>
                    <div class="message"><?php echo htmlspecialchars($respond['message']); ?></div>
                    <div class="comment-footer">
                    <span class="id">ID: <?php echo $comment['id']; ?></span>
                    <div class="response-actions">
                        <button class="btn-delete" onclick="window.location.href='deleteRes.php?id=<?=$respond['id']; ?>'">
                            Supprimer
                        </button>
                        <button class="btn-update" onclick="window.location.href='modifierRes.php?id=<?=$respond['id']; ?>&id_com=<?=$respond['id_com']; ?>&author=<?=$respond['author']; ?>&message=<?=$respond['message']; ?>'">
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
    

</body>
</html>