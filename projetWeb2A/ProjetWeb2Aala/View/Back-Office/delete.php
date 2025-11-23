<?php
include '../../Controller/CommentC.php';
$pc = new CommentC();

include '../../Controller/RespondC.php';
$rc = new RespondC();

$id = $_GET['id'];

$pc-> deleteComment($id);
$rc-> deleteComRespond($id);

header('Location: afficher.php');
?>