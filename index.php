<?php
// Créé le 02:06:25 - 12:30:00

session_start();
require_once 'config.php';
require_once 'db.php';

// Simuler une session déjà connectée
if (isset($_SESSION['admin'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admin WHERE login = ?");
    $stmt->execute([$login]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin'] = $admin['login'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Identifiants incorrects.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion - UniWallet</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

  <div class="bg-white shadow p-8 rounded w-full max-w-md">
    <h2 class="text-xl font-bold mb-6 text-center">Connexion à UniWallet</h2>
    <?php if ($error): ?>
      <div class="bg-red-100 text-red-600 p-2 mb-4 text-sm rounded"><?= $error ?></div>
    <?php endif; ?>
    <form method="post">
      <label class="block mb-2 text-sm font-medium">Identifiant</label>
      <input type="text" name="login" required class="w-full p-2 border rounded mb-4" />

      <label class="block mb-2 text-sm font-medium">Mot de passe</label>
      <input type="password" name="password" required class="w-full p-2 border rounded mb-6" />

      <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Se connecter</button>
    </form>
  </div>

</body>
</html>
