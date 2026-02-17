<?php
require_once __DIR__ . '/../models/Don.php';
require_once __DIR__ . '/../models/TypeArticle.php';
require_once __DIR__ . '/../models/Categorie.php';
require_once __DIR__ . '/../models/Besoin.php';
require_once __DIR__ . '/../helpers/Pagination.php';

/**
 * Contrôleur Don
 */
class DonController {
    
    public static function index() {
        $donModel = new Don();
        $categorieModel = new Categorie();
        
        // Récupérer les filtres
        $query = Flight::request()->query;
        $filters = [
            'search' => $query['search'] ?? '',
            'categorie_id' => $query['categorie_id'] ?? '',
            'disponibilite' => $query['disponibilite'] ?? '',
            'date_debut' => $query['date_debut'] ?? '',
            'date_fin' => $query['date_fin'] ?? ''
        ];
        $page = max(1, (int)($query['page'] ?? 1));
        $perPage = 15;
        
        // Rechercher avec pagination
        $result = $donModel->searchPaginated($filters, $perPage, ($page - 1) * $perPage);
        $pagination = new Pagination($result['total'], $perPage, $page);
        
        Flight::render('dons/index', [
            'dons' => $result['data'],
            'pagination' => $pagination->getInfo(),
            'filters' => $filters,
            'categories' => $categorieModel->all(),
            'pageTitle' => 'Gestion des Dons'
        ]);
    }
    
    public static function create() {
        $typeModel = new TypeArticle();
        $categorieModel = new Categorie();
        
        $types = $typeModel->allWithCategorie();
        $categories = $categorieModel->all();
        
        Flight::render('dons/create', [
            'types' => $types,
            'categories' => $categories,
            'pageTitle' => 'Enregistrer un Don'
        ]);
    }
    
    public static function store() {
        $data = Flight::request()->data;
        
        // Validation
        if (empty($data['type_article_id']) || empty($data['quantite_totale'])) {
            $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
            Flight::redirect('/dons/create');
            return;
        }
        
        if ($data['quantite_totale'] <= 0) {
            $_SESSION['error'] = "La quantité doit être supérieure à 0.";
            Flight::redirect('/dons/create');
            return;
        }
        
        $donModel = new Don();
        $donModel->create([
            'type_article_id' => $data['type_article_id'],
            'quantite_totale' => $data['quantite_totale'],
            'donateur' => $data['donateur'] ?? null,
            'date_reception' => $data['date_reception'] ?? date('Y-m-d'),
            'description' => $data['description'] ?? null
        ]);
        
        $_SESSION['success'] = "Don enregistré avec succès.";
        Flight::redirect('/dons');
    }
    
    public static function show($id) {
        $donModel = new Don();
        $don = $donModel->findWithDetails($id);
        
        if (!$don) {
            $_SESSION['error'] = "Don non trouvé.";
            Flight::redirect('/dons');
            return;
        }
        
        $distributions = $donModel->getDistributions($id);
        
        Flight::render('dons/show', [
            'don' => $don,
            'distributions' => $distributions,
            'pageTitle' => 'Détails du Don'
        ]);
    }
    
    public static function edit($id) {
        $donModel = new Don();
        $don = $donModel->findWithDetails($id);
        
        if (!$don) {
            $_SESSION['error'] = "Don non trouvé.";
            Flight::redirect('/dons');
            return;
        }
        
        $typeModel = new TypeArticle();
        $types = $typeModel->allWithCategorie();
        
        Flight::render('dons/edit', [
            'don' => $don,
            'types' => $types,
            'pageTitle' => 'Modifier le Don'
        ]);
    }
    
    public static function update($id) {
        $data = Flight::request()->data;
        
        // Validation
        if (empty($data['type_article_id']) || empty($data['quantite_totale'])) {
            $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
            Flight::redirect('/dons/edit/' . $id);
            return;
        }
        
        $donModel = new Don();
        $don = $donModel->find($id);
        
        // Vérifier que la nouvelle quantité n'est pas inférieure à ce qui est déjà distribué
        $quantite_distribuee = $don['quantite_totale'] - $don['quantite_disponible'];
        if ($data['quantite_totale'] < $quantite_distribuee) {
            $_SESSION['error'] = "Impossible de réduire la quantité en-dessous de " . number_format($quantite_distribuee, 2) . " (déjà distribuée).";
            Flight::redirect('/dons/edit/' . $id);
            return;
        }
        
        $donModel->update($id, [
            'type_article_id' => $data['type_article_id'],
            'quantite_totale' => $data['quantite_totale'],
            'donateur' => $data['donateur'] ?? null,
            'description' => $data['description'] ?? null
        ]);
        
        $_SESSION['success'] = "Don mis à jour avec succès.";
        Flight::redirect('/dons');
    }
    
    public static function delete($id) {
        $donModel = new Don();
        $besoinModel = new Besoin();
        
        // Récupérer les distributions liées à ce don
        $sql = "SELECT * FROM distributions WHERE don_id = ?";
        $stmt = Flight::db()->prepare($sql);
        $stmt->execute([$id]);
        $distributions = $stmt->fetchAll();
        
        // Restaurer les quantités reçues des besoins pour chaque distribution
        foreach ($distributions as $dist) {
            $besoinModel->reduireQuantiteRecue($dist['besoin_id'], $dist['quantite']);
        }
        
        // Récupérer les achats liés à ce don
        $sql = "SELECT * FROM achats WHERE don_id = ?";
        $stmt = Flight::db()->prepare($sql);
        $stmt->execute([$id]);
        $achats = $stmt->fetchAll();
        
        // Restaurer les quantités reçues des besoins pour chaque achat
        foreach ($achats as $achat) {
            $besoinModel->reduireQuantiteRecue($achat['besoin_id'], $achat['quantite']);
        }
        
        // Supprimer le don (les distributions/achats seront supprimés en cascade)
        $donModel->delete($id);
        
        $_SESSION['success'] = "Don supprimé avec succès. Les besoins associés ont été mis à jour.";
        Flight::redirect('/dons');
    }
    
    public static function getDonsDisponibles($type_id) {
        $donModel = new Don();
        $dons = $donModel->getDonsDisponibles($type_id);
        $total = $donModel->getTotalDisponible($type_id);
        
        Flight::json([
            'dons' => $dons,
            'total_disponible' => $total
        ]);
    }
}
