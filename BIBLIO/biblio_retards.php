<?php
session_start();
require_once('../config/db.php');

// Vérification des droits : Admin ou Bibliothécaire uniquement
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'bibliothecaire' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../PAGE/login.php');
    exit();
}

try {
    // Requête pour trouver les livres non rendus (date_retour est NULL) et en retard
    $sql = "SELECT u.nom, u.email, l.titre, e.date_retour_prevue,
            DATEDIFF(CURDATE(), e.date_retour_prevue) as jours_retard
            FROM emprunts e
            JOIN utilisateurs u ON e.id_utilisateur = u.id
            JOIN livres l ON e.id_livre = l.id
            WHERE e.date_retour IS NULL 
            AND e.date_retour_prevue < CURDATE()
            ORDER BY jours_retard DESC";
            
    $retards = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Lecteurs en retard - City Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --gold: #d4a373; --bg: #0b0e11; --card: rgba(255, 255, 255, 0.05); }
        body { margin: 0; background: var(--bg); color: white; font-family: 'Poppins', sans-serif; display: flex; }
        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        h1 { font-family: 'Playfair Display', serif; color: #ff4d4d; } /* Rouge pour marquer l'urgence */
        
        .btn-back { background: transparent; border: 1px solid var(--gold); color: var(--gold); padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; }

        table { width: 100%; border-collapse: collapse; background: var(--card); border-radius: 15px; overflow: hidden; }
        th { background: rgba(255, 255, 255, 0.1); color: var(--gold); padding: 20px; text-align: left; text-transform: uppercase; font-size: 0.85rem; }
        td { padding: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        
        .retard-badge { color: #ff4d4d; font-weight: bold; }
        .amende { font-weight: bold; color: white; }
    </style>
</head>
<body>

    <?php include('navbar_biblio.php'); ?>

    <div class="main-content">
        <div class="header-flex">
            <h1>Lecteurs en retard</h1>
            <a href="biblio_dashboard.php" class="btn-back">Tableau de bord</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Lecteur</th>
                    <th>Livre</th>
                    <th>Date Prévue</th>
                    <th>Jours de retard</th>
                    <th>Amende estimée</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($retards)): ?>
                    <tr><td colspan="5" style="text-align:center; opacity:0.5;">Aucun retard détecté.</td></tr>
                <?php else: ?>
                    <?php foreach($retards as $r): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($r['nom']) ?></strong><br>
                                <span style="font-size: 0.8rem; color: #888;"><?= htmlspecialchars($r['email']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($r['titre']) ?></td>
                            <td><?= date('d/m/Y', strtotime($r['date_retour_prevue'])) ?></td>
                            <td class="retard-badge"><?= $r['jours_retard'] ?> jours</td>
                            <td class="amende"><?= number_format($r['jours_retard'] * 0.5, 2) ?> €</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>