const formulaireOffre = document.getElementById("formulaire-offre");
const messagePublication = document.getElementById("message-publication");

formulaireOffre.addEventListener("submit", function (evenement) {
  evenement.preventDefault();

  const titreOffre = document.getElementById("titre-offre").value;
  const nomEntreprise = document.getElementById("nom-entreprise").value;
  const dureeOffre = document.getElementById("duree-offre").value;
  const domaineOffre = document.getElementById("domaine-offre").value;
  const lieuOffre = document.getElementById("lieu-offre").value;
  const competencesOffre = document.getElementById("competences-offre").value;
  const descriptionOffre = document.getElementById("description-offre").value;

  if (
    titreOffre &&
    nomEntreprise &&
    dureeOffre &&
    domaineOffre &&
    lieuOffre &&
    competencesOffre &&
    descriptionOffre
  ) {
    messagePublication.textContent = "Votre offre a bien été publiée.";
    formulaireOffre.reset();
  } else {
    messagePublication.textContent = "Veuillez remplir tous les champs.";
  }
});
