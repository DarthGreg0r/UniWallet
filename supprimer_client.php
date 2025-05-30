<?php
session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Filtres
$statut = $_GET['statut'] ?? 'tous';
$recherche = $_GET['q'] ?? '';

$sql = "SELECT * FROM clients WHERE 1";
$params = [];

if ($statut !== 'tous') {
    $sql .= " AND statut = ?";
    $params[] = $statut;
}

if ($recherche) {
    $sql .= " AND (email LIKE ? OR pseudo LIKE ?)";
    $params[] = "%$recherche%";
    $params[] = "%$recherche%";
}

$sql .= " ORDER BY date_entree DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clients = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Clients – UniWallet</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; padding: 30px; }
        h1 { color: #111827; }
        .filter-box { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; }
        input, select { padding: 8px; margin: 5px 0; width: 100%; }
        table { width: 100%; background: white; border-collapse: collapse; border-radius: 8px; overflow: hidden; }
        th, td { padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        th { background: #f9fafb; }
        .actions a { margin-right: 10px; }
        .btn { padding: 5px 10px; border-radius: 5px; text-decoration: none; }
        .btn.edit { background: #2563eb; color: white; }
        .btn.delete { background: #b91c1c; color: white; }
        .btn.retirer { background: #065f46; color: white; }
    </style>
</head>
<body>

<h1>Liste des clients – <?= APP_NAME ?></h1>

<div class="filter-box">
    <form method="GET">
        <label>Statut :</label>
        <select name="statut">
            <option value="tous" <?= $statut === 'tous' ? 'selected' : '' ?>>Tous</option>
            <option value="actif" <?= $statut === 'actif' ? 'selected' : '' ?>>Actifs</option>
            <option value="sorti" <?= $statut === 'sorti' ? 'selected' : '' ?>>Sortis</option>
        </select>

        <label>Recherche (email ou pseudo) :</label>
        <input type="text" name="q" value="<?= htmlspecialchars($recherche) ?>">

        <input type="submit" value="Filtrer">
    </form>
</div>

<table>
    <thead>
        <tr>
            <th>Email</th>
            <th>Pseudo</th>
            <th>Capital</th>
            <th>Entrée</th>
            <th>Sortie</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($clients)): ?>
            <tr><td colspan="7">Aucun client trouvé.</td></tr>
        <?php else: ?>
            <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?= htmlspecialchars($client['email']) ?></td>
                    <td><?= htmlspecialchars($client['pseudo']) ?></td>
                    <td><?= number_format($client['capital_depose'], 2) ?> USDC</td>
                    <td><?= $client['date_entree'] ?></td>
                    <td><?= $client['date_sortie'] ?? '-' ?></td>
                    <td><?= $client['statut'] ?></td>
                    <td class="actions">
                        <?php if ($client['statut'] === 'actif'): ?>
                            <a class="btn edit" href="modifier_client.php?id=<?= $client['id'] ?>">Modifier</a>
                            <a class="btn retirer" href="retirer.php?id=<?= $client['id'] ?>">Retirer</a>
                        <?php endif; ?>
                        <a class="btn delete" href="supprimer_client.php?id=<?= $client['id'] ?>"
                           onclick="return confirm('❗ Cette action est irréversible.\n\nConfirmer la suppression du client : <?= htmlspecialchars($client['email']) ?> ?')">
                           Supprimer
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<p><a href="dashboard.php">← Retour au dashboard</a></p>

</body>
</html>

