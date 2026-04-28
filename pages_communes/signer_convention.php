<?php
session_start();
require_once '../db.php';

$id_user = $_SESSION['id_utilisateur'];
$id_stage = intval($_GET['id']);

// On récupère le stage pour vérifier l'état actuel
$stmt = $pdo->prepare("SELECT * FROM Stage WHERE id_stage = ?");
$stmt->execute([$id_stage]);
$stage = $stmt->fetch();

if (!$stage) die("Stage introuvable.");

$nouveau_etat = null;

// Logique de signature selon le rôle
if ($_SESSION['role'] === 'entreprise' && $stage['etat_suivi'] === 'Signature Entreprise') {
    $nouveau_etat = 'Signature Étudiant'; // Passe à l'étudiant après l'entreprise
} elseif ($_SESSION['role'] === 'etudiant' && $stage['etat_suivi'] === 'Signature Étudiant') {
    $nouveau_etat = 'Signature Admin'; // Revient à l'admin après l'étudiant
}

if ($nouveau_etat) {
    $update = $pdo->prepare("UPDATE Stage SET etat_suivi = ? WHERE id_stage = ?");
    $update->execute([$nouveau_etat, $id_stage]);
    
    logAction($id_user, "A signé numériquement la convention (Stage ID: $id_stage)");
    
    // Redirection selon le rôle
    $home = ($_SESSION['role'] === 'etudiant') ? "../etudiant/etudiant_home.php" : "../entreprise/entreprise_home.php";
    header("Location: $home?signed=1");
} else {
    die("Action non autorisée ou déjà effectuée.");
}
