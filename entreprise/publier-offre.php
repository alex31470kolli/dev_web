<?php
session_start();
// Vérification de la session entreprise
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'entreprise') {
    header('Location: connexion.html');
    exit();
}

// Inclusion de la base de données (le fichier est au même niveau ou adapter le chemin)
require_once '../db.php'; 

// Récupération des filières pour le menu dynamique
try {
    $query = $pdo->query("SELECT nom_filiere, niveau FROM Filiere");
    $toutes_filieres = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

$filieres_par_niveau = ['ING1' => [], 'ING2' => [], 'ING3' => []];
foreach ($toutes_filieres as $f) {
    $filieres_par_niveau[$f['niveau']][] = $f['nom_filiere'];
}

$id_user = $_SESSION['id_utilisateur'];

// 1. Récupérer d'abord l'id_entreprise
$stmtEnt = $pdo->prepare("SELECT id_entreprise FROM Entreprise WHERE id_referent = ?");
$stmtEnt->execute([$id_user]);
$ma_boite = $stmtEnt->fetch();

// 2. Récupérer uniquement les offres de cette entreprise
if ($ma_boite) {
    $query = $pdo->prepare("SELECT * FROM Offre WHERE id_entreprise = ? ORDER BY id_offre DESC");
    $query->execute([$ma_boite['id_entreprise']]);
    $mes_offres = $query->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Publier une offre - CY Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    
    <div class="container mt-4 mb-5">
        <a href="entreprise_home.php" class="btn btn-outline-secondary mb-4">← Retour à l'accueil</a>
        
        <div class="card shadow-sm mx-auto" style="max-width: 800px;">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">🚀 Déposer une nouvelle offre de stage</h3>
            </div>
            <div class="card-body">
                <form action="traitement_offre.php" method="POST">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Titre de l'offre</label>
                        <input type="text" class="form-control" name="titre" required placeholder="Ex: Développeur Fullstack">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Niveau requis</label>
                            <select class="form-select" name="annee" id="annee" onchange="actualiserFilieres()" required>
                                <option value="">-- Choisir --</option>
                                <option value="ING1">ING1</option>
                                <option value="ING2">ING2</option>
                                <option value="ING3">ING3</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Spécialité / Filière</label>
                            <select class="form-select" name="filiere" id="filiere" required>
                                <option value="">Sélectionnez un niveau</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date de début</label>
                            <input type="date" class="form-control" name="date_debut" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date de fin</label>
                            <input type="date" class="form-control" name="date_fin" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Lieu</label>
                        <input type="text" class="form-control" name="lieu" required placeholder="Ex: Cergy (95) ou Télétravail">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Missions du stage</label>
                        <textarea class="form-control" name="missions" rows="4" required placeholder="Décrivez les tâches confiées..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Compétences - mots clés</label>
                        <input type="text" class="form-control" name="competences" required placeholder="Ex: PHP, SQL, React...">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Publier l'offre</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        const filieresData = <?= json_encode($filieres_par_niveau); ?>;
        function actualiserFilieres() {
            const niveau = document.getElementById('annee').value;
            const select = document.getElementById('filiere');
            select.innerHTML = '<option value="Tous">Toutes les filières</option>';
            if (niveau && filieresData[niveau]) {
                filieresData[niveau].forEach(nom => {
                    const opt = document.createElement('option');
                    opt.value = nom;
                    opt.textContent = nom;
                    select.appendChild(opt);
                });
            }
        }
    </script>
</body>
</html>
