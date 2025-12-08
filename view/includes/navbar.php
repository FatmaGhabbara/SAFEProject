<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../view/frontoffice/login.php');
    exit();
}

$user_name = $_SESSION['prenom'] . ' ' . $_SESSION['nom'];
$user_email = $_SESSION['email'];
$user_role = $_SESSION['role'];

// Calculate base path correctly based on where navbar is included
// navbar.php is in: SAFEProject/view/includes/
// __DIR__ = SAFEProject/view/includes
// dirname(__DIR__) = SAFEProject/view
// dirname(dirname(__DIR__)) = SAFEProject
$project_root = dirname(dirname(__DIR__)); // SAFEProject/

// Get current script location (the page that includes navbar)
$current_script = $_SERVER['SCRIPT_FILENAME'];
$current_dir = dirname($current_script);

// Both paths need to be normalized (same format)
$project_root = str_replace('\\', '/', realpath($project_root));
$current_dir = str_replace('\\', '/', realpath($current_dir));

// Calculate relative path from current dir to project root
$relative_to_root = str_replace($project_root . '/', '', $current_dir);

// Count directory levels: "view/backoffice/support" = 3 levels
$depth_parts = array_filter(explode('/', $relative_to_root));
$depth = count($depth_parts);
$base_path = $depth > 0 ? str_repeat('../', $depth) : './';

// Determine if we're in specific directories
$is_in_frontoffice = strpos($relative_to_root, 'frontoffice') !== false;
$is_in_backoffice = strpos($relative_to_root, 'backoffice') !== false;
$is_in_support = strpos($relative_to_root, 'support') !== false;

// Determine home link based on role
if ($user_role === 'admin') {
    $home_link = $base_path . 'view/backoffice/support/support_requests.php';
} elseif ($user_role === 'counselor') {
    $home_link = $base_path . 'view/backoffice/support/dashboard_counselor.php';
} else {
    $home_link = $base_path . 'view/frontoffice/dashboard.php';
}

// Logout path - SIMPLE: use the same depth calculation as base_path
// From view/frontoffice/: depth=1 -> ../controller/auth/logout.php
// From view/backoffice/support/: depth=2 -> ../../controller/auth/logout.php
$logout_path = $base_path . 'controller/auth/logout.php';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo htmlspecialchars($home_link); ?>">
            <strong>🧠 SAFEProject</strong>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if ($user_role === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo htmlspecialchars($base_path . 'view/backoffice/support/support_requests.php'); ?>">
                            <i class="fas fa-list"></i> All Requests
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo htmlspecialchars($base_path . 'view/backoffice/support/counselors_list.php'); ?>">
                            <i class="fas fa-user-md"></i> Counselors
                        </a>
                    </li>
                <?php elseif ($user_role === 'counselor'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo htmlspecialchars($base_path . 'view/backoffice/support/dashboard_counselor.php'); ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo htmlspecialchars($base_path . 'view/backoffice/support/my_assigned_requests.php'); ?>">
                            <i class="fas fa-tasks"></i> Mes Demandes Assignées
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php 
                            if ($is_in_frontoffice && !$is_in_support) {
                                echo 'support/my_requests.php';
                            } else {
                                echo htmlspecialchars($base_path . 'view/frontoffice/support/my_requests.php');
                            }
                        ?>">
                            <i class="fas fa-inbox"></i> My Requests
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php 
                            if ($is_in_frontoffice && !$is_in_support) {
                                echo 'support/support_form.php';
                            } else {
                                echo htmlspecialchars($base_path . 'view/frontoffice/support/support_form.php');
                            }
                        ?>">
                            <i class="fas fa-plus-circle"></i> New Request
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav ms-auto">
                <!-- Notifications (for counselors) -->
                <?php if ($user_role === 'counselor'): 
                    require_once __DIR__ . '/../../controller/helpers.php';
                    $counselorUser = getCounselorByUserId($_SESSION['user_id']);
                    if ($counselorUser) {
                        $requests = findSupportRequestsByCounselor($counselorUser->getId());
                        $newAssignedRequests = count(array_filter($requests, function($req) {
                            return $req->getStatut() === 'assignee';
                        }));
                    } else {
                        $newAssignedRequests = 0;
                    }
                ?>
                <li class="nav-item me-3">
                    <a class="nav-link" href="<?php echo htmlspecialchars($base_path . 'view/backoffice/support/my_assigned_requests.php'); ?>">
                        <i class="fas fa-bell fa-lg"></i>
                        <?php if ($newAssignedRequests > 0): ?>
                            <span class="badge bg-danger rounded-pill" style="font-size: 0.7rem;"><?php echo $newAssignedRequests; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endif; ?>
                
                <!-- User Profile Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fa-lg"></i>
                        <span class="ms-2"><?php echo htmlspecialchars($user_name); ?></span>
                        <span class="badge bg-light text-dark ms-1"><?php echo $user_role === 'admin' ? 'admin' : ucfirst($user_role); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li class="dropdown-header">
                            <strong><?php echo htmlspecialchars($user_name); ?></strong><br>
                            <small class="text-muted"><?php echo htmlspecialchars($user_email); ?></small>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?php 
                                if ($is_in_frontoffice && !$is_in_support) {
                                    echo 'profil.php';
                                } else {
                                    echo htmlspecialchars($base_path . 'view/frontoffice/profil.php');
                                }
                            ?>">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                        </li>
                        <?php if ($user_role === 'user'): ?>
                        <li>
                            <a class="dropdown-item" href="<?php 
                                if ($is_in_frontoffice && !$is_in_support) {
                                    echo 'support/my_requests.php';
                                } else {
                                    echo htmlspecialchars($base_path . 'view/frontoffice/support/my_requests.php');
                                }
                            ?>">
                                <i class="fas fa-inbox"></i> My Requests
                            </a>
                        </li>
                        <?php elseif ($user_role === 'counselor'): ?>
                        <li>
                            <a class="dropdown-item" href="<?php echo htmlspecialchars($base_path . 'view/backoffice/support/dashboard_counselor.php'); ?>">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                            <a class="dropdown-item" href="<?php echo htmlspecialchars($base_path . 'view/backoffice/support/my_assigned_requests.php'); ?>">
                                <i class="fas fa-tasks"></i> Mes Demandes
                            </a>
                        </li>
                        <?php elseif ($user_role === 'admin'): ?>
                        <li>
                            <a class="dropdown-item" href="<?php echo htmlspecialchars($base_path . 'view/backoffice/support/support_requests.php'); ?>">
                                <i class="fas fa-list"></i> All Requests
                            </a>
                        </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?php echo $logout_path; ?>">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
.navbar {
    padding: 1rem 0;
}
.navbar-brand {
    font-size: 1.5rem;
    font-weight: bold;
}
.nav-link {
    font-weight: 500;
    transition: all 0.3s;
}
.nav-link:hover {
    transform: translateY(-2px);
}
.dropdown-menu {
    min-width: 250px;
    border: none;
    border-radius: 10px;
}
.dropdown-item {
    padding: 0.7rem 1.5rem;
    transition: all 0.3s;
}
.dropdown-item:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.dropdown-item i {
    width: 20px;
    margin-right: 10px;
}
.badge {
    font-size: 0.7rem;
    padding: 0.3rem 0.6rem;
}
</style>
