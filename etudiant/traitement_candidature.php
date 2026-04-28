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

    // 1. Gestion du fichier (Nouveau ou Existant)
    if ($choix === 'nouveau' && isset($_FILES['pj_nouvelle']) && $_FILES['pj_nouvelle']['error'] === 0) {
        $repertoire = "../documents/";
        if (!is_dir($repertoire)) mkdir($repertoire, 0777, true);
        $nom_fichier = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES['pj_nouvelle']['name']));
        move_uploaded_file($_FILES['pj_nouvelle']['tmp_name'], $repertoire . $nom_fichier);
    } elseif ($choix === 'existant' && !empty($_POST['doc_existant'])) {
        $nom_fichier = $_POST['doc_existant'];
    }

    try {
        $pdo->beginTransaction();

        // 2. Envoi du message interne
        $stmtMsg = $pdo->prepare("INSERT INTO Message (id_expediteur, id_destinataire, sujet, contenu, fichier_joint) VALUES (?, ?, ?, ?, ?)");
        $stmtMsg->execute([$expediteur, $destinataire, $sujet, $contenu, $nom_fichier]);

        // 3. Création de la candidature
        $stmtCand = $pdo->prepare("INSERT INTO Candidature (id_utilisateur, id_offre, id_entreprise, statut) VALUES (?, ?, ?, 0)");
        $stmtCand->execute([$expediteur, $id_offre, $id_entreprise]);

        // 4. Log de l'action
        $pdo->prepare("INSERT INTO Trace (id_utilisateur, acte) VALUES (?, ?)")
            ->execute([$expediteur, "A postulé à l'offre ID : $id_offre"]);

        $pdo->commit();
        header("Location: etudiant_home.php?success=candidature");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->getCode() == 23000) die("Erreur : Vous avez déjà postulé à cette offre.");
        die("Erreur SQL : " . $e->getMessage());
    }
}
