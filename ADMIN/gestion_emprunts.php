<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'bibliothecaire')) { header('Location: PAGE/login.php'); exit(); }

// On récupère tous les emprunts non encore rendus
$sql = "SELECT e.id as id_emprunt, e.date_retour_prevue, l.titre, u.nom as lecteur 
        FROM emprunts e 
        JOIN livres l ON e.id_livre = l.id 
        JOIN utilisateurs u ON e.id_utilisateur = u.id 
        WHERE date_emprunt is not null
        ORDER BY e.date_retour_prevue ASC";
$emprunts_actifs = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Emprunts - City Library</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="background: #02060a; color: white; padding: 40px;">

    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 style="color:var(--gold); font-family:'Playfair Display';">Retours de livres</h2>
        <a href="admin_dashboard.php" class="btn-browse outline">Tableau de bord</a>
    </div>

    <div class="card" style="margin-top:30px; background: rgba(255,255,255,0.02);">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--gold); text-align: left;">
                    <th style="padding:15px;">Lecteur</th>
                    <th style="padding:15px;">Livre</th>
                    <th style="padding:15px;">Date Limite</th>
                    <th style="padding:15px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($emprunts_actifs)): ?>
                    <tr><td colspan="4" style="padding:20px; text-align:center;">Aucun emprunt en cours.</td></tr>
                <?php else: ?>
                    <?php foreach($emprunts_actifs as $emp): ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                        <td style="padding:15px;"><?php echo htmlspecialchars($emp['lecteur']); ?></td>
                        <td style="padding:15px;"><?php echo htmlspecialchars($emp['titre']); ?></td>
                        <td style="padding:15px; <?php echo (strtotime($emp['date_retour_prevue']) < time()) ? 'color:#e74c3c; font-weight:bold;' : ''; ?>">
                            <?php echo date('d/m/Y', strtotime($emp['date_retour_prevue'])); ?>
                        </td>
                        <td style="padding:15px;">
                            <a href="retourner.php?id_emprunt=<?php echo $emp['id_emprunt']; ?>" 
                               class="btn-browse gold" style="padding:5px 15px; font-size:0.8rem;"
                               onclick="return confirm('Confirmer le retour de ce livre ?')">
                               Marquer comme Rendu
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>