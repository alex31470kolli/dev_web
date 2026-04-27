# Notice d'utilisation de ce github

## Partie 1 : Première connexion

### Méthode 1 : via les commandes du terminal

    copier le lien du github via le bouton :  <>code
    
    dans votre terminal :
    git clone lien
    
    /* vérifier les droits du fichiers et modier en conséquence */
    
    faites la commande :
    cd dev_web
    
    connectez-vous à voter utilisateur mySQL 
      /* rappel : mysql -u nomUtilisateur -p */


### Méthode 2 : Téléchargement du fichier
    Clisquez sur "Downlad zip" via le bouton : <>code

    dézipper votre fichier

via votre terminal :

allez dans le fichier dézippé

faites la commande :
  source bdd_finale.sql
    
  sortez du terminal sql (ou ouvrez un autre onglet du terminal)
    
  faite la commande :
  php -S localhost:8080
  cela ba bloquer les commandes terminal, ne faites pas de CTRL+C
  
  dans chrome (jsp si les autres navigateurs fonctionnent) :
  localhost:8080/inscription.php


  Si il vous arrive des merdes pour cette étape, contactez Mathéo

## Partie 2 : Connexion
  Lorsque vous voulez vous connecter, vous aurez besoin d'un code (authentification à 2 facteurs).
  Cependant, puisque le serveur n'est pas encore hébergé, on ne peut pas envoyer de mail. C'est donc pour cela que j'écris cette partie.
  Pour obtenir le code, faites une requête SQL permettant de récupérer le code avec votre compte. 
  Ex : 
  SELECT mail,a2f,a2f_expire FROM Utilisateur WHERE mail='exemplemail@gmail.com';
  Vous ne devez obtenir qu'une seule réponse avec votre code et la date à laquelle il expire.

  Vous pouvez ainsi saisir le bon code pour vous connecter.

  (je sais que c'est énervant, mais c'est la seule manière exploitable facilement que j'ai trouvée qui me permette d'utiliser l'A2f)
  
## Partie 3 : Test des fonctionnalités

  Tentez de créer des comptes admin/entreprise pour voir si on peut se conneter avec eux (et les accepter avec votre compte admin principal)

  Créez un/plusieurs comptes étudiants (normalement 
  
  Testez les fonctionnalités admins présentes (contactez Mathéo si vous ne comprenez pas comment ça marche / si vous rencontrez des bugs)
  Testez les fonctionnalités des comptes entreprises (contactez Lionel si elles buggent)
  Testez les fonctionnalités des comptes étudiants

## Partie 4 : Quand il y a eu des changements

  Tout reprendre depuis l'étape 1
  (ne pas oublier de recloner le git dans votre pc: git clone lien dans votre terminal)


Écrit par Mathéo
