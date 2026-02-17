<?php
require_once __DIR__ . '/Model.php';

/**
 * Modèle TypeArticle
 */
class TypeArticle extends Model {
    protected $table = 'types_articles';

    public function allWithCategorie() {
        $sql = "SELECT t.*, c.nom as categorie_nom 
                FROM types_articles t 
                LEFT JOIN categories c ON t.categorie_id = c.id 
                ORDER BY c.nom, t.nom";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getByCategorie($categorie_id) {
        $sql = "SELECT * FROM types_articles WHERE categorie_id = ? ORDER BY nom";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$categorie_id]);
        return $stmt->fetchAll();
    }
}
