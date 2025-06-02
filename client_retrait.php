<?php
// Créé le 02:06:25 à 11:52:00

require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

if (!isAdminAuthenticated()) {
    header('Location: index.php');
    exit;
}

// Traitement du retrait
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contractId = $_POST['contract_id'] ?? '';
    $dateSortie = $_POST['date_sortie'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM clients WHERE contrat_id = ? AND statut = 'actif'");
    $stmt->execute([$contractId]);
    $client = $stmt->fetch();

    if (!$client) {
        $error = "Contrat introuvable ou déjà retiré.";
    } else {
        // Calcul des mois entre entrée et sortie
        $months = monthsBetween($client['date_entree'], $dateSortie);
        $penalite = ($months < 6) ? 0.10 : 0.00;

        // Récupération des performances et UPV
        $stmtPerf = $pdo->query("SELECT SUM(benefices) as total_benef FROM performances");
        $totalBenefices = $stmtPerf->fetch()['total_benef'];

        $stmtUPV = $pdo->prepare("SELECT SUM(nombre_upv) FROM upv");
        $stmtUPV->execute();
        $totalUPV = $stmtUPV->fetchColumn();

        $stmtClientUPV = $pdo->prepare("SELECT SUM(nombre_upv) FROM upv WHERE contrat_id = ?");
        $stmtClientUPV->execute([$contractId]);
        $clientUPV = $stmtClientUPV->fetchColumn();

        $ratio = $clientUPV / $totalUPV;
        $partBenef = $totalBenefices * 0.50 * $ratio;
        $montantBrut = $client['capital'] + $partBenef;
        $montantFinal = $montantBrut - ($client['capital'] * $penalite);

        // Mise à jour client
        $update = $pdo->prepare("UPDATE clients SET statut = 'retire', date_sortie = ?, penalite = ?, performance = ?, montant_final = ? WHERE contrat_id = ?");
        $update->execute([$dateSortie, $penalite * 100, round(($partBenef / $client['capital']) * 100, 2), $montantFinal, $contractId]);

        // Suppression des UPV
        $deleteUPV = $pdo->prepare("DELETE FROM upv WHERE contrat_id = ?");
        $deleteUPV->execute([$contractId]);

        // Journalisation
        $log = $pdo->prepare("INSERT INTO journal (contrat_id, action, detail, date_action) VALUES (?, 'retrait', ?, NOW())");
        $log->execute([$contractId, "Retrait client avec performance {$partBenef} et pénalité {$penalite}"]);

        $success = true;
    }
}
?>

<?php include 'header.php'; ?>
<div class="p-4">
    <h2 class="text-xl font-bold mb-4">Retrait d'un client</h2>
    <?php if (!empty($success)): ?>
        <div class="p-4 bg-green-100 border border-green-300">Le retrait a été effectué avec succès.</div>
    <?php elseif (!empty($error)): ?>
        <div class="p-4 bg-red-100 border border-red-300"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
        <div>
            <label for="contract_id">Numéro de contrat</label>
            <input type="text" name="contract_id" id="contract_id" required class="input">
        </div>
        <div>
            <label for="date_sortie">Date de sortie</label>
            <input type="date" name="date_sortie" id="date_sortie" required class="input">
        </div>
        <button type="submit" class="btn btn-primary">Valider le retrait</button>
    </form>
</div>
<?php include 'footer.php'; ?>
