<?php
session_start();
require_once('../config/db.php');

// SÉCURITÉ : Seul l'admin peut ajouter des livres
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: PAGE/login.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titre = $_POST['titre'];
    $auteur = $_POST['auteur'];
    
    // Gestion de l'image
    $image_name = $_FILES['image']['name'];
    $target_dir = "../img/";
    $target_file = $target_dir . basename($image_name);

    // Déplacement de l'image dans le dossier img/
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $stmt = $pdo->prepare("INSERT INTO livres (titre, auteur, image_url, id_categorie) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$titre, $auteur, $image_name, $_POST['id_categorie']])) {
            $message = "<p style='color: #2ecc71;'>Livre ajouté avec succès !</p>";
        }
    } else {
        $message = "<p style='color: #e74c3c;'>Erreur lors du téléchargement de l'image.</p>";
    }
}
?>

<?php
// ... (tes inclusions et connexion PDO)

try {
    // Force la récupération en tableau associatif
    $query_cat = $pdo->query("SELECT id, nom FROM categories ORDER BY nom ASC");
    $categories = $query_cat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = []; // Évite l'erreur si la table n'existe pas
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Livre - City Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --gold: #d4a373; }
        body {
            background: #121212;
            color: white;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.05);
            padding: 40px;
            border-radius: 15px;
            border: 1px solid var(--gold);
            width: 400px;
        }
        input, button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 5px;
            border: none;
            box-sizing: border-box;
        }


.form-control-luxe {
    width: 100%;
    padding: 12px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(212, 163, 115, 0.3);
    color: white;
    border-radius: 8px;
    appearance: none; /* Enlève le style par défaut */
    cursor: pointer;
}

.form-control-luxe option {
    background: #1a1a1a; /* Fond sombre pour les options */
    color: white;
}

.form-control-luxe:focus {
    border-color: var(--gold);
    outline: none;
    box-shadow: 0 0 10px rgba(212, 163, 115, 0.2);
}


        input[type="text"], input[type="file"] {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        button {
            background: var(--gold);
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover { filter: brightness(1.2); }
        .back-link { display: block; text-align: center; color: var(--gold); text-decoration: none; margin-top: 15px; }
    </style>
</head>
<body>

    <div class="form-container">
        <h2 style="text-align: center; font-family: 'Playfair Display';">Ajouter un Nouveau Livre</h2>
        <?php echo $message; ?>
        <label>Format du livre :</label>
        <select name="format" required>
            <option value="physique">Livre Physique</option>
            <option value="ebook">eBook (Numérique)</option>
            <option value="audiobook">Audiobook (Audio)</option>
        </select>
        <br></br>
<div class="form-group">
    <label for="id_categorie">Catégorie</label>
    <select name="id_categorie" id="id_categorie" class="form-control-luxe">
        <option value="">-- Sélectionner une catégorie --</option>
        
        <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" 
                    <?php 
                        // On garde la sélection si on modifie un livre existant
                        if (isset($livre['id_categorie']) && $livre['id_categorie'] == $cat['id']) {
                            echo 'selected';
                        } 
                    ?>>
                    <?php echo htmlspecialchars($cat['nom']); ?>
                </option>
            <?php endforeach; ?>
        <?php else: ?>
            <option value="">(Aucune catégorie trouvée en base)</option>
        <?php endif; ?>
    </select>
</div>
    
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="text" name="titre" placeholder="Titre du livre" required>
            <input type="text" name="auteur" placeholder="Auteur" required>
            <label style="font-size: 0.8rem; opacity: 0.7;">Image de couverture :</label>
            <input type="file" name="image" accept="image/*" required>
            <button type="submit">Enregistrer le livre</button>
        </form>
    
        <a href="admin_dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour au Panel Admin</a>
    </div>

</body>
</html>