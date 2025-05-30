<?php
// ajouter_client.php
session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pseudo = $_POST['pseudo'];
    $capital = floatval($_POST['capital_depose']);
    $date_entree = $_POST['date_entree'];
    $contract_id = uniqid('C');

    // Récupérer la dernière valeur d’1 UPV
    $stmt = $pdo->query("SELECT valeur_upv FROM performances ORDER BY date_performance DESC LIMIT 1");
    $valeur_upv = $stmt->fetchColumn();
    $valeur_upv = $valeur_upv ?: 1;

    $nb_upv = $capital / $valeur_upv;

    $stmt = $pdo->prepare("INSERT INTO clients (email, pseudo, capital_depose, date_entree, contract_id, nb_upv) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$email, $pseudo, $capital, $date_entree, $contract_id, $nb_upv]);

    // Log UPV
    $client_id = $pdo->lastInsertId();
    $stmt = $pdo->prepare("INSERT INTO upv_log (client_id, date_attribution, upv, valeur_upv) VALUES (?, ?, ?, ?)");
    $stmt->execute([$client_id, $date_entree, $nb_upv, $valeur_upv]);

    header('Location: clients.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un client – UniWallet</title>
    <style>
        body { font-family: Arial; padding: 30px; background: #f1f5f9; }
        form { background: white; padding: 20px; border-radius: 5px; max-width: 400px; margin: auto; }
        label { display: block; margin-top: 10px; }
        input[type="text"], input[type="number"], input[type="date"] {
            width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box;
        }
        input[type="submit"] { margin-top: 20px; padding: 10px 20px; }
    </style>
</head>
<body>
    <h1>Ajouter un client</h1>
    <form method="post">
        <label>Email :</label>
        <input type="text" name="email" required>

        <label>Pseudo / Nom :</label>
        <input type="text" name="pseudo" required>

        <label>Capital déposé ($ USDC) :</label>
        <input type="number" step="0.01" name="capital_depose" required>

        <label>Date d'entrée :</label>
        <input type="date" name="date_entree" required>

        <input type="submit" value="Ajouter le client">
    </form>
</body>
</html>

