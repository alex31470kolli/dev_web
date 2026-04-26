<?php
session_start();
date_default_timezone_set('Europe/Paris');
// 1. On vide toutes les variables de session
$_SESSION = array();

// 2. On détruit la session sur le serveur
session_destroy();

// 3. Optionnel : On détruit le cookie de session dans le navigateur (pour une sécurité maximale)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. On redirige vers la page de connexion
header("Location: connexion.html");
exit();
?>
