<?php
require_once 'db.php';
session_start();

$email_saisi = $_POST['email'];
$mdp_saisi = $_POST['motdepasse'];

$sql = "SELECT * FROM Utilisateur WHERE mail = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email_saisi]);
$user = $stmt->fetch();

if ($user) {
    if ($user['est_valide'] == 0) {
        echo json_encode(['success' => false, 'message' => "Votre compte est en attente de validation par un administrateur."]);
        exit();
    }

    if (password_verify($mdp_saisi, $user['mot_de_passe'])) {
        $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
        $_SESSION['role'] = $user['role_utilisateur']; 
        $_SESSION['prenom'] = $user['prenom'];

        logAction($user['id_utilisateur'], "Connexion réussie"); 

        echo json_encode([
            'success' => true, 
            'role' => $user['role_utilisateur']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Mot de passe incorrect']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
}
