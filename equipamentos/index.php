<?php
session_start();
require_once "../includes/funcoes.php";

// Verificar se o usuário está logado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../login.php");
    exit();
}

// Verificar nível do usuário
$usuario_nivel = $_SESSION["usuario_nivel"] ?? "user";
$is_admin = $usuario_nivel === "admin";
$is_view = $usuario_nivel === "view";
$can_edit = $is_admin || $usuario_nivel === "user";

// ============================================
// CARREGAR DADOS DOS DIFERENTES BANCOS
// ============================================
$equipamentosEstoque = carregarEquipamentosPorStatus('estoque');
$equipamentosAlocados = carregarEquipamentosPorStatus('alocado');
$equipamentosEmprestados = carregarEquipamentosPorStatus('emprestado');
$equipamentosManutencao = carregarEquipamentosPorStatus('manutencao');
$equipamentosForaUso = carregarEquipamentosPorStatus('fora_uso');

// Combinar todos os equipamentos para estatísticas
$todosEquipamentos = array_merge(
    $equipamentosEstoque,
    $equipamentosAlocados,
    $equipamentosEmprestados,
    $equipamentosManutencao,
    $equipamentosForaUso
);

// Carregar colaboradores
$colaboradores = lerArquivoJSON("../data/colaboradores/ativos.json");
if ($colaboradores === false) $colaboradores = [];

// Criar mapa de colaboradores
$mapaColaboradores = [];
foreach ($colaboradores as $colaborador) {
    $mapaColaboradores[$colaborador["id"]] = $colaborador;
}

// ============================================
// ESTATÍSTICAS
// ============================================
$totalEquipamentos = count($todosEquipamentos);
$totalEstoque = count($equipamentosEstoque);
$totalAlocados = count($equipamentosAlocados);
$totalEmprestados = count($equipamentosEmprestados);
$totalManutencao = count($equipamentosManutencao);
$totalForaUso = count($equipamentosForaUso);

// Calcular centros de custo únicos
$centrosCustoUnicos = [];
foreach ($todosEquipamentos as $equipamento) {
    if (!empty($equipamento["centro_custo"])) {
        $centrosCustoUnicos[$equipamento["centro_custo"]] = $equipamento["centro_custo"];
    }
}
sort($centrosCustoUnicos);

// ============================================
// APLICAR FILTROS
// ============================================
$filtro = $_GET["filtro"] ?? "todos";
$tipo = $_GET["tipo"] ?? "todos";
$status = $_GET["status"] ?? "todos";
$colaboradorId = $_GET["colaborador"] ?? null;
$centro_custo = $_GET["centro_custo"] ?? "todos";
$busca = $_GET["busca"] ?? "";

// Selecionar a fonte de dados baseada no filtro principal
if ($filtro === "estoque") {
    $equipamentosFonte = $equipamentosEstoque;
} elseif ($filtro === "alocados") {
    $equipamentosFonte = $equipamentosAlocados;
} elseif ($filtro === "emprestados") {
    $equipamentosFonte = $equipamentosEmprestados;
} elseif ($filtro === "manutencao") {
    $equipamentosFonte = $equipamentosManutencao;
} elseif ($filtro === "fora_uso") {
    $equipamentosFonte = $equipamentosForaUso;
} else {
    $equipamentosFonte = $todosEquipamentos;
}

$equipamentosFiltrados = $equipamentosFonte;

// Filtrar por colaborador
if ($colaboradorId) {
    $equipamentosFiltrados = array_filter($equipamentosFiltrados, function ($equipamento) use ($colaboradorId) {
        return ($equipamento["colaborador_id"] ?? null) == $colaboradorId;
    });
}

// Filtrar por tipo
if ($tipo !== "todos") {
    $equipamentosFiltrados = array_filter($equipamentosFiltrados, function ($e) use ($tipo) {
        return ($e["tipo"] ?? '') === $tipo;
    });
}

// Filtrar por status
if ($status !== "todos" && $filtro === "todos" && !$colaboradorId) {
    $equipamentosFiltrados = array_filter($equipamentosFiltrados, function ($e) use ($status) {
        return ($e["status"] ?? '') === $status;
    });
}

