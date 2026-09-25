<?php
session_start();
require_once('../config/db.php');

// On récupère l'ID du livre depuis l'URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Requête pour récupérer les détails du livre et sa catégorie
$stmt = $pdo->prepare("SELECT livres.*, categories.nom AS cat_nom 
                       FROM livres 
                       LEFT JOIN categories ON livres.id_categorie = categories.id 
                       WHERE livres.id = ?");
$stmt->execute([$id]);
$livre = $stmt->fetch();

if (!$livre) {
    die("Livre non trouvé.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $livre['titre']; ?> - City Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #0a0a0a; color: white; font-family: 'Poppins', sans-serif; padding: 50px; }
        .detail-container { display: flex; gap: 50px; max-width: 1000px; margin: auto; background: rgba(255,255,255,0.05); padding: 30px; border-radius: 20px; }
        .detail-img { width: 350px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .detail-info h1 { font-family: 'Playfair Display'; color: #d4a373; font-size: 3rem; margin-top: 0; }
        .badge { background: #d4a373; color: black; padding: 5px 15px; border-radius: 5px; font-weight: bold; }
        .btn-emprunt { display: inline-block; margin-top: 30px; background: #d4a373; color: black; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin-right: 15px; transition: 0.3s; }
        .btn-emprunt:hover { opacity: 0.9; transform: translateY(-2px); }
        
        /* Style pour le message d'indisponibilité */
        .msg-indisponible { display: inline-flex; align-items: center; gap: 10px; margin-top: 30px; background: rgba(231, 76, 60, 0.15); color: #e74c3c; border: 1px solid #e74c3c; padding: 15px 25px; border-radius: 8px; font-weight: 600; }
    </style>
</head>
<body>

    <a href="../index.php" style="color: #d4a373; text-decoration: none;"><i class="fas fa-arrow-left"></i> Retour</a>

    <div class="detail-container">
        <img src="../img/<?php echo $livre['image_url']; ?>" class="detail-img">
        <div class="detail-info">
            <span class="badge"><?php echo strtoupper($livre['format']); ?></span>
            <h1><?php echo htmlspecialchars($livre['titre']); ?></h1>
            <p style="font-size: 1.5rem; opacity: 0.8;">Auteur : <strong><?php echo htmlspecialchars($livre['auteur']); ?></strong></p>
            <p>Catégorie : <?php echo htmlspecialchars($livre['cat_nom'] ?? 'Non classé'); ?></p>
            
            <?php if ($livre['exemplaires_dispo'] > 0): ?>
                
                <a href="../LECTEUR/emprunter.php?id=<?php echo $livre['id']; ?>" class="btn-emprunt">
                    <i class="fas fa-bookmark"></i> Emprunter ce livre
                </a>

                <a href="../LECTEUR/reservations.php?id=<?php echo $livre['id']; ?>" class="btn-emprunt">
                    <i class="fas fa-calendar-alt"></i> Réserver ce livre
                </a>

            <?php else: ?>
                
                <div class="msg-indisponible">
                    <i class="fas fa-exclamation-triangle"></i> Ce livre est actuellement indisponible pour l'emprunt ou la réservation.
                </div>

            <?php endif; ?>
        </div>
    </div>

</body>
</html>