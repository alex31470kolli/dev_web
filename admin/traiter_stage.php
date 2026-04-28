<?php
session_start();
require_once '../db.php';

if ($_SESSION['role'] !== 'admin') exit();

// On récupère les données soit par POST (upload) soit par GET (clic simple)
$id_stage = isset($_POST['id_stage']) ? intval($_POST['id_stage']) : intval($_GET['id']);
$action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];

if ($action === 'envoyer') {
    // --- GESTION DE L'UPLOAD DE LA CONVENTION ---
    if (isset($_FILES['convention_pdf']) && $_FILES['convention_pdf']['error'] === 0) {
        $repertoire = "../documents/";
        if (!is_dir($repertoire)) mkdir($repertoire, 0777, true);

        $nom_fichier = "convention_stage_" . $id_stage . "_" . time() . ".pdf";
        
        if (move_uploaded_file($_FILES['convention_pdf']['tmp_name'], $repertoire . $nom_fichier)) {
            // Mise à jour de l'état ET du chemin du fichier
            $stmt = $pdo->prepare("UPDATE Stage SET etat_suivi = 'Signature Entreprise', chemin_convention = ? WHERE id_stage = ?");
            $stmt->execute([$nom_fichier, $id_stage]);
            
            logAction($_SESSION['id_utilisateur'], "A généré et envoyé la convention pour le stage #$id_stage");
        }
    }
} elseif ($action === 'finaliser') {
    // Étape 6 : L'admin signe en dernier
    $stmt = $pdo->prepare("UPDATE Stage SET etat_suivi = 'En cours' WHERE id_stage = ?");
    $stmt->execute([$id_stage]);
    logAction($_SESSION['id_utilisateur'], "Stage #$id_stage officiellement lancé.");
}

header("Location: admin_home.php?success=1");
exit();
