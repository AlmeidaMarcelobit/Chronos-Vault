<?php
session_start();
require_once 'includes/funcoes.php';
verificarSessao();

$colaboradores = lerArquivoJSON('data/colaboradores.json');
$equipamentos = lerArquivoJSON('data/equipamentos.json');

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

$page_title = 'Dashboard - Sistema de Gestão';
$page_css   = 'css/home.css';
$page_js    = 'js/index.js';

include 'includes/header.php';
?>

<main class="container">
    <div class="dashboard">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>

        <div class="cards">
            <div class="card card-primary">
                <div class="card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="card-content">
                    <h3>Colaboradores</h3>
                    <p class="card-number"><?php echo $totalColaboradores; ?></p>
                    <a href="colaboradores/" class="card-link">Ver todos</a>
                </div>
            </div>

            <div class="card card-success">
                <div class="card-icon">
                    <i class="fas fa-laptop"></i>
                </div>
                <div class="card-content">
                    <h3>Equipamentos</h3>
                    <p class="card-number"><?php echo $totalEquipamentos; ?></p>
                    <a href="equipamentos/" class="card-link">Ver todos</a>
                </div>
            </div>

            <div class="card card-warning">
                <div class="card-icon">
                    <i class="fas fa-warehouse"></i>
                </div>
                <div class="card-content">
                    <h3>Em Estoque</h3>
                    <p class="card-number"><?php echo $totalEstoque; ?></p>
                    <a href="equipamentos/?filtro=estoque" class="card-link">Ver estoque</a>
                </div>
            </div>

            <div class="card card-info">
                <div class="card-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="card-content">
                    <h3>Atribuídos</h3>
                    <p class="card-number"><?php echo $totalAtribuidos; ?></p>
                    <a href="equipamentos/?filtro=atribuidos" class="card-link">Ver atribuídos</a>
                </div>
            </div>
        </div>

        <div class="quick-actions">
            <h2><i class="fas fa-bolt"></i> Ações Rápidas</h2>
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

        <div class="recent-activity">
            <h2><i class="fas fa-history"></i> Atividade Recente</h2>
            <div class="activity-list">
                <?php
                $equipamentosRecentes = array_slice($equipamentos, -5, 5, true);
                $equipamentosRecentes = array_reverse($equipamentosRecentes, true);

                foreach ($equipamentosRecentes as $equipamento):
                    $statusClass = $equipamento['status'] === 'estoque' ? 'status-ativo' : 'status-inativo';
                    $statusText  = $equipamento['status'] === 'estoque' ? 'Em Estoque' : 'Atribuído';
                ?>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-laptop"></i>
                    </div>
                    <div class="activity-content">
                        <p><strong><?php echo htmlspecialchars($equipamento['marca'] . ' ' . $equipamento['modelo']); ?></strong>
                        (Patrimônio: <?php echo htmlspecialchars($equipamento['patrimonio']); ?>)</p>
                        <span class="activity-time"><?php echo formatarData($equipamento['data_cadastro']); ?></span>
                        <span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
