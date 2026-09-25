<?php
session_start();
require_once('../config/db.php');

// 1. SÉCURITÉ : Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../PAGE/login.php");
    exit();
}

// 2. Vérifier si l'ID de l'emprunt est bien reçu
if (isset($_GET['id'])) {
    $emprunt_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    $date_aujourdhui = date('Y-m-d');

    // 3. MISE À JOUR : On ajoute la date de retour uniquement si l'emprunt appartient bien à l'utilisateur
    $sql = "UPDATE emprunts 
            SET date_retour = ? 
            WHERE id = ? AND id_utilisateur = ? AND date_retour IS NULL";
    
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$date_aujourdhui, $emprunt_id, $user_id])) {
        // Redirection vers le profil avec un message de confirmation
        header("Location: profile.php?msg=returned");
        exit();
    } else {
        header("Location: profile.php?msg=error");
        exit();
    }
} else {
    header("Location: profile.php");
    exit();
}
?>