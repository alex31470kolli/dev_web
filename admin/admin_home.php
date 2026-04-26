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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - CY TECH Stages</title>
    <link rel="stylesheet" href="/assets/css/css_all.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>

    <header class="top-nav">
        <div class="container-nav">
            <span class="logo">CY TECH ADMIN</span>
            <nav>
                <span>Bienvenue, <strong><?= htmlspecialchars($prenom_admin) ?></strong></span>
                <a href="../deconnexion.php" class="logout-link">🚪 Déconnexion</a>
            </nav>
        </div>
    </header>

    <main class="container-full">
        <section class="admin-intro">
            <h1>Panneau de Contrôle de la Plateforme</h1>
            <p>Gérez les utilisateurs, les entreprises et consultez les archives du site.</p>
        </section>

        <div class="dashboard-layout">
            <aside class="actions-list">
                <a href="verifier_comptes.php" class="action-card">
                    <div class="icon">👥</div>
                    <div class="text">
                        <h3>Valider les Comptes</h3>
                        <p>Approuver les entreprises et admins en attente.</p>
                    </div>
                </a>

                <a href="acces_logs.php" class="action-card">
                    <div class="icon">📜</div>
                    <div class="text">
                        <h3>Journal d'Activité</h3>
                        <p>Consulter les logs (traces) du site.</p>
                    </div>
                </a>

                <a href="consulter_archives.php" class="action-card">
                    <div class="icon">📦</div>
                    <div class="text">
                        <h3>Consulter les Archives</h3>
                        <p>Historique des stages des années passées.</p>
                    </div>
                </a>

                <a href="statistiques.php" class="action-card">
                    <div class="icon">📊</div>
                    <div class="text">
                        <h3>Statistiques</h3>
                        <p>Analyse des offres et taux de placement.</p>
                    </div>
                </a>
            </aside>

            <section class="management-tables">
                <div class="filter-box">
                    <form method="GET" class="filter-form">
                        <select name="filiere">
                            <option value="">Toutes les filières</option>
                            <option value="GI" <?= $filtre_filiere == 'GI' ? 'selected' : '' ?>>GI</option>
                            <option value="GM - Data" <?= $filtre_filiere == 'GM - Data' ? 'selected' : '' ?>>GM - Data</option>
                        </select>

                        <select name="annee">
                            <option value="">Toutes les classes</option>
                            <option value="ING1" <?= $filtre_annee == 'ING1' ? 'selected' : '' ?>>ING1</option>
                            <option value="ING2" <?= $filtre_annee == 'ING2' ? 'selected' : '' ?>>ING2</option>
                            <option value="ING3" <?= $filtre_annee == 'ING3' ? 'selected' : '' ?>>ING3</option>
                        </select>

                        <button type="submit" class="btn-filter">Filtrer</button>
                        <a href="admin_home.php" class="btn-reset">Réinitialiser</a>
                    </form>
                </div>

                <h2>🚀 Stages en cours d'exécution</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Étudiant</th>
                                <th>Classe</th>
                                <th>Filière</th>
                                <th>Sujet / Offre</th>
                                <th>Fin prévue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($stages_en_cours as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['prenom'] . " " . $s['nom_utilisateur']) ?></td>
                                <td><?= $s['annee'] ?></td>
                                <td><?= $s['filiere'] ?></td>
                                <td><?= htmlspecialchars($s['titre']) ?></td>
                                <td><?= date('d/m/Y', strtotime($s['date_fin'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <br>

                <h2>📁 Stages terminés (À archiver)</h2>
                <div class="table-responsive">
                    <table class="table-urgent">
                        <thead>
                            <tr>
                                <th>Étudiant</th>
                                <th>Offre</th>
                                <th>Date de fin</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($stages_termines as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nom_utilisateur']) ?></td>
                                    <td><?= htmlspecialchars($row['titre']) ?></td>
                                    <td><strong><?= date('d/m/Y', strtotime($row['date_fin'])) ?></strong></td>
                                    <td>
                                        <form method='POST' action='archiver_stage.php' class="inline-form">
                                            <input type='hidden' name='id_stage' value='<?= $row['id_stage'] ?>'>
                                            <button type='submit' class="btn-archive">Archiver</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

</body>
</html>
