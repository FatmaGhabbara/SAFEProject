<?php
include '../../Controller/PostC.php';
include '../../Controller/CommentC.php';
include '../../Controller/RespondC.php';

$pc = new PostC();
$cc = new CommentC();
$rc = new RespondC();

$id = $_GET['id'];

$pc-> deletePost($id);
$cc-> deleteComPost($id);
$rc-> deleteResComPost($id);
header('Location: index.php');
?>