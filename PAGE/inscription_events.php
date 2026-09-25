<?php
session_start();
require_once('../config/db.php');

// 1. Sécurité : L'utilisateur doit être connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: events.php?status=auth_event");
    exit();
}

$id_user = $_SESSION['user_id'];
// On récupère le nom de l'event via l'URL (ex: ?event=Atelier Archives)
$event_name = isset($_GET['event']) ? $_GET['event'] : '';

if (empty($event_name)) {
    header("Location: events.php");
    exit();
}

// 2. Vérifier si l'utilisateur est déjà inscrit
$check = $pdo->prepare("SELECT id FROM inscriptions_events WHERE id_utilisateur = ? AND titre_event = ?");
$check->execute([$id_user, $event_name]);

if ($check->rowCount() > 0) {
    // Déjà inscrit -> redirection avec un message d'erreur
    header("Location: events.php?status=already_sub");
} else {
    // 3. Insertion de l'inscription
    $ins = $pdo->prepare("INSERT INTO inscriptions_events (id_utilisateur, titre_event) VALUES (?, ?)");
    if ($ins->execute([$id_user, $event_name])) {
        // Succès -> redirection vers le profil ou l'accueil
        header("Location: events.php?status=sub_ok");
    } else {
        header("Location: events.php?status=error");
    }
}
exit();