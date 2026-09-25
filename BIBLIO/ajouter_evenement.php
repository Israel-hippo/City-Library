<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'bibliothecaire' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../PAGE/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'];
    $desc = $_POST['description'];
    $date = $_POST['date_evenement'];
    $capacite = $_POST['capacite_max'];

    $stmt = $pdo->prepare("INSERT INTO evenements (titre, description, date_evenement, capacite_max) VALUES (?, ?, ?, ?)");
    $stmt->execute([$titre, $desc, $date, $capacite]);
    
    header("Location: gestion_evenements.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvel Événement - City Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --gold: #d4a373; --bg: #0b0e11; --card: rgba(255, 255, 255, 0.05); }
        body { margin: 0; background: var(--bg); color: white; font-family: 'Poppins', sans-serif; display: flex; }
        .main-content { margin-left: 260px; padding: 50px; width: calc(100% - 260px); }
        .form-container { background: var(--card); padding: 40px; border-radius: 20px; max-width: 600px; border: 1px solid rgba(212,163,115,0.2); }
        label { display: block; color: var(--gold); margin-bottom: 8px; font-weight: bold; }
        input, textarea { width: 100%; padding: 12px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 8px; margin-bottom: 20px; box-sizing: border-box; }
        .btn-save { background: var(--gold); color: black; padding: 15px; border: none; border-radius: 8px; width: 100%; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <?php include('navbar_biblio.php'); ?>
    <div class="main-content">
        <div class="form-container">
            <h1>Créer un <span style="color: var(--gold);">Événement</span></h1>
            <form action="" method="POST">
                <label>Titre de l'événement</label>
                <input type="text" name="titre" required>
                
                <label>Date et Heure</label>
                <input type="datetime-local" name="date_evenement" required>
                
                <label>Capacité Max</label>
                <input type="number" name="capacite_max" value="20" required>
                
                <label>Description</label>
                <textarea name="description" rows="5" required></textarea>
                
                <button type="submit" class="btn-save">Publier l'événement</button>
            </form>
        </div>
    </div>
</body>
</html>