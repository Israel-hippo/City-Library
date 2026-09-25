<?php
session_start();
require_once('../config/db.php');

// Sécurité : Accès réservé aux bibliothécaires et admins
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'bibliothecaire' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../PAGE/login.php');
    exit();
}

try {
    // 1. Récupération du Top 3 des lecteurs (basé sur le nombre d'emprunts dans la table emprunts)
    $sql_top = "SELECT u.nom, COUNT(e.id) as livres_lus 
                FROM utilisateurs u 
                JOIN emprunts e ON u.id = e.id_utilisateur 
                WHERE u.role = 'lecteur'
                GROUP BY u.id 
                ORDER BY livres_lus DESC 
                LIMIT 3";
    $top_lecteurs = $pdo->query($sql_top)->fetchAll();

    // 2. Récupération des événements avec le calcul en temps réel des inscrits
    $sql_events = "SELECT e.*, (SELECT COUNT(*) FROM inscriptions_events WHERE titre_event = e.titre) as nb_inscrits 
                   FROM evenements e 
                   ORDER BY e.date_evenement ASC 
                   LIMIT 4";
    $evenements = $pdo->query($sql_events)->fetchAll();

} catch (PDOException $e) {
    $erreur = "Erreur de base de données : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Bibliothécaire - City Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --gold: #d4a373; --bg: #0b0e11; --card: rgba(255, 255, 255, 0.05); }
        body { margin: 0; background: var(--bg); color: white; font-family: 'Poppins', sans-serif; display: flex; }
        
        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        h1 { font-family: 'Playfair Display', serif; margin-bottom: 10px; }
        .subtitle { color: #888; margin-bottom: 40px; font-size: 0.9rem; }

        /* Section Top Lecteurs */
        .dashboard-section { background: var(--card); padding: 30px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 40px; }
        .section-title { display: flex; align-items: center; gap: 15px; color: var(--gold); margin-bottom: 25px; font-size: 1.1rem; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        
        .top-lecteur-item { 
            display: flex; justify-content: space-between; align-items: center; 
            padding: 15px 0; border-bottom: 1px solid rgba(255,255,255,0.05); 
        }
        .rank-badge { 
            width: 35px; height: 35px; background: var(--gold); color: black; 
            border-radius: 50%; display: flex; align-items: center; justify-content: center; 
            font-weight: bold; margin-right: 15px; font-size: 0.9rem;
        }

        /* Section Suivi des Événements */
        .btn-add-ev { 
            background: var(--gold); color: black; padding: 12px 20px; border-radius: 8px; 
            text-decoration: none; font-weight: bold; font-size: 0.9rem; margin-bottom: 25px; 
            display: inline-block; transition: 0.3s;
        }
        .btn-add-ev:hover { transform: scale(1.02); opacity: 0.9; }

        .events-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px; }
        .event-card { 
            background: var(--card); padding: 25px; border-radius: 15px; 
            border: 1px solid rgba(255,255,255,0.05); border-left: 4px solid var(--gold);
        }
        .event-card h3 { margin: 0 0 8px 0; font-family: 'Playfair Display'; font-size: 1.2rem; }
        .event-card .nb-inscrits { color: var(--gold); font-weight: bold; display: block; margin-bottom: 15px; }
        
        .btn-view-list { 
            color: #b0b0b0; text-decoration: none; font-size: 0.85rem; 
            display: flex; align-items: center; gap: 8px; transition: 0.3s;
        }
        .btn-view-list:hover { color: var(--gold); }
    </style>
</head>
<body>

    <?php include('navbar_biblio.php'); ?>

    <div class="main-content">
        <h1>Dashboard <span style="color: var(--gold);">Bibliothécaire</span></h1>
        <p class="subtitle">Bienvenue, Bibliothécaire. Voici l'état actuel de votre bibliothèque.</p>

        <div class="dashboard-section">
            <div class="section-title"><i class="fas fa-trophy"></i> Top 3 Lecteurs</div>
            <?php if(empty($top_lecteurs)): ?>
                <p style="opacity: 0.5;">Aucune donnée d'emprunt disponible.</p>
            <?php else: ?>
                <?php foreach($top_lecteurs as $index => $l): ?>
                    <div class="top-lecteur-item">
                        <div style="display: flex; align-items: center;">
                            <div class="rank-badge"><?= $index + 1 ?></div>
                            <span style="font-weight: 500;"><?= htmlspecialchars($l['nom']) ?></span>
                        </div>
                        <span style="color: var(--gold); font-size: 0.9rem;"><?= $l['livres_lus'] ?> livres lus</span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="section-title"><i class="fas fa-calendar-check"></i> Suivi des Inscriptions des événements</div>
        
        <a href="ajouter_evenement.php" class="btn-add-ev"><i class="fas fa-plus"></i> Ajouter un événement</a>
        
        <div class="events-grid">
            <?php if(empty($evenements)): ?>
                <p style="opacity: 0.5;">Aucun événement programmé.</p>
            <?php else: ?>
                <?php foreach($evenements as $ev): ?>
                    <div class="event-card">
                        <h3><?= htmlspecialchars($ev['titre']) ?></h3>
                        <span class="nb-inscrits"><?= $ev['nb_inscrits'] ?> inscrit(s)</span>
                        
                        <a href="liste_inscrits.php?event=<?= urlencode($ev['titre']) ?>" class="btn-view-list">
                            <i class="fas fa-list-ul"></i> Voir la liste des noms
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>