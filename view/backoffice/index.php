<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/AdminController.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../frontoffice/login.php');
    exit();
}

$admin = new AdminController();
$stats = $admin->getStats();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Dashboard</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

<h2>Dashboard Admin</h2>
<p>Nombre d'utilisateurs : <?= $stats['users'] ?></p>

<nav>
    <a href="user_list.php">Gérer utilisateurs</a> |
    <a href="../frontoffice/index.php">Retour au site</a> |
    <a href="../frontoffice/logout.php">Déconnexion</a>
</nav>

</body>
</html>
