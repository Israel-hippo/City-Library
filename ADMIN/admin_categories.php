<?php
session_start();
require_once('../config/db.php');

// Sécurité : Seul l'admin accède
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: PAGE/login.php');
    exit();
}

// Ajouter une catégorie
if (isset($_POST['ajouter'])) {
    $nom = $_POST['nom_categorie'];
    $stmt = $pdo->prepare("INSERT INTO categories (nom) VALUES (?)");
    $stmt->execute([$nom]);
}

// Supprimer une catégorie
if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: ADMIN/admin_categories.php');
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Catégories - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body style="background: #02060a; color: white; padding: 50px;">

    <div style="display:flex; justify-content:space-between; align-items:center;">
    <h2 style="color: var(--gold);"><i class="fas fa-exclamation-triangle"></i> Gestion des Catégories</h2>
        <a href="ADMIN/admin_dashboard.php" class="btn-browse outline">Tableau de bord</a>
    </div>    

    <div class="card" style="max-width: 400px; margin-bottom: 30px;">
        <form method="POST">
            <input type="text" name="nom_categorie" placeholder="Nom de la catégorie (ex: Fiction)" required style="width: 80%; padding: 10px;">
            <button type="submit" name="ajouter" class="btn-browse gold">Ajouter</button>
        </form>
    </div>

    <table style="width: 100%; border-collapse: collapse; background: rgba(255,255,255,0.05);">
        <thead>
            <tr style="border-bottom: 2px solid var(--gold);">
                <th style="padding: 15px; text-align: left;">ID</th>
                <th style="padding: 15px; text-align: left;">Nom</th>
                <th style="padding: 15px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <td style="padding: 15px;"><?php echo $cat['id']; ?></td>
                <td style="padding: 15px;"><?php echo htmlspecialchars($cat['nom']); ?></td>
                <td style="padding: 15px; text-align: center;">
                    <a href="ADMIN/admin_categories.php?supprimer=<?php echo $cat['id']; ?>" 
                       onclick="return confirm('Supprimer cette catégorie ?')" 
                       style="color: #ff4d4d; text-decoration: none;">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>