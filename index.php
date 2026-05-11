<?php
session_start();
require_once 'includes/funcoes.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Carregar dados
$colaboradores = lerArquivoJSON('data/colaboradores.json');
$equipamentos = lerArquivoJSON('data/equipamentos.json');
$linhas = lerArquivoJSON('data/linhas.json');

// Estatísticas de Colaboradores
$totalColaboradores = count($colaboradores);
$colaboradoresHomeOffice = count(array_filter($colaboradores, function($c) {
    return ($c['tipo_trabalho'] ?? 'local') === 'home';
}));
$colaboradoresPresencial = $totalColaboradores - $colaboradoresHomeOffice;

// Estatísticas de Equipamentos
$totalEquipamentos = count($equipamentos);
$equipamentosEstoque = count(array_filter($equipamentos, function($e) {
    return $e['status'] === 'estoque';
}));
$equipamentosAlocados = count(array_filter($equipamentos, function($e) {
    return $e['status'] === 'alocado';
}));
$equipamentosEmprestados = count(array_filter($equipamentos, function($e) {
    return $e['status'] === 'emprestado';
}));
$equipamentosManutencao = count(array_filter($equipamentos, function($e) {
    return $e['status'] === 'manutencao';
}));
$equipamentosForaUso = count(array_filter($equipamentos, function($e) {
    return $e['status'] === 'fora_uso';
}));

// Estatísticas de Linhas
$totalLinhas = count($linhas);
$linhasDisponiveis = count(array_filter($linhas, function($l) {
    return $l['status'] === 'disponivel';
}));
$linhasAlocadas = count(array_filter($linhas, function($l) {
    return $l['status'] === 'alocado';
}));

// Equipamentos por tipo
$equipamentosPorTipo = [];
foreach ($equipamentos as $e) {
    $tipo = $e['tipo'];
    if (!isset($equipamentosPorTipo[$tipo])) {
        $equipamentosPorTipo[$tipo] = 0;
    }
    $equipamentosPorTipo[$tipo]++;
}

// Equipamentos por centro de custo (Top 5)
$equipamentosPorCentroCusto = [];
foreach ($equipamentos as $e) {
    $cc = $e['centro_custo'];
    if (!isset($equipamentosPorCentroCusto[$cc])) {
        $equipamentosPorCentroCusto[$cc] = 0;
    }
    $equipamentosPorCentroCusto[$cc]++;
}
arsort($equipamentosPorCentroCusto);
$topCentroCusto = array_slice($equipamentosPorCentroCusto, 0, 5, true);

$page_title = 'Dashboard - Sistema de Gestão';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<!-- ==================== HEADER ==================== -->
<header class="header">
    <div class="header-content">
        <div class="logo">
            <a href="index.php">
                <i class="fas fa-laptop-house"></i>
                <h1>Sistema de Gestão</h1>
            </a>
        </div>

        <div class="user-menu">
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário'); ?></span>
            </div>

            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sair</span>
            </a>
        </div>
    </div>

    <nav class="nav-container">
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="index.php" class="nav-link active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="colaboradores/index.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Colaboradores</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="equipamentos/index.php" class="nav-link">
                    <i class="fas fa-laptop"></i>
                    <span>Equipamentos</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="linhas/index.php" class="nav-link">
                    <i class="fas fa-phone"></i>
                    <span>Linhas</span>
                </a>
            </li>
        </ul>
    </nav>
</header>

