<!DOCTYPE html>
<html lang="pt-br">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<link rel="stylesheet" href="../css/principal.css">
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Playwrite+DE+Grund:wght@100..400&display=swap" rel="stylesheet">
		<meta name="author" content="Marcelo de Araujo Almeida">
		<title>Titulo Aqui</title>
	</head>
	<body>
		<header><img alt="LOGO AMORSAUDE" src="../icones/logo_amorsaude.png"></header>
		<?php include 'header.html';?> 
		<main>
			<header class="ativo">
				<h1>COLABORADORES ATIVOS</h1>
			</header>
            <article>
                <h2>DADOS DO COLABORADOR</h2>
                <div class="colaborador-info">
                  <div class="colaborador-img"> <img src="../foto_colaboradores" alt="" class="foto" > </div>
                  <div class="colaborador-details">
                    <p><strong>Colaborador:</strong>Aline Aparecida Vieira Do Carmo Silva</p>
                    <p><strong>Email:</strong></p>
                    <p><strong>Cargo:</strong>Biomédico</p>
                    <p><strong>Departamento:</strong>Louzada</p>
                    <p><strong>CPF:</strong>475.468.958-55</p>
                    <p><strong>Data da admissão:</strong></p>
                    <p><strong>Data da desligamento:</strong></p>
                    <p><strong>Centro de Custo:</strong>18004 - Laboratório Louzada</p>
                  </div>
                </div>
            </article>
			<header class="desativados">
				<h1>COLABORADORES DESATIVADOS</h1>
			</header>
		</main>
		<?php include 'footer.html'?> 
	</body>
</html>