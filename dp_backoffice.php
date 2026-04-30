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
                    <p><strong>Colaborador:</strong> Ana Luisa Rezende Machado Fumagalli</p>
                    <p><strong>Email:</strong> analuisa.fumagalli@amorsaude.com</p>
                    <p><strong>Cargo:</strong> Coordenadora de Backoffice</p>
                    <p><strong>Departamento:</strong> Gerencia Operações</p>
                    <p><strong>CPF:</strong> 430.792.958-62</p>
                    <p><strong>Data da Entrada:</strong> 7/ago/2023</p>
                    <p><strong>Centro de Custo:</strong> 12005 - BackOffice</p>
                </div>
            </section>
            <h2>Dados equipamentos</h2>
            <section class="equipamentos-info">
                <div class="equipamento">
                    <p><strong>Tipo:</strong> Notebook Win-10 i5-1135G7 8GB</p>
                    <p><strong>Marca/Modelo:</strong> Dell Vostro 15 3510</p>
                    <p class="serial"><strong>S/N:</strong> CNZ64R3</p>
                    <p><strong>Patrimônio:</strong> 257</p>
                </div>
                <div class="equipamento">
                    <p><strong>Tipo:</strong> Teclado e Mouse</p>
                    <p><strong>Marca/Modelo:</strong> Logitech MK270</p>
                    <p class="serial"><strong>S/N:</strong> 2347CE268028</p>
                    <p><strong>Patrimônio:</strong> 1553</p>
                </div>
            </section>
            <section class="ticket">
                <p><strong>ID Ticket:</strong> #32692</p>
                <p><strong>Equipamentos:</strong>Teclado e Mouse</p>
            </section>
        </article>

    </main>
</div>
<?php
include 'footer.php'
?>
</body>
