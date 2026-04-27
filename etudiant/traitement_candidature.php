<?php
session_start();
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expediteur = $_SESSION['id_utilisateur'];
    $destinataire = $_POST['id_destinataire'];
    $sujet = $_POST['sujet'];
    $contenu = $_POST['contenu'];
    $id_offre = $_POST['id_offre'];           
    $id_entreprise = $_POST['id_entreprise']; 
    $nom_fichier = null;

    $choix = $_POST['choix_fichier'];

    // --- GESTION DU FICHIER ---
    if ($choix === 'nouveau') {
        if (isset($_FILES['pj_nouvelle']) && $_FILES['pj_nouvelle']['error'] === 0) {
            $repertoire = "../documents/";
            if (!is_dir($repertoire)) mkdir($repertoire, 0777, true);
            
            $nom_fichier = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES['pj_nouvelle']['name']));
            move_uploaded_file($_FILES['pj_nouvelle']['tmp_name'], $repertoire . $nom_fichier);
        }
    } elseif ($choix === 'existant') {
        if (!empty($_POST['doc_existant'])) {
            $nom_fichier = $_POST['doc_existant']; 
        }
    }

    // --- 1. ENVOI DU MESSAGE ---
    try {
        $stmtMsg = $pdo->prepare("INSERT INTO Message (id_expediteur, id_destinataire, sujet, contenu, fichier_joint) VALUES (?, ?, ?, ?, ?)");
        $stmtMsg->execute([$expediteur, $destinataire, $sujet, $contenu, $nom_fichier]);
    } catch (PDOException $e) {
        die("Erreur critique lors de l'envoi du message : " . $e->getMessage());
    }

    // --- 2. CRÉATION DE LA CANDIDATURE ---
    try {
        $stmtCand = $pdo->prepare("INSERT INTO Candidature (id_utilisateur, id_offre, id_entreprise, statut) VALUES (?, ?, ?, 0)");
        $stmtCand->execute([$expediteur, $id_offre, $id_entreprise]);

        // Redirection en cas de succès
        header("Location: etudiant_home.php?success=candidature");
        exit();

    } catch (PDOException $e) {
        // Le code d'erreur SQL 23000 correspond spécifiquement à une violation de contrainte (doublon sur clé primaire)
        if ($e->getCode() == 23000) {
            die("Erreur : Vous avez déjà postulé à cette offre. Votre dossier est en attente.");
        } else {
            die("Erreur inattendue sur la candidature : " . $e->getMessage());
        }
    }
}
?>
