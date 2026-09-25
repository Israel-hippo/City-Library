<?php
session_start();
require_once('config/db.php');

// On récupère le format choisi (All par défaut)
$format_filter = isset($_GET['format']) ? $_GET['format'] : 'all';

// Requête dynamique selon le filtre
if ($format_filter !== 'all') {
    $query = $pdo->prepare("SELECT * FROM livres WHERE format = ? ORDER BY id DESC LIMIT 8");
    $query->execute([$format_filter]);
} else {
    $query = $pdo->query("SELECT * FROM livres ORDER BY id DESC LIMIT 8");
}
$livres = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>City Library - Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Poppins:wght@300;400;600&display=swap');

        :root { --gold: #d4a373; --red: #e74c3c; }

        .filter-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 40px;
        }

        .filter-btn {
            text-decoration: none;
            color: white;
            padding: 10px 25px;
            border-radius: 30px;
            border: 1px solid rgba(212, 163, 115, 0.4);
            background: rgba(255, 255, 255, 0.05);
            transition: 0.3s;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-btn:hover, .filter-btn.active {
            background: var(--gold);
            color: black;
            border-color: var(--gold);
            font-weight: 600;
        }

        /* Badge de format sur la carte */
        .format-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--gold);
            color: black;
            padding: 3px 10px;
            border-radius: 5px;
            font-size: 0.7rem;
            font-weight: bold;
            text-transform: uppercase;
            z-index: 2;
        }

        /* Nouveau badge Rouge si le stock est à 0 */
        .out-of-stock-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--red);
            color: white;
            padding: 3px 10px;
            border-radius: 5px;
            font-size: 0.7rem;
            font-weight: bold;
            text-transform: uppercase;
            z-index: 2;
            box-shadow: 0 2px 10px rgba(231, 76, 60, 0.4);
        }

        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.85)), 
                        url('img/library-bg.png') no-repeat center center fixed;
            background-size: cover;
            color: white;
            font-family: 'Poppins', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* --- HEADER --- */
        header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 8%;
            box-sizing: border-box;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(5px);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: white;
        }

        .logo i { font-size: 2rem; color: var(--gold); }
        .logo h2 { font-family: 'Playfair Display', serif; margin: 0; font-size: 1.8rem; }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 30px;
            align-items: center;
            margin: 0;
        }

        .nav-links a { 
            color: white; 
            text-decoration: none; 
            transition: 0.3s;
            font-size: 0.95rem;
        }

        .nav-links a:hover { color: var(--gold); }

        .btn-account { 
            border: 1.5px solid var(--gold); 
            padding: 8px 20px; 
            border-radius: 8px; 
            color: var(--gold) !important;
        }

        .btn-admin {
            background: var(--gold);
            color: black !important;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: 600;
        }

        /* --- WELCOME MESSAGE --- */
        .welcome-msg {
            text-align: center;
            margin-top: 80px;
        }
        .welcome-msg h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            margin: 0;
        }
        /* --- SEARCH SECTION --- */
        .search-section {
            margin: 40px 0;
            width: 100%;
            max-width: 650px;
        }

        .search-pill {
            display: flex;
            background: white;
            border-radius: 50px;
            padding: 5px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
        }

        .search-pill input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 12px 25px;
            font-size: 1rem;
            outline: none;
            color: #333;
        }

        .search-pill button {
            background: #222;
            border: none;
            color: white;
            padding: 0 30px;
            border-radius: 40px;
            cursor: pointer;
            transition: 0.3s;
        }

        .search-pill button:hover { background: var(--gold); color: black; }

        /* --- TITRE EXPLORE --- */
        .explore-header {
            text-align: center;
            width: 84%;
            margin-bottom: 40px;
        }

        .explore-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 4.5rem;
            font-weight: 900;
            margin: 0;
            line-height: 1.1;
        }

        .explore-header p {
            font-size: 1.3rem;
            opacity: 0.7;
            margin-top: 10px;
            font-weight: 300;
        }

        /* --- FEATURES --- */
        .features-grid {
            display: flex;
            gap: 50px;
            margin-bottom: 60px;
        }

        .feature-item {
            text-align: center;
            text-decoration: none;
            color: white;
            transition: 0.3s;
            opacity: 0.8;
        }

        .feature-item i { font-size: 2.2rem; color: var(--gold); margin-bottom: 10px; display: block; }
        .feature-item:hover { opacity: 1; transform: translateY(-5px); }

        /* --- BOOKS GRID --- */
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            width: 85%;
            max-width: 1200px;
            margin-bottom: 80px;
        }

        .book-card-link {
            text-decoration: none;
            color: inherit;
            display: block;
            transition: transform 0.3s ease;
        }

        .book-card-link:hover {
            transform: translateY(-10px);
        }

        .book-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
            transition: 0.3s;
            height: 100%;
            cursor: pointer;
            position: relative;
        }

        .book-card:hover {
            border-color: var(--gold);
            background: rgba(255,255,255,0.1);
        }

        .book-card img { content-visibility: auto; width: 100%; height: 350px; object-fit: cover; }
        
        /* Assombrir légèrement la couverture si hors stock */
        .book-card.out-of-stock img { filter: grayscale(40%) brightness(70%); }

        .book-info { padding: 15px; }
        .book-info h3 { margin: 0; font-family: 'Playfair Display'; font-size: 1.2rem; }
        .book-info p { margin: 5px 0 0; opacity: 0.6; font-size: 0.85rem; }

    </style>
