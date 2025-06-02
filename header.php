<?php
// Créé le 02:06:25 - 12:10:00

require_once 'db.php';

// Récupérer les totaux pour le header
$stmt = $pdo->query("SELECT 
    SUM(capital_initial) AS capital_total,
    SUM(montant_net) AS valeur_totale
    FROM clients 
    WHERE statut = 'actif'");
$totaux = $stmt->fetch();

$capital = number_format($totaux['capital_total'], 2);
$valeur = number_format($totaux['valeur_totale'], 2);
$evolution = $totaux['capital_total'] > 0 ? round((($totaux['valeur_totale'] - $totaux['capital_total']) / $totaux['capital_total']) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?= APP_NAME ?> - Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <header class="bg-white shadow p-4 flex justify-between items-center">
    <h1 class="text-xl font-bold"><?= APP_NAME ?> <span class="text-sm text-gray-500">v<?= APP_VERSION ?></span></h1>
    <nav class="space-x-4">
      <a href="dashboard.php" class="text-blue-600 hover:underline">Dashboard</a>
      <a href="clients.php" class="text-blue-600 hover:underline">Clients</a>
      <a href="wallet.php" class="text-blue-600 hover:underline">Portefeuille</a>
      <a href="historique.php" class="text-blue-600 hover:underline">Historique</a>
      <a href="exportimport.php" class="text-blue-600 hover:underline">Export/Import</a>
    </nav>
  </header>

  <section class="p-4 bg-blue-50 border-b">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center text-sm">
      <div><strong>Capital déposé :</strong> <?= $capital ?> $</div>
      <div><strong>Valeur actuelle :</strong> <?= $valeur ?> $</div>
      <div><strong>Évolution :</strong> <?= $evolution ?> %</div>
    </div>
  </section>
