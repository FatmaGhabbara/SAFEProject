<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/AuthController.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/config.php';
session_start();

$errors = [];
$maxFileSize = 2 * 1024 * 1024; // 2MB max
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
$uploadPath = $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/view/frontoffice/assets/images/uploads/';

// Créer le dossier s'il n'existe pas
if (!file_exists($uploadPath)) {
    mkdir($uploadPath, 0777, true);
}

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: member_dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $role = trim($_POST['role']);

    // Validation des champs
    if (empty($firstname) || empty($lastname)) {
        $errors[] = "Nom et prénom requis.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email valide requis.";
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Mot de passe requis (minimum 6 caractères).";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    
    // Validation du rôle
    $allowedRoles = ['membre', 'conseilleur'];
    if (!in_array($role, $allowedRoles)) {
        $role = 'membre'; // Valeur par défaut sécurisée
    }

    // Vérifier si l'email existe déjà
    if (empty($errors)) {
        try {
            $db = config::getConnexion();  
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "Cette adresse email est déjà utilisée.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de vérification de l'email.";
        }
    }

    // Gestion de l'upload de la photo
    $profilePicture = null; // Valeur par défaut sera null
    
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        
        // Validation supplémentaire avec getimagesize()
        $image_info = @getimagesize($fileTmpName);
        if ($image_info === false) {
            $errors[] = "Le fichier n'est pas une image valide.";
        }
        
        $fileType = $image_info['mime'] ?? mime_content_type($fileTmpName);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Validation de la photo
        if ($fileSize > $maxFileSize) {
            $errors[] = "La photo est trop volumineuse (maximum 2MB).";
        }
        elseif (!in_array($fileType, $allowedTypes)) {
            $errors[] = "Type de fichier non autorisé. Formats acceptés: JPG, JPEG, PNG, GIF.";
        }
        elseif (!in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $errors[] = "Extension de fichier non autorisée.";
        }
        else {
            // Générer un nom de fichier unique
            $newFileName = 'profile_' . time() . '_' . uniqid() . '.' . $fileExtension;
            $uploadFile = $uploadPath . $newFileName;
            
            // Déplacer le fichier uploadé
            if (move_uploaded_file($fileTmpName, $uploadFile)) {
                $profilePicture = $newFileName; // Stocker juste le nom de fichier
            } else {
                $errors[] = "Erreur lors de l'upload de la photo.";
            }
        }
    } elseif (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors[] = "Erreur lors de l'upload du fichier (Code: " . $_FILES['profile_picture']['error'] . ").";
    }

    if (empty($errors)) {
        $authController = new AuthController();
        $fullname = trim($firstname . ' ' . $lastname);
        
        // Appeler la méthode register avec la photo de profil
        $result = $authController->register($fullname, $email, $password, $role, $profilePicture); 
        
        if ($result === true) {
            $_SESSION['success'] = "Inscription réussie ! Votre compte est en attente de validation.";
            header("Location: login.php");
            exit;
        } else {
            $errors[] = $result;
            // Supprimer l'image uploadée si l'inscription a échoué
            if ($profilePicture && file_exists($uploadPath . $profilePicture)) {
                unlink($uploadPath . $profilePicture);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - SafeSpace</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <noscript><link rel="stylesheet" href="assets/css/noscript.css"></noscript>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .photo-preview-container {
            text-align: center;
            margin: 20px 0;
        }
        .photo-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e0e0e0;
            margin-bottom: 10px;
            display: none;
        }
        .photo-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            color: #999;
            font-size: 40px;
        }
        .file-input-wrapper {
            margin-bottom: 15px;
        }
        .file-input-label {
            display: inline-block;
            padding: 8px 16px;
            background: #4e73df;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .file-input-label:hover {
            background: #2e59d9;
        }
        .file-name {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .photo-hint {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        .password-match {
            font-size: 12px;
            margin-top: 5px;
        }
        .match-ok {
            color: #1cc88a;
        }
        .match-error {
            color: #e74a3b;
        }
    </style>
        
</head>
<body class="is-preload">

<div id="page-wrapper">

    <!-- Header -->
    <header id="header">
        <h1><a href="index.php">SafeSpace</a></h1>
        <nav>
            <a href="index.php">Accueil</a> |
            <a href="login.php">Connexion</a> |
            <a href="register.php">Inscription</a>
        </nav>
    </header>

    <!-- Wrapper -->
    <section id="wrapper">
        <header>
            <div class="inner">
                <h2>Inscription</h2>
                <p>Rejoignez la communauté SafeSpace</p>
            </div>
        </header>

        <!-- Content -->
        <div class="wrapper">
            <div class="inner">

                <?php if(isset($_SESSION['success'])): ?>
                    <div class="success">
                        <p><?= htmlspecialchars($_SESSION['success']) ?></p>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if(!empty($errors)): ?>
                    <div class="error">
                        <?php foreach($errors as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="" enctype="multipart/form-data">
                    <div class="fields">
                        <!-- Photo de profil -->
                        <div class="field full">
                            <div class="photo-preview-container">
                                <div class="photo-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                                <img id="photoPreview" class="photo-preview" src="" alt="Aperçu de la photo">
                                <div class="file-input-wrapper">
                                    <label class="file-input-label">
                                        <i class="fas fa-camera"></i> Choisir une photo de profil
                                        <input type="file" name="profile_picture" id="profile_picture" accept="image/*" onchange="previewImage(this)" style="display: none;">
                                    </label>
                                </div>
                                <div class="file-name" id="fileName">Aucun fichier choisi</div>
                                <div class="photo-hint">Formats acceptés: JPG, PNG, GIF (max 2MB)</div>
                            </div>
                        </div>

                        <div class="field half">
                            <label for="firstname">Prénom</label>
                            <input type="text" name="firstname" id="firstname" 
                                   placeholder="Votre prénom" 
                                   value="<?= htmlspecialchars($_POST['firstname'] ?? '') ?>" 
                                   required />
                        </div>
                        <div class="field half">
                            <label for="lastname">Nom</label>
                            <input type="text" name="lastname" id="lastname" 
                                   placeholder="Votre nom" 
                                   value="<?= htmlspecialchars($_POST['lastname'] ?? '') ?>" 
                                   required />
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" 
                                   placeholder="exemple@mail.com" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                                   required />
                        </div>
                        
                        <!-- Champ Mot de passe avec générateur IA -->
                        <div class="field">
                            <label for="password">
                                <i class="fas fa-lock"></i> Mot de passe 
                                <span class="ai-badge">IA</span>
                            </label>
                            
                            <div class="password-field">
                                <input type="password" name="password" id="password" 
                                       placeholder="Créez un mot de passe sécurisé" 
                                       minlength="6" required
                                       oninput="checkPasswordStrength(this.value)" />
                                <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                            
                            <!-- Indicateur de force -->
                            <div id="passwordStrength" style="display: none;" class="strength-container">
                                <div class="strength-label">
                                    <span>Force du mot de passe :</span>
                                    <span id="strengthText"></span>
                                </div>
                                <div class="strength-bar">
                                    <div id="strengthBar" class="strength-fill"></div>
                                </div>
                                <div id="passwordFeedback" class="password-feedback"></div>
                            </div>
                            
                            <button type="button" class="btn-ai" onclick="showPasswordGenerator()" style="margin-top: 15px;">
                                <i class="fas fa-robot"></i> Générer un mot de passe avec IA
                            </button>
                        </div>
                        <div class="field">
                            <label for="confirm_password">Confirmer le mot de passe</label>
                            <input type="password" name="confirm_password" id="confirm_password" placeholder="Retapez votre mot de passe" required />
                            <div id="passwordMatch" class="password-match"></div>
                        </div>
                      
                        <!-- Champ Rôle -->
                        <div class="field">
                            <label for="role">Rôle</label>
                            <select name="role" id="role" required>
                                <option value="membre" <?= ($_POST['role'] ?? '') == 'membre' ? 'selected' : '' ?>>Membre</option>
                                <option value="conseilleur" <?= ($_POST['role'] ?? '') == 'conseilleur' ? 'selected' : '' ?>>Conseilleur</option>
                            </select>
                            <p style="font-size: 0.85rem; color: #6c757d; margin-top: 5px;">
                                <i class="fas fa-info-circle"></i> 
                                Les comptes conseillers nécessitent une validation par l'administrateur
                            </p>
                        </div>
                    </div>
                    
                    <!-- Générateur de mot de passe IA (caché par défaut) -->
                    <div class="ai-password-section" id="passwordGenerator" style="display: none;">
                        <div class="ai-header">
                            <i class="fas fa-robot"></i>
                            <span>Générateur IA de mots de passe</span>
                            <span class="ai-badge">INTELLIGENCE ARTIFICIELLE</span>
                        </div>
                        
                        <div class="ai-content">
                            <!-- Mot de passe généré -->
                            <div class="password-display-box">
                                <div id="generatedPasswordText">Cliquez sur "Générer" pour créer un mot de passe sécurisé</div>
                                <button type="button" class="copy-btn" onclick="copyGeneratedPassword()" id="copyBtn" style="display: none;">
                                    <i class="fas fa-copy"></i> Copier
                                </button>
                            </div>
                            
                            <!-- Options de génération -->
                            <div class="options-grid">
                                <div class="option-group">
                                    <h4><i class="fas fa-ruler"></i> Longueur du mot de passe</h4>
                                    <div class="length-control">
                                        <input type="range" id="pwdLength" min="8" max="32" value="12" 
                                               class="length-slider" 
                                               oninput="updateLengthValue(this.value)">
                                        <div style="text-align: center; margin-top: 10px;">
                                            <span class="length-value" id="lengthValue">12 caractères</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="option-group">
                                    <h4><i class="fas fa-sliders-h"></i> Type de caractères</h4>
                                    <div class="checkboxes">
                                        <div class="checkbox-item">
                                            <input type="checkbox" id="includeUpper" checked>
                                            <label for="includeUpper">Majuscules (A-Z)</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox" id="includeNumbers" checked>
                                            <label for="includeNumbers">Chiffres (0-9)</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox" id="includeSymbols" checked>
                                            <label for="includeSymbols">Symboles (!@#$%)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Boutons de génération -->
                            <div class="action-buttons">
                                <button type="button" class="btn-ai" onclick="generateAIPassword('strong')">
                                    <i class="fas fa-shield-alt"></i> Générer un mot de passe fort
                                </button>
                                <button type="button" class="btn-ai btn-ai-secondary" onclick="generateAIPassword('memorable')">
                                    <i class="fas fa-brain"></i> Générer un mot de passe mémorable
                                </button>
                            </div>
                            
                            <!-- Thèmes optionnels -->
                            <div style="text-align: center; margin: 25px 0;">
                                <h4 class="theme-title">
                                    <i class="fas fa-palette"></i> Thèmes optionnels
                                </h4>
                                <div class="theme-buttons">
                                    <button type="button" class="theme-btn" onclick="generateThemedPassword('tech')">
                                        <i class="fas fa-laptop-code"></i> Tech
                                    </button>
                                    <button type="button" class="theme-btn" onclick="generateThemedPassword('nature')">
                                        <i class="fas fa-leaf"></i> Nature
                                    </button>
                                    <button type="button" class="theme-btn" onclick="generateThemedPassword('food')">
                                        <i class="fas fa-utensils"></i> Nourriture
                                    </button>
                                    <button type="button" class="theme-btn" onclick="generateThemedPassword('fantasy')">
                                        <i class="fas fa-dragon"></i> Fantaisie
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Actions sur le mot de passe généré -->
                            <div class="action-buttons" id="passwordActions" style="display: none;">
                                <button type="button" class="btn-ai" onclick="useGeneratedPassword()" id="usePasswordBtn">
                                    <i class="fas fa-check-circle"></i> Utiliser ce mot de passe
                                </button>
                                <button type="button" class="btn-ai btn-ai-secondary" onclick="checkPasswordLeak()" id="checkLeakBtn">
                                    <i class="fas fa-shield-check"></i> Vérifier les fuites
                                </button>
                            </div>
                            
                            <!-- Indice mnémotechnique -->
                            <div class="password-hint" id="passwordHint" style="display: none;">
                                <i class="fas fa-lightbulb"></i>
                                <span id="hintText"></span>
                            </div>
                            
                            <!-- Résultat vérification fuite -->
                            <div class="leak-check-result" id="leakCheckResult"></div>
                            
                            <!-- Conseils de sécurité -->
                            <div class="security-tips">
                                <h4><i class="fas fa-tips"></i> Conseils de sécurité</h4>
                                <ul>
                                    <li>Utilisez un mot de passe différent pour chaque site</li>
                                    <li>Évitez les informations personnelles (date de naissance, nom)</li>
                                    <li>Changez votre mot de passe tous les 3 mois</li>
                                    <li>Activez l'authentification à deux facteurs si disponible</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <ul class="actions">
                        <li>
                            <input type="submit" value="S'inscrire" class="primary" 
                                   onclick="return validateForm()" />
                        </li>
                        <li>
                            <a href="login.php" class="button">
                                <i class="fas fa-sign-in-alt"></i> J'ai déjà un compte
                            </a>
                        </li>
                    </ul>
                </form>

            </div>
        </div>
    </section>

    <!-- Footer -->
    <section id="footer">
        <div class="inner">
            <p>Protégeons ensemble, agissons avec bienveillance.</p>
        </div>
    </section>

</div>

<!-- Scripts -->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/jquery.scrollex.min.js"></script>
<script src="assets/js/browser.min.js"></script>
<script src="assets/js/breakpoints.min.js"></script>
<script src="assets/js/util.js"></script>
<script src="assets/js/main.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

<script>
    // Prévisualisation de la photo
    function previewImage(input) {
        const preview = document.getElementById('photoPreview');
        const placeholder = document.querySelector('.photo-placeholder');
        const fileName = document.getElementById('fileName');
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            fileName.textContent = file.name;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            }
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
            placeholder.style.display = 'flex';
            fileName.textContent = 'Aucun fichier choisi';
        }
    }

    // Vérification de la correspondance des mots de passe
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        const matchDiv = document.getElementById('passwordMatch');
        
        if (confirmPassword.length === 0) {
            matchDiv.innerHTML = '';
            matchDiv.className = 'password-match';
        } else if (password === confirmPassword) {
            matchDiv.innerHTML = '<i class="fas fa-check"></i> Les mots de passe correspondent';
            matchDiv.className = 'password-match match-ok';
        } else {
            matchDiv.innerHTML = '<i class="fas fa-times"></i> Les mots de passe ne correspondent pas';
            matchDiv.className = 'password-match match-error';
        }
    });

    // Validation du formulaire côté client
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        // Vérifier les mots de passe
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Les mots de passe ne correspondent pas.');
            document.getElementById('confirm_password').focus();
            return false;
        }
        
        return true;
    });

    // Simuler le clic sur le fichier input quand on clique sur le label
    document.querySelector('.file-input-label').addEventListener('click', function() {
        document.getElementById('profile_picture').click();
    });
