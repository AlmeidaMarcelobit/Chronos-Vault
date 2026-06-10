<?php
session_start();
require_once '../includes/funcoes.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

// Verificar nível do usuário (apenas admin pode ver inativos)
$usuario_nivel = $_SESSION['usuario_nivel'] ?? 'user';
$is_admin = ($usuario_nivel === 'admin');

if (!$is_admin) {
    $_SESSION['mensagem'] = 'Acesso negado. Apenas administradores podem acessar esta página.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

// Carregar colaboradores inativos
$colaboradoresInativos = lerArquivoJSON('../data/colaboradores/inativos.json');
if ($colaboradoresInativos === false) $colaboradoresInativos = [];

// Garantir que todos os colaboradores tenham os campos necessários
foreach ($colaboradoresInativos as &$colab) {
    if (!isset($colab['matricula'])) $colab['matricula'] = '';
    if (!isset($colab['cpf'])) $colab['cpf'] = '';
    if (!isset($colab['departamento'])) $colab['departamento'] = '';
    if (!isset($colab['email'])) $colab['email'] = '';
    if (!isset($colab['tipo_trabalho'])) $colab['tipo_trabalho'] = 'local';
}

// Ordenar colaboradores em ordem alfabética por nome
usort($colaboradoresInativos, function($a, $b) {
    return strcmp($a['nome'], $b['nome']);
});

// Buscar por nome (se aplicável)
$busca = $_GET['busca'] ?? '';
if ($busca) {
    $colaboradoresInativos = array_filter($colaboradoresInativos, function($colaborador) use ($busca) {
        return stripos($colaborador['nome'], $busca) !== false ||
                (isset($colaborador['matricula']) && stripos($colaborador['matricula'], $busca) !== false) ||
                (isset($colaborador['cpf']) && stripos($colaborador['cpf'], $busca) !== false) ||
                (isset($colaborador['departamento']) && stripos($colaborador['departamento'], $busca) !== false) ||
                (isset($colaborador['email']) && stripos($colaborador['email'], $busca) !== false);
    });
    
    // Reordenar após o filtro
    usort($colaboradoresInativos, function($a, $b) {
        return strcmp($a['nome'], $b['nome']);
    });
}

// Estatísticas
$totalInativos = count($colaboradoresInativos);
$totalHomeOfficeInativos = count(array_filter($colaboradoresInativos, function($c) {
    return ($c['tipo_trabalho'] ?? 'local') === 'home';
}));

// Função para reativar colaborador
function reativarColaborador($colaboradorId, $colaboradoresInativos, $colaboradoresAtivos) {
    // Buscar colaborador nos inativos
    $colaboradorEncontrado = null;
    $colaboradorIndex = null;
    foreach ($colaboradoresInativos as $index => $colab) {
        if ($colab['id'] == $colaboradorId) {
            $colaboradorEncontrado = $colab;
            $colaboradorIndex = $index;
            break;
        }
    }
    
    if (!$colaboradorEncontrado) {
        return ['success' => false, 'message' => 'Colaborador não encontrado nos inativos.'];
    }
    
    // Remover dados de inativação
    unset($colaboradorEncontrado['data_inativacao']);
    unset($colaboradorEncontrado['motivo_inativacao']);
    
    // Adicionar data de reativação
    $colaboradorEncontrado['data_reativacao'] = date('Y-m-d H:i:s');
    $colaboradorEncontrado['data_atualizacao'] = date('Y-m-d H:i:s');
    
    // Remover dos inativos
    array_splice($colaboradoresInativos, $colaboradorIndex, 1);
    
    // Adicionar aos ativos
    $colaboradoresAtivos[] = $colaboradorEncontrado;
    
    // Salvar alterações
    $saveInativos = salvarArquivoJSON('../data/colaboradores/inativos.json', $colaboradoresInativos);
    $saveAtivos = salvarArquivoJSON('../data/colaboradores/ativos.json', $colaboradoresAtivos);
    
    if ($saveInativos && $saveAtivos) {
        return ['success' => true, 'message' => "Colaborador reativado com sucesso!"];
    } else {
        return ['success' => false, 'message' => 'Erro ao reativar colaborador. Tente novamente.'];
    }
}

// Função para excluir permanentemente
function excluirPermanente($colaboradorId, $colaboradoresInativos) {
    $colaboradorIndex = null;
    foreach ($colaboradoresInativos as $index => $colab) {
        if ($colab['id'] == $colaboradorId) {
            $colaboradorIndex = $index;
            break;
        }
    }
    
    if ($colaboradorIndex === null) {
        return ['success' => false, 'message' => 'Colaborador não encontrado.'];
    }
    
    array_splice($colaboradoresInativos, $colaboradorIndex, 1);
    
    if (salvarArquivoJSON('../data/colaboradores/inativos.json', $colaboradoresInativos)) {
        return ['success' => true, 'message' => 'Colaborador excluído permanentemente.'];
    } else {
        return ['success' => false, 'message' => 'Erro ao excluir colaborador.'];
    }
}

// Processar reativação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reativar'])) {
        $colaboradorId = $_POST['colaborador_id'] ?? null;
        if ($colaboradorId) {
            $colaboradoresAtivos = lerArquivoJSON('../data/colaboradores/ativos.json');
            if ($colaboradoresAtivos === false) $colaboradoresAtivos = [];
            
            $resultado = reativarColaborador($colaboradorId, $colaboradoresInativos, $colaboradoresAtivos);
            $_SESSION['mensagem'] = $resultado['message'];
            $_SESSION['mensagem_tipo'] = $resultado['success'] ? 'success' : 'error';
            
            header('Location: inativos.php');
            exit;
        }
    } elseif (isset($_POST['excluir_permanente'])) {
        $colaboradorId = $_POST['colaborador_id'] ?? null;
        if ($colaboradorId) {
            $resultado = excluirPermanente($colaboradorId, $colaboradoresInativos);
            $_SESSION['mensagem'] = $resultado['message'];
            $_SESSION['mensagem_tipo'] = $resultado['success'] ? 'success' : 'error';
            
            header('Location: inativos.php');
            exit;
        }
    }
}
?>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colaboradores Inativos - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/colaboradores/inativos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/favicon/favicon.png">
</head>
<body>

