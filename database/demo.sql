-- =============================================================
-- Shoop Moda Style — données de démonstration (optionnel)
-- À importer APRÈS schema.sql.
-- Supprimer ce fichier du serveur après la mise en production.
-- =============================================================

SET NAMES utf8mb4;

DELETE FROM `product_images`;
DELETE FROM `products`;
DELETE FROM `categories`;
DELETE FROM `promo_codes`;

ALTER TABLE `categories` AUTO_INCREMENT = 1;
ALTER TABLE `products` AUTO_INCREMENT = 1;
ALTER TABLE `product_images` AUTO_INCREMENT = 1;
ALTER TABLE `promo_codes` AUTO_INCREMENT = 1;

-- ----------------------------
-- Catégories
-- ----------------------------
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `sort_order`, `is_active`) VALUES
  (1, 'Femme', 'femme', 'dress', 1, 1),
  (2, 'Enfants', 'enfants', 'tshirt', 2, 1),
  (3, 'Pyjamas & Ensembles', 'pyjamas', 'moon', 3, 1),
  (4, 'Robes', 'robes', 'jacket', 4, 1);

-- ----------------------------
-- Produits
-- ----------------------------
INSERT INTO `products`
(`id`, `category_id`, `title`, `slug`, `description`, `price`, `sale_price`, `rating`, `stock`,
 `sizes`, `colors`, `is_flash`, `is_featured`, `is_active`) VALUES
(1, 3, 'Ensemble pyjama satin « Istanbul »', 'ensemble-pyjama-satin-istanbul',
 'Ensemble deux pièces en satin fluide, finitions soignées et ceinture élastiquée. Confort léger pour les soirées et les matins tranquilles.',
 299.00, 249.00, 4.9, 14,
 '["S","M","L","XL","XXL"]',
 '[{"name":"Marron","hex":"#704F38"},{"name":"Beige","hex":"#C2A691"},{"name":"Noir","hex":"#1F2029"}]',
 1, 1, 1),

(2, 3, 'Pyjama coton « Bosphore »', 'pyjama-coton-bosphore',
 'Pyjama en coton doux, coupe ample et respirante. Idéal pour dormir confortablement en toute saison.',
 229.00, NULL, 4.8, 20,
 '["S","M","L","XL"]',
 '[{"name":"Crème","hex":"#EFE7DE"},{"name":"Marron","hex":"#704F38"}]',
 1, 0, 1),

(3, 4, 'Robe longue « Sultan »', 'robe-longue-sultan',
 'Robe longue à la coupe fluide, manches longues et ceinture assortie. Une pièce élégante pour les occasions spéciales.',
 349.00, NULL, 5.0, 8,
 '["S","M","L","XL"]',
 '[{"name":"Noir","hex":"#1F2029"},{"name":"Beige","hex":"#C2A691"}]',
 0, 1, 1),

(4, 4, 'Robe plissée « Élégance »', 'robe-plissee-elegance',
 'Robe midi plissée au tombé léger. Se porte facilement au quotidien comme en soirée.',
 299.00, 249.00, 4.7, 10,
 '["S","M","L","XL","XXL"]',
 '[{"name":"Marron","hex":"#704F38"},{"name":"Crème","hex":"#EFE7DE"}]',
 1, 0, 1),

(5, 1, 'Chemise en soie « Perle »', 'chemise-soie-perle',
 'Chemise en soie au toucher satiné, col classique et boutons nacrés. Parfaite avec un pantalon taille haute.',
 259.00, NULL, 4.8, 12,
 '["S","M","L","XL"]',
 '[{"name":"Crème","hex":"#EFE7DE"},{"name":"Beige","hex":"#C2A691"}]',
 0, 1, 1),

(6, 1, 'Pantalon taille haute « Moda »', 'pantalon-taille-haute-moda',
 'Pantalon taille haute à jambe droite, matière stretch confortable. Une base indispensable du dressing.',
 179.00, NULL, 4.6, 25,
 '["S","M","L","XL","XXL"]',
 '[{"name":"Noir","hex":"#1F2029"},{"name":"Marron","hex":"#704F38"}]',
 0, 0, 1),

(7, 1, 'Trench « Automne »', 'trench-automne',
 'Trench mi-long à ceinture, doublure légère et poches passepoilées. La pièce de mi-saison.',
 429.00, 379.00, 4.9, 6,
 '["S","M","L","XL"]',
 '[{"name":"Beige","hex":"#C2A691"},{"name":"Noir","hex":"#1F2029"}]',
 0, 1, 1),

(8, 1, 'T-shirt coton « Douceur »', 't-shirt-coton-douceur',
 'T-shirt en coton peigné, col rond et coupe droite. Doux, simple, à porter tous les jours.',
 99.00, NULL, 4.5, 40,
 '["S","M","L","XL","XXL"]',
 '[{"name":"Blanc","hex":"#FFFFFF"},{"name":"Marron","hex":"#704F38"}]',
 0, 0, 1),

