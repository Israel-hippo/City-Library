<?php
session_start();
require_once('../config/db.php');
if(isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("DELETE FROM inscriptions_events WHERE id = ? AND id_utilisateur = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
}
header("Location: ../LECTEUR/profile.php");