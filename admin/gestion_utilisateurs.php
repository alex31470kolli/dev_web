<?php
session_start();
require_once '../db.php';

// --- SÉCURITÉ ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../connexion.html'); exit();
}

$message = "";

// --- 0. RÉCUPÉRATION DES FILIÈRES POUR LE JS ---
$stmtF = $pdo->query("SELECT nom_filiere, niveau FROM Filiere");
$filieres_data = [];
while ($row = $stmtF->fetch()) {
    $filieres_data[$row['niveau']][] = $row['nom_filiere'];
}
$json_filieres = json_encode($filieres_data);

// --- 1. LOGIQUE DE SUPPRESSION ---
if (isset($_POST['supprimer_id'])) {
    $id = intval($_POST['supprimer_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM Utilisateur WHERE id_utilisateur = ?");
        $stmt->execute([$id]);
        $message = "<div class='alert alert-success shadow-sm'>Utilisateur supprimé.</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Erreur : Impossible de supprimer cet utilisateur.</div>";
    }
}

// --- 2. LOGIQUE DE MODIFICATION (UPDATE) ---
if (isset($_POST['update_user'])) {
    $id = intval($_POST['user_id']);
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    $role = $_POST['role'];

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE Utilisateur SET prenom = ?, nom_utilisateur = ? WHERE id_utilisateur = ?");
        $stmt->execute([$prenom, $nom, $id]);

        if ($role === 'etudiant') {
            $stmtEtu = $pdo->prepare("UPDATE Utilisateur SET annee = ?, filiere = ? WHERE id_utilisateur = ?");
            $stmtEtu->execute([$_POST['annee'], $_POST['filiere'], $id]);
        } elseif ($role === 'entreprise') {
            $stmtEnt = $pdo->prepare("UPDATE Entreprise SET nom_entreprise = ? WHERE id_referent = ?");
            $stmtEnt->execute([$_POST['nom_entreprise'], $id]);
        }

        $pdo->commit();
        $message = "<div class='alert alert-success shadow-sm'>Modifications enregistrées avec succès.</div>";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "<div class='alert alert-danger'>Erreur lors de la mise à jour.</div>";
    }
}

// --- 3. RECHERCHE ET PAGINATION ---
$search = $_GET['search'] ?? '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$countSql = "SELECT COUNT(*) FROM Utilisateur WHERE nom_utilisateur LIKE :s OR prenom LIKE :s OR mail LIKE :s";
$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute(['s' => "%$search%"]);
$total_rows = $stmtCount->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT u.*, e.nom_entreprise 
        FROM Utilisateur u 
        LEFT JOIN Entreprise e ON u.id_utilisateur = e.id_referent 
        WHERE (u.nom_utilisateur LIKE :s OR u.prenom LIKE :s OR u.mail LIKE :s)
        ORDER BY u.id_utilisateur DESC
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':s', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Utilisateurs - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5 pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>⚙️ Gestion des Utilisateurs</h2>
        <a href="admin_home.php" class="btn btn-secondary btn-sm">← Retour</a>
    </div>

    <?= $message ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <select name="limit" class="form-select" onchange="this.form.submit()">
                        <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5 par page</option>
                        <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10 par page</option>
                        <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20 par page</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-dark w-100">Appliquer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Identité</th>
                        <th>Détails</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td class="ps-3">
                            <strong><?= htmlspecialchars($u['prenom'] . " " . $u['nom_utilisateur']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($u['mail']) ?></small>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?= strtoupper($u['role_utilisateur']) ?></span><br>
                            <small class="text-muted">
                                <?php if($u['role_utilisateur'] == 'etudiant'): ?>
                                    <?= $u['annee'] ?> | <?= htmlspecialchars($u['filiere']) ?>
                                <?php elseif($u['role_utilisateur'] == 'entreprise'): ?>
                                    <?= htmlspecialchars($u['nom_entreprise'] ?? 'N/A') ?>
                                <?php endif; ?>
                            </small>
                        </td>
                        <td class="text-end pe-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $u['id_utilisateur'] ?>">Modifier</button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?');">
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

<?php foreach($users as $u): ?>
<div class="modal fade" id="editModal<?= $u['id_utilisateur'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier <?= htmlspecialchars($u['prenom']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-start">
                <input type="hidden" name="user_id" value="<?= $u['id_utilisateur'] ?>">
                <input type="hidden" name="role" value="<?= $u['role_utilisateur'] ?>">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Prénom</label>
                    <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($u['prenom']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nom</label>
                    <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($u['nom_utilisateur']) ?>" required>
                </div>

                <?php if($u['role_utilisateur'] == 'etudiant'): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Année (ING)</label>
                        <select name="annee" class="form-select select-annee" data-target="filiere_<?= $u['id_utilisateur'] ?>">
                            <option value="ING1" <?= $u['annee'] == 'ING1' ? 'selected' : '' ?>>ING1</option>
                            <option value="ING2" <?= $u['annee'] == 'ING2' ? 'selected' : '' ?>>ING2</option>
                            <option value="ING3" <?= $u['annee'] == 'ING3' ? 'selected' : '' ?>>ING3</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Filière</label>
                        <select name="filiere" class="form-select" id="filiere_<?= $u['id_utilisateur'] ?>" data-current="<?= htmlspecialchars($u['filiere']) ?>" required>
                            </select>
                    </div>
                <?php elseif($u['role_utilisateur'] == 'entreprise'): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nom de l'entreprise</label>
                        <input type="text" name="nom_entreprise" class="form-control" value="<?= htmlspecialchars($u['nom_entreprise']) ?>">
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" name="update_user" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<script>
// Données transmises par PHP
const filieresParAnnee = <?= $json_filieres ?>;

// Fonction pour mettre à jour les filières
function updateFilieres(selectAnnee) {
    const targetId = selectAnnee.getAttribute('data-target');
    const selectFiliere = document.getElementById(targetId);
    const anneeChoisie = selectAnnee.value;
    const filiereActuelle = selectFiliere.getAttribute('data-current');

    // On vide le select
    selectFiliere.innerHTML = '<option value="" disabled selected>-- Choisissez une filière --</option>';

    // On ajoute les nouvelles options
    if (filieresParAnnee[anneeChoisie]) {
        filieresParAnnee[anneeChoisie].forEach(f => {
            const option = document.createElement('option');
            option.value = f;
            option.textContent = f;
            if (f === filiereActuelle) option.selected = true;
            selectFiliere.appendChild(option);
        });
    }
}

// Initialisation au chargement et écoute des changements
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.select-annee').forEach(select => {
        updateFilieres(select); // Remplir au chargement
        select.addEventListener('change', () => updateFilieres(select)); // Ecouter le changement
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
