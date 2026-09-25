<?php
session_start();
require_once('../config/db.php');

// Sécurité : Seul l'admin peut exporter
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('Accès refusé');
}

// 1. Nom du fichier avec la date du jour
$nom_fichier = "inventaire_bibliotheque_" . date('d-m-Y') . ".csv";

// 2. Entêtes PHP pour forcer le téléchargement du fichier CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $nom_fichier);

// 3. Ouvrir le flux de sortie
$output = fopen('php://output', 'w');

// 4. Ajouter la ligne d'entête (titres des colonnes)
// On utilise fputcsv pour gérer automatiquement les virgules et guillemets
fputcsv($output, array('ID', 'Titre', 'Auteur', 'ISBN', 'Total Exemplaires', 'Disponibles'));

// 5. Récupérer les données de la base
$query = $pdo->query("SELECT id, titre, auteur, isbn, exemplaires_total, exemplaires_dispo FROM livres");

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}

// 6. Fermer le flux
fclose($output);
exit();
?>