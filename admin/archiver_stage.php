<?php
session_start();
require_once '../db.php'; // On remonte d'un dossier

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit("Accès refusé");
}

if (isset($_POST['id_stage'])) {
    $id_stage = intval($_POST['id_stage']);

    // 1. Mise à jour du statut en base de données
    $sql = "UPDATE Stage SET etat_suivi = 'Archivé' WHERE id_stage = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$id_stage])) {
        // 2. Journalisation de l'action (Point 4 des consignes)
        logAction($_SESSION['id_utilisateur'], "Archivage du stage ID : $id_stage");
        
        echo json_encode(['success' => true, 'message' => 'Le stage a été archivé avec succès.']);
    }
}
?>
