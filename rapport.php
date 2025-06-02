 <?php
// Créé le 02:06:25 à 13:32:00

require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;

if (!isAdminAuthenticated()) {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['id'])) {
    die("Contrat introuvable.");
}

$contractId = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM clients WHERE contract_id = ?");
$stmt->execute([$contractId]);
$client = $stmt->fetch();

if (!$client) {
    die("Client introuvable.");
}

$dateRapport = date('d/m/Y');
$performance = getLatestPerformancePercentage($pdo);
$capital = $client['capital'];
$benefices = $capital * ($performance / 100);
$commission = $benefices * 0.5;
$net = $capital + ($benefices - $commission);

if ($client['penalite'] === 'oui') {
    $penalite = $capital * 0.10;
    $net -= $penalite;
} else {
    $penalite = 0;
}

$html = "<h1>Rapport Mensuel – UniWallet</h1>";
$html .= "<p>Date : $dateRapport</p>";
$html .= "<p>Contrat ID : $contractId</p>";
$html .= "<p>Nom/Pseudo : {$client['name']}</p>";
$html .= "<p>Email : {$client['email']}</p>";
$html .= "<p>Date d'entrée : {$client['date_entree']}</p>";
$html .= "<p>Montant déposé : \${$capital}</p>";
$html .= "<p>Performance cumulée : {$performance}%</p>";
$html .= "<p>Bénéfices bruts : \$".number_format($benefices, 2)."</p>";
$html .= "<p>Commission admin (50%) : \$".number_format($commission, 2)."</p>";
$html .= "<p>Pénalité appliquée : \$".number_format($penalite, 2)."</p>";
$html .= "<h3>Montant Net final : \$".number_format($net, 2)."</h3>";

$rapportPath = __DIR__ . "/rapports/rapport_{$contractId}_".date('Ym').".pdf";
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
file_put_contents($rapportPath, $dompdf->output());

header('Content-Type: application/pdf');
header("Content-Disposition: inline; filename=rapport_$contractId.pdf");
echo $dompdf->output();
exit;
