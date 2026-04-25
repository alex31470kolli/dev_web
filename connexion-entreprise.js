const formulaireConnexionEntreprise = document.getElementById("formulaire-connexion-entreprise");
const messageErreurEntreprise = document.getElementById("message-erreur-entreprise");

formulaireConnexionEntreprise.addEventListener("submit", function (evenement) {
  evenement.preventDefault();

  const courrielEntreprise = document.getElementById("courriel-entreprise").value;
  const motDePasseEntreprise = document.getElementById("mot-de-passe-entreprise").value;
  
 /* a dégager quand le php sera fait*/

  if (courrielEntreprise === "entreprise@test.com" && motDePasseEntreprise === "1234") {
    alert("Connexion entreprise réussie !");
    window.location.href = "entreprise.html";
  } else {
    messageErreurEntreprise.textContent = "Email ou mot de passe incorrect.";
  }
});
