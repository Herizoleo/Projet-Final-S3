<?php
require_once __DIR__ . '/../models/Besoin.php';
require_once __DIR__ . '/../models/Ville.php';
require_once __DIR__ . '/../models/TypeArticle.php';
require_once __DIR__ . '/../models/Categorie.php';
require_once __DIR__ . '/../models/Don.php';
require_once __DIR__ . '/../helpers/Pagination.php';

/**
 * Contrôleur Besoin
 */
class BesoinController {
    
    public static function index() {
        $besoinModel = new Besoin();
        $villeModel = new Ville();
        $categorieModel = new Categorie();
        
        // Récupérer les filtres
        $query = Flight::request()->query;
        $filters = [
            'search' => $query['search'] ?? '',
            'statut' => $query['statut'] ?? '',
            'categorie_id' => $query['categorie_id'] ?? '',
            'ville_id' => $query['ville_id'] ?? '',
            'date_debut' => $query['date_debut'] ?? '',
            'date_fin' => $query['date_fin'] ?? ''
        ];
        $page = max(1, (int)($query['page'] ?? 1));
        $perPage = 15;
        
        // Rechercher avec pagination
        $result = $besoinModel->searchPaginated($filters, $perPage, ($page - 1) * $perPage);
        $pagination = new Pagination($result['total'], $perPage, $page);
        
        Flight::render('besoins/index', [
            'besoins' => $result['data'],
            'pagination' => $pagination->getInfo(),
            'filters' => $filters,
            'villes' => $villeModel->allWithRegion(),
            'categories' => $categorieModel->all(),
            'pageTitle' => 'Gestion des Besoins'
        ]);
    }
    
    public static function create() {
        $villeModel = new Ville();
        $typeModel = new TypeArticle();
        $categorieModel = new Categorie();
        
        $villes = $villeModel->allWithRegion();
        $types = $typeModel->allWithCategorie();
        $categories = $categorieModel->all();
        
        Flight::render('besoins/create', [
            'villes' => $villes,
            'types' => $types,
            'categories' => $categories,
            'pageTitle' => 'Ajouter un Besoin'
        ]);
    }
    
    public static function store() {
        $data = Flight::request()->data;
        
        // Validation
        if (empty($data['ville_id']) || empty($data['type_article_id']) || empty($data['quantite_necessaire'])) {
            $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
            Flight::redirect('/besoins/create');
            return;
        }
        
        if ($data['quantite_necessaire'] <= 0) {
            $_SESSION['error'] = "La quantité doit être supérieure à 0.";
            Flight::redirect('/besoins/create');
            return;
        }
        
        $besoinModel = new Besoin();
        $besoinModel->create([
            'ville_id' => $data['ville_id'],
            'type_article_id' => $data['type_article_id'],
            'quantite_necessaire' => $data['quantite_necessaire'],
            'date_enregistrement' => $data['date_enregistrement'] ?? date('Y-m-d')
        ]);
        
        $_SESSION['success'] = "Besoin enregistré avec succès.";
        Flight::redirect('/besoins');
    }
    
    public static function show($id) {
        $besoinModel = new Besoin();
        $besoin = $besoinModel->findWithDetails($id);
        
        if (!$besoin) {
            $_SESSION['error'] = "Besoin non trouvé.";
            Flight::redirect('/besoins');
            return;
        }
        
        $distributions = $besoinModel->getDistributions($id);
        $achats = $besoinModel->getAchats($id);
        
        // Récupérer le prix unitaire pour le bouton Acheter
        $sql = "SELECT t.prix_unitaire FROM types_articles t 
                JOIN besoins b ON b.type_article_id = t.id 
                WHERE b.id = ?";
        $stmt = Flight::db()->prepare($sql);
        $stmt->execute([$id]);
        $typeArticle = $stmt->fetch();
        
        Flight::render('besoins/show', [
            'besoin' => $besoin,
            'distributions' => $distributions,
            'achats' => $achats,
            'prix_unitaire' => $typeArticle['prix_unitaire'] ?? null,
            'pageTitle' => 'Détails du Besoin'
        ]);
    }
    
    public static function edit($id) {
        $besoinModel = new Besoin();
        $besoin = $besoinModel->findWithDetails($id);
        
        if (!$besoin) {
            $_SESSION['error'] = "Besoin non trouvé.";
            Flight::redirect('/besoins');
            return;
        }
        
        $villeModel = new Ville();
        $typeModel = new TypeArticle();
        
        $villes = $villeModel->allWithRegion();
        $types = $typeModel->allWithCategorie();
        
        Flight::render('besoins/edit', [
            'besoin' => $besoin,
            'villes' => $villes,
            'types' => $types,
            'pageTitle' => 'Modifier le Besoin'
        ]);
    }
    
    public static function update($id) {
        $data = Flight::request()->data;
        
        // Validation
        if (empty($data['ville_id']) || empty($data['type_article_id']) || empty($data['quantite_necessaire'])) {
            $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
            Flight::redirect('/besoins/edit/' . $id);
            return;
        }
        
        $besoinModel = new Besoin();
        $besoin = $besoinModel->find($id);
        
        // Vérifier que la nouvelle quantité nécessaire >= quantité déjà reçue
        if ($data['quantite_necessaire'] < $besoin['quantite_recue']) {
            $_SESSION['error'] = "Impossible de réduire la quantité nécessaire en-dessous de " . number_format($besoin['quantite_recue'], 2) . " (déjà reçue).";
            Flight::redirect('/besoins/edit/' . $id);
            return;
        }
        
        $besoinModel->update($id, [
            'ville_id' => $data['ville_id'],
            'type_article_id' => $data['type_article_id'],
            'quantite_necessaire' => $data['quantite_necessaire']
        ]);
        
        // Recalculer le statut
        $besoinModel->recalculerStatut($id);
        
        $_SESSION['success'] = "Besoin mis à jour avec succès.";
        Flight::redirect('/besoins');
    }
    
    public static function delete($id) {
        $besoinModel = new Besoin();
        $donModel = new Don();
        
        // Récupérer les distributions liées à ce besoin
        $sql = "SELECT * FROM distributions WHERE besoin_id = ?";
        $stmt = Flight::db()->prepare($sql);
        $stmt->execute([$id]);
        $distributions = $stmt->fetchAll();
        
        // Restaurer les quantités disponibles des dons pour chaque distribution
        foreach ($distributions as $dist) {
            $donModel->augmenterQuantiteDisponible($dist['don_id'], $dist['quantite']);
        }
        
        // Récupérer les achats liés à ce besoin
        $sql = "SELECT * FROM achats WHERE besoin_id = ?";
        $stmt = Flight::db()->prepare($sql);
        $stmt->execute([$id]);
        $achats = $stmt->fetchAll();
        
        // Restaurer l'argent disponible pour chaque achat
        foreach ($achats as $achat) {
            $donModel->augmenterQuantiteDisponible($achat['don_id'], $achat['montant_total']);
        }
        
        // Supprimer le besoin (les distributions/achats seront supprimés en cascade)
        $besoinModel->delete($id);
        
        $_SESSION['success'] = "Besoin supprimé avec succès. Les stocks ont été restaurés.";
        Flight::redirect('/besoins');
    }
}