(9, 2, 'Ensemble enfant « Petit Prince »', 'ensemble-enfant-petit-prince',
 'Ensemble deux pièces pour enfant, coton doux et ceinture élastiquée. Confort toute la journée.',
 149.00, NULL, 4.8, 18,
 '["2-3 ans","4-5 ans","6-7 ans","8-9 ans"]',
 '[{"name":"Bleu nuit","hex":"#1F2029"},{"name":"Beige","hex":"#C2A691"}]',
 0, 0, 1),

(10, 2, 'Pyjama enfant « Étoile »', 'pyjama-enfant-etoile',
 'Pyjama enfant en coton respirant, motifs discrets et poignets doux. Pensé pour bien dormir.',
 129.00, 109.00, 4.9, 22,
 '["2-3 ans","4-5 ans","6-7 ans","8-9 ans"]',
 '[{"name":"Crème","hex":"#EFE7DE"},{"name":"Marron","hex":"#704F38"}]',
 1, 0, 1),

(11, 2, 'Robe enfant « Fleur »', 'robe-enfant-fleur',
 'Robe enfant légère à manches courtes, doublure coton. Jolie pour les fêtes de famille.',
 169.00, NULL, 4.7, 15,
 '["2-3 ans","4-5 ans","6-7 ans"]',
 '[{"name":"Beige","hex":"#C2A691"},{"name":"Crème","hex":"#EFE7DE"}]',
 0, 0, 1),

(12, 3, 'Ensemble famille « Cocooning »', 'ensemble-famille-cocooning',
 'Ensemble coordonné pour elle et les enfants, matière douce et chaude. Parfait pour les moments à la maison.',
 399.00, 349.00, 5.0, 9,
 '["S","M","L","XL","XXL"]',
 '[{"name":"Beige","hex":"#C2A691"},{"name":"Marron","hex":"#704F38"},{"name":"Crème","hex":"#EFE7DE"}]',
 1, 1, 1);

-- ----------------------------
-- Images (placeholders générés, remplacés via l'admin)
-- ----------------------------
INSERT INTO `product_images` (`product_id`, `image_path`, `sort_order`, `is_primary`) VALUES
  (1, 'assets/images/placeholders/ensemble-pyjama-satin-istanbul.svg', 1, 1),
  (1, 'assets/images/placeholders/ensemble-pyjama-satin-istanbul-2.svg', 2, 0),
  (2, 'assets/images/placeholders/pyjama-coton-bosphore.svg', 1, 1),
  (2, 'assets/images/placeholders/pyjama-coton-bosphore-2.svg', 2, 0),
  (3, 'assets/images/placeholders/robe-longue-sultan.svg', 1, 1),
  (3, 'assets/images/placeholders/robe-longue-sultan-2.svg', 2, 0),
  (4, 'assets/images/placeholders/robe-plissee-elegance.svg', 1, 1),
  (4, 'assets/images/placeholders/robe-plissee-elegance-2.svg', 2, 0),
  (5, 'assets/images/placeholders/chemise-soie-perle.svg', 1, 1),
  (5, 'assets/images/placeholders/chemise-soie-perle-2.svg', 2, 0),
  (6, 'assets/images/placeholders/pantalon-taille-haute-moda.svg', 1, 1),
  (6, 'assets/images/placeholders/pantalon-taille-haute-moda-2.svg', 2, 0),
  (7, 'assets/images/placeholders/trench-automne.svg', 1, 1),
  (7, 'assets/images/placeholders/trench-automne-2.svg', 2, 0),
  (8, 'assets/images/placeholders/t-shirt-coton-douceur.svg', 1, 1),
  (8, 'assets/images/placeholders/t-shirt-coton-douceur-2.svg', 2, 0),
  (9, 'assets/images/placeholders/ensemble-enfant-petit-prince.svg', 1, 1),
  (9, 'assets/images/placeholders/ensemble-enfant-petit-prince-2.svg', 2, 0),
  (10, 'assets/images/placeholders/pyjama-enfant-etoile.svg', 1, 1),
  (10, 'assets/images/placeholders/pyjama-enfant-etoile-2.svg', 2, 0),
  (11, 'assets/images/placeholders/robe-enfant-fleur.svg', 1, 1),
  (11, 'assets/images/placeholders/robe-enfant-fleur-2.svg', 2, 0),
  (12, 'assets/images/placeholders/ensemble-famille-cocooning.svg', 1, 1),
  (12, 'assets/images/placeholders/ensemble-famille-cocooning-2.svg', 2, 0);

-- ----------------------------
-- Code promo de bienvenue
-- ----------------------------
INSERT INTO `promo_codes`
(`code`, `discount_type`, `discount_value`, `min_order`, `max_uses`, `used_count`, `is_active`, `expires_at`) VALUES
  ('BIENVENUE', 'percent', 10.00, 200.00, 100, 0, 1, '2027-12-31 23:59:59');
