<?php
require_once __DIR__ . '/Model.php';

/**
 * Modèle Ville
 */
class Ville extends Model {
    protected $table = 'villes';

    public function allWithRegion() {
        $sql = "SELECT v.*, r.nom as region_nom 
                FROM villes v 
                LEFT JOIN regions r ON v.region_id = r.id 
                ORDER BY r.nom, v.nom";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function findWithRegion($id) {
        $sql = "SELECT v.*, r.nom as region_nom 
                FROM villes v 
                LEFT JOIN regions r ON v.region_id = r.id 
                WHERE v.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO villes (nom, region_id, population_sinistree) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['nom'],
            $data['region_id'],
            $data['population_sinistree'] ?? 0
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE villes SET nom = ?, region_id = ?, population_sinistree = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nom'],
            $data['region_id'],
            $data['population_sinistree'] ?? 0,
            $id
        ]);
    }

    public function getBesoinsParVille($id) {
        $sql = "SELECT b.*, t.nom as article_nom, t.unite, c.nom as categorie_nom,
                       (b.quantite_necessaire - b.quantite_recue) as reste_a_recevoir
                FROM besoins b
                JOIN types_articles t ON b.type_article_id = t.id
                JOIN categories c ON t.categorie_id = c.id
                WHERE b.ville_id = ?
                ORDER BY c.nom, t.nom";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function getStatistiquesVille($id) {
        $sql = "SELECT 
                    COUNT(b.id) as total_besoins,
                    SUM(CASE WHEN b.statut = 'satisfait' THEN 1 ELSE 0 END) as besoins_satisfaits,
                    SUM(CASE WHEN b.statut = 'partiel' THEN 1 ELSE 0 END) as besoins_partiels,
                    SUM(CASE WHEN b.statut = 'en_attente' THEN 1 ELSE 0 END) as besoins_en_attente
                FROM besoins b
                WHERE b.ville_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
