const formulaireConnexion = document.getElementById("formulaire-connexion");
const messageErreur = document.getElementById("message-erreur");

formulaireConnexion.addEventListener("submit", function (evenement) {
  evenement.preventDefault();

  const courriel = document.getElementById("courriel").value;
  const motDePasse = document.getElementById("mot-de-passe").value;
  
/* J'ai mis ce qu'il y a en dessous pour tester mais faudra l'enlever quand vous aurez fait php*/

  if (courriel === "etudiant@test.com" && motDePasse === "1234") {
    alert("Connexion réussie !");
    window.location.href = "pageetudiant.html";
  } else {
    messageErreur.textContent = "Email ou mot de passe incorrect.";
  }
});
