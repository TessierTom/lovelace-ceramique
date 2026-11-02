-- Jeu de données de test commun à toute l'équipe (compatible avec docs/MPD.sql)
-- 1 atelier · 5 comptes · 6 étapes · 8 modèles de pièce · 30 commandes · ~75 tickets
-- Ordre imposé par les clés étrangères : d'abord les tables référencées, ensuite celles qui les référencent.

-- Atelier
INSERT INTO organisation (nom) VALUES ('Atelier Claire Martin');

-- Comptes : 1 Administrateur, 4 Collaborateurs
-- Mot de passe de test pour tous les comptes : ceramique (haché avec password_hash() de PHP, jamais en clair)
INSERT INTO utilisateur (identifiant, mdp, email, nom, organisation_id, role) VALUES
 ('claire', '$2y$10$pLmg3szwTg.VwQQ4VlGfmO5YKGTNCZiSBbDqs553hgVOswi0B4Dza', 'claire@atelier.fr', 'Claire Martin', 1, 'Administrateur'),
 ('hugo', '$2y$10$pLmg3szwTg.VwQQ4VlGfmO5YKGTNCZiSBbDqs553hgVOswi0B4Dza', 'hugo@atelier.fr', 'Hugo Bernard', 1, 'Collaborateur'),
 ('ines', '$2y$10$pLmg3szwTg.VwQQ4VlGfmO5YKGTNCZiSBbDqs553hgVOswi0B4Dza', 'ines@atelier.fr', 'Inès Lambert', 1, 'Collaborateur'),
 ('lea', '$2y$10$pLmg3szwTg.VwQQ4VlGfmO5YKGTNCZiSBbDqs553hgVOswi0B4Dza', 'lea@atelier.fr', 'Léa Fontaine', 1, 'Collaborateur'),
 ('malik', '$2y$10$pLmg3szwTg.VwQQ4VlGfmO5YKGTNCZiSBbDqs553hgVOswi0B4Dza', 'malik@atelier.fr', 'Malik Benali', 1, 'Collaborateur');

-- Étapes de fabrication, dans l'ordre de l'atelier
INSERT INTO etape (nom, ordre) VALUES
 ('Façonnage', 1),
 ('Séchage', 2),
 ('Cuisson biscuit', 3),
 ('Émaillage', 4),
 ('Cuisson émail', 5),
 ('Terminé', 6);

-- Modèles de pièce
INSERT INTO piece (nom) VALUES
 ('Tasse'),
 ('Bol'),
 ('Assiette'),
 ('Vase'),
 ('Saladier'),
 ('Mug'),
 ('Théière'),
 ('Plat');

-- 30 commandes créées par Claire (Admin, id 1), de septembre à novembre 2026
-- Répartition des statuts : 8 livrées, 5 terminées, 12 en cours, 5 à faire
INSERT INTO commande (nom_client, date_creation, statut, utilisateur_id) VALUES
 ('Café Lumière', '2026-09-01', 'livree', 1),
 ('Restaurant Le Potager', '2026-09-03', 'livree', 1),
 ('Mme Durand', '2026-09-05', 'livree', 1),
 ('Boutique Terre & Feu', '2026-09-07', 'livree', 1),
 ('Hôtel des Canuts', '2026-09-09', 'livree', 1),
 ('M. Bernardi', '2026-09-11', 'livree', 1),
 ('Épicerie Fine Bellecour', '2026-09-13', 'livree', 1),
 ('Salon de thé Camélia', '2026-09-15', 'livree', 1),
 ('Mme Nguyen', '2026-09-18', 'terminee', 1),
 ('Bistrot du Vieux Lyon', '2026-09-20', 'terminee', 1),
 ('Fleuriste Pétale', '2026-09-22', 'terminee', 1),
 ('M. Lefèvre', '2026-09-24', 'terminee', 1),
 ('Concept store Argile', '2026-09-26', 'terminee', 1),
 ('Crêperie Bretonne', '2026-09-28', 'en_cours', 1),
 ('Mme Moreau', '2026-09-30', 'en_cours', 1),
 ('Restaurant Saveurs du Monde', '2026-10-03', 'en_cours', 1),
 ('Galerie Céladon', '2026-10-05', 'en_cours', 1),
 ('M. Haddad', '2026-10-07', 'en_cours', 1),
 ('Brasserie Saint-Paul', '2026-10-09', 'en_cours', 1),
 ('Mme Rossi', '2026-10-11', 'en_cours', 1),
 ('Atelier Zen Yoga', '2026-10-13', 'en_cours', 1),
 ('Cave à vin Le Cep', '2026-10-15', 'en_cours', 1),
 ('M. Dubois', '2026-10-18', 'en_cours', 1),
 ('Pâtisserie Douceur', '2026-10-20', 'en_cours', 1),
 ('Mme Petit', '2026-10-22', 'en_cours', 1),
 ('Chambre d''hôtes La Glycine', '2026-10-24', 'a_faire', 1),
 ('M. Garnier', '2026-10-26', 'a_faire', 1),
 ('Coffee shop Grain de Sable', '2026-10-28', 'a_faire', 1),
 ('Mme Laurent', '2026-10-30', 'a_faire', 1),
 ('Traiteur Les Délices', '2026-11-02', 'a_faire', 1);

