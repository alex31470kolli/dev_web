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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📜 Journal des Traces</h2>
            <a href="admin_home.php" class="btn btn-secondary">← Retour</a>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form class="row g-3 align-items-end" method="GET">
                    <div class="col-md-4">
                        <label class="form-label">Utilisateur</label>
                        <select class="form-select" name="user_id">
                            <option value="">Tous</option>
                            <?php foreach($utilisateurs as $user): ?>
                                <option value="<?= $user['id_utilisateur'] ?>" <?= ($user_filter == $user['id_utilisateur']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['prenom'] . " " . $user['nom_utilisateur']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="date" value="<?= htmlspecialchars($date_filter) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Afficher</label>
                        <select class="form-select" name="limit">
                            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10 lignes</option>
                            <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20 lignes</option>
                            <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50 lignes</option>
                            <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100 lignes</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Date & Heure</th>
                            <th>Utilisateur</th>
                            <th>Action (Acte)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($logs as $log): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($log['date_action'])) ?></td>
                            <td><strong><?= htmlspecialchars($log['prenom'] . " " . $log['nom_utilisateur']) ?></strong></td>
                            <td><?= htmlspecialchars($log['acte']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