<!-- ==================== CONTEÚDO PRINCIPAL ==================== -->
<main class="main-container">
    <div class="dashboard-header">
        <h1><i class="fas fa-chart-line"></i> Dashboard</h1>
        <p class="dashboard-subtitle">Visão geral do sistema de gestão</p>
    </div>

    <!-- Cards de Resumo -->
    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-content">
                <h3>Colaboradores</h3>
                <p class="stat-number"><?php echo $totalColaboradores; ?></p>
                <div class="stat-detail">
                    <span><i class="fas fa-home"></i> Home: <?php echo $colaboradoresHomeOffice; ?></span>
                    <span><i class="fas fa-building"></i> Local: <?php echo $colaboradoresPresencial; ?></span>
                </div>
            </div>
        </div>

        <div class="stat-card stat-success">
            <div class="stat-icon"><i class="fas fa-laptop"></i></div>
            <div class="stat-content">
                <h3>Equipamentos</h3>
                <p class="stat-number"><?php echo $totalEquipamentos; ?></p>
                <div class="stat-detail">
                    <span><i class="fas fa-warehouse"></i> Estoque: <?php echo $equipamentosEstoque; ?></span>
                    <span><i class="fas fa-user-check"></i> Alocados: <?php echo $equipamentosAlocados; ?></span>
                </div>
            </div>
        </div>

        <div class="stat-card stat-info">
            <div class="stat-icon"><i class="fas fa-phone"></i></div>
            <div class="stat-content">
                <h3>Linhas</h3>
                <p class="stat-number"><?php echo $totalLinhas; ?></p>
                <div class="stat-detail">
                    <span><i class="fas fa-check-circle"></i> Disp.: <?php echo $linhasDisponiveis; ?></span>
                    <span><i class="fas fa-user-check"></i> Aloc.: <?php echo $linhasAlocadas; ?></span>
                </div>
            </div>
        </div>

        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="fas fa-chart-pie"></i></div>
            <div class="stat-content">
                <h3>Ocupação</h3>
                <p class="stat-number"><?php echo $totalEquipamentos > 0 ? round((($equipamentosAlocados + $equipamentosEmprestados) / $totalEquipamentos) * 100) : 0; ?>%</p>
                <div class="stat-detail">
                    <span><i class="fas fa-chart-line"></i> Utilização</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos - Primeira Linha -->
    <div class="charts-row">
        <!-- Gráfico de Status dos Equipamentos (Pizza) -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-pie"></i> Status dos Equipamentos</h3>
                <p>Distribuição atual dos equipamentos</p>
            </div>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="chart-legend">
                <div class="legend-item"><span class="color-box" style="background: #2ECC71;"></span> Em Estoque (<?php echo $equipamentosEstoque; ?>)</div>
                <div class="legend-item"><span class="color-box" style="background: #3498DB;"></span> Alocados (<?php echo $equipamentosAlocados; ?>)</div>
                <div class="legend-item"><span class="color-box" style="background: #F39C12;"></span> Emprestados (<?php echo $equipamentosEmprestados; ?>)</div>
                <div class="legend-item"><span class="color-box" style="background: #E74C3C;"></span> Manutenção (<?php echo $equipamentosManutencao; ?>)</div>
                <div class="legend-item"><span class="color-box" style="background: #95A5A6;"></span> Fora de Uso (<?php echo $equipamentosForaUso; ?>)</div>
            </div>
        </div>

        <!-- Gráfico de Equipamentos por Tipo (Pizza) -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-pie"></i> Equipamentos por Tipo</h3>
                <p>Distribuição por categoria</p>
            </div>
            <div class="chart-container">
                <canvas id="tipoChart"></canvas>
            </div>
            <div class="chart-legend">
                <?php foreach ($equipamentosPorTipo as $tipo => $quantidade): ?>
                    <div class="legend-item">
                        <span class="color-box tipo-<?php echo $tipo; ?>"></span>
                        <?php echo getTipoTexto($tipo); ?> (<?php echo $quantidade; ?>)
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Gráficos - Segunda Linha -->
    <div class="charts-row">
        <!-- Top Centros de Custo (Barra Horizontal) -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-bar"></i> Top Centros de Custo</h3>
                <p>Centros com mais equipamentos</p>
            </div>
            <div class="chart-container">
                <canvas id="centroCustoChart"></canvas>
            </div>
        </div>

        <!-- Status das Linhas (Pizza) -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-pie"></i> Status das Linhas</h3>
                <p>Distribuição das linhas telefônicas</p>
            </div>
            <div class="chart-container">
                <canvas id="linhasChart"></canvas>
            </div>
            <div class="chart-legend">
                <div class="legend-item"><span class="color-box" style="background: #2ECC71;"></span> Disponível (<?php echo $linhasDisponiveis; ?>)</div>
                <div class="legend-item"><span class="color-box" style="background: #F39C12;"></span> Alocado (<?php echo $linhasAlocadas; ?>)</div>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="quick-actions-section">
        <div class="section-header">
            <h2><i class="fas fa-bolt"></i> Ações Rápidas</h2>
            <p>Realize as principais operações do sistema</p>
        </div>
        <div class="action-grid">
            <a href="colaboradores/adicionar.php" class="action-card">
                <div class="action-icon"><i class="fas fa-user-plus"></i></div>
                <div class="action-content">
                    <h3>Adicionar Colaborador</h3>
                    <p>Cadastre um novo colaborador</p>
                </div>
            </a>
            <a href="equipamentos/adicionar.php" class="action-card">
                <div class="action-icon"><i class="fas fa-laptop-medical"></i></div>
                <div class="action-content">
                    <h3>Adicionar Equipamento</h3>
                    <p>Cadastre um novo equipamento</p>
                </div>
            </a>
            <a href="linhas/adicionar.php" class="action-card">
                <div class="action-icon"><i class="fas fa-phone-plus"></i></div>
                <div class="action-content">
                    <h3>Adicionar Linha</h3>
                    <p>Cadastre uma nova linha</p>
                </div>
            </a>
            <a href="equipamentos/atribuir_caixa.php" class="action-card">
                <div class="action-icon"><i class="fas fa-boxes"></i></div>
                <div class="action-content">
                    <h3>Atribuir Caixa</h3>
                    <p>Atribuir caixa inteira</p>
                </div>
            </a>
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
                <li><a href="colaboradores/index.php"><i class="fas fa-users"></i> Colaboradores</a></li>
                <li><a href="equipamentos/index.php"><i class="fas fa-laptop"></i> Equipamentos</a></li>
                <li><a href="linhas/index.php"><i class="fas fa-phone"></i> Linhas</a></li>
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
                    <span class="stat-number"><?php echo $equipamentosAlocados + $equipamentosEmprestados; ?></span>
                    <span class="stat-label">Em Uso</span>
                </div>
                <div class="footer-stat">
                    <span class="stat-number"><?php echo $equipamentosEstoque; ?></span>
                    <span class="stat-label">Disponíveis</span>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
        <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
