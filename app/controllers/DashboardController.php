<?php
require_once __DIR__ . '/../models/Ville.php';
require_once __DIR__ . '/../models/Besoin.php';
require_once __DIR__ . '/../models/Don.php';
require_once __DIR__ . '/../models/Distribution.php';
require_once __DIR__ . '/../models/Categorie.php';
require_once __DIR__ . '/../models/Achat.php';

/**
 * Contrôleur Tableau de Bord
 */
class DashboardController {
    
    public static function index() {
        $db = Flight::db();
        
        // Statistiques générales
        $stats = [];
        
        // Total des villes
        $stmt = $db->query("SELECT COUNT(*) as total FROM villes");
        $stats['total_villes'] = $stmt->fetch()['total'];
        
        // Total des dons en argent disponible
        $stmt = $db->query("SELECT COALESCE(SUM(d.quantite_disponible), 0) as total 
                           FROM dons d 
                           JOIN types_articles t ON d.type_article_id = t.id 
                           JOIN categories c ON t.categorie_id = c.id 
                           WHERE c.nom = 'Argent'");
        $stats['total_argent_disponible'] = $stmt->fetch()['total'] ?? 0;
        
        // Total des besoins
        $stmt = $db->query("SELECT COUNT(*) as total FROM besoins");
        $stats['total_besoins'] = $stmt->fetch()['total'];
        
        // Besoins satisfaits
        $stmt = $db->query("SELECT COUNT(*) as total FROM besoins WHERE statut = 'satisfait'");
        $stats['besoins_satisfaits'] = $stmt->fetch()['total'];
        
        // Besoins partiels
        $stmt = $db->query("SELECT COUNT(*) as total FROM besoins WHERE statut = 'partiel'");
        $stats['besoins_partiels'] = $stmt->fetch()['total'];
        
        // Besoins en attente
        $stmt = $db->query("SELECT COUNT(*) as total FROM besoins WHERE statut = 'en_attente'");
        $stats['besoins_en_attente'] = $stmt->fetch()['total'];
        
        // Total des dons
        $stmt = $db->query("SELECT COUNT(*) as total FROM dons");
        $stats['total_dons'] = $stmt->fetch()['total'];
        
        // Total distributions
        $stmt = $db->query("SELECT COUNT(*) as total FROM distributions");
        $stats['total_distributions'] = $stmt->fetch()['total'];
        
        // Total achats
        $stmt = $db->query("SELECT COUNT(*) as total FROM achats");
        $stats['total_achats'] = $stmt->fetch()['total'];
        
        // Montant total des achats
        $stmt = $db->query("SELECT COALESCE(SUM(montant_total), 0) as total FROM achats");
        $stats['montant_achats'] = $stmt->fetch()['total'];
        
        // Liste des villes avec besoins et dons attribués
        $sql = "SELECT v.id, v.nom as ville_nom, r.nom as region_nom, v.population_sinistree,
                       COUNT(DISTINCT b.id) as nb_besoins,
                       SUM(CASE WHEN b.statut = 'satisfait' THEN 1 ELSE 0 END) as besoins_satisfaits,
                       SUM(CASE WHEN b.statut = 'partiel' THEN 1 ELSE 0 END) as besoins_partiels,
                       SUM(CASE WHEN b.statut = 'en_attente' THEN 1 ELSE 0 END) as besoins_en_attente,
                       COALESCE((SELECT SUM(a.montant_total) FROM achats a 
                                 JOIN besoins b2 ON a.besoin_id = b2.id 
                                 WHERE b2.ville_id = v.id), 0) as total_achats
                FROM villes v
                LEFT JOIN regions r ON v.region_id = r.id
                LEFT JOIN besoins b ON v.id = b.ville_id
                GROUP BY v.id
                ORDER BY r.nom, v.nom";
        $stmt = $db->query($sql);
        $villes = $stmt->fetchAll();
        
        // Détails des besoins par ville
        $detailsParVille = [];
        foreach ($villes as $ville) {
            $sql = "SELECT b.*, t.nom as article_nom, t.unite, c.nom as categorie_nom,
                           (b.quantite_necessaire - b.quantite_recue) as reste
                    FROM besoins b
                    JOIN types_articles t ON b.type_article_id = t.id
                    JOIN categories c ON t.categorie_id = c.id
                    WHERE b.ville_id = ?
                    ORDER BY c.nom, t.nom";
            $stmt = $db->prepare($sql);
            $stmt->execute([$ville['id']]);
            $detailsParVille[$ville['id']] = $stmt->fetchAll();
        }
        
        // Dons récents
        $sql = "SELECT d.*, t.nom as article_nom, t.unite, c.nom as categorie_nom
                FROM dons d
                JOIN types_articles t ON d.type_article_id = t.id
                JOIN categories c ON t.categorie_id = c.id
                ORDER BY d.date_reception DESC
                LIMIT 5";
        $stmt = $db->query($sql);
        $donsRecents = $stmt->fetchAll();
        
        // Distributions récentes
        $sql = "SELECT di.*, v.nom as ville_nom, t.nom as article_nom, t.unite
                FROM distributions di
                JOIN besoins b ON di.besoin_id = b.id
                JOIN villes v ON b.ville_id = v.id
                JOIN types_articles t ON b.type_article_id = t.id
                ORDER BY di.date_distribution DESC
                LIMIT 5";
        $stmt = $db->query($sql);
        $distributionsRecentes = $stmt->fetchAll();
        
        Flight::render('dashboard/index', [
            'stats' => $stats,
            'villes' => $villes,
            'detailsParVille' => $detailsParVille,
            'donsRecents' => $donsRecents,
            'distributionsRecentes' => $distributionsRecentes,
            'pageTitle' => 'Tableau de Bord'
        ]);
    }
}
