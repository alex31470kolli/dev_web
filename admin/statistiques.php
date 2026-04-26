<?php
session_start();
require_once '../db.php';

// Sécurité Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../connexion.html');
    exit();
}

// --- 1. Nombre d'offres par Filière ---
$sql_filiere = "SELECT filiere, COUNT(*) as nb FROM Offre GROUP BY filiere";
$stats_filiere = $pdo->query($sql_filiere)->fetchAll();

// --- 2. Taux de placement (Étudiants en stage / Total étudiants) ---
$total_etudiants = $pdo->query("SELECT COUNT(*) FROM Utilisateur WHERE role_utilisateur = 'etudiant'")->fetchColumn();
$etudiants_en_stage = $pdo->query("SELECT COUNT(DISTINCT id_etudiant) FROM Stage WHERE etat_suivi IN ('En cours', 'Terminé')")->fetchColumn();

$taux_placement = ($total_etudiants > 0) ? round(($etudiants_en_stage / $total_etudiants) * 100, 1) : 0;

// --- 3. Top 5 des Entreprises les plus actives ---
$sql_top_ent = "SELECT e.nom_entreprise, COUNT(o.id_offre) as nb_offres 
                FROM Entreprise e 
                JOIN Offre o ON e.id_entreprise = o.id_entreprise 
                GROUP BY e.id_entreprise 
                ORDER BY nb_offres DESC LIMIT 5";
$top_entreprises = $pdo->query($sql_top_ent)->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques - CY Tech Stages</title>
    <link rel="stylesheet" href="/assets/css/css_all.css">
</head>
<body>
    <div class="container">
        <h1>📊 Tableau de Bord des Statistiques</h1>
        <a href="admin_home.php">← Retour au Dashboard</a>

        <div class="card-grid">
            <div class="card" style="border-left-color: #48bb78;">
                <h3>🎓 Taux de Placement</h3>
                <p style="font-size: 2rem; font-weight: bold; color: #2f855a;"><?= $taux_placement ?>%</p>
                <p><?= $etudiants_en_stage ?> étudiants sur <?= $total_etudiants ?> ont trouvé un stage.</p>
            </div>
            
            <div class="card">
                <h3>🏢 Entreprises</h3>
                <p style="font-size: 2rem; font-weight: bold;"><?= count($top_entreprises) ?></p>
                <p>Entreprises partenaires actives.</p>
            </div>
        </div>

        <div style="display: flex; gap: 30px; margin-top: 40px;">
            <div style="flex: 1;">
                <h2>📍 Offres par Filière</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Filière</th>
                            <th>Nombre d'offres</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($stats_filiere as $f): ?>
                        <tr>
                            <td><?= htmlspecialchars($f['filiere'] ?: 'Non spécifiée') ?></td>
                            <td><strong><?= $f['nb'] ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="flex: 1;">
                <h2>🏆 Top Recruteurs</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Entreprise</th>
                            <th>Offres déposées</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($top_entreprises as $ent): ?>
                        <tr>
                            <td><?= htmlspecialchars($ent['nom_entreprise']) ?></td>
                            <td><?= $ent['nb_offres'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
