<?php
require_once __DIR__ . '/../models/Distribution.php';
require_once __DIR__ . '/../models/Besoin.php';
require_once __DIR__ . '/../models/Don.php';
require_once __DIR__ . '/../models/Ville.php';
require_once __DIR__ . '/../models/Categorie.php';
require_once __DIR__ . '/../helpers/Pagination.php';

/**
 * Contrôleur Distribution
 */
class DistributionController {
    
    public static function index() {
        $distributionModel = new Distribution();
        $villeModel = new Ville();
        $categorieModel = new Categorie();
        
        // Récupérer les filtres
        $query = Flight::request()->query;
        $filters = [
            'search' => $query['search'] ?? '',
            'categorie_id' => $query['categorie_id'] ?? '',
            'ville_id' => $query['ville_id'] ?? '',
            'date_debut' => $query['date_debut'] ?? '',
            'date_fin' => $query['date_fin'] ?? ''
        ];
        $page = max(1, (int)($query['page'] ?? 1));
        $perPage = 15;
        
        // Rechercher avec pagination
        $result = $distributionModel->searchPaginated($filters, $perPage, ($page - 1) * $perPage);
        $pagination = new Pagination($result['total'], $perPage, $page);
        
        Flight::render('distributions/index', [
            'distributions' => $result['data'],
            'pagination' => $pagination->getInfo(),
            'filters' => $filters,
            'villes' => $villeModel->allWithRegion(),
            'categories' => $categorieModel->all(),
            'pageTitle' => 'Historique des Distributions'
        ]);
    }
    
    public static function create() {
        $besoinModel = new Besoin();
        $besoins = $besoinModel->getBesoinsNonSatisfaits();
        
        Flight::render('distributions/create', [
            'besoins' => $besoins,
            'pageTitle' => 'Nouvelle Distribution'
        ]);
    }
    
    public static function createForBesoin($besoin_id) {
        $besoinModel = new Besoin();
        $besoin = $besoinModel->findWithDetails($besoin_id);
        
        if (!$besoin) {
            $_SESSION['error'] = "Besoin non trouvé.";
            Flight::redirect('/besoins');
            return;
        }
        
        $donModel = new Don();
        $donsDisponibles = $donModel->getDonsDisponibles($besoin['type_article_id']);
        $totalDisponible = $donModel->getTotalDisponible($besoin['type_article_id']);
        
        Flight::render('distributions/create_for_besoin', [
            'besoin' => $besoin,
            'donsDisponibles' => $donsDisponibles,
            'totalDisponible' => $totalDisponible,
            'pageTitle' => 'Distribuer pour: ' . $besoin['article_nom']
        ]);
    }
    
    public static function store() {
        $data = Flight::request()->data;
        
        // Validation des champs obligatoires
        if (empty($data['besoin_id']) || empty($data['don_id']) || empty($data['quantite'])) {
            $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
            Flight::redirect('/distributions/create');
            return;
        }
        
        $quantite = floatval($data['quantite']);
        
        if ($quantite <= 0) {
            $_SESSION['error'] = "La quantité doit être supérieure à 0.";
            Flight::redirect('/distributions/create/' . $data['besoin_id']);
            return;
        }
        
        $donModel = new Don();
        $besoinModel = new Besoin();
        
        // Récupérer le don
        $don = $donModel->find($data['don_id']);
        
        if (!$don) {
            $_SESSION['error'] = "Don non trouvé.";
            Flight::redirect('/distributions/create');
            return;
        }
        
        // RÈGLE DE GESTION: Vérifier que la quantité demandée ne dépasse pas le stock disponible
        if ($quantite > $don['quantite_disponible']) {
            $_SESSION['error'] = "Erreur: La quantité demandée (" . number_format($quantite, 2) . ") est supérieure au stock disponible (" . number_format($don['quantite_disponible'], 2) . "). Opération annulée.";
            Flight::redirect('/distributions/create/' . $data['besoin_id']);
            return;
        }
        
        // Récupérer le besoin pour vérifier la quantité restante
        $besoin = $besoinModel->find($data['besoin_id']);
        $resteARecevoir = $besoin['quantite_necessaire'] - $besoin['quantite_recue'];
        
        // RÈGLE: Limiter automatiquement la quantité au besoin restant (pas de surplus)
        if ($resteARecevoir <= 0) {
            $_SESSION['error'] = "Ce besoin est déjà satisfait.";
            Flight::redirect('/distributions');
            return;
        }
        
        $quantiteInitiale = $quantite;
        if ($quantite > $resteARecevoir) {
            $quantite = $resteARecevoir;
            $_SESSION['warning'] = "La quantité a été limitée à " . number_format($quantite, 2) . " (besoin restant). Surplus de " . number_format($quantiteInitiale - $quantite, 2) . " non distribué.";
        }
        
        // Créer la distribution
        $distributionModel = new Distribution();
        $distributionModel->create([
            'besoin_id' => $data['besoin_id'],
            'don_id' => $data['don_id'],
            'quantite' => $quantite,
            'date_distribution' => $data['date_distribution'] ?? date('Y-m-d'),
            'notes' => $data['notes'] ?? null
        ]);
        
        // Mettre à jour le stock du don
        $donModel->reduireQuantiteDisponible($data['don_id'], $quantite);
        
        // Mettre à jour la quantité reçue du besoin
        $besoinModel->updateQuantiteRecue($data['besoin_id'], $quantite);
        
        $_SESSION['success'] = "Distribution effectuée avec succès.";
        Flight::redirect('/distributions');
    }
    
    public static function delete($id) {
        $distributionModel = new Distribution();
        $donModel = new Don();
        $besoinModel = new Besoin();
        
        // Récupérer la distribution avant suppression
        $distribution = $distributionModel->find($id);
        
        if (!$distribution) {
            $_SESSION['error'] = "Distribution non trouvée.";
            Flight::redirect('/distributions');
            return;
        }
        
        // Restaurer la quantité disponible du don
        $donModel->augmenterQuantiteDisponible($distribution['don_id'], $distribution['quantite']);
        
        // Réduire la quantité reçue du besoin
        $besoinModel->reduireQuantiteRecue($distribution['besoin_id'], $distribution['quantite']);
        
        // Supprimer la distribution
        $distributionModel->delete($id);
        
        $_SESSION['success'] = "Distribution supprimée. Les stocks ont été restaurés.";
        Flight::redirect('/distributions');
    }
}
