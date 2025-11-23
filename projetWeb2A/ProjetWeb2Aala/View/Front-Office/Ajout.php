<?php

include '../../Controller/CommentC.php';
$author = $_GET['author'];
$message = $_GET['message'];  
$time = $_GET['currentTime'];
$pc = new CommentC();
$p = new Comment($author, $message, $time);
$pc-> addComment($p);
header('Location: afficher.php');

?>