<?php
require_once __DIR__ . '/Model.php';

/**
 * Modèle Don
 */
class Don extends Model {
    protected $table = 'dons';

    public function allWithDetails() {
        $sql = "SELECT d.*, t.nom as article_nom, t.unite, c.nom as categorie_nom
                FROM dons d
                JOIN types_articles t ON d.type_article_id = t.id
                JOIN categories c ON t.categorie_id = c.id
                ORDER BY d.date_reception DESC";
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
            $where[] = "(t.nom LIKE ? OR d.donateur LIKE ? OR d.description LIKE ?)";
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
        
        // Filtre par disponibilité
        if ($filters['disponibilite'] === 'disponible') {
            $where[] = "d.quantite_disponible > 0";
        } elseif ($filters['disponibilite'] === 'epuise') {
            $where[] = "d.quantite_disponible = 0";
        }
        
        // Filtre par date début
        if (!empty($filters['date_debut'])) {
            $where[] = "d.date_reception >= ?";
            $params[] = $filters['date_debut'];
        }
        
        // Filtre par date fin
        if (!empty($filters['date_fin'])) {
            $where[] = "d.date_reception <= ?";
            $params[] = $filters['date_fin'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Compter le total
        $countSql = "SELECT COUNT(*) as total
                     FROM dons d
                     JOIN types_articles t ON d.type_article_id = t.id
                     JOIN categories c ON t.categorie_id = c.id
                     $whereClause";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        // Récupérer les données paginées
        $sql = "SELECT d.*, t.nom as article_nom, t.unite, c.nom as categorie_nom
                FROM dons d
                JOIN types_articles t ON d.type_article_id = t.id
                JOIN categories c ON t.categorie_id = c.id
                $whereClause
                ORDER BY d.date_reception DESC
                LIMIT $limit OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        return ['data' => $data, 'total' => $total];
    }

    public function findWithDetails($id) {
        $sql = "SELECT d.*, t.nom as article_nom, t.unite, c.nom as categorie_nom
                FROM dons d
                JOIN types_articles t ON d.type_article_id = t.id
                JOIN categories c ON t.categorie_id = c.id
                WHERE d.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO dons (type_article_id, quantite_totale, quantite_disponible, donateur, date_reception, description) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['type_article_id'],
            $data['quantite_totale'],
            $data['quantite_totale'], // Au départ, tout est disponible
            $data['donateur'] ?? null,
            $data['date_reception'] ?? date('Y-m-d'),
            $data['description'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        // Récupérer le don actuel pour calculer la différence
        $don = $this->find($id);
        $ancienne_quantite = $don['quantite_totale'];
        $nouvelle_quantite = $data['quantite_totale'];
        
        // Calculer la quantité déjà distribuée
        $quantite_distribuee = $ancienne_quantite - $don['quantite_disponible'];
        
        // Vérifier que la nouvelle quantité >= quantité déjà distribuée
        if ($nouvelle_quantite < $quantite_distribuee) {
            return false; // Impossible de réduire en-dessous de ce qui est déjà distribué
        }
        
        // Nouvelle quantité disponible = nouvelle_quantite - quantité déjà distribuée
        $nouvelle_disponible = $nouvelle_quantite - $quantite_distribuee;
        
        $sql = "UPDATE dons SET type_article_id = ?, quantite_totale = ?, quantite_disponible = ?, donateur = ?, description = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['type_article_id'],
            $nouvelle_quantite,
            $nouvelle_disponible,
            $data['donateur'] ?? null,
            $data['description'] ?? null,
            $id
        ]);
    }

    public function reduireQuantiteDisponible($id, $quantite) {
        $sql = "UPDATE dons SET quantite_disponible = quantite_disponible - ? WHERE id = ? AND quantite_disponible >= ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$quantite, $id, $quantite]);
    }

    /**
     * Augmenter la quantité disponible (utilisé lors de la suppression d'une distribution/achat)
     */
    public function augmenterQuantiteDisponible($id, $quantite) {
        $sql = "UPDATE dons SET quantite_disponible = LEAST(quantite_disponible + ?, quantite_totale) WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$quantite, $id]);
    }

    public function getDonsDisponibles($type_article_id) {
        $sql = "SELECT d.*, t.nom as article_nom, t.unite
                FROM dons d
                JOIN types_articles t ON d.type_article_id = t.id
                WHERE d.type_article_id = ? AND d.quantite_disponible > 0
                ORDER BY d.date_reception ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$type_article_id]);
        return $stmt->fetchAll();
    }

    public function getTotalDisponible($type_article_id) {
        $sql = "SELECT SUM(quantite_disponible) as total FROM dons WHERE type_article_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$type_article_id]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getDistributions($don_id) {
        $sql = "SELECT di.*, b.quantite_necessaire, b.quantite_recue,
                       v.nom as ville_nom, t.nom as article_nom
                FROM distributions di
                JOIN besoins b ON di.besoin_id = b.id
                JOIN villes v ON b.ville_id = v.id
                JOIN types_articles t ON b.type_article_id = t.id
                WHERE di.don_id = ?
                ORDER BY di.date_distribution DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$don_id]);
        return $stmt->fetchAll();
    }

    /**
     * Récupère les dons en argent (Ariary) disponibles pour acheter des besoins
     */
    public function getDonsArgentDisponibles() {
        $sql = "SELECT d.*, t.nom as article_nom, t.unite
                FROM dons d
                JOIN types_articles t ON d.type_article_id = t.id
                JOIN categories c ON t.categorie_id = c.id
                WHERE c.nom = 'Argent' AND d.quantite_disponible > 0
                ORDER BY d.date_reception ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Total argent disponible pour achats
     */
    public function getTotalArgentDisponible() {
        $sql = "SELECT COALESCE(SUM(d.quantite_disponible), 0) as total
                FROM dons d
                JOIN types_articles t ON d.type_article_id = t.id
                JOIN categories c ON t.categorie_id = c.id
                WHERE c.nom = 'Argent'";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}
