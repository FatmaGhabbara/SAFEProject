<?php
include '../../Controller/CommentC.php';
$cc = new CommentC();

include '../../Controller/RespondC.php';
$rc = new RespondC();

$id = $_GET['id'];

$cc-> deleteComment($id);
$rc-> deleteResCom($id);

header('Location: index.php');
?>