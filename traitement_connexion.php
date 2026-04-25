<?php
require_once 'db.php';
session_start();

$email_saisi = $_POST['email'];
$mdp_saisi = $_POST['motdepasse'];

// 1. On cherche l'utilisateur dans la BDD
$sql = "SELECT * FROM Utilisateur WHERE mail = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email_saisi]);
$user = $stmt->fetch();

if ($user) {
    // 2. On compare le MDP saisi avec le hachage de la BDD
    if (password_verify($mdp_saisi, $user['mot_de_passe'])) {
        // SUCCÈS : Le mot de passe est correct
        $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
        $_SESSION['role'] = $user['role'];
        
        // Log de l'action dans le fichier trace [cite: 79]
        logAction($user['id_utilisateur'], "Connexion réussie"); 

        echo json_encode(['success' => true]);
    } else {
        // ÉCHEC : Mauvais mot de passe
        echo json_encode(['success' => false, 'message' => 'Mot de passe incorrect']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Utilisateur inconnu']);
}