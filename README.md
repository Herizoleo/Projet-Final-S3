# BNGRC - Application de Gestion des Dons pour les Sinistrés

Application web de suivi des collectes et des distributions de dons pour les sinistrés, développée avec Flight PHP MVC et MySQL.

## 📋 Fonctionnalités

- **Gestion des Villes**: Ajout, modification, suppression des villes par région
- **Gestion des Besoins**: Enregistrement des besoins des sinistrés par ville
  - Besoins en nature (riz, huile, etc.)
  - Besoins en matériaux (tôle, clou, etc.)
  - Besoins en argent
- **Gestion des Dons**: Enregistrement des dons reçus
- **Distribution des Dons**: Attribution des dons aux besoins des villes
- **Tableau de Bord**: Vue d'ensemble avec statistiques et suivi

## 🔒 Règle de Gestion

L'application empêche de distribuer une quantité supérieure au stock disponible. Une erreur est affichée si la quantité demandée dépasse les dons disponibles.

## 🚀 Installation

### Prérequis
- PHP >= 7.4
- MySQL >= 5.7
- Composer
- Serveur web (Apache/Nginx)

### Étapes d'installation

1. **Cloner le projet**
```bash
cd d:\zozo
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Créer la base de données**
```bash
mysql -u root -p < database/schema.sql
```

4. **Configurer la base de données**
Éditer `app/config/database.php` si nécessaire:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'bngrc_dons');
define('DB_USER', 'root');
define('DB_PASS', '');
```

5. **Configurer le serveur web**

**Avec PHP Built-in Server (développement):**
```bash
cd public
php -S localhost:8000
```

**Avec XAMPP/WAMP:**
- Configurer le DocumentRoot vers le dossier `public/`
- Ou copier le projet dans `htdocs/` et accéder via `http://localhost/zozo/public/`

## 📁 Structure du Projet

```
zozo/
├── app/
│   ├── config/
│   │   ├── database.php      # Configuration BDD
│   │   └── routes.php        # Routes de l'application
│   ├── controllers/
│   │   ├── DashboardController.php
│   │   ├── VilleController.php
│   │   ├── BesoinController.php
│   │   ├── DonController.php
│   │   ├── DistributionController.php
│   │   └── CategorieController.php
│   ├── models/
│   │   ├── Model.php
│   │   ├── Ville.php
│   │   ├── Region.php
│   │   ├── Besoin.php
│   │   ├── Don.php
│   │   ├── Distribution.php
│   │   ├── Categorie.php
│   │   └── TypeArticle.php
│   └── views/
│       ├── layout.php
│       ├── dashboard/
│       ├── villes/
│       ├── besoins/
│       ├── dons/
│       ├── distributions/
│       └── categories/
├── database/
│   └── schema.sql            # Script de création BDD
├── public/
│   ├── index.php             # Point d'entrée
│   └── .htaccess             # Réécriture URL
├── composer.json
└── README.md
```

## 🎨 Technologies Utilisées

- **Backend**: PHP 7.4+, Flight Framework
- **Base de données**: MySQL
- **Frontend**: Bootstrap 5, Bootstrap Icons
- **Police**: Inter (Google Fonts)

## 📊 Pages de l'Application

1. **Tableau de Bord** (`/`) - Vue d'ensemble avec statistiques
2. **Villes** (`/villes`) - CRUD des villes
3. **Besoins** (`/besoins`) - CRUD des besoins
4. **Dons** (`/dons`) - CRUD des dons
5. **Distributions** (`/distributions`) - Gestion des distributions
6. **Catégories** (`/categories`) - Liste des catégories et types d'articles

## 📝 Licence

Projet éducatif - BNGRC Madagascar
