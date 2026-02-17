<?php
require_once __DIR__ . '/../models/Ville.php';
require_once __DIR__ . '/../models/Region.php';

/**
 * Contrôleur Ville
 */
class VilleController {
    
    public static function index() {
        $villeModel = new Ville();
        $villes = $villeModel->allWithRegion();
        
        Flight::render('villes/index', [
            'villes' => $villes,
            'pageTitle' => 'Gestion des Villes'
        ]);
    }
    
    public static function create() {
        $regionModel = new Region();
        $regions = $regionModel->all();
        
        Flight::render('villes/create', [
            'regions' => $regions,
            'pageTitle' => 'Ajouter une Ville'
        ]);
    }
    
    public static function store() {
        $data = Flight::request()->data;
        
        // Validation
        if (empty($data['nom']) || empty($data['region_id'])) {
            $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
            Flight::redirect('/villes/create');
            return;
        }
        
        $villeModel = new Ville();
        $villeModel->create([
            'nom' => $data['nom'],
            'region_id' => $data['region_id'],
            'population_sinistree' => $data['population_sinistree'] ?? 0
        ]);
        
        $_SESSION['success'] = "Ville ajoutée avec succès.";
        Flight::redirect('/villes');
    }
    
    public static function show($id) {
        $villeModel = new Ville();
        $ville = $villeModel->findWithRegion($id);
        
        if (!$ville) {
            $_SESSION['error'] = "Ville non trouvée.";
            Flight::redirect('/villes');
            return;
        }
        
        $besoins = $villeModel->getBesoinsParVille($id);
        $stats = $villeModel->getStatistiquesVille($id);
        
        Flight::render('villes/show', [
            'ville' => $ville,
            'besoins' => $besoins,
            'stats' => $stats,
            'pageTitle' => 'Détails - ' . $ville['nom']
        ]);
    }
    
    public static function edit($id) {
        $villeModel = new Ville();
        $ville = $villeModel->find($id);
        
        if (!$ville) {
            $_SESSION['error'] = "Ville non trouvée.";
            Flight::redirect('/villes');
            return;
        }
        
        $regionModel = new Region();
        $regions = $regionModel->all();
        
        Flight::render('villes/edit', [
            'ville' => $ville,
            'regions' => $regions,
            'pageTitle' => 'Modifier - ' . $ville['nom']
        ]);
    }
    
    public static function update($id) {
        $data = Flight::request()->data;
        
        // Validation
        if (empty($data['nom']) || empty($data['region_id'])) {
            $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
            Flight::redirect('/villes/edit/' . $id);
            return;
        }
        
        $villeModel = new Ville();
        $villeModel->update($id, [
            'nom' => $data['nom'],
            'region_id' => $data['region_id'],
            'population_sinistree' => $data['population_sinistree'] ?? 0
        ]);
        
        $_SESSION['success'] = "Ville mise à jour avec succès.";
        Flight::redirect('/villes');
    }
    
    public static function delete($id) {
        $villeModel = new Ville();
        $villeModel->delete($id);
        
        $_SESSION['success'] = "Ville supprimée avec succès.";
        Flight::redirect('/villes');
    }
}
