<?php
// Créé le 02:06:25 à 13:24:00

require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

if (!isAdminAuthenticated()) {
    header('Location: index.php');
    exit;
}

if (isset($_POST['export_clients'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="clients_export.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Statut', 'Contrat ID', 'Nom', 'Email', 'Date Entrée', 'Capital', 'Performance', 'Montant Final', 'Pénalité', 'UPV', 'Date Sortie']);
    $stmt = $pdo->query("SELECT * FROM clients");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

if (isset($_POST['export_journal'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="journal_export.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Date', 'Action', 'Contrat ID', 'Ancienne Valeur', 'Nouvelle Valeur', 'Utilisateur']);
    $stmt = $pdo->query("SELECT * FROM journal");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

include 'header.php';
?>

<div class="p-4">
    <h2 class="text-xl font-bold mb-4">Export / Import CSV</h2>

    <form method="post" class="space-y-4">
        <button name="export_clients" class="bg-blue-500 text-white px-4 py-2 rounded">Exporter les clients</button>
        <button name="export_journal" class="bg-green-500 text-white px-4 py-2 rounded">Exporter l'historique</button>
    </form>
</div>

<?php include 'footer.php'; ?>
