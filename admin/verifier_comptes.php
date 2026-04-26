<?php
// Requête pour trouver les comptes en attente
$stmt = $pdo->query("SELECT * FROM Utilisateur WHERE est_valide = FALSE AND role != 'etudiant'");
$en_attente = $stmt->fetchAll();
?>

<h2>Comptes en attente de validation</h2>
<table>
    <tr>
        <th>Nom</th>
        <th>Email</th>
        <th>Rôle</th>
        <th>Action</th>
    </tr>
    <?php foreach($en_attente as $u): ?>
    <tr>
        <td><?php echo htmlspecialchars($u['nom_utilisateur']); ?></td>
        <td><?php echo htmlspecialchars($u['mail']); ?></td>
        <td><?php echo htmlspecialchars($u['role']); ?></td>
        <td>
            <button onclick="validerCompte(<?php echo $u['id_utilisateur']; ?>)">Approuver</button>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
