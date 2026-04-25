DROP database IF EXISTS siteStage;
CREATE DATABASE siteStage ;
USE siteStage ;

DROP TABLE IF EXISTS Utilisateur;
DROP TABLE IF EXISTS Entreprise;
DROP TABLE IF EXISTS Offre;
DROP TABLE IF EXISTS Canditature;
DROP TABLE IF EXISTS Document;
DROP TABLE IF EXISTS Trace;

CREATE TABLE Utilisateur (
	id_utilisateur integer(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	prenom varchar(50),
	nom_utilisateur varchar(50),
	mail varchar(100) UNIQUE,
	mot_de_passe varchar(255),
	role ENUM('etudiant','entreprise','admin') NOT NULL, 
    filiere varchar(30)
);

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
    acte varchar(30), 
    date_action datetime DEFAULT CURRENT_TIMESTAMP, 
    
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur)
);

