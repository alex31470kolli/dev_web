<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'entreprise') { 
    header('Location: connexion.html'); exit(); 
}

$id_user = $_SESSION['id_utilisateur'];

// Trouver l'entreprise de ce référent
$stmtEnt = $pdo->prepare("SELECT id_entreprise FROM Entreprise WHERE id_referent = ?");
$stmtEnt->execute([$id_user]);
$entreprise = $stmtEnt->fetch();

if (!$entreprise) { die("Erreur : Aucune entreprise associée."); }
$id_ent = $entreprise['id_entreprise'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'accepter') {
    $id_etudiant = intval($_POST['id_etudiant']);
    $id_offre_post = intval($_POST['id_offre']);

    $pdo->beginTransaction(); // Sécurité : tout passe ou rien ne passe
    try {
        // 1. Update de la candidature
        $pdo->prepare("UPDATE Candidature SET statut = 1 WHERE id_utilisateur = ? AND id_offre = ?")
            ->execute([$id_etudiant, $id_offre_post]);

        // 2. Création du stage en attente de l'admin
        // On récupère les dates de l'offre pour remplir le stage
        $stmtOffre = $pdo->prepare("SELECT date_debut, date_fin FROM Offre WHERE id_offre = ?");
        $stmtOffre->execute([$id_offre_post]);
        $o = $stmtOffre->fetch();

        $sqlStage = "INSERT INTO Stage (id_etudiant, id_offre, date_debut_stage, date_fin, etat_suivi) 
                     VALUES (?, ?, ?, ?, 'Validation Admin')";
        $pdo->prepare($sqlStage)->execute([$id_etudiant, $id_offre_post, $o['date_debut'], $o['date_fin']]);

        logAction($_SESSION['id_utilisateur'], "A accepté l'étudiant $id_etudiant (Stage créé)");
        $pdo->commit();
        $message_alerte = "<div class='alert alert-success'>Candidature acceptée ! Le dossier est transmis à l'admin.</div>";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message_alerte = "<div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div>";
    }
}

// RÉCUPÉRATION DES CANDIDATURES EN ATTENTE (statut = 0)
$sql = "SELECT c.*, u.prenom, u.nom_utilisateur, u.mail, o.titre 
        FROM Candidature c
        JOIN Utilisateur u ON c.id_utilisateur = u.id_utilisateur
        JOIN Offre o ON c.id_offre = o.id_offre
        WHERE c.id_entreprise = ? AND c.statut = 0";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_ent]);
$candidatures = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer les candidatures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>👥 Candidatures en attente</h2>
        <a href="entreprise_home.php" class="btn btn-secondary">Retour à l'accueil</a>
    </div>

    <?= $message_alerte ?? '' ?>

    <div class="card shadow-sm">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Candidat</th>
                        <th>Offre concernée</th>
                        <th>Contact</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($candidatures as $cand): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($cand['prenom'] . ' ' . $cand['nom_utilisateur']) ?></strong></td>
                        <td><?= htmlspecialchars($cand['titre']) ?></td>
                        <td><a href="../pages_communes/messagerie.php" class="btn btn-sm btn-outline-info">Voir ses messages</a></td>
                        <td class="text-end">
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="id_etudiant" value="<?= $cand['id_utilisateur'] ?>">
                                <input type="hidden" name="id_offre" value="<?= $cand['id_offre'] ?>">
                                
                                <button type="submit" name="action" value="accepter" class="btn btn-success btn-sm me-2">✅ Accepter</button>
                                <button type="submit" name="action" value="refuser" class="btn btn-danger btn-sm">❌ Refuser</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($candidatures)): ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">Aucune nouvelle candidature.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>
