<?php
// Créé le 02:06:25 à 12:57:00

require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;

if (!isAdminAuthenticated()) {
    header('Location: index.php');
    exit;
}

$contractId = $_GET['id'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM clients WHERE contrat_id = ?");
$stmt->execute([$contractId]);
$client = $stmt->fetch();

if (!$client) {
    echo "<p>Contrat introuvable.</p>";
    exit;
}

$stmtUPV = $pdo->prepare("SELECT SUM(nombre_upv) FROM upv WHERE contrat_id = ?");
$stmtUPV->execute([$contractId]);
$upv = $stmtUPV->fetchColumn();

$html = "<h1>Détail du contrat client</h1>";
$html .= "<table border='1' cellpadding='5' cellspacing='0'>";
$html .= "<tr><th>Champ</th><th>Valeur</th></tr>";
$html .= "<tr><td>Statut</td><td>{$client['statut']}</td></tr>";
$html .= "<tr><td>Numéro de contrat</td><td>{$client['contrat_id']}</td></tr>";
$html .= "<tr><td>Nom ou pseudo</td><td>{$client['nom']}</td></tr>";
$html .= "<tr><td>Email</td><td>{$client['email']}</td></tr>";
$html .= "<tr><td>Date d’entrée</td><td>{$client['date_entree']}</td></tr>";
$html .= "<tr><td>Capital déposé</td><td>{$client['capital']} $</td></tr>";
$html .= "<tr><td>Performance</td><td>{$client['performance']} %</td></tr>";
$montantTotal = round($client['capital'] + ($client['capital'] * $client['performance'] / 100), 2);
$html .= "<tr><td>Montant total brut</td><td>{$montantTotal} $</td></tr>";
$html .= "<tr><td>Montant final</td><td>{$client['montant_final']} $</td></tr>";
$html .= "<tr><td>Pénalité appliquée</td><td>" . ($client['penalite'] > 0 ? 'Oui' : 'Non') . "</td></tr>";
$html .= "<tr><td>UPV attribuées</td><td>{$upv}</td></tr>";
$html .= "<tr><td>Date de sortie</td><td>" . ($client['date_sortie'] ?? 'N/A') . "</td></tr>";
$html .= "</table>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Contrat_{$client['contrat_id']}.pdf");
