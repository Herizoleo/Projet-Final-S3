<?php
require_once __DIR__ . '/../models/Categorie.php';
require_once __DIR__ . '/../models/TypeArticle.php';

/**
 * Contrôleur Catégorie
 */
class CategorieController {
    
    public static function index() {
        $categorieModel = new Categorie();
        $categories = $categorieModel->allWithTypes();
        
        // Récupérer les types pour chaque catégorie
        $typesParCategorie = [];
        foreach ($categories as $cat) {
            $typesParCategorie[$cat['id']] = $categorieModel->getTypesArticles($cat['id']);
        }
        
        Flight::render('categories/index', [
            'categories' => $categories,
            'typesParCategorie' => $typesParCategorie,
            'pageTitle' => 'Catégories et Types d\'Articles'
        ]);
    }
    
    public static function create() {
        Flight::render('categories/create', [
            'pageTitle' => 'Nouvelle Catégorie'
        ]);
    }
    
    public static function store() {
        $data = Flight::request()->data;
        
        if (empty($data['nom'])) {
            $_SESSION['error'] = "Le nom de la catégorie est obligatoire.";
            Flight::redirect('/categories/create');
            return;
        }
        
        $categorieModel = new Categorie();
        $categorieModel->create([
            'nom' => $data['nom'],
            'description' => $data['description'] ?? null
        ]);
        
        $_SESSION['success'] = "Catégorie créée avec succès.";
        Flight::redirect('/categories');
    }
    
    public static function edit($id) {
        $categorieModel = new Categorie();
        $categorie = $categorieModel->find($id);
        
        if (!$categorie) {
            $_SESSION['error'] = "Catégorie non trouvée.";
            Flight::redirect('/categories');
            return;
        }
        
        Flight::render('categories/edit', [
            'categorie' => $categorie,
            'pageTitle' => 'Modifier la Catégorie'
        ]);
    }
    
    public static function update($id) {
        $data = Flight::request()->data;
        
        if (empty($data['nom'])) {
            $_SESSION['error'] = "Le nom de la catégorie est obligatoire.";
            Flight::redirect('/categories/edit/' . $id);
            return;
        }
        
        $categorieModel = new Categorie();
        $categorieModel->update($id, [
            'nom' => $data['nom'],
            'description' => $data['description'] ?? null
        ]);
        
        $_SESSION['success'] = "Catégorie modifiée avec succès.";
        Flight::redirect('/categories');
    }
    
    public static function delete($id) {
        $categorieModel = new Categorie();
        $categorie = $categorieModel->find($id);
        
        if (!$categorie) {
            $_SESSION['error'] = "Catégorie non trouvée.";
            Flight::redirect('/categories');
            return;
        }
        
        // Vérifier qu'il n'y a pas de types d'articles
        $types = $categorieModel->getTypesArticles($id);
        if (!empty($types)) {
            $_SESSION['error'] = "Impossible de supprimer : cette catégorie contient des types d'articles.";
            Flight::redirect('/categories');
            return;
        }
        
        $categorieModel->delete($id);
        $_SESSION['success'] = "Catégorie supprimée avec succès.";
        Flight::redirect('/categories');
    }
}
