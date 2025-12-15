<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../frontoffice/login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'];
$userName = $_SESSION['fullname'] ?? 'Utilisateur';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Calendrier - SafeSpace</title>
    
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        .calendar-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 2rem;
        }
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e3e6f0;
        }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }
        .calendar-day-header {
            text-align: center;
            font-weight: bold;
            padding: 10px;
            background: #4e73df;
            color: white;
            border-radius: 5px;
        }
        .calendar-day {
            text-align: center;
            padding: 15px;
            border: 1px solid #e3e6f0;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            min-height: 80px;
        }
        .calendar-day:hover {
            background: #f8f9fc;
            border-color: #4e73df;
            transform: translateY(-2px);
        }
        .calendar-day.today {
            background: #4e73df;
            color: white;
            font-weight: bold;
        }
        .calendar-day.has-event {
            background: #d1ecf1;
            border-color: #17a2b8;
        }
        .event-indicator {
            width: 8px;
            height: 8px;
            background: #28a745;
            border-radius: 50%;
            display: inline-block;
            margin-top: 5px;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        
        <!-- Sidebar -->
        <?php if ($userRole === 'admin'): ?>
            <?php include 'includes/admin_sidebar.php'; ?>
        <?php else: ?>
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= $userRole === 'conseilleur' ? 'adviser_dashboard.php' : 'member_dashboard.php' ?>">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="sidebar-brand-text mx-3">SafeSpace</div>
            </a>

            <hr class="sidebar-divider my-0">
            
            <li class="nav-item">
                <a class="nav-link" href="<?= $userRole === 'conseilleur' ? 'adviser_dashboard.php' : 'member_dashboard.php' ?>">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Interface</div>
            
            <li class="nav-item">
                <a class="nav-link" href="edit_profile.php">
                    <i class="fas fa-fw fa-user-edit"></i>
                    <span>Modifier mon profil</span>
                </a>
            </li>
            
            <?php if ($userRole === 'membre'): ?>
            <li class="nav-item">
                <a class="nav-link" href="my_support_requests.php">
                    <i class="fas fa-fw fa-headset"></i>
                    <span>Mes Demandes de Support</span>
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item">
                <a class="nav-link" href="my_consultations.php">
                    <i class="fas fa-fw fa-comments"></i>
                    <span>Mes Consultations</span>
                </a>
            </li>
            
            <?php if ($userRole === 'conseilleur'): ?>
            <li class="nav-item">
                <a class="nav-link" href="counselor_requests.php">
                    <i class="fas fa-fw fa-tasks"></i>
                    <span>Mes Demandes Assignées</span>
                </a>
            </li>
            <?php endif; ?>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Navigation</div>
            
            <li class="nav-item">
                <a class="nav-link" href="../frontoffice/index.php">
                    <i class="fas fa-fw fa-globe"></i>
                    <span>Site Public</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="../frontoffice/logout.php">
                    <i class="fas fa-fw fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </li>

            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <?php endif; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= htmlspecialchars($userName) ?></span>
                                <i class="fas fa-user fa-fw"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                                <a class="dropdown-item" href="edit_profile.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profil
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../frontoffice/logout.php">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Déconnexion
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>

                <!-- Page Content -->
                <div class="container-fluid">
                    
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-calendar-alt text-primary"></i> Calendrier
                        </h1>
                        <div>
                            <button class="btn btn-sm btn-primary" onclick="alert('Fonctionnalité à venir')">
                                <i class="fas fa-plus"></i> Nouvel Événement
                            </button>
                        </div>
                    </div>

                    <!-- Info Alert -->
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle"></i>
                        <strong>Fonctionnalité en développement</strong> - Le calendrier interactif sera bientôt disponible pour gérer vos rendez-vous et événements.
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>

                    <!-- Calendar -->
                    <div class="calendar-container">
                        <div class="calendar-header">
                            <button class="btn btn-outline-primary" onclick="alert('Mois précédent')">
                                <i class="fas fa-chevron-left"></i> Précédent
                            </button>
                            <h3 class="mb-0">
                                <?php 
                                setlocale(LC_TIME, 'fr_FR.UTF-8', 'fra');
                                echo strftime('%B %Y', time()); 
                                ?>
                            </h3>
                            <button class="btn btn-outline-primary" onclick="alert('Mois suivant')">
                                Suivant <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>

                        <div class="calendar-grid">
                            <!-- Day Headers -->
                            <div class="calendar-day-header">Lun</div>
                            <div class="calendar-day-header">Mar</div>
                            <div class="calendar-day-header">Mer</div>
                            <div class="calendar-day-header">Jeu</div>
                            <div class="calendar-day-header">Ven</div>
                            <div class="calendar-day-header">Sam</div>
                            <div class="calendar-day-header">Dim</div>

                            <!-- Calendar Days (Example for current month) -->
                            <?php
                            $today = date('j');
                            $daysInMonth = date('t');
                            $firstDayOfMonth = date('N', strtotime(date('Y-m-01')));
                            
                            // Empty cells before first day
                            for ($i = 1; $i < $firstDayOfMonth; $i++) {
                                echo '<div class="calendar-day" style="opacity: 0.3;"></div>';
                            }
                            
                            // Days of the month
                            for ($day = 1; $day <= $daysInMonth; $day++) {
                                $isToday = ($day == $today) ? 'today' : '';
                                echo "<div class='calendar-day $isToday' onclick='alert(\"Jour $day sélectionné\")'>";
                                echo "<div>$day</div>";
                                // Example: Add event indicator on some days
                                if ($day % 7 == 0) {
                                    echo '<div class="event-indicator"></div>';
                                }
                                echo "</div>";
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Upcoming Events -->
                    <div class="card shadow mt-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-clock"></i> Événements à Venir
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-check fa-3x text-gray-300 mb-3"></i>
                                <p class="text-muted">Aucun événement planifié</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; SafeSpace <?= date('Y') ?></span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="assets/js/sb-admin-2.min.js"></script>
</body>
</html>
