<?php
session_start();
require_once 'includes/funcoes.php';
verificarSessao();

$colaboradores = lerArquivoJSON('data/colaboradores.json');
$equipamentos = lerArquivoJSON('data/equipamentos.json');

// Calcular totais
$totalColaboradores = count($colaboradores);
$totalEquipamentos = count($equipamentos);
$totalEstoque = 0;
$totalAtribuidos = 0;

foreach ($equipamentos as $equipamento) {
    if ($equipamento['status'] === 'estoque') {
        $totalEstoque++;
    } else {
        $totalAtribuidos++;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/Favicon/Favicon Main/favicon.ico">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Dashboard - Sistema de Gestão</title>
</head>
<body>

    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <a href="home.php">
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
                    <a href="home.php" class="nav-link active">
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
    
    <!-- Alert Messages -->
    <?php if (isset($_SESSION['mensagem'])): ?>
    <div class="global-alert alert-<?php echo $_SESSION['mensagem_tipo'] ?? 'info'; ?>">
        <div class="alert-content">
            <i class="fas fa-<?php echo $_SESSION['mensagem_tipo'] === 'success' ? 'check-circle' : ($_SESSION['mensagem_tipo'] === 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
            <span><?php echo htmlspecialchars($_SESSION['mensagem']); ?></span>
        </div>
        <button class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
    <?php 
    unset($_SESSION['mensagem']);
    unset($_SESSION['mensagem_tipo']);
    endif; ?>
    
    <!-- Main Content -->
    <main class="main-container">
        <div class="dashboard-header">
            <h1>
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </h1>
            <p class="dashboard-subtitle">Visão geral do sistema de gestão</p>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3>Colaboradores</h3>
                    <p class="stat-number"><?php echo $totalColaboradores; ?></p>
                    <a href="colaboradores/" class="stat-link">Ver todos <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-laptop"></i>
                </div>
                <div class="stat-content">
                    <h3>Equipamentos</h3>
                    <p class="stat-number"><?php echo $totalEquipamentos; ?></p>
                    <a href="equipamentos/" class="stat-link">Ver todos <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-warehouse"></i>
                </div>
                <div class="stat-content">
                    <h3>Em Estoque</h3>
                    <p class="stat-number"><?php echo $totalEstoque; ?></p>
                    <a href="equipamentos/?filtro=estoque" class="stat-link">Ver estoque <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            
            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-content">
                    <h3>Atribuídos</h3>
                    <p class="stat-number"><?php echo $totalAtribuidos; ?></p>
                    <a href="equipamentos/?filtro=atribuidos" class="stat-link">Ver atribuídos <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="section-header">
                <h2><i class="fas fa-bolt"></i> Ações Rápidas</h2>
                <p>Realize as principais operações do sistema</p>
            </div>
            <div class="action-buttons">
                <a href="colaboradores/adicionar.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Adicionar Colaborador
                </a>
                <a href="equipamentos/adicionar.php" class="btn btn-success">
                    <i class="fas fa-laptop-medical"></i> Adicionar Equipamento
                </a>
                <a href="equipamentos/" class="btn btn-info">
                    <i class="fas fa-exchange-alt"></i> Gerenciar Atribuições
                </a>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="recent-activities">
            <div class="section-header" style="padding: var(--spacing-lg) var(--spacing-lg) 0;">
                <h2><i class="fas fa-history"></i> Atividade Recente</h2>
                <p>Últimos equipamentos cadastrados</p>
            </div>
            <div class="activity-list">
                <?php if (empty($equipamentosRecentes)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Nenhum equipamento cadastrado ainda</p>
                    <a href="equipamentos/adicionar.php" class="btn btn-primary">Adicionar primeiro equipamento</a>
                </div>
                <?php else: ?>
                    <?php foreach ($equipamentosRecentes as $equipamento): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-laptop"></i>
                        </div>
                        <div class="activity-content">
                            <p>
                                <strong><?php echo htmlspecialchars($equipamento['marca'] . ' ' . $equipamento['modelo']); ?></strong>
                                (Patrimônio: <?php echo htmlspecialchars($equipamento['patrimonio']); ?>)
                            </p>
                            <div>
                                <span class="activity-time">
                                    <i class="fas fa-calendar-alt"></i> 
                                    <?php echo formatarData($equipamento['data_cadastro']); ?>
                                </span>
                                <span class="status-badge <?php echo $equipamento['status'] === 'estoque' ? 'status-ativo' : 'status-inativo'; ?>">
                                    <?php echo $equipamento['status'] === 'estoque' ? 'Em Estoque' : 'Atribuído'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3><i class="fas fa-laptop-house"></i> Sistema de Gestão</h3>
                <p>Controle de colaboradores e equipamentos</p>
            </div>
            
            <div class="footer-section">
                <h3>Links Rápidos</h3>
                <ul class="footer-links">
                    <li><a href="home.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="colaboradores/home.php"><i class="fas fa-users"></i> Colaboradores</a></li>
                    <li><a href="equipamentos/home.php"><i class="fas fa-laptop"></i> Equipamentos</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Estatísticas</h3>
                <div class="footer-stats">
                    <div class="footer-stat">
                        <span class="stat-number"><?php echo $totalColaboradores; ?></span>
                        <span class="stat-label">Colaboradores</span>
                    </div>
                    <div class="footer-stat">
                        <span class="stat-number"><?php echo $totalEquipamentos; ?></span>
                        <span class="stat-label">Equipamentos</span>
                    </div>
                    <div class="footer-stat">
                        <span class="stat-number"><?php echo $totalEstoque; ?></span>
                        <span class="stat-label">Em Estoque</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
            <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
        </div>
    </footer>
    <script src="js/script.js"></script>
    
    <script>
        // Fechar alerta após 5 segundos
        setTimeout(function() {
            const alert = document.querySelector('.global-alert');
            if (alert) {
                alert.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
        
        // Slide out animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>