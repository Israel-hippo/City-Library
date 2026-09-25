<?php
session_start();
require_once('../config/db.php');

$search = isset($_GET['q']) ? $_GET['q'] : '';

// Requête avec JOIN pour récupérer le nom de la catégorie (cat_nom)
$sql = "SELECT livres.*, categories.nom AS cat_nom 
        FROM livres 
        LEFT JOIN categories ON livres.id_categorie = categories.id 
        WHERE livres.titre LIKE :q OR livres.auteur LIKE :q";

$stmt = $pdo->prepare($sql);
$stmt->execute(['q' => "%$search%"]);
$resultats = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche - City Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --gold: #d4a373; --bg: #050505; }
        body { background: var(--bg); color: white; font-family: 'Poppins', sans-serif; margin: 0; padding: 40px; }
        
        .back-link { color: var(--gold); text-decoration: none; display: inline-block; margin-bottom: 20px; }
        
        .results-container { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
            gap: 30px; 
            margin-top: 30px; 
        }

        .book-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(212, 163, 115, 0.3);
            border-radius: 15px;
            padding: 20px;
            transition: 0.3s;
        }

        /* CORRECTION VISUELLE : L'image ne sera plus étirée */
        .book-card img {
            width: 100%;
            height: 400px;
            object-fit: cover; /* Recadre l'image proprement */
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .book-card h3 { margin: 10px 0; font-family: 'Playfair Display'; color: var(--gold); }
        .book-card p { margin: 5px 0; font-size: 0.9rem; opacity: 0.8; }

        .btn-emprunter {
            display: inline-block;
            background: var(--gold);
            color: black;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 15px;
            transition: 0.3s;
        }

        .btn-emprunter:hover { transform: scale(1.05); filter: brightness(1.1); }
        
        .no-results { text-align: center; margin-top: 50px; opacity: 0.5; }
        .btn-emprunt { display: inline-block; margin-top: 15px; background: #d4a373; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; }

    </style>
</head>
<body>

    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
    
    <h1>Résultats pour : "<?php echo htmlspecialchars($search); ?>"</h1>

    <div class="results-container">
        <?php if (count($resultats) > 0): ?>
            <?php foreach ($resultats as $livre): ?>
                <div class="book-card">
                    <img src="../img/<?php echo htmlspecialchars($livre['image_url']); ?>" alt="Couverture">
                    
                    <h3><?php echo htmlspecialchars($livre['titre']); ?></h3>
                    <p><strong>Auteur:</strong> <?php echo htmlspecialchars($livre['auteur']); ?></p>
                    
                    <p><strong>Catégorie:</strong> <?php echo htmlspecialchars($livre['cat_nom'] ?? 'Non classé'); ?></p>
                    
                    <a href="../LECTEUR/emprunter.php?id=<?php echo $livre['id']; ?>" class="btn-emprunter">Emprunter</a>

                    <a href="../LECTEUR/reservations.php?id=<?php echo $livre['id']; ?>" class="btn-emprunt" style="background-color: #d4a373;">
                        <i class="fas fa-calendar-alt"></i> Réserver ce livre
                    </a>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 20px;"></i>
                <p>Aucun livre ne correspond à votre recherche.</p>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>