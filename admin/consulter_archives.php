<?php
session_start();
require_once '../db.php';

// Sécurité Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ../connexion.html'); exit(); }

// On récupère uniquement les stages avec le statut 'Archivé'
$stmt = $pdo->query("SELECT s.*, u.nom_utilisateur, u.prenom, o.titre 
                     FROM Stage s 
                     JOIN Utilisateur u ON s.id_etudiant = u.id_utilisateur 
                     JOIN Offre o ON s.id_offre = o.id_offre 
                     WHERE s.etat_suivi = 'Archivé'
                     ORDER BY s.date_fin DESC");
$archives = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Archives des Stages - CY Tech</title>
    <link rel="stylesheet" href="/assets/css/css_all.css">
</head>
<body>
    <h1>📦 Archives des dossiers de stage</h1>
    <a href="admin_home.php">← Retour au Dashboard</a>

    <table border="1" style="width:100%; margin-top:20px; border-collapse: collapse;">
        <tr style="background:#eee;">
            <th>Date</th>
            <th>Étudiant</th>
            <th>Entreprise / Offre</th>
            <th>Statut</th>
        </tr>
        <?php foreach($archives as $a): ?>
        <tr>
            <td><?= $a['date_fin'] ?></td>
            <td><?= htmlspecialchars($a['prenom'] . " " . $a['nom_utilisateur']) ?></td>
            <td><?= htmlspecialchars($a['titre']) ?></td>
            <td><span style="color:gray;">🗄️ Archivé</span></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
