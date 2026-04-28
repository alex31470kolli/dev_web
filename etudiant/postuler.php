<?php
session_start();
require_once '../db.php'; // Ajustez le chemin vers db.php si besoin

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'etudiant') {
    header('Location: ../connexion.html'); 
    exit();
}

// On vérifie qu'on a bien reçu un ID d'offre dans l'URL
if (!isset($_GET['id_offre'])) {
    die("Aucune offre sélectionnée.");
}

$id_offre = intval($_GET['id_offre']);

// On récupère les détails de l'offre et de l'entreprise
$sql = "SELECT o.titre, o.id_entreprise, e.nom_entreprise, e.id_referent 
        FROM Offre o 
        JOIN Entreprise e ON o.id_entreprise = e.id_entreprise 
        WHERE o.id_offre = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_offre]);
$offre = $stmt->fetch();


// Vérifier si l'étudiant a déjà postulé à CETTE offre
$stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM Candidature WHERE id_utilisateur = ? AND id_offre = ?");
$stmtCheck->execute([$_SESSION['id_utilisateur'], $id_offre]);
$deja_postule = $stmtCheck->fetchColumn();

// Si oui, on affiche un message et on arrête le script (ou on cache le formulaire)
if ($deja_postule > 0) {
    echo "
    <div class='container mt-5'>
        <div class='alert alert-warning text-center shadow-sm'>
            <h4>🚩 Action impossible</h4>
            <p>Vous avez déjà envoyé une candidature pour l'offre : <strong>" . htmlspecialchars($offre['titre']) . "</strong>.</p>
            <hr>
            <a href='etudiant_home.php' class='btn btn-primary'>Retour à l'accueil</a>
        </div>
    </div>";
    exit(); // On arrête l'affichage du reste de la page
}


if (!$offre) {
    die("Cette offre n'existe plus.");
}

// Récupérer les documents de l'étudiant
$stmtDocs = $pdo->prepare("SELECT * FROM Document WHERE id_possesseur = ?");
$stmtDocs->execute([$_SESSION['id_utilisateur']]);
$mes_docs = $stmtDocs->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Postuler - CY Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Candidature pour : <?= htmlspecialchars($offre['nom_entreprise']) ?></h2>
                <a href="etudiant_home.php" class="btn btn-outline-secondary">Annuler</a>
            </div>

            <div class="card shadow-sm border-primary mb-5">
                <div class="card-header bg-primary text-white">
                    <strong>Nouveau Message</strong>
                </div>
                <div class="card-body">
                    <form action="traitement_candidature.php" method="POST" enctype="multipart/form-data">
                        
                        <input type="hidden" name="id_destinataire" value="<?= $offre['id_referent'] ?>">
                        <input type="hidden" name="sujet" value="Candidature : <?= htmlspecialchars($offre['titre']) ?>">
                        <input type="hidden" name="id_offre" value="<?= $id_offre ?>">
                        <input type="hidden" name="id_entreprise" value="<?= $offre['id_entreprise'] ?>">

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label text-muted">À :</label>
                            <div class="col-sm-10">
                                <input type="text" readonly class="form-control-plaintext fw-bold" value="Service Recrutement - <?= htmlspecialchars($offre['nom_entreprise']) ?>">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label text-muted">Objet :</label>
                            <div class="col-sm-10">
                                <input type="text" readonly class="form-control-plaintext fw-bold" value="Candidature : <?= htmlspecialchars($offre['titre']) ?>">
                            </div>
                        </div>
                        
                        <hr>

                        <div class="mb-3">
                            <textarea name="contenu" class="form-control" rows="8" placeholder="Rédigez votre message de motivation ici..." required></textarea>
                        </div>

                        <div class="mb-4 p-4 bg-light rounded border">
                            <label class="form-label fw-bold mb-3">📎 Pièce jointe (CV ou Lettre)</label>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="choix_fichier" id="choixExistant" value="existant" checked>
                                <label class="form-check-label fw-bold" for="choixExistant">
                                    Choisir dans mon porte-documents
                                </label>
                                <select class="form-select mt-2" name="doc_existant" id="menuDocExistant">
                                    <option value="">-- Sélectionnez un document --</option>
                                    <?php foreach($mes_docs as $doc): ?>
                                        <option value="<?= htmlspecialchars($doc['chemin_fichier']) ?>">
                                            <?= htmlspecialchars($doc['nom_doc']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <?php if(empty($mes_docs)): ?>
                                        <option value="" disabled>Aucun document disponible (Allez dans 'Mes Documents')</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <hr>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="choix_fichier" id="choixNouveau" value="nouveau">
                                <label class="form-check-label fw-bold" for="choixNouveau">
                                    Téléverser un nouveau fichier depuis mon appareil
                                </label>
                                <input class="form-control mt-2" type="file" name="pj_nouvelle" id="inputNouveauDoc" disabled>
                                <small class="text-muted">Max 5Mo. Le fichier ne sera pas sauvegardé dans votre porte-documents.</small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success px-4">
                                🚀 Envoyer ma candidature
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    const radioExistant = document.getElementById('choixExistant');
    const radioNouveau = document.getElementById('choixNouveau');
    const menuExistant = document.getElementById('menuDocExistant');
    const inputNouveau = document.getElementById('inputNouveauDoc');

    radioExistant.addEventListener('change', () => {
        menuExistant.disabled = false;
        inputNouveau.disabled = true;
    });

    radioNouveau.addEventListener('change', () => {
        menuExistant.disabled = true;
        inputNouveau.disabled = false;
    });
</script>

</body>
</html>
