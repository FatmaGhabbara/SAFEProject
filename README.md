# SAFEProject – Module Posts & Interactions

Guide détaillé pour le module de gestion des publications, commentaires et réponses pour le projet SafeSpace.

## Description du Projet

Ce module fait partie du projet **SAFEProject**, développé dans le cadre du cours **Technologies Web** à **ESPRIT**.

L'objectif principal de ce module est d'implémenter un système complet de gestion de contenu et d'interaction utilisateur, incluant :

* [cite_start]Création, modification et suppression de **Publications (Posts)**[cite: 144].
* [cite_start]Ajout de **Commentaires** aux publications[cite: 144].
* [cite_start]Fonctionnalité de **Réponse** aux commentaires (système de threads)[cite: 144].
* Intégration avec le module d'Authentification pour l'affichage des utilisateurs (Auteur du post/commentaire).

Le projet utilise l'architecture **MVC**.

## Table des Matières

* [Installation](#installation)
* [Utilisation](#utilisation)
* [Main Functionalities](#main-functionalities)
* [Contribution](#contribution)
* [License](#license)

## Installation

[cite_start]Décrivez les étapes pour installer et configurer le projet[cite: 175].

1.  **Clonez le repository** :
    ```bash
    git clone [https://github.com/FatmaGhabbara/SAFEProject.git](https://github.com/FatmaGhabbara/SAFEProject.git)
    cd SAFEProject
    git checkout Ala
    ```

2.  **Configuration du Serveur Local (XAMPP/WAMP)** :
    * [cite_start]Installez un environnement de serveur local (comme XAMPP ou WAMPP)[cite: 180].
    * [cite_start]Démarrez Apache et MySQL[cite: 182].
    * [cite_start]Placez le projet à l'intérieur du dossier `htdocs` (XAMPP) ou `www` (WAMP)[cite: 181].

3.  **Configuration de la Base de Données** :
    * Ouvrez phpMyAdmin.
    * Créez une base de données nommée `safespace`.
    * Importez le fichier SQL fourni (doit inclure les tables `posts`, `commentaires`, et `reponses`).
    * Configurez la connexion à la base de données dans les fichiers de configuration du projet.

## Utilisation

[cite_start]Le projet est développé en **PHP** et utilise l'architecture **MVC**[cite: 215].

### PHP Setup (Prérequis)

* **Version PHP Recommandée** : PHP 8.
* **Base de Données** : MySQL utilisant PDO.

### Accéder à l'Application

* **Page Principale (Forum/Liste des Posts)** :
    `http://localhost/SAFEProject/view/frontoffice/index.php` 

* **Accès Post-Authentification** :
    Assurez-vous d'être connecté via le module d'Authentification avant d'accéder aux fonctionnalités de création et de modification.

### Main Functionalities

* **Gestion des Publications (CRUD)** : Les utilisateurs peuvent créer, lire, mettre à jour et supprimer leurs propres posts.
* **Système de Commentaires** : Ajout et affichage des commentaires sous chaque publication.
* **Fonctionnalité de Réponse** : Permet de répondre directement à un commentaire pour créer un fil de discussion.
* [cite_start]**Règles d'Affichage** : Seuls les utilisateurs connectés peuvent interagir (poster, commenter)[cite: 144].
* **Modération** : (Si applicable, ajouter ici les fonctionnalités de modération, ex: suppression par l'Admin).

## Contribution

[cite_start]Les contributions sont les bienvenues[cite: 257].

Pour contribuer à ce module :

1.  [cite_start]**Fork le repository**[cite: 268].
2.  [cite_start]**Créez une nouvelle branche** pour vos modifications[cite: 266].
3.  [cite_start]**Effectuez vos modifications**[cite: 266].
4.  [cite_start]**Commitez vos changements**[cite: 266].
5.  [cite_start]**Ouvrez une Pull Request**[cite: 266].

## Licence

[cite_start]Ce projet est développé pour des **fins académiques** uniquement[cite: 286].

Développé par **Azzabi Alaedinne** et **Fatma EZZAHRA Ghabbara** – ESPRIT – Web Technologies.
