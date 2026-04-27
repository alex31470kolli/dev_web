-- 1. INSERTION D'UTILISATEURS (Mot de passe haché pour tous : '1234')
-- Note : est_valide = 1 pour permettre la connexion immédiate
INSERT INTO Utilisateur (prenom, nom_utilisateur, mail, mot_de_passe, role_utilisateur, annee, filiere, est_valide)
VALUES 
('Alice', 'Etudiant_ING1', 'alice@cytech.fr', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'etudiant', 'ING1', 'GI', 1),
('Bob', 'Etudiant_ING3', 'bob@cytech.fr', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'etudiant', 'ING3', 'Cybersécurité', 1),
('Marc', 'Referent_TechCorp', 'marc@techcorp.com', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'entreprise', NULL, NULL, 1),
('Julie', 'Referent_DataFlow', 'julie@dataflow.fr', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'entreprise', NULL, NULL, 1);

-- 2. INSERTION D'ENTREPRISES 
-- On lie les référents créés ci-dessus (IDs 3 et 4 si la table était vide)
INSERT INTO Entreprise (nom_entreprise, id_referent) 
VALUES 
('TechCorp', (SELECT id_utilisateur FROM Utilisateur WHERE mail = 'marc@techcorp.com')),
('DataFlow', (SELECT id_utilisateur FROM Utilisateur WHERE mail = 'julie@dataflow.fr'));

-- 3. INSERTION D'OFFRES DE STAGE
INSERT INTO Offre (titre, missions, competences, filiere, date_debut, date_fin, id_entreprise)
VALUES 
-- Offre pour TechCorp (Informatique ING1/ING2)
('Stage Développeur Web Junior', 
 'Développement de nouvelles fonctionnalités sur notre plateforme interne en utilisant PHP et SQL.', 
 'HTML, CSS, PHP, MySQL', 
 'GI', 
 '2026-06-01', '2026-08-31', 
 (SELECT id_entreprise FROM Entreprise WHERE nom_entreprise = 'TechCorp')),

-- Offre pour TechCorp (Cybersécurité ING3)
('Analyste Sécurité - SOC', 
 'Surveillance des réseaux, analyse de logs et détection d''intrusions.', 
 'Wireshark, Linux, Scripting Python', 
 'Cybersécurité', 
 '2026-02-01', '2026-07-31', 
 (SELECT id_entreprise FROM Entreprise WHERE nom_entreprise = 'TechCorp')),

-- Offre pour DataFlow (Data/IA ING3)
('Assistant Data Scientist', 
 'Nettoyage de jeux de données et mise en place de modèles de prédiction simples.', 
 'Python, Pandas, Scikit-learn', 
 'IA', 
 '2026-03-01', '2026-08-31', 
 (SELECT id_entreprise FROM Entreprise WHERE nom_entreprise = 'DataFlow'));