</head>
<body>

    <header>
        <a href="index.php" class="logo">
            <i class="fas fa-book-open"></i>
            <h2>City Library</h2>
        </a>
        <nav>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="PAGE/events.php">Events</a></li>

                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['role'] === 'admin'): ?>
                        <li><a href="ADMIN/admin_dashboard.php" class="btn-admin"><i class="fas fa-lock"></i> Admin</a></li>
                    <?php elseif($_SESSION['role'] === 'bibliothecaire'): ?>
                        <li><a href="BIBLIO/biblio_dashboard.php" class="btn-admin"><i class="fas fa-user-shield"></i> Biblio</a></li>
                    <?php endif; ?>
                    
                    <li><a href="LECTEUR/profile.php" class="btn-account">My Profile</a></li>
                    <li><a href="PAGE/logout.php" style="color: #ff4d4d;"><i class="fas fa-power-off"></i></a></li>
                <?php else: ?>
                    <li><a href="PAGE/login.php" class="btn-account">My Account</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="welcome-msg">
        <?php if(isset($_SESSION['nom'])): ?>
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['nom']); ?></h1>
        <?php else: ?>
            <h1>Welcome to the City Library</h1>
        <?php endif; ?>
        <p style="opacity: 0.6; font-size: 1.1rem; margin-top: 10px;">Access thousands of books from anywhere.</p>
    </div>

    <div class="search-section">
        <form action="PAGE/search.php" method="GET" class="search-pill">
            <input type="text" name="q" placeholder="Search by title, author or ISBN...">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <div class="features-grid">
        <a href="index.php?format=all" class="filter-btn <?php echo $format_filter == 'all' ? 'active' : ''; ?>">
            <i class="fas fa-th"></i> All Books
        </a>
        <a href="index.php?format=ebook" class="filter-btn <?php echo $format_filter == 'ebook' ? 'active' : ''; ?>">
            <i class="fas fa-tablet-alt"></i> eBooks
        </a>
        <a href="index.php?format=audiobook" class="filter-btn <?php echo $format_filter == 'audiobook' ? 'active' : ''; ?>">
            <i class="fas fa-headphones"></i> Audiobooks
        </a>
        <a href="index.php?format=physique" class="filter-btn <?php echo $format_filter == 'physique' ? 'active' : ''; ?>">
            <i class="fas fa-bookmark"></i> Physiques
        </a>
        <a href="PAGE/inscription.php" class="feature-item">
            <i class="fas fa-id-card"></i>
            <span>Membership</span>
        </a>
    </div>

    <div class="explore-header">
        <h1>Explore Our Collections</h1>
        <p>Discover thousands of stories, resources, and knowledge.</p>
    </div>

    <div class="books-grid">
        <?php foreach($livres as $livre): 
            // Condition pour vérifier la rupture de stock
            $indisponible = ($livre['exemplaires_dispo'] <= 0);
        ?>
            <a href="PAGE/book_details.php?id=<?php echo $livre['id']; ?>" class="book-card-link">
                <div class="book-card <?php echo $indisponible ? 'out-of-stock' : ''; ?>">
                    
                    <span class="format-badge"><?php echo htmlspecialchars($livre['format']); ?></span>
                    
                    <?php if($indisponible): ?>
                        <span class="out-of-stock-badge"><i class="fas fa-times-circle"></i> Épuisé</span>
                    <?php endif; ?>
                    
                    <img src="img/<?php echo htmlspecialchars($livre['image_url']); ?>" loading="lazy" alt="Cover">
                    
                    <div class="book-info">
                        <h3><?php echo htmlspecialchars($livre['titre']); ?></h3>
                        <p>par <?php echo htmlspecialchars($livre['auteur']); ?></p>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <script src="js/script.js"></script>
</body>
</html>