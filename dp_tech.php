<head xmlns="http://www.w3.org/1999/html">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="css/all.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <title>Estoque</title>
</head>

<body>
<header>
    <img src="icones/LOGO_RGB_AMORSAUDE_FINAL_HORIZONTAL.png" alt="LOGO AMORSAUDE">
</header>
<div class="container"> <?php
    include 'header.php'
    ?>
    <main>
        <article>
            <h2>Dados do colaborador</h2>
            <section class="colaborador-info">
                <div class="colaborador-details">
                    <p><strong>Colaborador:</strong> Thiago Isaac da Silva</p>
                    <p><strong>Email:</strong> thiago.silva@amorsaude.com</p>
                    <p><strong>Cargo:</strong> Analista De Suporte Pl</p>
                    <p><strong>Departamento:</strong> Suporte</p>
                    <p><strong>CPF:</strong> 436.577.728-98</p>
                    <p><strong>Data da Entrada:</strong> 22-ago.-2023</p>
                    <p><strong>Centro de Custo:</strong> 13019 - Suporte</p>
                </div>
            </section>
            <h2>Dados equipamentos</h2>
            <section class="equipamentos-info">
                <div class="equipamento">
                    <p><strong>Tipo:</strong> Notebook</p>
                    <p><strong>Marca/Modelo:</strong> Dell Latitude 3420</p>
                    <p class="serial"><strong>S/N:</strong> DTZHY3</p>
                    <p><strong>Patrimônio:</strong> 312</p>
                </div>
                <div class="equipamento">
                    <p><strong>Tipo:</strong> Teclado e Mouse</p>
                    <p><strong>Marca/Modelo:</strong> KM5221W</p>
                    <p class="serial"><strong>S/N:</strong> 331-KOUI-AO1</p>
                    <p><strong>Patrimônio:</strong> 318-851</p>
                </div>
                <div class="equipamento">
                    <p><strong>Tipo:</strong> Suporte</p>
                    <p><strong>Marca/Modelo:</strong> Aluminio</p>
                    <p class="serial"><strong>S/N:</strong> (Não fornecido)</p>
                    <p><strong>Patrimônio:</strong> 862</p>
                </div>
                <div class="equipamento">
                    <p><strong>Tipo:</strong> Fone</p>
                    <p><strong>Marca/Modelo:</strong> Logitech H390</p>
                    <p class="serial"><strong>S/N:</strong> 2316gg00at18</p>
                    <p><strong>Patrimônio:</strong> 1198</p>
                </div>
            </section>
            <section class="ticket">
                <p><strong>ID Ticket:</strong>#54344</p>
                <p><strong>Equipamentos:</strong>Fone</p>
            </section>
        </article>
    </main>
</div>
<?php
include 'footer.php'
?>
</body>
