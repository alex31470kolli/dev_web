<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'entreprise') {
    header('Location: connexion-entreprise.html');
    exit();
}
require_once '../db.php';
?>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Page entreprise - consultation et publication d'offres de stage" />
  <title>Espace Entreprise - Offres de stage</title>
  <link rel="stylesheet" href="/assets/css/entreprise.css" />
</head>
<body>
  <header class="barre-haut">
    <div class="zone-logo">
      <img src="CYTech.jpg" alt="Logo de l'école" class="logo-ecole" />
      <div>
        <h1>Espace Entreprise</h1>
        <p class="sous-titre">Publier et consulter les offres de stage</p>
      </div>
    </div>

    <nav class="actions-haut">
      <a href="connexion-entreprise.html" class="bouton-navigation">Se connecter</a>
      <a href="publier-offre.php" class="bouton-navigation">Publier une offre</a>
      <a href="#" class="bouton-navigation">Mes offres</a>
    </nav>
  </header>

  <main class="conteneur-principal">
    <section class="zone-presentation">
      <div>
        <h2>Diffusez vos opportunités de stage</h2>
        <p>
          Déposez une offre de stage, consultez les offres déjà publiées et facilitez
          la mise en relation avec les étudiants.
        </p>
      </div>
    </section>

    <section class="zone-filtres">
      <h3>Rechercher une offre publiée</h3>

      <div class="grille-filtres">
        <div class="groupe-filtre">
          <label for="duree">Durée</label>
          <select id="duree">
            <option value="">Toutes les durées</option>
            <option value="2 mois">2 mois</option>
            <option value="3 mois">3 mois</option>
            <option value="4 mois">4 mois</option>
            <option value="6 mois">6 mois</option>
          </select>
        </div>

        <div class="groupe-filtre">
          <label for="domaine">Domaine</label>
          <select id="domaine">
            <option value="">Tous les domaines</option>
            <option value="Développement web">Développement web</option>
            <option value="Cybersécurité">Cybersécurité</option>
            <option value="Data">Data</option>
            <option value="Réseaux">Réseaux</option>
            <option value="IA">IA</option>
          </select>
        </div>

        <div class="groupe-filtre">
          <label for="lieu">Lieu</label>
          <select id="lieu">
            <option value="">Tous les lieux</option>
            <option value="Paris">Paris</option>
            <option value="Lyon">Lyon</option>
            <option value="Toulouse">Toulouse</option>
            <option value="Distanciel">Distanciel</option>
            <option value="Hybride">Hybride</option>
          </select>
        </div>

        <div class="groupe-filtre">
          <label for="mot-cle">Mot-clé</label>
          <input type="text" id="mot-cle" placeholder="Ex : Java, cybersécurité, réseau..." />
        </div>
      </div>

      <div class="zone-boutons-filtres">
        <button id="bouton-recherche" class="bouton-principal">Rechercher</button>
        <button id="bouton-reinitialisation" class="bouton-secondaire">Réinitialiser</button>
      </div>
    </section>

    <section class="zone-resultats">
      <div class="entete-resultats">
        <h3>Offres publiées</h3>
        <span id="compteur-resultats">0 offre</span>
      </div>

      <div id="conteneur-offres" class="grille-offres"></div>
    </section>
  </main>

  <footer class="pied-page">
    <p>© 2026 - Plateforme de suivi et d’archivage des stages</p>
  </footer>

  <script src="/assets/js/entreprise.js"></script>
</body>
</html>
