<?php
session_start();

// Impede acesso direto sem login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../../apps/sessao/login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Inventário de Monitores</title>
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
<h2 style="text-align:center; margin-top: 10px">💻 Inventário de Monitores</h2>
<div class="container">
    <div class="card">
        <div class="info">Caixa:<span class="label">01</span></div>
        <div class="info">Centro de Custo<span class="label">12010 - Inteligência de Negócios</span></div>
        <div class="info">Modelo:<span class="label"> Dell P2018H</span></div>
        <div class="info">S/N:<span class="label"></span>BR-00CPG1-TVB00-9CA-1VEU-A08</div>
        <div class="info">Patrimônio:<span class="label">043</span></div>
    </div>
    <div class="card">
        <div class="info">Caixa:<span class="label">02</span></div>
        <div class="info">Centro de Custo:<span class="label">Sem Centro Custo</span></div>
        <div class="info">Modelo:<span class="label">Dell P2018H</span></div>
        <div class="info">S/N:<span class="label">BR-00CPG1-TVB00-9CA-1L9U</span></div>
        <div class="info">Patrimônio:<span class="label">143</span></div>
    </div>
    <div class="card">
        <div class="info">Caixa:<span class="label">04</span></div>
        <div class="info">Centro de Custo<span class="label">12007 - Regional 2</span></div>
        <div class="info">Modelo:<span class="label">LG 22MP410-B</span></div>
        <div class="info">S/N:<span class="label">303AZSP94390</span></div>
        <div class="info">Patrimônio:<span class="label">399</span></div>
    </div>
    <div class="card">
        <div class="info">Caixa:<span class="label">05</span></div>
        <div class="info">Centro de Custo<span class="label">12005 - Backoffice</span></div>
        <div class="info">Modelo:<span class="label">LG22P410-B</span></div>
        <div class="info">S/N:<span class="label">303AZZX8K061</span></div>
        <div class="info">Patrimônio:<span class="label">395</span></div>
    </div>
    <div class="card">
        <div class="info">Caixa:<span class="label">06</span></div>
        <div class="info">Centro de Custo<span class="label">12006 - Regional 1</span></div>
        <div class="info">Modelo:<span class="label">LG 22MP410-B</span></div>
        <div class="info">S/N:<span class="label">403AZBZ6H853</span></div>
        <div class="info">Patrimônio:<span class="label">1480</span></div>
    </div>
    <div class="card">
        <div class="info">Caixa:<span class="label">07</span></div>
        <div class="info">Centro de Custo<span class="label">12006 - Regional 1</span></div>
        <div class="info">Modelo:<span class="label">LG 22MP410-B</span></div>
        <div class="info">S/N:<span class="label">112AZB7C018</span></div>
        <div class="info">Patrimônio:<span class="label">060</span></div>
    </div>
    <div class="card">
        <div class="info">Caixa:<span class="label">08</span></div>
        <div class="info">Centro de Custo<span class="label">12005 - Backoffice</span></div>
        <div class="info">Modelo:<span class="label">LG 22MP410-B</span></div>
        <div class="info">S/N:<span class="label">303AZCQ93931</span></div>
        <div class="info">Patrimônio:<span class="label">394</span></div>
    </div>
    <div class="card">
        <div class="info">Caixa:<span class="label">09</span></div>
        <div class="info">Centro de Custo<span class="label">12006 - Regional 1</span></div>
        <div class="info">Modelo:<span class="label">LG 22MP410-B</span></div>
        <div class="info">S/N:<span class="label">402AZDB4W274</span></div>
        <div class="info">Patrimônio:<span class="label">1472</span></div>
    </div>
    <div class="card">
        <div class="info">Caixa:<span class="label">10</span></div>
        <div class="info">Centro de Custo<span class="label">Sem Custo</span></div>
        <div class="info">Modelo:<span class="label">LG 22MP410-B</span></div>
        <div class="info">S/N:<span class="label">401AZXC5Y114</span></div>
        <div class="info">Patrimônio:<span class="label">1501</span></div>
    </div>
    <div class="card">
        <div class="info">Caixa:<span class="label">11</span></div>
        <div class="info">Centro de Custo<span class="label">12006 - Regional 1</span></div>
        <div class="info">Modelo:<span class="label">LG-22MP410-B</span></div>
        <div class="info">S/N:<span class="label">403AZPUJ240</span></div>
        <div class="info">Patrimônio:<span class="label">1475</span></div>
    </div>
    <div class="card">
        <div class="info">Caixa:<span class="label">12</span></div>
        <div class="info">Centro de Custo<span class="label">Sem Centro Custo</span></div>
        <div class="info">Modelo:<span class="label">Dell P2018HC</span></div>
        <div class="info">S/N:<span class="label">BR-00CPG1-TVB00-04E-2FIU-A08</span></div>
        <div class="info">Patrimônio:<span class="label">039</span></div>
    </div>
    <div class="card">
        <div class="info">Caixa:<span class="label">13</span></div>
        <div class="info">Centro de Custo<span class="label">Sem Centro Custo</span></div>
        <div class="info">Modelo:<span class="label">Dell P2018HC</span></div>
        <div class="info">S/N:<span class="label">BR-00CPG1-TVB00-9CA-1V8U-A08</span></div>
        <div class="info">Patrimônio:<span class="label">139</span></div>
    </div>
    <div class="card">
        <div class="info">Caixa:<span class="label">14</span></div>
        <div class="info">Centro de Custo<span class="label">12007 - Regional 2</span></div>
        <div class="info">Modelo:<span class="label">Dell E1920HF</span></div>
        <div class="info">S/N:<span class="label">BR-0V4FTW-TVB00-1A6-8O0B-A03</span></div>
        <div class="info">Patrimônio:<span class="label">033</span></div>
    </div>
    <div class="card">
        <div class="info">Caixa:<span class="label">15</span></div>
        <div class="info">Centro de Custo<span class="label">Sem Centro Custo</span></div>
        <div class="info">Modelo:<span class="label">Dell E1920HF</span></div>
        <div class="info">S/N:<span class="label">BR-0V4FTW-TVB00-1A6-80QB-A03</span></div>
        <div class="info">Patrimônio:<span class="label">057</span></div>
    </div>
    <div class="card">
        <div class="info">Caixa:<span class="label">16</span></div>
        <div class="info">Centro de Custo<span class="label">12006 - Regional 1</span></div>
        <div class="info">Modelo:<span class="label">Dell E1920HF</span></div>
        <div class="info">S/N:<span class="label">BR-OV4FTW-TVB00-1A6-8P0B-A03</span></div>
        <div class="info">Patrimônio:<span class="label">070</span></div>
    </div>
    <div class="card">
        <div class="info">Caixa:<span class="label"> 17</span></div>
        <div class="info">Centro de Custo<span class="label">Sem Centro Custo</span></div>
        <div class="info">Modelo:<span class="label">Dell E1920HF</span></div>
        <div class="info">S/N:<span class="label">BR-0V4FTW-TVB00-1A6-8P5B-A03</span></div>
        <div class="info">Patrimônio:<span class="label">004</span></div>
    </div>
    <div class="card">
        <div class="info">Caixa:<span class="label">18</span></div>
        <div class="info">Centro de Custo<span class="label"> 13013 - Produto</span></div>
        <div class="info">Modelo:<span class="label">Dell E1920HF</span></div>
        <div class="info">S/N:<span class="label">BR-0V4FTW-TVB00-1A6-8PRB-A03</span></div>
        <div class="info">Patrimônio:<span class="label">069</span></div>
    </div>
    <div class="card">
        <div class="info">Caixa:<span class="label">30</span></div>
        <div class="info">Centro de Custo<span class="label">Sem Centro Custo</span></div>
        <div class="info">Modelo:<span class="label">LG22MP410-B</span></div>
        <div class="info">S/N:<span class="label">303AZQV96500</span></div>
        <div class="info">Patrimônio:<span class="label"></span></div>
    </div>
</div>
<?php
include '../includes/footer.php';
?>

</body>

</html>
