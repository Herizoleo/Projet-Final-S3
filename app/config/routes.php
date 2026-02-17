<?php
/**
 * Configuration des routes de l'application
 */

require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/../controllers/VilleController.php';
require_once __DIR__ . '/../controllers/BesoinController.php';
require_once __DIR__ . '/../controllers/DonController.php';
require_once __DIR__ . '/../controllers/DistributionController.php';
require_once __DIR__ . '/../controllers/CategorieController.php';
require_once __DIR__ . '/../controllers/TypeArticleController.php';
require_once __DIR__ . '/../controllers/AchatController.php';
require_once __DIR__ . '/../controllers/VenteController.php';
require_once __DIR__ . '/../controllers/ResetController.php';

// Page d'accueil - Tableau de bord
Flight::route('/', ['DashboardController', 'index']);
Flight::route('/dashboard', ['DashboardController', 'index']);

// Routes pour les villes
Flight::route('GET /villes', ['VilleController', 'index']);
Flight::route('GET /villes/create', ['VilleController', 'create']);
Flight::route('POST /villes/store', ['VilleController', 'store']);
Flight::route('GET /villes/edit/@id', ['VilleController', 'edit']);
Flight::route('POST /villes/update/@id', ['VilleController', 'update']);
Flight::route('GET /villes/delete/@id', ['VilleController', 'delete']);
Flight::route('GET /villes/@id', ['VilleController', 'show']);

// Routes pour les besoins
Flight::route('GET /besoins', ['BesoinController', 'index']);
Flight::route('GET /besoins/create', ['BesoinController', 'create']);
Flight::route('POST /besoins/store', ['BesoinController', 'store']);
Flight::route('GET /besoins/edit/@id', ['BesoinController', 'edit']);
Flight::route('POST /besoins/update/@id', ['BesoinController', 'update']);
Flight::route('GET /besoins/delete/@id', ['BesoinController', 'delete']);
Flight::route('GET /besoins/@id', ['BesoinController', 'show']);

// Routes pour les dons
Flight::route('GET /dons', ['DonController', 'index']);
Flight::route('GET /dons/create', ['DonController', 'create']);
Flight::route('POST /dons/store', ['DonController', 'store']);
Flight::route('GET /dons/edit/@id', ['DonController', 'edit']);
Flight::route('POST /dons/update/@id', ['DonController', 'update']);
Flight::route('GET /dons/delete/@id', ['DonController', 'delete']);
Flight::route('GET /dons/@id', ['DonController', 'show']);

// Routes pour les distributions
Flight::route('GET /distributions', ['DistributionController', 'index']);
Flight::route('GET /distributions/create', ['DistributionController', 'create']);
Flight::route('GET /distributions/create/@besoin_id', ['DistributionController', 'createForBesoin']);
Flight::route('POST /distributions/store', ['DistributionController', 'store']);
Flight::route('POST /distributions/delete/@id', ['DistributionController', 'delete']);

// Routes pour les achats (achat de besoins avec dons en argent)
Flight::route('GET /achats', ['AchatController', 'index']);
Flight::route('GET /achats/create', ['AchatController', 'create']);
Flight::route('GET /achats/create/@besoin_id', ['AchatController', 'createForBesoin']);
Flight::route('POST /achats/store', ['AchatController', 'store']);
Flight::route('POST /achats/delete/@id', ['AchatController', 'delete']);
Flight::route('GET /recap', ['AchatController', 'recap']);

// Routes pour les catégories
Flight::route('GET /categories', ['CategorieController', 'index']);
Flight::route('GET /categories/create', ['CategorieController', 'create']);
Flight::route('POST /categories/store', ['CategorieController', 'store']);
Flight::route('GET /categories/edit/@id', ['CategorieController', 'edit']);
Flight::route('POST /categories/update/@id', ['CategorieController', 'update']);
Flight::route('GET /categories/delete/@id', ['CategorieController', 'delete']);

// Routes pour les types d'articles
Flight::route('GET /types-articles/create', ['TypeArticleController', 'create']);
Flight::route('POST /types-articles/store', ['TypeArticleController', 'store']);
Flight::route('GET /types-articles/edit/@id', ['TypeArticleController', 'edit']);
Flight::route('POST /types-articles/update/@id', ['TypeArticleController', 'update']);
Flight::route('GET /types-articles/delete/@id', ['TypeArticleController', 'delete']);

// API pour récupérer les dons disponibles pour un type d'article
Flight::route('GET /api/dons-disponibles/@type_id', ['DonController', 'getDonsDisponibles']);
Flight::route('GET /api/recap-stats', ['AchatController', 'getRecapStats']);

// Routes pour les ventes de dons (V3)
Flight::route('GET /ventes', ['VenteController', 'index']);
Flight::route('GET /ventes/create', ['VenteController', 'create']);
Flight::route('GET /ventes/create/@don_id', ['VenteController', 'createForDon']);
Flight::route('POST /ventes/store', ['VenteController', 'store']);
Flight::route('GET /ventes/config', ['VenteController', 'config']);
Flight::route('POST /ventes/config', ['VenteController', 'updateConfig']);
Flight::route('GET /api/ventes/check/@don_id', ['VenteController', 'checkVendable']);

// Routes pour la réinitialisation (V3)
Flight::route('GET /reset', ['ResetController', 'index']);
Flight::route('POST /reset', ['ResetController', 'reset']);
Flight::route('POST /reset/complete', ['ResetController', 'resetComplete']);
