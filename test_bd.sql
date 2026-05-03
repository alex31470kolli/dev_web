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
