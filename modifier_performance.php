<?php
// Créé le 02:06:25 à 13:11:00

require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

if (!isAdminAuthenticated()) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "ID de performance manquant.";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM performances WHERE id = ?");
$stmt->execute([$id]);
$performance = $stmt->fetch();

if (!$performance) {
    echo "Performance introuvable.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $capital = floatval($_POST['capital']);
    $benefices = floatval($_POST['benefices']);
    $commission = $benefices * 0.5;
    $valeur_totale = $capital + ($benefices - $commission);

    $update = $pdo->prepare("UPDATE performances SET date = ?, capital_total = ?, benefices = ?, commission = ?, valeur_totale = ? WHERE id = ?");
    $update->execute([$date, $capital, $benefices, $commission, $valeur_totale, $id]);

    header("Location: wallet.php");
    exit;
}
?>

<?php include 'header.php'; ?>

<div class="p-4">
    <h2 class="text-xl font-bold mb-4">Modifier la performance</h2>
    <form method="POST" class="space-y-2">
        <input type="date" name="date" value="<?php echo $performance['date']; ?>" required class="border p-2 rounded">
        <input type="number" name="capital" value="<?php echo $performance['capital_total']; ?>" step="0.01" required class="border p-2 rounded">
        <input type="number" name="benefices" value="<?php echo $performance['benefices']; ?>" step="0.01" required class="border p-2 rounded">
        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Mettre à jour</button>
    </form>
</div>

<?php include 'footer.php'; ?>
