<?php
// ... connexion et session ...
$user_id = $_SESSION['user_id'];
$mes_livres = $pdo->prepare("SELECT e.*, l.titre FROM emprunts e JOIN livres l ON e.id_livre = l.id WHERE e.id_utilisateur = ?");
$mes_livres->execute([$user_id]);
$emprunts = $mes_livres->fetchAll();
?>