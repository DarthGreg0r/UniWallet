<?php
// 02:06:2025 - 12:05:00
require_once 'db.php';

$login = 'admin';
$password = 'admin123';
$otp_secret = bin2hex(random_bytes(10)); // Génère un secret OTP

$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Vérifie si un admin existe déjà
$stmt = $pdo->prepare("SELECT COUNT(*) FROM admin WHERE login = ?");
$stmt->execute([$login]);
$count = $stmt->fetchColumn();

if ($count == 0) {
    $stmt = $pdo->prepare("INSERT INTO admin (login, password_hash, otp_secret) VALUES (?, ?, ?)");
    $stmt->execute([$login, $password_hash, $otp_secret]);
    echo "<p style='color:green;'>✅ Administrateur créé avec succès.</p>";
    echo "<p><strong>Login :</strong> $login</p>";
    echo "<p><strong>Mot de passe :</strong> $password</p>";
    echo "<p><strong>OTP secret :</strong> $otp_secret</p>";
} else {
    echo "<p style='color:red;'>❌ Un administrateur avec le login '$login' existe déjà.</p>";
}
?>
