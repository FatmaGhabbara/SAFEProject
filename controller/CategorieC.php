<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/categorie.php';

class CategorieC {
    private function handleException(Exception $e) {
        $msg = $e->getMessage();
        // Detect missing table or schema errors and suggest running migrations
        if (stripos($msg, 'doesn\'t exist') !== false || stripos($msg, 'no such table') !== false || stripos($msg, '42S02') !== false) {
            echo 'Erreur liée à la base de données: table manquante. Exécutez `php migrate.php` pour appliquer le schéma. Détails: ' . $msg;
        } else {
            echo 'Erreur: ' . $msg;
        }
    }
    public function addCategorie(Categorie $categorie) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('INSERT INTO categories (nom_categorie, description) VALUES (:nom_categorie, :description)');
            $query->execute([
                'nom_categorie' => $categorie->getNomCategorie(),
                'description' => $categorie->getDescription(),
            ]);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    public function listCategories() {
        $db = config::getConnexion();
        try {
            $stmt = $db->query('SELECT * FROM categories ORDER BY nom_categorie');
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->handleException($e);
            return [];
        }
    }

    public function getCategorie(int $id) {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare('SELECT * FROM categories WHERE id_categorie = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    public function updateCategorie(int $id, Categorie $categorie) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('UPDATE categories SET nom_categorie = :nom_categorie, description = :description WHERE id_categorie = :id');
            $query->execute([
                'nom_categorie' => $categorie->getNomCategorie(),
                'description' => $categorie->getDescription(),
                'id' => $id
            ]);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    public function deleteCategorie(int $id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('DELETE FROM categories WHERE id_categorie = :id');
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
}
?>
