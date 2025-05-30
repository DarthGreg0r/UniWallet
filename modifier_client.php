<?php
// modifier_client.php
session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$message = '';

if (isset($_GET['id'])) {
    $client_id = intval($_GET['id']);

    // Récupérer les infos du client
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch();

    if (!$client) {
        $message = "Client introuvable.";
    }
} else {
    header("Location: clients.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pseudo = $_POST['pseudo'];
    $capital = floatval($_POST['capital']);
    $date_entree = $_POST['date_entree'];
    $statut = $_POST['statut'];

    try {
        $stmt = $pdo->prepare("UPDATE clients SET email = ?, pseudo = ?, capital_depose = ?, date_entree = ?, statut = ? WHERE id = ?");
        $stmt->execute([$email, $pseudo, $capital, $date_entree, $statut, $client_id]);
        $message = "✅ Client mis à jour.";
        // Rafraîchir les données
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->execute([$client_id]);
        $client = $stmt->fetch();
    } catch (Exception $e) {
        $message = "❌ Erreur : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier client – UniWallet</title>
    <style>
        body { font-family: Arial; background: #f9fafb; padding: 30px; }
        form { background: white; padding: 20px; border-radius: 8px; max-width: 600px; margin: auto; }
        input, select { padding: 8px; margin-bottom: 10px; width: 100%; }
        label { font-weight: bold; display: block; margin-top: 10px; }
    </style>
</head>
<body>
<h1>Modifier les informations du client</h1>
<p><?= $message ?></p>
<form method="POST">
    <label>Email :</label>
    <input type="email" name="email" required value="<?= htmlspecialchars($client['email']) ?>">

    <label>Pseudo / Nom :</label>
    <input type="text" name="pseudo" value="<?= htmlspecialchars($client['pseudo']) ?>">

    <label>Capital déposé ($) :</label>
    <input type="number" name="capital" step="0.01" required value="<?= $client['capital_depose'] ?>">

    <label>Date d'entrée :</label>
    <input type="date" name="date_entree" required value="<?= $client['date_entree'] ?>">

    <label>Statut :</label>
    <select name="statut">
        <option value="actif" <?= $client['statut'] === 'actif' ? 'selected' : '' ?>>Actif</option>
        <option value="sorti" <?= $client['statut'] === 'sorti' ? 'selected' : '' ?>>Sorti</option>
    </select>

    <input type="submit" value="Mettre à jour">
</form>
<p><a href="clients.php">← Retour à la liste des clients</a></p>
</body>
</html>

