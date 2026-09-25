<?php
session_start();
require_once('../config/db.php');

// SÉCURITÉ : Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../PAGE/login.php");
    exit();
}

// Vérifier si l'ID est présent dans l'URL
if (isset($_GET['id'])) {
    $id_a_supprimer = $_GET['id'];

    // Empêcher l'admin de se supprimer lui-même par accident
    if ($id_a_supprimer == $_SESSION['user_id']) {
        header("Location: admin.php?error=self_delete");
        exit();
    }

    try {
        // Exécution de la suppression
        $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id_a_supprimer]);
    } catch (PDOException $e) {
        // Si l'utilisateur a des emprunts en cours (contrainte de clé étrangère)
        header("Location: admin.php?error=foreign_key");
        exit();
    }
}

// Retour direct à la page de gestion des membres (et pas le dashboard)
header("Location: admin.php");
exit();
?>