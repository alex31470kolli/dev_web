<?php
session_start();
require_once '../db.php'; // Assurez-vous que le chemin est correct

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $titre = $_POST['titre'];
    $filiere = $_POST['filiere'];
    $debut = $_POST['date_debut'];
    $fin = $_POST['date_fin'];
    $missions = $_POST['missions'];
    $competences = $_POST['competences'];
    
    $id_user = $_SESSION['id_utilisateur'];

    // Trouver l'ID entreprise dont l'utilisateur est le référent
    $stmt = $pdo->prepare("SELECT id_entreprise FROM Entreprise WHERE id_referent = ?");
    $stmt->execute([$id_user]);
    $ent = $stmt->fetch();

    if ($ent) {
        $id_entreprise = $ent['id_entreprise'];
        // Insertion de l'offre avec cet $id_entreprise
        $sql = "INSERT INTO Offre (titre, missions, competences, filiere, date_debut, date_fin, id_entreprise, lieu, annee_cible) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        // ... execute ...
    } else {
        die("Erreur : Vous n'êtes pas déclaré comme référent d'une entreprise.");
    }

    try {
        // 1. On doit d'abord trouver l'ID de l'entreprise lié à cet utilisateur
        $stmtEnt = $pdo->prepare("SELECT id_entreprise FROM Entreprise WHERE id_referent = ?");
        $stmtEnt->execute([$id_user]);
        $entreprise = $stmtEnt->fetch();

        if (!$entreprise) {
            die("Erreur : Aucune entreprise associée à votre compte.");
        }

        $id_entreprise = $entreprise['id_entreprise'];

        // 2. Insertion avec les noms de colonnes EXACTS du SQL
        // Colonnes : titre, missions, competences, filiere, date_debut, date_fin, id_entreprise
        $sql = "INSERT INTO Offre (titre, missions, competences, filiere, date_debut, date_fin, id_entreprise) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $titre, 
            $missions, 
            $competences, 
            $filiere, 
            $debut, 
            $fin, 
            $id_entreprise
        ]);

        // Enregistrement dans les logs
        logAction($id_user, "A publié une offre : $titre");

        header("Location: entreprise_home.php?success=1");
        exit();

    } catch (PDOException $e) {
        die("Erreur SQL : " . $e->getMessage());
    }
}
