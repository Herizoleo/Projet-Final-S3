<?php
require_once __DIR__ . '/Model.php';

/**
 * Modèle Region
 */
class Region extends Model {
    protected $table = 'regions';

    public function allWithVillesCount() {
        $sql = "SELECT r.*, COUNT(v.id) as nb_villes 
                FROM regions r 
                LEFT JOIN villes v ON r.id = v.region_id 
                GROUP BY r.id 
                ORDER BY r.nom";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
