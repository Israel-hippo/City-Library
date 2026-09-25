<?php
session_start();
require_once('../config/db.php');

// Sécurité : Accès réservé aux bibliothécaires et admins
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'bibliothecaire' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../PAGE/login.php');
    exit();
}

$message = "";
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: biblio_inventaire_livres.php");
    exit();
}

// Récupération des informations actuelles du livre
$stmt = $pdo->prepare("SELECT * FROM livres WHERE id = ?");
$stmt->execute([$id]);
$livre = $stmt->fetch();

if (!$livre) {
    header("Location: biblio_inventaire_livres.php");
    exit();
}

// Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'];
    $auteur = $_POST['auteur'];
    $isbn = $_POST['isbn'];
    $format = $_POST['format'];
    $exemplaires_total = $_POST['exemplaires_total'];
    $exemplaires_dispo = $_POST['exemplaires_dispo'];
    $id_categorie = $_POST['id_categorie'];
    $image_name = $livre['image_url']; // Par défaut, on garde l'ancienne image

    // Gestion d'une nouvelle image si uploadée
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $image_name = $_FILES['image']['name'];
        $target = "../img/couvertures/" . basename($image_name);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    }

    try {
        $sql = "UPDATE livres SET 
                titre = ?, auteur = ?, isbn = ?, format = ?, 
                exemplaires_total = ?, exemplaires_dispo = ?, 
                id_categorie = ?, image_url = ? 
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $titre, $auteur, $isbn, $format, 
            $exemplaires_total, $exemplaires_dispo, 
            $id_categorie, $image_name, $id
        ]);
        
        header("Location: biblio_inventaire_livres.php?msg=Livre mis à jour avec succès");
        exit();
    } catch (PDOException $e) {
        $message = "Erreur lors de la modification : " . $e->getMessage();
    }
}

// Récupération des catégories
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Livre - City Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --gold: #d4a373; --bg: #0b0e11; --card: rgba(255, 255, 255, 0.05); }
        body { margin: 0; background: var(--bg); color: white; font-family: 'Poppins', sans-serif; display: flex; }
        .main-content { margin-left: 260px; padding: 50px; width: calc(100% - 260px); }
        .form-container { 
            background: var(--card); padding: 40px; border-radius: 20px; 
            border: 1px solid rgba(212, 163, 115, 0.2); max-width: 800px;
        }
        h1 { font-family: 'Playfair Display', serif; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: var(--gold); font-weight: bold; font-size: 0.9rem; }
        input, select { 
            width: 100%; padding: 12px; background: rgba(0,0,0,0.3); 
            border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 8px; 
        }
        .btn-submit { 
            background: var(--gold); color: black; padding: 15px 30px; border: none; 
            border-radius: 8px; font-weight: bold; cursor: pointer; width: 100%; margin-top: 20px;
        }
        .btn-back { color: #b0b0b0; text-decoration: none; display: inline-block; margin-bottom: 20px; }
        .current-img { width: 60px; height: 90px; object-fit: cover; border-radius: 4px; margin-top: 10px; border: 1px solid var(--gold); }
    </style>
</head>
<body>

    <?php include('navbar_biblio.php'); ?>

    <div class="main-content">
        <a href="biblio_inventaire_livres.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour</a>
        
        <div class="form-container">
            <h1>Modifier le livre : <span style="color: var(--gold);"><?= htmlspecialchars($livre['titre']) ?></span></h1>
            
            <form action="" method="POST" enctype="multipart/form-data">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Titre</label>
                        <input type="text" name="titre" value="<?= htmlspecialchars($livre['titre']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Auteur</label>
                        <input type="text" name="auteur" value="<?= htmlspecialchars($livre['auteur']) ?>" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>ISBN</label>
                        <input type="text" name="isbn" value="<?= htmlspecialchars($livre['isbn']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Catégorie</label>
                        <select name="id_categorie" required>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $livre['id_categorie']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Exemplaires Total</label>
                        <input type="number" name="exemplaires_total" value="<?= $livre['exemplaires_total'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Exemplaires Disponibles</label>
                        <input type="number" name="exemplaires_dispo" value="<?= $livre['exemplaires_dispo'] ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Format</label>
                    <select name="format">
                        <option value="physique" <?= ($livre['format'] == 'physique') ? 'selected' : '' ?>>Physique</option>
                        <option value="ebook" <?= ($livre['format'] == 'ebook') ? 'selected' : '' ?>>E-book</option>
                        <option value="audiobook" <?= ($livre['format'] == 'audiobook') ? 'selected' : '' ?>>Audiobook</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Changer la couverture (laisser vide pour garder l'actuelle)</label>
                    <input type="file" name="image" accept="image/*">
                    <img src="../img/<?= htmlspecialchars($livre['image_url']) ?>" class="current-img" alt="Actuelle">
                </div>

                <button type="submit" class="btn-submit">Enregistrer les modifications</button>
            </form>
        </div>
    </div>
</body>
</html>