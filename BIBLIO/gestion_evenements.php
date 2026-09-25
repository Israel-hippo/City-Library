<?php
session_start();
require_once('../config/db.php');

// Sécurité
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'bibliothecaire' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../PAGE/login.php');
    exit();
}

// Récupération des événements et du nombre d'inscrits
$sql = "SELECT e.*, (SELECT COUNT(*) FROM inscriptions_events WHERE titre_event = e.titre) as nb_inscrits 
        FROM evenements e ORDER BY date_evenement ASC";
$evenements = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Événements - City Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --gold: #d4a373; --bg: #0b0e11; --card: rgba(255, 255, 255, 0.05); }
        body { margin: 0; background: var(--bg); color: white; font-family: 'Poppins', sans-serif; display: flex; }
        .main-content { margin-left: 260px; padding: 50px; width: calc(100% - 260px); }
        
        h1 { font-family: 'Playfair Display', serif; margin-bottom: 30px; }
        .btn-add { 
            background: var(--gold); color: black; padding: 12px 25px; 
            border-radius: 8px; text-decoration: none; font-weight: bold; 
            display: inline-block; margin-bottom: 30px; transition: 0.3s;
        }
        .btn-add:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(212, 163, 115, 0.3); }

        .events-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; }
        .event-card { 
            background: var(--card); border-radius: 15px; padding: 25px; 
            border: 1px solid rgba(255,255,255,0.05); border-left: 4px solid var(--gold);
            position: relative;
        }
        .event-card h3 { margin-top: 0; color: white; font-family: 'Playfair Display'; }
        .event-card p { color: #b0b0b0; font-size: 0.9rem; line-height: 1.5; }
        .event-meta { font-size: 0.85rem; color: var(--gold); margin-top: 15px; display: flex; justify-content: space-between; }
        
        .nb-inscrits { background: rgba(212, 163, 115, 0.1); padding: 5px 10px; border-radius: 5px; color: var(--gold); font-weight: bold; }
    </style>
</head>
<body>
    <?php include('navbar_biblio.php'); ?>

    <div class="main-content">
        <h1>Suivi des <span style="color: var(--gold);">Événements</span></h1>
        
        <a href="ajouter_evenement.php" class="btn-add">
            <i class="fas fa-plus"></i> Ajouter un événement
        </a>

        <div class="events-grid">
            <?php foreach($evenements as $ev): ?>
            <div class="event-card">
                <h3><?= htmlspecialchars($ev['titre']) ?></h3>
                <p><?= nl2br(htmlspecialchars(substr($ev['description'], 0, 120))) ?>...</p>
                
                <div class="event-meta">
                    <span><i class="far fa-calendar-alt"></i> <?= date('d/m/Y H:i', strtotime($ev['date_evenement'])) ?></span>
                    <span class="nb-inscrits"><?= $ev['nb_inscrits'] ?> inscrit(s)</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>