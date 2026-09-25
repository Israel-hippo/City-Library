<?php
session_start();
require_once('../config/db.php'); // Vérifie bien que le chemin vers db.php est correct depuis le dossier PAGE

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // On récupère le préfixe et on ajoute le domaine automatiquement
    $email_complet = $_POST['email_prefix'] . "@bibliotheque.com";
    $mdp = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email_complet]);
        $user = $stmt->fetch();

        // 1. Vérification de l'existence de l'utilisateur et du mot de passe
        if ($user && $mdp == $user['mot_de_passe']) {
            
            // 2. Vérification de l'approbation pour les lecteurs uniquement
            if ($user['role'] == 'lecteur' && $user['approuve'] == 0) {
                $erreur = "Votre compte est en attente d'approbation. Veuillez remettre votre fiche signée au bibliothécaire.";
            } else {
                // 3. Connexion réussie : on stocke les infos en session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['nom'] = $user['nom'];
            
                // 4. Redirection selon le rôle vers les nouveaux dossiers
                if ($_SESSION['role'] == 'admin') {
                    header('Location: ../ADMIN/admin_dashboard.php');
                } elseif ($_SESSION['role'] == 'bibliothecaire') {
                    header('Location: ../BIBLIO/biblio_dashboard.php');
                } else {
                    // Redirection pour les lecteurs approuvés
                    header('Location: ../index.php');
                }
                exit();
            }
        } else {
            // Si l'utilisateur n'existe pas ou le mot de passe est faux
            $erreur = "Identifiant ou mot de passe incorrect.";
        }
    } catch (PDOException $e) {
        $erreur = "Erreur de base de données : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - City Library</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* On garde ton design sombre et café */
        .login-box {
            max-width: 400px;
            margin: 100px auto;
            padding: 40px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--gold);
            border-radius: 15px;
            text-align: center;
        }
        .input-group {
            display: flex;
            align-items: center;
            background: white;
            border-radius: 5px;
            margin-bottom: 15px;
            padding: 5px 10px;
        }
        .input-group input {
            border: none;
            outline: none;
            padding: 10px;
            flex: 1;
        }
        .domain { color: #555; font-weight: bold; font-size: 0.9rem; }
    </style>
</head>
<body style="background: #02060a; color: white; font-family: 'Poppins', sans-serif;">

    <div class="login-box">
        <h2 style="font-family: 'Playfair Display'; color: var(--gold);">Connexion</h2>
        
        <?php if($erreur): ?>
            <p style="color: #ff4d4d;"><?php echo $erreur; ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <input type="text" name="email_prefix" placeholder="Identifiant" required>
                <span class="domain">@bibliotheque.com</span>
            </div>
            
            <input type="password" name="password" placeholder="Mot de passe" required 
                   style="width: 100%; padding: 12px; margin-bottom: 20px; border-radius: 5px; border: none;">
            
            <button type="submit" class="btn-browse gold" style="width: 100%; cursor: pointer;">
                Se connecter
            </button>
        </form>
        
        <p style="margin-top: 20px; font-size: 0.8rem; opacity: 0.6;">
            Accès réservé au personnel et abonnés.
        </p>
    </div>

</body>
</html>