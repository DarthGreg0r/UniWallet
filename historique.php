<?php
// Créé le 02:06:25 à 13:21:00

require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

if (!isAdminAuthenticated()) {
    header('Location: index.php');
    exit;
}

$historique = $pdo->query("SELECT * FROM journal ORDER BY date DESC")->fetchAll();

include 'header.php';
?>

<div class="p-4">
    <h2 class="text-xl font-bold mb-4">Historique des modifications</h2>
    <table class="w-full table-auto border border-gray-300">
        <thead class="bg-gray-100">
            <tr>
                <th class="border p-2">Date</th>
                <th class="border p-2">Action</th>
                <th class="border p-2">Contrat</th>
                <th class="border p-2">Ancienne valeur</th>
                <th class="border p-2">Nouvelle valeur</th>
                <th class="border p-2">Utilisateur</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($historique as $log): ?>
                <tr>
                    <td class="border p-2"><?php echo $log['date']; ?></td>
                    <td class="border p-2"><?php echo $log['action']; ?></td>
                    <td class="border p-2"><?php echo $log['contrat_id']; ?></td>
                    <td class="border p-2"><?php echo $log['ancienne_valeur']; ?></td>
                    <td class="border p-2"><?php echo $log['nouvelle_valeur']; ?></td>
                    <td class="border p-2"><?php echo $log['utilisateur']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
