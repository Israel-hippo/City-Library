<?php
session_start();
require_once('../config/db.php');

$message = "";
$inscription_reussie = false; // Variable pour suivre l'état de l'inscription

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'];
    $email_prefix = $_POST['email_prefix'];
    $email_complet = $email_prefix . "@bibliotheque.com";
    $adresse = $_POST['adresse'];
    $telephone = $_POST['telephone'];
    $mdp = $_POST['password']; 
    $role = 'lecteur';

    try {
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, adresse, telephone, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $email_complet, $mdp, $adresse, $telephone, $role]);
        $message = "Inscription réussie ! Votre compte a été créé avec succès.";
        $inscription_reussie = true; // L'inscription est validée
    } catch (PDOException $e) {
        $message = "Erreur : cet identifiant est déjà utilisé.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - City Library</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .form-box { max-width: 500px; margin: 50px auto; padding: 30px; background: rgba(255,255,255,0.05); border: 1px solid #d4a373; border-radius: 15px; }
        .input-group { display: flex; align-items: center; background: white; border-radius: 5px; margin-bottom: 15px; padding: 2px 10px; color: black; }
        .input-group input { border: none; outline: none; padding: 10px; flex: 1; }
        input[type="text"], input[type="password"] { width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 5px; border: none; }
        .success-container { text-align: center; padding: 20px; }
        .btn-download { background: #d4a373; color: black; padding: 15px 25px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>

    <div class="form-box">
        <h2 style="font-family: 'Playfair Display'; color: #d4a373; text-align:center;">Créer un compte Lecteur</h2>
        
        <?php if($message): ?>
            <p style="text-align:center; color: #d4a373; font-weight: bold;"><?php echo $message; ?></p>
        <?php endif; ?>

        <?php if($inscription_reussie): ?>
            <div class="success-container">
                <p style="color: white; margin-bottom: 10px;">Veuillez télécharger et imprimer votre fiche pour la présenter à la bibliothèque.</p>
                <a href="generer_inscription.php?nom=<?php echo urlencode($nom); ?>&email=<?php echo urlencode($email_complet); ?>&adresse=<?php echo urlencode($adresse); ?>&telephone=<?php echo urlencode($telephone); ?>" class="btn-download">
                    <i class="fas fa-file-pdf"></i> Télécharger ma fiche d'inscription (PDF)
                </a>
                <p style="margin-top: 30px;"><a href="login.php" style="color:#d4a373; text-decoration: none;">Aller vers la page de connexion →</a></p>
            </div>
        <?php else: ?>
            <form method="POST">
                <label>Nom complet :</label>
                <input type="text" name="nom" placeholder="Ex: Jean Dupont" required style="color:black;">
                
                <label>Identifiant souhaité :</label>
                <div class="input-group">
                    <input type="text" name="email_prefix" placeholder="identifiant" required>
                    <span style="color:#555; font-weight:bold;">@bibliotheque.com</span>
                </div>

                <label>Adresse :</label>
                <input type="text" name="adresse" placeholder="Ex: 123 Rue Principale, Paris" required style="color:black;">

                <label>Téléphone :</label>
                <input type="text" name="telephone" placeholder="Ex: +33 1 23 45 67 89" required style="color:black;">
                
                <label>Mot de passe :</label>
                <input type="password" name="password" placeholder="••••••••" required style="color:black;">
                
                <button type="submit" class="btn-browse gold" style="width:100%; cursor:pointer; margin-top:10px;">S'inscrire</button>
            </form>
            <p style="text-align:center; margin-top:15px;"><a href="login.php" style="color:white; font-size:0.8rem;">Déjà inscrit ? Se connecter</a></p>
        <?php endif; ?>
    </div>

</body>
</html>