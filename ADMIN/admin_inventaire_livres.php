<?php
session_start();
require_once('../config/db.php');

// SÉCURITÉ : Seul l'admin accède à l'inventaire
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'bibliothecaire')) {
    header("Location: ../PAGE/login.php");
    exit();
}

// GESTION DE LA SUPPRESSION
if (isset($_GET['delete_id'])) {
    $id_livre = $_GET['delete_id'];
    
    // On récupère d'abord le nom de l'image pour la supprimer du dossier img/
    $stmt_img = $pdo->prepare("SELECT image_url FROM livres WHERE id = ?");
    $stmt_img->execute([$id_livre]);
    $img = $stmt_img->fetchColumn();

    if ($img && file_exists("../img/" . $img)) {
        unlink("../img/" . $img); // Supprime le fichier physique
    }

    if (isset($_GET['delete_id'])) {
    $id_livre = $_GET['delete_id'];

    // 1. On supprime d'abord tous les emprunts liés à ce livre
    $stmt1 = $pdo->prepare("DELETE FROM emprunts WHERE id_livre = ?");
    $stmt1->execute([$id_livre]);

    // 2. Maintenant, on peut supprimer le livre sans erreur
    $stmt2 = $pdo->prepare("DELETE FROM livres WHERE id = ?");
    $stmt2->execute([$id_livre]);

    header("Location: admin_inventaire_livres.php?msg=deleted");
    exit();
}
}

// RÉCUPÉRATION DES LIVRES
$query = $pdo->query("SELECT * FROM livres ORDER BY id DESC");
$livres = $query->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inventaire des Livres - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --gold: #d4a373; --bg: #121212; --red: #e63946; }

        /* Conteneur pour aligner les boutons proprement */
.action-buttons {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

/* Style du bouton Modifier */
.btn-edit {
    background: transparent;
    color: var(--gold); /* Utilise ta variable or #d4a373 */
    border: 1px solid var(--gold);
    padding: 6px 15px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-edit:hover {
    background: var(--gold);
    color: #000; /* Devient noir au survol pour le contraste */
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(212, 163, 115, 0.3);
}

/* Style pour l'icône de suppression pour qu'elle soit plus propre */
.btn-delete {
    color: #ff4d4d;
    font-size: 1.1rem;
    transition: 0.3s;
    text-decoration: none;
}

.btn-delete:hover {
    color: #ff1a1a;
    transform: scale(1.2);
}

        body { background: var(--bg); color: white; font-family: 'Poppins', sans-serif; margin: 0; display: flex; }

        .stock-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: bold;
}
.stock-badge.available {
    background: rgba(46, 204, 113, 0.2);
    color: #2ecc71;
    border: 1px solid #2ecc71;
}
.stock-badge.empty {
    background: rgba(231, 76, 60, 0.2);
    color: #e74c3c;
    border: 1px solid #e74c3c;
}
        
        .sidebar { width: 250px; height: 100vh; background: #000; position: fixed; padding: 20px; border-right: 1px solid var(--gold); }
        .main-content { margin-left: 290px; padding: 40px; width: 100%; }

        .nav-item { display: block; padding: 12px; color: white; text-decoration: none; margin-bottom: 10px; border-radius: 5px; transition: 0.3s; }
        .nav-item:hover, .nav-item.active { background: var(--gold); color: black; }
        .nav-item i { margin-right: 10px; width: 20px; }

        .book-table { width: 95%; border-collapse: collapse; background: rgba(255,255,255,0.03); margin-top: 20px; }
        .book-table th, .book-table td { padding: 15px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .book-table th { color: var(--gold); background: rgba(212, 163, 115, 0.1); }
        
        .book-img { width: 50px; height: 70px; object-fit: cover; border-radius: 4px; border: 1px solid rgba(255,255,255,0.1); }
        
        .btn-delete { color: var(--red); text-decoration: none; font-size: 1.2rem; transition: 0.3s; }
        .btn-delete:hover { transform: scale(1.2); }
    </style>
</head>
<body>

    <td class="actions">
    <a href="admin_inventaire_livres.php?delete_id=<?php echo $l['id']; ?>" class="btn-delete">
        <i class="fas fa-trash"></i>
    </a>
</td>

<div class="sidebar">
    <h2 style="font-family: 'Playfair Display'; color: var(--gold);"><i class="fas fa-book-open"></i> Admin</h2>
    <hr style="border: 0.5px solid rgba(255,255,255,0.1); margin: 20px 0;">
    <a href="admin_dashboard.php" class="nav-item"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="admin.php" class="nav-item"><i class="fas fa-users"></i> Utilisateurs</a>
    <a href="admin_inventaire_livres.php" class="nav-item active"><i class="fas fa-book"></i> Inventaire Livres</a>
    <a href="admin_ajouter_livre.php" class="nav-item"><i class="fas fa-plus-circle"></i> Ajouter Livre</a>
    <a href="../PAGE/logout.php" class="nav-item" style="color: #ff4d4d;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
</div>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 95%;">
        <h1 style="font-family: 'Playfair Display';">Gestion de l'Inventaire</h1>
        <a href="admin_ajouter_livre.php" style="background: var(--gold); color: black; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold;">+ Nouveau Livre</a>
    </div>

    <table class="book-table">
        <thead>
            <tr>
                <th>Couverture</th>
                <th>Titre</th>
                <th>Auteur</th>
                <th>Stock (Dispo / Total)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($livres as $l): ?>
            <tr>
                <td>
                    <img src="../img/<?php echo htmlspecialchars($l['image_url']); ?>" class="book-img" alt="Cover">
                </td>
                <td><strong><?php echo htmlspecialchars($l['titre']); ?></strong></td>
                <td><?php echo htmlspecialchars($l['auteur']); ?></td>
                <td style="text-align: center;">
                    <span class="stock-badge <?php echo ($l['exemplaires_dispo'] == 0) ? 'empty' : 'available'; ?>">
                        <?php echo $l['exemplaires_dispo']; ?> / <?php echo $l['exemplaires_total']; ?>
                    </span>
                </td>
                <td>
                    <div class="action-buttons">
                        <a href="edit_book.php?id=<?php echo $l['id']; ?>" class="btn-edit">
                            <i class="fas fa-pen"></i> Modifier
                        </a>
                        <a href="admin_inventaire_livres.php?delete_id=<?php echo $l['id']; ?>" 
                            class="btn-delete" 
                            onclick="return confirm('Supprimer définitivement ce livre ?');">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>