<?php
require_once __DIR__ . '/Model.php';

/**
 * Modèle Categorie
 */
class Categorie extends Model {
    protected $table = 'categories';

    public function allWithTypes() {
        $sql = "SELECT c.*, COUNT(t.id) as nb_types 
                FROM categories c 
                LEFT JOIN types_articles t ON c.id = t.categorie_id 
                GROUP BY c.id 
                ORDER BY c.nom";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getTypesArticles($categorie_id) {
        $sql = "SELECT * FROM types_articles WHERE categorie_id = ? ORDER BY nom";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$categorie_id]);
        return $stmt->fetchAll();
    }
}