<!-- ==================== HEADER ==================== -->
<header class="header">
    <div class="header-content">
        <div class="logo">
            <a href="../index.php">
                <i class="fas fa-users"></i>
                <h1>Gestão de Colaboradores</h1>
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
            <li class="nav-item"><a href="index.php" class="nav-link"><i class="fas fa-users"></i><span>Colaboradores</span></a></li>
            <li class="nav-item"><a href="inativos.php" class="nav-link active"><i class="fas fa-archive"></i><span>Inativos</span></a></li>
            <li class="nav-item"><a href="../equipamentos/index.php" class="nav-link"><i class="fas fa-laptop"></i><span>Equipamentos</span></a></li>
            <li class="nav-item"><a href="../linhas/index.php" class="nav-link"><i class="fas fa-phone"></i><span>Linhas</span></a></li>
            <li class="nav-item"><a href="../termos/index.php" class="nav-link"><i class="fas fa-file-contract"></i><span>Termos</span></a></li>
            <li class="nav-item"><a href="../usuarios/index.php" class="nav-link"><i class="fas fa-user-cog"></i><span>Usuários</span></a></li>
        </ul>
    </nav>
</header>

<!-- Mensagens de alerta -->
<?php if (isset($_SESSION['mensagem'])): ?>
    <div class="global-alert" style="background: <?php echo $_SESSION['mensagem_tipo'] === 'success' ? 'rgba(76,175,80,0.1)' : 'rgba(244,67,54,0.1)'; ?>; border-left: 4px solid <?php echo $_SESSION['mensagem_tipo'] === 'success' ? '#4CAF50' : '#F44336'; ?>;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas fa-<?php echo $_SESSION['mensagem_tipo'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>" style="color: <?php echo $_SESSION['mensagem_tipo'] === 'success' ? '#4CAF50' : '#F44336'; ?>;"></i>
            <span><?php echo htmlspecialchars($_SESSION['mensagem']); ?></span>
        </div>
        <button onclick="this.parentElement.style.display='none'" style="background: none; border: none; font-size: 1.25rem; cursor: pointer;">&times;</button>
    </div>
    <?php unset($_SESSION['mensagem']); unset($_SESSION['mensagem_tipo']); ?>
<?php endif; ?>

