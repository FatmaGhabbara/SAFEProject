<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - SAFEProject Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/support-module.css">
    <link rel="stylesheet" href="../css/sb-admin-2.min.css">
</head>
<body id="page-top">

<?php
session_start();

// MODE TEST : FORCER la session administrateur
$_SESSION['user_id'] = 1;  // Admin
$_SESSION['role'] = 'admin';
$_SESSION['user_name'] = 'Administrateur';

require_once '../../../config.php';
require_once '../../../controller/helpers.php';

// Récupérer les statistiques
$globalStats = getGlobalStats();
$topCounselors = getTopCounselors(5);
$monthlyStats = getMonthlyStats(6);
$averageResponseTime = getAverageResponseTime();

// Récupérer tous les conseillers avec leurs stats
$counselors = getAllCounselors();
$counselorStats = [];
foreach ($counselors as $counselorUser) {
    $stats = getCounselorStats($counselorUser->getId());
    $counselorStats[] = [
        'id' => $counselorUser->getId(),
        'nom' => $counselorUser->getNom(),
        'prenom' => $counselorUser->getPrenom(),
        'specialite' => $counselorUser->getSpecialite(),
        'statut' => $counselorUser->getStatutCounselor(),
        'total_demandes' => $stats['total_demandes'] ?? 0,
        'demandes_terminees' => $stats['demandes_terminees'] ?? 0,
        'demandes_actives' => $stats['demandes_actives'] ?? 0,
        'temps_resolution_moyen' => $stats['temps_resolution_moyen'] ?? null
    ];
}
?>

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="support_requests.php">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="sidebar-brand-text mx-3">SAFEProject</div>
            </a>

            <hr class="sidebar-divider my-0">


            <hr class="sidebar-divider">

            <div class="sidebar-heading">Support Psychologique</div>

            <li class="nav-item">
                <a class="nav-link" href="support_requests.php">
                    <i class="fas fa-fw fa-inbox"></i>
                    <span>Demandes de Support</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="counselors_list.php">
                    <i class="fas fa-fw fa-user-md"></i>
                    <span>Conseillers</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="users_list.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Utilisateurs</span>
                </a>
            </li>

            <li class="nav-item active">
                <a class="nav-link" href="counselor_stats.php">
                    <i class="fas fa-fw fa-chart-area"></i>
                    <span>Statistiques</span>
                </a>
            </li>

            <hr class="sidebar-divider d-none d-md-block">
        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <h1 class="h3 mb-0 text-gray-800">Statistiques du Module Support</h1>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="../../frontoffice/index.html">
                                <i class="fas fa-home"></i>
                                <span class="d-none d-lg-inline">Site Public</span>
                            </a>
                        </li>
                        <div class="topbar-divider d-none d-sm-block"></div>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-user"></i>
                                <span class="d-none d-lg-inline">Admin</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Statistiques globales -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card stat-primary">
                                <div class="text-center">
                                    <p class="stat-label">Total Demandes</p>
                                    <p class="stat-value"><?php echo $globalStats['total_demandes']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card stat-success">
                                <div class="text-center">
                                    <p class="stat-label">Terminées</p>
                                    <p class="stat-value"><?php echo $globalStats['demandes_terminees']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card stat-warning">
                                <div class="text-center">
                                    <p class="stat-label">En Attente</p>
                                    <p class="stat-value"><?php echo $globalStats['demandes_en_attente']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card stat-info">
                                <div class="text-center">
                                    <p class="stat-label">Temps Moyen</p>
                                    <p class="stat-value"><?php echo number_format($averageResponseTime, 1); ?>h</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Graphiques -->
                    <div class="row mb-4">
                        
                        <!-- Graphique évolution mensuelle -->
                        <div class="col-xl-8 mb-4">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-chart-line me-2"></i>
                                        Évolution des demandes (6 derniers mois)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="monthlyChart"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Répartition par statut -->
                        <div class="col-xl-4 mb-4">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-chart-pie me-2"></i>
                                        Répartition par statut
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="statusChart"></canvas>
                                </div>
                            </div>
                        </div>
                        
                    </div>

                    <!-- Meilleurs conseillers -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-trophy me-2"></i>
                                        Top 5 Conseillers (Demandes terminées)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="30%">Conseiller</th>
                                                    <th width="25%">Spécialité</th>
                                                    <th width="15%">Total demandes</th>
                                                    <th width="15%">Terminées</th>
                                                    <th width="10%">Temps moyen (h)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $rank = 1; ?>
                                                <?php foreach ($topCounselors as $counselor): ?>
                                                <tr>
                                                    <td>
                                                        <?php if ($rank == 1): ?>
                                                            <span class="badge bg-warning">🥇</span>
                                                        <?php elseif ($rank == 2): ?>
                                                            <span class="badge bg-secondary">🥈</span>
                                                        <?php elseif ($rank == 3): ?>
                                                            <span class="badge bg-info">🥉</span>
                                                        <?php else: ?>
                                                            <?php echo $rank; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo secureOutput($counselor['nom'] . ' ' . $counselor['prenom']); ?></strong>
                                                    </td>
                                                    <td><?php echo secureOutput($counselor['specialite']); ?></td>
                                                    <td><?php echo $counselor['total_demandes']; ?></td>
                                                    <td>
                                                        <span class="badge bg-success">
                                                            <?php echo $counselor['demandes_terminees']; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php echo $counselor['temps_moyen'] ? number_format($counselor['temps_moyen'], 1) : '-'; ?>
                                                    </td>
                                                </tr>
                                                <?php $rank++; ?>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistiques détaillées par conseiller -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-table me-2"></i>
                                        Statistiques détaillées par conseiller
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Conseiller</th>
                                                    <th>Spécialité</th>
                                                    <th>Total</th>
                                                    <th>Terminées</th>
                                                    <th>Actives</th>
                                                    <th>Temps moyen (h)</th>
                                                    <th>Statut</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($counselorStats as $counselor): ?>
                                                <tr>
                                                    <td><?php echo secureOutput($counselor['nom'] . ' ' . $counselor['prenom']); ?></td>
                                                    <td><?php echo secureOutput($counselor['specialite']); ?></td>
                                                    <td><?php echo $counselor['total_demandes'] ?? 0; ?></td>
                                                    <td><?php echo $counselor['demandes_terminees'] ?? 0; ?></td>
                                                    <td><?php echo $counselor['demandes_actives'] ?? 0; ?></td>
                                                    <td>
                                                        <?php 
                                                        $tempsMoyen = $counselor['temps_resolution_moyen'] ?? null;
                                                        echo $tempsMoyen ? number_format($tempsMoyen, 1) : '-';
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $statusClass = '';
                                                        switch($counselor['statut']) {
                                                            case 'actif': $statusClass = 'bg-success'; break;
                                                            case 'en_pause': $statusClass = 'bg-warning'; break;
                                                            case 'inactif': $statusClass = 'bg-danger'; break;
                                                        }
                                                        ?>
                                                        <span class="badge <?php echo $statusClass; ?>">
                                                            <?php echo ucfirst(str_replace('_', ' ', $counselor['statut'])); ?>
                                                        </span>
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

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white mt-4">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; SAFEProject 2025 - Module Support Psychologique</span>
                    </div>
                </div>
            </footer>

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Graphique évolution mensuelle
        const monthlyCtx = document.getElementById('monthlyChart');
        const monthlyData = <?php echo json_encode($monthlyStats); ?>;
        
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: monthlyData.map(item => item.mois),
                datasets: [
                    {
                        label: 'Total demandes',
                        data: monthlyData.map(item => item.total),
                        borderColor: '#4A90E2',
                        backgroundColor: 'rgba(74, 144, 226, 0.1)',
                        tension: 0.3
                    },
                    {
                        label: 'Terminées',
                        data: monthlyData.map(item => item.terminees),
                        borderColor: '#27AE60',
                        backgroundColor: 'rgba(39, 174, 96, 0.1)',
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Graphique répartition par statut
        const statusCtx = document.getElementById('statusChart');
        const globalStats = <?php echo json_encode($globalStats); ?>;
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['En attente', 'Assignées', 'En cours', 'Terminées', 'Annulées'],
                datasets: [{
                    data: [
                        globalStats.demandes_en_attente,
                        globalStats.demandes_assignees,
                        globalStats.demandes_en_cours,
                        globalStats.demandes_terminees,
                        globalStats.demandes_annulees
                    ],
                    backgroundColor: [
                        '#F39C12',
                        '#3498DB',
                        '#9B59B6',
                        '#27AE60',
                        '#95A5A6'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });
    </script>

</body>
</html>

