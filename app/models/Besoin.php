<?php
require_once __DIR__ . '/Model.php';

/**
 * Modèle Besoin
 */
class Besoin extends Model {
    protected $table = 'besoins';

    public function allWithDetails() {
        $sql = "SELECT b.*, v.nom as ville_nom, r.nom as region_nom,
                       t.nom as article_nom, t.unite, c.nom as categorie_nom,
                       (b.quantite_necessaire - b.quantite_recue) as reste
                FROM besoins b
                JOIN villes v ON b.ville_id = v.id
                JOIN regions r ON v.region_id = r.id
                JOIN types_articles t ON b.type_article_id = t.id
                JOIN categories c ON t.categorie_id = c.id
                ORDER BY b.date_enregistrement DESC, v.nom";
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
            $where[] = "(v.nom LIKE ? OR t.nom LIKE ? OR r.nom LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        // Filtre par statut
        if (!empty($filters['statut'])) {
            $where[] = "b.statut = ?";
            $params[] = $filters['statut'];
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
            $where[] = "b.date_enregistrement >= ?";
            $params[] = $filters['date_debut'];
        }
        
        // Filtre par date fin
        if (!empty($filters['date_fin'])) {
            $where[] = "b.date_enregistrement <= ?";
            $params[] = $filters['date_fin'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Compter le total
        $countSql = "SELECT COUNT(*) as total
                     FROM besoins b
                     JOIN villes v ON b.ville_id = v.id
                     JOIN regions r ON v.region_id = r.id
                     JOIN types_articles t ON b.type_article_id = t.id
                     JOIN categories c ON t.categorie_id = c.id
                     $whereClause";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        // Récupérer les données paginées
        $sql = "SELECT b.*, v.nom as ville_nom, r.nom as region_nom,
                       t.nom as article_nom, t.unite, c.nom as categorie_nom,
                       (b.quantite_necessaire - b.quantite_recue) as reste
                FROM besoins b
                JOIN villes v ON b.ville_id = v.id
                JOIN regions r ON v.region_id = r.id
                JOIN types_articles t ON b.type_article_id = t.id
                JOIN categories c ON t.categorie_id = c.id
                $whereClause
                ORDER BY b.date_enregistrement DESC, v.nom
                LIMIT $limit OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        return ['data' => $data, 'total' => $total];
    }

    public function findWithDetails($id) {
        $sql = "SELECT b.*, v.nom as ville_nom, r.nom as region_nom,
                       t.nom as article_nom, t.unite, c.nom as categorie_nom,
                       t.id as type_article_id,
                       (b.quantite_necessaire - b.quantite_recue) as reste
                FROM besoins b
                JOIN villes v ON b.ville_id = v.id
                JOIN regions r ON v.region_id = r.id
                JOIN types_articles t ON b.type_article_id = t.id
                JOIN categories c ON t.categorie_id = c.id
                WHERE b.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO besoins (ville_id, type_article_id, quantite_necessaire, date_enregistrement) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['ville_id'],
            $data['type_article_id'],
            $data['quantite_necessaire'],
            $data['date_enregistrement'] ?? date('Y-m-d')
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE besoins SET ville_id = ?, type_article_id = ?, quantite_necessaire = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['ville_id'],
            $data['type_article_id'],
            $data['quantite_necessaire'],
            $id
        ]);
    }

    public function updateQuantiteRecue($id, $quantite_ajoutee) {
        // Récupérer le besoin actuel
        $besoin = $this->find($id);
        $nouvelle_quantite = $besoin['quantite_recue'] + $quantite_ajoutee;
        
        // Déterminer le nouveau statut
        $statut = 'en_attente';
        if ($nouvelle_quantite >= $besoin['quantite_necessaire']) {
            $statut = 'satisfait';
        } elseif ($nouvelle_quantite > 0) {
            $statut = 'partiel';
        }
        
        $sql = "UPDATE besoins SET quantite_recue = ?, statut = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nouvelle_quantite, $statut, $id]);
    }

    /**
     * Réduire la quantité reçue (utilisé lors de la suppression d'une distribution/achat)
     */
    public function reduireQuantiteRecue($id, $quantite_retiree) {
        $besoin = $this->find($id);
        $nouvelle_quantite = max(0, $besoin['quantite_recue'] - $quantite_retiree);
        
        // Déterminer le nouveau statut
        $statut = 'en_attente';
        if ($nouvelle_quantite >= $besoin['quantite_necessaire']) {
            $statut = 'satisfait';
        } elseif ($nouvelle_quantite > 0) {
            $statut = 'partiel';
        }
        
        $sql = "UPDATE besoins SET quantite_recue = ?, statut = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nouvelle_quantite, $statut, $id]);
    }

    public function getDistributions($besoin_id) {
        $sql = "SELECT d.*, dn.donateur, dn.date_reception
                FROM distributions d
                JOIN dons dn ON d.don_id = dn.id
                WHERE d.besoin_id = ?
                ORDER BY d.date_distribution DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$besoin_id]);
        return $stmt->fetchAll();
    }

    public function getBesoinsNonSatisfaits() {
        $sql = "SELECT b.*, v.nom as ville_nom, r.nom as region_nom,
                       t.nom as article_nom, t.unite, c.nom as categorie_nom,
                       (b.quantite_necessaire - b.quantite_recue) as reste
                FROM besoins b
                JOIN villes v ON b.ville_id = v.id
                JOIN regions r ON v.region_id = r.id
                JOIN types_articles t ON b.type_article_id = t.id
                JOIN categories c ON t.categorie_id = c.id
                WHERE b.statut != 'satisfait'
                ORDER BY c.nom, v.nom";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Récupère les besoins achetables (avec prix unitaire) non satisfaits
     */
    public function getBesoinsAchetables() {
        $sql = "SELECT b.*, v.nom as ville_nom, r.nom as region_nom,
                       t.nom as article_nom, t.unite, t.prix_unitaire,
                       c.nom as categorie_nom,
                       (b.quantite_necessaire - b.quantite_recue) as reste
                FROM besoins b
                JOIN villes v ON b.ville_id = v.id
                JOIN regions r ON v.region_id = r.id
                JOIN types_articles t ON b.type_article_id = t.id
                JOIN categories c ON t.categorie_id = c.id
                WHERE b.statut != 'satisfait' AND t.prix_unitaire IS NOT NULL
                ORDER BY v.nom, c.nom";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Récupère les achats pour un besoin donné
     */
    public function getAchats($besoin_id) {
        $sql = "SELECT a.*, d.donateur, d.date_reception
                FROM achats a
                JOIN dons d ON a.don_id = d.id
                WHERE a.besoin_id = ?
                ORDER BY a.date_achat DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$besoin_id]);
        return $stmt->fetchAll();
    }

    /**
     * Recalculer le statut d'un besoin en fonction de quantite_recue vs quantite_necessaire
     */
    public function recalculerStatut($id) {
        $besoin = $this->find($id);
        
        $statut = 'en_attente';
        if ($besoin['quantite_recue'] >= $besoin['quantite_necessaire']) {
            $statut = 'satisfait';
        } elseif ($besoin['quantite_recue'] > 0) {
            $statut = 'partiel';
        }
        
        $sql = "UPDATE besoins SET statut = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$statut, $id]);
    }
}
