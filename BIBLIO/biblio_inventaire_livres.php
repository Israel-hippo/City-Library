<?php
session_start();
require_once('../config/db.php');

// Sécurité
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'bibliothecaire' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../PAGE/login.php');
    exit();
}

// Suppression
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM livres WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: biblio_inventaire_livres.php?msg=Livre supprimé");
    exit();
}

// Récupération avec les bons noms de colonnes (SQL)
$livres = $pdo->query("SELECT * FROM livres ORDER BY titre ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inventaire - City Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --gold: #d4a373; --bg: #0b0e11; --card: rgba(255, 255, 255, 0.05); }
        body { margin: 0; background: var(--bg); color: white; font-family: 'Poppins', sans-serif; display: flex; }
        .main-content { margin-left: 260px; padding: 50px; width: calc(100% - 260px); }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .inventory-table { width: 100%; border-collapse: collapse; background: var(--card); border-radius: 15px; overflow: hidden; }
        th { text-align: left; padding: 20px; background: rgba(212, 163, 115, 0.1); color: var(--gold); text-transform: uppercase; font-size: 0.8rem; }
        td { padding: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); vertical-align: middle; }
        .book-cover { width: 50px; height: 75px; object-fit: cover; border-radius: 4px; border: 1px solid var(--gold); }
        .stock-badge { padding: 5px 12px; border-radius: 20px; font-weight: bold; font-size: 0.85rem; border: 1px solid currentColor; }
        .stock-low { color: #e74c3c; background: rgba(231, 76, 60, 0.1); }
        .stock-ok { color: #2ecc71; background: rgba(46, 204, 113, 0.1); }
        .btn-add { background: var(--gold); color: black; padding: 12px 25px; border-radius: 8px; text-decoration: none; font-weight: bold; }
        .action-link { color: #b0b0b0; margin-left: 15px; text-decoration: none; transition: 0.3s; }
        .action-link:hover { color: var(--gold); }
    </style>
</head>
<body>

    <?php include('navbar_biblio.php'); ?>

    <div class="main-content">
        <div class="header-flex">
            <h1 style="font-family: 'Playfair Display';">Gestion de <span style="color: var(--gold);">l'Inventaire</span></h1>
            <a href="biblio_ajouter_livre.php" class="btn-add"><i class="fas fa-plus"></i> Nouveau Livre</a>
        </div>

        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Couverture</th>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>Stock (Dispo / Total)</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($livres as $livre): ?>
                <tr>
                    <td>
                        <img src="../img/<?= htmlspecialchars($livre['image_url']) ?>" 
                             onerror="this.src='../img/default.jpg'" 
                             class="book-cover">
                    </td>
                    <td><strong><?= htmlspecialchars($livre['titre']) ?></strong></td>
                    <td style="color: #b0b0b0;"><?= htmlspecialchars($livre['auteur']) ?></td>
                    <td>
                        <?php $isLow = ($livre['exemplaires_dispo'] <= 2); ?>
                        <span class="stock-badge <?= $isLow ? 'stock-low' : 'stock-ok' ?>">
                            <?= $livre['exemplaires_dispo'] ?> / <?= $livre['exemplaires_total'] ?>
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <a href="biblio_modifier_livre.php?id=<?= $livre['id'] ?>" class="action-link"><i class="fas fa-edit"></i></a>
                        <a href="biblio_inventaire_livres.php?delete_id=<?= $livre['id'] ?>" 
                           class="action-link" style="color: #ff4d4d;"
                           onclick="return confirm('Supprimer ce livre ?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>