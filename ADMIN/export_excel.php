<?php
session_start();
require_once('../config/db.php');

// Sécurité : Seul l'admin peut exporter
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit("Accès refusé");
}

// 1. Définir les headers pour Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Statistiques_Membres_" . date('Y-m-d') . ".xls");

// 2. Récupération des données avec COMPTAGE des emprunts
// On utilise LEFT JOIN pour inclure même ceux qui n'ont jamais rien emprunté (affichera 0)
$sql = "SELECT u.id, u.nom, u.email, u.role, u.telephone, u.adresse, COUNT(e.id) AS nb_emprunts 
        FROM utilisateurs u
        LEFT JOIN emprunts e ON u.id = e.id_utilisateur
        GROUP BY u.id
        ORDER BY nb_emprunts DESC";

$query = $pdo->query($sql);
$utilisateurs = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
    .table-excel { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; }
    .table-excel th { 
        background-color: #d4a373; 
        color: white; 
        padding: 10px;
        border: 1px solid #444;
    }
    .table-excel td { 
        padding: 8px;
        border: 1px solid #ccc; 
        text-align: left;
    }
    .row-even { background-color: #f9f9f9; }
    .badge-admin { color: #d4a373; font-weight: bold; }
</style>

<table class="table-excel">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom Complet</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Adresse</th>
            <th>Rôle</th>
            <th>Total Emprunts</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($utilisateurs as $index => $user): ?>
            <tr class="<?php echo ($index % 2 == 0) ? 'row-even' : ''; ?>">
                <td>#<?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['nom']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['telephone']); ?></td>
                <td><?php echo htmlspecialchars($user['adresse']); ?></td>
                <td class="<?php echo ($user['role'] == 'admin') ? 'badge-admin' : ''; ?>">
                    <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                </td>
                <td style="text-align: center; font-weight: bold;">
                    <?php echo $user['nb_emprunts']; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>