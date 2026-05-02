<?php
session_start();
require_once '../db.php';

// Sécurité
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'entreprise') {
    header('Location: ../connexion.html');
    exit();
}

$id_user = $_SESSION['id_utilisateur'];

// Récupération de l'entreprise de l'utilisateur
$stmtEnt = $pdo->prepare("SELECT id_entreprise, nom_entreprise FROM Entreprise WHERE id_referent = ?");
$stmtEnt->execute([$id_user]);
$entreprise = $stmtEnt->fetch();

if (!$entreprise) {
    die("Erreur : Votre compte n'est lié à aucune entreprise.");
}

$id_ent = $entreprise['id_entreprise'];
$message = '';
$offre = null;

// Chargement des filières
$filieres = $pdo->query("SELECT DISTINCT nom_filiere FROM Filiere ORDER BY nom_filiere")->fetchAll(PDO::FETCH_COLUMN);

// Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_offre'])) {
    $id_offre = intval($_POST['id_offre']);
    $titre = trim($_POST['titre']);
    $missions = trim($_POST['missions']);
    $competences = trim($_POST['competences']);
    $filiere = trim($_POST['filiere']);
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];

    if ($titre === '' || $missions === '' || $competences === '' || $filiere === '' || $date_debut === '' || $date_fin === '') {
        $message = "<div class='alert alert-danger'>Tous les champs obligatoires doivent être remplis.</div>";
    } else {
        $stmt = $pdo->prepare("UPDATE Offre SET titre = ?, missions = ?, competences = ?, filiere = ?, date_debut = ?, date_fin = ? WHERE id_offre = ? AND id_entreprise = ?");
        $result = $stmt->execute([$titre, $missions, $competences, $filiere, $date_debut, $date_fin, $id_offre, $id_ent]);
        
        if ($result) {
            $message = "<div class='alert alert-success'>✅ Offre mise à jour avec succès.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Erreur lors de la mise à jour.</div>";
        }
    }
}

// Chargement de l'offre
$id_offre = intval($_GET['id'] ?? $_POST['id_offre'] ?? 0);
if ($id_offre > 0) {
    $stmt = $pdo->prepare("SELECT * FROM Offre WHERE id_offre = ? AND id_entreprise = ?");
    $stmt->execute([$id_offre, $id_ent]);
    $offre = $stmt->fetch();
}

if (!$offre) {
    header('Location: entreprise_home.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier l'offre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>✏️ Modifier votre offre</h2>
            <p class="text-muted mb-0">Offre actuelle : <?= htmlspecialchars($offre['titre']) ?></p>
        </div>
        <a href="entreprise_home.php" class="btn btn-secondary">← Retour</a>
    </div>

    <?= $message ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="id_offre" value="<?= $offre['id_offre'] ?>">
                <input type="hidden" name="modifier_offre" value="1">

                <div class="mb-3">
                    <label class="form-label fw-bold">Titre de l'offre</label>
                    <input type="text" name="titre" class="form-control" value="<?= htmlspecialchars($offre['titre']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Missions</label>
                    <textarea name="missions" class="form-control" rows="5" required><?= htmlspecialchars($offre['missions']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Compétences</label>
                    <input type="text" name="competences" class="form-control" value="<?= htmlspecialchars($offre['competences']) ?>" required>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Filière</label>
                        <select name="filiere" class="form-select" required>
                            <?php foreach ($filieres as $f): ?>
                                <option value="<?= htmlspecialchars($f) ?>" <?= $offre['filiere'] === $f ? 'selected' : '' ?>><?= htmlspecialchars($f) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Date de début</label>
                        <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($offre['date_debut']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Date de fin</label>
                        <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($offre['date_fin']) ?>" required>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>

    <div class="alert alert-info mt-4">
        <strong>ℹ️ Information :</strong> Vos modifications sont appliquées immédiatement. L'offre reste visible aux étudiants.
    </div>
</div>
</body>
</html>
