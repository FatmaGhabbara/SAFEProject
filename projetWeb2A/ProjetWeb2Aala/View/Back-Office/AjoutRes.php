<?php

include '../../Controller/RespondC.php';
$id_Com = $_GET['id_com'];
$author = $_GET['author'];
$message = $_GET['message'];  
$time = $_GET['currentTime'];
$pc = new RespondC();
$p = new Respond($id_Com, $author, $message,$time);
$pc-> addRespond($p);
header('Location: afficher.php');

?>