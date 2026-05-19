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
$can_edit = ($is_admin || $usuario_nivel === 'user'); // Admin e usuário comum podem editar

$equipamentos = lerArquivoJSON('../data/equipamentos.json');
$colaboradores = lerArquivoJSON('../data/colaboradores.json');

// Criar mapa de colaboradores para busca rápida
$mapaColaboradores = [];
foreach ($colaboradores as $colaborador) {
    $mapaColaboradores[$colaborador['id']] = $colaborador;
}

// Obter todas as caixas únicas dos equipamentos
$caixas = [];
foreach ($equipamentos as $equipamento) {
    if (!empty($equipamento['caixa'] ?? '')) {
        $caixas[$equipamento['caixa']] = $equipamento['caixa'];
    }
}
sort($caixas);

// Estatísticas GLOBAIS
$totalEquipamentos = count($equipamentos);
$totalEstoque = count(array_filter($equipamentos, function($e) { return $e['status'] === 'estoque'; }));
$totalAlocados = count(array_filter($equipamentos, function($e) { return $e['status'] === 'alocado'; }));
$totalEmprestados = count(array_filter($equipamentos, function($e) { return $e['status'] === 'emprestado'; }));
$totalManutencao = count(array_filter($equipamentos, function($e) { return $e['status'] === 'manutencao'; }));
$totalForaUso = count(array_filter($equipamentos, function($e) { return $e['status'] === 'fora_uso'; }));

// Aplicar filtros
$filtro = $_GET['filtro'] ?? 'todos';
$tipo = $_GET['tipo'] ?? 'todos';
$status = $_GET['status'] ?? 'todos';
$colaboradorId = $_GET['colaborador'] ?? null;
$centro_custo = $_GET['centro_custo'] ?? 'todos';
$caixa_id = $_GET['caixa'] ?? '';

// Extrair centros de custo únicos
$centrosCustoUnicos = [];
foreach ($equipamentos as $equipamento) {
    if (!empty($equipamento['centro_custo'])) {
        $centrosCustoUnicos[$equipamento['centro_custo']] = $equipamento['centro_custo'];
    }
}
sort($centrosCustoUnicos);

// Filtrar equipamentos
$equipamentosFiltrados = $equipamentos;

if ($colaboradorId) {
    $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($equipamento) use ($colaboradorId) {
        return $equipamento['colaborador_id'] == $colaboradorId;
    });
} else {
    if ($filtro === 'estoque') {
        $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($e) { return $e['status'] === 'estoque'; });
    } elseif ($filtro === 'alocados') {
        $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($e) { return $e['status'] === 'alocado'; });
    } elseif ($filtro === 'emprestados') {
        $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($e) { return $e['status'] === 'emprestado'; });
    } elseif ($filtro === 'manutencao') {
        $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($e) { return $e['status'] === 'manutencao'; });
    } elseif ($filtro === 'fora_uso') {
        $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($e) { return $e['status'] === 'fora_uso'; });
    }
}

if ($tipo !== 'todos') {
    $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($e) use ($tipo) {
        return $e['tipo'] === $tipo;
    });
}

if ($status !== 'todos' && !$colaboradorId && $filtro === 'todos') {
    $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($e) use ($status) {
        return $e['status'] === $status;
    });
}

if ($centro_custo !== 'todos') {
    $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($e) use ($centro_custo) {
        return $e['centro_custo'] === $centro_custo;
    });
}

if (!empty($caixa_id)) {
    $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($e) use ($caixa_id) {
        return ($e['caixa'] ?? '') === $caixa_id;
    });
}

$busca = $_GET['busca'] ?? '';
if ($busca) {
    $buscaLower = strtolower($busca);
    $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($e) use ($buscaLower) {
        return stripos($e['patrimonio'], $buscaLower) !== false ||
                stripos($e['serial'] ?? '', $buscaLower) !== false ||
                stripos($e['hostname'] ?? '', $buscaLower) !== false ||
                stripos($e['marca'], $buscaLower) !== false ||
                stripos($e['modelo'], $buscaLower) !== false ||
                stripos(getTipoTexto($e['tipo']), $buscaLower) !== false ||
                stripos($e['caixa'] ?? '', $buscaLower) !== false ||
                stripos($e['centro_custo'], $buscaLower) !== false;
    });
}

$equipamentosFiltrados = array_values($equipamentosFiltrados);

