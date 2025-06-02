<?php
// Créé le 02:06:25 à 13:17:00

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

if (!isAdminAuthenticated()) {
    header('Location: index.php');
    exit;
}

// Nombre total de clients actifs
$totalClients = $pdo->query("SELECT COUNT(*) FROM clients WHERE statut = 'actif'")->fetchColumn();

// Total des fonds déposés
$totalCapital = $pdo->query("SELECT SUM(capital) FROM clients WHERE statut = 'actif'")->fetchColumn();

// Performance cumulée (%), dernière ligne saisie
$lastPerf = $pdo->query("SELECT * FROM performances ORDER BY date DESC LIMIT 1")->fetch();

// Total des UPV
$totalUPV = $pdo->query("SELECT SUM(nombre_upv) FROM upv")->fetchColumn();

// Historique récent (5 dernières actions)
$historique = $pdo->query("SELECT * FROM journal ORDER BY date DESC LIMIT 5")->fetchAll();

include 'header.php';
?>

<div class="p-4">
    <h2 class="text-xl font-bold mb-4">Tableau de bord</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white shadow rounded p-4">
            <h3 class="text-gray-700 font-semibold">Clients actifs</h3>
            <p class="text-2xl text-blue-600"><?php echo $totalClients; ?></p>
        </div>
        <div class="bg-white shadow rounded p-4">
            <h3 class="text-gray-700 font-semibold">Capital total déposé</h3>
            <p class="text-2xl text-green-600"><?php echo number_format($totalCapital, 2); ?> $</p>
        </div>
        <div class="bg-white shadow rounded p-4">
            <h3 class="text-gray-700 font-semibold">Performance cumulée</h3>
            <p class="text-2xl text-purple-600"><?php echo $lastPerf ? $lastPerf['benefices'] . ' $' : 'N/A'; ?></p>
        </div>
        <div class="bg-white shadow rounded p-4">
            <h3 class="text-gray-700 font-semibold">Total des UPV</h3>
            <p class="text-2xl text-orange-600"><?php echo number_format($totalUPV, 2); ?></p>
        </div>
    </div>

    <h3 class="text-lg font-semibold mb-2">5 dernières modifications</h3>
    <table class="w-full table-auto border border-gray-300">
        <thead class="bg-gray-100">
            <tr>
                <th class="border p-2">Date</th>
                <th class="border p-2">Action</th>
                <th class="border p-2">Contrat</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($historique as $log): ?>
                <tr>
                    <td class="border p-2"><?php echo $log['date']; ?></td>
                    <td class="border p-2"><?php echo $log['action']; ?></td>
                    <td class="border p-2"><?php echo $log['contrat_id']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
