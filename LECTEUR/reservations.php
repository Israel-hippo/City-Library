<?php
session_start();
require_once('../config/db.php');

// 1. SÉCURITÉ : L'utilisateur doit être connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../PAGE/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$id_livre = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. RÉCUPÉRATION DES INFOS DU LIVRE
$stmt = $pdo->prepare("SELECT * FROM livres WHERE id = ?");
$stmt->execute([$id_livre]);
$livre = $stmt->fetch();

if (!$livre) {
    header("Location: ../index.php");
    exit();
}

$error = "";

// 3. LOGIQUE DE RÉSERVATION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_reservation = $_POST['date_reservation'];
    
    // Vérifier si une réservation existe déjà pour cet utilisateur et ce livre
    $check = $pdo->prepare("SELECT id FROM reservations WHERE id_utilisateur = ? AND id_livre = ? AND statut = 'en_attente'");
    $check->execute([$user_id, $id_livre]);

    if ($check->rowCount() > 0) {
        $error = "Vous avez déjà une réservation en attente pour ce titre.";
    } else {
        $ins = $pdo->prepare("INSERT INTO reservations (id_utilisateur, id_livre, date_reservation) VALUES (?, ?, ?)");
        if ($ins->execute([$user_id, $id_livre, $date_reservation])) {
            header("Location: profile.php?status=res_ok");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réserver - City Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --gold: #d4a373; --dark: #0a0a0a; --card: #1a1a1a; }
        
        body {
            background: var(--dark);
            color: white;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .reservation-container {
            background: var(--card);
            width: 90%;
            max-width: 500px;
            padding: 40px;
            border-radius: 20px;
            border: 1px solid rgba(212, 163, 115, 0.2);
            box-shadow: 0 20px 40px rgba(0,0,0,0.6);
            text-align: center;
        }

        .book-preview {
            margin-bottom: 25px;
        }

        .book-preview img {
            width: 140px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.5);
            border: 2px solid var(--gold);
        }

        h2 { font-family: 'Playfair Display'; color: var(--gold); font-size: 1.8rem; margin: 15px 0 5px; }
        p.subtitle { color: #888; font-size: 0.9rem; margin-bottom: 30px; }

        .form-group { text-align: left; margin-bottom: 20px; }
        label { display: block; font-size: 0.8rem; color: var(--gold); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; }

        input[type="date"] {
            width: 100%;
            padding: 12px;
            background: rgba(0,0,0,0.2);
            border: 1px solid #333;
            border-radius: 8px;
            color: white;
            font-family: inherit;
            box-sizing: border-box;
        }

        input[type="date"]:focus { border-color: var(--gold); outline: none; }

        .btn-confirm {
            background: var(--gold);
            color: black;
            border: none;
            width: 100%;
            padding: 15px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-confirm:hover { background: #b88b5d; transform: translateY(-2px); }

        .error-msg { background: rgba(255, 77, 77, 0.1); color: #ff4d4d; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 0.85rem; }
        
        .back-btn { display: inline-block; margin-top: 20px; color: #666; text-decoration: none; font-size: 0.85rem; transition: 0.3s; }
        .back-btn:hover { color: var(--gold); }
    </style>
</head>
<body>

<div class="reservation-container">
    <div class="book-preview">
        <img src="../img/<?php echo htmlspecialchars($livre['image_url']); ?>" alt="Couverture">
        <h2><?php echo htmlspecialchars($livre['titre']); ?></h2>
        <p class="subtitle">Par <?php echo htmlspecialchars($livre['auteur']); ?></p>
    </div>

    <?php if($error): ?>
        <div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Choisir la date de retrait</label>
            <input type="date" name="date_reservation" min="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <button type="submit" class="btn-confirm">
            <i class="fas fa-calendar-check"></i> Confirmer la réservation
        </button>
    </form>

    <a href="../PAGE/book_details.php?id=<?php echo $id_livre; ?>" class="back-btn">
        <i class="fas fa-arrow-left"></i> Annuler et revenir au livre
    </a>
</div>

</body>
</html>