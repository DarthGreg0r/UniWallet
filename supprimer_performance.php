<?php
// Créé le 02:06:25 à 13:14:00

require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

if (!isAdminAuthenticated()) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM performances WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: wallet.php");
exit;
