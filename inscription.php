<?php 
// 1. On inclut la connexion à la BDD au tout début pour que le PHP plus bas fonctionne
require_once 'db.php'; 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>CY TECH stages - Inscription</title> 
    <link rel="stylesheet" href="/assets/css/css_all.css">
</head>
<body>

    <h1>Bienvenue sur le site de stages de CY TECH</h1>
    <p><b>Créez un compte !</b></p>

    <form id="form-inscription">
        <label>Nom</label> <input type="text" name="nom" required> <br>
        <label>Prénom</label> <input type="text" name="prenom" required> <br>
        <label>Email</label> <input type="email" name="email" required> <br>
        <label>Mot de passe</label> <input type="password" name="motdepasse" required> <br>
        
        <label>Choix du groupe</label> <br>
        <input type="radio" id="etudiant" name="role" value="etudiant"> <label for="etudiant">Étudiant</label><br>
        <input type="radio" id="entreprise" name="role" value="entreprise"> <label for="entreprise">Entreprise</label><br>
        <input type="radio" id="admin" name="role" value="admin"> <label for="admin">Administrateur</label>
        
        <div id="bloc-etudiant">
            <label>Année d'étude :</label>
            <select id="annee" name="annee">
                <option value="">-- Choisir --</option>
                <option value="ING1">ING 1</option>
                <option value="ING2">ING 2</option>
                <option value="ING3">ING 3</option>
            </select>

            <div id="bloc-filiere">
                <label>Spécialisation :</label>
                <select id="filiere" name="filiere"></select>
            </div>
        </div>

        <div id="bloc-entreprise">
            <label>Votre entreprise :</label>
            <select id="choix-entreprise" name="id_entreprise">
                <option value="nouvelle">-- Nouvelle entreprise (écrire ci-dessous) --</option>
                <?php
                // Récupération des entreprises existantes [cite: 11]
                $res = $pdo->query("SELECT id_entreprise, nom_entreprise FROM Entreprise");
                while($row = $res->fetch()) {
                    echo "<option value='".$row['id_entreprise']."'>".htmlspecialchars($row['nom_entreprise'])."</option>";
                }
                ?>
            </select>
            <br>
            <input type="text" id="nom-nouvelle-entreprise" name="nom_entreprise" placeholder="Nom de la nouvelle entreprise">
        </div>

        <br><br>
        <input type="submit" value="M'inscrire">
    </form>

    <script>
    const radios = document.getElementsByName('role');
    const blocEtudiant = document.getElementById('bloc-etudiant');
    const blocEntreprise = document.getElementById('bloc-entreprise');
    const selectAnnee = document.getElementById('annee');
    const selectFiliere = document.getElementById('filiere');
    const blocFiliere = document.getElementById('bloc-filiere');

    // 1. Gestion de l'affichage selon le rôle (Etudiant / Entreprise / Admin) [cite: 25]
    radios.forEach(r => {
        r.addEventListener('change', () => {
            blocEtudiant.style.display = (r.value === 'etudiant') ? 'block' : 'none';
            blocEntreprise.style.display = (r.value === 'entreprise') ? 'block' : 'none';
        });
    });

    // 2. Logique dynamique des filières (ING1, ING2, ING3) [cite: 12, 14]
    selectAnnee.addEventListener('change', function() {
        selectFiliere.innerHTML = '';
        let options = [];
        if(this.value === 'ING1' || this.value === 'ING2') options = ['GM - Finance', 'GM - Data', 'GI'];
        if(this.value === 'ING3') options = ['HPDA', 'Cybersécurité', 'IA', 'Cloud Computing'];
        
        if(options.length > 0) {
            blocFiliere.style.display = 'block';
            options.forEach(opt => {
                let o = document.createElement('option');
                o.value = opt;
                o.textContent = opt;
                selectFiliere.appendChild(o);
            });
        } else { 
            blocFiliere.style.display = 'none'; 
        }
    });

    // 3. Envoi des données via Fetch (AJAX) [cite: 26, 27]
    document.getElementById('form-inscription').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);

        try {
            const response = await fetch('traitement_inscription.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                alert("Inscription réussie !");
                // On n'affiche l'alerte que si le compte n'est pas encore valide
                if (result.est_valide === 0) {
                    alert("Votre compte doit être validé par un administrateur.");
                }
                window.location.href = "connexion.html";
            } else {
                alert("Erreur : " + result.message);
            }
        } catch (error) {
            alert("Erreur de connexion au serveur.");
        }
    });
    </script>
</body>
</html>
