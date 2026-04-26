<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../connexion.html');
    exit();
}

// Récupération des filtres depuis l'URL
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
$user_filter = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

// Construction dynamique de la requête SQL 
$query = "SELECT t.*, u.nom_utilisateur, u.prenom 
          FROM Trace t 
          JOIN Utilisateur u ON t.id_utilisateur = u.id_utilisateur 
          WHERE 1=1";
$params = [];

if ($user_filter) {
    $query .= " AND t.id_utilisateur = ?";
    $params[] = $user_filter;
}
if ($date_filter) {
    $query .= " AND DATE(t.date_action) = ?";
    $params[] = $date_filter;
}

$query .= " ORDER BY t.date_action DESC LIMIT " . $limit;
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Liste des utilisateurs pour le filtre déroulant
$utilisateurs = $pdo->query("SELECT id_utilisateur, nom_utilisateur, prenom FROM Utilisateur")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Journal d'activité - CY Tech</title>
    <link rel="stylesheet" href="css_all.css">
    <link rel="stylesheet" href="/assets/css/css_all.css">
</head>
<body>
    <h1>📜 Journal des Traces (Fichier Trace)</h1>
    <a href="admin_home.php">← Retour au dashboard</a>

    <form class="filters" method="GET">
        <div>
            <label>Utilisateur :</label><br>
            <select name="user_id">
                <option value="">Tous</option>
                <?php foreach($utilisateurs as $user): ?>
                    <option value="<?= $user['id_utilisateur'] ?>" <?= ($user_filter == $user['id_utilisateur']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['prenom'] . " " . $user['nom_utilisateur']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Date :</label><br>
            <input type="date" name="date" value="<?= htmlspecialchars($date_filter) ?>">
        </div>
        <div>
            <label>Afficher :</label><br>
            <select name="limit">
                <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20</option>
                <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
            </select>
        </div>
        <button type="submit" style="padding: 5px 15px; cursor: pointer;">Filtrer</button>
    </form>

    <table>
        <tr>
            <th>Date & Heure</th>
            <th>Utilisateur</th>
            <th>Action (Acte)</th>
        </tr>
        <?php foreach($logs as $log): ?>
        <tr>
            <td><?= $log['date_action'] ?></td>
            <td><?= htmlspecialchars($log['prenom'] . " " . $log['nom_utilisateur']) ?></td>
            <td><?= htmlspecialchars($log['acte']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