// Filtrar por centro de custo
if ($centro_custo !== "todos") {
    $equipamentosFiltrados = array_filter($equipamentosFiltrados, function ($e) use ($centro_custo) {
        return ($e["centro_custo"] ?? '') === $centro_custo;
    });
}

// Filtrar por busca
if ($busca) {
    $buscaLower = strtolower($busca);
    $equipamentosFiltrados = array_filter($equipamentosFiltrados, function ($e) use ($buscaLower) {
        return stripos($e["patrimonio"] ?? '', $buscaLower) !== false ||
            stripos($e["serial"] ?? '', $buscaLower) !== false ||
            stripos($e["hostname"] ?? '', $buscaLower) !== false ||
            stripos($e["marca"] ?? '', $buscaLower) !== false ||
            stripos($e["modelo"] ?? '', $buscaLower) !== false ||
            stripos(getTipoTexto($e["tipo"] ?? ''), $buscaLower) !== false ||
            stripos($e["centro_custo"] ?? '', $buscaLower) !== false;
    });
}

$equipamentosFiltrados = array_values($equipamentosFiltrados);
$totalFiltrado = count($equipamentosFiltrados);
?>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipamentos - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/equipamentos/index.css">
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
                <i class="fas fa-laptop-house"></i>
                <h1>Gestão de Equipamentos</h1>
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
            <?php if ($is_admin): ?>
                <li class="nav-item"><a href="../termos/index.php" class="nav-link"><i class="fas fa-file-contract"></i><span>Termos</span></a></li>
                <li class="nav-item"><a href="../usuarios/index.php" class="nav-link"><i class="fas fa-user-cog"></i><span>Usuários</span></a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<!-- ==================== CONTEÚDO PRINCIPAL ==================== -->
