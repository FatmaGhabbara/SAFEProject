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

// Get old data and errors if any
$errors = $_SESSION['register_errors'] ?? [];
$old = $_SESSION['old_data'] ?? [];
unset($_SESSION['register_errors'], $_SESSION['old_data']);

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SAFEProject - Register</title>
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
        .form-control {
            border-radius: 0.35rem;
        }
    </style>
</head>

<body class="bg-gradient-primary">
    <div class="container">
        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4">Create an Account!</h1>
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
                            
                            <form class="user" method="POST" action="../../controller/auth/register.php">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                
                                <!-- Role Selection -->
                                <div class="form-group">
                                    <label for="role" class="text-gray-700 font-weight-bold">I want to register as:</label>
                                    <select name="role" id="role" class="form-control" required onchange="toggleCounselorFields()">
                                        <option value="user" <?php echo ($old['role'] ?? 'user') === 'user' ? 'selected' : ''; ?>>User (Request Support)</option>
                                        <option value="counselor" <?php echo ($old['role'] ?? '') === 'counselor' ? 'selected' : ''; ?>>Counselor (Provide Support)</option>
                                    </select>
                                </div>
                                
                                <!-- Name Fields -->
                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <label for="prenom" class="text-gray-700 font-weight-bold small">Prénom <span class="text-danger">*</span></label>
                                        <input type="text" name="prenom" id="prenom" class="form-control form-control-user" 
                                               placeholder="Prénom" value="<?php echo htmlspecialchars($old['prenom'] ?? ''); ?>" 
                                               required minlength="2" maxlength="100"
                                               pattern="[a-zA-ZÀ-ÿ\s\-\']+"
                                               title="Lettres, espaces, tirets et apostrophes uniquement">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="nom" class="text-gray-700 font-weight-bold small">Nom <span class="text-danger">*</span></label>
                                        <input type="text" name="nom" id="nom" class="form-control form-control-user" 
                                               placeholder="Nom" value="<?php echo htmlspecialchars($old['nom'] ?? ''); ?>" 
                                               required minlength="2" maxlength="100"
                                               pattern="[a-zA-ZÀ-ÿ\s\-\']+"
                                               title="Lettres, espaces, tirets et apostrophes uniquement">
                                    </div>
                                </div>
                                
                                <!-- Email -->
                                <div class="form-group">
                                    <label for="email" class="text-gray-700 font-weight-bold small">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control form-control-user" 
                                           placeholder="Adresse email" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>" 
                                           required maxlength="255">
                                </div>
                                
                                <!-- Password Fields -->
                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <label for="password" class="text-gray-700 font-weight-bold small">Mot de passe <span class="text-danger">*</span></label>
                                        <input type="password" name="password" id="password" class="form-control form-control-user" 
                                               placeholder="Mot de passe (min 6 caractères)" 
                                               required minlength="6" maxlength="72">
                                        <small class="form-text text-muted">
                                            Minimum 6 caractères
                                        </small>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="password_confirm" class="text-gray-700 font-weight-bold small">Confirmer <span class="text-danger">*</span></label>
                                        <input type="password" name="password_confirm" id="password_confirm" class="form-control form-control-user" 
                                               placeholder="Confirmer le mot de passe" 
                                               required minlength="6" maxlength="72">
                                        <small class="form-text text-muted" id="password-match"></small>
                                    </div>
                                </div>
                                
                                <!-- Counselor Fields (hidden by default) -->
                                <div id="counselor-fields" style="display: <?php echo ($old['role'] ?? 'user') === 'counselor' ? 'block' : 'none'; ?>;">
                                    <hr class="my-4">
                                    <h6 class="text-primary mb-3"><i class="fas fa-user-md"></i> Informations professionnelles</h6>
                                    
                                    <div class="form-group">
                                        <label for="specialite" class="text-gray-700 font-weight-bold">
                                            Spécialité <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="specialite" id="specialite" 
                                               class="form-control form-control-user" 
                                               placeholder="Ex: Psychologie clinique, Gestion du stress, Thérapie cognitive..." 
                                               value="<?php echo htmlspecialchars($old['specialite'] ?? ''); ?>"
                                               minlength="3" maxlength="255"
                                               pattern="[a-zA-ZÀ-ÿ0-9\s\-\'\.\(\)]+"
                                               title="Lettres, chiffres, espaces, tirets, apostrophes et points autorisés">
                                        <small class="form-text text-muted">
                                            Minimum 3 caractères, maximum 255 caractères
                                        </small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="biographie" class="text-gray-700 font-weight-bold">
                                            Biographie <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="biographie" id="biographie" class="form-control" rows="6" 
                                                  placeholder="Décrivez votre parcours professionnel, vos compétences, votre expérience... (minimum 50 caractères, 10 mots minimum)"
                                                  minlength="50" maxlength="2000" required><?php echo htmlspecialchars($old['biographie'] ?? ''); ?></textarea>
                                        <small class="form-text text-muted">
                                            <span id="biographie-count">0</span> / 2000 caractères | Minimum 50 caractères et 10 mots
                                        </small>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-user btn-block">
                                    Register Account
                                </button>
                            </form>
                            
                            <hr>
                            <div class="text-center">
                                <a class="small" href="login.php">Already have an account? Login!</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleCounselorFields() {
            const role = document.getElementById('role').value;
            const counselorFields = document.getElementById('counselor-fields');
            counselorFields.style.display = role === 'counselor' ? 'block' : 'none';
            
            // Make fields required/optional based on role
            const specialite = document.querySelector('input[name="specialite"]');
            const biographie = document.querySelector('textarea[name="biographie"]');
            if (role === 'counselor') {
                specialite.required = true;
                biographie.required = true;
            } else {
                specialite.required = false;
                biographie.required = false;
                specialite.value = '';
                biographie.value = '';
            }
        }

        // Validation en temps réel du mot de passe
        document.getElementById('password_confirm').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const passwordConfirm = this.value;
            const matchElement = document.getElementById('password-match');
            
            if (passwordConfirm.length > 0) {
                if (password === passwordConfirm) {
                    matchElement.textContent = '✓ Les mots de passe correspondent';
                    matchElement.className = 'form-text text-success';
                } else {
                    matchElement.textContent = '✗ Les mots de passe ne correspondent pas';
                    matchElement.className = 'form-text text-danger';
                }
            } else {
                matchElement.textContent = '';
            }
        });

        // Compteur de caractères pour la biographie
        const biographieField = document.getElementById('biographie');
        const biographieCount = document.getElementById('biographie-count');
        
        if (biographieField) {
            biographieField.addEventListener('input', function() {
                const count = this.value.length;
                biographieCount.textContent = count;
                
                if (count < 50) {
                    biographieCount.className = 'text-danger';
                } else if (count > 2000) {
                    biographieCount.className = 'text-danger';
                } else {
                    biographieCount.className = 'text-success';
                }
            });
            
            // Initialiser le compteur
            biographieField.dispatchEvent(new Event('input'));
        }

        // Initialize fields on page load
        toggleCounselorFields();
        
        // Validation du formulaire avant soumission
        document.querySelector('form.user').addEventListener('submit', function(e) {
            const role = document.getElementById('role').value;
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;
            
            // Vérifier la correspondance des mots de passe
            if (password !== passwordConfirm) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas.');
                return false;
            }
            
            // Validation simple du mot de passe (minimum 6 caractères)
            if (password.length < 6) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 6 caractères.');
                return false;
            }
            
            // Validation spécifique aux conseillers
            if (role === 'counselor') {
                const specialite = document.getElementById('specialite').value.trim();
                const biographie = document.getElementById('biographie').value.trim();
                
                if (specialite.length < 3) {
                    e.preventDefault();
                    alert('La spécialité doit contenir au moins 3 caractères.');
                    return false;
                }
                
                if (biographie.length < 50) {
                    e.preventDefault();
                    alert('La biographie doit contenir au moins 50 caractères.');
                    return false;
                }
                
                // Compter les mots dans la biographie
                const wordCount = biographie.split(/\s+/).filter(word => word.length > 0).length;
                if (wordCount < 10) {
                    e.preventDefault();
                    alert('La biographie doit contenir au moins 10 mots.');
                    return false;
                }
            }
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

