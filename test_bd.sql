/*
 SCRIPT DE DONNÉES DE TEST POUR SITESTAGE
 Mot de passe pour tous les comptes : 1234
 Hachage : $2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6
*/

-- 1. INSERTION DES UTILISATEURS (Étudiants, Entreprises, Admin supp)
INSERT INTO Utilisateur (prenom, nom_utilisateur, mail, mot_de_passe, role_utilisateur, annee, filiere, est_valide)
VALUES 
-- Étudiants
('Alice', 'Etudiant_ING1', 'alice@cytech.fr', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'etudiant', 'ING1', 'GI', 1),
('Bob', 'Etudiant_ING2', 'bob@cytech.fr', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'etudiant', 'ING2', 'GM - Data', 1),
('Charlie', 'Etudiant_ING3', 'charlie@cytech.fr', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'etudiant', 'ING3', 'Cybersécurité', 1),
-- Comptes entreprises
('Marc', 'Referent_Tech1', 'marc@tech1.com', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'entreprise', NULL, NULL, 1),
('Julie', 'Referent_Cloud2', 'julie@cloud2.fr', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'entreprise', NULL, NULL, 1),
-- Admin supplémentaire
('Sophie', 'Admin_Test', 'sophie@admin.fr', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'admin', NULL, NULL, 1);

-- 2. INSERTION DES ENTREPRISES
INSERT INTO Entreprise (nom_entreprise, id_referent) 
VALUES 
('Entreprise Tech 1', (SELECT id_utilisateur FROM Utilisateur WHERE mail = 'marc@tech1.com')),
('Services Cloud 2', (SELECT id_utilisateur FROM Utilisateur WHERE mail = 'julie@cloud2.fr'));

-- 3. INSERTION DES OFFRES
INSERT INTO Offre (titre, missions, competences, filiere, date_debut, date_fin, id_entreprise, mis_en_avant, cible_mise_en_avant)
VALUES 
-- Offre Standard
('Développeur Fullstack Junior', 'Développement Web PHP/JS', 'PHP, SQL, JS', 'GI', '2026-06-01', '2026-08-31', 1, 0, 'TOUS'),
-- Offre Mise en avant pour ING3
('Analyste SOC - Cybersécurité', 'Surveillance réseau et logs', 'Linux, Wireshark', 'Cybersécurité', '2026-02-01', '2026-07-31', 1, 1, 'ING3'),
-- Offre Mise en avant pour ING1
('Stage Découverte Data', 'Analyse de données simples', 'Excel, Python', 'GM - Data', '2026-05-15', '2026-07-15', 2, 1, 'ING1'),
-- Offre Expirée (pour tester les filtres)
('Stage Ancien', 'Mission terminée', 'N/A', 'GI', '2023-01-01', '2023-03-01', 2, 0, 'TOUS');

-- 4. INSERTION DES CANDIDATURES
INSERT INTO Candidature (id_utilisateur, id_offre, id_entreprise, statut)
VALUES 
-- Alice postule à l'offre 1
((SELECT id_utilisateur FROM Utilisateur WHERE mail = 'alice@cytech.fr'), 1, 1, 0),
-- Bob postule à l'offre 1 (pour tester le fait que l'entreprise voit plusieurs personnes)
((SELECT id_utilisateur FROM Utilisateur WHERE mail = 'bob@cytech.fr'), 1, 1, 0),
-- Charlie postule à l'offre 2
((SELECT id_utilisateur FROM Utilisateur WHERE mail = 'charlie@cytech.fr'), 2, 1, 1); -- Accepté

-- 5. INSERTION DES STAGES (Workflow de test)
INSERT INTO Stage (id_etudiant, id_offre, date_debut_stage, date_fin, etat_suivi, chemin_convention)
VALUES 
-- Stage au début (Attente Admin)
((SELECT id_utilisateur FROM Utilisateur WHERE mail = 'alice@cytech.fr'), 1, '2026-06-01', '2026-08-31', 'Validation Admin', NULL),
-- Stage en cours de signature (Attente Entreprise)
((SELECT id_utilisateur FROM Utilisateur WHERE mail = 'charlie@cytech.fr'), 2, '2026-02-01', '2026-07-31', 'Signature Entreprise', 'convention_test_prevue.pdf'),
-- Stage actif
((SELECT id_utilisateur FROM Utilisateur WHERE mail = 'bob@cytech.fr'), 3, '2026-05-15', '2026-07-15', 'En cours', 'convention_signee_bob.pdf');

-- 6. MESSAGERIE DE TEST
INSERT INTO Message (id_expediteur, id_destinataire, sujet, contenu)
VALUES 
(2, 5, 'Question sur mon stage', "Bonjour Marc, j'ai une question concernant les horaires."),
(5, 2, 'Re: Question sur mon stage', 'Bonjour Alice, nous commençons à 9h00.');

-- 7. DOCUMENTS DE TEST
INSERT INTO Document (id_possesseur, nom_doc, chemin_fichier)
VALUES 
(2, 'CV Alice 2026', 'cv_alice.pdf'),
(3, 'CV Bob Master', 'cv_bob.pdf');
