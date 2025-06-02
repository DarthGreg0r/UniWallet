<?php
// Créé le 02:06:25 à 13:02:00

require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

if (!isAdminAuthenticated()) {
    header('Location: index.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $capital = floatval($_POST['capital']);
    $benefices = floatval($_POST['benefices']);
    $commission = $benefices * 0.5;
    $valeur_totale = $capital + ($benefices - $commission);

    // Vérifie si une entrée existe déjà pour cette date
    $check = $pdo->prepare("SELECT COUNT(*) FROM performances WHERE date = ?");
    $check->execute([$date]);
    if ($check->fetchColumn() > 0) {
        $message = 'Une entrée existe déjà pour cette date.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO performances (date, capital_total, benefices, commission, valeur_totale) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$date, $capital, $benefices, $commission, $valeur_totale]);
        $message = 'Performance ajoutée avec succès.';
    }
}

$performances = $pdo->query("SELECT * FROM performances ORDER BY date DESC")->fetchAll();
?>

<?php include 'header.php'; ?>

<div class="p-4">
    <h2 class="text-xl font-bold mb-4">Saisie des performances du portefeuille</h2>

    <?php if ($message): ?>
        <div class="mb-4 text-green-600 font-semibold"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" class="mb-6 space-y-2">
        <input type="date" name="date" required class="border p-2 rounded">
        <input type="number" name="capital" step="0.01" placeholder="Capital total" required class="border p-2 rounded">
        <input type="number" name="benefices" step="0.01" placeholder="Bénéfices réalisés" required class="border p-2 rounded">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Ajouter</button>
    </form>

    <table class="min-w-full table-auto border border-gray-300">
        <thead class="bg-gray-100">
            <tr>
                <th class="border p-2">Date</th>
                <th class="border p-2">Capital</th>
                <th class="border p-2">Bénéfices</th>
                <th class="border p-2">Commission (50%)</th>
                <th class="border p-2">Valeur totale</th>
                <th class="border p-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($performances as $perf): ?>
                <tr>
                    <td class="border p-2"><?php echo $perf['date']; ?></td>
                    <td class="border p-2"><?php echo $perf['capital_total']; ?> $</td>
                    <td class="border p-2"><?php echo $perf['benefices']; ?> $</td>
                    <td class="border p-2"><?php echo $perf['commission']; ?> $</td>
                    <td class="border p-2"><?php echo $perf['valeur_totale']; ?> $</td>
                    <td class="border p-2">
                        <a href="modifier_performance.php?id=<?php echo $perf['id']; ?>" class="text-blue-600">Modifier</a> |
                        <a href="supprimer_performance.php?id=<?php echo $perf['id']; ?>" class="text-red-600" onclick="return confirm('Confirmer la suppression ?');">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
