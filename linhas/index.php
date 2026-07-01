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

$linhas = lerArquivoJSON('../data/linhas.json');
$colaboradores = lerArquivoJSON('../data/colaboradores/ativos.json');

// Criar mapa de colaboradores
$mapaColaboradores = [];
foreach ($colaboradores as $colaborador) {
    $mapaColaboradores[$colaborador['id']] = $colaborador;
}

// Ordenar linhas por número
usort($linhas, function($a, $b) {
    return strcmp($a['numero'], $b['numero']);
});

// Estatísticas
$totalLinhas = count($linhas);
$totalDisponiveis = count(array_filter($linhas, function($l) { return $l['status'] === 'disponivel'; }));
$totalAlocados = count(array_filter($linhas, function($l) { return $l['status'] === 'alocado'; }));
$totalIndisponiveis = count(array_filter($linhas, function($l) { return $l['status'] === 'indisponivel'; }));
$totalChips = count(array_filter($linhas, function($l) { return $l['tipo'] === 'chip'; }));
$totalEChips = count(array_filter($linhas, function($l) { return $l['tipo'] === 'echip'; }));
$pctAlocados = $totalLinhas > 0 ? round(($totalAlocados / $totalLinhas) * 100) : 0;

// Aplicar filtros
$busca = $_GET['busca'] ?? '';
$status = $_GET['status'] ?? '';
$tipo = $_GET['tipo'] ?? '';

// Filtrar por busca
if (!empty($busca)) {
    $buscaLower = strtolower($busca);
    $buscaNumeros = preg_replace('/[^0-9]/', '', $busca);

    $linhas = array_filter($linhas, function($linha) use ($buscaLower, $buscaNumeros) {
        $numeroPuro = preg_replace('/[^0-9]/', '', $linha['numero']);
        return stripos($linha['numero'], $buscaLower) !== false ||
                stripos($numeroPuro, $buscaNumeros) !== false ||
                stripos($linha['centro_custo'], $buscaLower) !== false;
    });
}

// Filtrar por status
if (!empty($status)) {
    $linhas = array_filter($linhas, function($linha) use ($status) {
        return $linha['status'] === $status;
    });
}

// Filtrar por tipo
if (!empty($tipo)) {
    $linhas = array_filter($linhas, function($linha) use ($tipo) {
        return $linha['tipo'] === $tipo;
    });
}

$totalFiltrado = count($linhas);

