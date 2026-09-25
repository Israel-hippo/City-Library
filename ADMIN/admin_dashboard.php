<?php
session_start();
require_once('../config/db.php');

// 1. SÉCURITÉ : Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../PAGE/login.php");
    exit();
}

try {
    // 2. RÉCUPÉRATION DES STATISTIQUES
    $countUsers = $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
    $countLivres = $pdo->query("SELECT COUNT(*) FROM livres")->fetchColumn();
    
    // Vérifie si la table 'emprunts' existe pour les emprunts actifs
    $checkTable = $pdo->query("SHOW TABLES LIKE 'emprunts'")->rowCount();
    $countEmprunts = 0;
    if ($checkTable > 0) {
        $countEmprunts = $pdo->query("SELECT COUNT(*) FROM emprunts WHERE date_retour IS NULL")->fetchColumn();
    }

    // 3. TOP 3 LECTEURS (Utilisation de LEFT JOIN pour éviter le bloc vide)
    $sql_top = "SELECT u.nom, COUNT(e.id) as total 
                FROM utilisateurs u 
                LEFT JOIN emprunts e ON u.id = e.id_utilisateur 
                GROUP BY u.id 
                ORDER BY total DESC 
                LIMIT 3";
    $top_lecteurs = $pdo->query($sql_top)->fetchAll();

    // Récupérer la liste de TOUS les événements et le nombre d'inscrits
    $sql_events = "SELECT e.id, e.titre AS titre_event, COUNT(i.id) as nb_inscrits 
               FROM evenements e
               LEFT JOIN inscriptions_events i ON e.titre = i.titre_event 
               GROUP BY e.id, e.titre
               ORDER BY e.date_evenement ASC";
    $events_stats = $pdo->query($sql_events)->fetchAll();

    // 4. DERNIÈRES INSCRIPTIONS
    $recentUsers = $pdo->query("SELECT nom, email, adresse, role, date_inscription, telephone FROM utilisateurs ORDER BY id DESC LIMIT 5")->fetchAll();

} catch (PDOException $e) {
    $error = "Erreur de base de données : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - City Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --gold: #d4a373;
            --bg-dark: #121212;
            --sidebar-bg: #000000;
            --card-bg: rgba(255, 255, 255, 0.05);
            --text-gray: #b0b0b0;
        }

        body { margin: 0; background: var(--bg-dark); color: white; font-family: 'Poppins', sans-serif; display: flex; }

        /* SIDEBAR */
        .sidebar { width: 260px; height: 100vh; background: var(--sidebar-bg); position: fixed; padding: 30px 20px; border-right: 1px solid rgba(212, 163, 115, 0.3); box-sizing: border-box; }
        .sidebar h2 { font-family: 'Playfair Display', serif; color: var(--gold); text-align: center; margin-bottom: 40px; letter-spacing: 2px; }
        .nav-item { display: flex; align-items: center; padding: 15px; color: white; text-decoration: none; margin-bottom: 10px; border-radius: 8px; transition: 0.3s; gap: 15px; }
        .nav-item:hover, .nav-item.active { background: var(--gold); color: black; }

        /* MAIN CONTENT */
        .main-content { margin-left: 260px; padding: 50px; width: calc(100% - 260px); box-sizing: border-box; }
        
        /* TOP READERS SECTION */
        .top-readers {
            background: linear-gradient(145deg, rgba(212, 163, 115, 0.1), rgba(0,0,0,0));
            padding: 25px;
            border-radius: 15px;
            border: 1px solid rgba(212, 163, 115, 0.2);
            margin-bottom: 40px;
        }
        .reader-item { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .reader-item:last-child { border-bottom: none; }
        .rank { width: 30px; height: 30px; background: var(--gold); color: black; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; margin-right: 15px; }

        /* STATS GRID */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-top: 20px; }
        .stat-card { background: var(--card-bg); padding: 30px; border-radius: 15px; border-bottom: 3px solid var(--gold); transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card i { font-size: 2rem; color: var(--gold); margin-bottom: 15px; }
        .stat-card h3 { margin: 0; font-size: 0.9rem; color: var(--text-gray); text-transform: uppercase; }
        .stat-card p { margin: 10px 0 0; font-size: 2.2rem; font-weight: bold; }

        /* TABLE */
        table { width: 100%; border-collapse: collapse; background: var(--card-bg); border-radius: 10px; overflow: hidden; margin-top: 20px; }
        th { text-align: left; padding: 15px; background: rgba(212, 163, 115, 0.1); color: var(--gold); }
        td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .badge { background: rgba(255,255,255,0.1); padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; }
        .event-card {
    position: relative; /* Pour placer les boutons par rapport à la carte */
    background: #1a1a1a;
    border: 1px solid #d4a373;
    padding: 20px;
    border-radius: 12px;
}

.event-actions {
    position: absolute;
    top: 10px;
    right: 10px;
}

.event-actions i {
    margin-left: 10px;
    cursor: pointer;
    font-size: 0.9rem;
}

.edit-icon { color: #3498db; }
.delete-icon { color: #e74c3c; }

.event-actions i:hover {
    transform: scale(1.2);
}
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>CITY LIBRARY</h2>
        <a href="admin_dashboard.php" class="nav-item active"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="admin.php" class="nav-item"><i class="fas fa-users"></i> Membres</a>
        <a href="admin_inventaire_livres.php" class="nav-item"><i class="fas fa-book"></i> Inventaire</a>
        <a href="admin_categories.php" class="nav-item"><i class="fas fa-book"></i> Catégories</a>
        <a href="gestion_retards.php" class="nav-item"><i class="fas fa-plus-circle"></i> Les retards</a>

        <div style="margin-top: 50px;">
            <a href="../index.php" class="nav-item"><i class="fas fa-eye"></i> Voir le site</a>
            <a href="../PAGE/logout.php" class="nav-item" style="color: #ff4d4d;"><i class="fas fa-power-off"></i> Déconnexion</a>
        </div>
    </div>

    <div class="main-content">
        <div class="header-title">
            <h1 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; margin: 0;">Tableau de Bord</h1>
            <p style="color: var(--text-gray); margin-bottom: 40px;">Bienvenue, Administrateur. Voici l'état actuel de votre bibliothèque.</p>
        </div>

        <div class="top-readers">
            <h3 style="font-family: 'Playfair Display'; color: var(--gold); margin-top: 0;">
                <i class="fas fa-trophy"></i> Top 3 Lecteurs
            </h3>
            <?php if (!empty($top_lecteurs) && $top_lecteurs[0]['total'] > 0): ?>
                <?php foreach ($top_lecteurs as $index => $reader): ?>
                    <?php if($reader['total'] > 0): ?>
                        <div class="reader-item">
                            <div style="display: flex; align-items: center;">
                                <div class="rank"><?php echo $index + 1; ?></div>
                                <span><?php echo htmlspecialchars($reader['nom']); ?></span>
                            </div>
                            <span style="font-weight: bold; color: var(--gold);"><?php echo $reader['total']; ?> livres lus</span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="opacity: 0.5; margin: 0;">Aucune donnée d'emprunt enregistrée pour le moment.</p>
            <?php endif; ?>
        </div>


        <div class="recent-section" style="margin-top: 40px;">
            <h2 style="font-family: 'Playfair Display'; color: var(--gold);">
                <i class="fas fa-calendar-check"></i> Suivi des Inscriptions des événements
            </h2>
    
            <a href="admin_add_event.php" style="background: var(--gold); color: black; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 0.9rem; display: inline-block; margin-bottom: 20px;">
                <i class="fas fa-plus"></i> Ajouter un événement
            </a>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <?php if(!empty($events_stats)): ?>
                    <?php foreach($events_stats as $event): ?>
                        <div class="event-card" style="position: relative; background: var(--card-bg); padding: 25px; border-radius: 12px; border-left: 4px solid var(--gold);">
                
                            <div class="event-actions" style="position: absolute; top: 15px; right: 15px; z-index: 10;">
                                <a href="admin_modifier_evenement.php?id=<?= $event['id'] ?>" title="Modifier" style="color: #3498db; margin-right: 15px; text-decoration: none;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="admin_supprimer_evenement.php?id=<?= $event['id'] ?>" 
                                    onclick="return confirm('Supprimer cet événement ?')" 
                                    title="Supprimer" style="color: #e74c3c; text-decoration: none;">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>

                            <h3 style="margin: 0 0 10px 0; font-size: 1.2rem; padding-right: 60px;">
                                <?= htmlspecialchars($event['titre_event']); ?>
                            </h3>

                            <p style="margin: 0; color: var(--gold); font-weight: bold; font-size: 1.4rem;">
                                <?= $event['nb_inscrits']; ?> <span style="font-size: 0.9rem; font-weight: normal; opacity: 0.8;">inscrit(s)</span>
                            </p>

                            <a href="admin_events.php?id=<?= $event['id'] ?>" style="display: block; margin-top: 15px; color: #888; text-decoration: none; font-size: 0.85rem;">
                                <i class="fas fa-list"></i> Voir la liste des noms
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="opacity: 0.5;">Aucun événement prévu pour le moment.</p>
                <?php endif; ?>
            </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-user-friends"></i>
                <h3>Total Membres</h3>
                <p><?php echo $countUsers; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-book"></i>
                <h3>Livres en Stock</h3>
                <p><?php echo $countLivres; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-exchange-alt"></i>
                <h3>Emprunts Actifs</h3>
                <p><?php echo $countEmprunts; ?></p>
            </div>
        </div>

        <div class="recent-section" style="margin-top: 50px;">
            <h2 style="font-family: 'Playfair Display', serif;">Dernières Inscriptions</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Adresse</th>
                        <th>Téléphone</th>
                        <th>Rôle</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($recentUsers)): ?>
                        <?php foreach($recentUsers as $ru): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($ru['nom']); ?></strong></td>
                            <td><?php echo htmlspecialchars($ru['email']); ?></td>
                            <td><?php echo htmlspecialchars($ru['adresse']); ?></td>
                            <td><?php echo htmlspecialchars($ru['telephone']); ?></td>
                            <td><span class="badge"><?php echo htmlspecialchars($ru['role']); ?></span></td>
                            <td><?php echo date('d/m/Y', strtotime($ru['date_inscription'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center;">Aucun utilisateur trouvé.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>