<main class="main-container">

    <!-- HEADER DA PÁGINA -->
    <div class="page-header">
        <div class="page-title-section">
            <h1><i class="fas fa-laptop"></i> Equipamentos</h1>
            <p class="page-subtitle">Gerencie todos os equipamentos da sua organização</p>
        </div>
        <?php if ($can_edit): ?>
            <div class="page-actions">
                <div class="action-group">
                    <a href="alocar_multiplos.php" class="btn btn-outline">
                        <i class="fas fa-layer-group"></i>
                        <span>Alocar Múltiplos</span>
                    </a>
                </div>
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="addEquipmentDropdown">
                        <i class="fas fa-plus"></i>
                        <span>Adicionar Equipamento</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="adicionar.php?tipo=desktop"><i class="fas fa-desktop"></i> Desktop</a>
                        <a class="dropdown-item" href="adicionar.php?tipo=notebook"><i class="fas fa-laptop"></i> Notebook</a>
                        <a class="dropdown-item" href="adicionar.php?tipo=monitor"><i class="fas fa-tv"></i> Monitor</a>
                        <a class="dropdown-item" href="adicionar.php?tipo=teclado"><i class="fas fa-keyboard"></i> Teclado</a>
                        <a class="dropdown-item" href="adicionar.php?tipo=mouse"><i class="fas fa-mouse"></i> Mouse</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="adicionar.php?tipo=celular"><i class="fas fa-mobile-alt"></i> Celular</a>
                        <a class="dropdown-item" href="adicionar.php?tipo=suporte"><i class="fas fa-toolbox"></i> Suporte de Notebook</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="adicionar.php"><i class="fas fa-plus-circle"></i> Outros...</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- FILTROS RÁPIDOS -->
    <div class="filter-tabs">
        <a href="?filtro=todos" class="filter-tab <?php echo $filtro == "todos" ? "active" : ""; ?>">
            <i class="fas fa-list"></i> Todos (<?php echo $totalEquipamentos; ?>)
        </a>
        <a href="?filtro=estoque" class="filter-tab <?php echo $filtro == "estoque" ? "active" : ""; ?>">
            <i class="fas fa-warehouse"></i> Estoque (<?php echo $totalEstoque; ?>)
        </a>
        <a href="?filtro=alocados" class="filter-tab <?php echo $filtro == "alocados" ? "active" : ""; ?>">
            <i class="fas fa-user-check"></i> Alocados (<?php echo $totalAlocados; ?>)
        </a>
        <a href="?filtro=emprestados" class="filter-tab <?php echo $filtro == "emprestados" ? "active" : ""; ?>">
            <i class="fas fa-handshake"></i> Emprestados (<?php echo $totalEmprestados; ?>)
        </a>
        <a href="?filtro=manutencao" class="filter-tab <?php echo $filtro == "manutencao" ? "active" : ""; ?>">
            <i class="fas fa-tools"></i> Manutenção (<?php echo $totalManutencao; ?>)
        </a>
        <a href="?filtro=fora_uso" class="filter-tab <?php echo $filtro == "fora_uso" ? "active" : ""; ?>">
            <i class="fas fa-times-circle"></i> Fora de Uso (<?php echo $totalForaUso; ?>)
        </a>
    </div>

    <!-- FILTROS AVANÇADOS -->
    <div class="filter-card">
        <form method="GET" class="filter-form">
            <div class="filter-grid">
                <div class="filter-group">
                    <label><i class="fas fa-tag"></i> Tipo</label>
                    <select name="tipo" class="form-control">
                        <option value="todos">Todos os Tipos</option>
                        <?php foreach (getTiposEquipamentos() as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php echo $tipo == $key ? "selected" : ""; ?>><?php echo $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-dollar-sign"></i> Centro de Custo</label>
                    <select name="centro_custo" class="form-control">
                        <option value="todos">Todos os Centros</option>
                        <?php foreach ($centrosCustoUnicos as $centro): ?>
                            <option value="<?php echo htmlspecialchars($centro); ?>" <?php echo $centro_custo == $centro ? "selected" : ""; ?>><?php echo htmlspecialchars($centro); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-info-circle"></i> Status</label>
                    <select name="status" class="form-control">
                        <option value="todos">Todos os Status</option>
                        <option value="estoque" <?php echo $status == "estoque" ? "selected" : ""; ?>>Em Estoque</option>
                        <option value="alocado" <?php echo $status == "alocado" ? "selected" : ""; ?>>Alocado</option>
                        <option value="emprestado" <?php echo $status == "emprestado" ? "selected" : ""; ?>>Emprestado</option>
                        <option value="manutencao" <?php echo $status == "manutencao" ? "selected" : ""; ?>>Em Manutenção</option>
                        <option value="fora_uso" <?php echo $status == "fora_uso" ? "selected" : ""; ?>>Fora de Uso</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-user"></i> Colaborador</label>
                    <select name="colaborador" class="form-control">
                        <option value="">Todos os Colaboradores</option>
                        <?php foreach ($colaboradores as $colaborador): ?>
                            <option value="<?php echo $colaborador["id"]; ?>" <?php echo $colaboradorId == $colaborador["id"] ? "selected" : ""; ?>><?php echo htmlspecialchars($colaborador["nome"]); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group full-width">
                    <label><i class="fas fa-search"></i> Buscar</label>
                    <input type="text" name="busca" class="form-control" placeholder="Patrimônio, serial, hostname, marca, modelo, centro de custo..." value="<?php echo htmlspecialchars($busca); ?>">
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Aplicar Filtros</button>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Limpar</a>
            </div>
            <?php if ($filtro !== "todos"): ?>
                <input type="hidden" name="filtro" value="<?php echo htmlspecialchars($filtro); ?>">
            <?php endif; ?>
        </form>
    </div>

    <!-- STATS CARDS -->
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon primary"><i class="fas fa-warehouse"></i></div><div class="stat-content"><h3>Em Estoque</h3><p class="stat-number"><?php echo $totalEstoque; ?></p></div></div>
        <div class="stat-card"><div class="stat-icon success"><i class="fas fa-user-check"></i></div><div class="stat-content"><h3>Alocados</h3><p class="stat-number"><?php echo $totalAlocados; ?></p></div></div>
        <div class="stat-card"><div class="stat-icon info"><i class="fas fa-handshake"></i></div><div class="stat-content"><h3>Emprestados</h3><p class="stat-number"><?php echo $totalEmprestados; ?></p></div></div>
        <div class="stat-card"><div class="stat-icon warning"><i class="fas fa-tools"></i></div><div class="stat-content"><h3>Manutenção</h3><p class="stat-number"><?php echo $totalManutencao; ?></p></div></div>
        <div class="stat-card"><div class="stat-icon danger"><i class="fas fa-times-circle"></i></div><div class="stat-content"><h3>Fora de Uso</h3><p class="stat-number"><?php echo $totalForaUso; ?></p></div></div>
    </div>

    <!-- TABELA DE EQUIPAMENTOS -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr><th>Tipo</th><th>Patrimônio</th><th>Hostname</th><th>Marca/Modelo</th><th>Nº Série</th><th>Centro de Custo</th><th>Status</th><th>Colaborador</th><th>Ações</th></tr>
            </thead>
            <tbody>
            <?php if (empty($equipamentosFiltrados)): ?>
                <tr><td colspan="9" class="empty-state"><i class="fas fa-search"></i><p>Nenhum equipamento encontrado</p><a href="index.php" class="btn btn-secondary">Limpar filtros</a></td></tr>
            <?php else: ?>
                <?php foreach ($equipamentosFiltrados as $equipamento):
                    $statusClasses = ["estoque" => "status-ativo", "alocado" => "status-inativo", "emprestado" => "status-info", "manutencao" => "status-warning", "fora_uso" => "status-danger"];
                    $statusClass = $statusClasses[$equipamento["status"]] ?? "status-ativo";
                    $colaboradorNome = ($equipamento["colaborador_id"] && isset($mapaColaboradores[$equipamento["colaborador_id"]])) ? $mapaColaboradores[$equipamento["colaborador_id"]]["nome"] : "N/A";
                    $hostnameDisplay = "---";
                    if (!empty($equipamento["hostname"])) {
                        $hostnameDisplay = htmlspecialchars($equipamento["hostname"]);
                        if ($equipamento["tipo"] === "notebook") {
                            $hostnameDisplay = '<span class="hostname-badge hostname-notebook"><i class="fas fa-laptop"></i> ' . $hostnameDisplay . '</span>';
                        } else {
                            $hostnameDisplay = '<span class="hostname-badge"><i class="fas fa-network-wired"></i> ' . $hostnameDisplay . '</span>';
                        }
                    }
                ?>
                <tr>
                    <td data-label="Tipo"><span class="tipo-badge"><i class="fas fa-<?php echo getIconByType($equipamento["tipo"]); ?>"></i> <?php echo getTipoTexto($equipamento["tipo"]); ?></span></td>
                    <td data-label="Patrimônio"><strong><?php echo htmlspecialchars($equipamento["patrimonio"]); ?></strong></td>
                    <td data-label="Hostname"><?php echo $hostnameDisplay; ?></td>
                    <td data-label="Marca/Modelo"><?php echo htmlspecialchars(($equipamento["marca"] ?? '') . ' ' . ($equipamento["modelo"] ?? '')); ?></td>
                    <td data-label="Nº Série"><?php echo !empty($equipamento["serial"]) ? htmlspecialchars($equipamento["serial"]) : "---"; ?></td>
                    <td data-label="Centro de Custo"><?php echo htmlspecialchars($equipamento["centro_custo"] ?? '---'); ?></td>
                    <td data-label="Status"><span class="status-badge <?php echo $statusClass; ?>"><i class="fas fa-<?php echo getIconByStatus($equipamento["status"]); ?>"></i> <?php echo getStatusTexto($equipamento["status"]); ?></span></td>
                    <td data-label="Colaborador"><?php echo htmlspecialchars($colaboradorNome); ?></td>
                    <td data-label="Ações">
                        <div class="action-buttons">
                            <?php if ($can_edit): ?>
                                <a href="editar.php?id=<?php echo $equipamento["id"]; ?>" class="action-btn action-edit" title="Editar"><i class="fas fa-edit"></i></a>
                            <?php endif; ?>
                            <?php if ($can_edit && $equipamento["status"] == "estoque"): ?>
                                <a href="atribuir.php?id=<?php echo $equipamento["id"]; ?>" class="action-btn action-equipments" title="Atribuir"><i class="fas fa-user-check"></i></a>
                            <?php endif; ?>
                            <?php if ($can_edit && in_array($equipamento["status"], ["alocado", "emprestado"])): ?>
                                <a href="devolver.php?id=<?php echo $equipamento["id"]; ?>" class="action-btn action-return" title="Devolver" onclick="return confirm('Devolver este equipamento para o estoque?')"><i class="fas fa-undo"></i></a>
                            <?php endif; ?>
                            <?php if ($can_edit && !in_array($equipamento["status"], ["manutencao", "fora_uso"])): ?>
                                <button onclick="enviarManutencao(<?php echo json_encode((string)$equipamento['id']); ?>)" class="action-btn action-warning" title="Enviar para Manutenção"><i class="fas fa-tools"></i></button>
                            <?php endif; ?>
                            <?php if ($can_edit && $equipamento["status"] == "manutencao"): ?>
                                <button onclick="retornarManutencao(<?php echo json_encode((string)$equipamento['id']); ?>)" class="action-btn action-success" title="Retornar da Manutenção"><i class="fas fa-arrow-left"></i></button>
                            <?php endif; ?>
                            <?php if ($can_edit && $equipamento["status"] != "fora_uso"): ?>
                                <button onclick="marcarForaUso(<?php echo json_encode((string)$equipamento['id']); ?>)" class="action-btn action-delete" title="Marcar Fora de Uso"><i class="fas fa-times-circle"></i></button>
                            <?php endif; ?>
                            <button type="button" class="action-btn action-view" onclick="showEquipmentDetails(<?php echo htmlspecialchars(json_encode($equipamento)); ?>)" title="Ver Detalhes"><i class="fas fa-eye"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- FOOTER DA PÁGINA -->
    <div class="page-footer">
        <div class="total-count">
            <i class="fas fa-chart-line"></i>
            <span>Resultados: <strong><?php echo $totalFiltrado; ?></strong> de <?php echo $totalEquipamentos; ?> equipamentos</span>
        </div>
    </div>

</main>

<!-- ==================== MODAL MANUTENÇÃO ==================== -->
<div id="modalManutencao" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-tools"></i> Enviar para Manutenção</h3>
            <button class="modal-close" onclick="closeModalManutencao()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Informe o problema do equipamento:</p>
            <textarea id="problemaManutencao" class="form-control" rows="3" placeholder="Descreva o problema..."></textarea>
            <input type="hidden" id="equipamentoIdManutencao" value="">
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeModalManutencao()">Cancelar</button>
                <button class="btn btn-warning" onclick="confirmarEnviarManutencao()">Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODAL FORA DE USO ==================== -->
<div id="modalForaUso" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-times-circle"></i> Marcar como Fora de Uso</h3>
            <button class="modal-close" onclick="closeModalForaUso()">&times;</button>
        </div>
        <div class="modal-body">
            <p><strong>Atenção!</strong> Esta ação é irreversível.</p>
            <p>Informe o motivo:</p>
            <textarea id="motivoForaUso" class="form-control" rows="3" placeholder="Descreva o motivo..."></textarea>
            <input type="hidden" id="equipamentoIdForaUso" value="">
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeModalForaUso()">Cancelar</button>
                <button class="btn btn-danger" onclick="confirmarForaUso()">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODAL DETALHES ==================== -->
<div id="equipmentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-laptop"></i> Detalhes do Equipamento</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody"></div>
    </div>
</div>

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
                <div class="footer-stat"><span class="stat-number"><?php echo $totalEquipamentos; ?></span><span class="stat-label">Total</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo $totalEstoque; ?></span><span class="stat-label">Estoque</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo $totalAlocados + $totalEmprestados; ?></span><span class="stat-label">Em Uso</span></div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>Sistema de Gestão &copy; <?php echo date("Y"); ?> - Todos os direitos reservados</p>
        <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
</footer>

<script>
    // ==================== FUNÇÕES PARA MANUTENÇÃO ====================
    let equipamentoIdManutencao = null;
    
    function enviarManutencao(id) {
        equipamentoIdManutencao = id;
        document.getElementById('equipamentoIdManutencao').value = id;
        document.getElementById('problemaManutencao').value = '';
        document.getElementById('modalManutencao').style.display = 'block';
    }
    
    function closeModalManutencao() {
        document.getElementById('modalManutencao').style.display = 'none';
        equipamentoIdManutencao = null;
    }
    
    function confirmarEnviarManutencao() {
    const problema = document.getElementById('problemaManutencao').value.trim();
    if (!problema) {
        alert('Por favor, descreva o problema do equipamento.');
        return;
    }
    
    const requestData = { 
        acao: 'enviar', 
        id: equipamentoIdManutencao,
        problema: problema 
    };
    
    console.log('Enviando requisição:', requestData); // Debug
    
    fetch('ajax/manutencao.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        console.log('Resposta recebida:', response); // Debug
        return response.json();
    })
    .then(data => {
        console.log('Dados:', data); // Debug
        if (data.success) {
            showSuccessMessage(data.message || 'Equipamento enviado para manutenção!');
            setTimeout(() => location.reload(), 1500);
        } else {
            alert('Erro: ' + (data.message || 'Erro ao processar solicitação'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao processar solicitação: ' + error.message);
    });
    closeModalManutencao();
}
    
    function retornarManutencao(id) {
        if (!confirm('Retornar este equipamento da manutenção? Ele voltará para o estoque.')) return;
        
        fetch('ajax/manutencao.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ acao: 'retornar', id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage(data.message || 'Equipamento retornado da manutenção!');
                setTimeout(() => location.reload(), 1500);
            } else {
                alert('Erro: ' + (data.message || 'Erro ao processar solicitação'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao processar solicitação');
        });
    }
    
    // ==================== FUNÇÕES PARA FORA DE USO ====================
    let equipamentoIdForaUso = null;
    
    function marcarForaUso(id) {
        equipamentoIdForaUso = id;
        document.getElementById('equipamentoIdForaUso').value = id;
        document.getElementById('motivoForaUso').value = '';
        document.getElementById('modalForaUso').style.display = 'block';
    }
    
    function closeModalForaUso() {
        document.getElementById('modalForaUso').style.display = 'none';
        equipamentoIdForaUso = null;
    }
    
    function confirmarForaUso() {
        const motivo = document.getElementById('motivoForaUso').value.trim();
        if (!motivo) {
            alert('Por favor, informe o motivo para marcar como fora de uso.');
            return;
        }
        
        fetch('ajax/fora_uso.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                id: equipamentoIdForaUso,
                motivo: motivo 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage(data.message || 'Equipamento marcado como Fora de Uso!');
                setTimeout(() => location.reload(), 1500);
            } else {
                alert('Erro: ' + (data.message || 'Erro ao processar solicitação'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao processar solicitação');
        });
        closeModalForaUso();
    }
    
    // ==================== FUNÇÕES DE UTILIDADE ====================
    function showSuccessMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'global-alert';
        alertDiv.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 1000; background: rgba(76,175,80,0.1); border-left: 4px solid #4CAF50; color: #2e7d32; padding: 1rem; border-radius: 8px; display: flex; align-items: center; gap: 0.75rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);';
        alertDiv.innerHTML = `
            <i class="fas fa-check-circle" style="color: #4CAF50;"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; margin-left: 0.5rem;">&times;</button>
        `;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 3000);
    }
    
    // ==================== FUNÇÕES DO MODAL DE DETALHES ====================
    function showEquipmentDetails(equipamento) {
        function formatarData(dataString) {
            if (!dataString) return '---';
            return new Date(dataString).toLocaleDateString('pt-BR');
        }
        
        let historico = '';
        if (equipamento.historico_manutencao && equipamento.historico_manutencao.length > 0) {
            historico = '<h4><i class="fas fa-history"></i> Histórico de Manutenção</h4><div class="historico-list">';
            equipamento.historico_manutencao.forEach(item => {
                historico += `<div class="historico-item"><strong>${formatarData(item.data_envio)}</strong>${item.data_retorno ? ` até ${formatarData(item.data_retorno)}` : '(em andamento)'}<br><small>Problema: ${item.problema || '---'}</small>${item.resultado ? `<br><small>Resultado: ${item.resultado}</small>` : ''}</div>`;
            });
            historico += '</div>';
        } else {
            historico = '<p><i class="fas fa-info-circle"></i> Nenhuma manutenção registrada.</p>';
        }
        
        const hostnameHtml = equipamento.hostname ? `<div class="detail-item"><strong>Hostname:</strong> <span class="hostname-badge ${equipamento.tipo === 'notebook' ? 'hostname-notebook' : ''}"><i class="fas fa-${equipamento.tipo === 'notebook' ? 'laptop' : 'network-wired'}"></i> ${equipamento.hostname}</span></div>` : '';
        
        const especificacoesHtml = equipamento.especificacoes ? `
            <div class="detail-row">
                ${equipamento.especificacoes.ram ? `<div class="detail-item"><strong>Memória RAM:</strong> ${equipamento.especificacoes.ram}</div>` : ''}
                ${equipamento.especificacoes.processador ? `<div class="detail-item"><strong>Processador:</strong> ${equipamento.especificacoes.processador}</div>` : ''}
                ${equipamento.especificacoes.hd ? `<div class="detail-item"><strong>Armazenamento:</strong> ${equipamento.especificacoes.hd}</div>` : ''}
            </div>
        ` : '';
        
        const content = `<div class="equipment-details">
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
                <div class="detail-item"><strong>Colaborador:</strong> ${getColaboradorNome(equipamento.colaborador_id)}</div>
            </div>
            ${especificacoesHtml}
            <div class="detail-row">
                <div class="detail-item full-width"><strong>Observações:</strong><br>${equipamento.observacoes || 'Nenhuma observação registrada.'}</div>
            </div>
            ${historico}
        </div>`;
        
        document.getElementById('modalBody').innerHTML = content;
        document.getElementById('equipmentModal').style.display = 'block';
    }
    
    function closeModal() {
        document.getElementById('equipmentModal').style.display = 'none';
    }
    
    function getColaboradorNome(id) {
        const colaboradores = <?php echo json_encode($mapaColaboradores); ?>;
        return colaboradores[id] ? colaboradores[id].nome : 'N/A';
    }
    
    function getStatusText(status) {
        const map = { 'estoque': 'Em Estoque', 'alocado': 'Alocado', 'emprestado': 'Emprestado', 'manutencao': 'Em Manutenção', 'fora_uso': 'Fora de Uso' };
        return map[status] || status;
    }
    
    function getTipoText(tipo) {
        const tipoMap = <?php echo json_encode(getTiposEquipamentos()); ?>;
        return tipoMap[tipo] || tipo;
    }
    
    // Fechar modais ao clicar fora
    window.onclick = function(event) {
        const modalManutencao = document.getElementById('modalManutencao');
        const modalForaUso = document.getElementById('modalForaUso');
        const modalDetails = document.getElementById('equipmentModal');
        if (event.target === modalManutencao) closeModalManutencao();
        if (event.target === modalForaUso) closeModalForaUso();
        if (event.target === modalDetails) closeModal();

        // Fechar dropdown ao clicar fora dele
        if (!event.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
                menu.classList.remove('dropdown-open');
            });
        }
    }

    // ==================== DROPDOWN ADICIONAR EQUIPAMENTO ====================
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownToggle = document.getElementById('addEquipmentDropdown');
        if (dropdownToggle) {
            const dropdown = dropdownToggle.closest('.dropdown');
            const menu = dropdown.querySelector('.dropdown-menu');
            const chevron = dropdownToggle.querySelector('.fa-chevron-down');

            dropdownToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                const isOpen = menu.classList.contains('dropdown-open');
                document.querySelectorAll('.dropdown-menu').forEach(function(m) {
                    m.classList.remove('dropdown-open');
                });
                document.querySelectorAll('#addEquipmentDropdown .fa-chevron-down').forEach(function(c) {
                    c.style.transform = '';
                });
                if (!isOpen) {
                    menu.classList.add('dropdown-open');
                    if (chevron) chevron.style.transform = 'rotate(180deg)';
                }
            });

            menu.querySelectorAll('.dropdown-item').forEach(function(item) {
                item.addEventListener('click', function() {
                    menu.classList.remove('dropdown-open');
                    if (chevron) chevron.style.transform = '';
                });
            });
        }
    });
</script>

</body>
</html>