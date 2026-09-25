<?php
session_start();
require_once('../config/db.php');

// SÉCURITÉ : Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../PAGE/login.php");
    exit();
}

// RÉCUPÉRATION DES UTILISATEURS
$query = $pdo->query("SELECT * FROM utilisateurs ORDER BY id DESC");
$utilisateurs = $query->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Membres - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root { --gold: #d4a373; --bg: #121212; --red: #e63946; --blue: #457b9d; --green: #1D6F42; }

        body { margin: 0; background: var(--bg); color: white; font-family: 'Poppins', sans-serif; display: flex; }

        /* SIDEBAR */
        .sidebar { width: 250px; height: 100vh; background: #000; position: fixed; padding: 20px; border-right: 1px solid var(--gold); box-sizing: border-box; }
        .nav-item { display: block; padding: 12px; color: white; text-decoration: none; margin-bottom: 10px; border-radius: 5px; transition: 0.3s; }
        .nav-item:hover, .nav-item.active { background: var(--gold); color: black; }
        .nav-item i { margin-right: 10px; width: 20px; }

        /* MAIN CONTENT */
        .main-content { margin-left: 270px; padding: 40px; width: calc(100% - 270px); }

        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 20px; }

        /* BARRE DE RECHERCHE */
        .search-container { position: relative; width: 300px; }
        .search-container input {
            width: 100%; padding: 10px 15px 10px 40px; border-radius: 25px; border: 1px solid rgba(212, 163, 115, 0.5);
            background: rgba(255,255,255,0.05); color: white; outline: none; transition: 0.3s;
        }
        .search-container input:focus { border-color: var(--gold); box-shadow: 0 0 10px rgba(212, 163, 115, 0.2); }
        .search-container i { position: absolute; left: 15px; top: 12px; color: var(--gold); }

        /* TABLEAU */
        table { width: 100%; border-collapse: collapse; background: rgba(255, 255, 255, 0.03); border-radius: 10px; overflow: hidden; }
        th { text-align: left; padding: 15px; background: rgba(212, 163, 115, 0.1); color: var(--gold); border-bottom: 2px solid var(--gold); }
        td { padding: 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        .role-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; text-transform: uppercase; border: 1px solid rgba(255,255,255,0.3); }
        .role-admin { border-color: var(--gold); color: var(--gold); }
        
        .btn-action { padding: 8px; border-radius: 5px; color: white; text-decoration: none; margin-right: 5px; display: inline-block; }
        .btn-edit { background: var(--blue); }
        .btn-delete { background: var(--red); }
        .btn-excel { background: var(--green); color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: flex; align-items: center; gap: 8px; font-weight: 600; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2 style="font-family: 'Playfair Display'; color: var(--gold);"><i class="fas fa-user-shield"></i> Admin</h2>
        <hr style="border: 0.5px solid rgba(255,255,255,0.1); margin: 20px 0;">
        <a href="admin_dashboard.php" class="nav-item"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="admin.php" class="nav-item active"><i class="fas fa-users"></i> Utilisateurs</a>
        <a href="admin_inventaire_livres.php" class="nav-item"><i class="fas fa-book"></i> Inventaire Livres</a>
        <a href="../index.php" class="nav-item"><i class="fas fa-home"></i> Voir le site</a>
        <a href="../PAGE/logout.php" class="nav-item" style="margin-top: 50px; color: #ff4d4d;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>

    <div class="main-content">
        <div class="admin-header">
            <h1 style="font-family: 'Playfair Display'; margin: 0;">Gestion des Membres</h1>
            
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Rechercher un membre...">
            </div>

            <a href="export_excel.php" class="btn-excel">
                <i class="fas fa-file-excel"></i> Exporter CSV
            </a>
        </div>

        <table id="userTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($utilisateurs as $user): ?>
                <tr>
                    <td>#<?php echo $user['id']; ?></td>
                    <td class="user-name"><strong><?php echo htmlspecialchars($user['nom']); ?></strong></td>
                    <td class="user-email"><?php echo htmlspecialchars($user['email']); ?></td>
                    <td class="user-telephone"><?php echo htmlspecialchars($user['telephone']); ?></td>

                    <td>
                        <span class="role-badge <?php echo ($user['role'] == 'admin') ? 'role-admin' : ''; ?>">
                            <?php echo htmlspecialchars($user['role']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn-action btn-edit" title="Modifier"><i class="fas fa-edit"></i></a>
                        
                        <?php if($user['id'] != $_SESSION['user_id']): ?>
                            <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Voulez-vous vraiment supprimer ce membre ?');" title="Supprimer"><i class="fas fa-trash"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    function filterTable() {
        let input = document.getElementById("searchInput");
        let filter = input.value.toLowerCase();
        let table = document.getElementById("userTable");
        let tr = table.getElementsByTagName("tr");

        for (let i = 1; i < tr.length; i++) {
            let tdNom = tr[i].getElementsByClassName("user-name")[0];
            let tdEmail = tr[i].getElementsByClassName("user-email")[0];
            if (tdNom || tdEmail) {
                let textNom = tdNom.textContent || tdNom.innerText;
                let textEmail = tdEmail.textContent || tdEmail.innerText;
                if (textNom.toLowerCase().indexOf(filter) > -1 || textEmail.toLowerCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
    </script>
</body>
</html>