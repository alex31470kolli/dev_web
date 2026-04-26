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
    <link rel="stylesheet" href="/assets/css/css_all.css">
</head>
<body>
    <h1>Comptes en attente de validation</h1>
    <a href="admin_home.php">← Retour au dashboard</a>

    <table>
        <tr>
            <th>Utilisateur</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Action</th>
        </tr>
        <?php foreach($en_attente as $u): ?>
        <tr>
            <td><?php echo htmlspecialchars($u['prenom'] . " " . $u['nom_utilisateur']); ?></td>
            <td><?php echo htmlspecialchars($u['mail']); ?></td>
            <td><?php echo htmlspecialchars($u['role_utilisateur']); ?></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="valider_id" value="<?php echo $u['id_utilisateur']; ?>">
                    <button type="submit" class="btn-valider">Approuver</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($en_attente)) echo "<tr><td colspan='4'>Aucune demande en attente.</td></tr>"; ?>
    </table>
</body>
</html>
