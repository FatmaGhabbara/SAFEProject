<?php
include '../../Controller/CommentC.php';
$author = $_GET['author'];
$message = $_GET['message'];
$time = $_GET['currentTime'];
$id = $_GET['id'];
$pc = new CommentC();
$p = new Comment($author, $message, $time);
$pc->modifyComment($p,$id);
header('Location: afficher.php');
?>