</script>

<!-- Script pour le générateur IA -->
<script>
let currentGeneratedPassword = '';
let passwordGenerated = false;

// Afficher/masquer le mot de passe
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'fas fa-eye';
    }
}

// Afficher le générateur de mot de passe
function showPasswordGenerator() {
    const generator = document.getElementById('passwordGenerator');
    generator.style.display = 'block';
    generator.classList.add('fade-in');
    generator.scrollIntoView({ behavior: 'smooth' });
}

// Mettre à jour l'affichage de la longueur
function updateLengthValue(value) {
    document.getElementById('lengthValue').textContent = value + ' caractères';
}

// Vérifier la force du mot de passe en temps réel
function checkPasswordStrength(password) {
    const strengthDiv = document.getElementById('passwordStrength');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const feedback = document.getElementById('passwordFeedback');
    
    if (password.length === 0) {
        strengthDiv.style.display = 'none';
        return;
    }
    
    strengthDiv.style.display = 'block';
    
    let score = 0;
    let messages = [];
    
    // Calcul du score
    if (password.length >= 12) score += 40;
    else if (password.length >= 8) score += 25;
    else if (password.length >= 6) score += 10;
    else messages.push('Trop court (minimum 6 caractères)');
    
    // Complexité
    if (/[a-z]/.test(password)) score += 10;
    else messages.push('Ajoutez des minuscules');
    
    if (/[A-Z]/.test(password)) score += 10;
    else messages.push('Ajoutez des majuscules');
    
    if (/[0-9]/.test(password)) score += 10;
    else messages.push('Ajoutez des chiffres');
    
    if (/[^a-zA-Z0-9]/.test(password)) score += 15;
    else messages.push('Ajoutez des symboles');
    
    // Pénalités
    if (/(.)\1{2,}/.test(password)) {
        score -= 10;
        messages.push('Évitez les répétitions');
    }
    
    if (/^(123|abc|qwe|azerty|password|admin|123456)/i.test(password)) {
        score -= 20;
        messages.push('Évitez les mots de passe courants');
    }
    
    score = Math.max(0, Math.min(100, score));
    
    // Mettre à jour la barre de force
    strengthBar.style.width = score + '%';
    
    // Changer la couleur selon le score
    if (score >= 80) {
        strengthBar.className = 'strength-fill strength-very-strong';
        strengthText.textContent = 'Très Fort';
        strengthText.style.color = '#4e73df';
    } else if (score >= 60) {
        strengthBar.className = 'strength-fill strength-strong';
        strengthText.textContent = 'Fort';
        strengthText.style.color = '#36b9cc';
    } else if (score >= 40) {
        strengthBar.className = 'strength-fill strength-good';
        strengthText.textContent = 'Bon';
        strengthText.style.color = '#1cc88a';
    } else if (score >= 20) {
        strengthBar.className = 'strength-fill strength-medium';
        strengthText.textContent = 'Moyen';
        strengthText.style.color = '#f6c23e';
    } else {
        strengthBar.className = 'strength-fill strength-weak';
        strengthText.textContent = 'Faible';
        strengthText.style.color = '#e74a3b';
    }
    
    // Afficher les conseils
    feedback.style.display = 'block';
    if (messages.length > 0 && score < 80) {
        feedback.textContent = '💡 ' + messages[0];
        feedback.className = 'password-feedback feedback-warning';
    } else if (score >= 80) {
        feedback.textContent = '✅ Excellent mot de passe !';
        feedback.className = 'password-feedback feedback-good';
    } else {
        feedback.textContent = '⚠️ Améliorez votre mot de passe';
        feedback.className = 'password-feedback feedback-error';
    }
}

