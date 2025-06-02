<?php
// Créé le 02:06:25 à 13:28:00

require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

if (!isAdminAuthenticated()) {
    header('Location: index.php');
    exit;
}

// Chemin de sauvegarde local (personnalisable)
$backupDir = __DIR__ . '/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0775, true);
}

// Traitement import si un fichier est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['backup_file'])) {
    $fileTmp = $_FILES['backup_file']['tmp_name'];
    if (file_exists($fileTmp)) {
        $importSQL = file_get_contents($fileTmp);
        $pdo->exec($importSQL);
        header("Location: dashboard.php?import=success");
        exit;
    }
}

// Traitement export (sauvegarde)
$timestamp = date('Ymd_His');
$backupFile = "$backupDir/backup_$timestamp.sql";

$tables = ["clients", "performances", "upv", "retraits", "journal", "rapports", "admin"];

$sqlDump = "-- Backup du ".date('d/m/Y H:i:s')."\n\n";

foreach ($tables as $table) {
    $res = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
    $sqlDump .= $res['Create Table'] . ";\n\n";

    $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $values = array_map(function ($val) use ($pdo) {
            return $pdo->quote($val);
        }, array_values($row));
        $sqlDump .= "INSERT INTO `$table` VALUES (" . implode(", ", $values) . ");\n";
    }
    $sqlDump .= "\n\n";
}

file_put_contents($backupFile, $sqlDump);

// Affichage interface d'import/export
include 'header.php';
?>

<div class="p-4">
    <h2 class="text-xl font-bold mb-4">Sauvegarde et restauration</h2>

    <form method="post" enctype="multipart/form-data" class="mb-6">
        <label class="block mb-2 font-medium">Importer une sauvegarde (.sql) :</label>
        <input type="file" name="backup_file" accept=".sql" required class="mb-2">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Restaurer</button>
    </form>

    <form method="get">
        <button formaction="backup.php" class="bg-green-600 text-white px-4 py-2 rounded">Télécharger une nouvelle sauvegarde</button>
    </form>
</div>

<?php include 'footer.php'; ?>
