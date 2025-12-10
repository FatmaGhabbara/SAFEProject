<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject-Post/controller/AdminController.php';
session_start();
// Add role check for security
/*if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/login.php');
    exit();
}*/
$adminController = new AdminController();
include '../../Controller/PostC.php';
include '../../Controller/CommentC.php';
include '../../Controller/RespondC.php';
$cc = new CommentC();
$rc = new RespondC();
$pc = new PostC();
$list_Post = $pc->listPost();
// ... (Keep all your existing PHP logic for actions and data fetching unchanged)
// Gestion des actions pour users
if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    switch ($action) {
        case 'approve':
            $adminController->approveUser($id);
            break;
        case 'block':
            $adminController->blockUser($id);
            break;
        case 'delete':
            $adminController->deleteUser($id);
            $pc->deleteUserPost($id);
            $cc-> deleteComPost($id);
            $rc-> deleteResComPost($id);
            break;
    }
    header("Location: users_list.php");
    exit();
}
// Gestion des actions pour posts
if (isset($_GET['post_action'], $_GET['post_id'])) {
    $id = intval($_GET['post_id']);
    $action = $_GET['post_action'];
    switch ($action) {
        case 'approve':
            $pc->ProuverPost($id); // Assuming this is the approve method; adjust if named differently
            break;
        case 'block':
            $pc->BlockPost($id);
            break;
    }
    header("Location: users_list.php");
    exit();
}
// Ajout pour recherche 
$allUsers = $adminController->getAllUsers();

$users = $allUsers; // Par défaut, tous les utilisateurs sont affichés

// --- LOGIQUE DE RECHERCHE AJOUTÉE ICI ---
$search_term = '';
if (isset($_GET['search_user']) && !empty(trim($_GET['search_user']))) {
    $search_term = trim($_GET['search_user']);
    // Filtrer les utilisateurs par nom complet (fullname)
    $filtered_users = [];
    $search_lower = strtolower($search_term);
    foreach ($allUsers as $user) {
        if (str_contains(strtolower($user['fullname']), $search_lower) || str_contains(strtolower($user['email']), $search_lower)) {
            $filtered_users[] = $user;
        }
    }
    $users = $filtered_users;
}
// --- FIN LOGIQUE DE RECHERCHE ---

