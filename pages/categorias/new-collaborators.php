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
    <title>Novos Colaboradores</title>
    <meta charset="UTF-8">
    <meta name="author" content="Marcelo de Araujo Almeida">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/global.css">
    <link rel="stylesheet" href="../../css/menu-drop.css">
    <link rel="icon" href="../imagem/favicon/favicon-16x16.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../../scripts/menudrop.js" defer></script>

</head>

<body id="corpo">
<?php include '../includes/nav.php'; ?>
<div class="container">
    <div class="card">
        <div class="info">Ticket:<span class="label"></span></div>
        <div class="info">Candidato:<span class="label"></span></div>
        <div class="info">CPF:<span class="label"></span></div>
        <div class="info">Superior Imediato:<span class="label"></span></div>
        <div class="info">Cargo:<span class="label"></span></div>
        <div class="info">Departamento:<span class="label"></span></div>
        <div class="info">Substituição:<span class="label"></span></div>
        <div class="info">E-mail:<span class="label"></span></div>
        <div class="info">User AD:<span class="label"></span></div>
        <div class="info">Empresa:<span class="label"></span></div>
        <div class="info">Centro de Custo:<span class="label"></span></div>
        <div class="info">Numero Kits:<span class="label"></span></div>
    </div>

    <div class="card">
        <div class="info">Ticket:<span class="label">#164173</span></div>
        <div class="info">Candidato:<span class="label">Juliana Didone</span></div>
        <div class="info">CPF:<span class="label">325.005.518-22</span></div>
        <div class="info">Superior Imediato:<span class="label">Victor Lima</span></div>
        <div class="info">Cargo:<span class="label">Especialista em CRM</span></div>
        <div class="info">Departamento:<span class="label">CRM</span></div>
        <div class="info">Substituição:<span class="label"></span></div>
        <div class="info">E-mail:<span class="label">juliana.didone@amorsaude.com</span></div>
        <div class="info">User AD:<span class="label"></span></div>
        <div class="info">Empresa:<span class="label">1 - AmorSaúde</span></div>
        <div class="info">Centro de Custo:<span class="label">14003 - CRM</span></div>
        <div class="info">Numero Kits:<span class="label"></span></div>
    </div>
    <div class="card">
        <div class="info">Ticket:<span class="label">#178891</span></div>
        <div class="info">Candidato:<span class="label">Eduarda Birches</span></div>
        <div class="info">CPF:<span class="label">404.857.558-90</span></div>
        <div class="info">Superior Imediato:<span class="label">Marianne Lesur</span></div>
        <div class="info">Cargo:<span class="label">Assistente de Canais Digitais e Relacionamento</span></div>
        <div class="info">Departamento:<span class="label">Telemedicina Operação</span></div>
        <div class="info">Substituição:<span class="label">Daila Santos</span></div>
        <div class="info">E-mail:<span class="label">eduarda.birches@amorsaude.com</span></div>
        <div class="info">User AD:<span class="label"></span></div>
        <div class="info">Empresa:<span class="label">AmorSaúde Telemedicina</span></div>
        <div class="info">Centro de Custo:<span class="label">22000 - Telemedicina</span></div>
        <div class="info">Numero Kits:<span class="label"></span></div>
    </div>
    <div class="card">
        <div class="info">Ticket:<span class="label">#128504</span></div>
        <div class="info">Candidato:<span class="label">Daniela Martinelli Béo</span></div>
        <div class="info">CPF:<span class="label">421.484.598-10</span></div>
        <div class="info">Superior:<span class="label">Vanessa Posca</span></div>
        <div class="info">Subtituição:<span class="label">Iago Perin</span></div>
        <div class="info">Cargo:<span class="label">Consultor Perfomance</span></div>
        <div class="info">Departamento:<span class="label">Consultoria</span></div>
        <div class="info">E-mail:<span class="label">daniela.martinelli@amorsaude.com</span></div>
        <div class="info">User AD:<span class="label"></span></div>
        <div class="info">Empresa:<span class="label">AmorSaude</span></div>
        <div class="info">Centro de Custo:<span class="label"></span>13010 - Consultoria de Perfomace</div>
        <div class="info">Numero Kits:<span class="label"></span></div>
    </div>
    <div class="card">
        <div class="info">Ticket:<span class="label">#168914</span></div>
        <div class="info">Candidato:<span class="label">Wesley Rafael da Silva</span></div>
        <div class="info">CPF:<span class="label">496.354.958-11</span></div>
        <div class="info">Superior:<span class="label">Agata Cristina Silva Assunção</span></div>
        <div class="info">Cargo:<span class="label">Assistente de Processos e Documentações</span></div>
        <div class="info">Departamento:<span class="label">Backoffice</span></div>
        <div class="info">E-mail:<span class="label"></span>wesley.silva@amorsaude.com</div>
        <div class="info">User AD:<span class="label"></span></div>
        <div class="info">Empresa:<span class="label">AmorSaúde</span></div>
        <div class="info">Centro de Custo:<span class="label">12005 - BackOffice</span></div>
        <div class="info">Numero Kits:<span class="label"></span></div>
    </div>
    <div class="card">
        <div class="info">Ticket:<span class="label">#128351 - #172777</span></div>
        <div class="info">Candidato:<span class="label">Leandro Marcos de Deus</span></div>
        <div class="info">CPF:<span class="label">702.643.676-77</span></div>
        <div class="info">Superior:<span class="label">Marcos Henrique Corusse</span></div>
        <div class="info">Cargo:<span class="label">Desenvolvedo Full Stack Jr</span></div>
        <div class="info">Departamento:<span class="label">Tech</span></div>
        <div class="info">Substituição:<span class="label">Marcos Corusse</span></div>
        <div class="info">E-mail:<span class="label">leandro.marcos@amorsaude.com</span></div>
        <div class="info">User AD:<span class="label"></span></div>
        <div class="info">Empresa:<span class="label"></span>AmorSaude</div>
        <div class="info">Centro de Custo:<span class="label"></span></div>
        <div class="info">Numero Kits:<span class="label"></span></div>
    </div>
    <div class="card">
        <div class="info">Ticket:<span class="label">#176556</span></div>
        <div class="info">Candidato:<span class="label">Eduardo Valentim</span></div>
        <div class="info">CPF:<span class="label">833.702.774-68</span></div>
        <div class="info">Superior Imediato:<span class="label"></span></div>
        <div class="info">Cargo:<span class="label">Gerente de Engenharia</span></div>
        <div class="info">Departamento:<span class="label">Engenharia</span></div>
        <div class="info">Substituição:<span class="label">Dan Sabbagh</span></div>
        <div class="info">E-mail:<span class="label"></span></div>
        <div class="info">User AD:<span class="label"></span></div>
        <div class="info">Empresa:<span class="label">AmorSaúde</span></div>
        <div class="info">Centro de Custo:<span class="label">13009 - Diretoria de Tecnologia</span></div>
        <div class="info">Numero Kits:<span class="label"></span></div>
    </div>
    <div class="card">
        <div class="info">Ticket:<span class="label">#164179</span></div>
        <div class="info">Candidato:<span class="label">Victor Hugo Lopes Da Silva Moro</span></div>
        <div class="info">CPF:<span class="label">445.733.608-40</span></div>
        <div class="info">Superior Imediato:<span class="label">Victor Lima</span></div>
        <div class="info">Cargo de Admissão:<span class="label">Analista de QA Jr</span></div>
        <div class="info">Departamento:<span class="label">CRM</span></div>
        <div class="info">E-mail:<span class="label">victor.moro@amorsaude.com</span></div>
        <div class="info">User AD:<span class="label"></span></div>
        <div class="info">Empresa:<span class="label">AmorSaúde</span></div>
        <div class="info">Centro de Custo:<span class="label">14003 - CRM</span></div>
        <div class="info">Numero Kits:<span class="label"></span></div>
    </div>
    <div class="card">
        <div class="info">Colaborador:<span class="label">Pedro Luiz Vicente de Carvalho</span></div>
        <div class="info">Substituição:<span class="label">Lucilene Santos Matias</span></div>
        <div class="info">Cargo:<span class="label">Consultor de Performance</span></div>
        <div class="info">Departamento:<span class="label">Consultoria</span></div>
        <div class="info">E-mail:<span class="label">pedro.carvalho@amorsaude.com</span></div>
        <div class="info">CPF:<span class="label">470.311.428-30</span></div>
        <div class="info">Centro de Custo:<span class="label">12010 - Consultoria de Performance</span></div>
    </div>
    <div class="card">
        <div class="info">Colaborador:<span class="label">Julia Carolina Rogerio</span></div>
        <div class="info">Substituição:<span class="label">Yago dos Anjos Mariano</span></div>
        <div class="info">Departamento:<span class="label">Consultoria de Performance</span></div>
        <div class="info">E-mail:<span class="label"></span></div>
        <div class="info">CPF:<span class="label">377.938.578-36</span></div>
        <div class="info">Centro de Custo:<span class="label">12010 - Consultoria de Performance</span></div>
    </div>
    <div class="card">
        <div class="info">Colaborador:<span class="label">Agata Cristina Silva Assunção</span></div>
        <div class="info">Cargo:<span class="label">Supervisor de Operações</span></div>
        <div class="info">Departamento:<span class="label">Backoffice e SAF</span></div>
        <div class="info">E-mail:<span class="label">agata.assuncao@amorsaude.com</span></div>
        <div class="info">CPF:<span class="label">376.102.618-82</span></div>
        <div class="info">Centro de Custo:<span class="label">12005 - BackOffice</span></div>
    </div>
    <div class="card">
        <div class="info">Colaborador:<span class="label">Brenno Yan Romanatti Rogério</span></div>
        <div class="info">Substituição:<span class="label">Ana Beatriz Silveira</span></div>
        <div class="info">Cargo:<span class="label">Analista de QA JR</span></div>
        <div class="info">Departamento:<span class="label">Produto</span></div>
        <div class="info">E-mail:<span class="label">brenno.rogerio@amorsaude.com</span></div>
        <div class="info">CPF:<span class="label">>456.510.218-84</span></div>
        <div class="info">Centro de Custo:<span class="label">13013 - Produto</span></div>
    </div>
    <div class="card">
        <div class="info">Ticket:<span class="label"></span></div>
        <div class="info">Candidato:<span class="label">Thauany Heloisa Pereira</span></div>
        <div class="info">CPF:<span class="label">414.972.498-99</span></div>
        <div class="info">Superior Imediato:<span class="label"></span></div>
        <div class="info">Cargo de Admissão:<span class="label">Analista de Administração Pessoal JR</span></div>
        <div class="info">Departamento:<span class="label">Pessoas e Cultura</span></div>
        <div class="info">Substituição:<span class="label"></span></div>
        <div class="info">E-mail:<span class="label">thauany.pereira@amorsaude.com</span></div>
        <div class="info">User AD:<span class="label"></span></div>
        <div class="info">Empresa:<span class="label"></span></div>
        <div class="info">Centro de Custo:<span class="label">13007 - Remuneração e Benefícios</span></div>
        <div class="info">Numero Kits:<span class="label"></span></div>
    </div>
    <div class="card">
        <div class="info">Ticket:<span class="label"></span></div>
        <div class="info">Candidato:<span class="label">Marina Pereira Pardinho Ramos</span></div>
        <div class="info">CPF:<span class="label">323.808.848-38</span></div>
        <div class="info">Superior Imediato:<span class="label"></span></div>
        <div class="info">Departamento:<span class="label">Gestão de Rede</span></div>
        <div class="info">Cargo de Admissão:<span class="label">Analista de Documentações Gestão de Rede SR</span></div>
        <div class="info">Substituição:<span class="label"></span></div>
        <div class="info">E-mail:<span class="label">marina.ramos@amorsaude.com</span></div>
        <div class="info">User AD:<span class="label"></span></div>
        <div class="info">Empresa:<span class="label"></span></div>
        <div class="info">Centro de Custo:<span class="label">12006 - Gestão de Rede</span></div>
        <div class="info">Numero Kits:<span class="label"></span></div>
    </div>
    <div class="card">
        <div class="info">Ticket:<span class="label">#141110</span></div>
        <div class="info">Candidato:<span class="label">Beatriz Lima Gouvêa</span></div>
        <div class="info">CPF:<span class="label">526.497.578-76</span></div>
        <div class="info">Superior Imediato:<span class="label">Fabíola Todesco Quiles</span></div>
        <div class="info">Data prevista de Início:<span class="label">02/06/2025</span></div>
        <div class="info">E-mail:<span class="label">beatriz.gouvea@amorsaude.com</span></div>
        <div class="info">User AD:<span class="label">beatrizgouvea</span></div>
        <div class="info">Empresa:<span class="label">DentalVidas</span></div>
        <div class="info">Centro de Custo:<span class="label">71001 - Administrativo</span></div>
        <div class="info">Numero Kits:<span class="label"></span></div>
    </div>
    <div class="card">
        <div class="info">Ticket:<span class="label"></span></div>
        <div class="info">Candidato:<span class="label">Caio Almeida</span></div>
        <div class="info">CPF:<span class="label">047.845.731-60</span></div>
        <div class="info">Superior Imediato:<span class="label">Késsya de Lima Rosolini</span></div>
        <div class="info">Cargo de Admissão:<span class="label">Analista de Pessoas & Cultura Jr</span></div>
        <div class="info">Substituição:<span class="label">Laura Barros Ribeiro</span></div>
        <div class="info">E-mail:<span class="label">caio.almeida@amorsaude.com</span></div>
        <div class="info">User AD:<span class="label">caioalmeida</span></div>
        <div class="info">Empresa:<span class="label"></span>AmorSaude</div>
        <div class="info">Centro de Custo:<span class="label">13006 - Pessoas & Cultura</span></div>
        <div class="info">Numero Kits:<span class="label"></span></div>
    </div>
    <div class="card">
        <div class="info">Ticket:<span class="label"></span></div>
        <div class="info">Candidato:<span class="label">Júlia Bernardino Marin</span></div>
        <div class="info">CPF:<span class="label">514.357.068-97</span></div>
        <div class="info">Superior Imediato:<span class="label"></span></div>
        <div class="info">Cargo de Admissão:<span class="label">Analista Financeiro PL</span></div>
        <div class="info">Departamento:<span class="label">Financeiro </span></div>
        <div class="info">Substituição:<span class="label"></span></div>
        <div class="info">E-mail:<span class="label"></span></div>
        <div class="info">User AD:<span class="label"></span></div>
        <div class="info">Empresa:<span class="label"></span>AmorSaude</div>
        <div class="info">Centro de Custo:<span class="label">13001 - Financeiro</span></div>
        <div class="info">Numero Kits:<span class="label"></span></div>
    </div>

</div>

<footer>
    <p>&copy; 2024 - 2025 SaúdeTracker - Todos os direitos reservados.</p>
</footer>
</body>
</html>