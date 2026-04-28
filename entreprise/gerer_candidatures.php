<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'entreprise') { 
    header('Location: ../connexion.html'); exit(); 
}

$id_user = $_SESSION['id_utilisateur'];

// 1. Trouver l'entreprise
$stmtEnt = $pdo->prepare("SELECT id_entreprise FROM Entreprise WHERE id_referent = ?");
$stmtEnt->execute([$id_user]);
$entreprise = $stmtEnt->fetch();
$id_ent = $entreprise['id_entreprise'];

// 2. Traitement des actions (Accepter/Refuser)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_etu = intval($_POST['id_etudiant']);
    $id_off = intval($_POST['id_offre']);

    if ($_POST['action'] === 'accepter') {
        $pdo->beginTransaction();
        try {
            // On accepte l'étudiant
            $pdo->prepare("UPDATE Candidature SET statut = 1 WHERE id_utilisateur = ? AND id_offre = ?")->execute([$id_etu, $id_off]);
            
            // On crée le stage
            $stmtDate = $pdo->prepare("SELECT date_debut, date_fin FROM Offre WHERE id_offre = ?");
            $stmtDate->execute([$id_off]);
            $o = $stmtDate->fetch();

            $pdo->prepare("INSERT INTO Stage (id_etudiant, id_offre, date_debut_stage, date_fin, etat_suivi) VALUES (?, ?, ?, ?, 'Validation Admin')")
                ->execute([$id_etu, $id_off, $o['date_debut'], $o['date_fin']]);

            logAction($id_user, "A accepté un stagiaire pour l'offre #$id_off");
            $pdo->commit();
        } catch (Exception $e) { $pdo->rollBack(); die($e->getMessage()); }
    } 
    elseif ($_POST['action'] === 'refuser') {
        $pdo->prepare("UPDATE Candidature SET statut = 2 WHERE id_utilisateur = ? AND id_offre = ?")->execute([$id_etu, $id_off]);
    }
}

// 3. REQUÊTE FILTRÉE (Le coeur de votre demande)
// On sélectionne les candidatures EN ATTENTE (statut 0)
// MAIS UNIQUEMENT pour les offres qui n'ont pas encore de candidat accepté (statut 1)
$sql = "SELECT c.*, u.prenom, u.nom_utilisateur, o.titre 
        FROM Candidature c 
        JOIN Utilisateur u ON c.id_utilisateur = u.id_utilisateur 
        JOIN Offre o ON c.id_offre = o.id_offre 
        WHERE o.id_entreprise = ? 
        AND c.statut = 0 
        AND c.id_offre NOT IN (
            SELECT id_offre FROM Candidature WHERE statut = 1
        )
        ORDER BY c.id_offre ASC";

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
        <h2>👥 Candidatures à traiter</h2>
        <a href="entreprise_home.php" class="btn btn-secondary">← Retour</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Candidat</th>
                        <th>Offre concernée</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($candidatures as $cand): ?>
                    <tr>
                        <td class="ps-3"><strong><?= htmlspecialchars($cand['prenom'] . " " . $cand['nom_utilisateur']) ?></strong></td>
                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($cand['titre']) ?></span></td>
                        <td class="text-end pe-3">
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="id_etudiant" value="<?= $cand['id_utilisateur'] ?>">
                                <input type="hidden" name="id_offre" value="<?= $cand['id_offre'] ?>">
                                <button type="submit" name="action" value="accepter" class="btn btn-sm btn-success me-1">Accepter</button>
                                <button type="submit" name="action" value="refuser" class="btn btn-sm btn-outline-danger">Refuser</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($candidatures)): ?>
                        <tr><td colspan="3" class="text-center py-4 text-muted">Aucune candidature en attente de traitement.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
