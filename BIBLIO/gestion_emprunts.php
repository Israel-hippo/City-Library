<?php
session_start();
require_once('../config/db.php');

// Sécurité : Accès réservé au personnel
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'bibliothecaire' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../PAGE/login.php');
    exit();
}

try {
    // Requête pour récupérer les emprunts avec les noms des lecteurs et les titres des livres
    // On ne récupère que ceux qui n'ont pas encore été rendus (date_retour IS NULL)
    $sql = "SELECT e.*, u.nom AS lecteur_nom, l.titre AS livre_titre 
            FROM emprunts e
            JOIN utilisateurs u ON e.id_utilisateur = u.id
            JOIN livres l ON e.id_livre = l.id
            WHERE e.date_retour IS NULL
            ORDER BY e.date_emprunt DESC";
    
    $emprunts = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    $erreur = "Erreur SQL : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Emprunts - City Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --gold: #d4a373; --bg: #0b0e11; --card: rgba(255, 255, 255, 0.05); }
        body { margin: 0; background: var(--bg); color: white; font-family: 'Poppins', sans-serif; display: flex; }
        
        .main-content { margin-left: 260px; padding: 50px; width: calc(100% - 260px); }
        
        h1 { font-family: 'Playfair Display', serif; font-size: 2.2rem; margin-bottom: 30px; }
        
        .emprunt-table { width: 100%; border-collapse: collapse; background: var(--card); border-radius: 15px; overflow: hidden; }
        th { text-align: left; padding: 20px; background: rgba(212, 163, 115, 0.1); color: var(--gold); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
        td { padding: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        
        /* Badges de statut */
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; }
        .on-time { background: rgba(46, 204, 113, 0.1); color: #2ecc71; border: 1px solid #2ecc71; }
        .late { background: rgba(231, 76, 60, 0.1); color: #e74c3c; border: 1px solid #e74c3c; }

        .btn-action { 
            background: var(--gold); color: black; padding: 8px 15px; border-radius: 6px; 
            text-decoration: none; font-weight: bold; font-size: 0.85rem; transition: 0.3s;
        }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(212, 163, 115, 0.2); }
    </style>
</head>
<body>

    <?php include('navbar_biblio.php'); ?>

    <div class="main-content">
        <h1>Suivi des <span style="color: var(--gold);">Emprunts Actifs</span></h1>

        <?php if(empty($emprunts)): ?>
            <p style="opacity: 0.5;">Aucun emprunt en cours actuellement.</p>
        <?php else: ?>
            <table class="emprunt-table">
                <thead>
                    <tr>
                        <th>Lecteur</th>
                        <th>Livre</th>
                        <th>Date d'emprunt</th>
                        <th>Date de retour prévue</th>
                        <th>Statut</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($emprunts as $e): ?>
                    <?php 
                        // Vérification du retard
                        $today = date('Y-m-d');
                        $isLate = ($today > $e['date_retour_prevue']);
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($e['lecteur_nom']) ?></strong></td>
                        <td style="color: #b0b0b0;"><?= htmlspecialchars($e['livre_titre']) ?></td>
                        <td><?= date('d/m/Y', strtotime($e['date_emprunt'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($e['date_retour_prevue'])) ?></td>
                        <td>
                            <?php if($isLate): ?>
                                <span class="status-badge late"><i class="fas fa-exclamation-triangle"></i> En retard</span>
                            <?php else: ?>
                                <span class="status-badge on-time"><i class="fas fa-check"></i> En cours</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <a href="gestion_retours.php?id_emprunt=<?= $e['id'] ?>" class="btn-action">
                                <i class="fas fa-undo"></i> Enregistrer Retour
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>
</html>