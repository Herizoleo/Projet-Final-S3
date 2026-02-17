<?php
require_once __DIR__ . '/Model.php';

/**
 * Modèle Achat - Achat de besoins avec dons en argent
 */
class Achat extends Model {
    protected $table = 'achats';

    public function allWithDetails($ville_id = null) {
        $sql = "SELECT a.*, 
                       b.quantite_necessaire, b.quantite_recue,
                       v.nom as ville_nom, v.id as ville_id,
                       r.nom as region_nom,
                       t.nom as article_nom, t.unite, t.prix_unitaire,
                       c.nom as categorie_nom,
                       d.donateur, d.quantite_disponible as argent_disponible
                FROM achats a
                JOIN besoins b ON a.besoin_id = b.id
                JOIN villes v ON b.ville_id = v.id
                JOIN regions r ON v.region_id = r.id
                JOIN types_articles t ON b.type_article_id = t.id
                JOIN categories c ON t.categorie_id = c.id
                JOIN dons d ON a.don_id = d.id";
        
        if ($ville_id) {
            $sql .= " WHERE v.id = ?";
            $sql .= " ORDER BY a.date_achat DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ville_id]);
        } else {
            $sql .= " ORDER BY a.date_achat DESC";
            $stmt = $this->db->query($sql);
        }
        
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
            $where[] = "a.date_achat >= ?";
            $params[] = $filters['date_debut'];
        }
        
        // Filtre par date fin
        if (!empty($filters['date_fin'])) {
            $where[] = "a.date_achat <= ?";
            $params[] = $filters['date_fin'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Compter le total et sommes
        $countSql = "SELECT COUNT(*) as total, 
                            COALESCE(SUM(a.montant_total), 0) as total_montant,
                            COALESCE(SUM(a.quantite), 0) as total_quantite
                     FROM achats a
                     JOIN besoins b ON a.besoin_id = b.id
                     JOIN villes v ON b.ville_id = v.id
                     JOIN regions r ON v.region_id = r.id
                     JOIN types_articles t ON b.type_article_id = t.id
                     JOIN categories c ON t.categorie_id = c.id
                     JOIN dons d ON a.don_id = d.id
                     $whereClause";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $stats = $stmt->fetch();
        
        // Récupérer les données paginées
        $sql = "SELECT a.*, 
                       b.quantite_necessaire, b.quantite_recue,
                       v.nom as ville_nom, v.id as ville_id,
                       r.nom as region_nom,
                       t.nom as article_nom, t.unite, t.prix_unitaire,
                       c.nom as categorie_nom,
                       d.donateur, d.quantite_disponible as argent_disponible
                FROM achats a
                JOIN besoins b ON a.besoin_id = b.id
                JOIN villes v ON b.ville_id = v.id
                JOIN regions r ON v.region_id = r.id
                JOIN types_articles t ON b.type_article_id = t.id
                JOIN categories c ON t.categorie_id = c.id
                JOIN dons d ON a.don_id = d.id
                $whereClause
                ORDER BY a.date_achat DESC
                LIMIT $limit OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        return [
            'data' => $data, 
            'total' => $stats['total'],
            'total_montant' => $stats['total_montant'],
            'total_quantite' => $stats['total_quantite']
        ];
    }

    public function create($data) {
        $sql = "INSERT INTO achats (besoin_id, don_id, quantite, prix_unitaire, montant_total, date_achat, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['besoin_id'],
            $data['don_id'],
            $data['quantite'],
            $data['prix_unitaire'],
            $data['montant_total'],
            $data['date_achat'] ?? date('Y-m-d'),
            $data['notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function findWithDetails($id) {
        $sql = "SELECT a.*, 
                       b.quantite_necessaire, b.quantite_recue,
                       v.nom as ville_nom,
                       t.nom as article_nom, t.unite,
                       d.donateur
                FROM achats a
                JOIN besoins b ON a.besoin_id = b.id
                JOIN villes v ON b.ville_id = v.id
                JOIN types_articles t ON b.type_article_id = t.id
                JOIN dons d ON a.don_id = d.id
                WHERE a.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getTotalAchatsParVille() {
        $sql = "SELECT v.id, v.nom as ville_nom, r.nom as region_nom,
                       COALESCE(SUM(a.montant_total), 0) as total_achats
                FROM villes v
                LEFT JOIN regions r ON v.region_id = r.id
                LEFT JOIN besoins b ON b.ville_id = v.id
                LEFT JOIN achats a ON a.besoin_id = b.id
                GROUP BY v.id, v.nom, r.nom
                ORDER BY total_achats DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getStatsRecap() {
        // Besoins totaux en montant (quantité nécessaire * prix unitaire)
        $sqlBesoinsTotal = "SELECT COALESCE(SUM(b.quantite_necessaire * t.prix_unitaire), 0) as montant
                           FROM besoins b
                           JOIN types_articles t ON b.type_article_id = t.id
                           WHERE t.prix_unitaire IS NOT NULL";
        
        // Besoins satisfaits en montant (quantité reçue * prix unitaire)
        $sqlBesoinsSatisfaits = "SELECT COALESCE(SUM(b.quantite_recue * t.prix_unitaire), 0) as montant
                                FROM besoins b
                                JOIN types_articles t ON b.type_article_id = t.id
                                WHERE t.prix_unitaire IS NOT NULL";
        
        // Dons reçus en montant (tous types confondus, convertis en Ariary)
        // Pour les dons en nature/matériaux: quantité * prix unitaire
        // Pour les dons en argent: quantité directement
        $sqlDonsRecus = "SELECT 
                            COALESCE(SUM(CASE 
                                WHEN t.prix_unitaire IS NOT NULL THEN d.quantite_totale * t.prix_unitaire
                                ELSE d.quantite_totale
                            END), 0) as montant
                         FROM dons d
                         JOIN types_articles t ON d.type_article_id = t.id";
        
        // Dons dispatchés (distributions + achats) en montant
        $sqlDonsDispatches = "SELECT (
                                COALESCE((SELECT SUM(di.quantite * t.prix_unitaire) 
                                 FROM distributions di
                                 JOIN besoins b ON di.besoin_id = b.id
                                 JOIN types_articles t ON b.type_article_id = t.id
                                 WHERE t.prix_unitaire IS NOT NULL), 0)
                                +
                                COALESCE((SELECT SUM(a.montant_total) FROM achats a), 0)
                              ) as montant";

        $stmt1 = $this->db->query($sqlBesoinsTotal);
        $besoinsTotal = $stmt1->fetch()['montant'];

        $stmt2 = $this->db->query($sqlBesoinsSatisfaits);
        $besoinsSatisfaits = $stmt2->fetch()['montant'];

        $stmt3 = $this->db->query($sqlDonsRecus);
        $donsRecus = $stmt3->fetch()['montant'];

        $stmt4 = $this->db->query($sqlDonsDispatches);
        $donsDispatches = $stmt4->fetch()['montant'];

        return [
            'besoins_total' => $besoinsTotal,
            'besoins_satisfaits' => $besoinsSatisfaits,
            'dons_recus' => $donsRecus,
            'dons_dispatches' => $donsDispatches
        ];
    }
}
