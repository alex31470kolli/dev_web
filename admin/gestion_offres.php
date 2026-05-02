<?php
session_start();
require_once '../db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ../connexion.html'); exit(); }

// Suppression
if (isset($_POST['delete_offre'])) {
    $pdo->prepare("DELETE FROM Offre WHERE id_offre = ?")->execute([$_POST['id_offre']]);
    $msg = "Offre supprimée.";
}

// Mise en avant
if (isset($_POST['toggle_promo'])) {
    $id = intval($_POST['id_offre']);
    $promo = intval($_POST['valeur']);
    $cible = $_POST['cible'] ?? 'TOUS';
    $pdo->prepare("UPDATE Offre SET mis_en_avant = ?, cible_mise_en_avant = ? WHERE id_offre = ?")
        ->execute([$promo, $cible, $id]);
}

$offres = $pdo->query("SELECT o.*, e.nom_entreprise FROM Offre o JOIN Entreprise e ON o.id_entreprise = e.id_entreprise ORDER BY o.id_offre DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Offres</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="d-flex justify-content-between mb-4">
        <h2>📢 Gestion des Offres de Stage</h2>
        <a href="admin_home.php" class="btn btn-secondary">← Retour</a>
    </div>

    <div class="row">
        <?php foreach($offres as $o): ?>
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100 <?= $o['mis_en_avant'] ? 'border-warning border-3' : '' ?>">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($o['titre']) ?></h5>
                    <h6 class="card-subtitle mb-3 text-muted">Par : <?= htmlspecialchars($o['nom_entreprise']) ?></h6>
                    
                    <form method="POST" class="bg-light p-3 rounded mb-3">
                        <input type="hidden" name="id_offre" value="<?= $o['id_offre'] ?>">
                        <label class="form-label small fw-bold">⭐ Mise en avant</label>
                        <div class="d-flex gap-2">
                            <select name="cible" class="form-select form-select-sm">
                                <option value="TOUS" <?= $o['cible_mise_en_avant'] == 'TOUS' ? 'selected' : '' ?>>Toutes les classes</option>
                                <option value="ING1" <?= $o['cible_mise_en_avant'] == 'ING1' ? 'selected' : '' ?>>ING1 uniquement</option>
                                <option value="ING2" <?= $o['cible_mise_en_avant'] == 'ING2' ? 'selected' : '' ?>>ING2 uniquement</option>
                                <option value="ING3" <?= $o['cible_mise_en_avant'] == 'ING3' ? 'selected' : '' ?>>ING3 uniquement</option>
                            </select>
                            <?php if(!$o['mis_en_avant']): ?>
                                <button type="submit" name="toggle_promo" value="1" class="btn btn-sm btn-warning">Activer</button>
                                <input type="hidden" name="valeur" value="1">
                            <?php else: ?>
                                <button type="submit" name="toggle_promo" value="1" class="btn btn-sm btn-outline-danger">Désactiver</button>
                                <input type="hidden" name="valeur" value="0">
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-white border-0 text-end d-flex justify-content-end gap-2">
                    <a href="../details_offre.php?id=<?= $o['id_offre'] ?>" class="btn btn-sm btn-outline-secondary">Détails</a>
                    <a href="modifier_offre.php?id=<?= $o['id_offre'] ?>" class="btn btn-sm btn-primary">Modifier l'offre</a>
                    <form method="POST" onsubmit="return confirm('Supprimer définitivement cette offre ?');">
                        <input type="hidden" name="id_offre" value="<?= $o['id_offre'] ?>">
                        <button type="submit" name="delete_offre" class="btn btn-sm btn-danger">Supprimer l'offre</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
