<?php
session_start();
require_once('../config/db.php');

// 1. Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header("Location: ../PAGE/login.php");
    exit();
}

// 2. Vérification de l'ID de réservation dans l'URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $reservation_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    try {
        // 3. Suppression de la réservation
        // On vérifie l'id_utilisateur pour éviter qu'un malin ne supprime la réservation d'un autre via l'URL
        $sql = "DELETE FROM reservations WHERE id = ? AND id_utilisateur = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$reservation_id, $user_id]);

        // 4. Redirection avec un message de succès
        header("Location: profile.php?msg=res_annulée");
        exit();

    } catch (PDOException $e) {
        // En cas d'erreur SQL
        die("Erreur lors de l'annulation : " . $e->getMessage());
    }
} else {
    // Si l'ID est manquant, on renvoie simplement au profil
    header("Location: profile.php");
    exit();
}