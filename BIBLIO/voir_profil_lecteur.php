<?php
session_start();
require_once('../config/db.php');

// Sécurité : Accès réservé au personnel
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'bibliothecaire' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../PAGE/login.php');
    exit();
}

$id_lecteur = $_GET['id'] ?? null;

if (!$id_lecteur) {
    header("Location: lecteurs.php");
    exit();
}

try {
    // 1. Récupérer les infos du lecteur
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ? AND role = 'lecteur'");
    $stmt->execute([$id_lecteur]);
    $lecteur = $stmt->fetch();

    if (!$lecteur) {
        header("Location: lecteurs.php");
        exit();
    }

    // 2. Récupérer l'historique complet des emprunts (rendus et en cours)
    $sql_history = "SELECT e.*, l.titre, l.image_url 
                    FROM emprunts e 
                    JOIN livres l ON e.id_livre = l.id 
                    WHERE e.id_utilisateur = ? 
                    ORDER BY e.date_emprunt DESC";
    $stmt_h = $pdo->prepare($sql_history);
    $stmt_h->execute([$id_lecteur]);
    $historique = $stmt_h->fetchAll();

    // 3. Calculer quelques statistiques pour le profil
    $total_emprunts = count($historique);
    $en_cours = 0;
    foreach($historique as $h) { if($h['date_retour'] == null) $en_cours++; }

} catch (PDOException $e) {
    $erreur = "Erreur SQL : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil Lecteur - City Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --gold: #d4a373; --bg: #0b0e11; --card: rgba(255, 255, 255, 0.05); }
        body { margin: 0; background: var(--bg); color: white; font-family: 'Poppins', sans-serif; display: flex; }
        .main-content { margin-left: 260px; padding: 50px; width: calc(100% - 260px); }
        
        /* Header Profil */
        .profile-header { 
            display: flex; align-items: center; background: var(--card); 
            padding: 30px; border-radius: 20px; border-left: 5px solid var(--gold);
            margin-bottom: 40px;
        }
        .profile-avatar { 
            width: 80px; height: 80px; background: var(--gold); color: black; 
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; font-weight: bold; margin-right: 30px;
        }
        .profile-info h1 { margin: 0; font-family: 'Playfair Display', serif; }
        .profile-info p { margin: 5px 0; color: #b0b0b0; }

        /* Stats */
        .stats-row { display: flex; gap: 20px; margin-bottom: 40px; }
        .stat-box { 
            flex: 1; background: rgba(255,255,255,0.02); padding: 20px; 
            border-radius: 15px; border: 1px solid rgba(255,255,255,0.05); text-align: center;
        }
        .stat-box h2 { color: var(--gold); margin: 0; font-size: 1.8rem; }
        .stat-box span { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: #666; }

        /* Table Historique */
        .history-table { width: 100%; border-collapse: collapse; background: var(--card); border-radius: 15px; overflow: hidden; }
        th { text-align: left; padding: 15px 20px; background: rgba(212, 163, 115, 0.1); color: var(--gold); font-size: 0.8rem; }
        td { padding: 15px 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        
        .status-badge { padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; }
        .returned { background: rgba(46, 204, 113, 0.1); color: #2ecc71; }
        .not-returned { background: rgba(231, 76, 60, 0.1); color: #e74c3c; }

        .btn-back { color: #b0b0b0; text-decoration: none; margin-bottom: 20px; display: inline-block; transition: 0.3s; }
        .btn-back:hover { color: var(--gold); }
    </style>
</head>
<body>

    <?php include('navbar_biblio.php'); ?>

    <div class="main-content">
        <a href="lecteurs.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour à la liste</a>

        <div class="profile-header">
            <div class="profile-avatar"><?= strtoupper(substr($lecteur['nom'], 0, 1)) ?></div>
            <div class="profile-info">
                <h1><?= htmlspecialchars($lecteur['nom']) ?></h1>
                <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($lecteur['email']) ?></p>
                <p><i class="fas fa-calendar-alt"></i> Membre depuis le : <?= date('d/m/Y', strtotime($lecteur['date_inscription'])) ?></p>
            </div>
        </div>

        <div class="stats-row">
            <div class="stat-box">
                <h2><?= $total_emprunts ?></h2>
                <span>Total Emprunts</span>
            </div>
            <div class="stat-box">
                <h2><?= $en_cours ?></h2>
                <span>En cours</span>
            </div>
            <div class="stat-box">
                <h2><?= ($lecteur['approuve'] == 1) ? 'OUI' : 'NON' ?></h2>
                <span>Compte Approuvé</span>
            </div>
        </div>

        <h2 style="font-family: 'Playfair Display'; margin-bottom: 20px;">Historique des <span style="color: var(--gold);">Activités</span></h2>

        <table class="history-table">
            <thead>
                <tr>
                    <th>Livre</th>
                    <th>Date Emprunt</th>
                    <th>Retour Prévu</th>
                    <th>Date Retour Réelle</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($historique)): ?>
                    <tr><td colspan="5" style="text-align: center; opacity: 0.5;">Aucun historique pour ce lecteur.</td></tr>
                <?php else: ?>
                    <?php foreach ($historique as $h): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($h['titre']) ?></strong></td>
                        <td><?= date('d/m/Y', strtotime($h['date_emprunt'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($h['date_retour_prevue'])) ?></td>
                        <td><?= $h['date_retour'] ? date('d/m/Y', strtotime($h['date_retour'])) : '-' ?></td>
                        <td>
                            <?php if($h['date_retour']): ?>
                                <span class="status-badge returned">Rendu</span>
                            <?php else: ?>
                                <span class="status-badge not-returned">En possession</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>