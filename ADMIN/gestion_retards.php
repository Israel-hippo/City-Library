<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'bibliothecaire')) { header('Location: PAGE/login.php'); exit(); }

// On récupère les emprunts en retard (date_retour_prevue < aujourd'hui et non rendu)
$today = date('Y-m-d');
$sql = "SELECT e.*, l.titre, u.nom as lecteur_nom, u.email as lecteur_email,
        DATEDIFF(:today, e.date_retour_prevue) as jours_retard
        FROM emprunts e
        JOIN livres l ON e.id_livre = l.id
        JOIN utilisateurs u ON e.id_utilisateur = u.id
        WHERE e.date_retour_prevue < :today 
        AND e.date_retour IS NULL"; // Important : seulement ceux non rendus

$stmt = $pdo->prepare($sql);
$stmt->execute(['today' => $today]);
$retards = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="../css/style.css">
    <title>Gestion des Retards</title>
</head>
<body style="background: #02060a; color: white; padding: 40px;">

    <div style="display:flex; justify-content:space-between; align-items:center;">
    <h2 style="color: #e74c3c;"><i class="fas fa-exclamation-triangle"></i> Lecteurs en retard</h2>
        <a href="admin_dashboard.php" class="btn-browse outline">Tableau de bord</a>
    </div>

    <div class="card" style="margin-top:30px; background: rgba(255,255,255,0.02);">
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr style="background: rgba(231, 76, 60, 0.2); color: white;">
                    <th style="padding: 15px;">Lecteur</th>
                    <th style="padding: 15px;">Livre</th>
                    <th style="padding: 15px;">Date Prévue</th>
                    <th style="padding: 15px;">Jours de retard</th>
                    <th style="padding: 15px;">Amende estimée</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($retards)): ?>
                    <tr><td colspan="5" style="padding:20px; text-align:center;">Aucun retard en cours.</td></tr>
                <?php else: ?>
                    <?php foreach ($retards as $r): ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); text-align:center;">
                        <td style="padding: 15px;"><?php echo $r['lecteur_nom']; ?> (<?php echo $r['lecteur_email']; ?>)</td>
                        <td style="padding: 15px;"><?php echo $r['titre']; ?></td>
                        <td style="padding: 15px;"><?php echo $r['date_retour_prevue']; ?></td>
                        <td style="padding: 15px; color: #e74c3c; font-weight: bold;"><?php echo $r['jours_retard']; ?> jours</td>
                        <td style="padding: 15px;"><?php echo ($r['jours_retard'] * 0.5); ?> €</td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>