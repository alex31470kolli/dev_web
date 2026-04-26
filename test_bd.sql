-- SCRIPT D'INSERTION POUR TESTS
-- Base de données : siteStage

/* Ce fichier test a été généré par IA */

USE siteStage;

-- 1. Nettoyage des tables (Ordre respectant les contraintes FK)
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE Stage;
TRUNCATE TABLE Canditature;
TRUNCATE TABLE Document;
TRUNCATE TABLE Trace;
TRUNCATE TABLE Offre;
TRUNCATE TABLE Entreprise;
TRUNCATE TABLE Utilisateur;
TRUNCATE TABLE Filiere;
SET FOREIGN_KEY_CHECKS = 1;

-- 2. Insertion des Filières (pour tes filtres admin)
INSERT INTO Filiere (nom_filiere, niveau) VALUES 
('GI', 'ING1'), ('GM - Data', 'ING1'), ('GM - Finance', 'ING1'),
('GI', 'ING2'), ('GM - Data', 'ING2'), ('GM - Finance', 'ING2'),
('GI', 'ING3'), ('HPDA', 'ING3'), ('Cybersécurité', 'ING3');

-- 3. Insertion des Utilisateurs
-- MOT DE PASSE POUR TOUS : password123
INSERT INTO Utilisateur (prenom, nom_utilisateur, mail, mot_de_passe, role_utilisateur, annee, filiere, est_valide) VALUES
('Jean', 'Admin', 'admin@cytech.fr', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'admin', NULL, NULL, 1),
('Alice', 'Etudiant', 'alice@cytech.fr', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'etudiant', 'ING1', 'GI', 1),
('Bob', 'Etudiant', 'bob@cytech.fr', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'etudiant', 'ING2', 'GM - Data', 1),
('Marc', 'Referent', 'contact@google.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'entreprise', NULL, NULL, 1),
('Sophie', 'RH', 'rh@amazon.fr', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'entreprise', NULL, NULL, 0);

-- 4. Insertion des Entreprises
INSERT INTO Entreprise (nom_entreprise, id_referent) VALUES
('Google', 4),
('Amazon', 5);

-- 5. Insertion des Offres
INSERT INTO Offre (id_entreprise, titre, description, lieu, gratification, est_pour_tous) VALUES
(1, 'Développeur Fullstack', 'Stage de 6 mois sur React/Node', 'Paris', 1200.00, 0),
(1, 'Data Analyst', 'Analyse de données Big Data', 'Remote', 1100.00, 0),
(2, 'Ingénieur Cloud', 'Infrastructure AWS', 'Clichy', 1300.00, 1);

-- 6. Insertion des Candidatures
-- statut : 0 (attente), 1 (acceptée), 2 (refusée)
INSERT INTO Canditature (id_utilisateur, id_offre, id_entreprise, statut) VALUES
(2, 1, 1, 0),
(3, 3, 2, 1);

-- 7. Insertion des Stages (pour tester le dashboard admin)
-- Un stage "En cours" (devrait apparaître dans le 1er tableau de l'admin)
INSERT INTO Stage (id_etudiant, id_offre, date_debut_stage, date_fin, etat_suivi) VALUES
(2, 1, '2024-02-01', '2024-08-01', 'En cours');

-- Un stage "Terminé" (devrait apparaître dans le 2ème tableau pour archivage)
INSERT INTO Stage (id_etudiant, id_offre, date_debut_stage, date_fin, etat_suivi) VALUES
(3, 3, '2023-06-01', '2024-01-15', 'Terminé');

-- Un stage déjà "Archivé"
INSERT INTO Stage (id_etudiant, id_offre, date_debut_stage, date_fin, etat_suivi) VALUES
(2, 2, '2023-01-01', '2023-06-30', 'Archivé');
