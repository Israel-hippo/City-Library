<?php
session_start();
require_once('../config/db.php');

// 1. SÉCURITÉ : Vérification Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../PAGE/login.php");
    exit();
}

$message = "";
$id_livre = isset($_GET['id']) ? $_GET['id'] : null;

// 2. RÉCUPÉRATION DES CATÉGORIES (Indispensable pour le menu déroulant)
try {
    $query_cat = $pdo->query("SELECT id, nom FROM categories ORDER BY nom ASC");
    $categories = $query_cat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

// 3. TRAITEMENT DE LA MISE À JOUR (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_book'])) {
    $id = $_POST['id'];
    $titre = $_POST['titre'];
    $auteur = $_POST['auteur'];
    $isbn = $_POST['isbn'];
    $format = $_POST['format'];
    $exemplaires_total = intval($_POST['exemplaires_total']);
    
    // Récupération de la catégorie (conversion en NULL si vide)
    $id_categorie = !empty($_POST['id_categorie']) ? $_POST['id_categorie'] : null;

    try {
        // Calcul du stock disponible
        $stmt_stock = $pdo->prepare("SELECT exemplaires_total, exemplaires_dispo FROM livres WHERE id = ?");
        $stmt_stock->execute([$id]);
        $old_data = $stmt_stock->fetch();
        
        $difference = $exemplaires_total - $old_data['exemplaires_total'];
        $nouveau_dispo = $old_data['exemplaires_dispo'] + $difference;

        // REQUÊTE UNIQUE : On met tout à jour d'un coup, y compris id_categorie
        $sql = "UPDATE livres SET 
                titre = :titre, 
                auteur = :auteur, 
                isbn = :isbn, 
                format = :format, 
                exemplaires_total = :total, 
                exemplaires_dispo = :dispo,
                id_categorie = :id_cat 
                WHERE id = :id";
        
        $update = $pdo->prepare($sql);
        $params = [
            ':titre' => $titre,
            ':auteur' => $auteur,
            ':isbn' => $isbn,
            ':format' => $format,
            ':total' => $exemplaires_total,
            ':dispo' => $nouveau_dispo,
            ':id_cat' => $id_categorie, // PDO gérera le NULL correctement ici
            ':id' => $id
        ];

        if ($update->execute($params)) {
            $message = "<div class='alert success'>Livre et catégorie mis à jour avec succès !</div>";
            // On recharge les données du livre pour afficher les modifs dans le formulaire
            $stmt = $pdo->prepare("SELECT * FROM livres WHERE id = ?");
            $stmt->execute([$id]);
            $livre = $stmt->fetch();
        }
    } catch (PDOException $e) {
        $message = "<div class='alert error'>Erreur : " . $e->getMessage() . "</div>";
    }
}

// 4. RÉCUPÉRATION INITIALE (Si pas de POST)
if (!isset($livre) && $id_livre) {
    $stmt = $pdo->prepare("SELECT * FROM livres WHERE id = ?");
    $stmt->execute([$id_livre]);
    $livre = $stmt->fetch();
}

if (!$livre) {
    header("Location: admin_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le Livre - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #0f0f0f; color: white; font-family: 'Poppins', sans-serif; display: flex; justify-content: center; padding: 50px 0; }
        .edit-container { background: #1a1a1a; padding: 40px; border-radius: 15px; border: 1px solid #d4a373; width: 90%; max-width: 600px; }
        h2 { color: #d4a373; font-family: 'Playfair Display'; text-align: center; margin-bottom: 30px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }
        label { display: block; color: #888; font-size: 0.85rem; margin-bottom: 8px; }
        input, select { width: 100%; padding: 12px; background: #0a0a0a; border: 1px solid #333; border-radius: 8px; color: white; box-sizing: border-box; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .success { background: rgba(46, 204, 113, 0.2); color: #2ecc71; border: 1px solid #2ecc71; }
        .error { background: rgba(231, 76, 60, 0.2); color: #e74c3c; border: 1px solid #e74c3c; }
        .btn-update { background: #d4a373; color: black; border: none; width: 100%; padding: 15px; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 30px; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #d4a373; text-decoration: none; }
    </style>
</head>
<body>

<div class="edit-container">
    <h2><i class="fas fa-edit"></i> Modifier le Livre</h2>
    
    <?php echo $message; ?>

    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $livre['id']; ?>">

        <div class="form-grid">
            <div class="full-width">
                <label>Titre du livre</label>
                <input type="text" name="titre" value="<?php echo htmlspecialchars($livre['titre']); ?>" required>
            </div>

            <div class="full-width">
                <label>Auteur</label>
                <input type="text" name="auteur" value="<?php echo htmlspecialchars($livre['auteur']); ?>" required>
            </div>

            <div class="full-width">
                <label>Catégorie</label>
                <select name="id_categorie">
                    <option value="">-- Non classé --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id']; ?>" <?= ($livre['id_categorie'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($cat['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label>ISBN</label>
                <input type="text" name="isbn" value="<?php echo htmlspecialchars($livre['isbn']); ?>">
            </div>

            <div>
                <label>Format</label>
                <select name="format">
                    <option value="physique" <?= ($livre['format'] == 'physique') ? 'selected' : ''; ?>>Physique</option>
                    <option value="ebook" <?= ($livre['format'] == 'ebook') ? 'selected' : ''; ?>>E-book</option>
                    <option value="audiobook" <?= ($livre['format'] == 'audiobook') ? 'selected' : ''; ?>>Audiobook</option>
                </select>
            </div>

            <div>
                <label>Stock Total</label>
                <input type="number" name="exemplaires_total" value="<?php echo $livre['exemplaires_total']; ?>">
            </div>

            <div>
                <label>Stock Actuel (Aperçu)</label>
                <input type="text" value="<?php echo $livre['exemplaires_dispo']; ?> disponible(s)" disabled style="opacity: 0.5;">
            </div>
        </div>

        <button type="submit" name="update_book" class="btn-update">Sauvegarder les modifications</button>
    </form>

    <a href="admin_dashboard.php" class="back-link">← Retour à l'inventaire</a>
</div>

</body>
</html>