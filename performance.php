<?php
// performance.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

include 'header.php';

// Suppression d'une performance
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM performances WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: performance.php");
    exit();
}

// Mise à jour d'une performance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $stmt = $pdo->prepare("UPDATE performances SET date_performance = ?, valeur_brute = ?, gain_brut = ?, commission_prelevee = ?, valeur_nette = ?, total_upv = ?, valeur_upv = ? WHERE id = ?");
    $stmt->execute([
        $_POST['date_performance'],
        $_POST['valeur_brute'],
        $_POST['gain_brut'],
        $_POST['commission_prelevee'],
        $_POST['valeur_nette'],
        $_POST['total_upv'],
        $_POST['valeur_upv'],
        $_POST['update_id']
    ]);
    header("Location: performance.php");
    exit();
}

// Ajout d'une nouvelle performance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['update_id'])) {
    $date = $_POST['date_performance'];
    $valeur_brute = (float)$_POST['valeur_brute'];

    $stmt = $pdo->query("SELECT valeur_nette FROM performances ORDER BY date_performance DESC LIMIT 1");
    $valeur_nette_prec = $stmt->fetchColumn();
    $valeur_nette_prec = $valeur_nette_prec !== false ? (float)$valeur_nette_prec : $valeur_brute;

    $gain_brut = $valeur_brute - $valeur_nette_prec;
    $commission = $gain_brut > 0 ? $gain_brut * 0.5 : 0;
    $valeur_nette = $valeur_brute - $commission;

    $stmt = $pdo->query("SELECT SUM(nb_upv) FROM clients WHERE statut = 'actif'");
    $total_upv = (float)$stmt->fetchColumn();
    $valeur_upv = $total_upv > 0 ? $valeur_nette / $total_upv : 1;

    $stmt = $pdo->prepare("INSERT INTO performances (date_performance, valeur_brute, gain_brut, commission_prelevee, valeur_nette, total_upv, valeur_upv) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$date, $valeur_brute, $gain_brut, $commission, $valeur_nette, $total_upv, $valeur_upv]);

    header("Location: performance.php");
    exit();
}

$stmt = $pdo->query("SELECT * FROM performances ORDER BY date_performance DESC");
$performances = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Performance du portefeuille</title>
    <style>
        body { font-family: Arial; background: #f1f5f9; padding: 30px; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #f0f0f0; }
        form { margin-bottom: 30px; background: white; padding: 20px; border: 1px solid #ccc; }
    </style>
</head>
<body>
<h2>Ajouter une performance mensuelle</h2>
<form method="post">
    <label>Date :</label>
    <input type="date" name="date_performance" required>
    <label>Valeur brute du portefeuille :</label>
    <input type="number" name="valeur_brute" step="0.01" required>
    <button type="submit">Ajouter</button>
</form>

<h2>Historique des performances</h2>
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Valeur brute</th>
            <th>Gain brut</th>
            <th>Commission (50%)</th>
            <th>Valeur nette</th>
            <th>Total UPV</th>
            <th>Valeur d'1 UPV</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!empty($performances)): foreach ($performances as $perf): ?>
        <tr>
            <form method="post">
                <td><input type="date" name="date_performance" value="<?= htmlspecialchars($perf['date_performance']) ?>" required></td>
                <td><input type="number" name="valeur_brute" step="0.01" value="<?= $perf['valeur_brute'] ?>" required></td>
                <td><input type="number" name="gain_brut" step="0.01" value="<?= $perf['gain_brut'] ?>" required></td>
                <td><input type="number" name="commission_prelevee" step="0.01" value="<?= $perf['commission_prelevee'] ?>" required></td>
                <td><input type="number" name="valeur_nette" step="0.01" value="<?= $perf['valeur_nette'] ?>" required></td>
                <td><input type="number" name="total_upv" step="0.00000001" value="<?= $perf['total_upv'] ?>" required></td>
                <td><input type="number" name="valeur_upv" step="0.00000001" value="<?= $perf['valeur_upv'] ?>" required></td>
                <td>
                    <input type="hidden" name="update_id" value="<?= $perf['id'] ?>">
                    <button type="submit">Modifier</button>
                    <a href="performance.php?delete=<?= $perf['id'] ?>" onclick="return confirm('Supprimer cette entrée ?');">Supprimer</a>
                </td>
            </form>
        </tr>
    <?php endforeach; else: ?>
        <tr><td colspan="8">Aucune donnée de performance.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</body>
</html>

