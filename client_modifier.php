<?php
// Fichier : client_modifier.php
// Créé le : 02/06/25 à 12:17:00

require_once 'db.php';
require_once 'header.php';

$message = '';
$contrat_id = $_GET['id'] ?? null;

if (!$contrat_id) {
    echo "<div class='p-4 text-red-600'>ID de contrat manquant.</div>";
    exit;
}

// Charger les données existantes
$stmt = $pdo->prepare("SELECT * FROM clients WHERE contrat_id = ?");
$stmt->execute([$contrat_id]);
$client = $stmt->fetch();

if (!$client) {
    echo "<div class='p-4 text-red-600'>Client introuvable.</div>";
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $capital = floatval($_POST['capital']);
    $date_entree = $_POST['date_entree'];
    $statut = $_POST['statut'];

    if ($capital <= 0 || empty($email) || empty($nom)) {
        $message = "Tous les champs sont obligatoires et le capital doit être positif.";
    } else {
        $stmt = $pdo->prepare("UPDATE clients SET nom = ?, email = ?, capital_depose = ?, date_entree = ?, statut = ? WHERE contrat_id = ?");
        $stmt->execute([$nom, $email, $capital, $date_entree, $statut, $contrat_id]);

        $stmt = $pdo->prepare("INSERT INTO journal (action, contrat_id, details, date_action) VALUES (?, ?, ?, NOW())");
        $stmt->execute(['modification_client', $contrat_id, "Modification client (capital : $capital, statut : $statut)"]);

        $message = "✅ Modifications enregistrées.";
    }
}
?>

<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Modifier un client</h2>

    <?php if (!empty($message)) : ?>
        <div class="mb-4 text-green-600"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
        <div>
            <label class="block text-sm font-medium">Nom ou pseudo</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($client['nom']) ?>" class="border rounded w-full p-2" required>
        </div>

        <div>
            <label class="block text-sm font-medium">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($client['email']) ?>" class="border rounded w-full p-2" required>
        </div>

        <div>
            <label class="block text-sm font-medium">Montant déposé (USDT/USDC)</label>
            <input type="number" name="capital" step="0.01" value="<?= $client['capital_depose'] ?>" class="border rounded w-full p-2" required>
        </div>

        <div>
            <label class="block text-sm font-medium">Date d'entrée</label>
            <input type="date" name="date_entree" value="<?= $client['date_entree'] ?>" class="border rounded w-full p-2" required>
        </div>

        <div>
            <label class="block text-sm font-medium">Statut</label>
            <select name="statut" class="border rounded w-full p-2">
                <option value="actif" <?= $client['statut'] === 'actif' ? 'selected' : '' ?>>Actif</option>
                <option value="retire" <?= $client['statut'] === 'retire' ? 'selected' : '' ?>>Retiré</option>
                <option value="penalite" <?= $client['statut'] === 'penalite' ? 'selected' : '' ?>>Sous pénalité</option>
            </select>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Enregistrer</button>
    </form>
</div>
