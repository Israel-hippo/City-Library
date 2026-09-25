<?php
session_start();
require_once('../config/db.php');

// SÉCURITÉ : Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../PAGE/login.php");
    exit();
}

$message = "";

// 1. Initialisation de l'ID (fonctionne en GET lors du premier accès, et en POST lors de la validation)
$id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$id) {
    header("Location: admin.php");
    exit();
}

// 2. Traitement de la modification (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $role = $_POST['role'];

    try {
        $update = $pdo->prepare("UPDATE utilisateurs SET nom = ?, email = ?, telephone = ?, role = ? WHERE id = ?");
        if ($update->execute([$nom, $email, $telephone, $role, $id])) {
            $message = "<div style='background: rgba(46, 204, 113, 0.1); color: #2ecc71; border: 1px solid #2ecc71; padding: 12px; border-radius: 5px; text-align:center; margin-bottom: 20px; font-weight: bold;'>Utilisateur mis à jour avec succès !</div>";
        } else {
            $message = "<div style='background: rgba(231, 76, 60, 0.1); color: #e74c3c; border: 1px solid #e74c3c; padding: 12px; border-radius: 5px; text-align:center; margin-bottom: 20px; font-weight: bold;'>Erreur lors de la mise à jour.</div>";
        }
    } catch (PDOException $e) {
        $message = "<div style='background: rgba(231, 76, 60, 0.1); color: #e74c3c; border: 1px solid #e74c3c; padding: 12px; border-radius: 5px; text-align:center; margin-bottom: 20px; font-weight: bold;'>Erreur SQL : " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// 3. Récupération des infos fraîches de l'utilisateur pour l'affichage dans le formulaire
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Utilisateur - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --gold: #d4a373; }
        body { background: #121212; color: white; font-family: 'Poppins', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .edit-card { background: rgba(255, 255, 255, 0.05); padding: 40px; border-radius: 15px; border: 1px solid var(--gold); width: 100%; max-width: 450px; box-sizing: border-box; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: var(--gold); font-size: 0.9rem; }
        input, select { width: 100%; padding: 12px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.05); color: white; box-sizing: border-box; font-family: inherit; }
        option { background: #121212; color: white; }
        button { width: 100%; padding: 12px; background: var(--gold); border: none; border-radius: 5px; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 10px; font-size: 1rem; }
        button:hover { filter: brightness(1.2); transform: translateY(-2px); }
        .back-link { display: block; text-align: center; margin-top: 20px; color: white; text-decoration: none; opacity: 0.6; font-size: 0.9rem; }
        .back-link:hover { opacity: 1; color: var(--gold); }
    </style>
</head>
<body>

<div class="edit-card">
    <h2 style="text-align: center; font-family: 'Playfair Display'; margin-bottom: 30px; color: var(--gold);">Modifier le Profil</h2>
    
    <?php echo $message; ?>

    <form action="" method="POST">
        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
        
        <div class="form-group">
            <label>Nom Complet</label>
            <input type="text" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="form-group">
            <label>Téléphone</label>
            <input type="text" name="telephone" value="<?php echo htmlspecialchars($user['telephone']); ?>" required>
        </div>

        <div class="form-group">
            <label>Rôle du compte</label>
            <select name="role">
                <option value="lecteur" <?php if($user['role'] == 'lecteur') echo 'selected'; ?>>Lecteur</option>
                <option value="bibliothecaire" <?php if($user['role'] == 'bibliothecaire') echo 'selected'; ?>>Bibliothécaire</option>
                <option value="admin" <?php if($user['role'] == 'admin') echo 'selected'; ?>>Administrateur</option>
            </select>
        </div>

        <button type="submit">Enregistrer les modifications</button>
    </form>

    <a href="admin.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour à la gestion des membres</a>
</div>

</body>
</html>