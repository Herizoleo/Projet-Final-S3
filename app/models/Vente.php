<?php
require_once __DIR__ . '/Model.php';

/**
 * Modèle Vente - Vente de dons pour conversion en argent
 */
class Vente extends Model {
    protected $table = 'ventes';

    /**
     * Récupérer toutes les ventes avec détails
     */
    public function allWithDetails() {
        $sql = "SELECT v.*, 
                       d.donateur, d.date_reception,
                       t.nom as article_nom, t.unite, t.prix_unitaire,
                       c.nom as categorie_nom
                FROM ventes v
                JOIN dons d ON v.don_id = d.id
                JOIN types_articles t ON d.type_article_id = t.id
                JOIN categories c ON t.categorie_id = c.id
                ORDER BY v.date_vente DESC, v.id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Récupérer les dons vendables (disponibles et sans besoin en attente)
     */
    public function getDonsVendables() {
        $sql = "SELECT d.*, 
                       t.nom as article_nom, t.unite, t.prix_unitaire,
                       c.nom as categorie_nom, c.id as categorie_id,
                       COALESCE(
                           (SELECT SUM(b.quantite_necessaire - b.quantite_recue)
                            FROM besoins b 
                            WHERE b.type_article_id = d.type_article_id 
                            AND b.statut != 'satisfait'), 0
                       ) as besoin_restant
                FROM dons d
                JOIN types_articles t ON d.type_article_id = t.id
                JOIN categories c ON t.categorie_id = c.id
                WHERE d.quantite_disponible > 0
                AND c.nom != 'Argent'
                AND t.prix_unitaire IS NOT NULL
                HAVING besoin_restant = 0
                ORDER BY c.nom, t.nom";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Vérifier si un don peut être vendu
     */
    public function peutEtreVendu($don_id) {
        $sql = "SELECT d.quantite_disponible,
                       t.prix_unitaire,
                       c.nom as categorie_nom,
                       COALESCE(
                           (SELECT SUM(b.quantite_necessaire - b.quantite_recue)
                            FROM besoins b 
                            WHERE b.type_article_id = d.type_article_id 
                            AND b.statut != 'satisfait'), 0
                       ) as besoin_restant
                FROM dons d
                JOIN types_articles t ON d.type_article_id = t.id
                JOIN categories c ON t.categorie_id = c.id
                WHERE d.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$don_id]);
        $result = $stmt->fetch();
        
        if (!$result) return ['vendable' => false, 'raison' => 'Don non trouvé'];
        if ($result['categorie_nom'] == 'Argent') return ['vendable' => false, 'raison' => 'On ne peut pas vendre de l\'argent'];
        if ($result['quantite_disponible'] <= 0) return ['vendable' => false, 'raison' => 'Aucune quantité disponible'];
        if ($result['prix_unitaire'] === null) return ['vendable' => false, 'raison' => 'Pas de prix unitaire défini'];
        if ($result['besoin_restant'] > 0) return ['vendable' => false, 'raison' => 'Il reste des besoins en attente pour ce type d\'article'];
        
        return ['vendable' => true, 'quantite_max' => $result['quantite_disponible']];
    }

    /**
     * Effectuer une vente
     */
    public function vendre($don_id, $quantite, $pourcentage_reduction, $notes = null) {
        // Récupérer le don et son prix
        $sql = "SELECT d.*, t.prix_unitaire, t.nom as article_nom
                FROM dons d
                JOIN types_articles t ON d.type_article_id = t.id
                WHERE d.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$don_id]);
        $don = $stmt->fetch();
        
        if (!$don || $don['quantite_disponible'] < $quantite) {
            return false;
        }
        
        // Calculer le prix de vente
        $prix_original = $don['prix_unitaire'];
        $prix_vente = $prix_original * (1 - $pourcentage_reduction / 100);
        $montant_total = $prix_vente * $quantite;
        
        // Créer la vente
        $sql = "INSERT INTO ventes (don_id, quantite, prix_unitaire_original, pourcentage_reduction, prix_vente, montant_total, date_vente, notes)
                VALUES (?, ?, ?, ?, ?, ?, CURDATE(), ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$don_id, $quantite, $prix_original, $pourcentage_reduction, $prix_vente, $montant_total, $notes]);
        $vente_id = $this->db->lastInsertId();
        
        // Réduire la quantité disponible du don
        $sql = "UPDATE dons SET quantite_disponible = quantite_disponible - ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quantite, $don_id]);
        
        // Ajouter l'argent aux dons en argent
        $this->ajouterArgent($montant_total, "Vente de {$quantite} {$don['article_nom']}");
        
        return $vente_id;
    }

    /**
     * Ajouter l'argent de la vente comme don
     */
    private function ajouterArgent($montant, $description) {
        // Trouver le type article "Ariary"
        $sql = "SELECT id FROM types_articles WHERE nom = 'Ariary' LIMIT 1";
        $stmt = $this->db->query($sql);
        $ariary = $stmt->fetch();
        
        if (!$ariary) return false;
        
        // Créer un nouveau don en argent
        $sql = "INSERT INTO dons (type_article_id, quantite_totale, quantite_disponible, donateur, date_reception, description)
                VALUES (?, ?, ?, 'Vente de don', CURDATE(), ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$ariary['id'], $montant, $montant, $description]);
    }

    /**
     * Calculer le total des ventes
     */
    public function getTotalVentes() {
        $sql = "SELECT COUNT(*) as nb_ventes, 
                       SUM(montant_total) as total_montant,
                       SUM(quantite) as total_quantite
                FROM ventes";
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }

    /**
     * Recherche avec pagination
     */
    public function searchPaginated($filters, $limit, $offset) {
        $where = [];
        $params = [];
        
        if (!empty($filters['search'])) {
            $where[] = "(t.nom LIKE ? OR d.donateur LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }
        
        if (!empty($filters['date_debut'])) {
            $where[] = "v.date_vente >= ?";
            $params[] = $filters['date_debut'];
        }
        
        if (!empty($filters['date_fin'])) {
            $where[] = "v.date_vente <= ?";
            $params[] = $filters['date_fin'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Compter le total
        $sql = "SELECT COUNT(*) as total 
                FROM ventes v
                JOIN dons d ON v.don_id = d.id
                JOIN types_articles t ON d.type_article_id = t.id
                $whereClause";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        // Récupérer les données
        $sql = "SELECT v.*, 
                       d.donateur,
                       t.nom as article_nom, t.unite,
                       c.nom as categorie_nom
                FROM ventes v
                JOIN dons d ON v.don_id = d.id
                JOIN types_articles t ON d.type_article_id = t.id
                JOIN categories c ON t.categorie_id = c.id
                $whereClause
                ORDER BY v.date_vente DESC, v.id DESC
                LIMIT $limit OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return [
            'data' => $stmt->fetchAll(),
            'total' => $total
        ];
    }
}
