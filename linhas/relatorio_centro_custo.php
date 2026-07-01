<?php
session_start();
require_once '../includes/funcoes.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

// Carregar dados
$linhas = lerArquivoJSON('../data/linhas.json');
if ($linhas === false) $linhas = [];

// Estatísticas por centro de custo
$centrosCusto = [];
foreach ($linhas as $linha) {
    $cc = $linha['centro_custo'] ?? 'Sem CC';
    if (!isset($centrosCusto[$cc])) {
        $centrosCusto[$cc] = [
            'total' => 0,
            'disponiveis' => 0,
            'alocados' => 0,
            'indisponiveis' => 0,
            'chips' => 0,
            'echips' => 0
        ];
    }
    
    $centrosCusto[$cc]['total']++;
    
    if ($linha['status'] === 'disponivel') {
        $centrosCusto[$cc]['disponiveis']++;
    } elseif ($linha['status'] === 'alocado') {
        $centrosCusto[$cc]['alocados']++;
    } elseif ($linha['status'] === 'indisponivel') {
        $centrosCusto[$cc]['indisponiveis']++;
    }
    
    if ($linha['tipo'] === 'chip') {
        $centrosCusto[$cc]['chips']++;
    } elseif ($linha['tipo'] === 'echip') {
        $centrosCusto[$cc]['echips']++;
    }
}

// Ordenar por total (decrescente)
uasort($centrosCusto, function($a, $b) {
    return $b['total'] - $a['total'];
});

// Totais gerais
$totalLinhas = count($linhas);
$totalDisponiveis = array_sum(array_column($centrosCusto, 'disponiveis'));
$totalAlocados = array_sum(array_column($centrosCusto, 'alocados'));
$totalIndisponiveis = array_sum(array_column($centrosCusto, 'indisponiveis'));
$totalChips = array_sum(array_column($centrosCusto, 'chips'));
$totalEchips = array_sum(array_column($centrosCusto, 'echips'));

// Preparar dados para os gráficos (Chart.js)
$labels = array_keys($centrosCusto);
$dadosTotais = array_column($centrosCusto, 'total');
$dadosDisponiveis = array_column($centrosCusto, 'disponiveis');
$dadosAlocados = array_column($centrosCusto, 'alocados');
$dadosIndisponiveis = array_column($centrosCusto, 'indisponiveis');

// Cores para os gráficos
$cores = [
    'rgba(46, 204, 113, 0.8)',   // Verde - Disponível
    'rgba(52, 152, 219, 0.8)',   // Azul - Alocado
    'rgba(243, 156, 18, 0.8)',   // Laranja - Indisponível
];

$coresBorda = [
    'rgba(46, 204, 113, 1)',
    'rgba(52, 152, 219, 1)',
    'rgba(243, 156, 18, 1)',
];

