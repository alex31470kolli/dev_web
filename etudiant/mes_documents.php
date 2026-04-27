<?php
session_start();
require_once '../db.php';
$user_id = $_SESSION['id_utilisateur'];

// Récupération des documents déjà déposés
$docs = $pdo->prepare("SELECT * FROM Document WHERE id_possesseur = ?");
$docs->execute([$user_id]);
$mes_docs = $docs->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Documents - CY Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📁 Mon Porte-documents</h2>
        <a href="etudiant_home.php" class="btn btn-secondary">Retour</a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">Déposer un document</div>
                <div class="card-body">
                    <form action="upload_doc.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Type de document</label>
                            <input type="text" name="nom_doc" class="form-control" placeholder="Ex: CV 2026, Convention..." required>
                        </div>
                        <div class="mb-3">
                            <input type="file" name="fichier" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Téléverser</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nom</th>
                            <th>Fichier</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($mes_docs as $d): ?>
                        <tr>
                            <td><?= htmlspecialchars($d['nom_doc']) ?></td>
                            <td><small class="text-muted"><?= $d['chemin_fichier'] ?></small></td>
                            <td>
                                <a href="../documents/<?= $d['chemin_fichier'] ?>" class="btn btn-sm btn-outline-info" target="_blank">Voir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
