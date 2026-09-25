<?php
session_start();
require_once('../config/db.php');

// Sécurité : Accès réservé au personnel
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'bibliothecaire' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../PAGE/login.php');
    exit();
}

// Récupération du nom de l'événement depuis l'URL
$event_name = $_GET['event'] ?? null;

if (!$event_name) {
    header("Location: biblio_dashboard.php");
    exit();
}

try {
    // Requête pour lister les utilisateurs inscrits à cet événement précis
    $sql = "SELECT u.nom, u.email, u.telephone, i.date_inscription 
            FROM inscriptions_events i
            JOIN utilisateurs u ON i.id_utilisateur = u.id
            WHERE i.titre_event = ?
            ORDER BY i.date_inscription DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$event_name]);
    $participants = $stmt->fetchAll();
} catch (PDOException $e) {
    $erreur = "Erreur SQL : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Participants - <?= htmlspecialchars($event_name) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --gold: #d4a373; --bg: #0b0e11; --card: rgba(255, 255, 255, 0.05); }
        body { margin: 0; background: var(--bg); color: white; font-family: 'Poppins', sans-serif; display: flex; }
        .main-content { margin-left: 260px; padding: 50px; width: calc(100% - 260px); }
        
        h1 { font-family: 'Playfair Display', serif; margin-bottom: 10px; }
        .event-title { color: var(--gold); margin-bottom: 30px; }
        
        .participants-table { width: 100%; border-collapse: collapse; background: var(--card); border-radius: 15px; overflow: hidden; }
        th { text-align: left; padding: 20px; background: rgba(212, 163, 115, 0.1); color: var(--gold); text-transform: uppercase; font-size: 0.8rem; }
        td { padding: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        
        .btn-back { color: #b0b0b0; text-decoration: none; display: inline-block; margin-bottom: 20px; transition: 0.3s; }
        .btn-back:hover { color: var(--gold); }
    </style>
</head>
<body>

    <?php include('navbar_biblio.php'); ?>

    <div class="main-content">
        <a href="biblio_dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour au Dashboard</a>
        
        <h1>Liste des <span class="event-title">Inscrits</span></h1>
        <p style="margin-bottom: 30px;">Événement : <strong><?= htmlspecialchars($event_name) ?></strong></p>

        <?php if(empty($participants)): ?>
            <div style="background: var(--card); padding: 40px; text-align: center; border-radius: 15px;">
                <i class="fas fa-user-slash" style="font-size: 3rem; color: #444; margin-bottom: 15px;"></i>
                <p style="opacity: 0.5;">Aucun inscrit pour le moment.</p>
            </div>
        <?php else: ?>
            <table class="participants-table">
                <thead>
                    <tr>
                        <th>Nom du Lecteur</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Date d'inscription</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participants as $p): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($p['nom']) ?></strong></td>
                        <td style="color: #b0b0b0;"><?= htmlspecialchars($p['email']) ?></td>
                        <td><?= htmlspecialchars($p['telephone']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($p['date_inscription'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>
</html>