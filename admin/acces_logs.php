<?php
session_start();
require_once 'db.php';

// Sécurité : Seul l'admin passe
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: connexion.html');
    exit();
}

// Logique pour valider un compte si on clique sur le bouton
if (isset($_GET['valider_id'])) {
    $id = intval($_GET['valider_id']);
    $stmt = $pdo->prepare("UPDATE Utilisateur SET est_valide = 1 WHERE id_utilisateur = ?");
    $stmt->execute([$id]);
    logAction($_SESSION['user_id'], "Validation du compte ID $id"); // Trace l'action 
}

// Récupération des comptes en attente (Entreprises et Admins uniquement)
$en_attente = $pdo->query("SELECT * FROM Utilisateur WHERE est_valide = 0")->fetchAll();

// Récupération des 20 dernières traces 
$traces = $pdo->query("SELECT t.*, u.nom_utilisateur FROM Trace t JOIN Utilisateur u ON t.id_utilisateur = u.id_utilisateur ORDER BY date_action DESC LIMIT 20")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - CY Tech</title>
    <link rel="stylesheet" href="assets/css/admin.css"> </head>
<body>
    <h1>Panneau d'administration</h1>

    <section>
        <h2>Comptes en attente de validation</h2>
        <table border="1">
            <tr>
                <th>Nom</th>
                <th>Rôle</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
            <?php foreach($en_attente as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['nom_utilisateur']) ?></td>
                <td><?= htmlspecialchars($u['role_utilisateur']) ?></td>
                <td><?= htmlspecialchars($u['mail']) ?></td>
                <td><a href="acces_logs.php?valider_id=<?= $u['id_utilisateur'] ?>">Valider le compte</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>

    <hr>

    <section>
        <h2>Journal d'activité (Traces) [cite: 79]</h2>
        <ul>
            <?php foreach($traces as $t): ?>
                <li>[<?= $t['date_action'] ?>] <strong><?= $t['nom_utilisateur'] ?></strong> : <?= htmlspecialchars($t['acte']) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
</body>
</html>
