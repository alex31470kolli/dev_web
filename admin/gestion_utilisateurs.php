<?php
session_start();
require_once '../db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ../connexion.html'); exit(); }

// --- PARAMÈTRES DE RECHERCHE ET PAGINATION ---
$search = $_GET['search'] ?? '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// --- TRAITEMENT DES MODIFICATIONS ---
if (isset($_POST['update_user'])) {
    $id = intval($_POST['user_id']);
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    
    // Update de base
    $pdo->prepare("UPDATE Utilisateur SET prenom = ?, nom_utilisateur = ? WHERE id_utilisateur = ?")
        ->execute([$prenom, $nom, $id]);

    if ($_POST['role'] === 'etudiant') {
        $pdo->prepare("UPDATE Utilisateur SET annee = ?, filiere = ? WHERE id_utilisateur = ?")
            ->execute([$_POST['annee'], $_POST['filiere'], $id]);
    } elseif ($_POST['role'] === 'entreprise') {
        $pdo->prepare("UPDATE Entreprise SET nom_entreprise = ? WHERE id_referent = ?")
            ->execute([$_POST['nom_entreprise'], $id]);
    }
    $msg = "Utilisateur mis à jour.";
}

// --- REQUÊTE SQL DYNAMIQUE ---
$queryStr = "SELECT u.*, e.nom_entreprise 
             FROM Utilisateur u 
             LEFT JOIN Entreprise e ON u.id_utilisateur = e.id_referent 
             WHERE (u.nom_utilisateur LIKE :search OR u.mail LIKE :search OR u.prenom LIKE :search)";
             
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM Utilisateur WHERE nom_utilisateur LIKE :s OR mail LIKE :s");
$countStmt->execute(['s' => "%$search%"]);
$total_rows = $countStmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$stmt = $pdo->prepare($queryStr . " LIMIT $limit OFFSET $offset");
$stmt->execute(['search' => "%$search%"]);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Avancée Utilisateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5 pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>⚙️ Gestion des Utilisateurs</h2>
        <a href="admin_home.php" class="btn btn-secondary">← Dashboard</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Chercher un nom, un prénom ou un mail..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <select name="limit" class="form-select">
                        <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5 par page</option>
                        <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10 par page</option>
                        <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50 par page</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-dark w-100">Filtrer / Rechercher</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Utilisateur</th>
                    <th>Rôle / Infos</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($u['prenom'].' '.$u['nom_utilisateur']) ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars($u['mail']) ?></small>
                    </td>
                    <td>
                        <span class="badge bg-secondary"><?= strtoupper($u['role_utilisateur']) ?></span><br>
                        <small>
                            <?php if($u['role_utilisateur'] == 'etudiant'): ?>
                                <?= $u['annee'] ?> - <?= htmlspecialchars($u['filiere']) ?>
                            <?php elseif($u['role_utilisateur'] == 'entreprise'): ?>
                                Entreprise : <?= htmlspecialchars($u['nom_entreprise'] ?? 'Non définie') ?>
                            <?php endif; ?>
                        </small>
                    </td>
                    <td class="text-end pe-3">
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $u['id_utilisateur'] ?>">Modifier</button>
                    </td>
                </tr>

                <div class="modal fade" id="editModal<?= $u['id_utilisateur'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST" class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Modifier <?= htmlspecialchars($u['prenom']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="user_id" value="<?= $u['id_utilisateur'] ?>">
                                <input type="hidden" name="role" value="<?= $u['role_utilisateur'] ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">Prénom</label>
                                    <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($u['prenom']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nom</label>
                                    <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($u['nom_utilisateur']) ?>" required>
                                </div>

                                <?php if($u['role_utilisateur'] == 'etudiant'): ?>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Année</label>
                                            <select name="annee" class="form-select">
                                                <option value="ING1" <?= $u['annee'] == 'ING1' ? 'selected' : '' ?>>ING1</option>
                                                <option value="ING2" <?= $u['annee'] == 'ING2' ? 'selected' : '' ?>>ING2</option>
                                                <option value="ING3" <?= $u['annee'] == 'ING3' ? 'selected' : '' ?>>ING3</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Filière</label>
                                            <input type="text" name="filiere" class="form-control" value="<?= htmlspecialchars($u['filiere']) ?>">
                                        </div>
                                    </div>
                                <?php elseif($u['role_utilisateur'] == 'entreprise'): ?>
                                    <div class="mb-3">
                                        <label class="form-label">Nom de l'entreprise</label>
                                        <input type="text" name="nom_entreprise" class="form-control" value="<?= htmlspecialchars($u['nom_entreprise']) ?>">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="update_user" class="btn btn-success">Sauvegarder</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for($i=1; $i<=$total_pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= $search ?>&limit=<?= $limit ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
