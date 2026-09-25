<?php
session_start();
require_once('../config/db.php');

// 1. Sécurité Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../PAGE/login.php");
    exit();
}

$id_event = isset($_GET['id']) ? $_GET['id'] : null;
$inscrits = [];
$event_name = "";

if ($id_event) {
    // A. On récupère d'abord le titre de l'événement via son ID
    $stmt = $pdo->prepare("SELECT titre FROM evenements WHERE id = ?");
    $stmt->execute([$id_event]);
    $event = $stmt->fetch();

    if ($event) {
        $event_name = $event['titre'];

        // B. On récupère les membres inscrits à ce titre précis
        // On fait une jointure avec 'utilisateurs' pour avoir les noms et emails
        $sql = "SELECT u.nom, u.email, u.telephone, i.date_inscription 
                FROM inscriptions_events i 
                JOIN utilisateurs u ON i.id_utilisateur = u.id 
                WHERE i.titre_event = ?";
        $stmt_inscrits = $pdo->prepare($sql);
        $stmt_inscrits->execute([$event_name]);
        $inscrits = $stmt_inscrits->fetchAll();
    } else {
        die("Événement introuvable en base de données.");
    }
} else {
    die("Aucun identifiant d'événement fourni.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Inscrits - <?= htmlspecialchars($event_name) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #121212; color: white; font-family: 'Poppins', sans-serif; padding: 40px; }
        .container { max-width: 800px; margin: auto; background: #1a1a1a; padding: 30px; border-radius: 15px; border: 1px solid #d4a373; }
        h1 { color: #d4a373; font-family: 'Playfair Display', serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; color: #d4a373; border-bottom: 2px solid #333; padding: 12px; }
        td { padding: 12px; border-bottom: 1px solid #222; }
        .back-btn { display: inline-block; margin-bottom: 20px; color: #d4a373; text-decoration: none; }
    </style>
</head>
<body>

<div class="container">
    <a href="admin_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Retour au Dashboard</a>
    
    <h1>Inscrits : <?= htmlspecialchars($event_name) ?></h1>
    <p><?= count($inscrits) ?> personne(s) inscrite(s)</p>

    <?php if (!empty($inscrits)): ?>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inscrits as $inscrit): ?>
                    <tr>
                        <td><?= htmlspecialchars($inscrit['nom']) ?></td>
                        <td><?= htmlspecialchars($inscrit['email']) ?></td>
                        <td><?= htmlspecialchars($inscrit['telephone'] ?? 'Non renseigné') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="margin-top: 20px; opacity: 0.6;">Aucun membre n'est encore inscrit à cet événement.</p>
    <?php endif; ?>
</div>

</body>
</html>