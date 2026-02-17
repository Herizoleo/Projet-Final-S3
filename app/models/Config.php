<?php
require_once __DIR__ . '/Model.php';

/**
 * Modèle Config - Gestion de la configuration
 */
class Config extends Model {
    protected $table = 'config';

    /**
     * Récupérer une valeur de configuration
     */
    public function get($cle, $default = null) {
        $sql = "SELECT valeur FROM {$this->table} WHERE cle = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cle]);
        $result = $stmt->fetch();
        return $result ? $result['valeur'] : $default;
    }

    /**
     * Définir une valeur de configuration
     */
    public function set($cle, $valeur, $description = null) {
        $sql = "INSERT INTO {$this->table} (cle, valeur, description) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$cle, $valeur, $description]);
    }

    /**
     * Récupérer le pourcentage de réduction pour les ventes
     */
    public function getPourcentageReduction() {
        return (float) $this->get('pourcentage_reduction_vente', 10);
    }

    /**
     * Définir le pourcentage de réduction
     */
    public function setPourcentageReduction($pourcentage) {
        return $this->set('pourcentage_reduction_vente', $pourcentage);
    }

    /**
     * Récupérer toutes les configurations
     */
    public function all() {
        $sql = "SELECT * FROM {$this->table} ORDER BY cle";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
