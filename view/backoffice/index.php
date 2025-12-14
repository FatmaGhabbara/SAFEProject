<?php
// BACKOFFICE - Dashboard avec intégration des signalements
// Détection automatique du chemin vers la racine
$rootPath = dirname(dirname(__DIR__));
$configPath = $rootPath . DIRECTORY_SEPARATOR . 'config.php';

// Si config.php n'est pas trouvé, essayer un niveau au-dessus
if (!file_exists($configPath)) {
    $rootPath = dirname($rootPath);
    $configPath = $rootPath . DIRECTORY_SEPARATOR . 'config.php';
}

require_once $configPath;

// Chemins vers model et controller (utiliser $rootPath déjà calculé)
$modelPath = $rootPath . DIRECTORY_SEPARATOR . 'model';
$controllerPath = $rootPath . DIRECTORY_SEPARATOR . 'controller';

// Si model n'existe pas à cet endroit, essayer un niveau au-dessus
if (!is_dir($modelPath)) {
    $rootPath = dirname($rootPath);
    $modelPath = $rootPath . DIRECTORY_SEPARATOR . 'model';
    $controllerPath = $rootPath . DIRECTORY_SEPARATOR . 'controller';
}

require_once $modelPath . DIRECTORY_SEPARATOR . 'Signalement.php';
require_once $modelPath . DIRECTORY_SEPARATOR . 'Type.php';
require_once $controllerPath . DIRECTORY_SEPARATOR . 'TypeController.php';
require_once $controllerPath . DIRECTORY_SEPARATOR . 'SignalementController.php';

// Utiliser la connexion depuis config.php
if (!isset($db) || !$db) {
    $database = new Database();
    $db = $database->getConnection();
}

// Détecter si la requête est AJAX (XHR)
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

$signalementController = new SignalementController($db);
$typeController = new TypeController($db);

$message = '';

// AJOUTER UN TYPE
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'add_type') {
    if (!empty($_POST['nom'])) {
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $result = $typeController->createType($_POST['nom'], $description);
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $message = $result['message'];
        }
    }
}

// EDITER UN TYPE
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'edit_type') {
    if (!empty($_POST['id']) && !empty($_POST['nom'])) {
        $id = intval($_POST['id']);
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $result = $typeController->updateType($id, $_POST['nom'], $description);
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        } else {
            if ($result['success']) {
                $message = $result['message'];
            } else {
                $message = $result['message'];
            }
        }
    } else {
        $message = '❌ Les champs sont requis pour modifier un type.';
    }
}

// SUPPRIMER UN TYPE (avec suppression en cascade des signalements associés)
if (isset($_GET['delete_type'])) {
    $type_id = $_GET['delete_type'];
    
    try {
        // Démarrer une transaction
        $db->beginTransaction();
        
        // Compter les signalements qui seront supprimés
        $countQuery = "SELECT COUNT(*) as count FROM signalements WHERE type_id = :id";
        $countStmt = $db->prepare($countQuery);
        $countStmt->bindParam(':id', $type_id);
        $countStmt->execute();
        $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
        $signalementsCount = $countResult['count'];
        
        // Supprimer tous les signalements associés à ce type
        $deleteSignalementsQuery = "DELETE FROM signalements WHERE type_id = :id";
        $deleteSignalementsStmt = $db->prepare($deleteSignalementsQuery);
        $deleteSignalementsStmt->bindParam(':id', $type_id);
        $deleteSignalementsStmt->execute();
        
        // Supprimer le type
        $deleteTypeQuery = "DELETE FROM types WHERE id = :id";
        $deleteTypeStmt = $db->prepare($deleteTypeQuery);
        $deleteTypeStmt->bindParam(':id', $type_id);
        $deleteTypeStmt->execute();
        
        // Valider la transaction
        $db->commit();
        
        // Message de succès avec information sur les signalements supprimés
        if ($signalementsCount > 0) {
            $message = "✅ Type supprimé avec succès ! " . $signalementsCount . " signalement(s) associé(s) ont également été supprimé(s).";
    } else {
            $message = "✅ Type supprimé avec succès !";
        }
    } catch (Exception $e) {
        // En cas d'erreur, annuler la transaction
        $db->rollBack();
        $message = "❌ Erreur lors de la suppression : " . $e->getMessage();
    }
}

