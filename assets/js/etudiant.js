/* J'ai mis ça pour tester mais faudra l'enlever quand vous aurez lié la base de données*/

const offres = [
  {
    titre: "Stage Développeur Front-End",
    entreprise: "TechNova",
    duree: "3 mois",
    domaine: "Développement web",
    lieu: "Paris",
    description: "Participation au développement d'interfaces web modernes en HTML, CSS et JavaScript.",
    etiquettes: ["HTML", "CSS", "JavaScript"]
  },
  {
    titre: "Stage Analyste Cybersécurité",
    entreprise: "SecureIT",
    duree: "6 mois",
    domaine: "Cybersécurité",
    lieu: "Lyon",
    description: "Analyse des vulnérabilités, tests de sécurité et rédaction de rapports d'audit.",
    etiquettes: ["Sécurité", "Audit", "Réseaux"]
  },
  {
    titre: "Stage Data Analyst",
    entreprise: "DataSense",
    duree: "4 mois",
    domaine: "Data",
    lieu: "Distanciel",
    description: "Nettoyage, visualisation et interprétation de données pour aider à la prise de décision.",
    etiquettes: ["Python", "SQL", "Power BI"]
  },
  {
    titre: "Stage Développeur IA",
    entreprise: "AI Labs",
    duree: "6 mois",
    domaine: "IA",
    lieu: "Autre",
    description: "Contribution à des modèles d'apprentissage automatique et au traitement de données.",
    etiquettes: ["Machine Learning", "Python", "IA"]
  },
  {
    titre: "Stage Administrateur Réseaux",
    entreprise: "NetGroup",
    duree: "2 mois",
    domaine: "Réseaux",
    lieu: "Toulouse",
    description: "Support à l'administration réseau, configuration d'équipements et supervision.",
    etiquettes: ["Cisco", "Réseaux", "Supervision"]
  }
];

const conteneurOffres = document.getElementById("conteneur-offres");
const compteurResultats = document.getElementById("compteur-resultats");
const selectionDuree = document.getElementById("duree");
const selectionDomaine = document.getElementById("domaine");
const selectionLieu = document.getElementById("lieu");
const champMotCle = document.getElementById("mot-cle");
const boutonRecherche = document.getElementById("bouton-recherche");
const boutonReinitialisation = document.getElementById("bouton-reinitialisation");

function afficherOffres(listeOffres) {
  conteneurOffres.innerHTML = "";

  if (listeOffres.length === 0) {
    conteneurOffres.innerHTML = `
      <div class="aucun-resultat">
        <p>Aucune offre ne correspond à votre recherche.</p>
      </div>
    `;
    compteurResultats.textContent = "0 offre";
    return;
  }

  compteurResultats.textContent = `${listeOffres.length} offre${listeOffres.length > 1 ? "s" : ""}`;

  listeOffres.forEach(offre => {
    const carte = document.createElement("article");
    carte.classList.add("carte-offre");

    carte.innerHTML = `
      <h4>${offre.titre}</h4>
      <p class="nom-entreprise">${offre.entreprise}</p>
      <p class="information-offre"><strong>Durée :</strong> ${offre.duree}</p>
      <p class="information-offre"><strong>Domaine :</strong> ${offre.domaine}</p>
      <p class="information-offre"><strong>Lieu :</strong> ${offre.lieu}</p>
      <p class="description-offre">${offre.description}</p>
      <div>
        ${offre.etiquettes.map(etiquette => `<span class="etiquette-offre">${etiquette}</span>`).join("")}
      </div>
      <a href="#" class="bouton-offre">Voir l'offre</a>
    `;

    conteneurOffres.appendChild(carte);
  });
}

function filtrerOffres() {
  const dureeChoisie = selectionDuree.value.toLowerCase();
  const domaineChoisi = selectionDomaine.value.toLowerCase();
  const lieuChoisi = selectionLieu.value.toLowerCase();
  const motCle = champMotCle.value.toLowerCase().trim();

  const offresFiltrees = offres.filter(offre => {
    const correspondDuree = !dureeChoisie || offre.duree.toLowerCase() === dureeChoisie;
    const correspondDomaine = !domaineChoisi || offre.domaine.toLowerCase() === domaineChoisi;
    const correspondLieu = !lieuChoisi || offre.lieu.toLowerCase() === lieuChoisi;

    const texteRecherche = `
      ${offre.titre}
      ${offre.entreprise}
      ${offre.description}
      ${offre.domaine}
      ${offre.lieu}
      ${offre.etiquettes.join(" ")}
    `.toLowerCase();

    const correspondMotCle = !motCle || texteRecherche.includes(motCle);

    return correspondDuree && correspondDomaine && correspondLieu && correspondMotCle;
  });

  afficherOffres(offresFiltrees);
}

function reinitialiserFiltres() {
  selectionDuree.value = "";
  selectionDomaine.value = "";
  selectionLieu.value = "";
  champMotCle.value = "";
  afficherOffres(offres);
}

boutonRecherche.addEventListener("click", filtrerOffres);
boutonReinitialisation.addEventListener("click", reinitialiserFiltres);

afficherOffres(offres);
