# Comment utiliser le github

copier le lien du github via le bouton <>code

## Partie 1 : Première connexion
  dans votre terminal :
    git clone lien
    
    /* vérifier les droits du fichiers et modier en conséquence */
    
    faites la commande :
    cd dev_web
    
    connectez-vous à voter utilisateur mySQL 
      /* rappel : mysql -u nomUtilisateur -p */
    
    faites la commande :
    source bdd_finale.sql
    
    sortez du terminal sql
    
    faite la commande :
    php -S localhost:8080
    
    cela ba bloquer les commandes terminal, ne faites pas de CTRL+C
  
  dans chrome (jsp si les autres navigateurs fonctionnent) :
  localhost:8080/inscription.php

  /!\ C'est important pour les entreprises/admin qu'il y ait un compte administrateur pour leur accorder la connexion.
  créez un compte test en tant qu'admin (n'oubliez pas mail/mot de passe)

  Si il vous arrive des merdes pour cette étape, contactez Mathéo
  
## Partie 2 : Test des fonctionnalités

  Tentez de créer des comptes admin/entreprise pour voir si on peut se conneter avec eux (et les accepter avec votre compte admin principal)

  Créez un/plusieurs comptes étudiants (normalement 
  
  Testez les fonctionnalités admins présentes (contactez Mathéo si vous ne comprenez pas comment ça marche / si vous rencontrez des bugs)
  Testez les fonctionnalités des comptes entreprises (contactez Lionel si elles buggent)
  Testez les fonctionnalités des comptes étudiants


Écrit par Mathéo
