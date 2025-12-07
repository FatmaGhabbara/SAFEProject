<?php
// C:/xampp/htdocs/SAFEProject/windows-hello.php
session_start();

// Simuler une base utilisateur
$users = [
    'admin@example.com' => [
        'id' => 1,
        'name' => 'Administrateur',
        'password' => 'admin123',
        'has_windows_hello' => true
    ],
    'user@example.com' => [
        'id' => 2,
        'name' => 'Utilisateur Test',
        'password' => 'user123',
        'has_windows_hello' => false
    ]
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Windows Hello - SafeSpace</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0078d4;
            color: white;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .container {
            background: white;
            color: #333;
            border-radius: 10px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            text-align: center;
        }
        
        .windows-logo {
            color: #0078d4;
            font-size: 60px;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #005a9e;
            margin-bottom: 10px;
        }
        
        .status {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: bold;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }
        
        .btn {
            background: #0078d4;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin: 10px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #106ebe;
        }
        
        .btn-success {
            background: #107c10;
        }
        
        .btn-success:hover {
            background: #0e6b0e;
        }
        
        .login-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        input:focus {
            border-color: #0078d4;
            outline: none;
        }
        
        .help-link {
            margin-top: 20px;
            font-size: 14px;
        }
        
        .help-link a {
            color: #0078d4;
            text-decoration: none;
        }
        
        .help-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="windows-logo">
            <i class="fab fa-windows"></i>
        </div>
        
        <h1>Windows Hello</h1>
        <p>Connectez-vous avec votre empreinte digitale, reconnaissance faciale ou PIN Windows</p>
        
        <div class="status" id="compatibility-check">
            Vérification de Windows Hello...
        </div>
        
        <div class="info">
            <p><strong>Pour utiliser Windows Hello :</strong></p>
            <ol style="padding-left: 20px; margin-top: 10px;">
                <li>Votre PC doit être connecté à un compte Microsoft</li>
                <li>Windows Hello doit être configuré dans Paramètres Windows</li>
                <li>Vous devez avoir un PIN Windows ou empreinte digitale configuré</li>
            </ol>
        </div>
        
        <div id="windows-hello-section" style="display: none;">
            <button class="btn btn-success" onclick="startWindowsHello()" id="windows-hello-btn">
                <i class="fas fa-fingerprint"></i> Se connecter avec Windows Hello
            </button>
            
            <div id="auth-status" style="display: none; margin-top: 20px;">
                <div style="width: 40px; height: 40px; border: 3px solid #f3f3f3; border-top: 3px solid #0078d4; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                <p style="margin-top: 10px;">Authentification en cours...</p>
            </div>
        </div>
        
        <div class="login-form" id="manual-login">
            <h3 style="color: #005a9e; margin-bottom: 15px;">
                <i class="fas fa-key"></i> Connexion manuelle
            </h3>
            
            <form id="login-form" onsubmit="return manualLogin()">
                <div class="form-group">
                    <label for="email">Email :</label>
                    <input type="email" id="email" required placeholder="votre@email.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe :</label>
                    <input type="password" id="password" required placeholder="Votre mot de passe">
                </div>
                
                <button type="submit" class="btn" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
            </form>
        </div>
        
        <div class="help-link">
            <a href="#" onclick="showHelp()">
                <i class="fas fa-question-circle"></i> Aide : Configurer Windows Hello
            </a>
        </div>
        
        <div class="message" id="message"></div>
        
        <div style="margin-top: 30px;">
            <a href="login.php" style="color: #0078d4; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Retour à la connexion normale
            </a>
        </div>
    </div>
    
    <script>
        // Vérifier la compatibilité Windows Hello
        function checkWindowsHello() {
            const checkEl = document.getElementById('compatibility-check');
            
            // Vérifier si c'est Windows
            const isWindows = navigator.userAgent.includes('Windows');
            
            // Vérifier WebAuthn
            const hasWebAuthn = !!(navigator.credentials && navigator.credentials.create);
            
            if (isWindows && hasWebAuthn) {
                checkEl.innerHTML = '<i class="fas fa-check-circle"></i> Windows Hello est disponible';
                checkEl.className = 'status success';
                document.getElementById('windows-hello-section').style.display = 'block';
                return true;
            } else if (!isWindows) {
                checkEl.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Vous n\'êtes pas sur Windows';
                checkEl.className = 'status error';
                return false;
            } else {
                checkEl.innerHTML = '<i class="fas fa-times-circle"></i> Windows Hello non disponible';
                checkEl.className = 'status error';
                return false;
            }
        }
        
        // Démarrer Windows Hello
        async function startWindowsHello() {
            try {
                document.getElementById('windows-hello-btn').style.display = 'none';
                document.getElementById('auth-status').style.display = 'block';
                
                // Demander l'email pour identifier l'utilisateur
                const email = prompt('Entrez votre email pour Windows Hello :');
                if (!email) {
                    resetWindowsHelloUI();
                    return;
                }
                
                // Préparer les options pour Windows Hello
                const challenge = new Uint8Array(32);
                crypto.getRandomValues(challenge);
                
                const publicKey = {
                    challenge: challenge,
                    rp: {
                        name: 'SafeSpace',
                        id: window.location.hostname
                    },
                    user: {
                        id: new Uint8Array(16),
                        name: email,
                        displayName: email
                    },
                    pubKeyCredParams: [
                        { type: 'public-key', alg: -7 },  // ES256
                        { type: 'public-key', alg: -257 } // RS256
                    ],
                    timeout: 60000,
                    attestation: 'direct',
                    authenticatorSelection: {
                        authenticatorAttachment: 'platform', // Pour Windows Hello
                        userVerification: 'required'
                    }
                };
                
                // Appeler Windows Hello
                const credential = await navigator.credentials.create({ publicKey });
                
                // Simuler la connexion réussie
                showMessage('✅ Windows Hello authentifié avec succès !', 'success');
                
                // Rediriger vers le tableau de bord
                setTimeout(() => {
                    window.location.href = 'index.php?windowshello=success';
                }, 2000);
                
            } catch (error) {
                console.error('Windows Hello error:', error);
                
                if (error.name === 'NotAllowedError') {
                    showMessage('❌ Windows Hello a été annulé', 'error');
                } else if (error.name === 'NotSupportedError') {
                    showMessage('❌ Windows Hello non supporté sur ce compte', 'error');
                    showManualHelp();
                } else {
                    showMessage('❌ Erreur : ' + error.message, 'error');
                }
                
                resetWindowsHelloUI();
            }
        }
        
        // Connexion manuelle
        function manualLogin() {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            // Simulation - en réalité, vérifier en base de données
            if (email && password) {
                showMessage('🔐 Connexion en cours...', 'success');
                
                // Redirection après 2 secondes
                setTimeout(() => {
                    window.location.href = 'index.php?login=success';
                }, 2000);
            } else {
                showMessage('❌ Veuillez remplir tous les champs', 'error');
            }
            
            return false; // Empêcher le rechargement
        }
        
        // Afficher l'aide pour configurer Windows Hello
        function showHelp() {
            alert(`Pour configurer Windows Hello :
            
1. Cliquez sur Démarrer → Paramètres
2. Allez dans "Comptes" → "Options de connexion"
3. Cliquez sur "Windows Hello"
4. Suivez les instructions pour configurer :
   - Empreinte digitale
   - Reconnaissance faciale
   - PIN Windows
   
5. Assurez-vous d'être connecté avec un compte Microsoft
6. Redémarrez votre navigateur et réessayez`);
            
            return false;
        }
        
        // Afficher aide pour compte sans Windows Hello
        function showManualHelp() {
            alert(`Problème de compte Microsoft :
            
Votre compte Windows n'a pas accès à Windows Hello.

Solutions :
1. Connectez-vous avec un compte Microsoft sur Windows
2. Configurez un PIN Windows :
   - Paramètres → Comptes → Options de connexion
   - Cliquez sur "PIN Windows Hello"
   - Cliquez sur "Ajouter"
   
3. OU utilisez la connexion par mot de passe ci-dessous`);
        }
        
        // Afficher un message
        function showMessage(text, type) {
            const msgEl = document.createElement('div');
            msgEl.className = 'status ' + (type === 'success' ? 'success' : 'error');
            msgEl.innerHTML = text;
            
            const container = document.querySelector('.container');
            const existingMsg = document.getElementById('message');
            if (existingMsg) existingMsg.remove();
            
            msgEl.id = 'message';
            container.appendChild(msgEl);
            
            setTimeout(() => {
                if (msgEl.parentNode) msgEl.remove();
            }, 5000);
        }
        
        // Réinitialiser l'UI Windows Hello
        function resetWindowsHelloUI() {
            document.getElementById('windows-hello-btn').style.display = 'block';
            document.getElementById('auth-status').style.display = 'none';
        }
        
        // Vérifier au chargement
        document.addEventListener('DOMContentLoaded', function() {
            checkWindowsHello();
            
            // Ajouter animation CSS
            const style = document.createElement('style');
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        });
    </script>
    
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/brands.min.js"></script>
</body>
</html>
