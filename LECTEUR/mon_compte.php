<?php
$id_user = $_SESSION['user_id'];
$query = $pdo->prepare("
    SELECT e.*, l.titre 
    FROM emprunts e 
    JOIN livres l ON e.id_livre = l.id 
    WHERE e.id_utilisateur = ?
");
$query->execute([$id_user]);
$mes_emprunts = $query->fetchAll();
?>

<div class="card" style="margin: 20px;">
    <h3>Mes Emprunts</h3>
    <ul>
        <?php foreach ($mes_emprunts as $e): ?>
            <li style="color: <?php echo (strtotime($e['date_retour']) < time()) ? 'red' : 'white'; ?>">
                <?php echo $e['titre']; ?> - À rendre le : <?php echo $e['date_retour']; ?>
                <?php if(strtotime($e['date_retour']) < time()) echo " (EN RETARD !)"; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>