// Precompute posts by user... (unchanged)
$postsByUser = [];
foreach ($users as $user) {
    $uid = $user['id'];
    $uemail = $user['email'];
    $postsByUser[$uid] = [];
    foreach ($list_Post as $post) {
        if ($uid == ($post['id_user'] ?? '') || $uemail == ($post['author'] ?? '')) {
            $postsByUser[$uid][] = $post;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SafeSpace - Liste des Utilisateurs (Admin)</title>
    
    <link href="../frontoffice/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../frontoffice/assets/css/sb-admin-2.min.css" rel="stylesheet">
    
    <!-- Paste the entire <style> block from member_dashboard.php here -->
    <style>
        /* ... (Copy the full CSS from member_dashboard.php: body, .dashboard-container, .profile-header-elegant, etc.) */
        /* For brevity, I'm not pasting the full CSS here, but include it in your file. */
        /* Optional: Add table-specific styles */
        .admin-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        .admin-table th, .admin-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e8eef5;
        }
        .admin-table th {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            font-weight: 600;
        }
        .admin-table tr:hover {
            background: #f8fafc;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar (adapted for admin) -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="sidebar-brand-text mx-3">SafeSpace <sup>Admin</sup></div>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Administration</div>
            <li class="nav-item active">
                <a class="nav-link" href="users_list.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Gérer les utilisateurs</span>
                </a>
            </li>
            <!-- Add more admin links as needed -->
            <hr class="sidebar-divider">
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

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search" action="users_list.php" method="GET">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Rechercher un utilisateur (Nom ou Email)..." aria-label="Search" aria-describedby="basic-addon2" name="search_user" value="<?= htmlspecialchars($search_term) ?>">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    <?= htmlspecialchars($_SESSION['fullname'] ?? 'Admin') ?>
                                </span>
                                <i class="fas fa-user fa-fw"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
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
                    <div class="dashboard-container">
                        <!-- Welcome Card (optional, from dashboard) -->
                        <div class="welcome-card-elegant">
                            <h2>Liste des Utilisateurs et Posts</h2>
                            <p>Gérez les comptes et contenus ici.</p>
                        </div>

                        <!-- Main Table in Elegant Card -->
                        <div class="info-card-elegant">
                            <h3 class="section-title-elegant">Utilisateurs avec leurs Posts</h3>
                            <?php if (!empty($users)): ?>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <!-- ... (Copy your existing <tr> for headers and the PHP loop for rows unchanged) -->
                                    <!-- For example: -->
                                    <tr>
                                        <th style="padding: 10px; background: #34495e; color: white;">ID</th>
                                        <th style="padding: 10px; background: #34495e; color: white;">Nom complet</th>
                                        <th style="padding: 10px; background: #34495e; color: white;">Email</th>
                                        <th style="padding: 10px; background: #34495e; color: white;">Rôle</th>
                                        <th style="padding: 10px; background: #34495e; color: white;">Statut</th>
                                        <th style="padding: 10px; background: #34495e; color: white;">Actions (Utilisateur)</th>
                                        <th style="padding: 10px; background: #34495e; color: white;">ID Post</th>
                                        <th style="padding: 10px; background: #34495e; color: white;">Title</th>
                                        <th style="padding: 10px; background: #34495e; color: white;">Message</th>
                                        <th style="padding: 10px; background: #34495e; color: white;">Image</th>
                                        <th style="padding: 10px; background: #34495e; color: white;">Statut</th>
                                        <th style="padding: 10px; background: #34495e; color: white;">Actions (Post)</th>
                                    </tr>
                                    <?php foreach($users as $user): ?>
                                        <?php
                                            $uid = $user['id'];
                                            $userPosts = $postsByUser[$uid] ?? [];
                                            $numPosts = count($userPosts);
                                            $rowspan = $numPosts > 0 ? $numPosts : 1;
                                            $first = true;
                                            if ($numPosts == 0) {
                                                // User with no posts
                                                echo '<tr>';
                                                echo '<td>' . htmlspecialchars($user['id']) . '</td>';
                                                echo '<td>' . htmlspecialchars($user['fullname']) . '</td>';
                                                echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                                                echo '<td>';
                                                $roleIcons = [
                                                    'admin' => '👑',
                                                    'conseilleur' => '💼',
                                                    'membre' => '👤'
                                                ];
                                                echo ($roleIcons[$user['role']] ?? '👤') . ' ' . htmlspecialchars($user['role']);
                                                echo '</td>';
                                                echo '<td>' . htmlspecialchars($user['status']) . '</td>';
                                                echo '<td>';
                                                if ($user['status'] !== 'approved') {
                                                    echo '<a href="users_list.php?action=approve&id=' . $user['id'] . '" style="color: blue;">Approuver</a> |';
                                                }
                                                if ($user['status'] !== 'blocked') {
                                                    echo '<a href="users_list.php?action=block&id=' . $user['id'] . '" style="color: orange;">Bloquer</a> |';
                                                }
                                                echo '<a href="users_list.php?action=delete&id=' . $user['id'] . '" style="color: red;" onclick="return confirm(\'Supprimer cet utilisateur ?\')">Supprimer</a>';
                                                echo '</td>';
                                                echo '<td colspan="6" style="text-align: center;">Aucun post</td>';
                                                echo '</tr>';
                                            } else {
                                                // User with posts
                                                foreach ($userPosts as $post) {
                                                    echo '<tr>';
                                                    if ($first) {
                                                        echo '<td rowspan="' . $rowspan . '">' . htmlspecialchars($user['id']) . '</td>';
                                                        echo '<td rowspan="' . $rowspan . '">' . htmlspecialchars($user['fullname']) . '</td>';
                                                        echo '<td rowspan="' . $rowspan . '">' . htmlspecialchars($user['email']) . '</td>';
                                                        echo '<td rowspan="' . $rowspan . '">';
                                                        $roleIcons = [
                                                            'admin' => '👑',
                                                            'conseilleur' => '💼',
                                                            'membre' => '👤'
                                                        ];
                                                        echo ($roleIcons[$user['role']] ?? '👤') . ' ' . htmlspecialchars($user['role']);
                                                        echo '</td>';
                                                        echo '<td rowspan="' . $rowspan . '">' . htmlspecialchars($user['status']) . '</td>';
                                                        echo '<td rowspan="' . $rowspan . '">';
                                                        if ($user['status'] !== 'approved') {
                                                            echo '<a href="users_list.php?action=approve&id=' . $user['id'] . '" style="color: blue;">Approuver</a> |';
                                                        }
                                                        if ($user['status'] !== 'blocked') {
                                                            echo '<a href="users_list.php?action=block&id=' . $user['id'] . '" style="color: orange;">Bloquer</a> |';
                                                        }
                                                        echo '<a href="users_list.php?action=delete&id=' . $user['id'] . '" style="color: red;" onclick="return confirm(\'Supprimer cet utilisateur ?\')">Supprimer</a>';
                                                        echo '</td>';
                                                        $first = false;
                                                    }
                                                    echo '<td>' . htmlspecialchars($post['id']) . '</td>';
                                                    echo '<td>' . htmlspecialchars($post['author'] ?? 'N/A') . '</td>';
                                                    echo '<td>' . htmlspecialchars(substr($post['message'] ?? '', 0, 100)) . (strlen($post['message'] ?? '') > 100 ? '...' : '') . '</td>';
                                                    echo '<td>';
                                                    if (!empty($post['image'])) {
                                                        echo '<img src="' . htmlspecialchars($post['image']) . '" alt="Post image" style="max-width: 100px; max-height: 100px;">';
                                                    } else {
                                                        echo 'Aucune image';
                                                    }
                                                    echo '</td>';
                                                    echo '<td>' . htmlspecialchars($post['status'] ?? 'N/A') . '</td>';
                                                    echo '<td>';
                                                    if (isset($post['status']) && $post['status'] !== 'approved') {
                                                        echo '<a href="users_list.php?post_action=approve&post_id=' . $post['id'] . '" style="color: blue;">Approuver</a> |';
                                                    }
                                                    if (isset($post['status']) && $post['status'] !== 'blocked') {
                                                        echo '<a href="users_list.php?post_action=block&post_id=' . $post['id'] . '" style="color: orange;">Bloquer</a> |';
                                                    }
                                                    echo '<a href="deletePost.php?id=' . $post['id'] . '" style="color: red;" onclick="return confirm(\'Supprimer ce post ?\')">Supprimer</a>';
                                                    echo '</td>';
                                                    echo '</tr>';
                                                }
                                            }
                                            ?>
                                        <!-- ... (Your entire loop code for users and posts goes here, unchanged) -->
                                    <?php endforeach; ?>
                                </table>
                            </div>
                            <?php else: ?>
                                <p>Aucun utilisateur trouvé.</p>
                            <?php endif; ?>
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

    <!-- Scripts (from member_dashboard.php) -->
    <script src="../frontoffice/assets/vendor/jquery/jquery.min.js"></script>
    <script src="../frontoffice/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../frontoffice/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../frontoffice/assets/js/sb-admin-2.min.js"></script>
    
    <!-- Optional JS from dashboard -->
    <script>
        $(document).ready(function() {
            // Add any interactions, like hover effects if needed
        });
    </script>
</body>
</html>