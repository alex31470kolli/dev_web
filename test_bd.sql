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
 
 
 -- ==========================================================
-- TEST 1 : L'ÉTUDIANT ALICE POSTULE À L'OFFRE TECHCORP
-- ==========================================================

-- 1.1 Insertion de la candidature (statut 0 = en attente)
INSERT INTO Candidature (id_utilisateur, id_offre, id_entreprise, statut)
VALUES (
    (SELECT id_utilisateur FROM Utilisateur WHERE mail = 'alice@cytech.fr'),
    (SELECT id_offre FROM Offre WHERE titre = 'Stage Développeur Web Junior'),
    (SELECT id_entreprise FROM Entreprise WHERE nom_entreprise = 'TechCorp'),
    0
);

-- 1.2 Simulation du message envoyé avec le CV
INSERT INTO Message (id_expediteur, id_destinataire, sujet, contenu, fichier_joint)
VALUES (
    (SELECT id_utilisateur FROM Utilisateur WHERE mail = 'alice@cytech.fr'),
    (SELECT id_utilisateur FROM Utilisateur WHERE mail = 'marc@techcorp.com'),
    'Candidature : Stage Développeur Web Junior',
    'Bonjour, voici mon CV pour le stage de développement web.',
    '1714318000_cv_alice.pdf'
);

-- 1.3 Ajout de la trace (Log)
INSERT INTO Trace (id_utilisateur, acte) 
VALUES ((SELECT id_utilisateur FROM Utilisateur WHERE mail = 'alice@cytech.fr'), 'A postulé à l''offre ID 1 (Web Junior)');


-- ==========================================================
-- TEST 2 : L'ENTREPRISE (MARC) ACCEPTE ALICE
-- ==========================================================

-- 2.1 Mise à jour du statut de candidature (1 = acceptée)
UPDATE Candidature 
SET statut = 1 
WHERE id_utilisateur = (SELECT id_utilisateur FROM Utilisateur WHERE mail = 'alice@cytech.fr')
AND id_offre = (SELECT id_offre FROM Offre WHERE titre = 'Stage Développeur Web Junior');

-- 2.2 Création du dossier de STAGE (Etape : Validation Admin)
INSERT INTO Stage (id_etudiant, id_offre, date_debut_stage, date_fin, etat_suivi)
VALUES (
    (SELECT id_utilisateur FROM Utilisateur WHERE mail = 'alice@cytech.fr'),
    (SELECT id_offre FROM Offre WHERE titre = 'Stage Développeur Web Junior'),
    '2026-06-01', '2026-08-31',
    'Validation Admin'
);

-- 2.3 Log de l'acceptation
INSERT INTO Trace (id_utilisateur, acte) 
VALUES ((SELECT id_utilisateur FROM Utilisateur WHERE mail = 'marc@techcorp.com'), 'A accepté la candidature de Alice - Stage créé');


-- ==========================================================
-- TEST 3 : L'ADMIN VALIDE LE DOSSIER
-- ==========================================================

-- 3.1 L'admin fait passer le stage à l'étape suivante
UPDATE Stage 
SET etat_suivi = 'Signature Entreprise'
WHERE id_etudiant = (SELECT id_utilisateur FROM Utilisateur WHERE mail = 'alice@cytech.fr');

-- 3.2 Log de l'admin
INSERT INTO Trace (id_utilisateur, acte) 
VALUES (1, 'Admin a validé le dossier Alice et envoyé pour signature entreprise');