// Estatísticas filtradas
$totalFiltrado = count($equipamentosFiltrados);
$estoqueFiltrado = count(array_filter($equipamentosFiltrados, function($e) { return $e['status'] === 'estoque'; }));
$alocadosFiltrado = count(array_filter($equipamentosFiltrados, function($e) { return $e['status'] === 'alocado'; }));
$emprestadosFiltrado = count(array_filter($equipamentosFiltrados, function($e) { return $e['status'] === 'emprestado'; }));
$manutencaoFiltrado = count(array_filter($equipamentosFiltrados, function($e) { return $e['status'] === 'manutencao'; }));
$foraUsoFiltrado = count(array_filter($equipamentosFiltrados, function($e) { return $e['status'] === 'fora_uso'; }));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipamentos - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/equipamentos/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../img/favicon/favicon.png">
</head>
<body>
<!-- ==================== HEADER ==================== -->
<header class="header">
    <div class="header-content">
        <div class="logo">
            <a href="../index.php">
                <i class="fas fa-laptop-house"></i>
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
            <li class="nav-item">
                <a href="../index.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../colaboradores/index.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Colaboradores</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php" class="nav-link active">
                    <i class="fas fa-laptop"></i>
                    <span>Equipamentos</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../linhas/index.php" class="nav-link">
                    <i class="fas fa-phone"></i>
                    <span>Linhas</span>
                </a>
            </li>
            <?php if ($is_admin): ?>
                <li class="nav-item">
                    <a href="../usuarios/index.php" class="nav-link">
                        <i class="fas fa-user-cog"></i>
                        <span>Usuários</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<!-- ==================== CONTEÚDO PRINCIPAL ==================== -->
