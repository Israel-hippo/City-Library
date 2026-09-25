<?php
session_start();
require_once('../config/db.php');

// 1. SÉCURITÉ : Vérification des droits Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../PAGE/login.php");
    exit();
}

$message = "";
$id_event = isset($_GET['id']) ? $_GET['id'] : null;

// 2. TRAITEMENT DE LA MISE À JOUR (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event'])) {
    $id = $_POST['id'];
    $titre = $_POST['titre'];
    $date_event = $_POST['date_evenement'];
    $description = $_POST['description'];

    try {
        // On utilise l'ID pour savoir quel événement modifier
        $sql = "UPDATE evenements SET titre = ?, date_evenement = ?, description = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$titre, $date_event, $description, $id])) {
            $message = "<div class='alert success'>Événement mis à jour avec succès !</div>";
            // Petite pause avant redirection pour laisser l'admin lire le message
            header("Refresh: 2; url=admin_dashboard.php");
        }
    } catch (PDOException $e) {
        $message = "<div class='alert error'>Erreur : " . $e->getMessage() . "</div>";
    }
}

// 3. RÉCUPÉRATION DES DONNÉES ACTUELLES
if ($id_event) {
    // On utilise un alias 'titre AS titre_event' pour être raccord avec ton dashboard
    $stmt = $pdo->prepare("SELECT id, titre AS titre_event, date_evenement, description FROM evenements WHERE id = ?");
    $stmt->execute([$id_event]);
    $event = $stmt->fetch();
}

// Si l'événement n'existe pas, on dégage
if (!$event) {
    header("Location: admin_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier l'Événement - Administration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #0f0f0f; color: white; font-family: 'Poppins', sans-serif; display: flex; justify-content: center; padding: 50px 0; }
        .edit-container { background: #1a1a1a; padding: 40px; border-radius: 15px; border: 1px solid #d4a373; width: 90%; max-width: 500px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h2 { color: #d4a373; text-align: center; margin-bottom: 30px; font-family: 'Playfair Display', serif; }
        
        label { display: block; color: #888; margin-bottom: 8px; font-size: 0.9rem; }
        input, textarea { 
            width: 100%; padding: 12px; background: #0a0a0a; border: 1px solid #333; 
            border-radius: 8px; color: white; margin-bottom: 20px; box-sizing: border-box; font-size: 1rem;
        }
        input:focus, textarea:focus { border-color: #d4a373; outline: none; background: #111; }
        
        .btn-save { 
            background: #d4a373; color: black; border: none; width: 100%; padding: 15px; 
            border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 1rem;
        }
        .btn-save:hover { background: #b88b5d; transform: translateY(-2px); }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: bold; }
        .success { background: rgba(46, 204, 113, 0.1); color: #2ecc71; border: 1px solid #2ecc71; }
        .error { background: rgba(231, 76, 60, 0.1); color: #e74c3c; border: 1px solid #e74c3c; }
        
        .back-link { display: block; text-align: center; margin-top: 25px; color: #d4a373; text-decoration: none; font-size: 0.9rem; transition: 0.3s; }
        .back-link:hover { color: #fff; }
    </style>
</head>
<body>

<div class="edit-container">
    <h2><i class="fas fa-calendar-alt"></i> Modifier l'événement</h2>
    
    <?php echo $message; ?>

    <form method="POST">
        <input type="hidden" name="id" value="<?= $event['id'] ?>">

        <label>Nom de l'événement</label>
        <input type="text" name="titre" value="<?= htmlspecialchars($event['titre_event']) ?>" required>

        <label>Date et Heure</label>
        <input type="datetime-local" name="date_evenement" 
               value="<?= date('Y-m-d\TH:i', strtotime($event['date_evenement'])) ?>" required>

        <label>Description</label>
        <textarea name="description" rows="5"><?= htmlspecialchars($event['description']) ?></textarea>

        <button type="submit" name="update_event" class="btn-save">
            <i class="fas fa-check"></i> Enregistrer les modifications
        </button>
    </form>

    <a href="admin_dashboard.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Retour au tableau de bord
    </a>
</div>

</body>
</html>