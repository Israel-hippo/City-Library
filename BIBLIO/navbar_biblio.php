<style>
    .sidebar { 
        width: 260px; 
        height: 100vh; 
        background: #000000; 
        position: fixed; 
        padding: 30px 20px; 
        border-right: 1px solid rgba(212, 163, 115, 0.2); 
        box-sizing: border-box;
    }
    .sidebar h2 { 
        font-family: 'Playfair Display', serif; 
        color: #d4a373; 
        text-align: center; 
        margin-bottom: 40px; 
        letter-spacing: 2px; 
    }
    .nav-item { 
        display: flex; 
        align-items: center; 
        padding: 15px; 
        color: white; 
        text-decoration: none; 
        margin-bottom: 10px; 
        border-radius: 8px; 
        transition: 0.3s; 
        gap: 15px; 
    }
    .nav-item:hover, .nav-item.active { 
        background: #d4a373; 
        color: black; 
    }
</style>

<div class="sidebar">
    <h2>CITY LIBRARY</h2>
    <a href="biblio_dashboard.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
    <a href="valider_inscriptions.php" class="nav-item"><i class="fas fa-user-check"></i> Validations</a>
    <a href="gestion_emprunts.php" class="nav-item"><i class="fas fa-book-reader"></i> Emprunts</a>
    <a href="gestion_retours.php" class="nav-item"><i class="fas fa-undo"></i> Retours</a>
    <a href="lecteurs.php" class="nav-item"><i class="fas fa-users"></i> Liste Lecteurs</a>
    <a href="biblio_inventaire_livres.php" class="nav-item"><i class="fas fa-book"></i> Inventaire Livres</a>
    <a href="biblio_retards.php" class="nav-item"><i class="fas fa-clock"></i> Les retards</a>
    
    <div style="margin-top: 50px;">
        <a href="../index.php" class="nav-item"><i class="fas fa-eye"></i> Voir le site</a>
        <a href="../PAGE/logout.php" class="nav-item" style="color: #ff4d4d;"><i class="fas fa-power-off"></i> Déconnexion</a>
    </div>
</div>