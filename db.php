<?php

$host = '127.0.0.1'; // Correction de l'hôte
$dbname = 'siteStage';
$username = 'Matheo'; 
$password = 'Mismagius#001'; 

try {
    // Suppression du port 8080 ici, PDO se connecte au port SQL (3306) par défaut
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Test visuel (à supprimer une fois que ça marche)
    // echo "Connexion réussie !"; 
    
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
// Enregistrement des actions dans la base de données 

function logAction($id_utilisateur, $acte) {
    global $pdo;
    
    // 1. Enregistrement dans la table SQL 'Trace' (pour l'admin)
    $sql = "INSERT INTO Trace (id_utilisateur, acte, date_action) VALUES (?, ?, NOW())";
    $pdo->prepare($sql)->execute([$id_utilisateur, $acte]);

    // 2. Enregistrement dans un fichier texte (Fichier Trace physique)
    $ligne = date('Y-m-d H:i:s') . " - User ID $id_utilisateur : $acte" . PHP_EOL;
    file_put_contents('trace_activite.log', $ligne, FILE_APPEND);
}

?>
