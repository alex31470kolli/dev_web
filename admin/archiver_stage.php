<?php
session_start();
require_once '../db.php'; // On remonte d'un dossier pour trouver db.php

// --- SÉCURITÉ ---
// On vérifie que seul un admin peut faire cette action
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../connexion.html');
    exit();
}

// --- LOGIQUE D'ARCHIVAGE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_stage'])) {
    $id_stage = intval($_POST['id_stage']);

    try {
        // 1. Mise à jour du statut du stage
        $sql = "UPDATE Stage SET etat_suivi = 'Archivé' WHERE id_stage = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$id_stage])) {
            // 2. Journalisation de l'action (Point 4 des consignes)
            // On enregistre qui a archivé quoi
            $message_log = "Archivage du stage ID : " . $id_stage;
            logAction($_SESSION['id_utilisateur'], $message_log);

            // 3. Redirection avec un petit message de succès (optionnel)
            header('Location: admin_home.php?success=archived');
            exit();
        }
    } catch (PDOException $e) {
        die("Erreur lors de l'archivage : " . $e->getMessage());
    }
} else {
    // Si on essaie d'accéder au fichier sans envoyer de formulaire
    header('Location: admin_home.php');
    exit();
}
