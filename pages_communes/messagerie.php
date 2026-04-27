<?php
session_start();
require_once '../db.php'; 

if (!isset($_SESSION['id_utilisateur'])) { 
    header('Location: connexion.html'); 
    exit(); 
}

$mon_id = $_SESSION['id_utilisateur'];
$message_info = "";

// --- ACTION 1 : ENVOYER UN MESSAGE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['envoyer_msg'])) {
    $stmt = $pdo->prepare("INSERT INTO Message (id_expediteur, id_destinataire, sujet, contenu) VALUES (?, ?, ?, ?)");
    $stmt->execute([$mon_id, $_POST['destinataire'], $_POST['sujet'], $_POST['contenu']]);
    $message_info = "<div class='alert alert-success'>Message envoyé avec succès !</div>";
}

// --- ACTION 2 : SUPPRIMER UN MESSAGE ---
if (isset($_POST['supprimer_msg_id'])) {
    $id_a_supprimer = intval($_POST['supprimer_msg_id']);
    // On vérifie que l'utilisateur est bien le destinataire pour avoir le droit de le supprimer
    $stmtDel = $pdo->prepare("DELETE FROM Message WHERE id_message = ? AND id_destinataire = ?");
    if ($stmtDel->execute([$id_a_supprimer, $mon_id])) {
        $message_info = "<div class='alert alert-warning'>Message supprimé.</div>";
    }
}

// --- RÉCUPÉRATION DES MESSAGES ---
$sql_inbox = "SELECT m.*, u.prenom, u.nom_utilisateur, u.role_utilisateur 
              FROM Message m 
              JOIN Utilisateur u ON m.id_expediteur = u.id_utilisateur 
              WHERE m.id_destinataire = ? ORDER BY m.date_envoi DESC";
$stmtInbox = $pdo->prepare($sql_inbox);
$stmtInbox->execute([$mon_id]);
$boite_reception = $stmtInbox->fetchAll();

// Liste des utilisateurs pour l'envoi
$liste_users = $pdo->query("SELECT id_utilisateur, prenom, nom_utilisateur, role_utilisateur FROM Utilisateur ORDER BY role_utilisateur")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messagerie - CY Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .accordion-button:not(.collapsed) { background-color: #e7f1ff; color: #0c63e4; }
        .msg-meta { font-size: 0.85rem; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>✉️ Messagerie Interne</h2>
        <button onclick="history.back()" class="btn btn-secondary btn-sm">← Retour</button>
    </div>

    <?= $message_info ?>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white fw-bold">Nouveau Message</div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="envoyer_msg" value="1">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Destinataire</label>
                            <select class="form-select" name="destinataire" required>
                                <option value="">Chercher un utilisateur...</option>
                                <?php foreach($liste_users as $u): ?>
                                    <?php if($u['id_utilisateur'] != $mon_id): ?>
                                        <option value="<?= $u['id_utilisateur'] ?>">
                                            <?= htmlspecialchars($u['prenom'].' '.$u['nom_utilisateur']) ?> (<?= $u['role_utilisateur'] ?>)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Sujet</label>
                            <input type="text" name="sujet" class="form-control" required placeholder="Objet du message">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Message</label>
                            <textarea name="contenu" class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">🚀 Envoyer le message</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">📥 Boîte de réception</h5>
                    <span class="badge bg-primary rounded-pill"><?= count($boite_reception) ?></span>
                </div>
                
                <div class="accordion accordion-flush" id="accordionMessages">
                    <?php if(empty($boite_reception)): ?>
                        <div class="p-4 text-center text-muted">Aucun message reçu.</div>
                    <?php endif; ?>

                    <?php foreach($boite_reception as $index => $msg): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#msg-<?= $msg['id_message'] ?>">
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold"><?= htmlspecialchars($msg['sujet']) ?></span>
                                        <small class="text-muted">De : <?= htmlspecialchars($msg['prenom'].' '.$msg['nom_utilisateur']) ?></small>
                                    </div>
                                    <small class="ms-auto text-muted me-3 d-none d-md-block">
                                        <?= date('d/m/Y H:i', strtotime($msg['date_envoi'])) ?>
                                    </small>
                                </button>
                            </h2>
                            <div id="msg-<?= $msg['id_message'] ?>" class="accordion-collapse collapse" data-bs-parent="#accordionMessages">
                                <div class="accordion-body">
                                    <div class="p-3 bg-light rounded mb-3" style="white-space: pre-wrap;"><?= htmlspecialchars($msg['contenu']) ?></div>
                                    
                                    <div class="d-flex justify-content-end">
                                        <form method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer ce message ?');">
                                            <input type="hidden" name="supprimer_msg_id" value="<?= $msg['id_message'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                🗑️ Supprimer ce message
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
