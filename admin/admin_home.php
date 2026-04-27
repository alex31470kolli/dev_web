<?php
session_start();
require_once '../db.php';

// --- SÉCURITÉ ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../connexion.html');
    exit();
}

$prenom_admin = $_SESSION['prenom'] ?? 'Administrateur';
$filtre_filiere = $_GET['filiere'] ?? '';
$filtre_annee = $_GET['annee'] ?? '';

// --- REQUÊTE 1 : STAGES EN COURS ---
$sql_en_cours = "SELECT s.*, u.nom_utilisateur, u.prenom, u.annee, u.filiere, o.titre 
                 FROM Stage s 
                 JOIN Utilisateur u ON s.id_etudiant = u.id_utilisateur 
                 JOIN Offre o ON s.id_offre = o.id_offre 
                 WHERE s.etat_suivi = 'En cours'";

if ($filtre_filiere) $sql_en_cours .= " AND u.filiere = " . $pdo->quote($filtre_filiere);
if ($filtre_annee) $sql_en_cours .= " AND u.annee = " . $pdo->quote($filtre_annee);

$stages_en_cours = $pdo->query($sql_en_cours)->fetchAll();

// --- REQUÊTE 2 : STAGES TERMINÉS ---
$sql_termines = "SELECT s.*, u.nom_utilisateur, o.titre 
                 FROM Stage s 
                 JOIN Utilisateur u ON s.id_etudiant = u.id_utilisateur 
                 JOIN Offre o ON s.id_offre = o.id_offre 
                 WHERE s.etat_suivi = 'Terminé'
                 ORDER BY s.date_fin ASC";

$stages_termines = $pdo->query($sql_termines)->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - CY TECH Stages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="#">🛠️ Panneau Admin CY Tech</a>
            <div class="d-flex text-white align-items-center">
                <span class="me-3">Bonjour, <?= htmlspecialchars($prenom_admin) ?></span>
                <a href="../deconnexion.php" class="btn btn-outline-light btn-sm">Se déconnecter</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <div class="row">
            
            <div class="col-md-3 mb-4">
                <div class="list-group shadow-sm">
                    <a href="verifier_comptes.php" class="list-group-item list-group-item-action py-3">
                        <h6 class="mb-1">👥 Valider les Comptes</h6>
                        <small class="text-muted">Approuver les entreprises.</small>
                    </a>
                    <a href="acces_logs.php" class="list-group-item list-group-item-action py-3">
                        <h6 class="mb-1">📜 Journal d'Activité</h6>
                        <small class="text-muted">Consulter les traces du site.</small>
                    </a>
                    <a href="consulter_archives.php" class="list-group-item list-group-item-action py-3">
                        <h6 class="mb-1">📦 Consulter les Archives</h6>
                        <small class="text-muted">Historique des stages passés.</small>
                    </a>
                    <a href="statistiques.php" class="list-group-item list-group-item-action py-3">
                        <h6 class="mb-1">📊 Statistiques</h6>
                        <small class="text-muted">Analyse et taux de placement.</small>
                    </a>
                    <a href="gestion_utilisateurs.php" class="list-group-item list-group-item-action py-3">
                        <h6 class="mb-1">⚙️ Gestion Utilisateurs</h6>
                        <small class="text-muted">Modifier ou supprimer des comptes.</small>
                    </a>
                    <a href="../messagerie.php" class="list-group-item list-group-item-action py-3">
                        <h6 class="mb-1">✉️ Messagerie</h6>
                        <small class="text-muted">Boîte de réception centrale.</small>
                    </a>
                </div>
            </div>

            <div class="col-md-9">
                
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-2 align-items-center">
                            <div class="col-auto">
                                <select class="form-select" name="filiere">
                                    <option value="">Toutes filières</option>
                                    <option value="GI" <?= $filtre_filiere == 'GI' ? 'selected' : '' ?>>GI</option>
                                    <option value="GM - Data" <?= $filtre_filiere == 'GM - Data' ? 'selected' : '' ?>>GM - Data</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <select class="form-select" name="annee">
                                    <option value="">Toutes classes</option>
                                    <option value="ING1" <?= $filtre_annee == 'ING1' ? 'selected' : '' ?>>ING1</option>
                                    <option value="ING2" <?= $filtre_annee == 'ING2' ? 'selected' : '' ?>>ING2</option>
                                    <option value="ING3" <?= $filtre_annee == 'ING3' ? 'selected' : '' ?>>ING3</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">Filtrer</button>
                                <a href="admin_home.php" class="btn btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0 text-primary">🚀 Stages en cours d'exécution</h5></div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Classe/Filière</th>
                                    <th>Sujet / Offre</th>
                                    <th>Fin prévue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($stages_en_cours as $s): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($s['prenom'] . " " . $s['nom_utilisateur']) ?></strong></td>
                                    <td><span class="badge bg-secondary"><?= $s['annee'] ?></span> <?= $s['filiere'] ?></td>
                                    <td><?= htmlspecialchars($s['titre']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($s['date_fin'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card shadow-sm border-warning mb-5">
                    <div class="card-header bg-warning text-dark"><h5 class="mb-0">📁 Stages terminés (À archiver)</h5></div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <tbody>
                                <?php foreach($stages_termines as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['nom_utilisateur']) ?></td>
                                        <td><?= htmlspecialchars($row['titre']) ?></td>
                                        <td>Fin : <strong><?= date('d/m/Y', strtotime($row['date_fin'])) ?></strong></td>
                                        <td class="text-end">
                                            <form method='POST' action='archiver_stage.php' class="m-0">
                                                <input type='hidden' name='id_stage' value='<?= $row['id_stage'] ?>'>
                                                <button type='submit' class="btn btn-outline-warning">Archiver le dossier</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>
</html>