<main class="main-container">

    <!-- ==================== HEADER DA PÁGINA COM AÇÕES ==================== -->
    <div class="page-header">
        <div class="page-title-section">
            <h1><i class="fas fa-laptop"></i> Equipamentos</h1>
            <p class="page-subtitle">Gerencie todos os equipamentos da sua organização</p>
        </div>
        <?php if ($can_edit): ?>
            <div class="page-actions">
                <div class="action-group">
                    <a href="alocar_por_chamado.php" class="btn btn-outline">
                        <i class="fas fa-qrcode"></i> Alocar por Chamado
                    </a>
                    <a href="atribuir_caixa.php" class="btn btn-outline">
                        <i class="fas fa-boxes"></i>
                        <span>Atribuir Caixa</span>
                    </a>
                    <a href="alocar_multiplos.php" class="btn btn-outline">
                        <i class="fas fa-layer-group"></i>
                        <span>Alocar Múltiplos</span>
                    </a>
                </div>
                <a href="adicionar.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span>Adicionar Equipamento</span>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- ==================== FILTROS RÁPIDOS ==================== -->
    <div class="filter-tabs">
        <a href="?filtro=todos" class="filter-tab <?php echo $filtro == 'todos' ? 'active' : ''; ?>">
            <i class="fas fa-list"></i> Todos (<?php echo $totalEquipamentos; ?>)
        </a>
        <a href="?filtro=estoque" class="filter-tab <?php echo $filtro == 'estoque' ? 'active' : ''; ?>">
            <i class="fas fa-warehouse"></i> Estoque (<?php echo $totalEstoque; ?>)
        </a>
        <a href="?filtro=alocados" class="filter-tab <?php echo $filtro == 'alocados' ? 'active' : ''; ?>">
            <i class="fas fa-user-check"></i> Alocados (<?php echo $totalAlocados; ?>)
        </a>
        <a href="?filtro=emprestados" class="filter-tab <?php echo $filtro == 'emprestados' ? 'active' : ''; ?>">
            <i class="fas fa-handshake"></i> Emprestados (<?php echo $totalEmprestados; ?>)
        </a>
        <a href="?filtro=manutencao" class="filter-tab <?php echo $filtro == 'manutencao' ? 'active' : ''; ?>">
            <i class="fas fa-tools"></i> Manutenção (<?php echo $totalManutencao; ?>)
        </a>
        <a href="?filtro=fora_uso" class="filter-tab <?php echo $filtro == 'fora_uso' ? 'active' : ''; ?>">
            <i class="fas fa-times-circle"></i> Fora de Uso (<?php echo $totalForaUso; ?>)
        </a>
    </div>

    <!-- ==================== FILTROS AVANÇADOS ==================== -->
    <div class="filter-card">
        <form method="GET" class="filter-form">
            <div class="filter-grid">
                <div class="filter-group">
                    <label><i class="fas fa-tag"></i> Tipo</label>
                    <select name="tipo" class="form-control">
                        <option value="todos">Todos os Tipos</option>
                        <?php foreach (getTiposEquipamentos() as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php echo $tipo == $key ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label><i class="fas fa-box"></i> Caixa</label>
                    <select name="caixa" class="form-control">
                        <option value="">Todas as Caixas</option>
                        <?php foreach ($caixas as $caixa): ?>
                            <option value="<?php echo htmlspecialchars($caixa); ?>" <?php echo $caixa_id == $caixa ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($caixa); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label><i class="fas fa-dollar-sign"></i> Centro de Custo</label>
                    <select name="centro_custo" class="form-control">
                        <option value="todos">Todos os Centros</option>
                        <?php foreach ($centrosCustoUnicos as $centro): ?>
                            <option value="<?php echo htmlspecialchars($centro); ?>" <?php echo $centro_custo == $centro ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($centro); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label><i class="fas fa-info-circle"></i> Status</label>
                    <select name="status" class="form-control">
                        <option value="todos">Todos os Status</option>
                        <option value="estoque" <?php echo $status == 'estoque' ? 'selected' : ''; ?>>Em Estoque</option>
                        <option value="alocado" <?php echo $status == 'alocado' ? 'selected' : ''; ?>>Alocado</option>
                        <option value="emprestado" <?php echo $status == 'emprestado' ? 'selected' : ''; ?>>Emprestado</option>
                        <option value="manutencao" <?php echo $status == 'manutencao' ? 'selected' : ''; ?>>Em Manutenção</option>
                        <option value="fora_uso" <?php echo $status == 'fora_uso' ? 'selected' : ''; ?>>Fora de Uso</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label><i class="fas fa-user"></i> Colaborador</label>
                    <select name="colaborador" class="form-control">
                        <option value="">Todos os Colaboradores</option>
                        <?php foreach ($colaboradores as $colaborador): ?>
                            <option value="<?php echo $colaborador['id']; ?>" <?php echo $colaboradorId == $colaborador['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($colaborador['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group full-width">
                    <label><i class="fas fa-search"></i> Buscar</label>
                    <input type="text" name="busca" class="form-control"
                           placeholder="Patrimônio, serial, hostname, marca, modelo, centro de custo..."
                           value="<?php echo htmlspecialchars($busca); ?>">
                </div>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Aplicar Filtros
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpar
                </a>
            </div>

            <?php if ($filtro !== 'todos'): ?>
                <input type="hidden" name="filtro" value="<?php echo htmlspecialchars($filtro); ?>">
            <?php endif; ?>
        </form>
    </div>

    <!-- ==================== CARDS DE ESTATÍSTICAS ==================== -->
    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-icon"><i class="fas fa-warehouse"></i></div>
            <div class="stat-content">
                <h3>Em Estoque</h3>
                <p class="stat-number"><?php echo $estoqueFiltrado; ?></p>
                <small><?php echo $totalEquipamentos > 0 ? number_format(($estoqueFiltrado/$totalEquipamentos)*100, 1) : '0'; ?>%</small>
            </div>
        </div>
        <div class="stat-card stat-success">
            <div class="stat-icon"><i class="fas fa-user-check"></i></div>
            <div class="stat-content">
                <h3>Alocados</h3>
                <p class="stat-number"><?php echo $alocadosFiltrado; ?></p>
                <small><?php echo $totalEquipamentos > 0 ? number_format(($alocadosFiltrado/$totalEquipamentos)*100, 1) : '0'; ?>%</small>
            </div>
        </div>
        <div class="stat-card stat-info">
            <div class="stat-icon"><i class="fas fa-handshake"></i></div>
            <div class="stat-content">
                <h3>Emprestados</h3>
                <p class="stat-number"><?php echo $emprestadosFiltrado; ?></p>
                <small><?php echo $totalEquipamentos > 0 ? number_format(($emprestadosFiltrado/$totalEquipamentos)*100, 1) : '0'; ?>%</small>
            </div>
        </div>
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="fas fa-tools"></i></div>
            <div class="stat-content">
                <h3>Manutenção</h3>
                <p class="stat-number"><?php echo $manutencaoFiltrado; ?></p>
                <small><?php echo $totalEquipamentos > 0 ? number_format(($manutencaoFiltrado/$totalEquipamentos)*100, 1) : '0'; ?>%</small>
            </div>
        </div>
        <div class="stat-card stat-danger">
            <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
            <div class="stat-content">
                <h3>Fora de Uso</h3>
                <p class="stat-number"><?php echo $foraUsoFiltrado; ?></p>
                <small><?php echo $totalEquipamentos > 0 ? number_format(($foraUsoFiltrado/$totalEquipamentos)*100, 1) : '0'; ?>%</small>
            </div>
        </div>
    </div>

    <!-- ==================== TABELA DE EQUIPAMENTOS ==================== -->
    <div class="table-container">
        <table class="data-table">
            <thead>
            <tr>
                <th>Tipo</th>
                <th>Patrimônio</th>
                <th>Caixa</th>
                <th>Hostname</th>
                <th>Marca/Modelo</th>
                <th>Nº Série</th>
                <th>Centro de Custo</th>
                <th>Status</th>
                <th>Colaborador</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($equipamentosFiltrados)): ?>
            <tr>
                <td colspan="10" class="empty-state">
                    <i class="fas fa-search"></i>
                    <p>Nenhum equipamento encontrado</p>
                    <small>Tente ajustar os filtros ou a busca</small>
                    <a href="index.php" class="btn btn-secondary" style="margin-top: 15px;">Limpar filtros</a>
                </td>
        </table>
        <?php else: ?>
            <?php foreach ($equipamentosFiltrados as $equipamento):
                $statusClasses = [
                        'estoque' => 'status-ativo',
                        'alocado' => 'status-inativo',
                        'emprestado' => 'status-info',
                        'manutencao' => 'status-warning',
                        'fora_uso' => 'status-danger'
                ];
                $statusClass = $statusClasses[$equipamento['status']] ?? 'status-ativo';
                $colaboradorNome = 'N/A';
                if ($equipamento['colaborador_id'] && isset($mapaColaboradores[$equipamento['colaborador_id']])) {
                    $colaboradorNome = $mapaColaboradores[$equipamento['colaborador_id']]['nome'];
                }

                // Exibir hostname com destaque especial para notebooks
                $hostnameDisplay = '---';
                if (!empty($equipamento['hostname'])) {
                    $hostnameDisplay = htmlspecialchars($equipamento['hostname']);
                    if ($equipamento['tipo'] === 'notebook') {
                        $hostnameDisplay = '<span class="hostname-badge hostname-notebook"><i class="fas fa-laptop"></i> ' . $hostnameDisplay . '</span>';
                    } else {
                        $hostnameDisplay = '<span class="hostname-badge"><i class="fas fa-network-wired"></i> ' . $hostnameDisplay . '</span>';
                    }
                }
                ?>
                <tr>
                    <td data-label="Tipo">
                                <span class="tipo-badge">
                                    <i class="fas fa-<?php echo getIconByType($equipamento['tipo']); ?>"></i>
                                    <?php echo getTipoTexto($equipamento['tipo']); ?>
                                </span>
                    </td>
                    <td data-label="Patrimônio"><strong><?php echo htmlspecialchars($equipamento['patrimonio']); ?></strong></td>
                    <td data-label="Caixa"><?php echo !empty($equipamento['caixa']) ? htmlspecialchars($equipamento['caixa']) : '---'; ?></td>
                    <td data-label="Hostname"><?php echo $hostnameDisplay; ?></td>
                    <td data-label="Marca/Modelo"><?php echo htmlspecialchars($equipamento['marca'] . ' ' . $equipamento['modelo']); ?></td>
                    <td data-label="Nº Série"><?php echo !empty($equipamento['serial']) ? htmlspecialchars($equipamento['serial']) : '---'; ?></td>
                    <td data-label="Centro de Custo"><?php echo htmlspecialchars($equipamento['centro_custo']); ?></td>
                    <td data-label="Status">
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <i class="fas fa-<?php echo getIconByStatus($equipamento['status']); ?>"></i>
                                    <?php echo getStatusTexto($equipamento['status']); ?>
                                </span>
                    </td>
                    <td data-label="Colaborador"><?php echo htmlspecialchars($colaboradorNome); ?></td>
                    <td data-label="Ações">
                        <div class="action-buttons">
                            <?php if ($can_edit): ?>
                                <a href="editar.php?id=<?php echo $equipamento['id']; ?>" class="action-btn action-edit" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            <?php endif; ?>

                            <?php if ($can_edit && $equipamento['status'] == 'estoque'): ?>
                                <a href="atribuir.php?id=<?php echo $equipamento['id']; ?>" class="action-btn action-equipments" title="Atribuir">
                                    <i class="fas fa-user-check"></i>
                                </a>
                            <?php endif; ?>

                            <?php if ($can_edit && in_array($equipamento['status'], ['alocado', 'emprestado'])): ?>
                                <a href="devolver.php?id=<?php echo $equipamento['id']; ?>" class="action-btn action-return" title="Devolver" onclick="return confirm('Devolver este equipamento para o estoque?')">
                                    <i class="fas fa-undo"></i>
                                </a>
                            <?php endif; ?>

                            <?php if ($can_edit && $equipamento['status'] == 'manutencao'): ?>
                                <a href="finalizar_manutencao.php?id=<?php echo $equipamento['id']; ?>" class="action-btn action-success" title="Finalizar Manutenção" onclick="return confirm('Finalizar manutenção deste equipamento?')">
                                    <i class="fas fa-check"></i>
                                </a>
                            <?php endif; ?>

                            <?php if ($can_edit && in_array($equipamento['status'], ['alocado', 'emprestado', 'estoque'])): ?>
                                <a href="enviar_manutencao.php?id=<?php echo $equipamento['id']; ?>" class="action-btn action-warning" title="Enviar para Manutenção" onclick="return confirm('Enviar este equipamento para manutenção?')">
                                    <i class="fas fa-tools"></i>
                                </a>
                            <?php endif; ?>

                            <?php if ($can_edit && $equipamento['status'] !== 'fora_uso'): ?>
                                <a href="marcar_fora_uso.php?id=<?php echo $equipamento['id']; ?>" class="action-btn action-delete" title="Marcar Fora de Uso" onclick="return confirm('Marcar este equipamento como fora de uso? Esta ação é irreversível.')">
                                    <i class="fas fa-times-circle"></i>
                                </a>
                            <?php endif; ?>

                            <!-- Botão Visualizar - visível para todos os níveis -->
                            <button type="button" class="action-btn action-view" onclick="showEquipmentDetails(<?php echo htmlspecialchars(json_encode($equipamento)); ?>)" title="Ver Detalhes">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
        </table>
    </div>

    <!-- ==================== FOOTER DA PÁGINA ==================== -->
    <div class="page-footer">
        <div class="total-count">
            <i class="fas fa-chart-line"></i>
            <span>Resultados: <strong><?php echo $totalFiltrado; ?></strong> de <?php echo $totalEquipamentos; ?> equipamentos</span>
        </div>
        <div class="filter-summary">
            <small>
                <?php
                $filters = [];
                if ($colaboradorId && isset($mapaColaboradores[$colaboradorId])) $filters[] = 'Colaborador: ' . $mapaColaboradores[$colaboradorId]['nome'];
                elseif ($filtro !== 'todos') $filters[] = 'Status: ' . getStatusTexto($filtro);
                elseif ($status !== 'todos') $filters[] = 'Status: ' . getStatusTexto($status);
                if ($tipo !== 'todos') $filters[] = 'Tipo: ' . getTipoTexto($tipo);
                if ($centro_custo !== 'todos') $filters[] = 'CC: ' . $centro_custo;
                if (!empty($caixa_id)) $filters[] = 'Caixa: ' . $caixa_id;
                if ($busca) $filters[] = 'Busca: "' . htmlspecialchars($busca) . '"';
                echo implode(' | ', $filters) ?: 'Todos os equipamentos';
                ?>
            </small>
        </div>
    </div>
</main>

<!-- ==================== FOOTER ==================== -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3><i class="fas fa-laptop-house"></i> Sistema de Gestão</h3>
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
                <div class="footer-stat">
                    <span class="stat-number"><?php echo $totalEquipamentos; ?></span>
                    <span class="stat-label">Equipamentos</span>
                </div>
                <div class="footer-stat">
                    <span class="stat-number"><?php echo $totalEstoque; ?></span>
                    <span class="stat-label">Em Estoque</span>
                </div>
                <div class="footer-stat">
                    <span class="stat-number"><?php echo $totalAlocados; ?></span>
                    <span class="stat-label">Alocados</span>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
        <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
</footer>

<!-- ==================== MODAL DE DETALHES ==================== -->
<div id="equipmentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-laptop"></i> Detalhes do Equipamento</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody"></div>
    </div>
</div>

<style>
    /* Estilos para o hostname na tabela */
    .hostname-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 8px;
        background: var(--gray-100);
        border-radius: var(--radius-md);
        font-size: 0.75rem;
        font-family: monospace;
        color: var(--gray-700);
    }

    .hostname-notebook {
        background: rgba(107, 62, 143, 0.1);
        color: var(--grape);
        font-weight: 500;
    }

    .hostname-notebook i {
        color: var(--grape);
    }
