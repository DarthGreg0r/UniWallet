<?php
// Créé le 02:06:25 - 12:09:00

// --- Paramètres de connexion MySQL
define('DB_HOST', 'localhost');
define('DB_NAME', 'uniwallet');
define('DB_USER', 'root');
define('DB_PASS', '');

// --- Configuration de l'interface
define('APP_NAME', 'UniWallet');
define('APP_VERSION', '1.0');

// --- Configuration des e-mails
define('SMTP_HOST', 'smtp.exemple.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'votre@email.com');
define('SMTP_PASS', 'mot_de_passe');

// --- Répertoire des rapports PDF
define('PDF_PATH', __DIR__ . '/rapports/');

// --- URL de base (pour liens dynamiques)
define('BASE_URL', 'https://uniwallet.wojnicz.me');
?>
