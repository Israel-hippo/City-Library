<?php
session_start();
require_once('../config/db.php');

// Récupération des événements
try {
    $query = $pdo->query("SELECT * FROM evenements ORDER BY date_evenement ASC");
    $events = $query->fetchAll();
} catch (PDOException $e) {
    $events = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - City Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Poppins:wght@300;400;600&display=swap');

        :root { --gold: #d4a373; --dark: #0f0f0f; }

        body {
            margin: 0; padding: 0;
            background: linear-gradient(rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.85)), url('img/library-bg.jpg') no-repeat center center fixed;
            background-size: cover; color: white; font-family: 'Poppins', sans-serif;
            display: flex; flex-direction: column; align-items: center; min-height: 100vh;
        }

        /* --- NAVIGATION --- */
        header {
            width: 100%; display: flex; justify-content: space-between; align-items: center;
            padding: 20px 8%; box-sizing: border-box; background: rgba(0,0,0,0.6); backdrop-filter: blur(10px);
        }
        .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; color: white; }
        .logo i { color: var(--gold); }
        .nav-links { display: flex; list-style: none; gap: 30px; margin: 0; }
        .nav-links a { color: white; text-decoration: none; font-size: 0.9rem; transition: 0.3s; }
        .nav-links a:hover { color: var(--gold); }

        /* --- TOAST NOTIFICATION --- */
        .toast-container {
            position: fixed; top: 20px; right: 20px; z-index: 9999;
        }
        .toast {
            background: #1a1a1a; color: white; padding: 18px 25px; border-radius: 10px;
            border-left: 5px solid var(--gold); box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            display: flex; align-items: center; gap: 15px; margin-bottom: 10px;
            transform: translateX(120%); transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        .toast.show { transform: translateX(0); }
        .toast i { color: var(--gold); font-size: 1.4rem; }

        /* --- CARTES --- */
        .page-title { text-align: center; margin: 60px 0; }
        .page-title h1 { font-family: 'Playfair Display'; font-size: 3.5rem; color: var(--gold); margin: 0; }

        .events-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px; width: 85%; max-width: 1200px; margin-bottom: 50px;
        }
        .event-card {
            background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(212, 163, 115, 0.2);
            border-radius: 15px; padding: 40px; text-align: center; aspect-ratio: 1/1;
            display: flex; flex-direction: column; justify-content: center; transition: 0.3s;
        }
        .event-card:hover { border-color: var(--gold); transform: translateY(-10px); background: rgba(255,255,255,0.08); }
        
        .event-date { color: var(--gold); font-size: 0.85rem; letter-spacing: 2px; margin-bottom: 15px; }
        .btn-register {
            background: var(--gold); color: black; padding: 12px 25px; text-decoration: none;
            border-radius: 8px; font-weight: 600; margin-top: 20px; display: inline-block; transition: 0.3s;
        }
        .btn-register:hover { filter: brightness(1.2); transform: scale(1.05); }

        .back-link { margin-bottom: 50px; color: #888; text-decoration: none; transition: 0.3s; }
        .back-link:hover { color: var(--gold); }
    </style>
</head>
<body>

    <div class="toast-container" id="toastContainer">
        <?php if (isset($_GET['status'])): ?>
            <?php 
                $msg = "";
                $icon = "fa-check-circle";
                if ($_GET['status'] == 'sub_ok') $msg = "Inscription validée ! On se voit là-bas.";
                if ($_GET['status'] == 'already_sub') { $msg = "Vous êtes déjà inscrit."; $icon = "fa-info-circle"; }
                if ($_GET['status'] == 'auth_event') { $msg = "Connectez-vous d'abord."; $icon = "fa-user-lock"; }
            ?>
            <?php if ($msg != ""): ?>
                <div class="toast" id="mainToast">
                    <i class="fas <?php echo $icon; ?>"></i>
                    <span><?php echo $msg; ?></span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <header>
        <a href="../index.php" class="logo">
            <i class="fas fa-book-open"></i>
            <h2 style="font-family: 'Playfair Display'; margin:0;">City Library</h2>
        </a>
        <nav>
            <ul class="nav-links">
                <li><a href="../index.php">Home</a></li>
                <li><a href="events.php" style="color: var(--gold);">Events</a></li>
            </ul>
        </nav>
    </header>

    <div class="page-title">
        <h1>Upcoming Events</h1>
        <p>Join us for unique cultural experiences.</p>
    </div>

    <div class="events-grid">
        <?php if (empty($events)): ?>
            <p style="grid-column: 1/-1; text-align: center; opacity: 0.5;">No events scheduled.</p>
        <?php else: ?>
            <?php foreach($events as $event): ?>
                <div class="event-card">
                    <div class="event-date">
                        <i class="far fa-calendar-alt"></i> 
                        <?php echo date('d M Y', strtotime($event['date_evenement'])); ?>
                    </div>
                    <h3 style="font-family: 'Playfair Display'; font-size: 1.8rem; margin: 10px 0;"><?php echo htmlspecialchars($event['titre']); ?></h3>
                    <p style="font-size: 0.9rem; opacity: 0.7;"><?php echo htmlspecialchars(substr($event['description'], 0, 90)); ?>...</p>
                    <a href="inscription_events.php?event=<?php echo urlencode($event['titre']); ?>" class="btn-register">S'inscrire</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>

    <script>
        // Animation du Toast au chargement
        window.addEventListener('load', function() {
            const toast = document.getElementById('mainToast');
            if (toast) {
                // On l'affiche
                setTimeout(() => { toast.classList.add('show'); }, 100);
                
                // On le cache après 4 secondes
                setTimeout(() => {
                    toast.classList.remove('show');
                    // On nettoie l'URL pour éviter que le message revienne au refresh
                    const url = new URL(window.location);
                    url.searchParams.delete('status');
                    window.history.replaceState({}, document.title, url);
                }, 4000);
            }
        });
    </script>

</body>
</html>