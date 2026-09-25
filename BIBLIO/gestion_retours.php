<?php
session_start();
require_once('../config/db.php');

// Sécurité : Accès réservé au personnel
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'bibliothecaire' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../PAGE/login.php');
    exit();
}

$message = "";
$status = "";

// LOGIQUE DE TRAITEMENT DU RETOUR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_emprunt'])) {
    $id_emprunt = $_POST['id_emprunt'];

    try {
        $pdo->beginTransaction();

        // 1. Récupérer l'ID du livre lié à cet emprunt avant de valider
        $stmt = $pdo->prepare("SELECT id_livre FROM emprunts WHERE id = ? AND date_retour IS NULL");
        $stmt->execute([$id_emprunt]);
        $emprunt = $stmt->fetch();

        if ($emprunt) {
            $id_livre = $emprunt['id_livre'];

            // 2. Mettre à jour l'emprunt avec la date et l'heure du jour
            $updateEmprunt = $pdo->prepare("UPDATE emprunts SET date_retour = NOW() WHERE id = ?");
            $updateEmprunt->execute([$id_emprunt]);

            // 3. Augmenter le stock disponible de +1 dans la table livres (exemplaires_dispo)
            $updateStock = $pdo->prepare("UPDATE livres SET exemplaires_dispo = exemplaires_dispo + 1 WHERE id = ?");
            $updateStock->execute([$id_livre]);

            $pdo->commit();
            $message = "Le retour a été enregistré avec succès. Le stock du livre a été incrémenté de +1.";
            $status = "success";
        } else {
            $pdo->rollBack();
            $message = "Erreur : Cet emprunt a déjà été traité ou n'existe pas.";
            $status = "error";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Erreur technique : " . $e->getMessage();
        $status = "error";
    }
}

// RECUPÉRATION DES LIVRES PHYSIQUES ACTUELLEMENT EMPRUNTÉS POUR LA LISTE DÉROULANTE
try {
    $sql_recup_emprunts = "SELECT e.id AS emprunt_id, l.titre, u.nom AS lecteur_nom 
                           FROM emprunts e
                           JOIN livres l ON e.id_livre = l.id
                           JOIN utilisateurs u ON e.id_utilisateur = u.id
                           WHERE e.date_retour IS NULL AND l.format = 'physique'
                           ORDER BY l.titre ASC";
    $stmt_liste = $pdo->query($sql_recup_emprunts);
    $emprunts_en_cours = $stmt_liste->fetchAll();
} catch (PDOException $e) {
    die("Erreur lors du chargement des emprunts : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Enregistrer un Retour - City Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --gold: #d4a373; --bg: #0b0e11; --card: rgba(255, 255, 255, 0.05); }
        body { margin: 0; background: var(--bg); color: white; font-family: 'Poppins', sans-serif; display: flex; }
        .main-content { margin-left: 260px; padding: 50px; width: calc(100% - 260px); }
        
        .container { max-width: 600px; background: var(--card); padding: 40px; border-radius: 20px; border: 1px solid rgba(212, 163, 115, 0.2); }
        h1 { font-family: 'Playfair Display', serif; margin-bottom: 25px; }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
        .success { background: rgba(46, 204, 113, 0.1); color: #2ecc71; border: 1px solid #2ecc71; }
        .error { background: rgba(231, 76, 60, 0.1); color: #e74c3c; border: 1px solid #e74c3c; }

        label { display: block; margin-bottom: 10px; color: var(--gold); }
        select { width: 100%; padding: 12px; background: #161a1d; border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 8px; box-sizing: border-box; margin-bottom: 20px; font-size: 1rem; font-family: 'Poppins', sans-serif; }
        select option { background: #0b0e11; color: white; }
        
        .btn-return { background: var(--gold); color: black; padding: 15px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; width: 100%; transition: 0.3s; font-size: 1rem; }
        .btn-return:hover { opacity: 0.9; transform: scale(1.01); }
        
        .back-link { color: #b0b0b0; text-decoration: none; display: inline-block; margin-bottom: 20px; }
    </style>
</head>
<body>

    <?php include('navbar_biblio.php'); ?>

    <div class="main-content">
        <a href="gestion_emprunts.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour à la liste</a>

        <div class="container">
            <h1>Enregistrer un <span style="color: var(--gold);">Retour</span></h1>

            <?php if($message): ?>
                <div class="alert <?= $status ?>"><?= $message ?></div>
            <?php endif; ?>

            <form action="gestion_retours.php" method="POST">
                <div class="form-group">
                    <label for="id_emprunt">Sélectionner le livre physique à restituer</label>
                    <select name="id_emprunt" id="id_emprunt" required>
                        <option value="" disabled selected>-- Choisissez le livre rendu --</option>
                        <?php if ($emprunts_en_cours): ?>
                            <?php foreach ($emprunts_en_cours as $item): ?>
                                <option value="<?= $item['emprunt_id'] ?>">
                                    <?= htmlspecialchars($item['titre']) ?> — Emprunté par : <?= htmlspecialchars($item['lecteur_nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>Aucun livre physique n'est actuellement emprunté.</option>
                        <?php endif; ?>
                    </select>
                </div>
                <button type="submit" class="btn-return">Confirmer la remise du livre</button>
            </form>
            
            <p style="margin-top: 25px; font-size: 0.85rem; color: #888;">
                Note : Cette action rajoutera automatiquement +1 au stock d'exemplaires disponibles et retirera l'affichage sur le profil du lecteur.
            </p>
        </div>
    </div>

</body>
</html>