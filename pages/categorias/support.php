<?php
session_start();

// Impede acesso direto sem login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../../apps/sessao/login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Suporte</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Marcelo de Araujo Almeida">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/global.css">
    <link rel="stylesheet" href="../../css/menu-drop.css">
    <link rel="icon" href="../imagem/favicon/favicon-16x16.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../../scripts/menudrop.js" defer></script>
</head>

<body>
<?php include '../includes/nav.php'; ?>
    <h2>🔩Inventario de Suporte</h2>
    <div class="container">
        <div class="card">
            <div class="info">Modelo:<span class="label">Aluminio</span></div>
            <div class="info">S/N:<span class="label"></span></div>
            <div class="info">Patrimônio:<span class="label">1087</span></div>
        </div>
    </div>
<?php
include '../includes/footer.php';
?>
</body>

</html>
