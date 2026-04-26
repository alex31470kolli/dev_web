<?php
require_once 'db.php';
session_start();
date_default_timezone_set('Europe/Paris');

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
    // 1. Générer un code à 6 chiffres
    $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiration = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // 2. Sauvegarder en BDD
    $stmt = $pdo->prepare("UPDATE Utilisateur SET a2f = ?, a2f_expire = ? WHERE id_utilisateur = ?");
    $stmt->execute([$code, $expiration, $user['id_utilisateur']]);

    // 3. Envoyer le mail (Simulation pour le projet local)
    // mail($user['mail'], "Votre code de connexion", "Code : $code");
    
    // Pour tes tests, on stocke l'ID en session temporaire sans le connecter
    $_SESSION['temp_user_id'] = $user['id_utilisateur'];

    echo json_encode([
        'success' => true, 
        'requires_2fa' => true // On prévient le JS qu'il faut aller à la page 2FA
    ]);
    } else {
            echo json_encode(['success' => false, 'message' => 'Mot de passe incorrect']);
        }
    } 
else {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
}
