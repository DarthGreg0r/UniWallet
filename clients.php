<?php
// 02:06:2025 - 12:15:00
require_once 'db.php';
require_once 'header.php';

// Récupération des filtres
$filtre_statut = $_GET['statut'] ?? 'tous';
$filtre_email = $_GET['email'] ?? '';
$filtre_contrat = $_GET['contrat'] ?? '';

// Construction dynamique de la requête SQL
$conditions = [];
$params = [];

if ($filtre_statut != 'tous') {
    $conditions[] = 'statut = ?';
    $params[] = $filtre_statut;
}
if (!empty($filtre_email)) {
    $conditions[] = 'email LIKE ?';
    $params[] = '%' . $filtre_email . '%';
}
if (!empty($filtre_contrat)) {
    $conditions[] = 'contrat_id = ?';
    $params[] = $filtre_contrat;
}

$where_clause = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
$sql = "SELECT * FROM clients $where_clause ORDER BY date_entree DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="p-4">
  <h2 class="text-xl font-semibold mb-4">📋 Gestion des clients</h2>

  <form method="get" class="flex flex-wrap gap-2 mb-4">
    <select name="statut" class="border p-1">
      <option value="tous" <?= $filtre_statut === 'tous' ? 'selected' : '' ?>>Tous les statuts</option>
      <option value="actif" <?= $filtre_statut === 'actif' ? 'selected' : '' ?>>Actif</option>
      <option value="retire" <?= $filtre_statut === 'retire' ? 'selected' : '' ?>>Retiré</option>
      <option value="penalite" <?= $filtre_statut === 'penalite' ? 'selected' : '' ?>>Sous pénalité</option>
    </select>
    <input type="text" name="email" placeholder="Email" value="<?= htmlspecialchars($filtre_email) ?>" class="border p-1" />
    <input type="text" name="contrat" placeholder="Contrat ID" value="<?= htmlspecialchars($filtre_contrat) ?>" class="border p-1" />
    <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded">Filtrer</button>
  </form>

  <a href="client_ajouter.php" class="inline-block bg-green-600 text-white px-3 py-2 rounded mb-4">➕ Ajouter un client</a>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm border-collapse border border-gray-300">
      <thead class="bg-gray-100">
        <tr>
          <th class="border px-2 py-1">Statut</th>
          <th class="border px-2 py-1">Contrat ID</th>
          <th class="border px-2 py-1">Nom/Pseudo</th>
          <th class="border px-2 py-1">Email</th>
          <th class="border px-2 py-1">Date entrée</th>
          <th class="border px-2 py-1">Capital déposé</th>
          <th class="border px-2 py-1">Perf. portefeuille</th>
          <th class="border px-2 py-1">Montant brut</th>
          <th class="border px-2 py-1">Montant net</th>
          <th class="border px-2 py-1">UPV</th>
          <th class="border px-2 py-1">Sortie</th>
          <th class="border px-2 py-1">Pénalité</th>
          <th class="border px-2 py-1">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($clients as $c): ?>
          <tr>
            <td class="border px-2 py-1"><?= htmlspecialchars($c['statut']) ?></td>
            <td class="border px-2 py-1"><?= htmlspecialchars($c['contrat_id']) ?></td>
            <td class="border px-2 py-1"><?= htmlspecialchars($c['nom']) ?></td>
            <td class="border px-2 py-1"><?= htmlspecialchars($c['email']) ?></td>
            <td class="border px-2 py-1"><?= $c['date_entree'] ?></td>
            <td class="border px-2 py-1"><?= number_format($c['capital_usd'], 2) ?> $</td>
            <td class="border px-2 py-1"><?= $c['performance'] ?>%</td>
            <td class="border px-2 py-1"><?= number_format($c['montant_brut'], 2) ?> $</td>
            <td class="border px-2 py-1"><?= number_format($c['montant_net'], 2) ?> $</td>
            <td class="border px-2 py-1"><?= $c['upv'] ?></td>
            <td class="border px-2 py-1"><?= $c['date_sortie'] ?: '-' ?></td>
            <td class="border px-2 py-1"><?= $c['penalite_appliquee'] ? 'Oui' : 'Non' ?></td>
            <td class="border px-2 py-1">
              <a href="client_detail.php?id=<?= $c['id'] ?>" class="text-blue-500">📄</a>
              <a href="client_modifier.php?id=<?= $c['id'] ?>" class="text-yellow-500">✏️</a>
              <a href="client_retrait.php?id=<?= $c['id'] ?>" class="text-purple-500">💸</a>
              <a href="client_supprimer.php?id=<?= $c['id'] ?>" class="text-red-600" onclick="return confirm('Supprimer ce client ?')">🗑️</a>
            </td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
