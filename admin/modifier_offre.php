<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../connexion.html');
    exit();
}

$message = '';
$offre = null;

// Chargement des entreprises et des filières disponibles
$entreprises = $pdo->query("SELECT id_entreprise, nom_entreprise FROM Entreprise ORDER BY nom_entreprise")->fetchAll();
$filieres = $pdo->query("SELECT DISTINCT nom_filiere FROM Filiere ORDER BY nom_filiere")->fetchAll(PDO::FETCH_COLUMN);
$cibles = ['TOUS', 'ING1', 'ING2', 'ING3'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_offre'])) {
    $id = intval($_POST['id_offre']);
    $titre = trim($_POST['titre']);
    $missions = trim($_POST['missions']);
    $competences = trim($_POST['competences']);
    $filiere = trim($_POST['filiere']);
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $id_entreprise = intval($_POST['id_entreprise']);
    $mis_en_avant = isset($_POST['mis_en_avant']) ? 1 : 0;
    $cible_mise_en_avant = in_array($_POST['cible_mise_en_avant'], $cibles) ? $_POST['cible_mise_en_avant'] : 'TOUS';

    if ($titre === '' || $missions === '' || $competences === '' || $filiere === '' || $date_debut === '' || $date_fin === '') {
        $message = "<div class='alert alert-danger'>Tous les champs obligatoires doivent être remplis.</div>";
    } else {
        $stmt = $pdo->prepare("UPDATE Offre SET titre = ?, missions = ?, competences = ?, filiere = ?, date_debut = ?, date_fin = ?, id_entreprise = ?, mis_en_avant = ?, cible_mise_en_avant = ? WHERE id_offre = ?");
        $stmt->execute([$titre, $missions, $competences, $filiere, $date_debut, $date_fin, $id_entreprise, $mis_en_avant, $cible_mise_en_avant, $id]);
        $message = "<div class='alert alert-success'>Offre mise à jour avec succès.</div>";
    }
}

$id = intval($_GET['id'] ?? $_POST['id_offre'] ?? 0);
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT o.*, e.nom_entreprise FROM Offre o JOIN Entreprise e ON o.id_entreprise = e.id_entreprise WHERE o.id_offre = ?");
    $stmt->execute([$id]);
    $offre = $stmt->fetch();
}

if (!$offre) {
    header('Location: gestion_offres.php');
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
            <h2>✏️ Modifier l'offre</h2>
            <p class="text-muted mb-0">Offre actuelle : <?= htmlspecialchars($offre['titre']) ?></p>
        </div>
        <a href="gestion_offres.php" class="btn btn-secondary">← Retour à la gestion</a>
    </div>

    <?= $message ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="id_offre" value="<?= $offre['id_offre'] ?>">
                <input type="hidden" name="edit_offre" value="1">

                <div class="mb-3">
                    <label class="form-label">Titre de l'offre</label>
                    <input type="text" name="titre" class="form-control" value="<?= htmlspecialchars($offre['titre']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Missions</label>
                    <textarea name="missions" class="form-control" rows="5" required><?= htmlspecialchars($offre['missions']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Compétences</label>
                    <input type="text" name="competences" class="form-control" value="<?= htmlspecialchars($offre['competences']) ?>" required>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Filière</label>
                        <select name="filiere" class="form-select" required>
                            <?php foreach ($filieres as $f): ?>
                                <option value="<?= htmlspecialchars($f) ?>" <?= $offre['filiere'] === $f ? 'selected' : '' ?>><?= htmlspecialchars($f) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Entreprise</label>
                        <select name="id_entreprise" class="form-select" required>
                            <?php foreach ($entreprises as $ent): ?>
                                <option value="<?= $ent['id_entreprise'] ?>" <?= $offre['id_entreprise'] == $ent['id_entreprise'] ? 'selected' : '' ?>><?= htmlspecialchars($ent['nom_entreprise']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Date de début</label>
                        <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($offre['date_debut']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date de fin</label>
                        <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($offre['date_fin']) ?>" required>
                    </div>
                </div>

                <div class="row g-3 mt-3 align-items-end">
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="mis_en_avant" id="mis_en_avant" value="1" <?= $offre['mis_en_avant'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="mis_en_avant">Mettre en avant</label>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Cible mise en avant</label>
                        <select name="cible_mise_en_avant" class="form-select">
                            <?php foreach ($cibles as $c): ?>
                                <option value="<?= $c ?>" <?= $offre['cible_mise_en_avant'] === $c ? 'selected' : '' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
