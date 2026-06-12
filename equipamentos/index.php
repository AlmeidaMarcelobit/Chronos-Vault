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

// Combinar todos os equipamentos ativos (estoque, alocados, emprestados)
$equipamentosAtivosTotal = array_merge($equipamentosEstoque, $equipamentosAlocados, $equipamentosEmprestados);

// Combinar TODOS os equipamentos para filtros gerais
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

// Filtrar por colaborador (se especificado)
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

// Filtrar por status (apenas se não for filtro específico)
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
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipamentos - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/equipamentos/index.css">
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
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.1);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --transition: all 0.2s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-50) 100%); color: var(--gray-800); line-height: 1.5; }

        /* HEADER */
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

        /* CONTEÚDO PRINCIPAL */
        .main-container { max-width: 1440px; margin: 0 auto; padding: 2rem; }

        /* PAGE HEADER */
        .page-header { margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .page-header h1 { font-size: 1.75rem; font-weight: 700; color: var(--gray-800); display: flex; align-items: center; gap: 0.75rem; }
        .page-header h1 i { color: var(--primary); font-size: 1.75rem; }
        .page-subtitle { color: var(--gray-500); font-size: 0.875rem; margin-top: 0.25rem; margin-left: 2.5rem; }

        /* BOTÕES */
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: var(--radius-md); font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: var(--transition); border: none; cursor: pointer; font-family: 'Inter', sans-serif; }
        .btn-primary { background: var(--primary); color: var(--white); }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: var(--shadow-sm); }
        .btn-secondary { background: var(--gray-100); color: var(--gray-700); }
        .btn-secondary:hover { background: var(--gray-200); transform: translateY(-1px); }
        .btn-outline { background: transparent; color: var(--primary); border: 1px solid var(--primary); }
        .btn-outline:hover { background: var(--primary); color: var(--white); }

        /* FILTER TABS */
        .filter-tabs { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .filter-tab { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 2rem; background: var(--white); border: 1px solid var(--gray-200); color: var(--gray-600); text-decoration: none; font-size: 0.875rem; transition: var(--transition); }
        .filter-tab:hover { border-color: var(--primary); color: var(--primary); }
        .filter-tab.active { background: var(--primary); color: var(--white); border-color: var(--primary); }

        /* FILTER CARD */
        .filter-card { background: var(--white); border-radius: var(--radius-lg); padding: 1.25rem; margin-bottom: 1.5rem; border: 1px solid var(--gray-200); box-shadow: var(--shadow-sm); }
        .filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem; }
        .filter-group { display: flex; flex-direction: column; gap: 0.25rem; }
        .filter-group.full-width { grid-column: 1 / -1; }
        .filter-group label { font-size: 0.7rem; font-weight: 600; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.5px; }
        .filter-group label i { margin-right: 0.25rem; color: var(--primary); }
        .form-control { width: 100%; padding: 0.5rem 0.75rem; font-size: 0.875rem; border: 1px solid var(--gray-300); border-radius: var(--radius-md); transition: var(--transition); background: var(--white); }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1); }
        .filter-actions { display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem; }

        /* STATS CARDS */
        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card { background: var(--white); border-radius: var(--radius-lg); padding: 1rem; display: flex; align-items: center; gap: 0.75rem; box-shadow: var(--shadow-sm); border: 1px solid var(--gray-200); transition: var(--transition); }
        .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .stat-icon { width: 45px; height: 45px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .stat-icon.primary { background: rgba(33, 150, 243, 0.1); color: var(--primary); }
        .stat-icon.success { background: rgba(76, 175, 80, 0.1); color: var(--success); }
        .stat-icon.info { background: rgba(0, 188, 212, 0.1); color: var(--info); }
        .stat-icon.warning { background: rgba(255, 193, 7, 0.1); color: var(--warning); }
        .stat-icon.danger { background: rgba(244, 67, 54, 0.1); color: var(--danger); }
        .stat-content { flex: 1; }
        .stat-content h3 { font-size: 0.7rem; font-weight: 500; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.25rem; }
        .stat-number { font-size: 1.5rem; font-weight: 700; color: var(--gray-800); line-height: 1.2; }

        /* TABELA */
        .table-container { background: var(--white); border-radius: var(--radius-lg); border: 1px solid var(--gray-200); overflow-x: auto; margin-bottom: 1.5rem; }
        .data-table { width: 100%; border-collapse: collapse; min-width: 1000px; }
        .data-table thead { background: var(--gray-50); border-bottom: 1px solid var(--gray-200); }
        .data-table th { padding: 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: var(--gray-600); text-transform: uppercase; letter-spacing: 0.5px; }
        .data-table td { padding: 1rem; border-bottom: 1px solid var(--gray-100); font-size: 0.875rem; color: var(--gray-700); }
        .data-table tbody tr:hover { background: var(--gray-50); }
        .empty-state { text-align: center; padding: 3rem !important; }
        .empty-state i { font-size: 3rem; color: var(--gray-400); margin-bottom: 1rem; display: block; }
        .empty-state p { color: var(--gray-500); margin-bottom: 1rem; }

        /* BADGES */
        .tipo-badge { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.25rem 0.75rem; background: var(--gray-100); border-radius: 2rem; font-size: 0.75rem; color: var(--gray-700); }
        .status-badge { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.25rem 0.75rem; border-radius: 2rem; font-size: 0.75rem; font-weight: 500; }
        .status-ativo { background: rgba(76, 175, 80, 0.1); color: var(--success); }
        .status-inativo { background: rgba(244, 67, 54, 0.1); color: var(--danger); }
        .status-info { background: rgba(33, 150, 243, 0.1); color: var(--primary); }
        .status-warning { background: rgba(255, 193, 7, 0.1); color: var(--warning); }
        .status-danger { background: rgba(244, 67, 54, 0.1); color: var(--danger); }
        .hostname-badge { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.25rem 0.5rem; background: var(--gray-100); border-radius: var(--radius-sm); font-size: 0.7rem; font-family: monospace; }
        .hostname-notebook { background: rgba(33, 150, 243, 0.1); color: var(--primary); }

        /* ACTION BUTTONS */
        .action-buttons { display: flex; gap: 0.25rem; flex-wrap: wrap; }
        .action-btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: var(--radius-md); background: var(--gray-100); color: var(--gray-600); text-decoration: none; transition: var(--transition); border: none; cursor: pointer; }
        .action-btn:hover { transform: translateY(-2px); }
        .action-btn i { font-size: 0.875rem; }
        .action-edit:hover { background: var(--warning); color: var(--white); }
        .action-delete:hover { background: var(--danger); color: var(--white); }
        .action-equipments:hover { background: var(--success); color: var(--white); }
        .action-return:hover { background: var(--info); color: var(--white); }
        .action-warning:hover { background: var(--warning); color: var(--white); }
        .action-view:hover { background: var(--primary); color: var(--white); }

        /* PAGE FOOTER */
        .page-footer { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; padding-top: 1rem; border-top: 1px solid var(--gray-200); }
        .total-count { display: flex; align-items: center; gap: 0.5rem; color: var(--gray-600); font-size: 0.875rem; }
        .total-count i { color: var(--primary); }
        .total-count strong { color: var(--gray-800); }

        /* DROPDOWN */
        .dropdown { position: relative; display: inline-block; }
        .dropdown-toggle { cursor: pointer; }
        .dropdown-toggle .fa-chevron-down { font-size: 0.75rem; transition: transform 0.2s; }
        .dropdown:hover .dropdown-toggle .fa-chevron-down { transform: rotate(180deg); }
        .dropdown-menu { position: absolute; top: 100%; right: 0; background: var(--white); min-width: 220px; border-radius: var(--radius-md); box-shadow: var(--shadow-lg); border: 1px solid var(--gray-200); padding: 0.5rem 0; margin-top: 0.5rem; opacity: 0; visibility: hidden; transition: all 0.2s ease; z-index: 1000; }
        .dropdown:hover .dropdown-menu { opacity: 1; visibility: visible; }
        .dropdown-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.625rem 1rem; color: var(--gray-700); text-decoration: none; font-size: 0.875rem; transition: var(--transition); }
        .dropdown-item:hover { background: var(--gray-50); color: var(--primary); }
        .dropdown-item i { width: 1.25rem; color: var(--primary); }
        .dropdown-divider { height: 1px; background: var(--gray-200); margin: 0.25rem 0; }

        /* FOOTER */
        .footer { background: var(--white); border-top: 1px solid var(--gray-200); margin-top: 2rem; }
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

        /* RESPONSIVIDADE */
        @media (max-width: 1024px) {
            .stats-grid { grid-template-columns: repeat(3, 1fr); }
            .footer-content { grid-template-columns: 1fr; text-align: center; }
            .footer-section h3 { justify-content: center; }
            .footer-links a { justify-content: center; }
            .footer-stats { justify-content: center; }
        }
        @media (max-width: 768px) {
            .main-container { padding: 1rem; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .filter-grid { grid-template-columns: 1fr; }
            .filter-actions { flex-direction: column; }
            .filter-actions .btn { width: 100%; justify-content: center; }
            .page-header { flex-direction: column; align-items: flex-start; }
            .filter-tabs { overflow-x: auto; flex-wrap: nowrap; }
            .footer-bottom { flex-direction: column; }
            .dropdown-menu { position: fixed; top: auto; bottom: 0; left: 0; right: 0; width: 100%; border-radius: var(--radius-lg) var(--radius-lg) 0 0; max-height: 70vh; overflow-y: auto; }
            .dropdown:hover .dropdown-menu { opacity: 1; visibility: visible; }
        }
        @media (max-width: 480px) {
            .user-name { display: none; }
            .nav-link span { display: none; }
            .stats-grid { grid-template-columns: 1fr; }
            .action-btn { width: 36px; height: 36px; }
        }
    </style>
</head>
<body>

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
            <li class="nav-item"><a href="../equipamentos/index.php" class="nav-link active"><i class="fas fa-laptop"></i><span>Equipamentos</span></a></li>
            <li class="nav-item"><a href="../linhas/index.php" class="nav-link"><i class="fas fa-phone"></i><span>Linhas</span></a></li>
            <?php if ($is_admin): ?>
                <li class="nav-item"><a href="../termos/index.php" class="nav-link"><i class="fas fa-file-contract"></i><span>Termos</span></a></li>
                <li class="nav-item"><a href="../usuarios/index.php" class="nav-link"><i class="fas fa-user-cog"></i><span>Usuários</span></a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

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
                                <a href="enviar_manutencao.php?id=<?php echo $equipamento["id"]; ?>" class="action-btn action-warning" title="Enviar para Manutenção" onclick="return confirm('Enviar este equipamento para manutenção?')"><i class="fas fa-tools"></i></a>
                            <?php endif; ?>
                            <?php if ($can_edit && $equipamento["status"] == "manutencao"): ?>
                                <a href="finalizar_manutencao.php?id=<?php echo $equipamento["id"]; ?>" class="action-btn action-success" title="Finalizar Manutenção" onclick="return confirm('Finalizar manutenção deste equipamento?')"><i class="fas fa-check"></i></a>
                            <?php endif; ?>
                            <?php if ($can_edit && $equipamento["status"] != "fora_uso"): ?>
                                <a href="marcar_fora_uso.php?id=<?php echo $equipamento["id"]; ?>" class="action-btn action-delete" title="Marcar Fora de Uso" onclick="return confirm('Marcar este equipamento como FORA DE USO? Esta ação irá desassociar o colaborador e mover para fora de uso.')"><i class="fas fa-times-circle"></i></a>
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

<!-- MODAL -->
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
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); animation: fadeIn 0.2s ease; }
    .modal-content { background: var(--white); margin: 5% auto; max-width: 700px; width: 90%; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); animation: slideIn 0.2s ease; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem; border-bottom: 1px solid var(--gray-200); }
    .modal-header h3 { display: flex; align-items: center; gap: 0.5rem; color: var(--primary); }
    .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--gray-500); transition: var(--transition); }
    .modal-close:hover { color: var(--danger); }
    .modal-body { padding: 1.25rem; max-height: 60vh; overflow-y: auto; }
    .equipment-details { display: flex; flex-direction: column; gap: 1rem; }
    .detail-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; padding: 0.5rem 0; border-bottom: 1px solid var(--gray-100); }
    .detail-item.full-width { grid-column: 1 / -1; }
    .detail-item strong { font-size: 0.7rem; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.25rem; }
    .historico-list { margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem; }
    .historico-item { padding: 0.75rem; background: var(--gray-50); border-radius: var(--radius-md); border-left: 3px solid var(--primary); }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideIn { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @media (max-width: 768px) { .detail-row { grid-template-columns: 1fr; gap: 0.5rem; } }
</style>

<script>
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
        const content = `<div class="equipment-details"><div class="detail-row"><div class="detail-item"><strong>Patrimônio:</strong> ${equipamento.patrimonio}</div><div class="detail-item"><strong>Status:</strong> <span class="status-badge">${getStatusText(equipamento.status)}</span></div></div><div class="detail-row"><div class="detail-item"><strong>Tipo:</strong> ${getTipoText(equipamento.tipo)}</div><div class="detail-item"><strong>Marca/Modelo:</strong> ${equipamento.marca || '---'} ${equipamento.modelo || ''}</div></div><div class="detail-row">${hostnameHtml}<div class="detail-item"><strong>Nº Série:</strong> ${equipamento.serial || '---'}</div></div><div class="detail-row"><div class="detail-item"><strong>Centro de Custo:</strong> ${equipamento.centro_custo || '---'}</div><div class="detail-item"><strong>Colaborador:</strong> ${getColaboradorNome(equipamento.colaborador_id)}</div></div>${especificacoesHtml}<div class="detail-row"><div class="detail-item full-width"><strong>Observações:</strong><br>${equipamento.observacoes || 'Nenhuma observação registrada.'}</div></div>${historico}</div>`;
        document.getElementById('modalBody').innerHTML = content;
        document.getElementById('equipmentModal').style.display = 'block';
    }

    function getColaboradorNome(id) {
        const colaboradores = <?php echo json_encode($mapaColaboradores); ?>;
        return colaboradores[id] ? colaboradores[id].nome : 'N/A';
    }

    function closeModal() { document.getElementById('equipmentModal').style.display = 'none'; }
    window.onclick = function(event) { if (event.target == document.getElementById('equipmentModal')) closeModal(); };
    function getStatusText(status) { const map = { 'estoque': 'Em Estoque', 'alocado': 'Alocado', 'emprestado': 'Emprestado', 'manutencao': 'Em Manutenção', 'fora_uso': 'Fora de Uso' }; return map[status] || status; }
    function getTipoText(tipo) { const tipoMap = <?php echo json_encode(getTiposEquipamentos()); ?>; return tipoMap[tipo] || tipo; }
</script>

</body>
</html>