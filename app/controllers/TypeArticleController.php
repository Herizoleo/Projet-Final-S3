<?php
require_once __DIR__ . '/../models/TypeArticle.php';
require_once __DIR__ . '/../models/Categorie.php';

/**
 * Contrôleur Type d'Article
 */
class TypeArticleController {
    
    public static function create() {
        $categorieModel = new Categorie();
        
        Flight::render('types_articles/create', [
            'categories' => $categorieModel->all(),
            'pageTitle' => 'Nouveau Type d\'Article'
        ]);
    }
    
    public static function store() {
        $data = Flight::request()->data;
        
        if (empty($data['nom']) || empty($data['unite']) || empty($data['categorie_id'])) {
            $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
            Flight::redirect('/types-articles/create');
            return;
        }
        
        $typeModel = new TypeArticle();
        $typeModel->create([
            'nom' => $data['nom'],
            'unite' => $data['unite'],
            'categorie_id' => $data['categorie_id'],
            'prix_unitaire' => !empty($data['prix_unitaire']) ? $data['prix_unitaire'] : null
        ]);
        
        $_SESSION['success'] = "Type d'article créé avec succès.";
        Flight::redirect('/categories');
    }
    
    public static function edit($id) {
        $typeModel = new TypeArticle();
        $categorieModel = new Categorie();
        
        $type = $typeModel->find($id);
        
        if (!$type) {
            $_SESSION['error'] = "Type d'article non trouvé.";
            Flight::redirect('/categories');
            return;
        }
        
        Flight::render('types_articles/edit', [
            'type' => $type,
            'categories' => $categorieModel->all(),
            'pageTitle' => 'Modifier le Type d\'Article'
        ]);
    }
    
    public static function update($id) {
        $data = Flight::request()->data;
        
        if (empty($data['nom']) || empty($data['unite']) || empty($data['categorie_id'])) {
            $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
            Flight::redirect('/types-articles/edit/' . $id);
            return;
        }
        
        $typeModel = new TypeArticle();
        $typeModel->update($id, [
            'nom' => $data['nom'],
            'unite' => $data['unite'],
            'categorie_id' => $data['categorie_id'],
            'prix_unitaire' => !empty($data['prix_unitaire']) ? $data['prix_unitaire'] : null
        ]);
        
        $_SESSION['success'] = "Type d'article modifié avec succès.";
        Flight::redirect('/categories');
    }
    
    public static function delete($id) {
        $typeModel = new TypeArticle();
        $type = $typeModel->find($id);
        
        if (!$type) {
            $_SESSION['error'] = "Type d'article non trouvé.";
            Flight::redirect('/categories');
            return;
        }
        
        // Vérifier s'il est utilisé dans des besoins ou dons
        $db = Flight::db();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM besoins WHERE type_article_id = ?");
        $stmt->execute([$id]);
        $besoins = $stmt->fetch()['count'];
        
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM dons WHERE type_article_id = ?");
        $stmt->execute([$id]);
        $dons = $stmt->fetch()['count'];
        
        if ($besoins > 0 || $dons > 0) {
            $_SESSION['error'] = "Impossible de supprimer : ce type est utilisé dans $besoins besoin(s) et $dons don(s).";
            Flight::redirect('/categories');
            return;
        }
        
        $typeModel->delete($id);
        $_SESSION['success'] = "Type d'article supprimé avec succès.";
        Flight::redirect('/categories');
    }
}
