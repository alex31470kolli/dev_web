<?php
session_start();
require_once '../db.php';

// --- SÉCURITÉ ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'entreprise') { 
    header('Location: ../connexion.html'); exit(); 
}

$id_user = $_SESSION['id_utilisateur'];

// 1. Récupération de l'entreprise et de son ID
$stmtEnt = $pdo->prepare("SELECT id_entreprise, nom_entreprise FROM Entreprise WHERE id_referent = ?");
$stmtEnt->execute([$id_user]);
$entreprise = $stmtEnt->fetch();

if (!$entreprise) {
    die("Erreur : Votre compte n'est lié à aucune entreprise. Contactez l'administrateur.");
}
$id_ent = $entreprise['id_entreprise'];

// 2. Recherche de conventions à signer
$stmtSignEnt = $pdo->prepare("
    SELECT s.*, u.prenom, u.nom_utilisateur, o.titre, s.chemin_convention
    FROM Stage s 
    JOIN Utilisateur u ON s.id_etudiant = u.id_utilisateur
    JOIN Offre o ON s.id_offre = o.id_offre
    WHERE o.id_entreprise = ? AND s.etat_suivi = 'Signature Entreprise'");
$stmtSignEnt->execute([$id_ent]);
$conventions_a_signer = $stmtSignEnt->fetchAll();

// 3. SUPPRESSION D'OFFRE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_offre'])) {
    $id_offre = intval($_POST['id_offre']);
    $stmt = $pdo->prepare("DELETE FROM Offre WHERE id_offre = ? AND id_entreprise = ?");
    $stmt->execute([$id_offre, $id_ent]);
    header("Location: entreprise_home.php?success=deleted");
    exit();
}

// 4. RÉCUPÉRATION ET FILTRAGE DES OFFRES
$statut_filtre = $_GET['statut'] ?? 'tous';
$tri_date = $_GET['tri'] ?? 'recent';

$sql = "SELECT o.*, 
        (SELECT COUNT(*) FROM Candidature WHERE id_offre = o.id_offre AND statut = 1) as est_pourvue,
        (o.date_fin < CURRENT_DATE()) as est_expiree
        FROM Offre o 
        WHERE o.id_entreprise = ?";

if ($statut_filtre === 'pourvue') {
    $sql .= " AND (SELECT COUNT(*) FROM Candidature WHERE id_offre = o.id_offre AND statut = 1) > 0";
} elseif ($statut_filtre === 'recherche') {
    $sql .= " AND (SELECT COUNT(*) FROM Candidature WHERE id_offre = o.id_offre AND statut = 1) = 0";
}

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

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-0 shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="#">🏢 <?= htmlspecialchars($entreprise['nom_entreprise']) ?></a>
    <div class="ms-auto">
        <a href="../pages_communes/messagerie.php" class="btn btn-info btn-sm me-2 text-white">✉️ Messagerie</a>
        <a href="gerer_candidatures.php" class="btn btn-primary btn-sm me-2">👥 Candidatures</a>
        <a href="publier-offre.php" class="btn btn-success btn-sm me-2">Publier une offre</a>
        <a href="../pages_communes/profil.php" class="btn btn-secondary btn-sm me-2">👤 Mon Profil</a>
        <a href="../deconnexion.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
    </div>
  </div>
</nav>

<?php foreach($conventions_a_signer as $conv): ?>
    <div class="alert alert-info border-0 rounded-0 m-0 border-bottom shadow-sm py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <strong>🖋️ Signature en attente :</strong> Convention pour 
                <span class="fw-bold"><?= htmlspecialchars($conv['prenom'] . " " . $conv['nom_utilisateur']) ?></span> 
                (<?= htmlspecialchars($conv['titre']) ?>)
            </div>
            <div class="d-flex gap-2">
                <a href="../documents/<?= $conv['chemin_convention'] ?? '#' ?>" target="_blank" class="btn btn-sm btn-outline-primary">👁️ Consulter</a>
                <a href="../pages_communes/signer_convention.php?id=<?= $conv['id_stage'] ?>" class="btn btn-sm btn-primary">Signer la convention</a>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<main class="container mt-5">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Statut de l'offre</label>
                    <select name="statut" class="form-select">
                        <option value="tous" <?= $statut_filtre === 'tous' ? 'selected' : '' ?>>Toutes les offres</option>
                        <option value="recherche" <?= $statut_filtre === 'recherche' ? 'selected' : '' ?>>En recherche active</option>
                        <option value="pourvue" <?= $statut_filtre === 'pourvue' ? 'selected' : '' ?>>Offres pourvues</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Trier par</label>
                    <select name="tri" class="form-select">
                        <option value="recent" <?= $tri_date === 'recent' ? 'selected' : '' ?>>Plus récentes</option>
                        <option value="ancien" <?= $tri_date === 'ancien' ? 'selected' : '' ?>>Plus anciennes</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-dark w-100">Appliquer les filtres</button>
                </div>
            </form>
        </div>
    </div>

    <h3 class="mb-4">Vos offres publiées (<?= count($offres_publiees) ?>)</h3>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ Offre supprimée avec succès.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php foreach ($offres_publiees as $offre): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm <?= $offre['est_pourvue'] > 0 ? 'border-success' : ($offre['est_expiree'] ? 'border-secondary opacity-75' : 'border-primary') ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title <?= $offre['est_expiree'] ? 'text-secondary' : 'text-primary' ?> mb-0">
                                <?= htmlspecialchars($offre['titre']) ?>
                            </h5>
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
                    <div class="card-footer bg-white border-top pt-2 d-flex gap-2">
                        <a href="../pages_communes/details_offre.php?id=<?= $offre['id_offre'] ?>" class="btn btn-sm btn-info flex-grow-1">👁️ Détails</a>
                        <a href="modifier_offre_entreprise.php?id=<?= $offre['id_offre'] ?>" class="btn btn-sm btn-primary flex-grow-1">✏️ Modifier</a>
                        <form method="POST" class="d-flex gap-2" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette offre ?');" style="flex-grow: 1;">
                            <input type="hidden" name="id_offre" value="<?= $offre['id_offre'] ?>">
                            <button type="submit" name="delete_offre" class="btn btn-sm btn-danger flex-grow-1">🗑️ Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if(empty($offres_publiees)): ?>
            <div class="col-12"><div class="alert alert-info">Aucune offre ne correspond à vos critères.</div></div>
        <?php endif; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
