<?php
// Créé le 02:06:25 à 14:45:00

function isAdminAuthenticated() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function redirectIfNotLoggedIn() {
    if (!isAdminAuthenticated()) {
        header("Location: index.php");
        exit;
    }
}

function getLatestPerformance($pdo) {
    $stmt = $pdo->query("SELECT performance FROM performances ORDER BY date DESC LIMIT 1");
    return $stmt ? $stmt->fetchColumn() : 0;
}

function sanitize($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function formatDate($datetime) {
    return date('d/m/Y H:i:s', strtotime($datetime));
}