</style>

<script>
    // Funções para o modal
    function showEquipmentDetails(equipamento) {
        function formatarData(dataString) {
            if (!dataString) return '---';
            const data = new Date(dataString);
            return data.toLocaleDateString('pt-BR');
        }

        let historico = '';
        if (equipamento.historico_manutencao && equipamento.historico_manutencao.length > 0) {
            historico = '<h4><i class="fas fa-history"></i> Histórico de Manutenção</h4><div class="historico-list">';
            equipamento.historico_manutencao.forEach(item => {
                historico += `
                        <div class="historico-item">
                            <strong>${formatarData(item.data_envio)}</strong>
                            ${item.data_retorno ? ` até ${formatarData(item.data_retorno)}` : '(em andamento)'}
                            <br><small>Problema: ${item.problema || '---'}</small>
                            ${item.resultado ? `<br><small>Resultado: ${item.resultado}</small>` : ''}
                        </div>
                    `;
            });
            historico += '</div>';
        } else {
            historico = '<p><i class="fas fa-info-circle"></i> Nenhuma manutenção registrada.</p>';
        }

        const hostnameHtml = equipamento.hostname ?
            `<div class="detail-item"><strong>Hostname:</strong> <span class="hostname-badge ${equipamento.tipo === 'notebook' ? 'hostname-notebook' : ''}"><i class="fas fa-${equipamento.tipo === 'notebook' ? 'laptop' : 'network-wired'}"></i> ${equipamento.hostname}</span></div>` :
            '';

        const content = `
                <div class="equipment-details">
                    <div class="detail-row">
                        <div class="detail-item"><strong>Patrimônio:</strong> ${equipamento.patrimonio}</div>
                        <div class="detail-item"><strong>Status:</strong> <span class="status-badge">${getStatusText(equipamento.status)}</span></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-item"><strong>Tipo:</strong> ${getTipoText(equipamento.tipo)}</div>
                        <div class="detail-item"><strong>Marca/Modelo:</strong> ${equipamento.marca || '---'} ${equipamento.modelo || ''}</div>
                    </div>
                    <div class="detail-row">
                        ${hostnameHtml}
                        <div class="detail-item"><strong>Nº Série:</strong> ${equipamento.serial || '---'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-item"><strong>Centro de Custo:</strong> ${equipamento.centro_custo || '---'}</div>
                        <div class="detail-item"><strong>Caixa:</strong> ${equipamento.caixa || '---'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-item"><strong>Observações:</strong></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-item full-width">${equipamento.observacoes || 'Nenhuma observação registrada.'}</div>
                    </div>
                    ${historico}
                </div>
            `;
        document.getElementById('modalBody').innerHTML = content;
        document.getElementById('equipmentModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('equipmentModal').style.display = 'none';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('equipmentModal');
        if (event.target == modal) modal.style.display = 'none';
    }

    function getStatusText(status) {
        const statusMap = {
            'estoque': 'Em Estoque',
            'alocado': 'Alocado',
            'emprestado': 'Emprestado',
            'manutencao': 'Em Manutenção',
            'fora_uso': 'Fora de Uso'
        };
        return statusMap[status] || status;
    }

    function getTipoText(tipo) {
        const tipoMap = <?php echo json_encode(getTiposEquipamentos()); ?>;
        return tipoMap[tipo] || tipo;
    }

    <?php if (isset($mapaColaboradores)): ?>
    window.mapaColaboradores = <?php echo json_encode($mapaColaboradores); ?>;
    <?php endif; ?>
</script>
</body>
</html>