// Générer un mot de passe avec IA
async function generateAIPassword(type = 'strong') {
    try {
        const length = document.getElementById('pwdLength').value;
        const includeUpper = document.getElementById('includeUpper').checked;
        const includeNumbers = document.getElementById('includeNumbers').checked;
        const includeSymbols = document.getElementById('includeSymbols').checked;
        
        // Afficher un indicateur de chargement
        document.getElementById('generatedPasswordText').innerHTML = 
            '<i class="fas fa-spinner fa-spin"></i> Génération IA en cours...';
        
        // Envoyer la requête au serveur
        const response = await fetch('generate_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'generate',
                length: parseInt(length),
                includeUpper: includeUpper,
                includeNumbers: includeNumbers,
                includeSymbols: includeSymbols,
                type: type
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentGeneratedPassword = data.password;
            passwordGenerated = true;
            
            // Afficher le mot de passe généré
            document.getElementById('generatedPasswordText').textContent = currentGeneratedPassword;
            document.getElementById('copyBtn').style.display = 'block';
            document.getElementById('passwordActions').style.display = 'flex';
            
            // Afficher l'indice si disponible
            if (data.hint) {
                document.getElementById('hintText').textContent = data.hint;
                document.getElementById('passwordHint').style.display = 'block';
            }
            
            // Vérifier la force
            checkPasswordStrength(currentGeneratedPassword);
            
        } else {
            document.getElementById('generatedPasswordText').textContent = 
                'Erreur : ' + (data.message || 'Impossible de générer le mot de passe');
        }
        
    } catch (error) {
        console.error('Erreur:', error);
        // Fallback côté client
        generateFallbackPassword();
    }
}

