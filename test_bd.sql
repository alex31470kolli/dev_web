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

-- ==========================================================
-- TEST 4 : Offres fictives pour tester les différentes fonctionnalités associées aux offres
-- ==========================================================
-- Insertion d'étudiants de test
INSERT INTO Utilisateur (prenom, nom_utilisateur, mail, mot_de_passe, role_utilisateur, annee, filiere, est_valide)
VALUES 
('Jean', 'jean_etudiant', 'jean.etudiant@cytech.fr', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'etudiant', 'ING2', 'GI', 1),
('Marie', 'marie_etu', 'marie.etu@cytech.fr', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'etudiant', 'ING3', 'IA', 1),
('Pierre', 'pierre_data', 'pierre.data@cytech.fr', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'etudiant', 'ING2', 'GM - Data', 1);

-- Insertion d'entreprises de test
INSERT INTO Entreprise (nom_entreprise, id_referent)
VALUES 
('TechCorp Solutions', 2),
('InnovateSoft Inc', 3),
('DataSys Analytics', 4),
('CloudNet Infrastructure', 5);
-- Insertion d'offres de stages fictives
INSERT INTO Offre (titre, missions, competences, filiere, date_debut, date_fin, id_entreprise, mis_en_avant, cible_mise_en_avant)
VALUES 
(
    'Développeur Full Stack - Node.js & React',
    'Développer une application web moderne pour gérer les stocks internes. Vous travaillerez en équipe agile avec des développeurs expérimentés. Missions : créer des APIs RESTful, intégrer une base de données, développer une interface utilisateur réactive.',
    'Node.js, React, SQL, Git, REST API',
    'GI',
    '2026-06-01',
    '2026-08-31',
    1,
    1,
    'ING2'
),
(
    'Data Scientist - Machine Learning',
    'Rejoignez notre équipe Data pour développer des modèles de prédiction de tendances marché. Vous analyserez des données massives, créerez des visualisations et présenterez vos résultats à la direction. Opportunité unique d\'apprentissage en IA.',
    'Python, Machine Learning, Pandas, Scikit-learn, Data Visualization',
    'GM - Data',
    '2026-05-15',
    '2026-08-15',
    2,
    1,
    'ING2'
),
(
    'Ingénieur Cybersécurité - Pentesting',
    'Effectuez des tests de pénétration sur nos infrastructures et applications. Documentez les vulnérabilités, proposez des solutions de sécurisation. Travaillez avec une équipe passionnée par la sécurité informatique.',
    'Cybersécurité, Pentesting, Linux, Réseaux, OWASP',
    'Cybersécurité',
    '2026-06-15',
    '2026-09-15',
    3,
    1,
    'ING3'
),
(
    'Développeur Cloud - AWS',
    'Déployez et maintenez nos applications sur AWS. Configurez des infrastructure cloud scalables, optimisez les coûts, assurez la disponibilité 24/7. Formation AWS certifiée disponible.',
    'AWS, Docker, Kubernetes, Terraform, Linux',
    'Cloud Computing',
    '2026-07-01',
    '2026-09-30',
    4,
    1,
    'ING3'
),
(
    'Développeur Backend - Python Django',
    'Créez des APIs robustes pour nos services web. Travaillez avec Django, PostgreSQL et Redis. Participez à la conception de l\'architecture logicielle et à la code review.',
    'Python, Django, PostgreSQL, REST, Docker',
    'GI',
    '2026-06-01',
    '2026-08-31',
    1,
    0,
    'TOUS'
),
(
    'Analyste Financier - Excel & VBA',
    'Analysez les données financières du groupe, créez des tableaux de bord interactifs en VBA. Rapports mensuels, prévisions budgétaires, optimisation des processus.',
    'Excel, VBA, Finance, SQL, Reporting',
    'GM - Finance',
    '2026-05-20',
    '2026-08-20',
    2,
    0,
    'TOUS'
),
(
    'Ingénieur IA - Deep Learning',
    'Développez des modèles de deep learning pour la reconnaissance d\'images et le traitement du langage naturel. Utilisez TensorFlow et PyTorch dans un environnement de recherche.',
    'Python, TensorFlow, PyTorch, Deep Learning, CUDA',
    'IA',
    '2026-06-15',
    '2026-09-15',
    3,
    0,
    'TOUS'
),
(
    'Gestionnaire de Projet IT',
    'Pilotez des projets informatiques de 3-6 mois. Coordonnez les équipes, gérez les budgets, assurez la qualité des livrables. Utilisation de Jira et Agile.',
    'Gestion de projet, Agile, JIRA, Communication',
    'GI',
    '2026-05-01',
    '2026-08-31',
    4,
    0,
    'TOUS'
);
