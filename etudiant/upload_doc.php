<?php
session_start();
require_once '../db.php'; // Ajuste le chemin si db.php n'est pas dans le dossier parent

// Sécurité : On vérifie que l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../connexion.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user = $_SESSION['id_utilisateur'];
    $nom_doc = $_POST['nom_doc']; // Le nom que l'étudiant a tapé (ex: "Mon CV 2026")

    // 1. Vérification qu'un fichier a bien été envoyé sans erreur
    if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
        
        $repertoire = "../documents/"; // Le dossier de destination (à la racine)
        
        // (Optionnel mais recommandé) Créer le dossier s'il n'existe pas encore
        if (!is_dir($repertoire)) {
            mkdir($repertoire, 0777, true);
        }

        // 2. Sécurisation du nom du fichier
        // On récupère le vrai nom du fichier (ex: cv-mathieu.pdf)
        $nom_fichier_original = basename($_FILES['fichier']['name']);
        
        // On remplace les espaces et caractères bizarres par des tirets du bas
        $nom_fichier_propre = preg_replace("/[^a-zA-Z0-9.]/", "_", $nom_fichier_original);
        
        // On ajoute le timestamp (l'heure exacte) devant pour le rendre unique
        // Ex: 1715423812_cv_mathieu.pdf
        $nom_fichier_final = time() . "_" . $nom_fichier_propre;
        
        $chemin_complet = $repertoire . $nom_fichier_final;

        // 3. Déplacement du fichier temporaire vers le dossier final
        if (move_uploaded_file($_FILES['fichier']['tmp_name'], $chemin_complet)) {
            
            // 4. Si le fichier est bien déplacé, on l'enregistre dans la BDD
            try {
                $stmt = $pdo->prepare("INSERT INTO Document (id_possesseur, nom_doc, chemin_fichier) VALUES (?, ?, ?)");
                // On n'enregistre que le nom final dans la base de données, pas tout le chemin
                $stmt->execute([$id_user, $nom_doc, $nom_fichier_final]);

                // Redirection vers la page des documents avec un message de succès
                header("Location: mes_documents.php?success=upload");
                exit();
                
            } catch (PDOException $e) {
                die("Erreur SQL lors de l'enregistrement : " . $e->getMessage());
            }
            
        } else {
            die("Erreur : Impossible de déplacer le fichier sur le serveur. Vérifiez les droits d'écriture du dossier 'documents/'.");
        }
        
    } else {
        die("Erreur lors du téléchargement. Code d'erreur PHP : " . $_FILES['fichier']['error']);
    }
} else {
    // Si on accède à la page sans valider le formulaire, on redirige
    header("Location: mes_documents.php");
    exit();
}
?>
