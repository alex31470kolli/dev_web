<?php
session_start();
require_once 'db.php';

// --- SÉCURITÉ ---
// On vérifie que l'utilisateur est connecté ET qu'il est admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: connexion.html');
    exit();
}

$prenom_admin = $_SESSION['prenom'] ?? 'Administrateur';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - CY TECH Stages</title>
    <link rel="stylesheet" href="admin.css"> <style>
        
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>CY TECH Admin</h2>
        <p>Bienvenue, <?php echo htmlspecialchars($prenom_admin); ?></p>
        <hr>
        <nav>
            <p>Tableau de bord</p>
            <a href="admin_home.php" style="color:white; text-decoration:none; display:block; margin: 10px 0;">🏠 Accueil</a>
            <a href="deconnexion.php" class="btn-logout">Déconnexion</a>
        </nav>
    </div>

    <div class="main-content">
        <h1>Panneau de Contrôle de la Plateforme</h1>
        <p>Gérez les utilisateurs, les entreprises et consultez les archives du site.</p>

        <div class="card-grid">
            <a href="verifier_comptes.php" class="card">
                <h3>👥 Valider les Comptes</h3>
                <p>Approuver les nouvelles entreprises et les nouveaux comptes administrateurs en attente.</p>
            </a>

            <a href="acces_logs.php" class="card">
                <h3>📜 Journal d'Activité</h3>
                <p>Consulter les logs (traces) des actions effectuées sur le site (connexions, modifications).</p>
            </a>

            <a href="gestion_stages.php" class="card">
                <h3>💼 Gestion des Stages</h3>
                <p>Informatiser et suivre les différents stages des départements de l'école.</p>
            </a>

            <a href="statistiques.php" class="card">
                <h3>📊 Statistiques</h3>
                <p>Voir le nombre d'offres par filière (ING1, ING2, ING3) et le taux de placement.</p>
            </a>
        </div>

        <section style="margin-top: 50px; background: #e2e8f0; padding: 20px; border-radius: 12px;">
            <h3>Note pour la soutenance</h3>
            <p>Cette interface permet de répondre à l'exigence de <strong>contrôle par les enseignants</strong> mentionnée dans les consignes. Elle centralise les données des entreprises et assure la sécurité via les logs.</p>
        </section>
    </div>

</body>
</html>