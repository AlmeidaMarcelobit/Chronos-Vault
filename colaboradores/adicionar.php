<?php
session_start();
require_once '../includes/funcoes.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

// Verificar nível do usuário
$usuario_nivel = $_SESSION['usuario_nivel'] ?? 'user';
$is_admin = ($usuario_nivel === 'admin');
$is_view = ($usuario_nivel === 'view');
$can_edit = ($is_admin || $usuario_nivel === 'user');

$mensagem = '';
$tipoMensagem = '';

// Carregar colaboradores do novo caminho (ativos.json)
$colaboradores = lerArquivoJSON('../data/colaboradores/ativos.json');
if ($colaboradores === false) $colaboradores = [];

// Função para mesclar dados do colaborador (mantém dados não vazios)
function mesclarDadosColaborador($existente, $novo) {
    // Mesclar campos básicos (apenas se o novo valor não estiver vazio)
    if (!empty($novo['nome']) && $novo['nome'] !== ($existente['nome'] ?? '')) {
        $existente['nome'] = $novo['nome'];
    }
    if (!empty($novo['cargo']) && $novo['cargo'] !== ($existente['cargo'] ?? '')) {
        $existente['cargo'] = $novo['cargo'];
    }
    if (!empty($novo['cpf']) && $novo['cpf'] !== ($existente['cpf'] ?? '')) {
        $existente['cpf'] = $novo['cpf'];
    }
    if (!empty($novo['departamento']) && $novo['departamento'] !== ($existente['departamento'] ?? '')) {
        $existente['departamento'] = $novo['departamento'];
    }
    if (!empty($novo['centro_custo']) && $novo['centro_custo'] !== ($existente['centro_custo'] ?? '')) {
        $existente['centro_custo'] = $novo['centro_custo'];
    }
    if (!empty($novo['email']) && $novo['email'] !== ($existente['email'] ?? '')) {
        $existente['email'] = $novo['email'];
    }
    if (!empty($novo['tipo_trabalho']) && $novo['tipo_trabalho'] !== ($existente['tipo_trabalho'] ?? 'local')) {
        $existente['tipo_trabalho'] = $novo['tipo_trabalho'];
    }
    
    // Mesclar endereço (se for Home Office e tiver dados novos)
    if (($novo['tipo_trabalho'] ?? 'local') === 'home' && !empty($novo['endereco'])) {
        if (!isset($existente['endereco']) || !is_array($existente['endereco'])) {
            $existente['endereco'] = [];
        }
        
        $camposEndereco = ['logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'estado', 'cep'];
        foreach ($camposEndereco as $campo) {
            if (!empty($novo['endereco'][$campo]) && ($existente['endereco'][$campo] ?? '') !== $novo['endereco'][$campo]) {
                $existente['endereco'][$campo] = $novo['endereco'][$campo];
            }
        }
        
        // Se endereço ficou vazio, remover
        if (empty(array_filter($existente['endereco']))) {
            $existente['endereco'] = null;
        }
    }
    
    // Atualizar data de modificação
    $existente['data_atualizacao'] = date('Y-m-d H:i:s');
    
    return $existente;
}

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar e sanitizar os dados
    $matricula = trim($_POST['matricula'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '');
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
    $departamento = trim($_POST['departamento'] ?? '');
    $centro_custo = trim($_POST['centro_custo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tipo_trabalho = $_POST['tipo_trabalho'] ?? 'local';
    $endereco = trim($_POST['endereco'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');
    $bairro = trim($_POST['bairro'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $cep = preg_replace('/[^0-9]/', '', $_POST['cep'] ?? '');

    // NENHUMA VALIDAÇÃO OBRIGATÓRIA - TODOS OS CAMPOS SÃO OPCIONAIS
    $erros = [];

    // Apenas validações de formato se os campos forem preenchidos
    if (!empty($cpf) && !validarCPF($cpf)) {
        $erros[] = 'CPF inválido.';
    }

    // Validar e-mail se informado
    if (!empty($email) && !validarEmail($email)) {
        $erros[] = 'E-mail inválido.';
    }

    // Validar endereço se for Home Office e tiver dados
    if ($tipo_trabalho === 'home') {
        if (!empty($endereco) && empty($numero)) {
            $erros[] = 'Se o endereço for informado, o número é obrigatório.';
        }
        if (!empty($cep) && !validarCEP($cep)) {
            $erros[] = 'CEP inválido.';
        }
    }

    if (empty($erros)) {
        // VERIFICAR SE JÁ EXISTE UM COLABORADOR COM ESTA MATRÍCULA
        $colaboradorExistente = null;
        $colaboradorIndex = null;
        
        if (!empty($matricula)) {
            foreach ($colaboradores as $index => $colab) {
                if (isset($colab['matricula']) && $colab['matricula'] === $matricula) {
                    $colaboradorExistente = $colab;
                    $colaboradorIndex = $index;
                    break;
                }
            }
        }
        
        // Se encontrou colaborador existente por matrícula, mesclar dados
        if ($colaboradorExistente) {
            // Preparar dados do novo colaborador para mesclagem
            $novoDados = [
                'nome' => $nome,
                'cargo' => $cargo,
                'cpf' => $cpf,
                'departamento' => $departamento,
                'centro_custo' => $centro_custo,
                'email' => $email ?: null,
                'tipo_trabalho' => $tipo_trabalho,
                'endereco' => $tipo_trabalho === 'home' && !empty($endereco) ? [
                    'logradouro' => $endereco,
                    'numero' => $numero,
                    'complemento' => $complemento ?: null,
                    'bairro' => $bairro,
                    'cidade' => $cidade,
                    'estado' => $estado,
                    'cep' => $cep ?: null
                ] : null
            ];
            
            // Mesclar dados
            $colaboradores[$colaboradorIndex] = mesclarDadosColaborador($colaboradorExistente, $novoDados);
            
            // Salvar no JSON
            if (salvarArquivoJSON('../data/colaboradores/ativos.json', $colaboradores)) {
                $mensagem = 'Colaborador atualizado com sucesso! (Dados mesclados)';
                $tipoMensagem = 'success';
                
                // Limpar o formulário
                $_POST = [];
            } else {
                $mensagem = 'Erro ao atualizar o colaborador. Tente novamente.';
                $tipoMensagem = 'error';
            }
        } else {
            // Criar novo colaborador
            $novoColaborador = [
                'id' => gerarId($colaboradores),
                'matricula' => $matricula ?: null,
                'nome' => $nome ?: null,
                'cargo' => $cargo ?: null,
                'cpf' => $cpf ?: null,
                'departamento' => $departamento ?: null,
                'centro_custo' => $centro_custo ?: null,
                'email' => $email ?: null,
                'tipo_trabalho' => $tipo_trabalho,
                'endereco' => $tipo_trabalho === 'home' && !empty($endereco) ? [
                    'logradouro' => $endereco,
                    'numero' => $numero,
                    'complemento' => $complemento ?: null,
                    'bairro' => $bairro,
                    'cidade' => $cidade,
                    'estado' => $estado,
                    'cep' => $cep ?: null
                ] : null,
                'data_cadastro' => date('Y-m-d H:i:s'),
                'data_atualizacao' => date('Y-m-d H:i:s')
            ];
            
            // Adicionar ao array
            $colaboradores[] = $novoColaborador;
            
            // Salvar no JSON
            if (salvarArquivoJSON('../data/colaboradores/ativos.json', $colaboradores)) {
                $mensagem = 'Colaborador cadastrado com sucesso!';
                $tipoMensagem = 'success';
                
                // Limpar o formulário
                $_POST = [];
            } else {
                $mensagem = 'Erro ao salvar o colaborador. Tente novamente.';
                $tipoMensagem = 'error';
            }
        }
    }
    
    if (!empty($erros)) {
        $mensagem = implode('<br>', $erros);
        $tipoMensagem = 'error';
    }
}

// Função para formatar CEP
function formatarCEP($cep) {
    $cep = preg_replace('/[^0-9]/', '', $cep);
    if (strlen($cep) == 8) {
        return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
    }
    return $cep;
}

// Estatísticas para o footer
$total_colaboradores = count(lerArquivoJSON('../data/colaboradores/ativos.json'));
$total_equipamentos = count(lerArquivoJSON('../data/equipamentos.json'));
$equipamentos_data = lerArquivoJSON('../data/equipamentos.json');
$equipamentos_estoque = 0;
foreach ($equipamentos_data as $e) {
    if (($e['status'] ?? '') === 'estoque') $equipamentos_estoque++;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Colaborador - Sistema de Gestão</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/favicon/favicon.png">
    <style>
        /* ========================================
           VARIÁVEIS E RESET
           ======================================== */
        :root {
            --primary-light: #E8F4FD;
            --primary: #2196F3;
            --primary-dark: #1976D2;
            --primary-soft: #64B5F6;
            --success: #4CAF50;
            --danger: #F44336;
            --warning: #FFC107;
            --info: #00BCD4;
            --white: #FFFFFF;
            --gray-50: #FAFAFA;
            --gray-100: #F5F5F5;
            --gray-200: #EEEEEE;
            --gray-300: #E0E0E0;
            --gray-400: #BDBDBD;
            --gray-500: #9E9E9E;
            --gray-600: #757575;
            --gray-700: #616161;
            --gray-800: #424242;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --transition: all 0.2s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-50) 100%);
            color: var(--gray-800);
            line-height: 1.5;
        }

        /* HEADER */
        .header {
            background: var(--white);
            border-bottom: 1px solid var(--gray-200);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-sm);
        }

        .header-content {
            max-width: 1440px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--primary-dark);
            transition: var(--transition);
        }

        .logo a:hover {
            transform: translateY(-1px);
        }

        .logo i {
            font-size: 1.75rem;
        }

        .logo h1 {
            font-size: 1.35rem;
            font-weight: 600;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: var(--primary-light);
            padding: 0.5rem 1rem;
            border-radius: 2rem;
        }

        .user-info i {
            font-size: 1.1rem;
            color: var(--primary);
        }

        .user-name {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--gray-100);
            color: var(--gray-700);
            text-decoration: none;
            border-radius: 2rem;
            font-size: 0.875rem;
            transition: var(--transition);
        }

        .logout-btn:hover {
            background: var(--gray-200);
            transform: translateY(-1px);
        }

        /* NAVEGAÇÃO */
        .nav-container {
            background: var(--white);
            border-top: 1px solid var(--gray-100);
        }

        .nav-menu {
            max-width: 1440px;
            margin: 0 auto;
            padding: 0 2rem;
            list-style: none;
            display: flex;
            gap: 2rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 0;
            color: var(--gray-600);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: var(--transition);
            border-bottom: 2px solid transparent;
        }

        .nav-link:hover {
            color: var(--primary);
        }

        .nav-link.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        /* CONTEÚDO PRINCIPAL */
        .main-container {
            max-width: 1440px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* PAGE HEADER */
        .page-header {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-header h1 i {
            color: var(--primary);
            font-size: 1.75rem;
        }

        .page-subtitle {
            color: var(--gray-500);
            font-size: 0.875rem;
            margin-top: 0.25rem;
            margin-left: 2.5rem;
        }

        /* BOTÕES */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .btn-secondary {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .btn-secondary:hover {
            background: var(--gray-200);
            transform: translateY(-1px);
        }

        /* FORMULÁRIO */
        .form-card-container {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-group label i {
            color: var(--primary);
            width: 1rem;
        }

        .optional {
            color: var(--gray-500);
            font-size: 0.7rem;
            font-weight: normal;
            margin-left: 0.5rem;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-md);
            transition: var(--transition);
            background: var(--white);
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
        }

        select.form-select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%232196F3' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 2.5rem;
        }

        .form-text {
            font-size: 0.7rem;
            color: var(--gray-500);
            margin-top: 0.25rem;
            display: block;
        }

        /* SECTION DIVIDER */
        .section-divider {
            margin: 1.5rem 0 1rem 0;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--gray-200);
        }

        .section-divider h3 {
            font-size: 1rem;
            color: var(--gray-700);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* FORM ACTIONS */
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
        }

        /* GLOBAL ALERT */
        .global-alert {
            max-width: 1440px;
            margin: 1rem auto;
            padding: 1rem;
            border-radius: var(--radius-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: opacity 0.3s ease;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            border-left: 4px solid var(--success);
            color: #2e7d32;
        }

        .alert-error {
            background: rgba(244, 67, 54, 0.1);
            border-left: 4px solid var(--danger);
            color: #c62828;
        }

        .alert-info {
            background: rgba(33, 150, 243, 0.1);
            border-left: 4px solid var(--info);
            color: #1565C0;
        }

        .alert-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-close {
            background: none;
            border: none;
            font-size: 1.25rem;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
        }

        .alert-close:hover {
            opacity: 1;
        }

        /* FOOTER */
        .footer {
            background: var(--white);
            border-top: 1px solid var(--gray-200);
            margin-top: 3rem;
        }

        .footer-content {
            max-width: 1440px;
            margin: 0 auto;
            padding: 2rem;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .footer-section h3 {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: var(--gray-500);
            text-decoration: none;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--primary);
            transform: translateX(3px);
        }

        .footer-stats {
            display: flex;
            gap: 1rem;
        }

        .footer-stat {
            text-align: center;
        }

        .footer-stat .stat-number {
            display: block;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
        }

        .footer-stat .stat-label {
            font-size: 0.7rem;
            color: var(--gray-500);
        }

        .footer-bottom {
            max-width: 1440px;
            margin: 0 auto;
            padding: 1rem 2rem;
            border-top: 1px solid var(--gray-200);
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.7rem;
            color: var(--gray-500);
        }

        /* RESPONSIVIDADE */
        @media (max-width: 1024px) {
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .footer-section h3 {
                justify-content: center;
            }
            .footer-links a {
                justify-content: center;
            }
            .footer-stats {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }
            .form-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .form-actions {
                flex-direction: column;
            }
            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
            .footer-bottom {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .user-name {
                display: none;
            }
            .nav-link span {
                display: none;
            }
            .nav-link i {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>

<!-- ==================== HEADER ==================== -->
<header class="header">
    <div class="header-content">
        <div class="logo">
            <a href="../index.php">
                <i class="fas fa-user-plus"></i>
                <h1>Sistema de Gestão</h1>
            </a>
        </div>
        <div class="user-menu">
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário'); ?></span>
            </div>
            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sair</span>
            </a>
        </div>
    </div>
    <nav class="nav-container">
        <ul class="nav-menu">
            <li class="nav-item"><a href="../index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="index.php" class="nav-link active"><i class="fas fa-users"></i><span>Colaboradores</span></a></li>
            <li class="nav-item"><a href="../equipamentos/index.php" class="nav-link"><i class="fas fa-laptop"></i><span>Equipamentos</span></a></li>
            <li class="nav-item"><a href="../linhas/index.php" class="nav-link"><i class="fas fa-phone"></i><span>Linhas</span></a></li>
        </ul>
    </nav>
</header>

<!-- Mensagens de alerta -->
<?php if ($mensagem): ?>
    <div class="global-alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : ($tipoMensagem === 'info' ? 'info' : 'error'); ?>">
        <div class="alert-content">
            <i class="fas fa-<?php echo $tipoMensagem === 'success' ? 'check-circle' : ($tipoMensagem === 'info' ? 'info-circle' : 'exclamation-circle'); ?>"></i>
            <span><?php echo $mensagem; ?></span>
        </div>
        <button class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
<?php endif; ?>

<!-- ==================== CONTEÚDO PRINCIPAL ==================== -->
<main class="main-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-user-plus"></i> Adicionar Novo Colaborador</h1>
            <p class="page-subtitle">Preencha os dados abaixo para cadastrar um novo colaborador</p>
            <p class="page-subtitle" style="color: var(--info); margin-top: 5px;">
                <i class="fas fa-info-circle"></i> 
                <strong>Nota:</strong> Todos os campos são opcionais. Se a matrícula já existir, os dados serão automaticamente mesclados.
            </p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="form-card-container">
        <form method="POST" action="" class="form-card" id="form-colaborador">
            <div class="form-grid">
                <!-- Campo Matrícula (Chamado) - OPCIONAL -->
                <div class="form-group">
                    <label for="matricula">
                        <i class="fas fa-id-badge"></i>
                        <span>Chamado / Matrícula</span>
                    </label>
                    <input type="text"
                           id="matricula"
                           name="matricula"
                           value="<?php echo htmlspecialchars($_POST['matricula'] ?? ''); ?>"
                           class="form-control"
                           placeholder="Ex: #251506, #255676"
                           autofocus>
                    <small class="form-text">Número do chamado do colaborador - Se já existir, os dados serão mesclados</small>
                </div>

                <div class="form-group">
                    <label for="nome">
                        <i class="fas fa-user"></i>
                        <span>Nome Completo</span>
                        
                    </label>
                    <input type="text"
                           id="nome"
                           name="nome"
                           value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>"
                           class="form-control"
                           placeholder="Digite o nome completo">
                </div>

                <div class="form-group">
                    <label for="cargo">
                        <i class="fas fa-briefcase"></i>
                        <span>Cargo</span>
                        
                    </label>
                    <input type="text"
                           id="cargo"
                           name="cargo"
                           value="<?php echo htmlspecialchars($_POST['cargo'] ?? ''); ?>"
                           class="form-control"
                           placeholder="Digite o cargo">
                </div>

                <div class="form-group">
                    <label for="cpf">
                        <i class="fas fa-id-card"></i>
                        <span>CPF</span>
                        
                    </label>
                    <input type="text"
                           id="cpf"
                           name="cpf"
                           value="<?php echo htmlspecialchars($_POST['cpf'] ?? ''); ?>"
                           class="form-control cpf-mask"
                           placeholder="000.000.000-00"
                           maxlength="14">
                    <!--<small class="form-text">Digite apenas números ou com pontos e traço</small>-->
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        <span>E-mail</span>
                        
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           class="form-control"
                           placeholder="colaborador@empresa.com.br">
                </div>

                <div class="form-group">
                    <label for="tipo_trabalho">
                        <i class="fas fa-briefcase"></i>
                        <span>Tipo de Trabalho</span>
                        
                    </label>
                    <select id="tipo_trabalho" name="tipo_trabalho" class="form-select" onchange="toggleEnderecoFields()">
                        <option value="local" <?php echo ($_POST['tipo_trabalho'] ?? 'local') == 'local' ? 'selected' : ''; ?>>Presencial (Local)</option>
                        <option value="home" <?php echo ($_POST['tipo_trabalho'] ?? '') == 'home' ? 'selected' : ''; ?>>Home Office</option>
                    </select>
                </div>
            </div>

            <!-- Seção de Endereço (visível apenas quando Home Office) -->
            <div id="endereco-section" style="display: <?php echo (($_POST['tipo_trabalho'] ?? '') == 'home') ? 'block' : 'none'; ?>;">
                <div class="section-divider">
                    <h3><i class="fas fa-home"></i> Endereço Residencial</h3>
                    <small class="form-text">Todos os campos de endereço são opcionais</small>
                </div>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="endereco">
                            <i class="fas fa-road"></i>
                            <span>Logradouro</span>
                            
                        </label>
                        <input type="text"
                               id="endereco"
                               name="endereco"
                               value="<?php echo htmlspecialchars($_POST['endereco'] ?? ''); ?>"
                               class="form-control"
                               placeholder="Rua, Avenida, Alameda...">
                    </div>

                    <div class="form-group">
                        <label for="numero">
                            <i class="fas fa-hashtag"></i>
                            <span>Número</span>
                            
                        </label>
                        <input type="text"
                               id="numero"
                               name="numero"
                               value="<?php echo htmlspecialchars($_POST['numero'] ?? ''); ?>"
                               class="form-control"
                               placeholder="Número">
                    </div>

                    <div class="form-group">
                        <label for="complemento">
                            <i class="fas fa-plus-circle"></i>
                            <span>Complemento</span>
                            
                        </label>
                        <input type="text"
                               id="complemento"
                               name="complemento"
                               value="<?php echo htmlspecialchars($_POST['complemento'] ?? ''); ?>"
                               class="form-control"
                               placeholder="Apto, Bloco, Casa...">
                    </div>

                    <div class="form-group">
                        <label for="bairro">
                            <i class="fas fa-location-dot"></i>
                            <span>Bairro</span>
                            
                        </label>
                        <input type="text"
                               id="bairro"
                               name="bairro"
                               value="<?php echo htmlspecialchars($_POST['bairro'] ?? ''); ?>"
                               class="form-control"
                               placeholder="Bairro">
                    </div>

                    <div class="form-group">
                        <label for="cidade">
                            <i class="fas fa-city"></i>
                            <span>Cidade</span>
                            
                        </label>
                        <input type="text"
                               id="cidade"
                               name="cidade"
                               value="<?php echo htmlspecialchars($_POST['cidade'] ?? ''); ?>"
                               class="form-control"
                               placeholder="Cidade">
                    </div>

                    <div class="form-group">
                        <label for="estado">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Estado</span>
                            
                        </label>
                        <select id="estado" name="estado" class="form-select">
                            <option value="">Selecione o estado</option>
                            <?php foreach (getEstados() as $sigla => $nome): ?>
                                <option value="<?php echo $sigla; ?>" <?php echo (($_POST['estado'] ?? '') == $sigla) ? 'selected' : ''; ?>>
                                    <?php echo $sigla . ' - ' . $nome; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cep">
                            <i class="fas fa-mail-bulk"></i>
                            <span>CEP</span>
                            
                        </label>
                        <input type="text"
                               id="cep"
                               name="cep"
                               value="<?php echo htmlspecialchars($_POST['cep'] ?? ''); ?>"
                               class="form-control cep-mask"
                               placeholder="00000-000"
                               maxlength="9">
                    </div>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="departamento">
                        <i class="fas fa-building"></i>
                        <span>Departamento</span>
                        
                    </label>
                    <select id="departamento" name="departamento" class="form-select">
                        <option value="">Selecione o departamento</option>
                        <?php
                        $departamentos = [
                            'Administração', 'Comercial', 'Compras', 'Contabilidade', 'Desenvolvimento',
                            'Diretoria', 'Financeiro', 'Jurídico', 'Marketing', 'Recursos Humanos',
                            'Suporte Técnico', 'Tecnologia da Informação', 'Vendas'
                        ];
                        foreach ($departamentos as $dept):
                        ?>
                            <option value="<?php echo $dept; ?>" <?php echo (isset($_POST['departamento']) && $_POST['departamento'] == $dept) ? 'selected' : ''; ?>>
                                <?php echo $dept; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="centro_custo">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Centro de Custo</span>
                        
                    </label>
                    <input type="text"
                           id="centro_custo"
                           name="centro_custo"
                           value="<?php echo htmlspecialchars($_POST['centro_custo'] ?? ''); ?>"
                           class="form-control cc-mask"
                           placeholder="Ex: TI001, ADM002">
                    <small class="form-text">Código do centro de custo</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Colaborador
                </button>
                <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-redo"></i> Limpar
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</main>

<!-- ==================== FOOTER ==================== -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3><i class="fas fa-user-plus"></i> Sistema de Gestão</h3>
            <p>Controle de colaboradores e equipamentos</p>
        </div>
        <div class="footer-section">
            <h3>Links Rápidos</h3>
            <ul class="footer-links">
                <li><a href="../index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="index.php"><i class="fas fa-users"></i> Colaboradores</a></li>
                <li><a href="../equipamentos/index.php"><i class="fas fa-laptop"></i> Equipamentos</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Estatísticas</h3>
            <div class="footer-stats">
                <div class="footer-stat"><span class="stat-number"><?php echo $total_colaboradores; ?></span><span class="stat-label">Colaboradores</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo $total_equipamentos; ?></span><span class="stat-label">Equipamentos</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo $equipamentos_estoque; ?></span><span class="stat-label">Em Estoque</span></div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
        <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
</footer>

<script>
    // Mostrar/esconder campos de endereço
    const selectTipoTrabalho = document.getElementById('tipo_trabalho');
    const enderecoSection = document.getElementById('endereco-section');

    function toggleEnderecoFields() {
        if (selectTipoTrabalho.value === 'home') {
            enderecoSection.style.display = 'block';
        } else {
            enderecoSection.style.display = 'none';
        }
    }

    // Máscara para CPF
    const cpfInput = document.getElementById('cpf');
    if (cpfInput) {
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.substring(0, 11);
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            }
            e.target.value = value;
        });
    }

    // Máscara para CEP
    const cepInput = document.getElementById('cep');
    if (cepInput) {
        cepInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 8) value = value.substring(0, 8);
            if (value.length > 5) {
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
            }
            e.target.value = value;
        });
    }

    // Máscara para centro de custo
    const ccInput = document.getElementById('centro_custo');
    if (ccInput) {
        ccInput.addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase();
            value = value.replace(/[^A-Z0-9]/g, '');
            e.target.value = value;
        });
    }

    // Reset do formulário
    function resetForm() {
        if (confirm('Tem certeza que deseja limpar todos os campos?')) {
            document.getElementById('form-colaborador').reset();
            toggleEnderecoFields();
        }
    }

    // Inicializar estado do endereço
    document.addEventListener('DOMContentLoaded', function() {
        toggleEnderecoFields();
    });
</script>
</body>
</html>