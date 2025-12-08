

> **Plateforme web moderne de gestion du support psychologique** permettant aux utilisateurs de créer des demandes d'aide et d'être accompagnés par des conseillers professionnels qualifiés.

---

## 📋 Table des Matières

1. [🎯 Vue d'ensemble](#-vue-densemble)
2. [✨ Fonctionnalités Principales](#-fonctionnalités-principales)
3. [🏗️ Architecture du Projet](#️-architecture-du-projet)
4. [🗄️ Base de Données](#️-base-de-données)
5. [👥 Acteurs et Permissions](#-acteurs-et-permissions)
6. [🔧 Fonctionnalités Détaillées](#-fonctionnalités-détaillées)
7. [💻 Structure du Code](#-structure-du-code)
8. [🔍 Concepts Techniques](#-concepts-techniques)
9. [🚀 Installation](#-installation)
10. [📊 Statistiques et Rapports](#-statistiques-et-rapports)
11. [🔒 Sécurité](#-sécurité)
12. [❓ FAQ & Validation](#-faq--validation)

---

## 🎯 Vue d'ensemble

**SAFEProject** est une plateforme web complète de support psychologique conçue pour faciliter la communication entre les utilisateurs en détresse et des conseillers professionnels qualifiés. Le système offre un environnement sécurisé pour créer, gérer et suivre des demandes de soutien psychologique avec un système de messagerie intégré.

### 🎨 Caractéristiques Clés
- ✅ **Interface Moderne & Responsive** - Design adaptatif pour tous les appareils
- ✅ **Système de Rôles Avancé** - Gestion granulaire des permissions (User/Counselor/Admin)
- ✅ **Messagerie en Temps Réel** - Communication bidirectionnelle entre utilisateurs et conseillers
- ✅ **Gestion Automatisée** - Triggers SQL pour automatisation des workflows
- ✅ **Export PDF** - Génération de rapports détaillés des conversations
- ✅ **Statistiques Avancées** - Tableaux de bord avec métriques de performance
- ✅ **Sécurité Renforcée** - Protection CSRF, hashage bcrypt, sanitization complète

### 🛠️ Stack Technique
| Composant | Technologie | Version |
|-----------|-------------|---------|
| **Backend** | PHP (POO) | 7.4+ |
| **Base de données** | MySQL/MariaDB | 5.7+ |
| **Frontend** | HTML5, CSS3, JavaScript | - |
| **Framework CSS** | Bootstrap | 5.3 |
| **Architecture** | MVC Pattern | - |
| **PDO** | Prepared Statements | - |
| **Session Management** | PHP Sessions | - |

---

## ✨ Fonctionnalités Principales

### 🔐 Authentification & Gestion des Comptes
- **Inscription/Connexion sécurisée** avec validation email
- **Gestion de profil complète** (modification nom, email, mot de passe)
- **Profils conseillers enrichis** (spécialité, biographie, disponibilité)
- **Système de rôles** avec permissions granulaires
- **Gestion de session** avec timeout automatique

### 📝 Gestion des Demandes de Support
- **Création de demandes** avec titre, description et niveau d'urgence
- **Suivi en temps réel** du statut des demandes
- **Annulation de demandes** en attente
- **Suppression de demandes** (même après assignation)
- **Historique complet** de toutes les interactions
- **Filtrage et recherche** par statut, urgence, date

### 💬 Système de Messagerie
- **Chat bidirectionnel** entre utilisateur et conseiller
- **Envoi de messages** avec validation de contenu
- **Modification de messages** (avant clôture de la demande)
- **Suppression de messages** (propres messages uniquement)
- **Indicateur de lecture** pour les messages non lus
- **Messages automatiques** lors de l'assignation

### 👨‍⚕️ Espace Conseiller
- **Dashboard personnalisé** avec statistiques
- **Vue des demandes assignées** uniquement
- **Gestion de disponibilité** (actif/inactif/en pause)
- **Démarrage de conversations** (changement statut assignée → en_cours)
- **Clôture de demandes** avec notes finales
- **Compteur de demandes actives** mis à jour automatiquement

### 👑 Panneau d'Administration
- **Vue globale** de toutes les demandes
- **Assignation de conseillers** avec notes administratives
- **Gestion des conseillers** (création, modification, suppression)
- **Gestion des utilisateurs** (visualisation, suppression)
- **Statistiques complètes** (performance, temps de résolution)
- **Logs d'activité** pour audit

### 📊 Rapports & Exports
- **Export PDF** des conversations complètes
- **Statistiques par conseiller** (demandes traitées, temps moyen)
- **Rapports d'urgence** par niveau de priorité
- **Métriques de performance** (taux de résolution, délais)

---

## 🏗️ Architecture du Projet

### 📂 Structure des Dossiers

```
SAFEProject/
├── 📄 config.php                      # Configuration globale & helpers
├── 📄 index.php                       # Point d'entrée de l'application
├── 📄 setup_database.sh               # Script d'installation automatique
│
├── 📁 model/                          # Couche Modèle (Entités)
│   ├── User.php                      # Gestion des utilisateurs (tous rôles)
│   ├── SupportRequest.php            # Gestion des demandes de support
│   └── SupportMessage.php            # Gestion des messages
│
├── 📁 controller/                     # Couche Contrôleur (Logique métier)
│   ├── helpers.php                   # Fonctions utilitaires globales
│   ├── generate_user_guide.php       # Génération de documentation
│   │
│   ├── 📁 auth/                      # Authentification
│   │   ├── login.php                # Connexion utilisateur
│   │   ├── logout.php               # Déconnexion
│   │   ├── register.php             # Inscription
│   │   └── update_profile.php       # Modification de profil
│   │
│   └── 📁 support/                   # Module Support Psychologique
│       ├── create_request.php       # Création de demande (USER)
│       ├── cancel_request.php       # Annulation de demande (USER)
│       ├── user_delete_request.php  # Suppression de demande (USER)
│       │
│       ├── send_message.php         # Envoi de message
│       ├── update_message.php       # Modification de message
│       ├── delete_message.php       # Suppression de message
│       │
│       ├── counselor_start_request.php      # Démarrer conversation (COUNSELOR)
│       ├── counselor_complete_request.php   # Terminer demande (COUNSELOR)
│       ├── counselor_toggle_availability.php # Gestion disponibilité
│       │
│       ├── admin_assign_counselor.php       # Assignation (ADMIN)
│       ├── admin_create_counselor.php       # Création conseiller (ADMIN)
│       ├── admin_update_counselor.php       # Modification conseiller (ADMIN)
│       ├── admin_delete_counselor.php       # Suppression conseiller (ADMIN)
│       ├── admin_delete_request.php         # Suppression demande (ADMIN)
│       ├── admin_delete_user.php            # Suppression utilisateur (ADMIN)
│       └── generate_pdf.php                 # Export PDF
│
├── 📁 view/                           # Couche Vue (Interface utilisateur)
│   ├── dashboard.php                 # Redirection selon rôle
│   │
│   ├── 📁 frontoffice/               # Interface Utilisateurs
│   │   ├── login.php                # Page de connexion
│   │   ├── register.php             # Page d'inscription
│   │   ├── dashboard.php            # Tableau de bord utilisateur
│   │   ├── profil.php               # Gestion du profil
│   │   │
│   │   └── 📁 support/
│   │       ├── support_info.php     # Informations sur le support
│   │       ├── support_form.php     # Formulaire de demande
│   │       ├── my_requests.php      # Liste des demandes
│   │       └── request_details.php  # Détails & conversation
│   │
│   ├── 📁 backoffice/                # Interface Admin/Conseillers
│   │   └── 📁 support/
│   │       ├── dashboard_counselor.php      # Dashboard conseiller
│   │       ├── my_assigned_requests.php     # Demandes assignées
│   │       ├── request_conversation.php     # Conversation détaillée
│   │       │
│   │       ├── support_requests.php         # Toutes les demandes (ADMIN)
│   │       ├── assign_counselor.php         # Formulaire d'assignation
│   │       │
│   │       ├── counselors_list.php          # Liste des conseillers
│   │       ├── add_counselor.php            # Ajout conseiller
│   │       ├── edit_counselor.php           # Modification conseiller
│   │       ├── view_counselor.php           # Détails conseiller
│   │       ├── counselor_stats.php          # Statistiques conseillers
│   │       │
│   │       ├── users_list.php               # Liste des utilisateurs
│   │       └── view_user.php                # Détails utilisateur
│   │
│   └── 📁 includes/
│       └── navbar.php                # Barre de navigation responsive
│
├── 📁 database/
│   └── init_complete.sql             # Script SQL complet (tables + triggers + vues)
│
└── 📁 logs/                          # Journaux d'activité
    └── support_module_YYYY-MM-DD.log # Logs quotidiens

```

### 🎯 Pattern MVC Implémenté

```
┌─────────────┐      ┌──────────────┐      ┌─────────────┐
│    VIEW     │─────▶│  CONTROLLER  │─────▶│    MODEL    │
│  (Interface)│      │   (Logique)  │      │  (Données)  │
└─────────────┘      └──────────────┘      └─────────────┘
      ▲                      │                      │
      │                      ▼                      ▼
      │              ┌──────────────┐      ┌─────────────┐
      └──────────────│   Redirect   │      │  Database   │
                     └──────────────┘      └─────────────┘
```

---

## 🗄️ Base de Données

### 📊 Schéma Relationnel

Le système utilise **3 tables principales** avec une architecture optimisée :

```
┌─────────────────────────────────────────────────────────┐
│                    utilisateurs                         │
│  (Table unifiée pour tous les types d'utilisateurs)    │
├─────────────────────────────────────────────────────────┤
│ • id (PK)                                               │
│ • nom, prenom, email, password                          │
│ • role (user/admin/counselor)                           │
│ • statut (actif/inactif/suspendu)                       │
│ • specialite, biographie (counselors uniquement)        │
│ • disponibilite, nombre_demandes_actives                │
└─────────────────────────────────────────────────────────┘
           │                                    │
           │ 1:N                                │ 1:N
           ▼                                    ▼
┌──────────────────────────┐      ┌──────────────────────────┐
│   support_requests       │      │   support_messages       │
├──────────────────────────┤      ├──────────────────────────┤
│ • id (PK)                │      │ • id (PK)                │
│ • user_id (FK)           │◀─────│ • support_request_id (FK)│
│ • counselor_user_id (FK) │      │ • sender_id (FK)         │
│ • titre, description     │      │ • message                │
│ • urgence, statut        │      │ • date_envoi, lu         │
│ • dates (création, etc.) │      └──────────────────────────┘
└──────────────────────────┘
```

#### 1. Table `utilisateurs`
Contient **TOUS** les utilisateurs (users, counselors, admins) dans une seule table unifiée.

```sql
CREATE TABLE utilisateurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin', 'counselor') DEFAULT 'user',
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif',
    -- Colonnes spécifiques aux conseillers (NULL si role != 'counselor')
    specialite VARCHAR(255) DEFAULT NULL,
    biographie TEXT DEFAULT NULL,
    disponibilite BOOLEAN DEFAULT NULL,
    nombre_demandes_actives INT DEFAULT 0,
    statut_counselor ENUM('actif', 'inactif', 'en_pause') DEFAULT NULL
);
```

**Points clés** :
- **Table unifiée** : Un seul endroit pour tous les types d'utilisateurs
- **Colonnes conditionnelles** : Les champs `specialite`, `biographie`, etc. sont NULL pour les non-conseillers
- **Rôle déterminant** : Le champ `role` détermine les permissions et fonctionnalités

#### 2. Table `support_requests`
Stocke les demandes de support créées par les utilisateurs.

```sql
CREATE TABLE support_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    counselor_user_id INT DEFAULT NULL,
    titre VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    urgence ENUM('basse', 'moyenne', 'haute') DEFAULT 'moyenne',
    statut ENUM('en_attente', 'assignee', 'en_cours', 'terminee', 'annulee') DEFAULT 'en_attente',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_assignation DATETIME DEFAULT NULL,
    date_resolution DATETIME DEFAULT NULL,
    notes_admin TEXT,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (counselor_user_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
);
```

**Relations** :
- `user_id` → Créateur de la demande (utilisateur)
- `counselor_user_id` → Conseiller assigné (peut être NULL)
- **ON DELETE CASCADE** : Si l'utilisateur est supprimé, ses demandes sont supprimées
- **ON DELETE SET NULL** : Si le conseiller est supprimé, l'assignation devient NULL

#### 3. Table `support_messages`
Messages échangés dans le cadre d'une demande.

```sql
CREATE TABLE support_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    support_request_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    lu BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (support_request_id) REFERENCES support_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);
```

**Relations** :
- `support_request_id` → Demande associée
- `sender_id` → Expéditeur du message
- **ON DELETE CASCADE** : Si la demande ou l'utilisateur est supprimé, les messages sont supprimés

### 🔍 Vues SQL Optimisées

#### `v_counselor_stats` - Statistiques des Conseillers
Vue agrégée pour le suivi de performance des conseillers :
```sql
SELECT 
    u.id, u.nom, u.prenom, u.specialite,
    u.nombre_demandes_actives,
    COUNT(DISTINCT sr.id) as total_demandes,
    COUNT(DISTINCT CASE WHEN sr.statut = 'terminee' THEN sr.id END) as demandes_terminees,
    AVG(TIMESTAMPDIFF(HOUR, sr.date_assignation, sr.date_resolution)) as temps_resolution_moyen_heures
FROM utilisateurs u
LEFT JOIN support_requests sr ON u.id = sr.counselor_user_id
WHERE u.role = 'counselor'
GROUP BY u.id;
```

**Métriques disponibles :**
- Nombre total de demandes assignées
- Nombre de demandes terminées
- Temps de résolution moyen en heures
- Nombre de demandes actives en temps réel

#### `v_support_requests_full` - Vue Complète des Demandes
Vue dénormalisée pour affichage rapide avec toutes les informations :
```sql
SELECT 
    sr.*, 
    u.nom as user_nom, u.prenom as user_prenom, u.email as user_email,
    uc.nom as counselor_nom, uc.prenom as counselor_prenom, uc.specialite
FROM support_requests sr
INNER JOIN utilisateurs u ON sr.user_id = u.id
LEFT JOIN utilisateurs uc ON sr.counselor_user_id = uc.id;
```

### ⚡ Triggers Automatiques (Automation)

| Trigger | Événement | Action |
|---------|-----------|--------|
| **`tr_increment_active_requests`** | Assignation d'un conseiller | Incrémente `nombre_demandes_actives` du conseiller |
| **`tr_decrement_active_requests`** | Demande terminée/annulée | Décrémente `nombre_demandes_actives` du conseiller |
| **`tr_set_date_assignation`** | Assignation d'un conseiller | Met à jour automatiquement `date_assignation` |
| **`tr_set_date_resolution`** | Demande terminée | Met à jour automatiquement `date_resolution` |

**Avantages :**
- ✅ Cohérence des données garantie
- ✅ Pas de code PHP nécessaire pour ces opérations
- ✅ Performance optimale (niveau base de données)
- ✅ Impossible d'oublier de mettre à jour les compteurs

### 🔗 Relations & Contraintes d'Intégrité

| Relation | Type | Action ON DELETE |
|----------|------|------------------|
| `support_requests.user_id` → `utilisateurs.id` | 1:N | **CASCADE** - Supprime toutes les demandes de l'utilisateur |
| `support_requests.counselor_user_id` → `utilisateurs.id` | 1:N | **SET NULL** - Conserve la demande, retire l'assignation |
| `support_messages.support_request_id` → `support_requests.id` | 1:N | **CASCADE** - Supprime tous les messages de la demande |
| `support_messages.sender_id` → `utilisateurs.id` | 1:N | **CASCADE** - Supprime tous les messages de l'utilisateur |

### 📈 Index de Performance

```sql
-- Optimisation des requêtes fréquentes
INDEX idx_email ON utilisateurs(email);
INDEX idx_role ON utilisateurs(role);
INDEX idx_statut ON support_requests(statut);
INDEX idx_urgence ON support_requests(urgence);
INDEX idx_date_creation ON support_requests(date_creation);
INDEX idx_lu ON support_messages(lu);
```

---

## 👥 Acteurs et Permissions

### 1. 👤 USER (Utilisateur/Patient)

**Peut :**
- ✅ **Créer** des demandes de support
- ✅ **Voir** ses propres demandes
- ✅ **Modifier** ses propres messages (avant que la demande soit terminée)
- ✅ **Supprimer** ses propres messages (avant que la demande soit terminée)
- ✅ **Supprimer** ses propres demandes (à tout moment, même après assignation)
- ✅ **Annuler** ses demandes en attente
- ✅ **Modifier** son profil (nom, prénom, email, mot de passe)
- ✅ **Envoyer** des messages dans ses demandes assignées

**Ne peut pas :**
- ❌ Voir les demandes d'autres utilisateurs
- ❌ Assigner des conseillers
- ❌ Modifier les demandes d'autres utilisateurs
- ❌ Voir les statistiques des conseillers

### 2. 👨‍⚕️ COUNSELOR (Conseiller)

**Peut :**
- ✅ **Voir** les demandes qui lui sont assignées
- ✅ **Commencer** une conversation (changer le statut de "assignee" à "en_cours")
- ✅ **Envoyer** des messages dans ses demandes assignées
- ✅ **Modifier** ses propres messages
- ✅ **Supprimer** ses propres messages
- ✅ **Terminer** une demande (marquer comme "terminee")
- ✅ **Modifier** son profil (nom, prénom, email, mot de passe, spécialité, biographie)
- ✅ **Basculer** sa disponibilité

**Ne peut pas :**
- ❌ Créer des demandes
- ❌ Assigner d'autres conseillers
- ❌ Voir les demandes non assignées
- ❌ Supprimer des demandes

### 3. 👑 ADMIN (Administrateur)

**Peut :**
- ✅ **Tout ce que USER et COUNSELOR peuvent faire**
- ✅ **Voir** toutes les demandes (assignées et non assignées)
- ✅ **Assigner** des conseillers aux demandes
- ✅ **Créer** des conseillers
- ✅ **Modifier** les conseillers
- ✅ **Supprimer** les conseillers (si pas de demandes actives)
- ✅ **Supprimer** les utilisateurs
- ✅ **Supprimer** les demandes
- ✅ **Voir** les statistiques complètes
- ✅ **Ajouter des notes** lors de l'assignation

**Ne peut pas :**
- ❌ Modifier son rôle (doit rester admin)
- ❌ Se supprimer lui-même

---

## 🔧 Fonctionnalités Détaillées

### 📝 Gestion des Demandes

#### Création de Demande (`create_request.php`)
- **Acteur** : USER
- **Champs requis** : Titre, Description, Niveau d'urgence
- **Statut initial** : `en_attente`
- **Validation** : Titre min 5 caractères, Description min 20 caractères

#### Suppression de Demande (`user_delete_request.php`)
- **Acteur** : USER (propre demande uniquement)
- **Règles** : Peut supprimer à tout moment (même après assignation)
- **Effet** : Supprime la demande ET tous les messages associés (CASCADE)
- **Visibilité** : La demande disparaît aussi pour le conseiller

#### Annulation de Demande (`cancel_request.php`)
- **Acteur** : USER (propre demande uniquement)
- **Règles** : Uniquement si statut = `en_attente`
- **Effet** : Change le statut à `annulee` (ne supprime pas)

### 💬 Gestion des Messages

#### Envoi de Message (`send_message.php`)
- **Acteurs** : USER, COUNSELOR, ADMIN
- **Règles** :
  - USER : Uniquement dans ses propres demandes
  - COUNSELOR : Uniquement dans les demandes qui lui sont assignées
  - ADMIN : Dans toutes les demandes
- **Validation** : Message min 10 caractères
- **Effet** : Si demande = "assignee", passe automatiquement à "en_cours"

#### Modification de Message (`update_message.php`)
- **Acteurs** : USER, COUNSELOR (propre message uniquement)
- **Règles** :
  - Uniquement ses propres messages
  - Impossible si demande terminée/annulée
- **Validation** : Nouveau message min 10 caractères

#### Suppression de Message (`delete_message.php`)
- **Acteurs** : USER, COUNSELOR (propre message uniquement)
- **Règles** :
  - Uniquement ses propres messages
  - Impossible si demande terminée/annulée

### 👤 Gestion des Profils

#### Modification de Profil (`update_profile.php`)
- **Acteurs** : Tous (propre profil uniquement)
- **Champs modifiables** :
  - **Tous** : Nom, Prénom, Email, Mot de passe
  - **Counselor uniquement** : Spécialité, Biographie
- **Sécurité** :
  - Vérification unicité email
  - Mot de passe optionnel (ne change que si fourni)
  - Mise à jour de la session après modification

### 🔄 Assignation de Conseillers

#### Assignation (`admin_assign_counselor.php`)
- **Acteur** : ADMIN uniquement
- **Processus** :
  1. Sélection du conseiller
  2. Option : Ajout de notes admin
  3. Mise à jour du statut à "assignee"
  4. **Message automatique pour l'utilisateur** : Notification d'assignation
  5. **Message automatique pour le conseiller** : Informations complètes (patient, titre, urgence, notes admin)

### 📊 Statistiques et Rapports

#### Dashboard Utilisateur
- Nombre total de demandes créées
- Demandes en attente d'assignation
- Demandes en cours de traitement
- Demandes terminées
- Historique complet avec filtres

#### Dashboard Conseiller
- **Demandes actives** : Nombre de conversations en cours
- **Demandes totales** : Historique complet des assignations
- **Demandes terminées** : Nombre de cas résolus
- **Taux de résolution** : Pourcentage de demandes clôturées
- **Temps moyen de résolution** : Délai moyen en heures
- **Statut de disponibilité** : Actif/Inactif/En pause

#### Dashboard Administrateur
- **Vue globale** : Toutes les demandes (tous statuts)
- **Statistiques par urgence** : Répartition basse/moyenne/haute
- **Statistiques par statut** : En attente/Assignée/En cours/Terminée/Annulée
- **Performance des conseillers** : Classement par efficacité
- **Temps de réponse moyen** : Délai entre création et assignation
- **Taux de satisfaction** : Basé sur les demandes terminées

#### Export PDF
- **Génération automatique** de rapports de conversation
- **Contenu inclus** :
  - Informations de la demande (titre, description, urgence)
  - Détails utilisateur et conseiller
  - Historique complet des messages
  - Dates clés (création, assignation, résolution)
  - Statut final de la demande
- **Format** : HTML téléchargeable (compatible impression)
- **Nom du fichier** : `demande_support_{id}_{date}.html`

---

## 💻 Structure du Code

### Pattern MVC (Model-View-Controller)

#### 📦 MODELS (Modèles)
**Localisation** : `model/`

Les modèles représentent les **entités** du système (User, SupportRequest, SupportMessage).

**Exemple : `User.php`**
```php
class User {
    private $id;
    private $nom;
    private $prenom;
    // ...
    
    // Méthodes CRUD
    public function save() { }      // INSERT ou UPDATE
    public function delete() { }    // DELETE
    
    // Getters/Setters
    public function getNom() { }
    public function setNom($nom) { }
}
```

**Responsabilités** :
- Gestion des données
- Validation des attributs
- Interactions avec la base de données
- Logique métier basique

#### 🎮 CONTROLLERS (Contrôleurs)
**Localisation** : `controller/`

Les contrôleurs gèrent la **logique métier** et orchestrent les interactions.

**Exemple : `create_request.php`**
```php
// 1. Vérification de l'authentification
if (!isLoggedIn()) { redirect(); }

// 2. Vérification CSRF
if (!verifyCSRFToken($_POST['csrf_token'])) { }

// 3. Validation des données
$errors = [];
if (empty($titre)) { $errors[] = 'Titre requis'; }

// 4. Création de l'objet
$request = new SupportRequest();
$request->setTitre($titre);
$request->setUserId($_SESSION['user_id']);

// 5. Sauvegarde
if ($request->save()) {
    setFlashMessage('Succès', 'success');
} else {
    setFlashMessage('Erreur', 'error');
}

// 6. Redirection
redirect('view/...');
```

**Responsabilités** :
- Validation des données
- Vérification des permissions
- Orchestration des modèles
- Gestion des redirections
- Messages flash

#### 🎨 VIEWS (Vues)
**Localisation** : `view/`

Les vues gèrent l'**affichage** et l'interface utilisateur.

**Exemple : `request_details.php`**
```php
// 1. Récupération des données
$request = new SupportRequest($requestId);
$messages = findMessagesByRequest($requestId);

// 2. Affichage
<?php echo secureOutput($request->getTitre()); ?>
```

**Responsabilités** :
- Affichage HTML
- Présentation des données
- Formulaires utilisateur
- Interface responsive

---

## 🔍 Concepts Techniques

### Variables Superglobales PHP

#### `$_POST`
Contient les données envoyées via formulaire avec méthode POST.

```php
// Dans un formulaire
<form method="POST" action="create_request.php">
    <input name="titre" value="Ma demande">
</form>

// Dans le contrôleur
$titre = $_POST['titre']; // "Ma demande"
```

**Utilisation** :
- Données sensibles (mots de passe, modifications)
- Création/Modification de ressources
- Actions qui modifient l'état

#### `$_GET`
Contient les paramètres passés dans l'URL.

```php
// URL : request_details.php?id=5
$requestId = $_GET['id']; // 5
```

**Utilisation** :
- Identifiants de ressources
- Paramètres de filtrage
- Navigation entre pages

#### `$_SESSION`
Stocke des données persistantes pour un utilisateur connecté.

```php
// Définition
$_SESSION['user_id'] = 123;
$_SESSION['role'] = 'user';

// Utilisation
$userId = $_SESSION['user_id'];
```

**Utilisation** :
- Authentification
- Données utilisateur courantes
- Panier, préférences

### Fonction `isset()`

Vérifie si une variable existe et n'est pas NULL.

```php
// ❌ Erreur si $_POST['titre'] n'existe pas
$titre = $_POST['titre'];

// ✅ Sécurisé
if (isset($_POST['titre'])) {
    $titre = $_POST['titre'];
} else {
    $titre = '';
}

// Version courte
$titre = isset($_POST['titre']) ? $_POST['titre'] : '';
$titre = $_POST['titre'] ?? ''; // PHP 7.0+
```

**Pourquoi l'utiliser ?**
- Évite les erreurs "Undefined index"
- Validation des données
- Code défensif

### Sécurité

#### Protection CSRF (Cross-Site Request Forgery)

```php
// Génération du token
$token = generateCSRFToken();

// Dans le formulaire
<input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

// Vérification
if (!verifyCSRFToken($_POST['csrf_token'])) {
    // Requête frauduleuse
}
```

**Pourquoi ?** Empêche les attaques où un site malveillant fait des actions en votre nom.

#### Sanitization des Entrées

```php
// Fonction cleanInput() dans config.php
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Utilisation
$titre = cleanInput($_POST['titre']);
```

**Pourquoi ?** Empêche les injections XSS (Cross-Site Scripting).

#### Hashage des Mots de Passe

```php
// Hashage
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
// Résultat : $2y$10$...

// Vérification
if (password_verify($password, $hashedPassword)) {
    // Mot de passe correct
}
```

**Pourquoi ?** Les mots de passe ne sont jamais stockés en clair.

### Relations Base de Données

#### ON DELETE CASCADE
```sql
FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
```
**Effet** : Si l'utilisateur est supprimé, ses demandes sont automatiquement supprimées.

#### ON DELETE SET NULL
```sql
FOREIGN KEY (counselor_user_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
```
**Effet** : Si le conseiller est supprimé, l'assignation devient NULL (la demande reste).

---

## ❓ FAQ & Validation

### Questions Générales

**Q1 : Pourquoi avoir utilisé une seule table `utilisateurs` au lieu de tables séparées ?**

**R :** 
- **Simplicité** : Un seul endroit pour gérer tous les utilisateurs
- **Flexibilité** : Un utilisateur peut changer de rôle sans migration complexe
- **Performance** : Moins de JOINs nécessaires
- **Maintenance** : Code plus simple et réutilisable
- Les colonnes spécifiques aux conseillers sont NULL pour les autres rôles

**Q2 : Comment fonctionne le système de permissions ?**

**R :** 
- Basé sur le champ `role` dans la table `utilisateurs`
- Vérification dans chaque contrôleur : `if (!isAdmin()) { ... }`
- Chaque utilisateur ne peut modifier que ses propres ressources
- Les admins ont accès à tout via des vérifications supplémentaires

**Q3 : Que se passe-t-il si un utilisateur supprime sa demande après qu'elle soit assignée ?**

**R :** 
- La demande est supprimée de la base de données
- Tous les messages associés sont supprimés (CASCADE)
- Le conseiller ne voit plus la demande dans sa liste
- Le compteur `nombre_demandes_actives` du conseiller est automatiquement décrémenté par le trigger

**Q4 : Comment sont gérés les messages automatiques lors de l'assignation ?**

**R :** 
- Quand l'admin assigne un conseiller, deux messages sont créés automatiquement :
  1. **Pour l'utilisateur** : Notification que le conseiller X a été assigné
  2. **Pour le conseiller** : Informations complètes (patient, titre, urgence, notes admin)
- Ces messages sont créés via `SupportMessage` avec l'ID de l'admin comme `sender_id`

**Q5 : Pourquoi utiliser des triggers SQL au lieu de code PHP ?**

**R :** 
- **Cohérence** : Les triggers garantissent que les règles sont toujours appliquées, même si on modifie directement la BDD
- **Performance** : Exécution au niveau de la base de données
- **Automatisation** : Pas besoin de se rappeler de mettre à jour les compteurs manuellement
- **Intégrité** : Impossible d'oublier de mettre à jour une date d'assignation

### Questions Techniques

**Q6 : Expliquez le pattern MVC utilisé dans ce projet.**

**R :** 
- **Model** : `User.php`, `SupportRequest.php`, `SupportMessage.php` - Gestion des données
- **View** : `view/frontoffice/`, `view/backoffice/` - Interface utilisateur
- **Controller** : `controller/auth/`, `controller/support/` - Logique métier
- **Séparation des responsabilités** : Chaque composant a un rôle précis
- **Réutilisabilité** : Les modèles peuvent être utilisés par plusieurs contrôleurs

**Q7 : Comment fonctionne la validation des données ?**

**R :** 
- **Côté client** : JavaScript pour validation immédiate (longueur min, format email)
- **Côté serveur** : PHP pour validation sécurisée (toujours nécessaire)
- **Sanitization** : `cleanInput()` pour nettoyer les entrées
- **Validation métier** : Vérification des règles (ex: message min 10 caractères)

**Q8 : Pourquoi utiliser `isset()` avant d'accéder à `$_POST` ou `$_GET` ?**

**R :** 
- Évite les erreurs "Undefined index" si le paramètre n'existe pas
- Code défensif et robuste
- Meilleure gestion des erreurs
- Alternative moderne : `$_POST['titre'] ?? ''` (null coalescing operator)

**Q9 : Comment est géré le changement de mot de passe dans le profil ?**

**R :** 
- Le champ mot de passe est **optionnel** dans le formulaire
- Si vide : Le mot de passe actuel est conservé (pas de modification)
- Si rempli : Nouveau hash est généré avec `password_hash()`
- La méthode `update()` dans `User.php` compare les hashs pour décider si mise à jour nécessaire

**Q10 : Expliquez les relations entre les tables.**

**R :** 
- **utilisateurs** → **support_requests** : Un utilisateur peut avoir plusieurs demandes (1:N)
- **utilisateurs** → **support_requests** (counselor) : Un conseiller peut avoir plusieurs demandes assignées (1:N)
- **support_requests** → **support_messages** : Une demande peut avoir plusieurs messages (1:N)
- **utilisateurs** → **support_messages** : Un utilisateur peut envoyer plusieurs messages (1:N)

### Questions Fonctionnelles

**Q11 : Un conseiller peut-il modifier les messages d'un utilisateur ?**

**R :** Non. Chaque utilisateur (user ou counselor) ne peut modifier que ses propres messages. Vérification dans `update_message.php` : `if ($messageObj->getSenderId() != $_SESSION['user_id'])`

**Q12 : Que se passe-t-il si un conseiller supprime son compte ?**

**R :** 
- Si le conseiller a des demandes actives, la suppression est bloquée (vérification dans `admin_delete_counselor.php`)
- Si pas de demandes actives, le conseiller peut être supprimé
- Les demandes assignées deviennent `counselor_user_id = NULL` (ON DELETE SET NULL)
- Les messages du conseiller sont supprimés (ON DELETE CASCADE)

**Q13 : Comment fonctionne le système de notifications/messages flash ?**

**R :** 
- Utilisation de `$_SESSION['flash']` pour stocker temporairement les messages
- `setFlashMessage($message, $type)` : Définit un message
- `getFlashMessage()` : Récupère et supprime le message
- Affichage dans les vues avec Bootstrap alerts
- Auto-suppression après affichage

**Q14 : Un utilisateur peut-il voir les demandes d'autres utilisateurs ?**

**R :** Non. Chaque utilisateur ne voit que ses propres demandes. Vérification dans chaque contrôleur : `if ($request->getUserId() != $_SESSION['user_id'])`

**Q15 : Comment est géré le statut d'une demande ?**

**R :** 
- **en_attente** : Demande créée, pas encore assignée
- **assignee** : Conseiller assigné, pas encore de conversation
- **en_cours** : Conversation active (premier message envoyé)
- **terminee** : Demande résolue
- **annulee** : Demande annulée par l'utilisateur

Les transitions sont automatiques :
- `en_attente` → `assignee` : Lors de l'assignation (trigger)
- `assignee` → `en_cours` : Lors du premier message
- `en_cours` → `terminee` : Action manuelle du conseiller

---

## 🚀 Installation

### ⚙️ Prérequis Système

| Composant | Version Minimale | Recommandé |
|-----------|------------------|------------|
| **PHP** | 7.4 | 8.0+ |
| **MySQL/MariaDB** | 5.7 | 8.0+ |
| **Extensions PHP** | PDO, PDO_MySQL | + mbstring, json |
| **Serveur Web** | Apache/Nginx | Apache 2.4+ |
| **Mémoire PHP** | 128M | 256M+ |

### 📥 Installation Rapide

#### Option 1 : Installation Automatique (Recommandé)
```bash
# 1. Cloner le projet
git clone https://github.com/votre-repo/SAFEProject.git
cd SAFEProject

# 2. Exécuter le script d'installation
bash setup_database.sh

# 3. Lancer le serveur
php -S localhost:8000
```

#### Option 2 : Installation Manuelle
```bash
# 1. Cloner le projet
git clone https://github.com/votre-repo/SAFEProject.git
cd SAFEProject

# 2. Configurer la base de données
# Éditer config.php avec vos paramètres
nano config.php

# 3. Créer la base de données
mysql -u root -p
CREATE DATABASE safeproject_db11;
USE safeproject_db11;
SOURCE database/init_complete.sql;
EXIT;

# 4. Configurer les permissions
chmod -R 755 .
chmod -R 777 logs/

# 5. Lancer le serveur
php -S localhost:8000
```

### 🔧 Configuration

#### Fichier `config.php`
```php
// Configuration de la base de données
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'safeproject_db11');
define('DB_USER', 'root');
define('DB_PASS', '');  // Votre mot de passe
define('DB_CHARSET', 'utf8mb4');

// Fuseau horaire
date_default_timezone_set('Africa/Tunis');  // Adapter selon votre zone

// Mode développement (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### 🌐 Accès à l'Application

| Interface | URL | Description |
|-----------|-----|-------------|
| **Page de connexion** | `http://localhost:8000/view/frontoffice/login.php` | Point d'entrée principal |
| **Inscription** | `http://localhost:8000/view/frontoffice/register.php` | Création de compte |
| **Dashboard** | `http://localhost:8000/view/dashboard.php` | Redirection automatique selon rôle |

### 👤 Comptes de Test

Après l'initialisation, les comptes suivants sont disponibles :

| Rôle | Email | Mot de passe | Description |
|------|-------|--------------|-------------|
| **Admin** | `admin@safeproject.com` | `dddd` | Accès complet au système |
| **Conseiller** | `marie.martin@example.com` | `dddd` | Spécialité : Psychologie clinique |
| **Conseiller** | `sophie.bernard@example.com` | `dddd` | Spécialité : Gestion du stress |
| **Utilisateur** | `jean.dupont@example.com` | `dddd` | Compte utilisateur standard |
| **Utilisateur** | `pierre.dubois@example.com` | `dddd` | Compte utilisateur standard |

> ⚠️ **Important** : Changez ces mots de passe en production !

### 🐳 Installation avec Docker (Optionnel)

```bash
# Créer un conteneur MySQL
docker run -d \
  --name safeproject-mysql \
  -e MYSQL_ROOT_PASSWORD=root \
  -e MYSQL_DATABASE=safeproject_db11 \
  -p 3306:3306 \
  mysql:8.0

# Importer la base de données
docker exec -i safeproject-mysql mysql -uroot -proot safeproject_db11 < database/init_complete.sql

# Lancer l'application
php -S localhost:8000
```

### ✅ Vérification de l'Installation

1. **Test de connexion à la base de données**
   ```bash
   php -r "require 'config.php'; echo 'Connexion réussie!';"
   ```

2. **Vérifier les logs**
   ```bash
   ls -la logs/
   ```

3. **Tester l'authentification**
   - Accéder à `http://localhost:8000/view/frontoffice/login.php`
   - Se connecter avec un compte de test
   - Vérifier la redirection vers le dashboard

---

## 📊 Statistiques et Rapports

### 📈 Métriques Disponibles

#### Pour les Utilisateurs
- **Mes demandes** : Vue d'ensemble de toutes les demandes créées
- **Statut en temps réel** : Suivi de l'avancement de chaque demande
- **Historique des conversations** : Accès complet aux échanges

#### Pour les Conseillers
- **Demandes actives** : `nombre_demandes_actives` (mis à jour automatiquement)
- **Taux de résolution** : `demandes_terminees / total_demandes * 100`
- **Temps moyen de résolution** : Calculé via la vue `v_counselor_stats`
- **Charge de travail** : Nombre de demandes en cours vs capacité

#### Pour les Administrateurs
- **Vue globale du système** : Toutes les demandes avec filtres avancés
- **Performance par conseiller** : Classement et comparaison
- **Statistiques d'urgence** : Répartition par niveau de priorité
- **Temps de réponse** : Délai moyen entre création et assignation
- **Taux d'abandon** : Demandes annulées vs demandes terminées

### 📄 Export et Rapports

#### Génération de PDF
```php
// Accessible via : controller/support/generate_pdf.php?id={request_id}
// Génère un rapport HTML téléchargeable contenant :
- Informations complètes de la demande
- Profil utilisateur et conseiller
- Historique chronologique des messages
- Métadonnées (dates, statuts, urgence)
```

**Contrôle d'accès** :
- ✅ Utilisateurs : Uniquement leurs propres demandes
- ✅ Conseillers : Demandes qui leur sont assignées
- ✅ Admins : Toutes les demandes

---

## 🔒 Sécurité

### 🛡️ Mesures de Sécurité Implémentées

#### 1. Authentification & Sessions
```php
// Gestion sécurisée des sessions
session_start();
session_regenerate_id(true);  // Prévention du session fixation

// Vérification à chaque requête
if (!isLoggedIn()) {
    redirectToLogin();
}
```

**Caractéristiques** :
- ✅ Session timeout automatique
- ✅ Régénération d'ID de session après connexion
- ✅ Destruction complète lors de la déconnexion
- ✅ Vérification du rôle à chaque action

#### 2. Protection CSRF (Cross-Site Request Forgery)
```php
// Génération de token unique par session
$token = generateCSRFToken();  // bin2hex(random_bytes(32))

// Dans chaque formulaire
<input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

// Vérification côté serveur
if (!verifyCSRFToken($_POST['csrf_token'])) {
    die('Requête invalide');
}
```

**Implémentation** :
- ✅ Token unique par session
- ✅ Vérification avec `hash_equals()` (timing-safe)
- ✅ Présent sur tous les formulaires sensibles

#### 3. Hashage des Mots de Passe
```php
// Lors de l'inscription/modification
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
// Utilise bcrypt avec salt automatique

// Lors de la connexion
if (password_verify($inputPassword, $hashedPassword)) {
    // Authentification réussie
}
```

**Sécurité** :
- ✅ Algorithme bcrypt (coût adaptatif)
- ✅ Salt unique généré automatiquement
- ✅ Impossible de récupérer le mot de passe original
- ✅ Résistant aux attaques par rainbow tables

#### 4. Sanitization des Entrées
```php
// Nettoyage de toutes les entrées utilisateur
function cleanInput($data) {
    $data = trim($data);           // Supprime espaces
    $data = stripslashes($data);   // Supprime backslashes
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');  // Échappe HTML
    return $data;
}

// Utilisation systématique
$titre = cleanInput($_POST['titre']);
```

**Protection contre** :
- ✅ XSS (Cross-Site Scripting)
- ✅ Injection HTML
- ✅ Caractères spéciaux malveillants

#### 5. Requêtes Préparées (PDO)
```php
// Toutes les requêtes utilisent des prepared statements
$sql = "SELECT * FROM utilisateurs WHERE email = :email";
$stmt = $db->prepare($sql);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->execute();
```

**Protection contre** :
- ✅ Injection SQL
- ✅ Manipulation de requêtes
- ✅ Accès non autorisé aux données

#### 6. Contrôle d'Accès (Authorization)
```php
// Vérification des permissions à chaque action
if ($request->getUserId() != $_SESSION['user_id'] && !isAdmin()) {
    setFlashMessage('Accès non autorisé', 'error');
    redirect('dashboard.php');
}
```

**Règles** :
- ✅ Utilisateurs : Accès uniquement à leurs propres ressources
- ✅ Conseillers : Accès uniquement aux demandes assignées
- ✅ Admins : Accès complet avec logs d'audit

#### 7. Logging & Audit
```php
// Enregistrement de toutes les actions importantes
logAction("User {$userId} created request {$requestId}", 'info');
logAction("Failed login attempt for {$email}", 'warning');
logAction("Admin deleted user {$userId}", 'error');
```

**Fichiers de log** :
- 📁 `logs/support_module_YYYY-MM-DD.log`
- Format : `[timestamp] [level] [User: id] message`
- Rotation quotidienne automatique

#### 8. Validation des Données

**Côté Client (JavaScript)** :
- Validation immédiate des formulaires
- Feedback utilisateur en temps réel
- Prévention des erreurs basiques

**Côté Serveur (PHP)** :
```php
// Validation obligatoire côté serveur
$errors = [];
if (strlen($titre) < 5) {
    $errors[] = 'Le titre doit contenir au moins 5 caractères';
}
if (strlen($description) < 20) {
    $errors[] = 'La description doit contenir au moins 20 caractères';
}
```

### 🔐 Recommandations de Production

#### Configuration PHP (php.ini)
```ini
# Désactiver l'affichage des erreurs
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log

# Sécurité des sessions
session.cookie_httponly = 1
session.cookie_secure = 1  # Si HTTPS
session.use_strict_mode = 1
session.cookie_samesite = "Strict"

# Limites
max_execution_time = 30
memory_limit = 256M
upload_max_filesize = 10M
```

#### Configuration MySQL
```sql
-- Créer un utilisateur dédié (pas root)
CREATE USER 'safeproject_user'@'localhost' IDENTIFIED BY 'mot_de_passe_fort';
GRANT SELECT, INSERT, UPDATE, DELETE ON safeproject_db11.* TO 'safeproject_user'@'localhost';
FLUSH PRIVILEGES;
```

#### Checklist de Sécurité
- [ ] Changer tous les mots de passe par défaut
- [ ] Activer HTTPS (certificat SSL/TLS)
- [ ] Configurer un pare-feu (firewall)
- [ ] Limiter les tentatives de connexion
- [ ] Mettre en place des sauvegardes régulières
- [ ] Activer les logs d'audit
- [ ] Désactiver `display_errors` en production
- [ ] Utiliser un utilisateur MySQL dédié (pas root)
- [ ] Configurer les permissions de fichiers (755/644)
- [ ] Implémenter une politique de mots de passe forts

---

## 📝 Notes Importantes

### Sécurité
- ✅ Protection CSRF sur tous les formulaires
- ✅ Sanitization de toutes les entrées utilisateur
- ✅ Hashage des mots de passe (bcrypt)
- ✅ Vérification des permissions à chaque action
- ✅ Protection contre les injections SQL (PDO prepared statements)

### Bonnes Pratiques
- ✅ Séparation des responsabilités (MVC)
- ✅ Code réutilisable (helpers, modèles)
- ✅ Logging des actions importantes
- ✅ Messages d'erreur clairs pour l'utilisateur
- ✅ Validation côté client ET serveur

### 🚀 Améliorations Futures

#### Phase 2 - Fonctionnalités Avancées
- [ ] **Notifications en temps réel** (WebSockets/Server-Sent Events)
- [ ] **Système d'emails** (notifications d'assignation, rappels)
- [ ] **Chat en direct** (messagerie instantanée)
- [ ] **Appels vidéo** (intégration WebRTC)
- [ ] **Système de rendez-vous** (calendrier intégré)

#### Phase 3 - Analytics & IA
- [ ] **Graphiques interactifs** (Chart.js/D3.js)
- [ ] **Analyse de sentiment** des messages
- [ ] **Recommandation automatique** de conseillers
- [ ] **Détection d'urgence** par mots-clés
- [ ] **Chatbot IA** pour première assistance

#### Phase 4 - Mobile & API
- [ ] **Application mobile** (React Native/Flutter)
- [ ] **API RESTful** documentée (Swagger)
- [ ] **Authentification OAuth2**
- [ ] **Application progressive (PWA)**
- [ ] **Mode hors ligne**

#### Phase 5 - Conformité & Qualité
- [ ] **Conformité RGPD** complète
- [ ] **Certification ISO 27001**
- [ ] **Tests automatisés** (PHPUnit)
- [ ] **CI/CD Pipeline** (GitHub Actions)
- [ ] **Documentation API** (OpenAPI 3.0)

---

## 📞 Support & Contribution

### 🐛 Signaler un Bug
Si vous rencontrez un problème :
1. Vérifiez les logs : `logs/support_module_YYYY-MM-DD.log`
2. Consultez la section [FAQ](#-faq--validation)
3. Ouvrez une issue sur GitHub avec :
   - Description détaillée du problème
   - Étapes pour reproduire
   - Logs pertinents
   - Environnement (PHP version, OS, etc.)

### 💡 Proposer une Fonctionnalité
Pour suggérer une amélioration :
1. Vérifiez qu'elle n'existe pas déjà dans [Améliorations Futures](#-améliorations-futures)
2. Ouvrez une issue avec le label `enhancement`
3. Décrivez le cas d'usage et les bénéfices attendus

### 🤝 Contribuer au Code
Nous accueillons les contributions ! Pour contribuer :
```bash
# 1. Fork le projet
git clone https://github.com/votre-username/SAFEProject.git

# 2. Créer une branche
git checkout -b feature/ma-fonctionnalite

# 3. Commiter vos changements
git commit -m "Ajout de ma fonctionnalité"

# 4. Pousser vers votre fork
git push origin feature/ma-fonctionnalite

# 5. Ouvrir une Pull Request
```

**Guidelines de contribution** :
- ✅ Suivre le pattern MVC existant
- ✅ Commenter le code en français
- ✅ Ajouter des logs pour les actions importantes
- ✅ Tester toutes les fonctionnalités
- ✅ Respecter les conventions de nommage

### 📚 Documentation
- **README.md** : Ce fichier (documentation complète)
- **Logs** : `logs/support_module_*.log` (journaux d'activité)
- **Code** : Commentaires inline dans tous les fichiers
- **SQL** : `database/init_complete.sql` (schéma complet)

---

##   Licence

Ce projet est sous licence **MIT** - voir le fichier [LICENSE](LICENSE) pour plus de détails.

```
MIT License

Copyright (c) 2024 SAFEProject

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
```

---

## 🙏 Remerciements

### Technologies & Frameworks
- **PHP** - Langage backend
- **MySQL** - Base de données relationnelle
- **Bootstrap** - Framework CSS responsive
- **PDO** - Interface d'accès aux bases de données

### Inspirations
- Systèmes de ticketing (Zendesk, Freshdesk)
- Plateformes de télémédecine
- Applications de support psychologique

### Contributeurs
Merci à tous ceux qui ont contribué à ce projet ! 🎉

---

## 📊 Statistiques du Projet

| Métrique | Valeur |
|----------|--------|
| **Lignes de code** | ~5000+ |
| **Fichiers PHP** | 22 contrôleurs + 3 modèles |
| **Vues** | 22 pages |
| **Tables BDD** | 3 tables principales |
| **Triggers SQL** | 4 triggers automatiques |
| **Vues SQL** | 2 vues optimisées |
| **Fonctions de sécurité** | 8 couches de protection |
| **Rôles utilisateurs** | 3 (User/Counselor/Admin) |

---

## 🎯 Résumé Exécutif

**SAFEProject** est une solution complète et sécurisée de gestion du support psychologique, développée avec les meilleures pratiques de l'industrie :

### ✅ Points Forts
- **Architecture MVC robuste** avec séparation claire des responsabilités
- **Sécurité multicouche** (CSRF, XSS, SQL Injection, etc.)
- **Système de rôles flexible** avec permissions granulaires
- **Automatisation avancée** via triggers SQL
- **Interface responsive** adaptée à tous les appareils
- **Logging complet** pour audit et débogage
- **Export PDF** pour archivage et conformité
- **Code documenté** et maintenable

### 🎓 Cas d'Usage
- **Établissements de santé** : Gestion des consultations psychologiques
- **Universités** : Support psychologique pour étudiants
- **Entreprises** : Programme d'aide aux employés (PAE)
- **Associations** : Écoute et accompagnement bénévole
- **Téléconsultation** : Plateforme de thérapie en ligne

### 🌟 Valeur Ajoutée
- **Gain de temps** : Automatisation des tâches répétitives
- **Traçabilité** : Historique complet de toutes les interactions
- **Confidentialité** : Respect de la vie privée et des données sensibles
- **Évolutivité** : Architecture modulaire facile à étendre
- **Conformité** : Respect des normes de sécurité et de protection des données

---

<div align="center">

## 🛡️ SAFEProject

**Plateforme de Support Psychologique Sécurisée**

[![Made with PHP](https://img.shields.io/badge/Made%20with-PHP-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![Powered by MySQL](https://img.shields.io/badge/Powered%20by-MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Built with Bootstrap](https://img.shields.io/badge/Built%20with-Bootstrap-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)

**Développé avec ❤️ pour le bien-être mental**

[🌐 Site Web](#) • [📖 Documentation](#) • [🐛 Issues](https://github.com/votre-repo/SAFEProject/issues) • [💬 Discussions](https://github.com/votre-repo/SAFEProject/discussions)

---

**© 2024 SAFEProject - Tous droits réservés**

*"Parce que la santé mentale compte"*

</div>

