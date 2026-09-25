<?php
session_start();
require_once('../config/db.php');

// 1. SÉCURITÉ : Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../PAGE/login.php?error=must_login");
    exit();
}

// 2. Vérifier si l'ID du livre est présent
if (isset($_GET['id'])) {
    $id_livre = $_GET['id'];
    $id_user = $_SESSION['user_id'];
    $date_aujourdhui = date('Y-m-d');

    // 3. RÉCUPÉRER LES INFOS DU LIVRE (Stock et Titre)
    $stmt = $pdo->prepare("SELECT titre, exemplaires_dispo FROM livres WHERE id = ?");
    $stmt->execute([$id_livre]);
    $livre = $stmt->fetch();

    if (!$livre) {
        header("Location: ../index.php?error=not_found");
        exit();
    }

    // 4. VÉRIFICATION : Déjà emprunté et non rendu ?
    $check = $pdo->prepare("SELECT id FROM emprunts WHERE id_utilisateur = ? AND id_livre = ? AND date_retour IS NULL");
    $check->execute([$id_user, $id_livre]);

    if ($check->rowCount() > 0) {
        header("Location: profile.php?status=already_borrowed");
        exit();
    }

    // 5. VÉRIFICATION : Reste-t-il du stock ?
    if ($livre['exemplaires_dispo'] > 0) {
        
        try {
            // DÉBUT TRANSACTION (Pour être sûr que tout se passe bien ou rien du tout)
            $pdo->beginTransaction();

            // Au moment où l'utilisateur emprunte :
            $sql = "INSERT INTO emprunts (id_utilisateur, id_livre, date_emprunt, date_retour_prevue) 
            VALUES (:user, :livre, NOW(), DATE_ADD(NOW(), INTERVAL 14 DAY))";

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['user' => $id_user, 'livre' => $id_livre]);
            // B. Diminuer le stock disponible
            $upd = $pdo->prepare("UPDATE livres SET exemplaires_dispo = exemplaires_dispo - 1 WHERE id = ?");
            $upd->execute([$id_livre]);

            // Valider les changements
            $pdo->commit();
            header("Location: profile.php?status=success");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack(); // Annuler tout en cas d'erreur SQL
            header("Location: ../index.php?error=db_error");
            exit();
        }

    } else {
        // Plus de stock
        header("Location: ../index.php?error=no_stock");
        exit();
    }

} else {
    header("Location: ../index.php");
    exit();
}