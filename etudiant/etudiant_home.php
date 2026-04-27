<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'etudiant') {
    header('Location: ../connexion.html'); exit();
}

$mon_id = $_SESSION['id_utilisateur'];

// On sélectionne les offres non pourvues ET dont la date de fin est supérieure ou égale à aujourd'hui
$sql = "SELECT o.*, e.nom_entreprise 
        FROM Offre o 
        JOIN Entreprise e ON o.id_entreprise = e.id_entreprise 
        WHERE o.id_offre NOT IN (
            SELECT id_offre FROM Candidature WHERE statut = 1
        )
        AND o.date_fin >= CURRENT_DATE() 
        ORDER BY o.id_offre DESC";
$offres = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Étudiant - CY Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="#">🎓 CY Tech Étudiant</a>
        <div class="ms-auto">
            <a href="mes_documents.php" class="btn btn-light btn-sm me-2">📁 Mes Documents</a>
            <a href="../pages_communes/messagerie.php" class="btn btn-outline-light btn-sm me-2">✉️ Messagerie</a>
            <a href="../pages_communes/profil.php" class="btn btn-secondary btn-sm me-2">👤 Mon Profil</a>
            <a href="../deconnexion.php" class="btn btn-danger btn-sm">Déconnexion</a>
        </div>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4">Offres de stage disponibles</h2>
    
    <div class="row">
        <?php foreach ($offres as $o): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <span class="badge bg-dark mb-2"><?= htmlspecialchars($o['nom_entreprise']) ?></span>
                    <h5 class="card-title text-primary"><?= htmlspecialchars($o['titre']) ?></h5>
                    <p class="card-text small text-muted"><?= htmlspecialchars($o['filiere']) ?> - <?= htmlspecialchars($o['lieu']) ?></p>
                    <p class="card-text text-truncate"><?= htmlspecialchars($o['missions']) ?></p>
                </div>
                <div class="card-footer bg-white border-0">
                  <a href="postuler.php?id_offre=<?= $o['id_offre'] ?>" class="btn btn-primary w-100">
                      ✉️ Rédiger ma candidature
                  </a>
              </div>
            </div>
        </div>

        <div class="modal fade" id="modalPostuler<?= $o['id_offre'] ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="traitement_candidature.php" method="POST" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title">Candidater chez <?= htmlspecialchars($o['nom_entreprise']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id_destinataire" value="<?= $o['id_referent'] ?>">
                            <input type="hidden" name="sujet" value="Candidature : <?= htmlspecialchars($o['titre']) ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Votre message de motivation</label>
                                <textarea name="contenu" class="form-control" rows="4" required placeholder="Bonjour, je suis très intéressé par votre offre..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Joindre un fichier (CV/LM)</label>
                                <input type="file" name="pj" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Envoyer ma candidature</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
