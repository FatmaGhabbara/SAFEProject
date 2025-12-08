<?php
session_start();
require_once '../../config.php';

// If already logged in, redirect to appropriate dashboard based on role
if (isLoggedIn()) {
    $user_role = $_SESSION['role'] ?? 'user';
    
    if ($user_role === 'admin') {
        header('Location: ../../view/backoffice/support/support_requests.php');
        exit();
    } elseif ($user_role === 'counselor') {
        header('Location: ../../view/backoffice/support/my_assigned_requests.php');
        exit();
    } else {
        header('Location: ../../view/frontoffice/dashboard.php');
        exit();
    }
}

// CRITICAL: Clean up any residual session data from previous logout
// This ensures a fresh login even if logout didn't fully clear everything
if (!isLoggedIn()) {
    // If not logged in, ensure no user data remains in session
    if (isset($_SESSION['user_id']) || isset($_SESSION['logged_in'])) {
        // Clear any residual user data
        unset($_SESSION['user_id'], $_SESSION['email'], $_SESSION['nom'], 
              $_SESSION['prenom'], $_SESSION['role'], $_SESSION['logged_in']);
    }
}

// Get errors if any
$errors = $_SESSION['login_errors'] ?? [];
$old_email = $_SESSION['old_email'] ?? '';
unset($_SESSION['login_errors'], $_SESSION['old_email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SAFEProject - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .btn-user {
            border-radius: 10rem;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
        }
        .form-control-user {
            border-radius: 10rem;
            padding: 1.5rem 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>

<body class="bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                    </div>
                                    
                                    <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            <?php foreach ($errors as $error): ?>
                                                <li><?php echo $error; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <form class="user" method="POST" action="../../controller/auth/login.php">
                                        <div class="form-group">
                                            <input type="email" name="email" class="form-control form-control-user" 
                                                   placeholder="Enter Email Address..." value="<?php echo $old_email; ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" name="password" class="form-control form-control-user" 
                                                   placeholder="Password" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </button>
                                    </form>
                                    
                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="register.php">Create an Account!</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

