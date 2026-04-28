<?php
session_start();
require_once '../db.php';

if ($_SESSION['role'] !== 'admin') exit();

$id_stage = intval($_GET['id']);
$action = $_GET['action'];

if ($action === 'envoyer') {
    // Étape 4 : L'admin valide et demande la signature de l'entreprise
    $nouveau_etat = 'Signature Entreprise';
    $message = "A validé le dossier et envoyé la convention à l'entreprise.";
} elseif ($action === 'finaliser') {
    // Étape 6 : L'admin signe en dernier et lance le stage
    $nouveau_etat = 'En cours';
    $message = "A signé la convention. Le stage est maintenant EN COURS.";
}

$stmt = $pdo->prepare("UPDATE Stage SET etat_suivi = ? WHERE id_stage = ?");
if ($stmt->execute([$nouveau_etat, $id_stage])) {
    logAction($_SESSION['id_utilisateur'], "$message (Stage ID: $id_stage)");
    header("Location: admin_home.php?success=1");
}
