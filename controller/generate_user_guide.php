<?php
/**
 * Generate User Guide PDF
 * Public access - no login required
 */

require_once '../config.php';

// Generate user guide HTML content
function generateUserGuideHTML() {
    $content = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Guide Utilisateur - SAFEProject</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
            }
            .header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 30px;
                text-align: center;
                border-radius: 10px;
                margin-bottom: 30px;
            }
            .header h1 {
                margin: 0;
                font-size: 32px;
            }
            .header p {
                margin: 10px 0 0 0;
                font-size: 16px;
                opacity: 0.9;
            }
            .section {
                margin-bottom: 30px;
                padding: 20px;
                background: #f8f9fa;
                border-left: 4px solid #667eea;
                border-radius: 5px;
            }
            .section h2 {
                color: #667eea;
                margin-top: 0;
                font-size: 24px;
            }
            .section h3 {
                color: #764ba2;
                font-size: 18px;
                margin-top: 20px;
            }
            .step {
                margin: 15px 0;
                padding-left: 20px;
            }
            .step-number {
                display: inline-block;
                width: 30px;
                height: 30px;
                background: #667eea;
                color: white;
                border-radius: 50%;
                text-align: center;
                line-height: 30px;
                font-weight: bold;
                margin-right: 10px;
            }
            .info-box {
                background: #e7f3ff;
                border-left: 4px solid #0dcaf0;
                padding: 15px;
                margin: 15px 0;
                border-radius: 5px;
            }
            .warning-box {
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 15px;
                margin: 15px 0;
                border-radius: 5px;
            }
            .feature-list {
                list-style: none;
                padding: 0;
            }
            .feature-list li {
                padding: 10px 0;
                border-bottom: 1px solid #dee2e6;
            }
            .feature-list li:before {
                content: "✓ ";
                color: #25d49d;
                font-weight: bold;
                margin-right: 10px;
            }
            .footer {
                text-align: center;
                margin-top: 40px;
                padding-top: 20px;
                border-top: 2px solid #dee2e6;
                color: #6c757d;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>📚 Guide Utilisateur SAFEProject</h1>
            <p>Plateforme de Support Psychologique en Ligne</p>
            <p style="font-size: 14px; margin-top: 15px;">Version 1.0 - ' . date('d/m/Y') . '</p>
        </div>

        <div class="section">
            <h2>🎯 Bienvenue sur SAFEProject</h2>
            <p>
                SAFEProject est une plateforme dédiée au soutien psychologique en ligne. 
                Notre mission est de vous connecter avec des conseillers professionnels 
                pour vous accompagner dans vos démarches de bien-être mental.
            </p>
            <div class="info-box">
                <strong>Note importante :</strong> Toutes vos informations sont traitées de manière 
                strictement confidentielle et sécurisée.
            </div>
        </div>

        <div class="section">
            <h2>🚀 Démarrage Rapide</h2>
            
            <h3>1. Création de compte</h3>
            <div class="step">
                <span class="step-number">1</span>
                Cliquez sur "CRÉER UN COMPTE" sur la page d\'accueil
            </div>
            <div class="step">
                <span class="step-number">2</span>
                Remplissez le formulaire avec vos informations (nom, prénom, email, mot de passe)
            </div>
            <div class="step">
                <span class="step-number">3</span>
                Validez votre inscription
            </div>
            
            <h3>2. Connexion</h3>
            <div class="step">
                <span class="step-number">1</span>
                Cliquez sur "SE CONNECTER"
            </div>
            <div class="step">
                <span class="step-number">2</span>
                Entrez votre email et mot de passe
            </div>
            <div class="step">
                <span class="step-number">3</span>
                Accédez à votre tableau de bord
            </div>
        </div>

        <div class="section">
            <h2>💬 Créer une Demande de Soutien</h2>
            
            <div class="step">
                <span class="step-number">1</span>
                Depuis votre tableau de bord, cliquez sur "Nouvelle demande"
            </div>
            <div class="step">
                <span class="step-number">2</span>
                Remplissez le formulaire :
                <ul>
                    <li><strong>Titre :</strong> Résumé court de votre demande</li>
                    <li><strong>Description :</strong> Expliquez votre situation en détail</li>
                    <li><strong>Urgence :</strong> Basse, Moyenne ou Haute</li>
                </ul>
            </div>
            <div class="step">
                <span class="step-number">3</span>
                Soumettez votre demande
            </div>
            
            <div class="warning-box">
                <strong>⏱️ Délai de traitement :</strong> Un conseiller sera assigné à votre demande 
                dans les 24-48 heures. Vous recevrez une notification par email.
            </div>
        </div>

        <div class="section">
            <h2>📊 Suivi de vos Demandes</h2>
            
            <h3>Statuts des demandes :</h3>
            <ul class="feature-list">
                <li><strong>En attente :</strong> Votre demande a été reçue et attend l\'assignation d\'un conseiller</li>
                <li><strong>Assignée :</strong> Un conseiller a été assigné à votre demande</li>
                <li><strong>En cours :</strong> Le conseiller a commencé à traiter votre demande</li>
                <li><strong>Terminée :</strong> Votre demande a été résolue</li>
            </ul>
        </div>

        <div class="section">
            <h2>💭 Communication avec votre Conseiller</h2>
            
            <div class="step">
                <span class="step-number">1</span>
                Accédez à votre demande depuis le tableau de bord
            </div>
            <div class="step">
                <span class="step-number">2</span>
                Utilisez la zone de messagerie pour échanger avec votre conseiller
            </div>
            <div class="step">
                <span class="step-number">3</span>
                Recevez des réponses et conseils personnalisés
            </div>
            
            <div class="info-box">
                <strong>💡 Conseil :</strong> Soyez aussi détaillé que possible dans vos messages 
                pour permettre à votre conseiller de mieux vous aider.
            </div>
        </div>

        <div class="section">
            <h2>📥 Télécharger un Résumé PDF</h2>
            
            <p>Une fois votre demande terminée ou en cours, vous pouvez télécharger un résumé PDF :</p>
            
            <div class="step">
                <span class="step-number">1</span>
                Ouvrez votre demande
            </div>
            <div class="step">
                <span class="step-number">2</span>
                Cliquez sur le bouton rouge "Télécharger le PDF"
            </div>
            <div class="step">
                <span class="step-number">3</span>
                Le PDF contient l\'historique complet de vos échanges
            </div>
        </div>

        <div class="section">
            <h2>🔒 Confidentialité et Sécurité</h2>
            
            <ul class="feature-list">
                <li>Toutes vos données sont cryptées et sécurisées</li>
                <li>Les conseillers sont tenus au secret professionnel</li>
                <li>Vous pouvez supprimer votre compte à tout moment</li>
                <li>Aucune information n\'est partagée avec des tiers</li>
            </ul>
        </div>

        <div class="section">
            <h2>❓ Questions Fréquentes</h2>
            
            <h3>Combien coûte le service ?</h3>
            <p>Le service est actuellement gratuit pour tous les utilisateurs.</p>
            
            <h3>Puis-je choisir mon conseiller ?</h3>
            <p>Les conseillers sont assignés automatiquement en fonction de leur spécialité et disponibilité.</p>
            
            <h3>Combien de temps avant d\'avoir une réponse ?</h3>
            <p>Les conseillers répondent généralement dans les 24 heures.</p>
            
            <h3>Mes conversations sont-elles privées ?</h3>
            <p>Oui, toutes les conversations sont strictement confidentielles.</p>
        </div>

        <div class="section">
            <h2>📞 Besoin d\'Aide ?</h2>
            
            <p>Si vous rencontrez des difficultés ou avez des questions :</p>
            <ul>
                <li>📧 Email : support@safeproject.com</li>
                <li>📱 Téléphone : +33 1 23 45 67 89</li>
                <li>🕐 Disponibilité : Lundi - Vendredi, 9h - 18h</li>
            </ul>
        </div>

        <div class="footer">
            <p><strong>SAFEProject</strong> - Plateforme de Support Psychologique</p>
            <p>© ' . date('Y') . ' - Tous droits réservés</p>
            <p style="font-size: 12px; margin-top: 10px;">
                Document généré le ' . date('d/m/Y à H:i') . '
            </p>
        </div>
    </body>
    </html>';
    
    return $content;
}

// Generate and serve the PDF
$htmlContent = generateUserGuideHTML();

// Set headers for HTML download
header('Content-Type: text/html; charset=UTF-8');
header('Content-Disposition: attachment; filename="Guide_Utilisateur_SAFEProject_' . date('Y-m-d') . '.html"');
header('Content-Length: ' . strlen($htmlContent));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Output HTML content
echo $htmlContent;
exit();
?>
