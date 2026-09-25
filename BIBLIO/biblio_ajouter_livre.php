<?php
session_start();
require_once('../config/db.php');

// Sécurité : Accès réservé aux bibliothécaires et admins
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'bibliothecaire' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../PAGE/login.php');
    exit();
}

$message = "";

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'];
    $auteur = $_POST['auteur'];
    $isbn = $_POST['isbn'];
    $format = $_POST['format'];
    $exemplaires_total = $_POST['exemplaires_total'];
    $id_categorie = $_POST['id_categorie'];
    
    // Par défaut, au moment de l'ajout, les exemplaires dispos = exemplaires total
    $exemplaires_dispo = $exemplaires_total;

    // Gestion de l'image
    $image_name = "default.jpg";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $image_name = $_FILES['image']['name'];
        $target = "../img/couvertures/" . basename($image_name);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    }

    try {
        $sql = "INSERT INTO livres (titre, auteur, isbn, format, exemplaires_total, exemplaires_dispo, id_categorie, image_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$titre, $auteur, $isbn, $format, $exemplaires_total, $exemplaires_dispo, $id_categorie, $image_name]);
        
        header("Location: biblio_inventaire_livres.php?msg=Livre ajouté avec succès");
        exit();
    } catch (PDOException $e) {
        $message = "Erreur lors de l'ajout : " . $e->getMessage();
    }
}

// Récupération des catégories pour le menu déroulant
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Livre - City Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --gold: #d4a373; --bg: #0b0e11; --card: rgba(255, 255, 255, 0.05); }
        body { margin: 0; background: var(--bg); color: white; font-family: 'Poppins', sans-serif; display: flex; }
        
        .main-content { margin-left: 260px; padding: 50px; width: calc(100% - 260px); }
        
        .form-container { 
            background: var(--card); 
            padding: 40px; 
            border-radius: 20px; 
            border: 1px solid rgba(212, 163, 115, 0.2);
            max-width: 800px;
        }

        h1 { font-family: 'Playfair Display', serif; margin-bottom: 30px; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: var(--gold); font-weight: bold; font-size: 0.9rem; }
        
        input, select, textarea { 
            width: 100%; 
            padding: 12px; 
            background: rgba(0,0,0,0.3); 
            border: 1px solid rgba(255,255,255,0.1); 
            color: white; 
            border-radius: 8px; 
            box-sizing: border-box;
        }
        
        input:focus { border-color: var(--gold); outline: none; }

        .btn-submit { 
            background: var(--gold); 
            color: black; 
            padding: 15px 30px; 
            border: none; 
            border-radius: 8px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: 0.3s;
            width: 100%;
            margin-top: 20px;
        }
        .btn-submit:hover { transform: scale(1.02); box-shadow: 0 5px 15px rgba(212, 163, 115, 0.2); }
        
        .btn-back { color: #b0b0b0; text-decoration: none; display: inline-block; margin-bottom: 20px; }
    </style>
</head>
<body>

    <?php include('navbar_biblio.php'); ?>

    <div class="main-content">
        <a href="biblio_inventaire_livres.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour à l'inventaire</a>
        
        <div class="form-container">
            <h1>Ajouter un <span style="color: var(--gold);">Nouveau Livre</span></h1>
            
            <?php if($message): ?>
                <p style="color: #ff4d4d;"><?= $message ?></p>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Titre du livre</label>
                        <input type="text" name="titre" required placeholder="Ex: Harry Potter">
                    </div>
                    <div class="form-group">
                        <label>Auteur</label>
                        <input type="text" name="auteur" required placeholder="Ex: J.K. Rowling">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>ISBN / Code</label>
                        <input type="text" name="isbn" required placeholder="Ex: 978-001">
                    </div>
                    <div class="form-group">
                        <label>Catégorie</label>
                        <select name="id_categorie" required>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Format</label>
                        <select name="format">
                            <option value="physique">Livre Physique</option>
                            <option value="ebook">E-book</option>
                            <option value="audiobook">Livre Audio</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nombre d'exemplaires total</label>
                        <input type="number" name="exemplaires_total" min="1" value="1" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Image de couverture</label>
                    <input type="file" name="image" accept="image/*">
                    <small style="color: #666;">L'image sera stockée dans le dossier img/couvertures/</small>
                </div>

                <button type="submit" class="btn-submit">Enregistrer dans l'inventaire</button>
            </form>
        </div>
    </div>

</body>
</html>