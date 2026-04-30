<?php
session_start();
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_offre = intval($_POST['id_offre']);
    $id_entreprise = intval($_POST['id_entreprise']);
    $id_etudiant = $_SESSION['id_utilisateur'];
    $chemin_final = null;

    // Cas 1 : L'étudiant utilise un document déjà présent dans son profil
    if ($_POST['choix_fichier'] === 'existant') {
        $chemin_final = $_POST['doc_existant'];
    } 
    // Cas 2 : L'étudiant télécharge un nouveau fichier depuis son ordinateur[cite: 11]
    else if ($_POST['choix_fichier'] === 'nouveau' && isset($_FILES['pj_nouvelle'])) {
        $repertoire = "../documents/";
        // Création d'un nom unique pour éviter les écrasements
        $nom_fichier = time() . "_" . basename($_FILES['pj_nouvelle']['name']);
        $destination = $repertoire . $nom_fichier;

        if (move_uploaded_file($_FILES['pj_nouvelle']['tmp_name'], $destination)) {
            $chemin_final = $nom_fichier;
        }
    }

    // Insertion dans la base de données (incluant le chemin du fichier)
    $sql = "INSERT INTO Candidature (id_utilisateur, id_offre, id_entreprise, statut, chemin_fichier) 
            VALUES (?, ?, ?, 0, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$id_etudiant, $id_offre, $id_entreprise, $chemin_final])) {
        header('Location: etudiant_home.php?success=1');
    } else {
        echo "Erreur lors de la postulation.";
    }
}