// Générer un mot de passe thématique
async function generateThemedPassword(theme) {
    try {
        document.getElementById('generatedPasswordText').innerHTML = 
            '<i class="fas fa-spinner fa-spin"></i> Génération du thème ' + theme + '...';
        
        const response = await fetch('generate_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'themed',
                theme: theme,
                length: 14
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentGeneratedPassword = data.password;
            passwordGenerated = true;
            
            document.getElementById('generatedPasswordText').textContent = currentGeneratedPassword;
            document.getElementById('copyBtn').style.display = 'block';
            document.getElementById('passwordActions').style.display = 'flex';
            
            // Mettre à jour l'indice avec le thème
            document.getElementById('hintText').textContent = 
                'Mot de passe généré avec le thème "' + theme + '"';
            document.getElementById('passwordHint').style.display = 'block';
            
            checkPasswordStrength(currentGeneratedPassword);
        }
        
    } catch (error) {
        console.error('Erreur:', error);
        generateFallbackPassword();
    }
}

// Copier le mot de passe généré
function copyGeneratedPassword() {
    if (!currentGeneratedPassword) return;
    
    navigator.clipboard.writeText(currentGeneratedPassword).then(() => {
        const copyBtn = document.getElementById('copyBtn');
        const originalHTML = copyBtn.innerHTML;
        
        copyBtn.innerHTML = '<i class="fas fa-check"></i> Copié!';
        copyBtn.style.background = '#1cc88a';
        
        setTimeout(() => {
            copyBtn.innerHTML = originalHTML;
            copyBtn.style.background = '#4e73df';
        }, 2000);
        
    }).catch(err => {
        alert('Erreur lors de la copie : ' + err);
    });
}