-- Tickets : une ligne = une pièce à fabriquer dans une commande
-- utilisateur_id = artisan assigné (NULL = pas encore assigné) ; etape_id = étape actuelle de la pièce
-- Commandes livrées ou terminées : toutes les pièces sont à l'étape Terminé (6)
-- Commandes à faire : pièces au Façonnage (1), pas encore assignées
INSERT INTO commande_ticket (commande_id, piece_id, etape_id, utilisateur_id) VALUES
 (1, 1, 6, 4),          -- commande 1 : Tasse, Terminé
 (2, 4, 6, 3),          -- commande 2 : Vase, Terminé
 (2, 2, 6, 2),          -- commande 2 : Bol, Terminé
 (3, 1, 6, 2),          -- commande 3 : Tasse, Terminé
 (3, 2, 6, 3),          -- commande 3 : Bol, Terminé
 (3, 4, 6, 2),          -- commande 3 : Vase, Terminé
 (3, 4, 6, 5),          -- commande 3 : Vase, Terminé
 (4, 8, 6, 4),          -- commande 4 : Plat, Terminé
 (4, 1, 6, 3),          -- commande 4 : Tasse, Terminé
 (5, 6, 6, 4),          -- commande 5 : Mug, Terminé
 (5, 3, 6, 3),          -- commande 5 : Assiette, Terminé
 (5, 6, 6, 2),          -- commande 5 : Mug, Terminé
 (5, 2, 6, 5),          -- commande 5 : Bol, Terminé
 (6, 6, 6, 4),          -- commande 6 : Mug, Terminé
 (7, 1, 6, 5),          -- commande 7 : Tasse, Terminé
 (7, 2, 6, 5),          -- commande 7 : Bol, Terminé
 (7, 2, 6, 4),          -- commande 7 : Bol, Terminé
 (8, 4, 6, 2),          -- commande 8 : Vase, Terminé
 (8, 1, 6, 3),          -- commande 8 : Tasse, Terminé
 (8, 5, 6, 2),          -- commande 8 : Saladier, Terminé
 (9, 2, 6, 5),          -- commande 9 : Bol, Terminé
 (9, 5, 6, 5),          -- commande 9 : Saladier, Terminé
 (10, 3, 6, 4),         -- commande 10 : Assiette, Terminé
 (10, 6, 6, 3),         -- commande 10 : Mug, Terminé
 (10, 5, 6, 2),         -- commande 10 : Saladier, Terminé
 (11, 4, 6, 3),         -- commande 11 : Vase, Terminé
 (11, 8, 6, 5),         -- commande 11 : Plat, Terminé
 (12, 4, 6, 4),         -- commande 12 : Vase, Terminé
 (12, 1, 6, 3),         -- commande 12 : Tasse, Terminé
 (12, 1, 6, 4),         -- commande 12 : Tasse, Terminé
 (13, 5, 6, 2),         -- commande 13 : Saladier, Terminé
 (13, 4, 6, 4),         -- commande 13 : Vase, Terminé
 (13, 4, 6, 5),         -- commande 13 : Vase, Terminé
 (13, 7, 6, 5),         -- commande 13 : Théière, Terminé
 (14, 5, 2, 3),         -- commande 14 : Saladier, Séchage
 (14, 5, 5, 5),         -- commande 14 : Saladier, Cuisson émail
 (15, 6, 2, 3),         -- commande 15 : Mug, Séchage
 (15, 8, 1, 2),         -- commande 15 : Plat, Façonnage
 (15, 2, 2, 3),         -- commande 15 : Bol, Séchage
 (15, 7, 5, 2),         -- commande 15 : Théière, Cuisson émail
 (16, 7, 5, 5),         -- commande 16 : Théière, Cuisson émail
 (16, 5, 5, 2),         -- commande 16 : Saladier, Cuisson émail
 (16, 2, 5, 4),         -- commande 16 : Bol, Cuisson émail
 (16, 6, 1, 4),         -- commande 16 : Mug, Façonnage
 (17, 3, 4, 2),         -- commande 17 : Assiette, Émaillage
 (17, 5, 5, 3),         -- commande 17 : Saladier, Cuisson émail
 (17, 2, 3, NULL),      -- commande 17 : Bol, Cuisson biscuit
 (17, 4, 2, 4),         -- commande 17 : Vase, Séchage
 (18, 1, 5, 4),         -- commande 18 : Tasse, Cuisson émail
 (18, 8, 1, 2),         -- commande 18 : Plat, Façonnage
 (19, 5, 2, 2),         -- commande 19 : Saladier, Séchage
 (19, 4, 5, 2),         -- commande 19 : Vase, Cuisson émail
 (19, 2, 4, 2),         -- commande 19 : Bol, Émaillage
 (20, 3, 4, NULL),      -- commande 20 : Assiette, Émaillage
 (20, 3, 3, NULL),      -- commande 20 : Assiette, Cuisson biscuit
 (21, 4, 5, 3),         -- commande 21 : Vase, Cuisson émail
 (21, 5, 4, 4),         -- commande 21 : Saladier, Émaillage
 (21, 8, 5, 5),         -- commande 21 : Plat, Cuisson émail
 (21, 2, 2, 3),         -- commande 21 : Bol, Séchage
 (22, 6, 1, NULL),      -- commande 22 : Mug, Façonnage
 (23, 4, 1, 2),         -- commande 23 : Vase, Façonnage
 (23, 1, 2, 2),         -- commande 23 : Tasse, Séchage
 (24, 6, 1, NULL),      -- commande 24 : Mug, Façonnage
 (25, 5, 4, 3),         -- commande 25 : Saladier, Émaillage
 (25, 3, 5, NULL),      -- commande 25 : Assiette, Cuisson émail
 (26, 4, 1, NULL),      -- commande 26 : Vase, Façonnage
 (26, 8, 1, NULL),      -- commande 26 : Plat, Façonnage
 (26, 7, 1, NULL),      -- commande 26 : Théière, Façonnage
 (26, 4, 1, NULL),      -- commande 26 : Vase, Façonnage
 (27, 2, 1, NULL),      -- commande 27 : Bol, Façonnage
 (28, 6, 1, NULL),      -- commande 28 : Mug, Façonnage
 (28, 7, 1, NULL),      -- commande 28 : Théière, Façonnage
 (28, 7, 1, NULL),      -- commande 28 : Théière, Façonnage
 (28, 8, 1, NULL),      -- commande 28 : Plat, Façonnage
 (29, 2, 1, NULL),      -- commande 29 : Bol, Façonnage
 (30, 7, 1, NULL);      -- commande 30 : Théière, Façonnage
