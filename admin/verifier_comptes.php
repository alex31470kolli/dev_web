<?php
session_start();
require_once '../db.php'; // Correction : remonter d'un dossier pour db.php

// Sécurité : vérifier que l'utilisateur est bien admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../connexion.html');
    exit();
}

// Logique de validation si le bouton est cliqué
if (isset($_POST['valider_id'])) {
    $id = intval($_POST['valider_id']);
    $stmt = $pdo->prepare("UPDATE Utilisateur SET est_valide = 1 WHERE id_utilisateur = ?");
    if ($stmt->execute([$id])) {
        logAction($_SESSION['id_utilisateur'], "Validation du compte utilisateur ID : $id");
    }
}

// Récupération des comptes en attente (Entreprises et Admins uniquement) [cite: 25, 75]
$stmt = $pdo->query("SELECT * FROM Utilisateur WHERE est_valide = 0 AND role_utilisateur != 'etudiant'");
$en_attente = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Validation des comptes - CY Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Comptes en attente de validation</h2>
            <a href="admin_home.php" class="btn btn-secondary">← Retour au dashboard</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($en_attente as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['prenom'] . " " . $u['nom_utilisateur']); ?></td>
                            <td><?= htmlspecialchars($u['mail']); ?></td>
                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($u['role_utilisateur']); ?></span></td>
                            <td class="text-end">
                                <form method="POST" class="m-0">
                                    <input type="hidden" name="valider_id" value="<?= $u['id_utilisateur']; ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Approuver</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($en_attente)): ?>
                        <tr>
                            <td colspan='4' class="text-center text-muted py-3">Aucune demande en attente.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
