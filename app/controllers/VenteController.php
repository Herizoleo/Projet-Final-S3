<?php
require_once __DIR__ . '/../models/Vente.php';
require_once __DIR__ . '/../models/Don.php';
require_once __DIR__ . '/../models/Config.php';
require_once __DIR__ . '/../helpers/Pagination.php';

/**
 * Contrôleur Vente - Vente de dons pour conversion en argent
 */
class VenteController {
    
    public static function index() {
        $venteModel = new Vente();
        $configModel = new Config();
        
        // Récupérer les filtres
        $query = Flight::request()->query;
        $filters = [
            'search' => $query['search'] ?? '',
            'date_debut' => $query['date_debut'] ?? '',
            'date_fin' => $query['date_fin'] ?? ''
        ];
        $page = max(1, (int)($query['page'] ?? 1));
        $perPage = 15;
        
        // Rechercher avec pagination
        $result = $venteModel->searchPaginated($filters, $perPage, ($page - 1) * $perPage);
        $pagination = new Pagination($result['total'], $perPage, $page);
        $stats = $venteModel->getTotalVentes();
        
        Flight::render('ventes/index', [
            'ventes' => $result['data'],
            'pagination' => $pagination->getInfo(),
            'filters' => $filters,
            'stats' => $stats,
            'pourcentage_reduction' => $configModel->getPourcentageReduction(),
            'pageTitle' => 'Ventes de Dons'
        ]);
    }
    
    public static function create() {
        $venteModel = new Vente();
        $configModel = new Config();
        
        $donsVendables = $venteModel->getDonsVendables();
        $pourcentage = $configModel->getPourcentageReduction();
        
        Flight::render('ventes/create', [
            'donsVendables' => $donsVendables,
            'pourcentage_reduction' => $pourcentage,
            'pageTitle' => 'Vendre un Don'
        ]);
    }
    
    public static function createForDon($don_id) {
        $venteModel = new Vente();
        $donModel = new Don();
        $configModel = new Config();
        
        // Vérifier si le don peut être vendu
        $check = $venteModel->peutEtreVendu($don_id);
        
        if (!$check['vendable']) {
            $_SESSION['error'] = "Ce don ne peut pas être vendu: " . $check['raison'];
            Flight::redirect('/dons/' . $don_id);
            return;
        }
        
        $don = $donModel->findWithDetails($don_id);
        $pourcentage = $configModel->getPourcentageReduction();
        
        Flight::render('ventes/create_for_don', [
            'don' => $don,
            'quantite_max' => $check['quantite_max'],
            'pourcentage_reduction' => $pourcentage,
            'pageTitle' => 'Vendre: ' . $don['article_nom']
        ]);
    }
    
    public static function store() {
        $data = Flight::request()->data;
        $venteModel = new Vente();
        $configModel = new Config();
        
        // Validation
        if (empty($data['don_id']) || empty($data['quantite']) || $data['quantite'] <= 0) {
            $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
            Flight::redirect('/ventes/create');
            return;
        }
        
        // Vérifier si le don peut être vendu
        $check = $venteModel->peutEtreVendu($data['don_id']);
        if (!$check['vendable']) {
            $_SESSION['error'] = "Ce don ne peut pas être vendu: " . $check['raison'];
            Flight::redirect('/ventes/create');
            return;
        }
        
        // Vérifier la quantité
        if ($data['quantite'] > $check['quantite_max']) {
            $_SESSION['error'] = "Quantité demandée ({$data['quantite']}) supérieure à la quantité disponible ({$check['quantite_max']}).";
            Flight::redirect('/ventes/create/' . $data['don_id']);
            return;
        }
        
        // Récupérer le pourcentage (soit personnalisé, soit par défaut)
        $pourcentage = isset($data['pourcentage_reduction']) && $data['pourcentage_reduction'] !== '' 
            ? (float)$data['pourcentage_reduction'] 
            : $configModel->getPourcentageReduction();
        
        // Effectuer la vente
        $vente_id = $venteModel->vendre(
            $data['don_id'],
            $data['quantite'],
            $pourcentage,
            $data['notes'] ?? null
        );
        
        if ($vente_id) {
            $_SESSION['success'] = "Vente effectuée avec succès! L'argent a été ajouté aux dons disponibles.";
        } else {
            $_SESSION['error'] = "Erreur lors de la vente.";
        }
        
        Flight::redirect('/ventes');
    }
    
    public static function delete($id) {
        // Note: La suppression d'une vente est complexe car il faut restaurer le stock
        // Pour simplifier, on ne permet pas la suppression
        $_SESSION['error'] = "La suppression d'une vente n'est pas autorisée.";
        Flight::redirect('/ventes');
    }
    
    public static function config() {
        $configModel = new Config();
        
        Flight::render('ventes/config', [
            'pourcentage_reduction' => $configModel->getPourcentageReduction(),
            'pageTitle' => 'Configuration des Ventes'
        ]);
    }
    
    public static function updateConfig() {
        $data = Flight::request()->data;
        $configModel = new Config();
        
        if (isset($data['pourcentage_reduction'])) {
            $pourcentage = max(0, min(100, (float)$data['pourcentage_reduction']));
            $configModel->setPourcentageReduction($pourcentage);
            $_SESSION['success'] = "Configuration mise à jour. Nouveau pourcentage: {$pourcentage}%";
        }
        
        Flight::redirect('/ventes/config');
    }
    
    /**
     * API pour vérifier si un don est vendable
     */
    public static function checkVendable($don_id) {
        $venteModel = new Vente();
        $result = $venteModel->peutEtreVendu($don_id);
        
        header('Content-Type: application/json');
        echo json_encode($result);
    }
}
