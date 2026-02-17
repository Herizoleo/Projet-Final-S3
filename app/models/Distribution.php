<?php
require_once __DIR__ . '/Model.php';

/**
 * Modèle Distribution
 */
class Distribution extends Model {
    protected $table = 'distributions';

    public function allWithDetails() {
        $sql = "SELECT di.*, 
                       b.quantite_necessaire, b.quantite_recue as besoin_recu,
                       v.nom as ville_nom, r.nom as region_nom,
                       t.nom as article_nom, t.unite, c.nom as categorie_nom,
                       d.donateur, d.quantite_totale as don_total
                FROM distributions di
                JOIN besoins b ON di.besoin_id = b.id
                JOIN villes v ON b.ville_id = v.id
                JOIN regions r ON v.region_id = r.id
                JOIN types_articles t ON b.type_article_id = t.id
                JOIN categories c ON t.categorie_id = c.id
                JOIN dons d ON di.don_id = d.id
                ORDER BY di.date_distribution DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Recherche paginée avec filtres
     */
    public function searchPaginated($filters = [], $limit = 15, $offset = 0) {
        $where = [];
        $params = [];
        
        // Filtre recherche texte
        if (!empty($filters['search'])) {
            $where[] = "(v.nom LIKE ? OR t.nom LIKE ? OR d.donateur LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        // Filtre par catégorie
        if (!empty($filters['categorie_id'])) {
            $where[] = "c.id = ?";
            $params[] = $filters['categorie_id'];
        }
        
        // Filtre par ville
        if (!empty($filters['ville_id'])) {
            $where[] = "v.id = ?";
            $params[] = $filters['ville_id'];
        }
        
        // Filtre par date début
        if (!empty($filters['date_debut'])) {
            $where[] = "di.date_distribution >= ?";
            $params[] = $filters['date_debut'];
        }
        
        // Filtre par date fin
        if (!empty($filters['date_fin'])) {
            $where[] = "di.date_distribution <= ?";
            $params[] = $filters['date_fin'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Compter le total
        $countSql = "SELECT COUNT(*) as total
                     FROM distributions di
                     JOIN besoins b ON di.besoin_id = b.id
                     JOIN villes v ON b.ville_id = v.id
                     JOIN regions r ON v.region_id = r.id
                     JOIN types_articles t ON b.type_article_id = t.id
                     JOIN categories c ON t.categorie_id = c.id
                     JOIN dons d ON di.don_id = d.id
                     $whereClause";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        // Récupérer les données paginées
        $sql = "SELECT di.*, 
                       b.quantite_necessaire, b.quantite_recue as besoin_recu,
                       v.nom as ville_nom, v.id as ville_id, r.nom as region_nom,
                       t.nom as article_nom, t.unite, c.nom as categorie_nom,
                       d.donateur, d.quantite_totale as don_total
                FROM distributions di
                JOIN besoins b ON di.besoin_id = b.id
                JOIN villes v ON b.ville_id = v.id
                JOIN regions r ON v.region_id = r.id
                JOIN types_articles t ON b.type_article_id = t.id
                JOIN categories c ON t.categorie_id = c.id
                JOIN dons d ON di.don_id = d.id
                $whereClause
                ORDER BY di.date_distribution DESC
                LIMIT $limit OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        return ['data' => $data, 'total' => $total];
    }

    public function create($data) {
        $sql = "INSERT INTO distributions (besoin_id, don_id, quantite, date_distribution, notes) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['besoin_id'],
            $data['don_id'],
            $data['quantite'],
            $data['date_distribution'] ?? date('Y-m-d'),
            $data['notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function findWithDetails($id) {
        $sql = "SELECT di.*, 
                       b.quantite_necessaire, b.quantite_recue,
                       v.nom as ville_nom,
                       t.nom as article_nom, t.unite,
                       d.donateur
                FROM distributions di
                JOIN besoins b ON di.besoin_id = b.id
                JOIN villes v ON b.ville_id = v.id
                JOIN types_articles t ON b.type_article_id = t.id
                JOIN dons d ON di.don_id = d.id
                WHERE di.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
