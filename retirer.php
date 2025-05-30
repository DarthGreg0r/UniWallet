<?php
session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: clients.php');
    exit();
}

// Récupération du client
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$id]);
$client = $stmt->fetch();

if (!$client || $client['statut'] === 'sorti') {
    echo "Client introuvable ou déjà sorti.";
    exit();
}

$confirmation = '';
$capital = floatval($client['capital_depose']);
$benefice = 0.00;
$montant_total = $capital + $benefice;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_sortie = $_POST['date_sortie'] ?? date('Y-m-d');

    // Calcul durée
    $date_entree = new DateTime($client['date_entree']);
    $date_fin = new DateTime($date_sortie);
    $interval = $date_entree->diff($date_fin);
    $mois = ($interval->y * 12) + $interval->m;

    // Pénalité
    $penalite = $mois < 6 ? true : false;
    $montant_final = $penalite ? $montant_total * 0.90 : $montant_total;

    // MAJ client
    $update = $pdo->prepare("UPDATE clients SET date_sortie = ?, statut = 'sorti', penalite_appliquee = ?, montant_final = ? WHERE id = ?");
    $update->execute([$date_sortie, $penalite, $montant_final, $id]);

    // Transaction
    $insert = $pdo->prepare("INSERT INTO transactions (client_id, type, montant, date_transaction) VALUES (?, 'retrait', ?, ?)");
    $insert->execute([$id, $montant_final, $date_sortie]);

    $confirmation = "✅ Retrait effectué :<br>
    Capital : " . number_format($capital, 2) . " USDC<br>
    Bénéfices : " . number_format($benefice, 2) . " USDC<br>
    Montant brut : " . number_format($montant_total, 2) . " USDC<br>" .
    ($penalite ? "Pénalité : 10%<br>" : "") .
    "<strong>Montant net reversé : " . number_format($montant_final, 2) . " USDC</strong>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Retirer un client – UniWallet</title>
    <style>
        body { font-family: Arial; padding: 30px; background: #f9fafb; }
        .box { background: white; padding: 20px; max-width: 600px; margin: auto; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin: 10px 0; }
        .success { background: #ecfdf5; color: #065f46; padding: 10px; border-radius: 6px; }
    </style>
</head>
<body>

<div class="box">
    <h2>Retrait du client : <?= htmlspecialchars($client['email']) ?></h2>

    <?php if ($confirmation): ?>
        <div class="success">
            <?= $confirmation ?>
        </div>
        <p><a href="clients.php">← Retour à la liste des clients</a></p>
    <?php else: ?>
        <?php
            $date_entree = new DateTime($client['date_entree']);
            $date_today = new DateTime();
            $mois = $date_entree->diff($date_today)->y * 12 + $date_entree->diff($date_today)->m;
        ?>
        <form method="POST">
            <label>Date de sortie :</label>
            <input type="date" name="date_sortie" value="<?= date('Y-m-d') ?>" required>

            <p><strong>Capital initial :</strong> <?= number_format($capital, 2) ?> USDC</p>
            <p><strong>Bénéfices réalisés :</strong> <?= number_format($benefice, 2) ?> USDC</p>
            <p><strong>Montant brut :</strong> <?= number_format($montant_total, 2) ?> USDC</p>
            <p><strong>Durée actuelle :</strong> <?= $mois ?> mois</p>
            <p><strong>Pénalité :</strong> <?= $mois < 6 ? 'Oui (10%)' : 'Non' ?></p>

            <input type="submit" value="Valider le retrait">
        </form>
    <?php endif; ?>
</div>

</body>
</html>