<!-- ==================== CONTEÚDO PRINCIPAL ==================== -->
<main class="main-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-archive"></i> Colaboradores Inativos</h1>
            <p class="page-subtitle">Colaboradores que foram inativados do sistema</p>
        </div>
        <a href="index.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Voltar para Ativos
        </a>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon gray"><i class="fas fa-users"></i></div>
            <div class="stat-content">
                <h3>Total Inativos</h3>
                <div class="stat-number"><?php echo $totalInativos; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-home"></i></div>
            <div class="stat-content">
                <h3>Home Office Inativos</h3>
                <div class="stat-number"><?php echo $totalHomeOfficeInativos; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon danger"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-content">
                <h3>Período de Retenção</h3>
                <div class="stat-number">90 dias</div>
                <div class="stat-label">para exclusão automática</div>
            </div>
        </div>
    </div>

    <!-- Busca -->
    <div class="search-section">
        <form method="GET" action="">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="busca" class="search-input" placeholder="Buscar por nome, chamado, CPF, e-mail ou departamento..." value="<?php echo htmlspecialchars($busca); ?>">
                <button type="submit" class="btn btn-primary search-btn">Buscar</button>
                <?php if ($busca): ?>
                    <a href="inativos.php" class="btn btn-secondary">Limpar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Grid de Colaboradores Inativos -->
    <?php if (empty($colaboradoresInativos)): ?>
        <div class="empty-state">
            <i class="fas fa-archive"></i>
            <p>Nenhum colaborador inativo encontrado</p>
            <small>Colaboradores inativados aparecerão aqui</small>
            <?php if ($busca): ?>
                <div style="margin-top: 1rem;">
                    <a href="inativos.php" class="btn btn-secondary">Limpar busca</a>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="colaboradores-grid">
            <?php foreach ($colaboradoresInativos as $colaborador):
                $tipoTrabalho = $colaborador['tipo_trabalho'] ?? 'local';
                $dataInativacao = isset($colaborador['data_inativacao']) ? date('d/m/Y H:i', strtotime($colaborador['data_inativacao'])) : 'Data não registrada';
                $motivoInativacao = $colaborador['motivo_inativacao'] ?? 'Inativado pelo sistema';
                ?>
                <div class="colaborador-card">
                    <div class="card-header">
                        <div class="colaborador-nome">
                            <h3><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($colaborador['nome']); ?></h3>
                            <span class="tipo-badge-header">
                                <i class="fas fa-<?php echo $tipoTrabalho === 'home' ? 'home' : 'building'; ?>"></i>
                                <?php echo $tipoTrabalho === 'home' ? 'Home Office' : 'Presencial'; ?>
                            </span>
                        </div>
                        <div class="inativo-badge">
                            <i class="fas fa-user-slash"></i> Inativo
                        </div>
                        <?php if (!empty($colaborador['matricula'])): ?>
                            <div class="matricula-badge-header">
                                <i class="fas fa-id-badge"></i> <?php echo htmlspecialchars($colaborador['matricula']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-body">
                        <div class="inativo-info">
                            <p><i class="fas fa-calendar-times"></i> <strong>Inativado em:</strong> <?php echo $dataInativacao; ?></p>
                            <p><i class="fas fa-info-circle"></i> <strong>Motivo:</strong> <?php echo htmlspecialchars($motivoInativacao); ?></p>
                        </div>

                        <div class="info-group">
                            <div class="info-label"><i class="fas fa-briefcase"></i> Cargo</div>
                            <div class="info-value"><?php echo htmlspecialchars($colaborador['cargo'] ?? 'Não informado'); ?></div>
                        </div>

                        <div class="info-group">
                            <div class="info-label"><i class="fas fa-id-card"></i> CPF</div>
                            <div class="info-value"><?php echo formatarCPF($colaborador['cpf'] ?? ''); ?></div>
                        </div>

                        <div class="info-group">
                            <div class="info-label"><i class="fas fa-envelope"></i> E-mail</div>
                            <div class="info-value">
                                <?php if (!empty($colaborador['email'])): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($colaborador['email']); ?>" class="email-link">
                                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($colaborador['email']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Não informado</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="info-group">
                            <div class="info-label"><i class="fas fa-building"></i> Departamento</div>
                            <div class="info-value">
                                <span class="departamento-badge"><?php echo htmlspecialchars($colaborador['departamento'] ?? 'Não informado'); ?></span>
                            </div>
                        </div>

                        <div class="info-group">
                            <div class="info-label"><i class="fas fa-dollar-sign"></i> Centro de Custo</div>
                            <div class="info-value">
                                <span class="cc-badge"><?php echo htmlspecialchars($colaborador['centro_custo'] ?? 'Não informado'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <form method="POST" style="display: inline-block;" onsubmit="return confirm('Tem certeza que deseja reativar este colaborador?')">
                            <input type="hidden" name="colaborador_id" value="<?php echo $colaborador['id']; ?>">
                            <button type="submit" name="reativar" class="action-btn reativar" title="Reativar">
                                <i class="fas fa-user-plus"></i><span>Reativar</span>
                            </button>
                        </form>
                        <!--
                        <form method="POST" style="display: inline-block;" onsubmit="return confirm('Tem certeza que deseja excluir permanentemente este colaborador? Esta ação não pode ser desfeita.')">
                            <input type="hidden" name="colaborador_id" value="<?php echo $colaborador['id']; ?>">
                            <button type="submit" name="excluir_permanente" class="action-btn delete" title="Excluir Permanentemente">
                                <i class="fas fa-trash-alt"></i><span>Excluir</span>
                            </button>
                        </form>
                        -->
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<!-- ==================== FOOTER ==================== -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3><i class="fas fa-archive"></i> Sistema de Gestão</h3>
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
            <?php
            $total_ativos = count(lerArquivoJSON('../data/colaboradores/ativos.json'));
            $total_inativos = count(lerArquivoJSON('../data/colaboradores/inativos.json'));
            $total_equipamentos = count(lerArquivoJSON('../data/equipamentos.json'));
            ?>
            <div class="footer-stats">
                <div class="footer-stat"><span class="stat-number"><?php echo $total_ativos; ?></span><span class="stat-label">Ativos</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo $total_inativos; ?></span><span class="stat-label">Inativos</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo $total_equipamentos; ?></span><span class="stat-label">Equipamentos</span></div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
        <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
</footer>

</body>
</html>