</footer>

<script>
    // Gráfico de Status dos Equipamentos
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'pie',
        data: {
            labels: ['Em Estoque', 'Alocados', 'Emprestados', 'Em Manutenção', 'Fora de Uso'],
            datasets: [{
                data: [
                    <?php echo $equipamentosEstoque; ?>,
                    <?php echo $equipamentosAlocados; ?>,
                    <?php echo $equipamentosEmprestados; ?>,
                    <?php echo $equipamentosManutencao; ?>,
                    <?php echo $equipamentosForaUso; ?>
                ],
                backgroundColor: ['#2ECC71', '#3498DB', '#F39C12', '#E74C3C', '#95A5A6'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
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

    // Gráfico de Equipamentos por Tipo
    const ctxTipo = document.getElementById('tipoChart').getContext('2d');
    const tiposLabels = [
        <?php foreach ($equipamentosPorTipo as $tipo => $quantidade): ?>
        '<?php echo getTipoTexto($tipo); ?>',
        <?php endforeach; ?>
    ];
    const tiposData = [
        <?php foreach ($equipamentosPorTipo as $quantidade): ?>
        <?php echo $quantidade; ?>,
        <?php endforeach; ?>
    ];

    // Gerar cores dinâmicas para os tipos
    const tipoColors = [
        '#6B3E8F', '#9B59B6', '#C39BD3', '#4A266A', '#E8DAEF',
        '#2ECC71', '#27AE60', '#F1C40F', '#E67E22', '#3498DB'
    ];

    new Chart(ctxTipo, {
        type: 'pie',
        data: {
            labels: tiposLabels,
            datasets: [{
                data: tiposData,
                backgroundColor: tipoColors.slice(0, tiposLabels.length),
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
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

    // Gráfico de Top Centros de Custo (Barra Horizontal)
    const ctxCC = document.getElementById('centroCustoChart').getContext('2d');
    const ccLabels = [
        <?php foreach ($topCentroCusto as $cc => $quantidade): ?>
        '<?php echo addslashes($cc); ?>',
        <?php endforeach; ?>
    ];
    const ccData = [
        <?php foreach ($topCentroCusto as $quantidade): ?>
        <?php echo $quantidade; ?>,
        <?php endforeach; ?>
    ];

    new Chart(ctxCC, {
        type: 'bar',
        data: {
            labels: ccLabels,
            datasets: [{
                label: 'Quantidade de Equipamentos',
                data: ccData,
                backgroundColor: '#6B3E8F',
                borderRadius: 8,
                barPercentage: 0.7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Equipamentos: ${context.raw}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Quantidade'
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Gráfico de Status das Linhas
    const ctxLinhas = document.getElementById('linhasChart').getContext('2d');
    new Chart(ctxLinhas, {
        type: 'pie',
        data: {
            labels: ['Disponível', 'Alocado'],
            datasets: [{
                data: [<?php echo $linhasDisponiveis; ?>, <?php echo $linhasAlocadas; ?>],
                backgroundColor: ['#2ECC71', '#F39C12'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
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
</script>
</body>
</html>