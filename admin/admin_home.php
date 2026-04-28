<?php
session_start();
require_once '../db.php';

// --- SÉCURITÉ ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../connexion.html'); exit();
}

$prenom_admin = $_SESSION['prenom'] ?? 'Administrateur';
$filtre_filiere = $_GET['filiere'] ?? '';
$filtre_annee = $_GET['annee'] ?? '';

// --- 1. REQUÊTE : DOSSIERS OÙ L'ADMIN DOIT AGIR (Cartes) ---
$stmtTasks = $pdo->query("SELECT s.*, u.prenom, u.nom_utilisateur, o.titre 
                          FROM Stage s 
                          JOIN Utilisateur u ON s.id_etudiant = u.id_utilisateur
                          JOIN Offre o ON s.id_offre = o.id_offre
                          WHERE s.etat_suivi IN ('Validation Admin', 'Signature Admin')
                          ORDER BY s.id_stage ASC");
$taches_admin = $stmtTasks->fetchAll();

// --- 2. REQUÊTE : SUIVI DES SIGNATURES TIERS (Tableau simple) ---
$stmtWait = $pdo->query("SELECT s.*, u.prenom, u.nom_utilisateur, o.titre 
                          FROM Stage s 
                          JOIN Utilisateur u ON s.id_etudiant = u.id_utilisateur
                          JOIN Offre o ON s.id_offre = o.id_offre
                          WHERE s.etat_suivi IN ('Signature Entreprise', 'Signature Étudiant')");
$attente_tiers = $stmtWait->fetchAll();

// --- 3. REQUÊTE : STAGES EN COURS (Avec filtres) ---
$sql_en_cours = "SELECT s.*, u.nom_utilisateur, u.prenom, u.annee, u.filiere, o.titre 
                 FROM Stage s 
                 JOIN Utilisateur u ON s.id_etudiant = u.id_utilisateur 
                 JOIN Offre o ON s.id_offre = o.id_offre 
                 WHERE s.etat_suivi = 'En cours'";

if ($filtre_filiere) $sql_en_cours .= " AND u.filiere = " . $pdo->quote($filtre_filiere);
if ($filtre_annee) $sql_en_cours .= " AND u.annee = " . $pdo->quote($filtre_annee);

$stages_en_cours = $pdo->query($sql_en_cours)->fetchAll();

// --- 4. REQUÊTE : STAGES TERMINÉS ---
$stages_termines = $pdo->query("SELECT s.*, u.nom_utilisateur, o.titre 
                                FROM Stage s 
                                JOIN Utilisateur u ON s.id_etudiant = u.id_utilisateur 
                                JOIN Offre o ON s.id_offre = o.id_offre 
                                WHERE s.etat_suivi = 'Terminé'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Console Admin - CY Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-task { border-left: 5px solid #dc3545; transition: 0.3s; height: 100%; }
        .card-task:hover { transform: translateY(-5px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .sidebar-link { transition: 0.2s; }
        .sidebar-link:hover { background-color: #f8f9fa; padding-left: 1.5rem !important; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="#">🛠️ Admin CY Tech</a>
            <div class="ms-auto text-white">
                <span class="me-3">Bonjour, <?= htmlspecialchars($prenom_admin) ?></span>
                <a href="../pages_communes/profil.php" class="btn btn-info btn-sm me-2 text-white">👤 Profil</a>
                <a href="../deconnexion.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <div class="row">
            
            <div class="col-md-9">
                
                <h4 class="mb-4 text-danger">🚩 Vos actions requises (<?= count($taches_admin) ?>)</h4>
                <div class="row mb-5">
                    <?php foreach($taches_admin as $s): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm card-task">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center border-0">
                                <span class="badge bg-danger">Urgent</span>
                                <small class="text-muted">#<?= $s['id_stage'] ?></small>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($s['prenom'] . " " . $s['nom_utilisateur']) ?></h5>
                                <p class="card-text text-muted small">Offre : <?= htmlspecialchars($s['titre']) ?></p>
                                <p class="mb-0"><span class="badge bg-info text-dark"><?= $s['etat_suivi'] ?></span></p>
                            </div>
                            <div class="card-footer bg-white border-top-0 pt-0">
                                <?php if($s['etat_suivi'] == 'Validation Admin'): ?>
                                    <form action="traiter_stage.php" method="POST" enctype="multipart/form-data" class="mt-2">
                                        <input type="hidden" name="id_stage" value="<?= $s['id_stage'] ?>">
                                        <input type="hidden" name="action" value="envoyer">
                                        <div class="mb-2">
                                            <label class="x-small fw-bold" style="font-size: 0.75rem;">Joindre Convention (PDF) :</label>
                                            <input type="file" name="convention_pdf" class="form-control form-control-sm" required accept=".pdf">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm w-100">Envoyer pour signature</button>
                                    </form>
                                <?php else: ?>
                                    <div class="d-grid gap-2 mt-2">
                                        <a href="../documents/<?= $s['chemin_convention'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">👁️ Voir le document</a>
                                        <a href="traiter_stage.php?id=<?= $s['id_stage'] ?>&action=finaliser" class="btn btn-sm btn-success">Signer et Lancer</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if(empty($taches_admin)): ?>
                        <div class="col-12"><div class="alert alert-light text-center border">Aucune action immédiate requise.</div></div>
                    <?php endif; ?>
                </div>

                <div class="card shadow-sm mb-5">
                    <div class="card-header bg-info text-white fw-bold">⏳ En attente de signature tiers</div>
                    <div class="card-body p-0">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light"><tr><th>Stagiaire</th><th>Étape actuelle</th><th class="text-center">Document</th></tr></thead>
                            <tbody>
                                <?php foreach($attente_tiers as $at): ?>
                                <tr>
                                    <td class="ps-3"><?= htmlspecialchars($at['prenom']) ?></td>
                                    <td>
                                        <?php if($at['etat_suivi'] == 'Signature Entreprise'): ?>
                                            <span class="text-primary"><i class="bi bi-building"></i> Attente <strong>Entreprise</strong></span>
                                        <?php else: ?>
                                            <span class="text-warning"><i class="bi bi-person"></i> Attente <strong>Étudiant</strong></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><a href="../documents/<?= $at['chemin_convention'] ?>" target="_blank" class="btn btn-link btn-sm p-0">📄 Voir</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-2 align-items-center">
                            <div class="col-auto"><label class="small fw-bold">Filtres :</label></div>
                            <div class="col-auto">
                                <select class="form-select form-select-sm" name="filiere">
                                    <option value="">Toutes filières</option>
                                    <option value="GI" <?= $filtre_filiere == 'GI' ? 'selected' : '' ?>>GI</option>
                                    <option value="Cybersécurité" <?= $filtre_filiere == 'Cybersécurité' ? 'selected' : '' ?>>Cybersécurité</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <select class="form-select form-select-sm" name="annee">
                                    <option value="">Toutes classes</option>
                                    <option value="ING1" <?= $filtre_annee == 'ING1' ? 'selected' : '' ?>>ING1</option>
                                    <option value="ING2" <?= $filtre_annee == 'ING2' ? 'selected' : '' ?>>ING2</option>
                                    <option value="ING3" <?= $filtre_annee == 'ING3' ? 'selected' : '' ?>>ING3</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
                                <a href="admin_home.php" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0 text-primary">🚀 Stages en cours d'exécution (<?= count($stages_en_cours) ?>)</h5></div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Étudiant</th><th>Filière</th><th>Sujet</th><th>Fin</th></tr></thead>
                            <tbody>
                                <?php foreach($stages_en_cours as $sec): ?>
                                <tr>
                                    <td class="ps-3"><?= htmlspecialchars($sec['prenom'] . " " . $sec['nom_utilisateur']) ?></td>
                                    <td><span class="badge bg-secondary"><?= $sec['annee'] ?></span> <?= $sec['filiere'] ?></td>
                                    <td><?= htmlspecialchars($sec['titre']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($sec['date_fin'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card shadow-sm border-warning">
                    <div class="card-header bg-warning text-dark fw-bold">📁 Stages terminés (Prêts pour archivage)</div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <tbody>
                                <?php foreach($stages_termines as $st): ?>
                                <tr>
                                    <td class="ps-3"><?= htmlspecialchars($st['nom_utilisateur']) ?></td>
                                    <td><?= htmlspecialchars($st['titre']) ?></td>
                                    <td class="text-end pe-3">
                                        <form action="archiver_stage.php" method="POST" class="m-0">
                                            <input type="hidden" name="id_stage" value="<?= $st['id_stage'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-warning">Archiver</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white fw-bold">Menu Gestion</div>
                    <div class="list-group list-group-flush">
                        <a href="verifier_comptes.php" class="list-group-item list-group-item-action sidebar-link py-3">
                            <div class="fw-bold">👥 Valider les Comptes</div>
                            <small class="text-muted">Approuver les nouvelles entreprises.</small>
                        </a>
                        <a href="acces_logs.php" class="list-group-item list-group-item-action sidebar-link py-3">
                            <div class="fw-bold">📜 Journal d'Activité</div>
                            <small class="text-muted">Consulter les logs (Trace).</small>
                        </a>
                        <a href="gestion_utilisateurs.php" class="list-group-item list-group-item-action sidebar-link py-3">
                            <div class="fw-bold">⚙️ Gestion Utilisateurs</div>
                            <small class="text-muted">Supprimer ou modifier des profils.</small>
                        </a>
                        <a href="gestion_offres.php" class="list-group-item list-group-item-action sidebar-link py-3">
                            <div class="fw-bold">📢 Gestion des Offres</div>
                            <small class="text-muted">Modifier, supprimer ou mettre en avant.</small>
                        </a>
                        <a href="../pages_communes/messagerie.php" class="list-group-item list-group-item-action sidebar-link py-3">
                            <div class="fw-bold">✉️ Messagerie Centrale</div>
                            <small class="text-muted">Lire et envoyer des messages.</small>
                        </a>
                    </div>
                </div>
                
                <div class="mt-4 p-3 bg-white rounded shadow-sm border">
                    <h6>Statistiques rapides</h6>
                    <hr>
                    <p class="small mb-1">Dossiers à signer : <strong><?= count($taches_admin) ?></strong></p>
                    <p class="small mb-0">Stages actifs : <strong><?= count($stages_en_cours) ?></strong></p>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
