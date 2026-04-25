const formulaireConnexion = document.getElementById("formulaire-connexion");
const messageErreur = document.getElementById("message-erreur");

formulaireConnexion.addEventListener("submit", async function (evenement) {
  evenement.preventDefault();

  const formData = new FormData(formulaireConnexion);

  try {
    const response = await fetch('traitement_connexion.php', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.success) {
        // Redirection vers l'accueil étudiant (en PHP désormais)
        window.location.href = "pageetudiant.php"; 
    } else {
        messageErreur.textContent = result.message;
    }
  } catch (error) {
    messageErreur.textContent = "Erreur de connexion au serveur.";
  }
});
