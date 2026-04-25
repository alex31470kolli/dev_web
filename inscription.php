<?php
require_once 'db.php'; // On appelle la connexion

// Imaginons que les données viennent d'un formulaire HTML
$prenom = "Jean";
$nom = "Dupont";
$email = "jean.dupont@cytech.fr";
$mdp_clair = "MonMotDePasseSecu123";
$role = "etudiant";

// 1. Hachage du mot de passe (Obligatoire pour la sécurité)
$mdp_hash = password_hash($mdp_clair, PASSWORD_DEFAULT);

// 2. Préparation de la requête (Protection contre injections SQL)
$sql = "INSERT INTO Utilisateur (prenom, nom_utilisateur, mail, mot_de_passe, role) 
        VALUES (:prenom, :nom, :mail, :mdp, :role)";
$stmt = $pdo->prepare($sql);

// 3. Exécution avec les valeurs
$stmt->execute([
    'prenom' => $prenom,
    'nom'    => $nom,
    'mail'   => $email,
    'mdp'    => $mdp_hash,
    'role'   => $role
]);

echo "Utilisateur enregistré avec succès !";
?>
