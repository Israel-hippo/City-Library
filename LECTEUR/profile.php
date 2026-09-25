<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../PAGE/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$date_aujourdhui = new DateTime();

try {
    // 1. INFOS UTILISATEUR
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // 2. ÉVÉNEMENTS
    $sql_user_events = "SELECT e.titre, e.date_evenement, i.id AS inscription_id 
                        FROM inscriptions_events i
                        JOIN evenements e ON i.titre_event = e.titre 
                        WHERE i.id_utilisateur = ?
                        ORDER BY e.date_evenement ASC";
    $stmt_user_events = $pdo->prepare($sql_user_events);
    $stmt_user_events->execute([$user_id]);
    $mes_evenements = $stmt_user_events->fetchAll();

    // 3. EMPRUNTS - Correction ici : utilisation de l.format (aligné sur ta base de données)
    $sql_emprunts = "SELECT e.id AS emprunt_id, l.titre, l.image_url, l.format, e.date_emprunt, e.date_retour_prevue 
                     FROM emprunts e
                     JOIN livres l ON e.id_livre = l.id 
                     WHERE e.id_utilisateur = ? AND e.date_retour IS NULL";
    $stmt_emprunts = $pdo->prepare($sql_emprunts);
    $stmt_emprunts->execute([$user_id]);
    $mes_emprunts = $stmt_emprunts->fetchAll();

    // 4. RÉSERVATIONS
    $sql_reservations = "SELECT r.id AS reservation_id, l.titre, l.image_url, r.date_reservation
                         FROM reservations r
                         JOIN livres l ON r.id_livre = l.id 
                         WHERE r.id_utilisateur = ? AND r.date_reservation IS NOT NULL";
    $stmt_reservations = $pdo->prepare($sql_reservations);
    $stmt_reservations->execute([$user_id]);
    $mes_reservations = $stmt_reservations->fetchAll();

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil - City Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --gold: #d4a373; --red: #e74c3c; }
        body { margin: 0; background: linear-gradient(rgba(0,0,0,0.85), rgba(0,0,0,0.85)), url('img/library-bg.jpg') no-repeat center fixed; background-size: cover; color: white; font-family: 'Poppins', sans-serif; display: flex; flex-direction: column; align-items: center; }
        header { width: 100%; display: flex; justify-content: space-between; padding: 20px 8%; box-sizing: border-box; }
        .profile-container { width: 90%; max-width: 850px; margin: 40px 0; background: rgba(255,255,255,0.05); backdrop-filter: blur(15px); border: 1px solid rgba(212,163,115,0.2); border-radius: 20px; padding: 40px; }
        
        .section-title { text-align: left; color: var(--gold); border-bottom: 1px solid rgba(212,163,115,0.2); padding-bottom: 10px; margin-top: 40px; display: flex; justify-content: space-between; align-items: center; }
        
        .borrow-card { display: flex; align-items: center; background: rgba(255,255,255,0.03); padding: 15px; border-radius: 12px; margin-bottom: 15px; gap: 20px; border: 1px solid rgba(255,255,255,0.05); }
        .borrow-card.late { border: 1px solid var(--red); background: rgba(231, 76, 60, 0.05); }
        .borrow-card img { width: 60px; height: 90px; object-fit: cover; border-radius: 5px; }
        
        .borrow-info { flex-grow: 1; text-align: left; }
        .late-msg { color: var(--red); font-weight: bold; font-size: 0.85rem; }
        .fine-amount { background: var(--red); color: white; padding: 2px 8px; border-radius: 4px; margin-left: 10px; }
        
        .btn-cancel { color: #888; text-decoration: none; font-size: 0.9rem; transition: 0.3s; }
        .btn-cancel:hover { color: var(--red); }
        .status-badge { background: rgba(46, 204, 113, 0.2); color: #2ecc71; padding: 3px 10px; border-radius: 15px; font-size: 0.75rem; border: 1px solid #2ecc71; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px; text-align: left; }
        .info-item { background: rgba(255,255,255,0.03); padding: 15px; border-radius: 10px; }
        .btn-logout { display: inline-block; margin-top: 30px; color: var(--red); text-decoration: none; border: 1px solid var(--red); padding: 10px 20px; border-radius: 5px; }
        
        .date-normale { color: #d4a373; }
        .date-retard { color: #ff4d4d; text-shadow: 0 0 10px rgba(255, 77, 77, 0.3); font-weight: bold; animation: pulse-red 2s infinite; }
        @keyframes pulse-red { 0% { opacity: 1; } 50% { opacity: 0.7; } 100% { opacity: 1; } }
    </style>
</head>
<body>

<header>
    <h2 style="font-family: 'Playfair Display'; color: #d4a373;"><i class="fas fa-book-open"></i> City Library</h2>
    <a href="../index.php" style="color: white; text-decoration: none;">Accueil</a>
</header>

<div class="profile-container">
    <h1 style="font-family: 'Playfair Display';">Bonjour, <?php echo htmlspecialchars($user['nom']); ?></h1>

    <h3 class="section-title"><span><i class="fas fa-calendar-alt"></i> Mes Inscriptions</span></h3>
    <?php if ($mes_evenements): foreach ($mes_evenements as $ev): ?>
        <div class="borrow-card">
            <div class="borrow-info">
                <h4 style="margin:0;"><?php echo htmlspecialchars($ev['titre']); ?></h4>
                <small style="opacity:0.6;">Prévu le <?php echo date('d/m/Y', strtotime($ev['date_evenement'])); ?></small>
            </div>
            <span class="status-badge">Inscrit</span>
            <a href="../PAGE/annuler_inscription.php?id=<?php echo $ev['inscription_id']; ?>" class="btn-cancel" onclick="return confirm('Annuler cette inscription ?')"><i class="fas fa-trash"></i></a>
        </div>
    <?php endforeach; else: echo "<p style='opacity:0.5; text-align:left;'>Aucun événement prévu.</p>"; endif; ?>

    <h3 class="section-title"><span><i class="fas fa-book"></i> Emprunts & Délais</span></h3>
    <?php if ($mes_emprunts): foreach ($mes_emprunts as $emp): 
        $date_retour = new DateTime($emp['date_retour_prevue']);
        $est_en_retard = ($date_aujourdhui > $date_retour);
        $amande = 0;
        if($est_en_retard) {
            $diff = $date_aujourdhui->diff($date_retour);
            $amande = $diff->days * 0.5;
        }
    ?>
        <div class="borrow-card <?php echo $est_en_retard ? 'late' : ''; ?>">
            <img src="../img/<?php echo $emp['image_url']; ?>">
            <div class="borrow-info">
                <h4 style="margin:0;"><?php echo htmlspecialchars($emp['titre']); ?></h4>
                <p style="margin:5px 0; font-size:0.85rem;">
                    Format : <span style="color: var(--gold); text-transform: uppercase; font-size: 0.75rem; font-weight: bold;"><?php echo htmlspecialchars($emp['format']); ?></span><br>
                    À rendre pour le : <strong><?php echo date('d/m/Y', strtotime($emp['date_retour_prevue'])); ?></strong>
                    <?php if($est_en_retard): ?>
                        <span class="late-msg"> — <i class="fas fa-exclamation-triangle"></i> EN RETARD</span>
                        <span class="fine-amount"><?php echo number_format($amande, 2); ?> € d'amende</span>
                    <?php endif; ?>
                </p>
            </div>

            <?php if (strtolower($emp['format']) === 'ebook' || strtolower($emp['format']) === 'audiobook'): ?>
                <a href="rendre_livre.php?id=<?php echo $emp['emprunt_id']; ?>" style="color:var(--gold); text-decoration:none; font-size:0.8rem; border:1px solid var(--gold); padding:5px 10px; border-radius:5px;">Rendre</a>
            <?php else: ?>
                <span style="color: var(--gold); font-size: 0.8rem; font-style: italic; opacity: 0.85; text-align: right;">
                    <i class="fas fa-hand-holding-heart"></i> À rendre au guichet
                </span>
            <?php endif; ?>

        </div>
    <?php endforeach; else: echo "<p style='opacity:0.5; text-align:left;'>Aucun emprunt en cours.</p>"; endif; ?>

    <h3 class="section-title"><span><i class="fas fa-clock"></i> Mes Réservations</span></h3>
    <?php if ($mes_reservations): foreach ($mes_reservations as $res): ?>
        <div class="borrow-card">
            <img src="../img/<?php echo $res['image_url']; ?>">
            <div class="borrow-info">
                <h4 style="margin:0;"><?php echo htmlspecialchars($res['titre']); ?></h4>
                <small style="opacity:0.6;">Réservé le <?php echo date('d/m/Y', strtotime($res['date_reservation'])); ?></small>
            </div>
            <a href="annuler_reservation.php?id=<?php echo $res['reservation_id']; ?>" class="btn-cancel" onclick="return confirm('Annuler la réservation ?')">Annuler</a>
        </div>
    <?php endforeach; else: echo "<p style='opacity:0.5; text-align:left;'>Aucune réservation.</p>"; endif; ?>

    <div class="info-grid">
        <div class="info-item"><small style="color:var(--gold);">EMAIL</small><br><?php echo $user['email']; ?></div>
        <div class="info-item"><small style="color:var(--gold);">STATUT</small><br>Membre Actif</div>
    </div>

    <a href="../PAGE/logout.php" class="btn-logout">Déconnexion</a>
</div>

</body>
</html>