// Processar ação de indisponível
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['indisponivel'])) {
    $id = $_POST['id'] ?? null;
    if ($id) {
        foreach ($linhas as $index => $linha) {
            if ($linha['id'] == $id) {
                $linhas[$index]['status'] = 'indisponivel';
                $linhas[$index]['data_atualizacao'] = date('Y-m-d H:i:s');
                break;
            }
        }
        if (salvarArquivoJSON('../data/linhas.json', $linhas)) {
            $_SESSION['mensagem'] = 'Linha marcada como indisponível com sucesso!';
            $_SESSION['mensagem_tipo'] = 'success';
        } else {
            $_SESSION['mensagem'] = 'Erro ao marcar linha como indisponível.';
            $_SESSION['mensagem_tipo'] = 'error';
        }
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Linhas Telefônicas - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/linhas/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/favicon/favicon.png">
    <style>
        /* ========================================
           NOVOS ESTILOS PARA O BOTÃO INDISPONÍVEL
           ======================================== */
        .action-indisponivel:hover {
            background: var(--warning);
            color: var(--white);
            border-color: var(--warning);
        }

        .status-indisponivel {
            background: rgba(255, 193, 7, 0.15);
            color: #856404;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }

        .status-indisponivel i {
            color: var(--warning);
        }

        /* Badge Indisponível */
        .status-badge.status-indisponivel {
            background: rgba(255, 193, 7, 0.15);
            color: #856404;
        }

        .status-badge.status-indisponivel i {
            color: var(--warning);
        }
    </style>
</head>
<body>
<!-- HEADER -->
<header class="header">
    <div class="header-content">
        <div class="logo">
            <a href="../index.php">
                <i class="fas fa-phone"></i>
                <h1>Gestão de Linhas</h1>
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
            <li class="nav-item"><a href="../equipamentos/index.php" class="nav-link"><i class="fas fa-laptop"></i><span>Equipamentos</span></a></li>
            <li class="nav-item"><a href="../linhas/index.php" class="nav-link active"><i class="fas fa-phone"></i><span>Linhas</span></a></li>
            <?php if ($is_admin): ?>
                <li class="nav-item"><a href="../termos/index.php" class="nav-link"><i class="fas fa-file-contract"></i><span>Termos</span></a></li>
                <li class="nav-item"><a href="../usuarios/index.php" class="nav-link"><i class="fas fa-user-cog"></i><span>Usuários</span></a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<!-- CONTEÚDO PRINCIPAL -->
<main class="main-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-phone"></i> Linhas Telefônicas</h1>
            <p class="page-subtitle">Gerencie as linhas Vivo (Chip e E-Chip) da organização</p>
        </div>
        <?php if ($can_edit): ?>
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <a href="alocar_sequencial.php" class="btn btn-outline">
                    <i class="fas fa-list-ol"></i> Alocar Sequencial
                </a>
                <a href="adicionar.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Adicionar Linha
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-icon"><i class="fas fa-phone"></i></div>
            <div class="stat-content">
                <h3>Total de Linhas</h3>
                <p class="stat-number"><?php echo $totalLinhas; ?></p>
                <div class="stat-meta">
                    <span><i class="fas fa-sim-card"></i> <?php echo $totalChips; ?> chips</span>
                    <span><i class="fas fa-microchip"></i> <?php echo $totalEChips; ?> e-chips</span>
                </div>
            </div>
        </div>
        <div class="stat-card stat-success">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-content">
                <h3>Disponíveis</h3>
                <p class="stat-number"><?php echo $totalDisponiveis; ?></p>
                <div class="stat-bar-wrap">
                    <div class="stat-bar stat-bar-success" style="width:<?php echo $totalLinhas > 0 ? round(($totalDisponiveis/$totalLinhas)*100) : 0; ?>%"></div>
                </div>
            </div>
        </div>
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="fas fa-user-check"></i></div>
            <div class="stat-content">
                <h3>Alocados</h3>
                <p class="stat-number"><?php echo $totalAlocados; ?></p>
                <div class="stat-bar-wrap">
                    <div class="stat-bar stat-bar-warning" style="width:<?php echo $pctAlocados; ?>%"></div>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left: 4px solid var(--warning);">
            <div class="stat-icon" style="background: rgba(255, 193, 7, 0.15); color: var(--warning);">
                <i class="fas fa-ban"></i>
            </div>
            <div class="stat-content">
                <h3>Indisponíveis</h3>
                <p class="stat-number"><?php echo $totalIndisponiveis; ?></p>
                <div class="stat-bar-wrap">
                    <div class="stat-bar" style="width:<?php echo $totalLinhas > 0 ? round(($totalIndisponiveis/$totalLinhas)*100) : 0; ?>%; background: var(--warning);"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filter-card">
        <form method="GET" action="" class="filter-form">
            <div class="filter-grid">
                <div class="filter-group">
                    <label><i class="fas fa-search"></i> Buscar</label>
                    <input type="text" name="busca" class="form-control" placeholder="Número (ex: 16 99618-5975) ou Centro de Custo..." value="<?php echo htmlspecialchars($busca); ?>">
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-tag"></i> Tipo</label>
                    <select name="tipo" class="form-control">
                        <option value="">Todos</option>
                        <option value="chip" <?php echo $tipo == 'chip' ? 'selected' : ''; ?>>Chip Físico</option>
                        <option value="echip" <?php echo $tipo == 'echip' ? 'selected' : ''; ?>>E-Chip (eSIM)</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-circle"></i> Status</label>
                    <select name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="disponivel" <?php echo $status == 'disponivel' ? 'selected' : ''; ?>>Disponível</option>
                        <option value="alocado" <?php echo $status == 'alocado' ? 'selected' : ''; ?>>Alocado</option>
                        <option value="indisponivel" <?php echo $status == 'indisponivel' ? 'selected' : ''; ?>>Indisponível</option>
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Limpar</a>
            </div>
        </form>
    </div>

    <!-- Tabela de Linhas -->
    <div class="table-container">
        <table class="data-table">
            <thead>
            <tr>
                <th>Número</th>
                <th>Tipo</th>
                <th>Centro de Custo</th>
                <th>Status</th>
                <th>Colaborador</th>
                <th>Cadastro</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($linhas)): ?>
                <tr>
                    <td colspan="7" class="empty-state">
                        <i class="fas fa-phone-slash"></i>
                        <p>Nenhuma linha encontrada</p>
                        <?php if ($busca || $status || $tipo): ?>
                            <a href="index.php" class="btn btn-secondary">Limpar filtros</a>
                        <?php else: ?>
                            <a href="adicionar.php" class="btn btn-primary">Adicionar primeira linha</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($linhas as $linha):
                    $colaboradorNome = '---';
                    if (!empty($linha['colaborador_id']) && isset($mapaColaboradores[$linha['colaborador_id']])) {
                        $colaboradorNome = $mapaColaboradores[$linha['colaborador_id']]['nome'];
                    }
                    
                    // Classes de status
                    $statusClass = '';
                    if ($linha['status'] === 'disponivel') {
                        $statusClass = 'status-ativo';
                    } elseif ($linha['status'] === 'alocado') {
                        $statusClass = 'status-inativo';
                    } elseif ($linha['status'] === 'indisponivel') {
                        $statusClass = 'status-indisponivel';
                    }
                    
                    $tipoClass = $linha['tipo'] === 'chip' ? 'tipo-badge-chip' : 'tipo-badge-echip';
                    ?>
                    <tr>
                        <td data-label="Número">
                            <span class="numero-link">
                                <i class="fas fa-phone"></i>
                                <?php echo formatarTelefone($linha['numero']); ?>
                            </span>
                        </td>
                        <td data-label="Tipo">
                            <span class="tipo-badge <?php echo $tipoClass; ?>">
                                <i class="fas fa-<?php echo $linha['tipo'] === 'chip' ? 'sim-card' : 'microchip'; ?>"></i>
                                <?php echo getTipoLinhaTexto($linha['tipo']); ?>
                            </span>
                        </td>
                        <td data-label="Centro de Custo">
                            <span class="departamento-badge">
                                <?php echo htmlspecialchars($linha['centro_custo']); ?>
                            </span>
                        </td>
                        <td data-label="Status">
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <i class="fas fa-<?php 
                                    echo $linha['status'] === 'disponivel' ? 'check-circle' : 
                                        ($linha['status'] === 'alocado' ? 'user-check' : 'ban'); 
                                ?>"></i>
                                <?php 
                                    if ($linha['status'] === 'disponivel') {
                                        echo 'Disponível';
                                    } elseif ($linha['status'] === 'alocado') {
                                        echo 'Alocado';
                                    } elseif ($linha['status'] === 'indisponivel') {
                                        echo 'Indisponível';
                                    }
                                ?>
                            </span>
                        </td>
                        <td data-label="Colaborador">
                            <?php if ($colaboradorNome !== '---'): ?>
                                <div class="colaborador-info">
                                    <i class="fas fa-user-circle"></i>
                                    <span><?php echo htmlspecialchars($colaboradorNome); ?></span>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">---</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Cadastro">
                            <span class="data-cadastro">
                                <?php echo !empty($linha['data_cadastro']) ? date('d/m/Y', strtotime($linha['data_cadastro'])) : '---'; ?>
                            </span>
                        </td>
                        <td data-label="Ações">
                            <div class="action-buttons">
                                <?php if ($can_edit): ?>
                                    <a href="editar.php?id=<?php echo $linha['id']; ?>" class="action-btn action-edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                <?php endif; ?>

                                <?php if ($can_edit && $linha['status'] === 'disponivel'): ?>
                                    <a href="vincular.php?id=<?php echo $linha['id']; ?>" class="action-btn action-equipments" title="Vincular a Colaborador">
                                        <i class="fas fa-user-plus"></i>
                                    </a>
                                <?php elseif ($can_edit && $linha['status'] === 'alocado'): ?>
                                    <a href="desvincular.php?id=<?php echo $linha['id']; ?>" class="action-btn action-return" title="Desvincular" onclick="return confirm('Desvincular esta linha do colaborador? O centro de custo será alterado para 11001.')">
                                        <i class="fas fa-user-slash"></i>
                                    </a>
                                <?php endif; ?>

                                <!-- Botão Indisponível (apenas para linhas Disponíveis ou Alocadas) -->
                                <?php if ($can_edit && in_array($linha['status'], ['disponivel', 'alocado'])): ?>
                                    <form method="POST" style="display: inline-block;" 
                                          onsubmit="return confirm('Tem certeza que deseja marcar esta linha como INDISPONÍVEL?')">
                                        <input type="hidden" name="id" value="<?php echo $linha['id']; ?>">
                                        <button type="submit" name="indisponivel" class="action-btn action-indisponivel" title="Marcar como Indisponível">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($can_edit): ?>
                                    <a href="excluir.php?id=<?php echo $linha['id']; ?>" class="action-btn action-delete" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta linha?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="page-footer">
        <div class="total-count">
            <i class="fas fa-chart-line"></i>
            <span>Total: <strong><?php echo $totalFiltrado; ?></strong> linha(s)</span>
        </div>
        <?php if ($busca): ?>
            <div class="filter-summary">
                <small>Buscando por: <strong><?php echo htmlspecialchars($busca); ?></strong></small>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3><i class="fas fa-laptop-house"></i>Gestão de Linhas</h3>
            <p>Controle de colaboradores e equipamentos</p>
        </div>
        <div class="footer-section">
            <h3>Links Rápidos</h3>
            <ul class="footer-links">
                <li><a href="../index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="../colaboradores/index.php"><i class="fas fa-users"></i> Colaboradores</a></li>
                <li><a href="../equipamentos/index.php"><i class="fas fa-laptop"></i> Equipamentos</a></li>
                <li><a href="index.php"><i class="fas fa-phone"></i> Linhas</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Estatísticas</h3>
            <?php
            $total_equipamentos = count(lerArquivoJSON('../data/equipamentos.json'));
            ?>
            <div class="footer-stats">
                <div class="footer-stat"><span class="stat-number"><?php echo $totalLinhas; ?></span><span class="stat-label">Linhas</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo $total_equipamentos; ?></span><span class="stat-label">Equipamentos</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo count($colaboradores); ?></span><span class="stat-label">Colaboradores</span></div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
        <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
</footer>

<script src="../js/script.js"></script>
<script>
    // Fechar alerta após 5 segundos
    setTimeout(function() {
        const alert = document.querySelector('.global-alert');
        if (alert) {
            alert.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }
    }, 5000);
</script>
</body>
</html>