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
    <link rel="stylesheet" href="/assets/css/publier-offre.css">
    <style>
        .container { max-width: 800px; margin: 20px auto; padding: 20px; font-family: sans-serif; }
        .form-group { margin-bottom: 15px; display: flex; flex-direction: column; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        label { font-weight: bold; margin-bottom: 5px; }
        input, select, textarea { padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 20px; background: #1a365d; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🚀 Déposer une nouvelle offre de stage</h2>
        <form action="traitement_offre.php" method="POST">
            <div class="form-group">
                <label>Titre de l'offre</label>
                <input type="text" name="titre" required placeholder="Ex: Développeur Fullstack">
            </div>

            <div class="grid">
                <div class="form-group">
                    <label>Niveau requis</label>
                    <select name="annee" id="annee" onchange="actualiserFilieres()" required>
                        <option value="">-- Choisir --</option>
                        <option value="ING1">ING1</option>
                        <option value="ING2">ING2</option>
                        <option value="ING3">ING3</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Spécialité / Filière</label>
                    <select name="filiere" id="filiere" required>
                        <option value="">Sélectionnez un niveau</option>
                    </select>
                </div>
            </div>

            <div class="grid">
                <div class="form-group">
                    <label>Date de début</label>
                    <input type="date" name="date_debut" required>
                </div>
                <div class="form-group">
                    <label>Date de fin</label>
                    <input type="date" name="date_fin" required>
                </div>
            </div>

            <div class="form-group">
                <label>Lieu</label>
                <input type="text" name="lieu" required placeholder="Ex: Cergy (95) ou Télétravail">
            </div>

            <div class="form-group">
                <label>Missions du stage</label>
                <textarea name="missions" rows="5" required placeholder="Décrivez les tâches confiées..."></textarea>
            </div>

            <div class="form-group">
                <label>Compétences techniques requises</label>
                <input type="text" name="competences" required placeholder="Ex: PHP, SQL, React...">
            </div>

            <button type="submit">Publier l'offre</button>
        </form>
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
