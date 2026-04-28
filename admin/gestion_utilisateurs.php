<?php
session_start();
require_once '../db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ../connexion.html'); exit(); }

// Si l'admin demande la suppression d'un utilisateur
if (isset($_POST['supprimer_id'])) {
    $stmt = $pdo->prepare("DELETE FROM Utilisateur WHERE id_utilisateur = ?");
    $stmt->execute([$_POST['supprimer_id']]);
    $message_succes = "Utilisateur supprimé avec succès.";
}

// Récupération de tous les utilisateurs
$utilisateurs = $pdo->query("SELECT * FROM Utilisateur ORDER BY role_utilisateur, nom_utilisateur")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Utilisateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>⚙️ Gestion des Utilisateurs</h2>
        <a href="admin_home.php" class="btn btn-secondary">← Retour</a>
    </div>

    <?php if(isset($message_succes)): ?>
        <div class="alert alert-success"><?= $message_succes ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Nom Complet</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($utilisateurs as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom_utilisateur']) ?></td>
                        <td><?= htmlspecialchars($u['mail']) ?></td>
                        <td>
                            <span class="badge bg-<?= $u['role_utilisateur'] == 'admin' ? 'danger' : ($u['role_utilisateur'] == 'entreprise' ? 'info text-dark' : 'secondary') ?>">
                                <?= strtoupper($u['role_utilisateur']) ?>
                            </span>
                        </td>
                        <td><?= $u['est_valide'] ? '✅ Valide' : '⏳ En attente' ?></td>
                        <td class="text-end">
                            <form method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                <input type="hidden" name="supprimer_id" value="<?= $u['id_utilisateur'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
