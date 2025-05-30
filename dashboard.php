<?php
// dashboard.php
session_start();
require_once 'config.php';
require_once 'db.php';

// Vérifie si l'admin est connecté
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Récupère les données de base
$nb_clients = $pdo->query("SELECT COUNT(*) FROM clients WHERE statut = 'actif'")->fetchColumn();
$capital_total = $pdo->query("SELECT SUM(capital_depose) FROM clients WHERE statut = 'actif'")->fetchColumn();
$derniere_perf = $pdo->query("SELECT * FROM performances ORDER BY date_performance DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard – UniWallet</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        header { background: #111827; color: white; padding: 15px 30px; }
        main { padding: 30px; }
        .box { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        a.button { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #111827; color: white; text-decoration: none; border-radius: 6px; }
    </style>
</head>
<body>
    <header>
        <h1><?= APP_NAME ?> – Tableau de bord</h1>
    </header>

    <main>
        <div class="grid">
            <div class="box">
                <h2>Clients actifs</h2>
                <p><?= $nb_clients ?> client(s)</p>
            </div>
            <div class="box">
                <h2>Capital total déposé</h2>
                <p><?= number_format($capital_total, 2) ?> USDC</p>
            </div>
            <div class="box">
                <h2>Dernière performance</h2>
                <?php if ($derniere_perf): ?>
                    <p><?= $derniere_perf['date_performance'] ?> – <?= number_format($derniere_perf['valeur_portefeuille'], 2) ?> $</p>
                <?php else: ?>
                    <p>Aucune performance enregistrée.</p>
                <?php endif; ?>
            </div>
        </div>

        <a class="button" href="clients.php">Gérer les clients</a>
        <a class="button" href="performance.php">Mettre à jour la performance</a>
        <a class="button" href="logout.php" style="background: #991b1b;">Déconnexion</a>
    </main>
</body>
</html>