$page_title = 'Relatório de Linhas por Centro de Custo - Sistema de Gestão';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="icon" href="../img/favicon/favicon.png">
    <style>
        /* ========================================
           VARIÁVEIS E RESET
           ======================================== */
        :root {
            --primary-light: #E8F4FD;
            --primary: #2196F3;
            --primary-dark: #1976D2;
            --success: #2ECC71;
            --danger: #E74C3C;
            --warning: #F39C12;
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
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-50) 100%); color: var(--gray-800); line-height: 1.5; }

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
        .main-container { max-width: 1440px; margin: 0 auto; padding: 2rem; min-height: calc(100vh - 200px); }

        /* PAGE HEADER */
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .page-header h1 { font-size: 1.75rem; font-weight: 700; color: var(--gray-800); display: flex; align-items: center; gap: 0.75rem; }
        .page-header h1 i { color: var(--primary); font-size: 1.75rem; }
        .page-subtitle { color: var(--gray-500); font-size: 0.875rem; margin-top: 0.25rem; margin-left: 2.5rem; }

        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: var(--radius-md); font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: var(--transition); border: none; cursor: pointer; font-family: 'Inter', sans-serif; }
        .btn-primary { background: var(--primary); color: var(--white); }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: var(--shadow-sm); }
        .btn-secondary { background: var(--gray-100); color: var(--gray-700); }
        .btn-secondary:hover { background: var(--gray-200); transform: translateY(-1px); }

        /* STATS CARDS */
        .stats-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: var(--white); border-radius: var(--radius-lg); padding: 1rem; display: flex; align-items: center; gap: 0.75rem; box-shadow: var(--shadow-sm); border: 1px solid var(--gray-200); transition: var(--transition); }
        .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .stat-icon { width: 42px; height: 42px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
        .stat-icon.primary { background: rgba(33, 150, 243, 0.1); color: var(--primary); }
        .stat-icon.success { background: rgba(46, 204, 113, 0.1); color: var(--success); }
        .stat-icon.warning { background: rgba(243, 156, 18, 0.1); color: var(--warning); }
        .stat-icon.danger { background: rgba(231, 76, 60, 0.1); color: var(--danger); }
        .stat-icon.info { background: rgba(0, 188, 212, 0.1); color: var(--info); }
        .stat-icon.gray { background: rgba(158, 158, 158, 0.1); color: var(--gray-600); }
        .stat-content { flex: 1; min-width: 0; }
        .stat-content h3 { font-size: 0.65rem; font-weight: 500; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.1rem; }
        .stat-number { font-size: 1.5rem; font-weight: 700; color: var(--gray-800); line-height: 1.2; }

        /* CHART CARDS */
        .charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
        .chart-card { background: var(--white); border-radius: var(--radius-lg); padding: 1.25rem; border: 1px solid var(--gray-200); box-shadow: var(--shadow-sm); }
        .chart-card h3 { font-size: 1rem; font-weight: 600; color: var(--gray-700); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--gray-200); }
        .chart-card h3 i { color: var(--primary); }
        .chart-container { min-height: 300px; position: relative; }

        /* TABLE */
        .table-container { background: var(--white); border-radius: var(--radius-lg); border: 1px solid var(--gray-200); overflow-x: auto; margin-top: 1.5rem; }
        .data-table { width: 100%; border-collapse: collapse; min-width: 700px; }
        .data-table thead { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); }
        .data-table th { padding: 0.75rem 1rem; text-align: left; font-size: 0.7rem; font-weight: 600; color: var(--white); text-transform: uppercase; letter-spacing: 0.5px; }
        .data-table td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--gray-100); font-size: 0.85rem; color: var(--gray-700); }
        .data-table tbody tr:hover { background: var(--primary-light); }
        .data-table tbody tr:last-child td { border-bottom: none; }
        .data-table .total-row { background: var(--gray-50); font-weight: 600; border-top: 2px solid var(--gray-300); }
        .data-table .total-row td { color: var(--gray-800); }

        .status-badge { display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.15rem 0.5rem; border-radius: 2rem; font-size: 0.65rem; font-weight: 500; }
        .status-ativo { background: rgba(46, 204, 113, 0.12); color: var(--success); }
        .status-inativo { background: rgba(52, 152, 219, 0.12); color: var(--info); }
        .status-indisponivel { background: rgba(243, 156, 18, 0.12); color: var(--warning); }

        .cc-badge { display: inline-flex; align-items: center; gap: 0.25rem; background: var(--gray-100); padding: 0.15rem 0.5rem; border-radius: 0.25rem; font-size: 0.7rem; font-family: monospace; }

        /* FOOTER */
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

        /* RESPONSIVIDADE */
        @media (max-width: 1024px) {
            .stats-grid { grid-template-columns: repeat(3, 1fr); }
            .charts-row { grid-template-columns: 1fr; }
            .footer-content { grid-template-columns: 1fr; text-align: center; }
            .footer-section h3 { justify-content: center; }
            .footer-links a { justify-content: center; }
            .footer-stats { justify-content: center; }
        }
        @media (max-width: 768px) {
            .main-container { padding: 1rem; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
            .stat-card { padding: 0.75rem; }
            .stat-number { font-size: 1.25rem; }
            .page-header { flex-direction: column; align-items: flex-start; }
            .data-table thead { display: none; }
            .data-table tbody tr { display: block; margin-bottom: 0.75rem; border: 1px solid var(--gray-200); border-radius: var(--radius-md); padding: 0.75rem; background: var(--white); }
            .data-table td { display: flex; align-items: flex-start; gap: 0.5rem; padding: 0.35rem 0.5rem; border: none; font-size: 0.8rem; }
            .data-table td:before { content: attr(data-label); font-weight: 600; color: var(--gray-700); min-width: 110px; font-size: 0.7rem; text-transform: uppercase; }
            .footer-bottom { flex-direction: column; }
        }
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
            .user-name { display: none; }
            .nav-link span { display: none; }
            .nav-link i { font-size: 1.2rem; }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header class="header">
    <div class="header-content">
        <div class="logo">
            <a href="../index.php">
                <i class="fas fa-chart-pie"></i>
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
            <li class="nav-item"><a href="../equipamentos/index.php" class="nav-link"><i class="fas fa-laptop"></i><span>Equipamentos</span></a></li>
            <li class="nav-item"><a href="../linhas/index.php" class="nav-link"><i class="fas fa-phone"></i><span>Linhas</span></a></li>
            <li class="nav-item"><a href="relatorio_centro_custo.php" class="nav-link active"><i class="fas fa-chart-pie"></i><span>Relatório CC</span></a></li>
        </ul>
    </nav>
</header>

<!-- CONTEÚDO PRINCIPAL -->
<main class="main-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-chart-pie"></i> Relatório de Linhas por Centro de Custo</h1>
            <p class="page-subtitle">Distribuição de linhas telefônicas por centro de custo</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar para Linhas
        </a>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon gray"><i class="fas fa-phone"></i></div>
            <div class="stat-content">
                <h3>Total de Linhas</h3>
                <p class="stat-number"><?php echo $totalLinhas; ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-check-circle"></i></div>
            <div class="stat-content">
                <h3>Disponíveis</h3>
                <p class="stat-number"><?php echo $totalDisponiveis; ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon info"><i class="fas fa-user-check"></i></div>
            <div class="stat-content">
                <h3>Alocados</h3>
                <p class="stat-number"><?php echo $totalAlocados; ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-ban"></i></div>
            <div class="stat-content">
                <h3>Indisponíveis</h3>
                <p class="stat-number"><?php echo $totalIndisponiveis; ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon primary"><i class="fas fa-sim-card"></i></div>
            <div class="stat-content">
                <h3>Chips</h3>
                <p class="stat-number"><?php echo $totalChips; ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon primary"><i class="fas fa-microchip"></i></div>
            <div class="stat-content">
                <h3>E-Chips</h3>
                <p class="stat-number"><?php echo $totalEchips; ?></p>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="charts-row">
        <div class="chart-card">
            <h3><i class="fas fa-chart-bar"></i> Distribuição por Centro de Custo</h3>
            <div class="chart-container">
                <canvas id="barChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3><i class="fas fa-chart-pie"></i> Status por Centro de Custo</h3>
            <div class="chart-container">
                <canvas id="pieChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tabela Detalhada -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Centro de Custo</th>
                    <th>Total</th>
                    <th>Disponíveis</th>
                    <th>Alocados</th>
                    <th>Indisponíveis</th>
                    <th>Chips</th>
                    <th>E-Chips</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($centrosCusto)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="fas fa-phone-slash"></i>
                            <p>Nenhuma linha cadastrada</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($centrosCusto as $cc => $dados): ?>
                        <tr>
                            <td data-label="Centro de Custo"><span class="cc-badge"><?php echo htmlspecialchars($cc); ?></span></td>
                            <td data-label="Total"><strong><?php echo $dados['total']; ?></strong></td>
                            <td data-label="Disponíveis"><span class="status-badge status-ativo"><i class="fas fa-check-circle"></i> <?php echo $dados['disponiveis']; ?></span></td>
                            <td data-label="Alocados"><span class="status-badge status-inativo"><i class="fas fa-user-check"></i> <?php echo $dados['alocados']; ?></span></td>
                            <td data-label="Indisponíveis"><span class="status-badge status-indisponivel"><i class="fas fa-ban"></i> <?php echo $dados['indisponiveis']; ?></span></td>
                            <td data-label="Chips"><i class="fas fa-sim-card"></i> <?php echo $dados['chips']; ?></td>
                            <td data-label="E-Chips"><i class="fas fa-microchip"></i> <?php echo $dados['echips']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td data-label="Centro de Custo"><strong>TOTAL</strong></td>
                        <td data-label="Total"><strong><?php echo $totalLinhas; ?></strong></td>
                        <td data-label="Disponíveis"><strong><?php echo $totalDisponiveis; ?></strong></td>
                        <td data-label="Alocados"><strong><?php echo $totalAlocados; ?></strong></td>
                        <td data-label="Indisponíveis"><strong><?php echo $totalIndisponiveis; ?></strong></td>
                        <td data-label="Chips"><strong><?php echo $totalChips; ?></strong></td>
                        <td data-label="E-Chips"><strong><?php echo $totalEchips; ?></strong></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3><i class="fas fa-chart-pie"></i> Gestão de Linhas</h3>
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
            <div class="footer-stats">
                <div class="footer-stat"><span class="stat-number"><?php echo $totalLinhas; ?></span><span class="stat-label">Linhas</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo $totalAlocados; ?></span><span class="stat-label">Alocados</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo $totalDisponiveis; ?></span><span class="stat-label">Disponíveis</span></div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
        <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dados para os gráficos
        const labels = <?php echo json_encode($labels); ?>;
        const dadosTotais = <?php echo json_encode($dadosTotais); ?>;
        const dadosDisponiveis = <?php echo json_encode($dadosDisponiveis); ?>;
        const dadosAlocados = <?php echo json_encode($dadosAlocados); ?>;
        const dadosIndisponiveis = <?php echo json_encode($dadosIndisponiveis); ?>;

        // Cores
        const cores = [
            'rgba(46, 204, 113, 0.8)',
            'rgba(52, 152, 219, 0.8)',
            'rgba(243, 156, 18, 0.8)'
        ];

        // 1. Gráfico de Barras
        const ctxBar = document.getElementById('barChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Disponíveis',
                        data: dadosDisponiveis,
                        backgroundColor: 'rgba(46, 204, 113, 0.7)',
                        borderColor: 'rgba(46, 204, 113, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Alocados',
                        data: dadosAlocados,
                        backgroundColor: 'rgba(52, 152, 219, 0.7)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Indisponíveis',
                        data: dadosIndisponiveis,
                        backgroundColor: 'rgba(243, 156, 18, 0.7)',
                        borderColor: 'rgba(243, 156, 18, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.dataset.label || '';
                                const value = context.raw || 0;
                                const total = dadosTotais[context.dataIndex] || 1;
                                const percent = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percent}%)`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        title: { display: true, text: 'Quantidade de Linhas' }
                    }
                }
            }
        });

        // 2. Gráfico de Pizza
        const ctxPie = document.getElementById('pieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: dadosTotais,
                    backgroundColor: [
                        'rgba(46, 204, 113, 0.7)',
                        'rgba(52, 152, 219, 0.7)',
                        'rgba(243, 156, 18, 0.7)',
                        'rgba(155, 89, 182, 0.7)',
                        'rgba(26, 188, 156, 0.7)',
                        'rgba(231, 76, 60, 0.7)',
                        'rgba(149, 165, 166, 0.7)',
                        'rgba(241, 196, 15, 0.7)'
                    ],
                    borderColor: '#FFFFFF',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${label}: ${value} (${percent}%)`;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
</body>
</html>