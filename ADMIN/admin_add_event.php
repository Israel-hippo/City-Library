<?php
session_start();
require_once('../config/db.php');

// Sécurité : Vérifier si l'utilisateur est admin (ajuste selon ta logique de session)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: PAGE/login.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'];
    $description = $_POST['description'];
    $date_ev = $_POST['date_evenement'];

    if (!empty($titre) && !empty($date_ev)) {
        $ins = $pdo->prepare("INSERT INTO evenements (titre, description, date_evenement) VALUES (?, ?, ?)");
        if ($ins->execute([$titre, $description, $date_ev])) {
            $message = "<p style='color: #2ecc71;'>✅ Événement ajouté avec succès !</p>";
        } else {
            $message = "<p style='color: #e74c3c;'>❌ Erreur lors de l'ajout.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Événement - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #0a0a0a; color: white; font-family: 'Poppins', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .form-container { background: #111; padding: 40px; border-radius: 15px; border: 1px solid #d4a373; width: 100%; max-width: 500px; }
        h2 { color: #d4a373; font-family: 'Playfair Display'; margin-bottom: 25px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #888; font-size: 0.9rem; }
        input, textarea { width: 100%; padding: 12px; background: #1a1a1a; border: 1px solid #333; border-radius: 8px; color: white; box-sizing: border-box; }
        input:focus, textarea:focus { border-color: #d4a373; outline: none; }
        .btn-submit { background: #d4a373; color: black; border: none; width: 100%; padding: 15px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-submit:hover { background: #b88b5d; transform: translateY(-2px); }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #555; text-decoration: none; font-size: 0.8rem; }
    </style>
</head>
<body>

<div class="form-container">
    <h2><i class="fas fa-calendar-plus"></i> Nouvel Événement</h2>
    
    <?php echo $message; ?>

    <form method="POST">
        <div class="form-group">
            <label>Titre de l'événement</label>
            <input type="text" name="titre" placeholder="ex: Atelier Écriture" required>
        </div>

        <div class="form-group">
            <label>Date et Heure</label>
            <input type="datetime-local" name="date_evenement" required>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="4" placeholder="Décrivez l'événement..."></textarea>
        </div>

        <button type="submit" class="btn-submit">Publier l'événement</button>
    </form>

    <a href="ADMIN/admin_dashboard.php" class="back-link">← Retour au Dashboard</a>
</div>

</body>
</html>