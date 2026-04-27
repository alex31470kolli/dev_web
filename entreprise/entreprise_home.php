<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'entreprise') { 
    header('Location: connexion.html'); exit(); 
}

$id_user = $_SESSION['id_utilisateur'];

// 1. Récupération de l'entreprise
$stmtEnt = $pdo->prepare("SELECT id_entreprise, nom_entreprise FROM Entreprise WHERE id_referent = ?");
$stmtEnt->execute([$id_user]);
$entreprise = $stmtEnt->fetch();
$id_ent = $entreprise['id_entreprise'];

// 2. RÉCUPÉRATION DES FILTRES (via l'URL)
$statut_filtre = $_GET['statut'] ?? 'tous';
$tri_date = $_GET['tri'] ?? 'recent';

// 3. CONSTRUCTION DE LA REQUÊTE DYNAMIQUE
$sql = "SELECT o.*, 
        (SELECT COUNT(*) FROM Candidature WHERE id_offre = o.id_offre AND statut = 1) as est_pourvue,
        (o.date_fin < CURRENT_DATE()) as est_expiree
        FROM Offre o 
        WHERE o.id_entreprise = ?";

// Ajout du filtre d'activité
if ($statut_filtre === 'pourvue') {
    $sql .= " AND (SELECT COUNT(*) FROM Candidature WHERE id_offre = o.id_offre AND statut = 1) > 0";
} elseif ($statut_filtre === 'recherche') {
    $sql .= " AND (SELECT COUNT(*) FROM Candidature WHERE id_offre = o.id_offre AND statut = 1) = 0";
}

// Ajout du tri par date (id_offre suit généralement l'ordre chronologique de création)
$sql .= ($tri_date === 'ancien') ? " ORDER BY o.id_offre ASC" : " ORDER BY o.id_offre DESC";

$stmtOffres = $pdo->prepare($sql);
$stmtOffres->execute([$id_ent]);
$offres_publiees = $stmtOffres->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Tableau de bord - <?= htmlspecialchars($entreprise['nom_entreprise']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="#">🏢 <?= htmlspecialchars($entreprise['nom_entreprise']) ?></a>
    <div class="ms-auto">
        <a href="gerer_candidatures.php" class="btn btn-primary btn-sm me-2">👥 Candidatures</a>
        <a href="publier-offre.php" class="btn btn-success btn-sm me-2">Publier une offre</a>
        <a href="../deconnexion.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
    </div>
  </div>
</nav>

<main class="container">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Activité (Statut)</label>
                    <select name="statut" class="form-select">
                        <option value="tous" <?= $statut_filtre === 'tous' ? 'selected' : '' ?>>Toutes les offres</option>
                        <option value="recherche" <?= $statut_filtre === 'recherche' ? 'selected' : '' ?>>En recherche active</option>
                        <option value="pourvue" <?= $statut_filtre === 'pourvue' ? 'selected' : '' ?>>Offres pourvues</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Trier par date</label>
                    <select name="tri" class="form-select">
                        <option value="recent" <?= $tri_date === 'recent' ? 'selected' : '' ?>>Plus récentes en premier</option>
                        <option value="ancien" <?= $tri_date === 'ancien' ? 'selected' : '' ?>>Plus anciennes en premier</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-dark w-100">Appliquer les filtres</button>
                </div>
            </form>
        </div>
    </div>

    <h3 class="mb-4">Vos offres (<?= count($offres_publiees) ?>)</h3>

    <div class="row">
        <?php foreach ($offres_publiees as $offre): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm <?= $offre['est_pourvue'] > 0 ? 'border-success' : ($offre['est_expiree'] ? 'border-secondary opacity-75' : 'border-primary') ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title <?= $offre['est_expiree'] ? 'text-secondary' : 'text-primary' ?> mb-0"><?= htmlspecialchars($offre['titre']) ?></h5>
                            <?php if ($offre['est_pourvue'] > 0): ?>
                                <span class="badge rounded-pill bg-success">✅ Pourvue</span>
                            <?php elseif ($offre['est_expiree']): ?>
                                <span class="badge rounded-pill bg-secondary">⏰ Expirée</span>
                            <?php else: ?>
                                <span class="badge rounded-pill bg-warning text-dark">⏳ En recherche</span>
                            <?php endif; ?>
                        </div>
                        <p class="card-text small text-muted mb-1"><?= htmlspecialchars($offre['filiere']) ?></p>
                        <p class="card-text small">Fin : <?= date('d/m/Y', strtotime($offre['date_fin'])) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>
</body>
</html>
