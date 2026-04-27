DROP database IF EXISTS siteStage;
CREATE DATABASE siteStage ;
USE siteStage ;



# /!\ Ce fichier n'est qu'à lire une fois sinon les tables vont se réinitialiser

DROP TABLE IF EXISTS Utilisateur;
DROP TABLE IF EXISTS Entreprise;
DROP TABLE IF EXISTS Offre;
DROP TABLE IF EXISTS Candidature;
DROP TABLE IF EXISTS Document;
DROP TABLE IF EXISTS Trace;
DROP TABLE IF EXISTS Filiere;
DROP TABLE IF EXISTS Stage;
DROP TABLE IF EXISTS Message;



CREATE TABLE Utilisateur (
	id_utilisateur integer(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	prenom varchar(50),
	nom_utilisateur varchar(50),
	mail varchar(100) UNIQUE,
	mot_de_passe varchar(255) NOT NULL,
	role_utilisateur ENUM('etudiant','entreprise','admin') NOT NULL,
    annee ENUM('ING1', 'ING2', 'ING3') NULL,
    filiere varchar(50),
    est_valide BOOLEAN DEFAULT FALSE, 
    a2f varchar(6) NULL,
    a2f_expire DATETIME NULL
);

/* Premier admin dont le mot de passe est : 1234 */

INSERT INTO Utilisateur 
VALUES (1,'Premier Admin','admin_1','mailadmin@gmail.com','$2y$10$zyHJhXeOL3jA3RVnf3/Qi.yoU/x22gv0pe06xSUgUNzQLlDn6O8n6','admin',NULL,NULL,1,NULL,NULL);


CREATE TABLE Filiere (
    id_filiere INT AUTO_INCREMENT PRIMARY KEY,
    nom_filiere VARCHAR(50) NOT NULL,
    niveau ENUM('ING1', 'ING2', 'ING3') NOT NULL
);

-- Insertion des données par année
-- ING 1
INSERT INTO Filiere (nom_filiere, niveau) VALUES ('GM - Finance', 'ING1'), ('GM - Data', 'ING1'), ('GI', 'ING1');
-- ING 2
INSERT INTO Filiere (nom_filiere, niveau) VALUES ('GM - Finance', 'ING2'), ('GM - Data', 'ING2'), ('GI', 'ING2');
-- ING 3
INSERT INTO Filiere (nom_filiere, niveau) VALUES ('HPDA', 'ING3'), ('Cybersécurité', 'ING3'), ('IA', 'ING3'), ('Cloud Computing', 'ING3');


CREATE TABLE Entreprise (
	id_entreprise integer(3) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	nom_entreprise varchar(50),
    id_referent integer(5), /* Lien vers l'utilisateur qui gère l'entreprise */
    FOREIGN KEY (id_referent) REFERENCES Utilisateur(id_utilisateur)
	
);

CREATE TABLE Offre (
	id_offre integer(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	titre varchar(100),
	missions TEXT,
	competences varchar(200),
    filiere varchar(30),
    date_debut date,
    date_fin date,
	id_entreprise integer(3),
	
	FOREIGN KEY (id_entreprise) REFERENCES Entreprise(id_entreprise)
);

CREATE TABLE Candidature (
	id_utilisateur integer(5) NOT NULL,
	id_offre integer(5),
	id_entreprise integer(3) NOT NULL,
	statut integer(1), /* /!\ Pour le php, il faudra mettre 0 : en attente , 1 : acceptée , 2 : refusée */ 
	PRIMARY KEY (id_utilisateur, id_offre),
	
	FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur),
	FOREIGN KEY (id_entreprise) REFERENCES Entreprise(id_entreprise),
	FOREIGN KEY (id_offre) REFERENCES Offre(id_offre)

);


CREATE TABLE Document (
	id_doc integer(6) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	id_possesseur integer(5),
	nom_doc varchar(50),
    chemin_fichier varchar(200), 
	
	FOREIGN KEY (id_possesseur) REFERENCES Utilisateur(id_utilisateur)
);

CREATE TABLE Trace (
	id_action integer(7) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur integer(5),
    acte TEXT, 
    date_action datetime DEFAULT CURRENT_TIMESTAMP, 
    
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur)
);

CREATE TABLE Stage (
    id_stage integer(5) AUTO_INCREMENT PRIMARY KEY,
    id_etudiant integer(5) NOT NULL,
    id_offre integer(5) NOT NULL,
    date_debut_stage DATE NOT NULL,
    date_fin DATE NOT NULL,
    etat_suivi ENUM('En attente', 'En cours', 'Terminé', 'Archivé') DEFAULT 'En attente',
    note_entreprise INT DEFAULT NULL,
    commentaire_admin TEXT,
    chemin_convention VARCHAR(255), -- Pour l'archivage des dossiers
    FOREIGN KEY (id_etudiant) REFERENCES Utilisateur(id_utilisateur),
    FOREIGN KEY (id_offre) REFERENCES Offre(id_offre)
);

CREATE TABLE Message (
    id_message INT AUTO_INCREMENT PRIMARY KEY,
    id_expediteur INT NOT NULL,
    id_destinataire INT NOT NULL,
    sujet VARCHAR(150),
    contenu TEXT NOT NULL,
    fichier_joint VARCHAR(255) NULL,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    est_lu BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_expediteur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_destinataire) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE
);

