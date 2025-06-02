<?php
// Fichier : client_ajouter.php
// Créé le : 02/06/25 à 11:52:00
require_once 'db.php';
require_once 'header.php';
require_once 'functions.php';

// Traitement du formulaire
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $capital = floatval($_POST['capital']);
    $date_entree = $_POST['date_entree'];

    // Sécurité : valeurs minimales
    if ($capital <= 0 || empty($email) || empty($nom)) {
        $message = "Tous les champs sont obligatoires et le capital doit être positif.";
    } else {
        // Générer ID unique de contrat (6 caractères alphanum)
        $contrat_id = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

        // Récupère la dernière valeur du portefeuille
        $stmt = $pdo->query("SELECT * FROM performances ORDER BY date DESC LIMIT 1");
        $performance = $stmt->fetch();

        if (!$performance || $performance['valeur_totale'] <= 0 || $performance['total_upv'] <= 0) {
            $message = "Erreur : impossible de déterminer la valeur d'une UPV (aucune performance saisie).";
        } else {
            $valeur_portefeuille = $performance['valeur_totale'];
            $total_upv = $performance['total_upv'];
            $valeur_upv = $valeur_portefeuille / $total_upv;

            // Calcul des UPV
            $upv_attribuees = $capital / $valeur_upv;

            // Insertion du client
            $stmt = $pdo->prepare("INSERT INTO clients (contrat_id, nom, email, capital_depose, date_entree, statut) VALUES (?, ?, ?, ?, ?, 'actif')");
            $stmt->execute([$contrat_id, $nom, $email, $capital, $date_entree]);

            // Insertion des UPV
            $stmt = $pdo->prepare("INSERT INTO upv (contrat_id, date_attribution, montant_depose, valeur_upv, nombre_upv) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$contrat_id, $date_entree, $capital, $valeur_upv, $upv_attribuees]);

            // Log
            $stmt = $pdo->prepare("INSERT INTO journal (action, contrat_id, details, date_action) VALUES (?, ?, ?, NOW())");
            $stmt->execute(['ajout_client', $contrat_id, "Capital déposé : $capital - UPV attribuées : $upv_attribuees"]);

            $message = "✅ Client ajouté avec succès. Contrat ID : $contrat_id";
        }
    }
}
?>

<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Ajouter un client</h2>

    <?php if (!empty($message)) : ?>
        <div class="mb-4 text-red-600"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
        <div>
            <label class="block text-sm font-medium">Nom ou pseudo</label>
            <input type="text" name="nom" class="border rounded w-full p-2" required>
        </div>

        <div>
            <label class="block text-sm font-medium">Email</label>
            <input type="email" name="email" class="border rounded w-full p-2" required>
        </div>

        <div>
            <label class="block text-sm font-medium">Montant déposé (USDT/USDC)</label>
            <input type="number" name="capital" step="0.01" class="border rounded w-full p-2" required>
        </div>

        <div>
            <label class="block text-sm font-medium">Date d'entrée</label>
            <input type="date" name="date_entree" class="border rounded w-full p-2" required>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Ajouter</button>
    </form>
</div>
