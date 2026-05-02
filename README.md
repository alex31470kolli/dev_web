# Comment lancer le site web

/!\ Puisque le site n'est pas hébergé, tout doit être fait en utilisant le système d'exploitation Linux

## Partie 1 : Première connexion


### Méthode 1 : via les commandes du terminal
    
    Sur le github, branche main,
    copier le lien du github via le bouton :  <>code (celui sur fond vert)
    
    dans votre terminal :
    git clone lien
        
### Méthode 2 : Téléchargement du fichier
    Clisquez sur "Downlad zip" via le bouton : <>code

    décompressez votre fichier


ouvrez le fichier db.php (qui se trouve dans le répertoire dev_web) avec un éditeur de fichier/texte (ex : Visual Studio Code)
    Modifier les valeurs de $username et $password par les valeurs de vos identifiants mySQL

via votre terminal :

allez dans le fichier décompressé / cloné (il s'appelle dev_web)
connectez-vous à votre utilisateur mySQL 
      /* rappel de la commande : mysql -u nomUtilisateur -p */


faites la commande :

  source bdd_finale.sql
    
sortez du terminal sql (ou ouvrez un autre onglet du terminal)
    
faite la commande :
  php -S localhost:8080
cela ba bloquer les commandes terminal, ne faites pas de CTRL+C
  
dans chrome (jsp si les autres navigateurs fonctionnent) :
  localhost:8080/connexion.html

      S'il vous arrive des merdes pour cette étape, contactez Mathéo
      
## Partie 2 : Se rendre sur le site

    Voici comment accéder au site une fois le github téléchargé
    - Se placer dans le répertoire correspondant à ce qui vient d'être téléchargé ( nom= dev_web ) 
    - Lancer un serveur local , voici la commande à entrer dans le terminal linux :
        php -S localhost8080
    - L'adresse du site est : http://localhost:8000/connexion.html
    - Avant de se connecter, il faut lancer la base de donnéées 
    - Pour cela, 2 choix sont possibles : 
        modifier le fichier db.php aux lignes 5 et 6 et mettre vos identifiants et mots de passes 
        créer un utilisateur mysql nommé "Matheo" dont le mot de passe est "Mismagius#001"
    - Lancez mysql avec l'une des 2 méthodes expliquées précédemment
    - Activez la base de données "bdd_finale.sql" 
    
## Partie 3 : Connexion
    
  Voici les identifiants du premier admin créé : mailadmin@gmail.com / 1234  (adresse mail / mot de passe)
  
  Lorsque vous voulez vous connecter, vous aurez besoin d'un code (authentification à 2 facteurs).
  Cependant, puisque le serveur n'est pas encore hébergé, on ne peut pas envoyer de mail. C'est donc pour cela que j'écris cette partie.
  Pour obtenir le code, faites une requête SQL (dans votre terminal mySQL) permettant de récupérer le code avec votre compte. 
  Ex : 
  SELECT mail,a2f,a2f_expire FROM Utilisateur;
  Vous obtenez la liste des mails des utilisateurs, le code a2f de l'utilisateur qui tente de se connecter et le moment à partir duquel le code a2f n'est plus valable.

  Vous pouvez ainsi saisir le bon code pour vous connecter.
  
## Partie 4 : Test des fonctionnalités

  Tentez de créer des comptes admin/entreprise pour voir si on peut se conneter avec eux (et les accepter avec votre compte admin principal)
  (ou utiliser le fichier test_bd.sql avec la commande : source test_bd.sql dans le terminal mySQL)

  Créez un/plusieurs comptes étudiants (normalement 
  
  Testez les fonctionnalités admins présentes 
  Testez les fonctionnalités des comptes entreprises
  Testez les fonctionnalités des comptes étudiants

Écrit par Mathéo Catto
