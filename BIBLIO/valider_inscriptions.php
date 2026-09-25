<?php
session_start();
require_once('../config/db.php');

// Action de validation si on clique sur le bouton
if (isset($_GET['approve_id'])) {
    $stmt = $pdo->prepare("UPDATE utilisateurs SET approuve = 1 WHERE id = ?");
    $stmt->execute([$_GET['approve_id']]);
    header("Location: valider_inscriptions.php");
    exit();
}

// Récupération des lecteurs qui attendent (approuve = 0)
$attente = $pdo->query("SELECT * FROM utilisateurs WHERE role = 'lecteur' AND approuve = 0 ORDER BY date_inscription DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Validation Inscriptions - City Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --gold: #d4a373; --bg: #121212; --card: rgba(255, 255, 255, 0.05); }
        body { margin: 0; background: var(--bg); color: white; font-family: 'Poppins', sans-serif; display: flex; }
        
        .main-content { margin-left: 260px; padding: 50px; width: calc(100% - 260px); }
        
        h1 { font-family: 'Playfair Display', serif; font-size: 2.5rem; margin-bottom: 10px; }
        .subtitle { color: #b0b0b0; margin-bottom: 40px; }

        table { width: 100%; border-collapse: collapse; background: var(--card); border-radius: 15px; overflow: hidden; }
        th { text-align: left; padding: 20px; background: rgba(212, 163, 115, 0.1); color: var(--gold); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; }
        td { padding: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        tr:hover { background: rgba(212, 163, 115, 0.05); }

        .btn-approve { 
            background: #27ae60; 
            color: white; 
            padding: 10px 20px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-weight: bold; 
            font-size: 0.9rem; 
            transition: 0.3s;
            display: inline-block;
        }
        .btn-approve:hover { background: #219150; transform: scale(1.05); }
        .empty-msg { text-align: center; padding: 50px; opacity: 0.5; font-style: italic; }
    </style>
</head>
<body>

    <?php include('navbar_biblio.php'); ?>

    <div class="main-content">
        <h1>Validation des <span style="color: var(--gold);">Inscriptions</span></h1>
        <p class="subtitle">Veuillez valider le compte uniquement après réception de la fiche signée.</p>

        <table>
            <thead>
                <tr>
                    <th>Nom du Lecteur</th>
                    <th>Email</th>
                    <th>Date d'inscription</th>
                    <th style="text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($attente)): ?>
                    <tr><td colspan="4" class="empty-msg">Aucune inscription en attente de validation.</td></tr>
                <?php else: ?>
                    <?php foreach($attente as $u): ?>
                    <tr>
                        <td style="font-weight: bold;"><?= htmlspecialchars($u['nom']) ?></td>
                        <td style="color: #b0b0b0;"><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($u['date_inscription'])) ?></td>
                        <td style="text-align: center;">
                            <a href="valider_inscriptions.php?approve_id=<?= $u['id'] ?>" 
                               class="btn-approve" 
                               onclick="return confirm('Confirmez-vous la réception de la fiche papier pour <?= htmlspecialchars($u['nom']) ?> ?')">
                                <i class="fas fa-check-circle"></i> Valider la fiche
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