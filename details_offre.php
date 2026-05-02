<?php
session_start();
require_once 'db.php';

// Sécurité
if (!isset($_SESSION['role'])) {
    header('Location: ../connexion.html');
    exit();
}

$id_offre = intval($_GET['id'] ?? 0);

if ($id_offre <= 0) {
    die("Offre non trouvée.");
}

// Récupération complète de l'offre avec détails entreprise
$stmt = $pdo->prepare("
    SELECT o.*, e.nom_entreprise, e.id_entreprise,
           (SELECT COUNT(*) FROM Candidature WHERE id_offre = o.id_offre AND statut = 1) as nb_acceptes,
           (SELECT COUNT(*) FROM Candidature WHERE id_offre = o.id_offre AND statut = 0) as nb_candidats,
           (SELECT COUNT(*) FROM Candidature WHERE id_offre = o.id_offre AND statut = 2) as nb_refuses,
           (o.date_fin < CURRENT_DATE()) as est_expiree
    FROM Offre o 
    JOIN Entreprise e ON o.id_entreprise = e.id_entreprise
    WHERE o.id_offre = ?
");
$stmt->execute([$id_offre]);
$offre = $stmt->fetch();

if (!$offre) {
    die("Offre non trouvée.");
}

// Vérifier si l'utilisateur peut accéder à cette page
$acces_autorise = false;

if ($_SESSION['role'] === 'admin') {
    $acces_autorise = true;
} elseif ($_SESSION['role'] === 'entreprise') {
    // Vérifier que c'est son entreprise
    $stmtEnt = $pdo->prepare("SELECT id_entreprise FROM Entreprise WHERE id_referent = ?");
    $stmtEnt->execute([$_SESSION['id_utilisateur']]);
    $ent = $stmtEnt->fetch();
    $acces_autorise = ($ent && $ent['id_entreprise'] == $offre['id_entreprise']);
} elseif ($_SESSION['role'] === 'etudiant') {
    $acces_autorise = true;
}

if (!$acces_autorise) {
    die("Accès refusé.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($offre['titre']) ?> - Détails</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">📋 Détails de l'offre</a>
        <div class="ms-auto">
            <?php if ($_SESSION['role'] === 'etudiant'): ?>
                <a href="../etudiant/etudiant_home.php" class="btn btn-secondary btn-sm">← Retour</a>
            <?php elseif ($_SESSION['role'] === 'entreprise'): ?>
                <a href="entreprise_home.php" class="btn btn-secondary btn-sm">← Retour</a>
            <?php elseif ($_SESSION['role'] === 'admin'): ?>
                <a href="../admin/gestion_offres.php" class="btn btn-secondary btn-sm">← Retour</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0"><?= htmlspecialchars($offre['titre']) ?></h2>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-muted">Entreprise</h6>
                            <p class="fs-5"><?= htmlspecialchars($offre['nom_entreprise']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-muted">Filière</h6>
                            <p class="fs-5"><?= htmlspecialchars($offre['filiere']) ?></p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-muted">Date de début</h6>
                            <p class="fs-5"><?= date('d/m/Y', strtotime($offre['date_debut'])) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-muted">Date de fin</h6>
                            <p class="fs-5"><?= date('d/m/Y', strtotime($offre['date_fin'])) ?></p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="fw-bold mb-3">📝 Missions</h5>
                    <div class="bg-light p-3 rounded mb-4">
                        <p><?= nl2br(htmlspecialchars($offre['missions'])) ?></p>
                    </div>

                    <h5 class="fw-bold mb-3">🎯 Compétences requises</h5>
                    <div class="bg-light p-3 rounded">
                        <p><?= htmlspecialchars($offre['competences']) ?></p>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-muted">Statut</h6>
                            <?php if ($offre['est_expiree']): ?>
                                <span class="badge bg-secondary">⏰ Expirée</span>
                            <?php elseif ($offre['nb_acceptes'] > 0): ?>
                                <span class="badge bg-success">✅ Pourvue</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">⏳ En recherche</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-muted">Mise en avant</h6>
                            <?php if ($offre['mis_en_avant']): ?>
                                <span class="badge bg-info">⭐ En avant</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">—</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">📊 Statistiques</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted">Candidatures en attente</h6>
                        <div class="fs-4 fw-bold text-warning"><?= $offre['nb_candidats'] ?></div>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted">Candidatures acceptées</h6>
                        <div class="fs-4 fw-bold text-success"><?= $offre['nb_acceptes'] ?></div>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted">Candidatures refusées</h6>
                        <div class="fs-4 fw-bold text-danger"><?= $offre['nb_refuses'] ?></div>
                    </div>

                    <hr>

                    <h6 class="text-muted">Total candidatures</h6>
                    <div class="fs-5 fw-bold"><?= $offre['nb_candidats'] + $offre['nb_acceptes'] + $offre['nb_refuses'] ?></div>
                </div>
            </div>

            <?php if ($_SESSION['role'] === 'etudiant' && !$offre['est_expiree']): ?>
                <div class="card shadow-sm mt-3">
                    <div class="card-body text-center">
                        <a href="../etudiant/postuler.php?id=<?= $offre['id_offre'] ?>" class="btn btn-success w-100">
                            📨 Postuler à cette offre
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
