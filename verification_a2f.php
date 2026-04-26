<?php
session_start();
require_once 'db.php';
date_default_timezone_set('Europe/Paris');

// Initialisation de la variable pour éviter l'erreur "Undefined variable"
$erreur = "";

// Sécurité : si on arrive ici sans être passé par la page de connexion
if (!isset($_SESSION['temp_user_id'])) {
    header("Location: connexion.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code_saisi = $_POST['code'];
    $user_id = $_SESSION['temp_user_id'];

    // Vérification avec les colonnes exactes de votre SQL
    $stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE id_utilisateur = ? AND a2f = ? AND a2f_expire > NOW()");
    $stmt->execute([$user_id, $code_saisi]);
    $user = $stmt->fetch();

    if ($user) {
        // Initialisation de la session officielle
        $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
        $_SESSION['role'] = $user['role_utilisateur'];
        $_SESSION['prenom'] = $user['prenom'];
        
        // Nettoyage du code utilisé
        $pdo->prepare("UPDATE Utilisateur SET a2f = NULL, a2f_expire = NULL WHERE id_utilisateur = ?")->execute([$user_id]);
        unset($_SESSION['temp_user_id']);

        // Redirections selon le rôle
        if ($user['role_utilisateur'] === 'admin') {
            header("Location: admin/admin_home.php");
        } elseif ($user['role_utilisateur'] === 'entreprise') {
            header("Location: entreprise/entreprise_home.php");
        } else {
            header("Location: etudiant_home.php");
        }
        exit(); 
    } else {
        $erreur = "Code incorrect, expiré ou déjà utilisé.";
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification A2F - CY Tech</title>
    <link rel="stylesheet" href="/assets/css/css_all.css"> <style>
        .container-a2f { max-width: 400px; margin: 100px auto; text-align: center; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #fff; }
        .code-input { font-size: 24px; letter-spacing: 5px; text-align: center; width: 80%; padding: 10px; margin: 20px 0; }
        .error-msg { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container-a2f">
        <h1>🔐 Double Authentification</h1>
        <p>Un code de vérification vous a été envoyé par mail (simulé en BDD).</p>
        
        <?php if ($erreur): ?>
            <p class="error-msg"><?php echo $erreur; ?></p>
        <?php endif; ?>

        <form method="POST" action="verification_a2f.php">
            <label for="code">Entrez le code à 6 chiffres :</label><br>
            <input type="text" name="code" id="code" class="code-input" maxlength="6" required placeholder="000000">
            <br>
            <input type="submit" value="Vérifier le code">
        </form>
        
        <br>
        <a href="connexion.html">Retour à la connexion</a>
    </div>
</body>
</html>
