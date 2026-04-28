<?php
session_start();
require_once '../db.php';

// --- SÉCURITÉ ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'etudiant') {
    header('Location: ../connexion.html'); exit();
}

$mon_id = $_SESSION['id_utilisateur'];

// On récupère la classe de l'étudiant
$ma_classe = $_SESSION['annee'] ?? 'TOUS';

// On trie d'abord par mise en avant (1 avant 0), puis par ID
$sql = "SELECT o.*, e.nom_entreprise 
        FROM Offre o 
        JOIN Entreprise e ON o.id_entreprise = e.id_entreprise 
        WHERE o.id_offre NOT IN (SELECT id_offre FROM Candidature WHERE statut = 1)
        AND o.date_fin >= CURRENT_DATE() 
        ORDER BY 
            (o.mis_en_avant = 1 AND (o.cible_mise_en_avant = ? OR o.cible_mise_en_avant = 'TOUS')) DESC, 
            o.id_offre DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$ma_classe]);
$offres = $stmt->fetchAll();

// Recherche de convention à signer
$stmtSign = $pdo->prepare("SELECT s.*, o.titre, s.chemin_convention FROM Stage s JOIN Offre o ON s.id_offre = o.id_offre 
                           WHERE s.id_etudiant = ? AND s.etat_suivi = 'Signature Étudiant'");
$stmtSign->execute([$mon_id]);
$a_signer = $stmtSign->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Étudiant - CY Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-0 shadow-sm"> <div class="container">
        <a class="navbar-brand" href="#">🎓 CY Tech Étudiant</a>
        <div class="ms-auto">
            <a href="mes_documents.php" class="btn btn-light btn-sm me-2">📁 Mes Documents</a>
            <a href="../pages_communes/messagerie.php" class="btn btn-outline-light btn-sm me-2">✉️ Messagerie</a>
            <a href="../pages_communes/profil.php" class="btn btn-secondary btn-sm me-2">👤 Mon Profil</a>
            <a href="../deconnexion.php" class="btn btn-danger btn-sm">Déconnexion</a>
        </div>
    </div>
</nav>

<?php if($a_signer): ?>
    <div class="alert alert-warning border-0 rounded-0 m-0 shadow-sm py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <strong>🖋️ Action Requise :</strong> Vous avez une convention à signer pour : 
                <span class="badge bg-dark ms-2"><?= htmlspecialchars($a_signer['titre']) ?></span>
            </div>
            <div class="d-flex gap-2">
                <a href="../documents/<?= $a_signer['chemin_convention'] ?? '#' ?>" target="_blank" class="btn btn-sm btn-outline-dark">👁️ Consulter</a>
                <a href="../pages_communes/signer_convention.php?id=<?= $a_signer['id_stage'] ?>" class="btn btn-sm btn-dark">Signer le document</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="container mt-5">
    <h2 class="mb-4">Offres de stage disponibles</h2>
    <div class="row">
        <?php foreach ($offres as $o): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <span class="badge bg-dark mb-2"><?= htmlspecialchars($o['nom_entreprise']) ?></span>
                    <h5 class="card-title text-primary"><?= htmlspecialchars($o['titre']) ?></h5>
                    <p class="card-text text-truncate"><?= htmlspecialchars($o['missions']) ?></p>
                </div>
                <div class="card-footer bg-white border-0">
                  <a href="postuler.php?id_offre=<?= $o['id_offre'] ?>" class="btn btn-primary w-100">✉️ Rédiger ma candidature</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
