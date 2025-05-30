<?php
// header.php
if (!isset($_SESSION)) session_start();
?>
<header>
    <div>
        <strong>UniWallet</strong> – Tableau de bord
        <a href="clients.php">Clients</a>
        <a href="performance.php">Performance</a>
        <a href="ajouter_client.php">Ajouter client</a>
    </div>
    <div>
        <a href="logout.php">Déconnexion</a>
    </div>
</header>
<style>
    header {
        background: #0f172a;
        color: white;
        padding: 10px 20px;
        margin: -30px -30px 20px -30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    header a {
        color: white;
        margin-left: 20px;
        text-decoration: none;
    }
</style>

