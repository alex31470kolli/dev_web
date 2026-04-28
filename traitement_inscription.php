<?php
require_once 'db.php';
header('Content-Type: application/json');

try {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $role = strtolower($_POST['role']);
    $mdp = password_hash($_POST['motdepasse'], PASSWORD_DEFAULT);

    // --- NOUVELLE LOGIQUE DE VALIDATION AUTOMATIQUE ---
    
    // 1. On compte combien d'utilisateurs existent déjà dans la base
    $checkEmpty = $pdo->query("SELECT COUNT(*) FROM Utilisateur");
    $nbUsers = $checkEmpty->fetchColumn();

    if ($nbUsers == 0 && $role === 'admin') {
        // Si la table est vide et que c'est un admin qui s'inscrit, il est valide d'office
        $est_valide = 1;
    } else {
        // Sinon, on garde ta logique habituelle
        $est_valide = ($role === 'etudiant') ? 1 : 0;
    }
    // --------------------------------------------------

    $sql = "INSERT INTO Utilisateur (prenom, nom_utilisateur, mail, mot_de_passe, role_utilisateur, annee, filiere, est_valide) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $prenom, 
        $nom, 
        $email, 
        $mdp, 
        $role, 
        $annee = ($role === 'etudiant' && !empty($_POST['annee'])) ? $_POST['annee'] : null,
        $filiere = ($role === 'etudiant' && !empty($_POST['filiere'])) ? $_POST['filiere'] : null,
        $est_valide
    ]);

    $id_nouvel_utilisateur = $pdo->lastInsertId();

    // AJOUT DU LOG D'INSCRIPTION
    $log = $pdo->prepare("INSERT INTO Trace (id_utilisateur, acte) VALUES (?, ?)");
    $log->execute([$id_nouvel_utilisateur, "Nouvel utilisateur inscrit (Rôle: " . strtoupper($role) . ")"]);

    if ($role === 'entreprise' && !empty($_POST['nom_entreprise'])) {
        $nom_ent = $_POST['nom_entreprise'];
        
        // On crée l'entreprise en lui assignant l'utilisateur comme référent
        $sqlEnt = "INSERT INTO Entreprise (nom_entreprise, id_referent) VALUES (?, ?)";
        $stmtEnt = $pdo->prepare($sqlEnt);
        $stmtEnt ->execute([$nom_ent, $id_nouvel_utilisateur]);
    }

    echo json_encode(['success' => true, 'est_valide' => $est_valide]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