// Utiliser le mot de passe généré
function useGeneratedPassword() {
    if (!currentGeneratedPassword) return;
    
    document.getElementById('password').value = currentGeneratedPassword;
    document.getElementById('password').type = 'text';
    document.getElementById('toggleIcon').className = 'fas fa-eye-slash';
    
    // Vérifier la force
    checkPasswordStrength(currentGeneratedPassword);
    
    // Afficher un message de succès
    const useBtn = document.getElementById('usePasswordBtn');
    const originalHTML = useBtn.innerHTML;
    
    useBtn.innerHTML = '<i class="fas fa-check-double"></i> Utilisé!';
    useBtn.style.background = '#1cc88a';
    
    setTimeout(() => {
        useBtn.innerHTML = originalHTML;
        useBtn.style.background = '';
    }, 2000);
    
    // Fermer le générateur
    document.getElementById('passwordGenerator').style.display = 'none';
}

// Vérifier les fuites de mot de passe
async function checkPasswordLeak() {
    if (!currentGeneratedPassword) return;
    
    try {
        const leakResult = document.getElementById('leakCheckResult');
        leakResult.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Vérification des fuites en cours...';
        leakResult.style.display = 'block';
        leakResult.className = 'leak-check-result';
        
        const response = await fetch('generate_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'checkLeak',
                password: currentGeneratedPassword
            })
        });
        
        const data = await response.json();
        
        if (data.leaked) {
            leakResult.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${data.message}`;
            leakResult.className = 'leak-check-result leak-warning';
        } else {
            leakResult.innerHTML = `<i class="fas fa-shield-check"></i> ${data.message}`;
            leakResult.className = 'leak-check-result leak-safe';
        }
        
    } catch (error) {
        console.error('Erreur vérification fuite:', error);
        leakResult.innerHTML = 
            '<i class="fas fa-exclamation-circle"></i> Impossible de vérifier les fuites';
        leakResult.className = 'leak-check-result';
    }
}

// Fallback côté client
function generateFallbackPassword() {
    const length = document.getElementById('pwdLength').value;
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let password = '';
    
    for (let i = 0; i < length; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    currentGeneratedPassword = password;
    passwordGenerated = true;
    
    document.getElementById('generatedPasswordText').textContent = password;
    document.getElementById('copyBtn').style.display = 'block';
    document.getElementById('passwordActions').style.display = 'flex';
    
    document.getElementById('hintText').textContent = '💡 Mot de passe généré localement';
    document.getElementById('passwordHint').style.display = 'block';
    
    checkPasswordStrength(password);
}

// Validation du formulaire
function validateForm() {
    const password = document.getElementById('password').value;
    
    if (password.length < 6) {
        alert('Le mot de passe doit contenir au moins 6 caractères.');
        document.getElementById('password').focus();
        return false;
    }
    
    // Vérifier la force si l'utilisateur n'a pas généré de mot de passe
    if (!passwordGenerated) {
        const strengthText = document.getElementById('strengthText').textContent;
        if (strengthText === 'Faible' || strengthText === 'Très Faible') {
            if (!confirm('Votre mot de passe semble faible. Souhaitez-vous utiliser notre générateur IA pour en créer un plus sécurisé ?')) {
                return true; // L'utilisateur veut continuer malgré tout
            } else {
                showPasswordGenerator();
                return false;
            }
        }
    }
    
    return true;
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier la force du mot de passe initial s'il y en a un
    const initialPassword = document.getElementById('password').value;
    if (initialPassword) {
        checkPasswordStrength(initialPassword);
    }
});
</script>

</body>
</html>