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

if (!$can_edit) {
    $_SESSION['mensagem'] = 'Acesso negado. Apenas administradores e usuários podem adicionar equipamentos.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

$mensagem = '';
$tipoMensagem = '';

// Carregar colaboradores para o select (opcional)
$colaboradores = lerArquivoJSON('../data/colaboradores/ativos.json');
if ($colaboradores === false) $colaboradores = [];

// Verificar se veio tipo pré-selecionado pela URL
$tipoPreSelecionado = $_GET['tipo'] ?? '';
if ($tipoPreSelecionado && array_key_exists($tipoPreSelecionado, getTiposEquipamentosComIcones())) {
    $_POST['tipo'] = $tipoPreSelecionado;
}

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar e sanitizar os dados
    $tipo = $_POST['tipo'] ?? 'outro';
    $marca = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $patrimonio = trim($_POST['patrimonio'] ?? '');
    $serial = trim($_POST['serial'] ?? '');
    $centro_custo = trim($_POST['centro_custo'] ?? '');
    $status = $_POST['status'] ?? 'estoque';
    $colaborador_id = $_POST['colaborador_id'] ?? null;
    $hostname = trim($_POST['hostname'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');
    
    // Especificações técnicas
    $ram = trim($_POST['ram'] ?? '');
    $processador = trim($_POST['processador'] ?? '');
    $hd = trim($_POST['hd'] ?? '');
    
    // Validações básicas
    $erros = [];
    
    if (empty($tipo)) {
        $erros[] = 'O tipo de equipamento é obrigatório.';
    }
    
    if (empty($patrimonio)) {
        $erros[] = 'O número de patrimônio é obrigatório.';
    }
    
    // Validar hostname (obrigatório apenas para notebooks e desktops)
    if (($tipo === 'notebook' || $tipo === 'desktop') && empty($hostname)) {
        $erros[] = 'O hostname é obrigatório para equipamentos do tipo ' . ($tipo === 'notebook' ? 'Notebook' : 'Desktop') . '.';
    } elseif (!empty($hostname) && !preg_match('/^[a-zA-Z0-9\-_]+$/', $hostname)) {
        $erros[] = 'O hostname deve conter apenas letras, números, hífen ou underline.';
    }
    
    // Validar status
    if (!in_array($status, ['estoque', 'alocado', 'emprestado', 'manutencao', 'fora_uso'])) {
        $erros[] = 'Status inválido.';
    }
    
    // Verificar se precisa de colaborador (para alocado ou emprestado)
    if (($status === 'alocado' || $status === 'emprestado') && empty($colaborador_id)) {
        $erros[] = 'Selecione um colaborador para ' . ($status === 'emprestado' ? 'emprestar' : 'alocar') . ' o equipamento.';
    }
    
    // Verificar se patrimônio já existe
    if (patrimonioExiste($patrimonio)) {
        $erros[] = 'Este número de patrimônio já está cadastrado no sistema.';
    }
    
    // Verificar se serial já existe
    if (!empty($serial) && serialExiste($serial)) {
        $erros[] = 'Este número de série já está cadastrado no sistema.';
    }
    
    // Verificar se hostname já existe
    if (!empty($hostname) && ($tipo === 'notebook' || $tipo === 'desktop') && hostnameExiste($hostname)) {
        $erros[] = 'Este hostname já está cadastrado no sistema.';
    }
    
    if (empty($erros)) {
        // Criar objeto de especificações (apenas para Desktop e Notebook)
        $especificacoes = [];
        if ($tipo === 'desktop' || $tipo === 'notebook') {
            if (!empty($ram)) $especificacoes['ram'] = $ram;
            if (!empty($processador)) $especificacoes['processador'] = $processador;
            if (!empty($hd)) $especificacoes['hd'] = $hd;
        }
        
        // Criar novo equipamento
        $novoEquipamento = [
            'tipo' => $tipo,
            'marca' => $marca ?: null,
            'modelo' => $modelo ?: null,
            'patrimonio' => $patrimonio,
            'serial' => $serial ?: null,
            'hostname' => ($tipo === 'notebook' || $tipo === 'desktop') ? $hostname : null,
            'centro_custo' => $centro_custo ?: null,
            'especificacoes' => !empty($especificacoes) ? $especificacoes : null,
            'colaborador_id' => ($status === 'alocado' || $status === 'emprestado') ? (int)$colaborador_id : null,
            'status' => $status,
            'observacoes' => $observacoes ?: null,
            'data_cadastro' => date('Y-m-d H:i:s'),
            'data_atualizacao' => date('Y-m-d H:i:s'),
            'data_atribuicao' => ($status === 'alocado' || $status === 'emprestado') ? date('Y-m-d H:i:s') : null,
            'tipo_atribuicao' => $status === 'emprestado' ? 'emprestimo' : ($status === 'alocado' ? 'alocacao' : null)
        ];
        
        // Adicionar ao array específico por status
        if (adicionarEquipamento($novoEquipamento)) {
            $_SESSION['mensagem'] = 'Equipamento cadastrado com sucesso!';
            $_SESSION['mensagem_tipo'] = 'success';
            header('Location: index.php');
            exit;
        } else {
            $mensagem = 'Erro ao salvar o equipamento. Tente novamente.';
            $tipoMensagem = 'error';
        }
    } else {
        $mensagem = implode('<br>', $erros);
        $tipoMensagem = 'error';
    }
}

// Estatísticas para o footer
$total_equipamentos = count(carregarTodosEquipamentos());
$equipamentos_estoque = count(carregarEquipamentosPorStatus('estoque'));
$total_colaboradores = count(lerArquivoJSON('../data/colaboradores/ativos.json'));

// Tipos de equipamento com ícones
$tiposEquipamentos = getTiposEquipamentosComIcones();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Equipamento - Sistema de Gestão</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/favicon/favicon.png">
    <style>
        /* Mantenha o mesmo CSS da versão anterior */
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
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.1);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --transition: all 0.2s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-50) 100%); color: var(--gray-800); line-height: 1.5; }

        /* Header e Navigation (mesmo código anterior) */
        .header { background: var(--white); border-bottom: 1px solid var(--gray-200); position: sticky; top: 0; z-index: 100; box-shadow: var(--shadow-sm); }
        .header-content { max-width: 1440px; margin: 0 auto; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .logo a { display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: var(--primary-dark); transition: var(--transition); }
        .logo a:hover { transform: translateY(-1px); }
        .logo i { font-size: 1.75rem; }
        .logo h1 { font-size: 1.35rem; font-weight: 600; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .user-menu { display: flex; align-items: center; gap: 1.5rem; }
        .user-info { display: flex; align-items: center; gap: 0.75rem; background: var(--primary-light); padding: 0.5rem 1rem; border-radius: 2rem; }
        .user-info i { font-size: 1.1rem; color: var(--primary); }
        .user-name { font-size: 0.875rem; font-weight: 500; color: var(--gray-700); }
        .logout-btn { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: var(--gray-100); color: var(--gray-700); text-decoration: none; border-radius: 2rem; font-size: 0.875rem; transition: var(--transition); }
        .logout-btn:hover { background: var(--gray-200); transform: translateY(-1px); }
        .nav-container { background: var(--white); border-top: 1px solid var(--gray-100); }
        .nav-menu { max-width: 1440px; margin: 0 auto; padding: 0 2rem; list-style: none; display: flex; gap: 2rem; }
        .nav-link { display: flex; align-items: center; gap: 0.5rem; padding: 1rem 0; color: var(--gray-600); text-decoration: none; font-size: 0.875rem; font-weight: 500; transition: var(--transition); border-bottom: 2px solid transparent; }
        .nav-link:hover { color: var(--primary); }
        .nav-link.active { color: var(--primary); border-bottom-color: var(--primary); }
        .main-container { max-width: 1440px; margin: 0 auto; padding: 2rem; }
        .page-header { margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .page-header h1 { font-size: 1.75rem; font-weight: 700; color: var(--gray-800); display: flex; align-items: center; gap: 0.75rem; }
        .page-header h1 i { color: var(--primary); font-size: 1.75rem; }
        .page-subtitle { color: var(--gray-500); font-size: 0.875rem; margin-top: 0.25rem; margin-left: 2.5rem; }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: var(--radius-md); font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: var(--transition); border: none; cursor: pointer; font-family: 'Inter', sans-serif; }
        .btn-primary { background: var(--primary); color: var(--white); }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: var(--shadow-sm); }
        .btn-secondary { background: var(--gray-100); color: var(--gray-700); }
        .btn-secondary:hover { background: var(--gray-200); transform: translateY(-1px); }
        .form-card-container { background: var(--white); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); border: 1px solid var(--gray-200); }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-group label { display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--gray-700); margin-bottom: 0.5rem; font-size: 0.875rem; }
        .form-group label i { color: var(--primary); width: 1rem; }
        .required { color: var(--danger); margin-left: 0.25rem; }
        .optional { color: var(--gray-500); font-size: 0.7rem; font-weight: normal; margin-left: 0.5rem; }
        .form-control, .form-select { width: 100%; padding: 0.625rem 1rem; font-size: 0.875rem; border: 1px solid var(--gray-300); border-radius: var(--radius-md); transition: var(--transition); background: var(--white); font-family: 'Inter', sans-serif; }
        .form-control:focus, .form-select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1); }
        select.form-select { cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%232196F3' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 1rem center; padding-right: 2.5rem; }
        .form-text { font-size: 0.7rem; color: var(--gray-500); margin-top: 0.25rem; display: block; }
        .specs-section { background: var(--gray-50); border-radius: var(--radius-md); padding: 1rem; margin: 1rem 0; border: 1px solid var(--gray-200); }
        .specs-title { font-size: 0.875rem; font-weight: 600; color: var(--gray-700); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        .specs-title i { color: var(--primary); }
        .status-options { display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 0.5rem; }
        .status-option { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: var(--gray-50); border-radius: var(--radius-md); cursor: pointer; transition: var(--transition); border: 1px solid var(--gray-200); }
        .status-option:hover { background: var(--gray-100); border-color: var(--primary-soft); }
        .status-option input[type="radio"] { margin: 0; cursor: pointer; accent-color: var(--primary); }
        .status-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .status-dot-estoque { background: var(--success); }
        .status-dot-alocado { background: var(--info); }
        .status-dot-emprestado { background: var(--warning); }
        .status-dot-manutencao { background: var(--warning); }
        .status-dot-forauso { background: var(--danger); }
        .form-actions { display: flex; gap: 1rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--gray-200); }
        .global-alert { max-width: 1440px; margin: 1rem auto; padding: 1rem; border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center; transition: opacity 0.3s ease; }
        .alert-success { background: rgba(76, 175, 80, 0.1); border-left: 4px solid var(--success); color: #2e7d32; }
        .alert-error { background: rgba(244, 67, 54, 0.1); border-left: 4px solid var(--danger); color: #c62828; }
        .alert-content { display: flex; align-items: center; gap: 0.75rem; }
        .alert-close { background: none; border: none; font-size: 1.25rem; cursor: pointer; color: inherit; opacity: 0.7; }
        .alert-close:hover { opacity: 1; }
        .footer { background: var(--white); border-top: 1px solid var(--gray-200); margin-top: 3rem; }
        .footer-content { max-width: 1440px; margin: 0 auto; padding: 2rem; display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
        .footer-section h3 { font-size: 0.875rem; font-weight: 600; color: var(--gray-700); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        .footer-section p { font-size: 0.875rem; color: var(--gray-500); }
        .footer-links { list-style: none; }
        .footer-links li { margin-bottom: 0.5rem; }
        .footer-links a { color: var(--gray-500); text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.5rem; transition: var(--transition); }
        .footer-links a:hover { color: var(--primary); transform: translateX(3px); }
        .footer-stats { display: flex; gap: 1rem; }
        .footer-stat { text-align: center; }
        .footer-stat .stat-number { display: block; font-size: 1.25rem; font-weight: 600; color: var(--primary); }
        .footer-stat .stat-label { font-size: 0.7rem; color: var(--gray-500); }
        .footer-bottom { max-width: 1440px; margin: 0 auto; padding: 1rem 2rem; border-top: 1px solid var(--gray-200); text-align: center; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; font-size: 0.7rem; color: var(--gray-500); }

        @media (max-width: 1024px) { .footer-content { grid-template-columns: 1fr; text-align: center; } .footer-section h3 { justify-content: center; } .footer-links a { justify-content: center; } .footer-stats { justify-content: center; } }
        @media (max-width: 768px) { .main-container { padding: 1rem; } .form-grid { grid-template-columns: 1fr; gap: 1rem; } .page-header { flex-direction: column; align-items: flex-start; } .status-options { flex-direction: column; } .status-option { width: 100%; } .form-actions { flex-direction: column; } .form-actions .btn { width: 100%; justify-content: center; } .footer-bottom { flex-direction: column; } }
        @media (max-width: 480px) { .user-name { display: none; } .nav-link span { display: none; } .nav-link i { font-size: 1.2rem; } }
    </style>
</head>
<body>

<header class="header">
    <div class="header-content">
        <div class="logo">
            <a href="../index.php">
                <i class="fas fa-laptop"></i>
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
            <li class="nav-item"><a href="../colaboradores/index.php" class="nav-link"><i class="fas fa-users"></i><span>Colaboradores</span></a></li>
            <li class="nav-item"><a href="index.php" class="nav-link active"><i class="fas fa-laptop"></i><span>Equipamentos</span></a></li>
            <li class="nav-item"><a href="../linhas/index.php" class="nav-link"><i class="fas fa-phone"></i><span>Linhas</span></a></li>
        </ul>
    </nav>
</header>

<?php if ($mensagem): ?>
    <div class="global-alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : 'error'; ?>">
        <div class="alert-content">
            <i class="fas fa-<?php echo $tipoMensagem === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <span><?php echo $mensagem; ?></span>
        </div>
        <button class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
<?php endif; ?>

<main class="main-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-laptop-medical"></i> Adicionar Equipamento</h1>
            <p class="page-subtitle">Preencha os dados abaixo para cadastrar um novo equipamento</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="form-card-container">
        <form method="POST" action="" class="form-card" id="form-equipamento">
            <div class="form-grid">
                <div class="form-group">
                    <label for="tipo"><i class="fas fa-tag"></i> Tipo de Equipamento <span class="required">*</span></label>
                    <select id="tipo" name="tipo" required class="form-select" onchange="toggleEspecificacoes()">
                        <option value="">-- Selecione o tipo --</option>
                        <?php foreach ($tiposEquipamentos as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($tipoPreSelecionado == $key || (isset($_POST['tipo']) && $_POST['tipo'] == $key)) ? 'selected' : ''; ?>>
                                <i class="fas fa-<?php echo $value['icone']; ?>"></i> <?php echo $value['nome']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="marca"><i class="fas fa-industry"></i> Marca</label>
                    <input type="text" id="marca" name="marca" value="<?php echo htmlspecialchars($_POST['marca'] ?? ''); ?>" class="form-control" placeholder="Ex: Dell, HP, Lenovo">
                    <small class="form-text">Opcional</small>
                </div>

                <div class="form-group">
                    <label for="modelo"><i class="fas fa-laptop"></i> Modelo</label>
                    <input type="text" id="modelo" name="modelo" value="<?php echo htmlspecialchars($_POST['modelo'] ?? ''); ?>" class="form-control" placeholder="Ex: Latitude 5420, iPhone 13">
                    <small class="form-text">Opcional</small>
                </div>

                <div class="form-group" id="hostname-group" style="display: none;">
                    <label for="hostname"><i class="fas fa-network-wired"></i> Hostname <span class="required">*</span></label>
                    <input type="text" id="hostname" name="hostname" value="<?php echo htmlspecialchars($_POST['hostname'] ?? ''); ?>" class="form-control" placeholder="Ex: AS-NOTE-01">
                    <small class="form-text">Obrigatório para Notebooks e Desktops</small>
                </div>

                <div class="form-group">
                    <label for="patrimonio"><i class="fas fa-barcode"></i> Número de Patrimônio <span class="required">*</span></label>
                    <input type="text" id="patrimonio" name="patrimonio" value="<?php echo htmlspecialchars($_POST['patrimonio'] ?? ''); ?>" required class="form-control" placeholder="Ex: 001, 002">
                </div>

                <div class="form-group">
                    <label for="serial"><i class="fas fa-hashtag"></i> Número de Série</label>
                    <input type="text" id="serial" name="serial" value="<?php echo htmlspecialchars($_POST['serial'] ?? ''); ?>" class="form-control" placeholder="Ex: PE34F4">
                    <small class="form-text">Opcional</small>
                </div>

                <input type="hidden" name="centro_custo" value="11001">
            </div>

            <div id="especificacoes-section" class="specs-section" style="display: none;">
                <div class="specs-title">
                    <i class="fas fa-microchip"></i>
                    <span>Especificações Técnicas</span>
                    <small class="optional">(Opcional - Apenas para Desktop e Notebook)</small>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="ram"><i class="fas fa-memory"></i> Memória RAM</label>
                        <input type="text" id="ram" name="ram" value="<?php echo htmlspecialchars($_POST['ram'] ?? ''); ?>" class="form-control" placeholder="Ex: 8GB, 16GB DDR4">
                        <small class="form-text">Opcional</small>
                    </div>
                    <div class="form-group">
                        <label for="processador"><i class="fas fa-microchip"></i> Processador</label>
                        <input type="text" id="processador" name="processador" value="<?php echo htmlspecialchars($_POST['processador'] ?? ''); ?>" class="form-control" placeholder="Ex: Intel Core i5, Ryzen 7">
                        <small class="form-text">Opcional</small>
                    </div>
                    <div class="form-group">
                        <label for="hd"><i class="fas fa-hdd"></i> Armazenamento (HD/SSD)</label>
                        <input type="text" id="hd" name="hd" value="<?php echo htmlspecialchars($_POST['hd'] ?? ''); ?>" class="form-control" placeholder="Ex: 256GB SSD, 1TB HD">
                        <small class="form-text">Opcional</small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Status do Equipamento <span class="required">*</span></label>
                <div class="status-options">
                    <label class="status-option">
                        <input type="radio" name="status" value="estoque" <?php echo (!isset($_POST['status']) || $_POST['status'] === 'estoque') ? 'checked' : ''; ?> onchange="toggleColaboradorSelect(false)">
                        <span class="status-dot status-dot-estoque"></span>
                        <span>Em Estoque</span>
                    </label>
                    <label class="status-option">
                        <input type="radio" name="status" value="alocado" <?php echo (isset($_POST['status']) && $_POST['status'] === 'alocado') ? 'checked' : ''; ?> onchange="toggleColaboradorSelect(true)">
                        <span class="status-dot status-dot-alocado"></span>
                        <span>Alocar para Colaborador</span>
                    </label>
                    <label class="status-option">
                        <input type="radio" name="status" value="emprestado" <?php echo (isset($_POST['status']) && $_POST['status'] === 'emprestado') ? 'checked' : ''; ?> onchange="toggleColaboradorSelect(true)">
                        <span class="status-dot status-dot-emprestado"></span>
                        <span>Emprestar</span>
                    </label>
                    <label class="status-option">
                        <input type="radio" name="status" value="manutencao" <?php echo (isset($_POST['status']) && $_POST['status'] === 'manutencao') ? 'checked' : ''; ?> onchange="toggleColaboradorSelect(false)">
                        <span class="status-dot status-dot-manutencao"></span>
                        <span>Enviar para Manutenção</span>
                    </label>
                    <label class="status-option">
                        <input type="radio" name="status" value="fora_uso" <?php echo (isset($_POST['status']) && $_POST['status'] === 'fora_uso') ? 'checked' : ''; ?> onchange="toggleColaboradorSelect(false)">
                        <span class="status-dot status-dot-forauso"></span>
                        <span>Fora de Uso</span>
                    </label>
                </div>
            </div>

            <div class="form-group" id="colaborador-select" style="display: <?php echo (isset($_POST['status']) && in_array($_POST['status'], ['alocado', 'emprestado'])) ? 'block' : 'none'; ?>;">
                <label for="colaborador_id"><i class="fas fa-user"></i> Selecionar Colaborador <span class="required">*</span></label>
                <select id="colaborador_id" name="colaborador_id" class="form-select">
                    <option value="">-- Selecione um colaborador --</option>
                    <?php if (empty($colaboradores)): ?>
                        <option value="" disabled>Nenhum colaborador cadastrado</option>
                    <?php else: ?>
                        <?php foreach ($colaboradores as $colaborador): ?>
                            <option value="<?php echo $colaborador['id']; ?>" <?php echo (isset($_POST['colaborador_id']) && $_POST['colaborador_id'] == $colaborador['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($colaborador['nome'] . ' - ' . ($colaborador['departamento'] ?? 'N/A')); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if (empty($colaboradores)): ?>
                    <small class="form-text text-danger"><i class="fas fa-exclamation-triangle"></i> Não há colaboradores cadastrados. <a href="../colaboradores/adicionar.php">Cadastre um colaborador primeiro</a>.</small>
                <?php endif; ?>
            </div>

            <div class="form-group full-width">
                <label for="observacoes"><i class="fas fa-sticky-note"></i> Observações</label>
                <textarea id="observacoes" name="observacoes" class="form-control" rows="3" placeholder="Observações, características especiais, problemas conhecidos..."><?php echo htmlspecialchars($_POST['observacoes'] ?? ''); ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Equipamento</button>
                <button type="reset" class="btn btn-secondary" onclick="resetForm()"><i class="fas fa-redo"></i> Limpar</button>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            </div>
        </form>
    </div>
</main>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3><i class="fas fa-laptop"></i> Sistema de Gestão</h3>
            <p>Controle de colaboradores e equipamentos</p>
        </div>
        <div class="footer-section">
            <h3>Links Rápidos</h3>
            <ul class="footer-links">
                <li><a href="../index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="../colaboradores/index.php"><i class="fas fa-users"></i> Colaboradores</a></li>
                <li><a href="index.php"><i class="fas fa-laptop"></i> Equipamentos</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Estatísticas</h3>
            <div class="footer-stats">
                <div class="footer-stat"><span class="stat-number"><?php echo $total_equipamentos; ?></span><span class="stat-label">Equipamentos</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo $equipamentos_estoque; ?></span><span class="stat-label">Em Estoque</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo $total_colaboradores; ?></span><span class="stat-label">Colaboradores</span></div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
        <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
</footer>

<script>
    function toggleColaboradorSelect(show) {
        const selectDiv = document.getElementById('colaborador-select');
        const selectElement = document.getElementById('colaborador_id');
        if (show) {
            selectDiv.style.display = 'block';
            if (selectElement) selectElement.required = true;
        } else {
            selectDiv.style.display = 'none';
            if (selectElement) selectElement.required = false;
        }
    }

    function toggleEspecificacoes() {
        const tipo = document.getElementById('tipo').value;
        const hostnameGroup = document.getElementById('hostname-group');
        const hostnameInput = document.getElementById('hostname');
        const especificacoesSection = document.getElementById('especificacoes-section');
        const comHostname = (tipo === 'notebook' || tipo === 'desktop');

        if (comHostname) {
            hostnameGroup.style.display = 'block';
            hostnameInput.required = true;
            especificacoesSection.style.display = 'block';
        } else {
            hostnameGroup.style.display = 'none';
            hostnameInput.required = false;
            hostnameInput.value = '';
            especificacoesSection.style.display = 'none';
        }
    }

    const ccInput = document.getElementById('centro_custo');
    if (ccInput) {
        ccInput.addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase();
            value = value.replace(/[^A-Z0-9]/g, '');
            e.target.value = value;
        });
    }

    const hostnameInput = document.getElementById('hostname');
    if (hostnameInput) {
        hostnameInput.addEventListener('input', function(e) {
            let value = e.target.value;
            value = value.replace(/[^a-zA-Z0-9\-_]/g, '');
            e.target.value = value;
        });
    }

    function resetForm() {
        if (confirm('Tem certeza que deseja limpar todos os campos?')) {
            document.getElementById('form-equipamento').reset();
            toggleColaboradorSelect(false);
            toggleEspecificacoes();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const statusRadio = document.querySelector('input[name="status"]:checked');
        if (statusRadio) {
            toggleColaboradorSelect(statusRadio.value === 'alocado' || statusRadio.value === 'emprestado');
        }
        toggleEspecificacoes();
        
        const form = document.getElementById('form-equipamento');
        if (form) {
            form.addEventListener('submit', function(e) {
                const patrimonio = document.getElementById('patrimonio').value.trim();
                const tipo = document.getElementById('tipo').value;
                const statusRadio = document.querySelector('input[name="status"]:checked');
                const hostname = document.getElementById('hostname').value.trim();
                
                if (!statusRadio) {
                    alert('Selecione o status do equipamento.');
                    e.preventDefault();
                    return false;
                }
                const status = statusRadio.value;
                const colaborador = document.getElementById('colaborador_id') ? document.getElementById('colaborador_id').value : '';
                
                if (patrimonio.length < 3) {
                    alert('O número de patrimônio deve ter pelo menos 3 caracteres.');
                    e.preventDefault();
                    return false;
                }
                if (tipo === '') {
                    alert('Selecione o tipo de equipamento.');
                    e.preventDefault();
                    return false;
                }
                if ((tipo === 'notebook' || tipo === 'desktop') && hostname === '') {
                    alert('O hostname é obrigatório para equipamentos do tipo ' + (tipo === 'notebook' ? 'Notebook' : 'Desktop') + '.');
                    e.preventDefault();
                    return false;
                }
                if ((status === 'alocado' || status === 'emprestado') && colaborador === '') {
                    alert('Selecione um colaborador para ' + (status === 'emprestado' ? 'emprestar' : 'alocar') + ' o equipamento.');
                    e.preventDefault();
                    return false;
                }
                return true;
            });
        }
    });
    
    setTimeout(function() {
        const alert = document.querySelector('.global-alert');
        if (alert) {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }
    }, 5000);
</script>
</body>
</html>