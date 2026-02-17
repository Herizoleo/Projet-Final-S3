<?php
require_once __DIR__ . '/../models/Achat.php';
require_once __DIR__ . '/../models/Besoin.php';
require_once __DIR__ . '/../models/Don.php';
require_once __DIR__ . '/../models/Ville.php';
require_once __DIR__ . '/../models/Categorie.php';
require_once __DIR__ . '/../helpers/Pagination.php';

/**
 * Contrôleur Achat - Achat de besoins avec dons en argent
 */
class AchatController {
    
    public static function index() {
        $achatModel = new Achat();
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
        $result = $achatModel->searchPaginated($filters, $perPage, ($page - 1) * $perPage);
        $pagination = new Pagination($result['total'], $perPage, $page);
        
        Flight::render('achats/index', [
            'achats' => $result['data'],
            'pagination' => $pagination->getInfo(),
            'filters' => $filters,
            'stats' => [
                'total' => $result['total'],
                'total_montant' => $result['total_montant'],
                'total_quantite' => $result['total_quantite']
            ],
            'villes' => $villeModel->allWithRegion(),
            'categories' => $categorieModel->all(),
            'pageTitle' => 'Liste des Achats'
        ]);
    }
    
    public static function create() {
        $besoinModel = new Besoin();
        $donModel = new Don();
        $villeModel = new Ville();
        
        $besoins = $besoinModel->getBesoinsAchetables();
        $donsArgent = $donModel->getDonsArgentDisponibles();
        $totalArgent = $donModel->getTotalArgentDisponible();
        $villes = $villeModel->allWithRegion();
        
        Flight::render('achats/create', [
            'besoins' => $besoins,
            'donsArgent' => $donsArgent,
            'totalArgent' => $totalArgent,
            'villes' => $villes,
            'pageTitle' => 'Nouvel Achat'
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
        
        // Vérifier que le besoin a un prix unitaire
        $sql = "SELECT t.prix_unitaire FROM types_articles t 
                JOIN besoins b ON b.type_article_id = t.id 
                WHERE b.id = ?";
        $stmt = Flight::db()->prepare($sql);
        $stmt->execute([$besoin_id]);
        $typeArticle = $stmt->fetch();
        
        if (!$typeArticle || !$typeArticle['prix_unitaire']) {
            $_SESSION['error'] = "Cet article ne peut pas être acheté (pas de prix unitaire).";
            Flight::redirect('/besoins/' . $besoin_id);
            return;
        }
        
        $donModel = new Don();
        $donsArgent = $donModel->getDonsArgentDisponibles();
        $totalArgent = $donModel->getTotalArgentDisponible();
        
        Flight::render('achats/create_for_besoin', [
            'besoin' => $besoin,
            'prix_unitaire' => $typeArticle['prix_unitaire'],
            'donsArgent' => $donsArgent,
            'totalArgent' => $totalArgent,
            'pageTitle' => 'Acheter pour: ' . $besoin['article_nom']
        ]);
    }
    
    public static function store() {
        $data = Flight::request()->data;
        
        // Validation des champs obligatoires
        if (empty($data['besoin_id']) || empty($data['don_id']) || empty($data['quantite'])) {
            $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
            Flight::redirect('/achats/create');
            return;
        }
        
        $quantite = floatval($data['quantite']);
        
        if ($quantite <= 0) {
            $_SESSION['error'] = "La quantité doit être supérieure à 0.";
            Flight::redirect('/achats/create/' . $data['besoin_id']);
            return;
        }
        
        $donModel = new Don();
        $besoinModel = new Besoin();
        
        // Récupérer le don en argent
        $don = $donModel->find($data['don_id']);
        
        if (!$don) {
            $_SESSION['error'] = "Don non trouvé.";
            Flight::redirect('/achats/create');
            return;
        }
        
        // Récupérer le prix unitaire
        $sql = "SELECT t.prix_unitaire FROM types_articles t 
                JOIN besoins b ON b.type_article_id = t.id 
                WHERE b.id = ?";
        $stmt = Flight::db()->prepare($sql);
        $stmt->execute([$data['besoin_id']]);
        $typeArticle = $stmt->fetch();
        $prix_unitaire = $typeArticle['prix_unitaire'];
        
        // Calculer le montant total
        $montant_total = $quantite * $prix_unitaire;
        
        // RÈGLE DE GESTION: Vérifier que le montant ne dépasse pas l'argent disponible
        if ($montant_total > $don['quantite_disponible']) {
            $_SESSION['error'] = "Erreur: Le montant total (" . number_format($montant_total, 0, ',', ' ') . " Ar) est supérieur à l'argent disponible (" . number_format($don['quantite_disponible'], 0, ',', ' ') . " Ar). Opération annulée.";
            Flight::redirect('/achats/create/' . $data['besoin_id']);
            return;
        }
        
        // Récupérer le besoin pour vérifier la quantité restante
        $besoin = $besoinModel->find($data['besoin_id']);
        $resteARecevoir = $besoin['quantite_necessaire'] - $besoin['quantite_recue'];
        
        // RÈGLE: Limiter automatiquement la quantité au besoin restant (pas de surplus)
        if ($resteARecevoir <= 0) {
            $_SESSION['error'] = "Ce besoin est déjà satisfait.";
            Flight::redirect('/achats');
            return;
        }
        
        $quantiteInitiale = $quantite;
        if ($quantite > $resteARecevoir) {
            $quantite = $resteARecevoir;
            // Recalculer le montant total avec la nouvelle quantité
            $montant_total = $quantite * $prix_unitaire;
            $_SESSION['warning'] = "La quantité a été limitée à " . number_format($quantite, 2) . " (besoin restant). Montant ajusté: " . number_format($montant_total, 0, ',', ' ') . " Ar";
        }
        
        // Créer l'achat
        $achatModel = new Achat();
        $achatModel->create([
            'besoin_id' => $data['besoin_id'],
            'don_id' => $data['don_id'],
            'quantite' => $quantite,
            'prix_unitaire' => $prix_unitaire,
            'montant_total' => $montant_total,
            'date_achat' => $data['date_achat'] ?? date('Y-m-d'),
            'notes' => $data['notes'] ?? null
        ]);
        
        // Réduire l'argent disponible du don
        $donModel->reduireQuantiteDisponible($data['don_id'], $montant_total);
        
        // Mettre à jour la quantité reçue du besoin
        $besoinModel->updateQuantiteRecue($data['besoin_id'], $quantite);
        
        $_SESSION['success'] = "Achat effectué avec succès. Montant: " . number_format($montant_total, 0, ',', ' ') . " Ar";
        Flight::redirect('/achats');
    }
    
    public static function delete($id) {
        $achatModel = new Achat();
        $donModel = new Don();
        $besoinModel = new Besoin();
        
        // Récupérer l'achat avant suppression
        $achat = $achatModel->find($id);
        
        if (!$achat) {
            $_SESSION['error'] = "Achat non trouvé.";
            Flight::redirect('/achats');
            return;
        }
        
        // Restaurer l'argent disponible du don
        $donModel->augmenterQuantiteDisponible($achat['don_id'], $achat['montant_total']);
        
        // Réduire la quantité reçue du besoin
        $besoinModel->reduireQuantiteRecue($achat['besoin_id'], $achat['quantite']);
        
        // Supprimer l'achat
        $achatModel->delete($id);
        
        $_SESSION['success'] = "Achat supprimé. L'argent et le besoin ont été restaurés.";
        Flight::redirect('/achats');
    }

    /**
     * Page de récapitulation avec données Ajax
     */
    public static function recap() {
        Flight::render('achats/recap', [
            'pageTitle' => 'Récapitulation'
        ]);
    }

    /**
     * API pour récupérer les stats de récapitulation (Ajax)
     */
    public static function getRecapStats() {
        $achatModel = new Achat();
        $stats = $achatModel->getStatsRecap();
        
        header('Content-Type: application/json');
        echo json_encode($stats);
        exit;
    }
}
