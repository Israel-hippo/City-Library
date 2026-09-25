<?php
session_start();
require_once('../config/db.php');

// Sécurité : Accès réservé au personnel
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'bibliothecaire' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../PAGE/login.php');
    exit();
}

try {
    // Récupération de tous les utilisateurs ayant le rôle 'lecteur'
    // On trie par date d'inscription (les plus récents en premier)
    $sql = "SELECT * FROM utilisateurs WHERE role = 'lecteur' ORDER BY date_inscription DESC";
    $lecteurs = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    $erreur = "Erreur SQL : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Lecteurs - City Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --gold: #d4a373; --bg: #0b0e11; --card: rgba(255, 255, 255, 0.05); }
        body { margin: 0; background: var(--bg); color: white; font-family: 'Poppins', sans-serif; display: flex; }
        
        .main-content { margin-left: 260px; padding: 50px; width: calc(100% - 260px); }
        
        h1 { font-family: 'Playfair Display', serif; font-size: 2.2rem; margin-bottom: 10px; }
        .subtitle { color: #b0b0b0; margin-bottom: 40px; }
        
        .user-table { width: 100%; border-collapse: collapse; background: var(--card); border-radius: 15px; overflow: hidden; }
        th { text-align: left; padding: 20px; background: rgba(212, 163, 115, 0.1); color: var(--gold); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
        td { padding: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        tr:hover { background: rgba(255, 255, 255, 0.02); }

        .avatar-circle {
            width: 40px; height: 40px; background: var(--gold); color: black; 
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: bold; margin-right: 15px;
        }

        .status-pill { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; }
        .status-active { background: rgba(46, 204, 113, 0.1); color: #2ecc71; border: 1px solid #2ecc71; }
        .status-pending { background: rgba(212, 163, 115, 0.1); color: var(--gold); border: 1px solid var(--gold); }

        .btn-view { color: var(--gold); text-decoration: none; font-size: 1.1rem; }
    </style>
</head>
<body>

    <?php include('navbar_biblio.php'); ?>

    <div class="main-content">
        <h1>Annuaire des <span style="color: var(--gold);">Lecteurs</span></h1>
        <p class="subtitle">Consultez la liste des membres et leur état d'adhésion.</p>

        <?php if(empty($lecteurs)): ?>
            <p style="opacity: 0.5;">Aucun lecteur inscrit pour le moment.</p>
        <?php else: ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Membre</th>
                        <th>Email</th>
                        <th>Date d'adhésion</th>
                        <th>Statut</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lecteurs as $l): ?>
                    <tr>
                        <td style="display: flex; align-items: center;">
                            <div class="avatar-circle"><?= strtoupper(substr($l['nom'], 0, 1)) ?></div>
                            <strong><?= htmlspecialchars($l['nom']) ?></strong>
                        </td>
                        <td style="color: #b0b0b0;"><?= htmlspecialchars($l['email']) ?></td>
                        <td><?= date('d/m/Y', strtotime($l['date_inscription'])) ?></td>
                        <td>
                            <?php if($l['approuve'] == 1): ?>
                                <span class="status-pill status-active">Actif / Approuvé</span>
                            <?php else: ?>
                                <span class="status-pill status-pending">En attente de validation</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <a href="voir_profil_lecteur.php?id=<?= $l['id'] ?>" class="btn-view" title="Voir l'historique">
                                <i class="fas fa-external-link-alt"></i>
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