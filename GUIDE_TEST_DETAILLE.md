# 🧪 GUIDE DE TEST DÉTAILLÉ - Module Support Psychologique

## 📋 Table des Matières

1. [Prérequis](#prérequis)
2. [Vérification de l'Installation](#vérification-de-linstallation)
3. [Test de la Base de Données](#test-de-la-base-de-données)
4. [Scénario 1 : Test Utilisateur](#scénario-1--test-utilisateur)
5. [Scénario 2 : Test Administrateur](#scénario-2--test-administrateur)
6. [Scénario 3 : Test Conseiller](#scénario-3--test-conseiller)
7. [Tests de Sécurité](#tests-de-sécurité)
8. [Dépannage](#dépannage)

---

## ✅ Prérequis

### Checklist Avant de Commencer

- [ ] Docker est installé et en cours d'exécution
- [ ] Le conteneur MySQL `safeproject_mysql` est lancé
- [ ] La base de données est importée
- [ ] Le serveur PHP est lancé (`php -S localhost:8000`)
- [ ] Votre navigateur est ouvert

### Identifiants de Test

| Email | Mot de passe | Rôle |
|-------|--------------|------|
| admin@safeproject.com | password123 | admin |
| jean.dupont@example.com | password123 | user |
| pierre.dubois@example.com | password123 | user |
| marie.martin@example.com | password123 | counselor |
| sophie.bernard@example.com | password123 | counselor |

---

## 🔍 Vérification de l'Installation

### Étape 1 : Vérifier que Docker fonctionne

```bash
docker ps
```

**Résultat attendu :**
```
CONTAINER ID   IMAGE       COMMAND                  STATUS         PORTS                    NAMES
xxxxxxxxxxxxx  mysql:8.0   "docker-entrypoint.s…"   Up X minutes   0.0.0.0:3306->3306/tcp   safeproject_mysql
```

✅ **Test réussi si** : Le conteneur `safeproject_mysql` apparaît avec le statut "Up"

---

### Étape 2 : Vérifier la connexion à MySQL

```bash
docker exec safeproject_mysql mysql -u root -e "SELECT 'MySQL fonctionne!' as test;"
```

**Résultat attendu :**
```
+--------------------+
| test               |
+--------------------+
| MySQL fonctionne!  |
+--------------------+
```

✅ **Test réussi si** : Le message "MySQL fonctionne!" s'affiche

---

### Étape 3 : Vérifier le serveur PHP

Ouvrez dans votre navigateur :
```
http://localhost:8000/view/frontoffice/index.html
```

✅ **Test réussi si** : Une page HTML s'affiche (même si c'est une page basique)

---

## 🗄️ Test de la Base de Données

### Étape 1 : Vérifier les tables

```bash
docker exec safeproject_mysql mysql -u root safeproject_db -e "SHOW TABLES;"
```

**Résultat attendu :**
```
+---------------------------+
| Tables_in_safeproject_db  |
+---------------------------+
| counselors                |
| support_messages          |
| support_requests          |
| utilisateurs              |
| v_counselor_stats         |
| v_support_requests_full   |
+---------------------------+
```

✅ **Test réussi si** : Les 4 tables et 2 vues sont présentes

---

### Étape 2 : Vérifier les utilisateurs

```bash
docker exec safeproject_mysql mysql -u root safeproject_db -e "SELECT id, email, role FROM utilisateurs;"
```

**Résultat attendu :**
```
+----+---------------------------+-----------+
| id | email                     | role      |
+----+---------------------------+-----------+
|  1 | admin@safeproject.com     | admin     |
|  2 | jean.dupont@example.com   | user      |
|  3 | marie.martin@example.com  | counselor |
|  4 | sophie.bernard@example.com| counselor |
|  5 | pierre.dubois@example.com | user      |
+----+---------------------------+-----------+
```

✅ **Test réussi si** : 5 utilisateurs sont présents avec les bons rôles

---

### Étape 3 : Vérifier les conseillers

```bash
docker exec safeproject_mysql mysql -u root safeproject_db -e "SELECT * FROM counselors;"
```

**Résultat attendu :**
```
+----+---------+-------------------------+-------+------------------+--------+
| id | user_id | specialite              | bio   | disponibilite    | statut |
+----+---------+-------------------------+-------+------------------+--------+
|  1 |       3 | Psychologie clinique    | ...   | Lun-Ven 9h-17h   | actif  |
|  2 |       4 | Gestion du stress       | ...   | Mar-Jeu 10h-16h  | actif  |
+----+---------+-------------------------+-------+------------------+--------+
```

✅ **Test réussi si** : 2 conseillers sont présents

---

## 👤 Scénario 1 : Test Utilisateur

### 🎯 Objectif
Tester le parcours complet d'un utilisateur créant une demande de support.

---

### Étape 1 : Accéder à la page d'information

**URL :**
```
http://localhost:8000/view/frontoffice/support/support_info.php
```

**Actions à vérifier :**
- [ ] La page se charge sans erreur
- [ ] Le titre "Support Psychologique" est visible
- [ ] Les informations sur les services sont affichées
- [ ] Un bouton "Faire une demande" est présent
- [ ] Les conseillers disponibles sont listés (Marie Martin, Sophie Bernard)

**Capture d'écran attendue :**
```
┌─────────────────────────────────────────────┐
│  🧠 Support Psychologique                   │
│                                             │
│  Nos services de support...                 │
│                                             │
│  👥 Nos Conseillers                         │
│  • Marie Martin - Psychologie clinique      │
│  • Sophie Bernard - Gestion du stress       │
│                                             │
│  [Faire une demande] [Mes demandes]         │
└─────────────────────────────────────────────┘
```

✅ **Test réussi si** : Tous les éléments sont visibles et correctement formatés

---

### Étape 2 : Créer une nouvelle demande

**URL :**
```
http://localhost:8000/view/frontoffice/support/support_form.php
```

**Actions à effectuer :**

1. **Remplir le formulaire :**
   - Sujet : "Besoin d'aide pour gérer le stress"
   - Urgence : "Moyenne"
   - Message : "Bonjour, je ressens beaucoup de stress au travail ces derniers temps et j'aimerais en discuter avec un professionnel."

2. **Cliquer sur "Soumettre la demande"**

**Vérifications :**
- [ ] Le formulaire accepte la saisie
- [ ] Les champs obligatoires sont marqués avec *
- [ ] La liste déroulante "Urgence" contient : Faible, Moyenne, Élevée
- [ ] Un message de confirmation apparaît après soumission
- [ ] Vous êtes redirigé vers "Mes demandes"

**Messages d'erreur possibles :**
- ❌ "Tous les champs sont obligatoires" → Remplir tous les champs
- ❌ "Session non trouvée" → Vous devez être connecté (voir note ci-dessous)

> **📝 Note :** Si vous obtenez une erreur de session, cela signifie que le système de connexion n'est pas encore implémenté. Pour les tests, vous devrez temporairement modifier les fichiers pour simuler une session.

---

### Étape 3 : Voir les demandes créées

**URL :**
```
http://localhost:8000/view/frontoffice/support/my_requests.php
```

**Vérifications :**
- [ ] La liste de vos demandes s'affiche
- [ ] Chaque demande montre : Sujet, Statut, Date, Urgence
- [ ] Les badges de statut sont colorés (ex: "En attente" en jaune)
- [ ] Un bouton "Voir détails" est présent pour chaque demande

**Affichage attendu :**
```
┌─────────────────────────────────────────────┐
│  📋 Mes Demandes de Support                 │
│                                             │
│  ┌─────────────────────────────────────┐   │
│  │ Besoin d'aide pour gérer le stress  │   │
│  │ 🔴 Urgence: Moyenne                 │   │
│  │ 🟡 Statut: En attente               │   │
│  │ 📅 Créé le: 16/11/2025 16:30        │   │
│  │ [Voir détails] [Annuler]            │   │
│  └─────────────────────────────────────┘   │
└─────────────────────────────────────────────┘
```

✅ **Test réussi si** : Votre demande apparaît dans la liste

---

### Étape 4 : Voir les détails d'une demande

**URL :**
```
http://localhost:8000/view/frontoffice/support/request_details.php?id=1
```

**Vérifications :**
- [ ] Les détails complets de la demande sont affichés
- [ ] Le message initial est visible
- [ ] La zone de conversation est présente
- [ ] Un formulaire pour envoyer un message est disponible
- [ ] Le statut actuel est affiché clairement

**Affichage attendu :**
```
┌─────────────────────────────────────────────┐
│  Demande #1 - Besoin d'aide...              │
│  🟡 Statut: En attente                      │
│                                             │
│  📝 Message initial                         │
│  Bonjour, je ressens beaucoup de stress...  │
│                                             │
│  💬 Conversation (0 messages)               │
│  [Aucun message pour le moment]             │
│                                             │
│  ✉️ Envoyer un message                      │
│  [Textarea]                                 │
│  [Envoyer]                                  │
└─────────────────────────────────────────────┘
```

✅ **Test réussi si** : Tous les détails sont affichés correctement

---

### Étape 5 : Envoyer un message de suivi

**Actions à effectuer :**

1. **Dans la zone de texte, écrire :**
   ```
   J'aimerais préciser que ce stress affecte également mon sommeil.
   ```

2. **Cliquer sur "Envoyer"**

**Vérifications :**
- [ ] Le message est accepté
- [ ] Une confirmation apparaît
- [ ] Le message est ajouté à la conversation
- [ ] Le timestamp est correct
- [ ] L'expéditeur est marqué comme "Vous"

✅ **Test réussi si** : Le message apparaît immédiatement dans la conversation

---

### Étape 6 : Annuler une demande

**Actions à effectuer :**

1. **Retourner sur "Mes demandes"**
2. **Cliquer sur le bouton "Annuler" d'une demande**
3. **Confirmer l'annulation**

**Vérifications :**
- [ ] Une confirmation est demandée avant l'annulation
- [ ] Le statut passe à "Annulée"
- [ ] Le badge devient gris
- [ ] La demande reste visible mais marquée comme annulée

✅ **Test réussi si** : La demande est correctement annulée

---

## 👨‍💼 Scénario 2 : Test Administrateur

### 🎯 Objectif
Tester la gestion des demandes et l'assignation des conseillers.

---

### Étape 1 : Accéder au tableau de bord admin

**URL :**
```
http://localhost:8000/view/backoffice/support/support_requests.php
```

**Vérifications :**
- [ ] Toutes les demandes de support sont listées
- [ ] Les informations affichées : Utilisateur, Sujet, Urgence, Statut, Date, Conseiller assigné
- [ ] Des filtres sont disponibles (par statut, urgence, conseiller)
- [ ] Les statistiques sont affichées en haut

**Affichage attendu :**
```
┌─────────────────────────────────────────────────────────┐
│  📊 Tableau de Bord - Support Psychologique             │
│                                                         │
│  📈 Statistiques                                        │
│  • Total demandes: 5    • En attente: 2                │
│  • En cours: 2          • Résolues: 1                  │
│                                                         │
│  🔍 Filtres                                             │
│  [Statut ▼] [Urgence ▼] [Conseiller ▼] [Rechercher]    │
│                                                         │
│  📋 Liste des Demandes                                  │
│  ┌─────────────────────────────────────────────────┐   │
│  │ #1 | Jean Dupont | Stress au travail | 🔴      │   │
│  │ En attente | 16/11/2025 | Non assigné           │   │
│  │ [Assigner] [Voir] [Supprimer]                   │   │
│  └─────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
```

✅ **Test réussi si** : Le tableau de bord est complet et fonctionnel

---

### Étape 2 : Assigner un conseiller

**Actions à effectuer :**

1. **Cliquer sur "Assigner" pour la demande #1**
2. **URL :**
   ```
   http://localhost:8000/view/backoffice/support/assign_counselor.php?id=1
   ```

3. **Sélectionner un conseiller :**
   - Choisir "Marie Martin - Psychologie clinique"

4. **Cliquer sur "Assigner"**

**Vérifications :**
- [ ] La liste des conseillers disponibles est affichée
- [ ] Les spécialités sont visibles
- [ ] Le statut de disponibilité est indiqué
- [ ] Une confirmation est affichée après assignation
- [ ] Le statut de la demande passe de "En attente" à "En cours"

**Affichage attendu :**
```
┌─────────────────────────────────────────────┐
│  👥 Assigner un Conseiller                  │
│  Demande #1: Stress au travail              │
│                                             │
│  Sélectionner un conseiller:                │
│  ○ Marie Martin                             │
│    Psychologie clinique                     │
│    ✅ Disponible                            │
│                                             │
│  ○ Sophie Bernard                           │
│    Gestion du stress                        │
│    ✅ Disponible                            │
│                                             │
│  [Assigner] [Annuler]                       │
└─────────────────────────────────────────────┘
```

✅ **Test réussi si** : Le conseiller est assigné et le statut change

---

### Étape 3 : Gérer les conseillers

**URL :**
```
http://localhost:8000/view/backoffice/support/counselors_list.php
```

**Vérifications :**
- [ ] Tous les conseillers sont listés
- [ ] Les informations affichées : Nom, Spécialité, Disponibilité, Statut, Demandes actives
- [ ] Un bouton "Ajouter un conseiller" est présent
- [ ] Des boutons "Modifier" et "Supprimer" sont disponibles pour chaque conseiller

**Affichage attendu :**
```
┌─────────────────────────────────────────────────────────┐
│  👥 Gestion des Conseillers                             │
│                                                         │
│  [+ Ajouter un conseiller]                              │
│                                                         │
│  ┌─────────────────────────────────────────────────┐   │
│  │ Marie Martin                                    │   │
│  │ 📚 Psychologie clinique                         │   │
│  │ 🕐 Lun-Ven 9h-17h                               │   │
│  │ ✅ Actif | 2 demandes actives                   │   │
│  │ [Modifier] [Statistiques] [Désactiver]          │   │
│  └─────────────────────────────────────────────────┘   │
│                                                         │
│  ┌─────────────────────────────────────────────────┐   │
│  │ Sophie Bernard                                  │   │
│  │ 📚 Gestion du stress                            │   │
│  │ 🕐 Mar-Jeu 10h-16h                              │   │
│  │ ✅ Actif | 0 demandes actives                   │   │
│  │ [Modifier] [Statistiques] [Désactiver]          │   │
│  └─────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
```

✅ **Test réussi si** : La liste est complète et les actions sont disponibles

---

### Étape 4 : Ajouter un nouveau conseiller

**URL :**
```
http://localhost:8000/view/backoffice/support/add_counselor.php
```

**Actions à effectuer :**

1. **Remplir le formulaire :**
   - Utilisateur : Sélectionner un utilisateur existant ou créer un nouveau
   - Spécialité : "Thérapie cognitive et comportementale"
   - Bio : "Spécialisé dans les troubles anxieux avec 10 ans d'expérience"
   - Disponibilité : "Lun-Mer-Ven 14h-19h"

2. **Cliquer sur "Enregistrer"**

**Vérifications :**
- [ ] Le formulaire est clair et bien organisé
- [ ] Les champs obligatoires sont marqués
- [ ] La validation côté client fonctionne
- [ ] Une confirmation est affichée après création
- [ ] Le nouveau conseiller apparaît dans la liste

✅ **Test réussi si** : Le conseiller est créé avec succès

---

### Étape 5 : Voir les statistiques d'un conseiller

**URL :**
```
http://localhost:8000/view/backoffice/support/counselor_stats.php?id=1
```

**Vérifications :**
- [ ] Les statistiques globales sont affichées
- [ ] Nombre total de demandes traitées
- [ ] Nombre de demandes actives
- [ ] Nombre de demandes résolues
- [ ] Taux de résolution
- [ ] Temps moyen de réponse
- [ ] Historique des demandes

**Affichage attendu :**
```
┌─────────────────────────────────────────────────────────┐
│  📊 Statistiques - Marie Martin                         │
│  Psychologie clinique                                   │
│                                                         │
│  📈 Vue d'ensemble                                      │
│  ┌─────────────────┬─────────────────┬───────────────┐ │
│  │ Total demandes  │ Demandes actives│ Résolues      │ │
│  │      12         │        2        │      10       │ │
│  └─────────────────┴─────────────────┴───────────────┘ │
│                                                         │
│  🎯 Performance                                         │
│  • Taux de résolution: 83%                             │
│  • Temps moyen de réponse: 2h 15min                    │
│  • Note moyenne: 4.5/5                                 │
│                                                         │
│  📋 Demandes récentes                                   │
│  • #5 - Stress au travail (En cours)                   │
│  • #3 - Anxiété sociale (Résolue)                      │
│  • #1 - Burnout (Résolue)                              │
└─────────────────────────────────────────────────────────┘
```

✅ **Test réussi si** : Les statistiques sont détaillées et précises

---

### Étape 6 : Supprimer une demande

**Actions à effectuer :**

1. **Retourner au tableau de bord**
2. **Cliquer sur "Supprimer" pour une demande annulée**
3. **Confirmer la suppression**

**Vérifications :**
- [ ] Une confirmation est demandée
- [ ] La demande est supprimée de la base de données
- [ ] Un message de succès est affiché
- [ ] Les statistiques sont mises à jour

⚠️ **Note :** Seules les demandes "Annulées" ou "Résolues" devraient pouvoir être supprimées.

✅ **Test réussi si** : La demande est supprimée correctement

---

## 👨‍⚕️ Scénario 3 : Test Conseiller

### 🎯 Objectif
Tester l'interface conseiller et la réponse aux demandes.

---

### Étape 1 : Voir les demandes assignées

**URL (simulée) :**
```
http://localhost:8000/view/backoffice/support/support_requests.php?counselor=3
```

**Vérifications :**
- [ ] Seules les demandes assignées au conseiller sont affichées
- [ ] Les informations complètes sont visibles
- [ ] Un accès rapide aux détails est disponible

---

### Étape 2 : Répondre à une demande

**Actions à effectuer :**

1. **Ouvrir les détails d'une demande assignée**
2. **Lire le message initial de l'utilisateur**
3. **Écrire une réponse professionnelle :**
   ```
   Bonjour Jean,
   
   Merci d'avoir pris le temps de partager vos préoccupations. Le stress au travail est une problématique courante et il est important de la prendre au sérieux.
   
   Pouvez-vous me donner plus de détails sur les situations qui génèrent le plus de stress ? Cela m'aidera à mieux comprendre votre situation.
   
   Bien cordialement,
   Marie Martin
   ```

4. **Cliquer sur "Envoyer"**

**Vérifications :**
- [ ] Le message est envoyé avec succès
- [ ] Il apparaît dans la conversation
- [ ] L'expéditeur est correctement identifié (Conseiller)
- [ ] La date/heure est enregistrée

✅ **Test réussi si** : Le message est envoyé et visible

---

### Étape 3 : Clôturer une demande

**Actions à effectuer :**

1. **Après plusieurs échanges, cliquer sur "Marquer comme résolue"**
2. **Confirmer la clôture**

**Vérifications :**
- [ ] Le statut passe à "Résolue"
- [ ] La demande disparaît de la liste des demandes actives
- [ ] Les statistiques sont mises à jour
- [ ] L'utilisateur reçoit une notification (si implémentée)

✅ **Test réussi si** : La demande est clôturée correctement

---

## 🔒 Tests de Sécurité

### Test 1 : Injection SQL

**Actions à effectuer :**

1. **Dans un champ de recherche ou formulaire, essayer :**
   ```
   ' OR '1'='1
   ```

2. **Vérifier que l'application ne plante pas**

✅ **Test réussi si** : L'entrée est échappée ou rejetée proprement

---

### Test 2 : Cross-Site Scripting (XSS)

**Actions à effectuer :**

1. **Dans un message, essayer d'insérer :**
   ```html
   <script>alert('XSS')</script>
   ```

2. **Vérifier que le script ne s'exécute pas**

✅ **Test réussi si** : Le HTML est échappé et affiché comme texte

---

### Test 3 : Accès non autorisé

**Actions à effectuer :**

1. **Sans être connecté, essayer d'accéder à :**
   ```
   http://localhost:8000/view/backoffice/support/support_requests.php
   ```

2. **Vérifier qu'une redirection vers la page de connexion se produit**

✅ **Test réussi si** : L'accès est refusé ou redirigé

---

### Test 4 : CSRF Protection

**Actions à effectuer :**

1. **Inspecter le formulaire de création de demande**
2. **Vérifier la présence d'un token CSRF**
3. **Essayer de soumettre le formulaire sans le token**

✅ **Test réussi si** : La soumission est rejetée sans token valide

---

## 🐛 Dépannage

### Erreur : "Connection failed: SQLSTATE[HY000] [2002]"

**Cause :** MySQL n'est pas accessible

**Solutions :**
```bash
# Vérifier que le conteneur tourne
docker ps

# Redémarrer le conteneur
docker restart safeproject_mysql

# Vérifier la connexion
docker exec safeproject_mysql mysqladmin ping -h localhost
```

---

### Erreur : "Table 'utilisateurs' doesn't exist"

**Cause :** La base de données n'est pas importée

**Solution :**
```bash
docker exec -i safeproject_mysql mysql -u root safeproject_db < database/init_complete.sql
```

---

### Erreur : "Session not found"

**Cause :** Le système de session n'est pas configuré

**Solution temporaire :**
Ajouter en début de fichier PHP :
```php
<?php
session_start();
$_SESSION['user_id'] = 2; // Simuler Jean Dupont
$_SESSION['user_role'] = 'user';
?>
```

---

### Erreur : "Access denied for user 'root'@'localhost'"

**Cause :** Mauvais credentials dans `config.php`

**Solution :**
Vérifier `model/config.php` :
```php
define('DB_USER', 'root');
define('DB_PASS', '');  // Pas de mot de passe
```

---

### Page blanche sans erreur

**Cause :** Erreur PHP non affichée

**Solution :**
Ajouter en haut du fichier PHP :
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
```

---

## ✅ Checklist Finale

### Fonctionnalités Frontend
- [ ] Page d'information accessible
- [ ] Formulaire de création de demande fonctionnel
- [ ] Liste des demandes affichée correctement
- [ ] Détails d'une demande visibles
- [ ] Envoi de messages fonctionnel
- [ ] Annulation de demande opérationnelle

### Fonctionnalités Backend
- [ ] Tableau de bord admin accessible
- [ ] Assignation de conseiller fonctionnelle
- [ ] Gestion des conseillers complète
- [ ] Ajout de nouveau conseiller opérationnel
- [ ] Statistiques affichées correctement
- [ ] Suppression de demande fonctionnelle

### Base de Données
- [ ] Toutes les tables créées
- [ ] Données de test présentes
- [ ] Vues fonctionnelles
- [ ] Triggers actifs

### Sécurité
- [ ] Protection SQL injection
- [ ] Protection XSS
- [ ] Contrôle d'accès
- [ ] CSRF tokens (si implémenté)

---

## 🎉 Félicitations !

Si vous avez complété tous ces tests avec succès, votre module de support psychologique est **100% fonctionnel** ! 🚀

---

## 📞 Support

En cas de problème non résolu :
1. Vérifiez les logs PHP : `tail -f /var/log/php_errors.log`
2. Vérifiez les logs Docker : `docker logs safeproject_mysql`
3. Consultez la documentation : `README_MODULE_SUPPORT.md`

---

**Date de création :** 16 novembre 2025  
**Version :** 1.0.0  
**Dernière mise à jour :** 16 novembre 2025