// Chargement initial
$signalements = $signalementController->getAllSignalements();
$types = $typeController->getAllTypes();
$totalSignalements = count($signalements);
$totalTypes = count($types);
?>
<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Signalements - Dashboard</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <style>
        /* Variables CSS modernes */
        :root {
            --primary: #098d8a;
            --blue: #098d8a;
            --primary-gradient: linear-gradient(135deg, #098d8a 0%, #00bfa5 100%);
            --success-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.15);
            --shadow-xl: 0 20px 40px rgba(0,0,0,0.2);
        }

        /* Animations globales */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

        body {
            animation: fadeIn 0.5s ease-in;
        }

        /* Cards statistiques modernes */
        .stat-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none !important;
            border-radius: 15px !important;
            overflow: hidden;
            position: relative;
            background: white;
            box-shadow: var(--shadow-md);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--shadow-xl);
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-card .card-body {
            padding: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .stat-card .border-left-primary {
            border-left: 4px solid var(--primary) !important;
        }

        .stat-card .border-left-primary::before {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-card .border-left-success {
            border-left: 4px solid #1cc88a !important;
        }

        .stat-card .border-left-success::before {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .stat-card .border-left-info {
            border-left: 4px solid #36b9cc !important;
        }

        .stat-card .border-left-info::before {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-card .border-left-warning {
            border-left: 4px solid #f6c23e !important;
        }

        .stat-card .border-left-warning::before {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .stat-card i {
            transition: all 0.3s ease;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        .stat-card:hover i {
            transform: scale(1.1) rotate(5deg);
        }

        /* Cards principales */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        /* Force cards to have the same min-height for consistent layout */
        .full-width-card {
            min-height: 420px;
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            border-radius: 15px 15px 0 0 !important;
        }

        .card-header h6 {
            margin: 0;
            font-size: 1rem;
            letter-spacing: 0.5px;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Tableau moderne */
        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .table thead th {
            border: none;
            padding: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
        }

        .table tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid #f0f0f0;
        }

        .table tbody tr:hover {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            transform: scale(1.01);
            box-shadow: var(--shadow-sm);
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
        }

        /* Badges modernes */
        .badge-type {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: var(--shadow-sm);
            transition: all 0.2s ease;
        }

        .badge-type:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-md);
        }

        .admin-badge {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.7em;
            font-weight: 700;
            margin-left: 8px;
            box-shadow: var(--shadow-sm);
            animation: pulse 2s infinite;
        }

        /* Filtres modernes */
        .filter-section {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: var(--shadow-sm);
            animation: slideIn 0.5s ease-out;
        }

        .filter-section label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .filter-section .form-control {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        .filter-section .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        /* Boutons modernes */
        .btn {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            box-shadow: var(--shadow-sm);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .btn-danger {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .btn-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        /* Graphiques */
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: var(--shadow-sm);
        }

        /* Type items */
        .type-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 8px;
            background: #f8f9fc;
            border-radius: 10px;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .type-item:hover {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-left-color: #667eea;
            transform: translateX(5px);
        }

        /* Alertes modernes */
        .alert {
            border: none;
            border-radius: 12px;
            padding: 15px 20px;
            box-shadow: var(--shadow-sm);
            animation: slideIn 0.3s ease-out;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(17, 153, 142, 0.1) 0%, rgba(56, 239, 125, 0.1) 100%);
            color: #11998e;
            border-left: 4px solid #11998e;
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(240, 147, 251, 0.1) 0%, rgba(245, 87, 108, 0.1) 100%);
            color: #f5576c;
            border-left: 4px solid #f5576c;
        }

        /* Page heading */
        .page-heading {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
        }

        .page-heading h1 {
            margin: 0;
            font-weight: 700;
            font-size: 2rem;
        }

        /* DataTables personnalisé */
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 8px 15px;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 5px 10px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 8px;
            margin: 0 2px;
            transition: all 0.2s ease;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            transform: translateY(-2px);
        }

        /* Loading animation */
        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stat-card {
                margin-bottom: 1rem;
            }
            
            .filter-section {
                padding: 15px;
            }
        }

        /* Scrollbar personnalisée */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
    </style>

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="sidebar-brand-text mx-3">SAFE Admin</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Nav Item - Signalements Admin -->
            <li class="nav-item">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-exclamation-triangle"></i>
                    <span>Admin Signalements</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Interface
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                    aria-expanded="true" aria-controls="collapseTwo">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Components</span>
                </a>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Custom Components:</h6>
                        <a class="collapse-item" href="buttons.html">Buttons</a>
                        <a class="collapse-item" href="cards.html">Cards</a>
                    </div>
                </div>
            </li>

            <!-- Nav Item - Utilities Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
                    aria-expanded="true" aria-controls="collapseUtilities">
                    <i class="fas fa-fw fa-wrench"></i>
                    <span>Utilities</span>
                </a>
                <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Custom Utilities:</h6>
                        <a class="collapse-item" href="utilities-color.html">Colors</a>
                        <a class="collapse-item" href="utilities-border.html">Borders</a>
                        <a class="collapse-item" href="utilities-animation.html">Animations</a>
                        <a class="collapse-item" href="utilities-other.html">Other</a>
                    </div>
                </div>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Addons
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages"
                    aria-expanded="true" aria-controls="collapsePages">
                    <i class="fas fa-fw fa-folder"></i>
                    <span>Pages</span>
                </a>
                <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Login Screens:</h6>
                        <a class="collapse-item" href="login.html">Login</a>
                        <a class="collapse-item" href="register.html">Register</a>
                        <a class="collapse-item" href="forgot-password.html">Forgot Password</a>
                        <div class="collapse-divider"></div>
                        <h6 class="collapse-header">Other Pages:</h6>
                        <a class="collapse-item" href="404.html">404 Page</a>
                        <a class="collapse-item" href="blank.html">Blank Page</a>
                    </div>
                </div>
            </li>

            <!-- Nav Item - Charts -->
            <li class="nav-item">
                <a class="nav-link" href="charts.html">
                    <i class="fas fa-fw fa-chart-area"></i>
                    <span>Charts</span></a>
            </li>

            <!-- Nav Item - Tables -->
            <li class="nav-item">
                <a class="nav-link" href="tables.html">
                    <i class="fas fa-fw fa-table"></i>
                    <span>Tables</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

            <!-- Sidebar Message -->
            <div class="sidebar-card d-none d-lg-flex">
                <img class="sidebar-card-illustration mb-2" src="img/undraw_rocket.svg" alt="...">
                <p class="text-center mb-2"><strong>SB Admin Pro</strong> is packed with premium features, components, and more!</p>
                <a class="btn btn-success btn-sm" href="https://startbootstrap.com/theme/sb-admin-pro">Upgrade to Pro!</a>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Search -->
                    <form
                        class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..."
                                aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Content Row - Sections principales -->
                            <div class="row">

                                <!-- Section Signalements avec DataTables -->
                                <div class="col-lg-8 mb-4">
                            </div>
                            <div class="text-right">
                                <i class="fas fa-shield-alt fa-3x" style="opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Message de succès/erreur -->
                    <?php if ($message): ?>
                        <div class="alert alert-<?= strpos($message, '✅') !== false ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Content Row - Statistiques -->
                    <div class="row">

                        <!-- Total Signalements Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-2" style="letter-spacing: 1px; font-size: 0.7rem;">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>Total Signalements</div>
                                            <div class="h2 mb-0 font-weight-bold text-gray-800" style="font-size: 2.5rem;"><?= $totalSignalements ?></div>
                                            <div class="text-xs text-muted mt-1">
                                                <i class="fas fa-arrow-up text-success"></i> Tous les signalements
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); border-radius: 15px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-exclamation-triangle fa-2x" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Types Disponibles Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-2" style="letter-spacing: 1px; font-size: 0.7rem;">
                                                <i class="fas fa-tags mr-1"></i>Types Disponibles</div>
                                            <div class="h2 mb-0 font-weight-bold text-gray-800" style="font-size: 2.5rem;"><?= $totalTypes ?></div>
                                            <div class="text-xs text-muted mt-1">
                                                <i class="fas fa-check-circle text-success"></i> Catégories actives
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, rgba(17, 153, 142, 0.1) 0%, rgba(56, 239, 125, 0.1) 100%); border-radius: 15px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-tags fa-2x" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Signalements Aujourd'hui Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-2" style="letter-spacing: 1px; font-size: 0.7rem;">
                                                <i class="fas fa-calendar-day mr-1"></i>Aujourd'hui</div>
                                            <div class="h2 mb-0 font-weight-bold text-gray-800" id="statToday" style="font-size: 2.5rem;">0</div>
                                            <div class="text-xs text-muted mt-1">
                                                <i class="fas fa-clock text-info"></i> Dernières 24h
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, rgba(79, 172, 254, 0.1) 0%, rgba(0, 242, 254, 0.1) 100%); border-radius: 15px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-calendar-day fa-2x" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cette Semaine Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-2" style="letter-spacing: 1px; font-size: 0.7rem;">
                                                <i class="fas fa-calendar-week mr-1"></i>Cette Semaine</div>
                                            <div class="h2 mb-0 font-weight-bold text-gray-800" id="statWeek" style="font-size: 2.5rem;">0</div>
                                            <div class="text-xs text-muted mt-1">
                                                <i class="fas fa-chart-line text-warning"></i> 7 derniers jours
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, rgba(250, 112, 154, 0.1) 0%, rgba(254, 225, 64, 0.1) 100%); border-radius: 15px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-calendar-week fa-2x" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    

                    <!-- Content Row - Graphiques -->
                    <div class="row">
                        <!-- Graphique Pie Chart - Par Type -->
                        <div class="col-xl-6 col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-white">
                                        <i class="fas fa-chart-pie mr-2"></i>Répartition par Type
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="pieChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Graphique Bar Chart - Par Date -->
                        <div class="col-xl-6 col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-white">
                                        <i class="fas fa-chart-bar mr-2"></i>Évolution (7 derniers jours)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="barChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row - Sections principales -->
                    <div class="row">
                        <!-- Section Signalements avec DataTables -->
                        <div class="col-12 mb-4">
                            <div class="card shadow mb-4 full-width-card">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-white">
                                        <i class="fas fa-list-alt mr-2"></i>Signalements <span class="admin-badge">ADMIN</span>
                                    </h6>
                                    <button class="btn btn-sm btn-light" onclick="exportTable()" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3);">
                                        <i class="fas fa-download mr-1"></i> Exporter
                                    </button>
                                </div>
                                <div class="card-body">
                                    <!-- Filtres -->
                                    <div class="filter-section">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label><strong>Filtrer par Type :</strong></label>
                                                <select id="filterType" class="form-control form-control-sm">
                                                    <option value="">Tous les types</option>
                                                    <?php foreach ($types as $type): ?>
                                                        <option value="<?= htmlspecialchars($type['nom']) ?>"><?= htmlspecialchars($type['nom']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                    </div>
                                            <div class="col-md-4">
                                                <label><strong>Filtrer par Date :</strong></label>
                                                <select id="filterDate" class="form-control form-control-sm">
                                                    <option value="">Toutes les dates</option>
                                                    <option value="today">Aujourd'hui</option>
                                                    <option value="week">Cette semaine</option>
                                                    <option value="month">Ce mois</option>
                                                </select>
                                    </div>
                                            <div class="col-md-4">
                                                <label><strong>Recherche :</strong></label>
                                                <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Rechercher...">
                                                    </div>
                                                </div>
                                    </div>

                                    <!-- Tableau DataTables -->
                                    <div class="table-responsive">
                                        <table id="signalementsTable" class="table table-bordered table-hover" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Titre</th>
                                                    <th>Type</th>
                                                    <th>Description</th>
                                                    <th>Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($signalements as $signalement): ?>
                                                    <tr>
                                                        <td><?= $signalement['id'] ?></td>
                                                        <td><?= htmlspecialchars($signalement['titre']) ?></td>
                                                        <td>
                                                            <span class="badge badge-type badge-primary">
                                                                <?= htmlspecialchars($signalement['type_nom'] ?? 'Non spécifié') ?>
                                                            </span>
                                                        </td>
                                                        <td><?= htmlspecialchars(mb_substr($signalement['description'], 0, 50)) ?><?= mb_strlen($signalement['description']) > 50 ? '...' : '' ?></td>
                                                        <td><?= date('d/m/Y H:i', strtotime($signalement['created_at'])) ?></td>
                                                        <td>
                                                            <a href="signalements/detail_signalement.php?id=<?= $signalement['id'] ?>" 
                                                               class="btn btn-primary btn-sm" title="Voir détails">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="signalements/supprimer_signalement.php?id=<?= $signalement['id'] ?>" 
                                                               class="btn btn-danger btn-sm" 
                                                               title="Supprimer"
                                                               onclick="return confirm('Supprimer ce signalement ?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
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

                    <!-- Section Gestion des Types (moved below Signalements) -->
                    <div class="row mt-4">
                        <div class="col-12 mb-4">
                            <div class="card shadow mb-4 full-width-card">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-white">
                                        <i class="fas fa-tags mr-2"></i>Gestion des Types
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" class="mb-3">
                                        <input type="hidden" name="action" value="add_type">
                                        <div class="form-row align-items-center">
                                                <div class="col-md-6 mb-2">
                                                    <input type="text" name="nom" class="form-control" 
                                                           placeholder="Ex: Problème technique, Bug, Suggestion..." 
                                                           required
                                                           style="border-radius: 10px; border: 2px solid #e2e8f0; padding: 12px 15px; transition: all 0.3s ease;">
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <input type="text" name="description" class="form-control" 
                                                           placeholder="Description (facultatif) - ex: Problème lié au réseau..." 
                                                           style="border-radius: 10px; border: 2px solid #e2e8f0; padding: 12px 15px; transition: all 0.3s ease;">
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <button type="submit" class="btn btn-success btn-block" style="border-radius: 10px; padding: 10px 20px; font-weight: 600;">
                                                        <i class="fas fa-plus mr-1"></i> Ajouter Type
                                                    </button>
                                                </div>
                                        </div>
                                    </form>

                                    <h6 class="font-weight-bold">Types existants :</h6>
                                    <div class="type-list" style="max-height: 300px; overflow-y: auto;">
                                        <?php if (empty($types)): ?>
                                            <p class="text-muted font-italic">Aucun type créé.</p>
                                        <?php else: ?>
                                            <?php foreach ($types as $type): ?>
                                                <div class="type-item d-flex justify-content-between align-items-center py-2 border-bottom" data-id="<?= $type['id'] ?>">
                                                    <div>
                                                        <strong><?= htmlspecialchars($type['nom']) ?></strong>
                                                        <small class="text-muted d-block">ID: <?= $type['id'] ?></small>
                                                        <?php if (!empty($type['description'])): ?>
                                                            <p class="small text-muted mb-0"><?= htmlspecialchars(mb_substr($type['description'], 0, 150)) ?><?= mb_strlen($type['description']) > 150 ? '...' : '' ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <button type="button" class="btn btn-secondary btn-sm edit-type-btn" 
                                                                data-id="<?= $type['id'] ?>" 
                                                                data-nom="<?= htmlspecialchars($type['nom'], ENT_QUOTES) ?>" 
                                                                data-description="<?= htmlspecialchars($type['description'] ?? '', ENT_QUOTES) ?>"
                                                                title="Modifier">
                                                            ✏️
                                                        </button>
                                                        <a href="?delete_type=<?= $type['id'] ?>" class="btn btn-danger btn-sm" 
                                                           onclick="return confirm('Supprimer le type "<?= htmlspecialchars($type['nom']) ?>" ?')">
                                                            🗑️
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; SAFEProject 2024</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="login.html">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Type Modal -->
    <div class="modal fade" id="editTypeModal" tabindex="-1" role="dialog" aria-labelledby="editTypeLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTypeLabel">Modifier le Type</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_type">
                    <input type="hidden" name="id" id="edit-type-id">
                    <div class="form-group">
                        <label for="edit-type-nom">Nom</label>
                        <input type="text" class="form-control" name="nom" id="edit-type-nom" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-type-description">Description</label>
                        <textarea class="form-control" name="description" id="edit-type-description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Annuler</button>
                    <button class="btn btn-primary" type="submit">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- DataTables JavaScript -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Scripts pour le dashboard amélioré -->
    <script>
    // Initialiser DataTables
    let table;
    $(document).ready(function() {
        table = $('#signalementsTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json"
            },
            "order": [[4, "desc"]], // Trier par date décroissante
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Tous"]],
            "columnDefs": [
                { "orderable": true, "targets": [0, 1, 2, 4] },
                { "orderable": false, "targets": [3, 5] }
            ]
        });

        // Filtre par type
        $('#filterType').on('change', function() {
            table.column(2).search(this.value).draw();
        });

        // Filtre par date
        $('#filterDate').on('change', function() {
            const filter = this.value;
            if (filter === '') {
                table.column(4).search('').draw();
            } else {
                const today = new Date();
                let dateFilter = '';
                
                if (filter === 'today') {
                    dateFilter = today.toISOString().split('T')[0];
                } else if (filter === 'week') {
                    const weekAgo = new Date(today);
                    weekAgo.setDate(today.getDate() - 7);
                    // Utiliser une recherche personnalisée
                    $.fn.dataTable.ext.search.push(
                        function(settings, data, dataIndex) {
                            const dateStr = data[4];
                            const date = new Date(dateStr.split('/').reverse().join('-'));
                            return date >= weekAgo;
                        }
                    );
                } else if (filter === 'month') {
                    const monthAgo = new Date(today);
                    monthAgo.setMonth(today.getMonth() - 1);
                    $.fn.dataTable.ext.search.push(
                        function(settings, data, dataIndex) {
                            const dateStr = data[4];
                            const date = new Date(dateStr.split('/').reverse().join('-'));
                            return date >= monthAgo;
                        }
                    );
                }
                table.draw();
            }
        });

        // Recherche dans DataTables
        $('#searchInput').on('keyup', function() {
            table.search(this.value).draw();
        });

        // Charger les statistiques
        loadStatistics();
        loadCharts();

        // Focus styles for the input nom and description (Ajouter Type)
        var $typeInput = $('input[name="nom"], input[name="description"]');
        if ($typeInput.length) {
            $typeInput.on('focus', function() {
                $(this).css({ 'border-color': '#667eea', 'box-shadow': '0 0 0 3px rgba(102, 126, 234, 0.1)' });
            }).on('blur', function() {
                $(this).css({ 'border-color': '#e2e8f0', 'box-shadow': 'none' });
            });
        }

        // Open Edit Type modal and populate
        $(document).on('click', '.edit-type-btn', function() {
            var id = $(this).data('id');
            var nom = $(this).data('nom');
            var description = $(this).data('description');
            $('#edit-type-id').val(id);
            $('#edit-type-nom').val(nom);
            $('#edit-type-description').val(description);
            $('#editTypeModal').modal('show');
        });

        // AJAX submit for Edit Type modal
        $('#editTypeModal form').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var formData = $form.serialize();
            $.ajax({
                url: window.location.href,
                method: 'POST',
                data: formData,
                dataType: 'json'
            }).done(function(response) {
                if (response.success) {
                    var id = $('#edit-type-id').val();
                    var nom = $('#edit-type-nom').val();
                    var description = $('#edit-type-description').val();

                    // Update DOM in backoffice
                    var $item = $('.type-item[data-id="' + id + '"]');
                    $item.find('strong').text(nom);
                    $item.find('.small.text-muted').first().text('ID: ' + id);
                    if (description && description.trim() !== '') {
                        var $p = $item.find('p.type-desc-preview');
                        if ($p.length === 0) {
                            $item.find('div').first().append('<p class="small text-muted mb-0 type-desc-preview"></p>');
                            $p = $item.find('p.type-desc-preview');
                        }
                        $p.text(description.length > 150 ? description.substring(0,150) + '...' : description);
                    } else {
                        $item.find('p.type-desc-preview').remove();
                    }

                    // Update edit button data attributes
                    $item.find('.edit-type-btn').data('nom', nom).data('description', description);

                    // Broadcast update to other tabs (frontoffice)
                    var payload = { id: id, nom: nom, description: description };
                    if (window.BroadcastChannel) {
                        var bc = new BroadcastChannel('safeSpace-types');
                        bc.postMessage({ type: 'updated', data: payload });
                        bc.close();
                    } else {
                        localStorage.setItem('safeSpace-types', JSON.stringify({ type: 'updated', data: payload, ts: Date.now() }));
                        setTimeout(function() { localStorage.removeItem('safeSpace-types'); }, 1000);
                    }

                    $('#editTypeModal').modal('hide');
                    // Optionally show a small toast or alert
                    // For now, reload the page to update server-rendered content and messages
                    location.reload();
                } else {
                    alert(response.message || 'Erreur lors de la mise à jour');
                }
            }).fail(function() {
                alert('Erreur réseau lors de la mise à jour du type');
            });
        });
    });

    // Charger les statistiques
    function loadStatistics() {
        fetch('signalements/stats_api.php?action=all')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('statToday').textContent = data.data.today;
                    document.getElementById('statWeek').textContent = data.data.thisWeek;
                }
            })
            .catch(error => console.error('Erreur:', error));
    }

    // Charger les graphiques
    function loadCharts() {
        // Pie Chart - Par Type
        fetch('signalements/stats_api.php?action=by_type')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    const ctx = document.getElementById('pieChart').getContext('2d');
                    const labels = data.data.map(item => item.type);
                    const values = data.data.map(item => item.count);
                    const colors = generateColors(data.data.length);
                    
                    new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: values,
                                backgroundColor: colors,
                                borderWidth: 2,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = context.parsed || 0;
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = ((value / total) * 100).toFixed(1);
                                            return label + ': ' + value + ' (' + percentage + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            })
            .catch(error => console.error('Erreur:', error));

        // Bar Chart - Par Date
        fetch('signalements/stats_api.php?action=by_date')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const ctx = document.getElementById('barChart').getContext('2d');
                    const labels = data.data.map(item => {
                        const date = new Date(item.date + 'T00:00:00');
                        return date.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short' });
                    });
                    const values = data.data.map(item => item.count);
                    
                    // Vérifier s'il y a des données
                    const hasData = values.some(v => v > 0);
                    
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Signalements',
                                data: values,
                                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return 'Signalements: ' + context.parsed.y;
                                        }
                                    }
                                }
                            }
                        }
                    });
                    
                    // Afficher un message si pas de données
                    if (!hasData) {
                        const chartContainer = document.querySelector('#barChart').parentElement;
                        const noDataMsg = document.createElement('div');
                        noDataMsg.className = 'text-center text-muted mt-3';
                        noDataMsg.innerHTML = '<i class="fas fa-info-circle"></i> Aucun signalement sur les 7 derniers jours';
                        chartContainer.appendChild(noDataMsg);
                    }
                } else {
                    console.error('Erreur API:', data.error || 'Erreur inconnue');
                    const chartContainer = document.querySelector('#barChart').parentElement;
                    chartContainer.innerHTML = '<div class="text-center text-danger mt-3"><i class="fas fa-exclamation-triangle"></i> Erreur lors du chargement des données</div>';
                }
            })
            .catch(error => {
                console.error('Erreur fetch:', error);
                const chartContainer = document.querySelector('#barChart').parentElement;
                chartContainer.innerHTML = '<div class="text-center text-danger mt-3"><i class="fas fa-exclamation-triangle"></i> Erreur de connexion</div>';
            });
    }

    // Générer des couleurs pour le pie chart
    function generateColors(count) {
        const colors = [
            'rgba(54, 162, 235, 0.8)',
            'rgba(255, 99, 132, 0.8)',
            'rgba(75, 192, 192, 0.8)',
            'rgba(255, 206, 86, 0.8)',
            'rgba(153, 102, 255, 0.8)',
            'rgba(255, 159, 64, 0.8)',
            'rgba(199, 199, 199, 0.8)',
            'rgba(83, 102, 255, 0.8)',
            'rgba(255, 99, 255, 0.8)',
            'rgba(99, 255, 132, 0.8)'
        ];
        return colors.slice(0, count);
    }

    // Exporter le tableau
    function exportTable() {
        const table = document.getElementById('signalementsTable');
        let csv = [];
        const rows = table.querySelectorAll('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = [], cols = rows[i].querySelectorAll('td, th');
            
            for (let j = 0; j < cols.length - 1; j++) { // Exclure la colonne Actions
                row.push(cols[j].innerText);
            }
            
            csv.push(row.join(','));
        }
        
        // Télécharger le fichier CSV
        const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
        const downloadLink = document.createElement('a');
        downloadLink.download = 'signalements_' + new Date().toISOString().split('T')[0] + '.csv';
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = 'none';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }
    </script>

</body>

</html>
