<?php
session_start();
require_once('../config/db.php');

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // On supprime d'abord les inscriptions liées pour éviter les erreurs de clé étrangère
    $pdo->prepare("DELETE FROM inscriptions_evenements WHERE id_evenement = ?")->execute([$id]);
    
    // Puis on supprime l'événement
    $stmt = $pdo->prepare("DELETE FROM evenements WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: admin_dashboard.php");
exit();