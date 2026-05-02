DROP DATABASE IF EXISTS siteStage;
CREATE DATABASE siteStage;
USE siteStage;

-- ============================================================
-- 1. TABLE UTILISATEUR
-- ============================================================
CREATE TABLE Utilisateur (
    id_utilisateur INT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    prenom VARCHAR(50),
    nom_utilisateur VARCHAR(50),
    mail VARCHAR(100) UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role_utilisateur ENUM('etudiant', 'entreprise', 'admin') NOT NULL,
    annee ENUM('ING1', 'ING2', 'ING3') NULL,
    filiere VARCHAR(50),
    est_valide BOOLEAN DEFAULT FALSE, 
    a2f VARCHAR(6) NULL,
    a2f_expire DATETIME NULL
);

-- Insertion du premier admin (mot de passe : 1234)
INSERT INTO Utilisateur (id_utilisateur, prenom, nom_utilisateur, mail, mot_de_passe, role_utilisateur, est_valide)
VALUES (1, 'Premier Admin', 'admin_1', 'mailadmin@gmail.com', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'admin', 1);

-- Insertion d'utilisateurs entreprises de test
INSERT INTO Utilisateur (prenom, nom_utilisateur, mail, mot_de_passe, role_utilisateur, est_valide)
VALUES 
('Contact', 'tech_corp', 'contact@techcorp.fr', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'entreprise', 1),
('RH', 'innovate_soft', 'rh@innovatesoft.com', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'entreprise', 1),
('Recrutement', 'data_sys', 'jobs@datasys.fr', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'entreprise', 1),
('Manager', 'cloud_net', 'manager@cloudnet.io', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'entreprise', 1);

-- Insertion d'étudiants de test
INSERT INTO Utilisateur (prenom, nom_utilisateur, mail, mot_de_passe, role_utilisateur, annee, filiere, est_valide)
VALUES 
('Jean', 'jean_etudiant', 'jean.etudiant@cytech.fr', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'etudiant', 'ING2', 'GI', 1),
('Marie', 'marie_etu', 'marie.etu@cytech.fr', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'etudiant', 'ING3', 'IA', 1),
('Pierre', 'pierre_data', 'pierre.data@cytech.fr', '$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6', 'etudiant', 'ING2', 'GM - Data', 1);

-- ============================================================
-- 2. TABLE FILIERE
-- ============================================================
CREATE TABLE Filiere (
    id_filiere INT AUTO_INCREMENT PRIMARY KEY,
    nom_filiere VARCHAR(50) NOT NULL,
    niveau ENUM('ING1', 'ING2', 'ING3') NOT NULL
);

INSERT INTO Filiere (nom_filiere, niveau) VALUES 
('GM - Finance', 'ING1'), ('GM - Data', 'ING1'), ('GI', 'ING1'),
('GM - Finance', 'ING2'), ('GM - Data', 'ING2'), ('GI', 'ING2'),
('HPDA', 'ING3'), ('Cybersécurité', 'ING3'), ('IA', 'ING3'), ('Cloud Computing', 'ING3');

-- ============================================================
-- 3. TABLE ENTREPRISE
-- ============================================================
CREATE TABLE Entreprise (
    id_entreprise INT(3) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nom_entreprise VARCHAR(50),
    id_referent INT(5),
    -- ON DELETE SET NULL : permet de supprimer un utilisateur sans supprimer l'entreprise
    FOREIGN KEY (id_referent) REFERENCES Utilisateur(id_utilisateur) ON DELETE SET NULL
);

-- Insertion d'entreprises de test
INSERT INTO Entreprise (nom_entreprise, id_referent)
VALUES 
('TechCorp Solutions', 2),
('InnovateSoft Inc', 3),
('DataSys Analytics', 4),
('CloudNet Infrastructure', 5);

-- ============================================================
-- 4. TABLE OFFRE
-- ============================================================
CREATE TABLE Offre (
    id_offre INT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(100),
    missions TEXT,
    competences VARCHAR(200),
    filiere VARCHAR(30),
    date_debut DATE,
    date_fin DATE,
    id_entreprise INT(3),
    -- Gestion de la mise en avant
    mis_en_avant BOOLEAN DEFAULT FALSE,
    cible_mise_en_avant ENUM('ING1', 'ING2', 'ING3', 'TOUS') DEFAULT 'TOUS',
    FOREIGN KEY (id_entreprise) REFERENCES Entreprise(id_entreprise) ON DELETE CASCADE
);

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

-- ============================================================
-- 5. TABLE CANDIDATURE
-- ============================================================
CREATE TABLE Candidature (
    id_utilisateur INT(5) NOT NULL,
    id_offre INT(5) NOT NULL,
    id_entreprise INT(3) NOT NULL,
    chemin_fichier VARCHAR(255) NULL,
    statut INT(1) DEFAULT 0, -- 0:attente, 1:acceptée, 2:refusée
    PRIMARY KEY (id_utilisateur, id_offre),
    -- ON DELETE CASCADE : supprime les candidatures si l'étudiant est supprimé
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_entreprise) REFERENCES Entreprise(id_entreprise) ON DELETE CASCADE,
    FOREIGN KEY (id_offre) REFERENCES Offre(id_offre) ON DELETE CASCADE
);

-- ============================================================
-- 6. TABLE DOCUMENT (Porte-documents étudiant)
-- ============================================================
CREATE TABLE Document (
    id_doc INT(6) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_possesseur INT(5),
    nom_doc VARCHAR(50),
    chemin_fichier VARCHAR(200), 
    FOREIGN KEY (id_possesseur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE
);

-- ============================================================
-- 7. TABLE TRACE (Logs d'activité)
-- ============================================================
CREATE TABLE Trace (
    id_action INT(7) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT(5),
    acte TEXT, 
    date_action DATETIME DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur) ON DELETE SET NULL
);

-- ============================================================
-- 8. TABLE STAGE (Workflow de suivi et conventions)
-- ============================================================
CREATE TABLE Stage (
    id_stage INT(5) AUTO_INCREMENT PRIMARY KEY,
    id_etudiant INT(5) NOT NULL,
    id_offre INT(5) NOT NULL,
    date_debut_stage DATE NOT NULL,
    date_fin DATE NOT NULL,
    -- Workflow complet de signature
    etat_suivi ENUM('Validation Admin','Signature Entreprise','Signature Étudiant','Signature Admin', 'En attente', 'En cours', 'Terminé', 'Archivé') DEFAULT 'Validation Admin',
    note_entreprise INT DEFAULT NULL,
    commentaire_admin TEXT,
    chemin_convention VARCHAR(255),
    FOREIGN KEY (id_etudiant) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_offre) REFERENCES Offre(id_offre) ON DELETE CASCADE
);

-- ============================================================
-- 9. TABLE MESSAGE
-- ============================================================
CREATE TABLE Message (
    id_message INT AUTO_INCREMENT PRIMARY KEY,
    id_expediteur INT NOT NULL,
    id_destinataire INT NOT NULL,
    sujet VARCHAR(150),
    contenu TEXT NOT NULL,
    fichier_joint VARCHAR(255) NULL,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    est_lu BOOLEAN DEFAULT FALSE,
    -- ON DELETE CASCADE : nettoyage automatique de la messagerie
    FOREIGN KEY (id_expediteur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_destinataire) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE
);
