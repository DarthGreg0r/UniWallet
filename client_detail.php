<?php
// Créé le 02:06:25 à 12:52:00

require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

if (!isAdminAuthenticated()) {
    header('Location: index.php');
    exit;
}

$contractId = $_GET['id'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM clients WHERE contrat_id = ?");
$stmt->execute([$contractId]);
$client = $stmt->fetch();

if (!$client) {
    echo "<p>Contrat introuvable.</p>";
    exit;
}

$stmtUPV = $pdo->prepare("SELECT SUM(nombre_upv) FROM upv WHERE contrat_id = ?");
$stmtUPV->execute([$contractId]);
$upv = $stmtUPV->fetchColumn();

?>
<?php include 'header.php'; ?>
<div class="p-4">
    <h2 class="text-xl font-bold mb-4">Détails du client</h2>

    <table class="table-auto w-full border">
        <tr><th class="border px-4 py-2">Champ</th><th class="border px-4 py-2">Valeur</th></tr>
        <tr><td class="border px-4 py-2">Statut</td><td class="border px-4 py-2"><?php echo $client['statut']; ?></td></tr>
        <tr><td class="border px-4 py-2">Numéro de contrat</td><td class="border px-4 py-2"><?php echo $client['contrat_id']; ?></td></tr>
        <tr><td class="border px-4 py-2">Nom ou pseudo</td><td class="border px-4 py-2"><?php echo $client['nom']; ?></td></tr>
        <tr><td class="border px-4 py-2">Email</td><td class="border px-4 py-2"><?php echo $client['email']; ?></td></tr>
        <tr><td class="border px-4 py-2">Date d’entrée</td><td class="border px-4 py-2"><?php echo $client['date_entree']; ?></td></tr>
        <tr><td class="border px-4 py-2">Capital déposé</td><td class="border px-4 py-2"><?php echo $client['capital']; ?> $</td></tr>
        <tr><td class="border px-4 py-2">Performance</td><td class="border px-4 py-2"><?php echo $client['performance']; ?> %</td></tr>
        <tr><td class="border px-4 py-2">Montant total brut</td><td class="border px-4 py-2"><?php echo round($client['capital'] + ($client['capital'] * $client['performance'] / 100), 2); ?> $</td></tr>
        <tr><td class="border px-4 py-2">Montant final</td><td class="border px-4 py-2"><?php echo $client['montant_final']; ?> $</td></tr>
        <tr><td class="border px-4 py-2">Pénalité appliquée</td><td class="border px-4 py-2"><?php echo ($client['penalite'] > 0) ? 'Oui' : 'Non'; ?></td></tr>
        <tr><td class="border px-4 py-2">UPV attribuées</td><td class="border px-4 py-2"><?php echo $upv; ?></td></tr>
        <tr><td class="border px-4 py-2">Date de sortie</td><td class="border px-4 py-2"><?php echo $client['date_sortie'] ?? 'N/A'; ?></td></tr>
    </table>

    <div class="mt-4">
        <a href="clients.php" class="btn btn-secondary">⬅ Retour</a>
        <a href="imprimer_detail.php?id=<?php echo $contractId; ?>" class="btn btn-primary ml-2" target="_blank">🖨️ Imprimer / Exporter</a>
    </div>
</div>
<?php include 'footer.php'; ?>
