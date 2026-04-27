<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: connexion.html');
    exit();
}

$id_user = $_SESSION['id_utilisateur'];
$role = $_SESSION['role'];

// Définir dynamiquement le lien de retour en fonction du rôle
$lien_retour = 'connexion.html';
if ($role === 'admin') $lien_retour = '../admin/admin_home.php';
elseif ($role === 'entreprise') $lien_retour = '../entreprise/entreprise_home.php';
elseif ($role === 'etudiant') $lien_retour = '../etudiant/etudiant_home.php';

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom']);
    $nom = trim($_POST['nom_utilisateur']);
    $mail = trim($_POST['mail']);

    try {
        $stmt = $pdo->prepare("UPDATE Utilisateur SET prenom = ?, nom_utilisateur = ?, mail = ? WHERE id_utilisateur = ?");
        $stmt->execute([$prenom, $nom, $mail, $id_user]);
        
        // --- LOG DE L'ACTION ---
        if (function_exists('logAction')) {
            logAction($id_user, "A modifié son profil (Nom/Prénom/Email)");
        } else {
            // Sécurité au cas où la fonction logAction n'est pas chargée
            $log = $pdo->prepare("INSERT INTO Trace (id_utilisateur, acte) VALUES (?, ?)");
            $log->execute([$id_user, "A modifié son profil (Nom/Prénom/Email)"]);
        }

        // Mise à jour de la session
        $_SESSION['prenom'] = $prenom;
        $success = "Votre profil a été mis à jour avec succès.";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Erreur : Cette adresse email est déjà utilisée par un autre compte.";
        } else {
            $error = "Erreur SQL : " . $e->getMessage();
        }
    }
}

// Récupération des données actuelles pour pré-remplir le formulaire
$stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$id_user]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil - CY Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>👤 Mon Profil</h2>
                <a href="<?= $lien_retour ?>" class="btn btn-secondary">← Retour</a>
            </div>

            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <div class="card shadow-sm border-primary">
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Prénom</label>
                            <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nom</label>
                            <input type="text" name="nom_utilisateur" class="form-control" value="<?= htmlspecialchars($user['nom_utilisateur']) ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Adresse Email</label>
                            <input type="email" name="mail" class="form-control" value="<?= htmlspecialchars($user['mail']) ?>" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">💾 Enregistrer les modifications</button>
                    </form>
                </div>
                <div class="card-footer bg-white text-muted text-center small">
                    Rôle actuel : <span class="badge bg-secondary"><?= strtoupper($user['role_utilisateur']) ?></span>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
