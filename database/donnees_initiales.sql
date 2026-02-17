USE bngrc_dons;

-- Vider les tables existantes
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE ventes;
TRUNCATE TABLE achats;
TRUNCATE TABLE distributions;
TRUNCATE TABLE dons;
TRUNCATE TABLE besoins;
TRUNCATE TABLE villes;
TRUNCATE TABLE types_articles;
TRUNCATE TABLE categories;
TRUNCATE TABLE regions;
SET FOREIGN_KEY_CHECKS = 1;

-- Catégories
INSERT INTO categories (id, nom, description) VALUES
(1, 'Nature', 'Produits alimentaires et de première nécessité'),
(2, 'Matériaux', 'Matériaux de construction'),
(3, 'Argent', 'Dons monétaires');

-- Régions
INSERT INTO regions (id, nom) VALUES
(1, 'Atsimo-Atsinanana'),
(2, 'Atsinanana'),
(3, 'Diana'),
(4, 'Menabe'),
(5, 'Vatovavy-Fitovinany');

-- Villes
INSERT INTO villes (id, nom, region_id, population_sinistree) VALUES
(1, 'Toamasina', 2, 50000),
(2, 'Mananjary', 5, 30000),
(3, 'Farafangana', 1, 25000),
(4, 'Nosy Be', 3, 15000),
(5, 'Morondava', 4, 35000);

-- Types d'articles
INSERT INTO types_articles (id, nom, unite, categorie_id, prix_unitaire) VALUES
(1, 'Riz', 'kg', 1, 3000),
(2, 'Eau', 'L', 1, 1000),
(3, 'Tôle', 'feuille', 2, 25000),
(4, 'Bâche', 'pièce', 2, 15000),
(5, 'Ariary', 'Ar', 3, NULL),
(6, 'Huile', 'L', 1, 6000),
(7, 'Clous', 'kg', 2, 8000),
(8, 'Bois', 'm³', 2, 10000),
(9, 'Haricots', 'kg', 1, 4000),
(10, 'Groupe électrogène', 'pièce', 2, 6750000);

-- Besoins
INSERT INTO besoins (id, ville_id, type_article_id, quantite_necessaire, quantite_recue, date_enregistrement, statut) VALUES
(1, 1, 1, 800, 0, '2026-02-16', 'en_attente'),
(2, 1, 2, 1500, 0, '2026-02-15', 'en_attente'),
(3, 1, 3, 120, 0, '2026-02-16', 'en_attente'),
(4, 1, 4, 200, 0, '2026-02-15', 'en_attente'),
(5, 1, 5, 12000000, 0, '2026-02-16', 'en_attente'),
(6, 2, 1, 500, 0, '2026-02-15', 'en_attente'),
(7, 2, 6, 120, 0, '2026-02-16', 'en_attente'),
(8, 2, 3, 80, 0, '2026-02-15', 'en_attente'),
(9, 2, 7, 60, 0, '2026-02-16', 'en_attente'),
(10, 2, 5, 6000000, 0, '2026-02-15', 'en_attente'),
(11, 3, 1, 600, 0, '2026-02-16', 'en_attente'),
(12, 3, 2, 1000, 0, '2026-02-15', 'en_attente'),
(13, 3, 4, 150, 0, '2026-02-16', 'en_attente'),
(14, 3, 8, 100, 0, '2026-02-15', 'en_attente'),
(15, 3, 5, 8000000, 0, '2026-02-16', 'en_attente'),
(16, 4, 1, 300, 0, '2026-02-15', 'en_attente'),
(17, 4, 9, 200, 0, '2026-02-16', 'en_attente'),
(18, 4, 3, 40, 0, '2026-02-15', 'en_attente'),
(19, 4, 7, 30, 0, '2026-02-16', 'en_attente'),
(20, 4, 5, 4000000, 0, '2026-02-15', 'en_attente'),
(21, 5, 1, 700, 0, '2026-02-16', 'en_attente'),
(22, 5, 2, 1200, 0, '2026-02-15', 'en_attente'),
(23, 5, 4, 180, 0, '2026-02-16', 'en_attente'),
(24, 5, 8, 150, 0, '2026-02-15', 'en_attente'),
(25, 5, 5, 10000000, 0, '2026-02-16', 'en_attente'),
(26, 1, 10, 3, 0, '2026-02-15', 'en_attente');

-- Dons
INSERT INTO dons (id, type_article_id, quantite_totale, quantite_disponible, donateur, date_reception, description) VALUES
(1, 5, 5000000, 5000000, 'Particulier', '2026-02-16', NULL),
(2, 5, 3000000, 3000000, 'Entreprise locale', '2026-02-16', NULL),
(3, 5, 4000000, 4000000, 'ONG', '2026-02-17', NULL),
(4, 5, 1500000, 1500000, 'Association', '2026-02-17', NULL),
(5, 5, 6000000, 6000000, 'Gouvernement', '2026-02-17', NULL),
(6, 1, 400, 400, 'Particulier', '2026-02-16', NULL),
(7, 2, 600, 600, 'Entreprise locale', '2026-02-16', NULL),
(8, 3, 50, 50, 'ONG', '2026-02-17', NULL),
(9, 4, 70, 70, 'Association', '2026-02-17', NULL),
(10, 9, 100, 100, 'Gouvernement', '2026-02-17', NULL),
(11, 1, 2000, 2000, 'Particulier', '2026-02-18', NULL),
(12, 3, 300, 300, 'Entreprise locale', '2026-02-18', NULL),
(13, 2, 5000, 5000, 'ONG', '2026-02-18', NULL),
(14, 5, 20000000, 20000000, 'Association', '2026-02-19', NULL),
(15, 4, 500, 500, 'Gouvernement', '2026-02-19', NULL),
(16, 9, 88, 88, 'Particulier', '2026-02-17', NULL);

-- Configuration
INSERT INTO config (cle, valeur, description) VALUES
('pourcentage_reduction_vente', '10', 'Pourcentage de réduction lors de la vente d''un don (défaut: 10%)');
