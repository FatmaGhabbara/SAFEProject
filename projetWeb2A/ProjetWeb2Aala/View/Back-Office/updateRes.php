<?php
include '../../Controller/RespondC.php';
$author = $_GET['author'];
$message = $_GET['message'];
$time = $_GET['currentTime'];
$id = $_GET['id'];
$id_Com = $_GET['id_com'];

$rc = new RespondC();
$r = new Respond($id_Com, $author, $message, $time);
$rc->modifyRespond($r,$id);
header('Location: afficher.php');
?>
