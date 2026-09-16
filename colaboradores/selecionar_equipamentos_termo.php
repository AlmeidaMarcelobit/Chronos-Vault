<?php
session_start();
require_once '../includes/funcoes.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$colaboradores = lerArquivoJSON('../data/colaboradores.json');
$equipamentos = lerArquivoJSON('../data/equipamentos.json');

// Encontrar colaborador
$colaborador = null;
foreach ($colaboradores as $colab) {
    if ($colab['id'] == $id) {
        $colaborador = $colab;
        break;
    }
}

if (!$colaborador) {
    $_SESSION['mensagem'] = 'Colaborador não encontrado!';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

// Buscar equipamentos alocados ao colaborador
$equipamentosColaborador = [];
foreach ($equipamentos as $equip) {
    if ($equip['colaborador_id'] == $id && in_array($equip['status'], ['alocado', 'emprestado'])) {
        $equipamentosColaborador[] = $equip;
    }
}

// Processar seleção
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['equipamentos_selecionados'])) {
    $equipamentosSelecionados = $_POST['equipamentos_selecionados'];

    // Armazenar na sessão
    $_SESSION['termo_equipamentos_selecionados'] = $equipamentosSelecionados;
    $_SESSION['termo_colaborador_id'] = $id;

    // Redirecionar para gerar termo
    header('Location: gerar_termo_selecionados.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecionar Equipamentos - Termo de Responsabilidade</title>
    <link rel="stylesheet" href="../css/colaboradores/selecionar_termo.css">
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
                    <a href="index.php" class="nav-link active">
                        <i class="fas fa-users"></i>
                        <span>Colaboradores</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../equipamentos/index.php" class="nav-link">
                        <i class="fas fa-laptop"></i>
                        <span>Equipamentos</span>
                    </a>
                </li>
            </ul>
        </nav>
    </header>
    
    <!-- ==================== CONTEÚDO PRINCIPAL ==================== -->
    <main class="main-container">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-file-contract"></i> Selecionar Equipamentos</h1>
                <p class="page-subtitle">Selecione os equipamentos que deseja incluir no termo de responsabilidade</p>
            </div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>

        <!-- Informações do Colaborador -->
        <div class="info-card">
            <h3><i class="fas fa-user-circle"></i> Dados do Colaborador</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Nome:</span>
                    <span class="info-value"><?php echo htmlspecialchars($colaborador['nome']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">CPF:</span>
                    <span class="info-value"><?php echo formatarCPF($colaborador['cpf']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Cargo:</span>
                    <span class="info-value"><?php echo htmlspecialchars($colaborador['cargo']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Departamento:</span>
                    <span class="info-value"><?php echo htmlspecialchars($colaborador['departamento']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Centro de Custo:</span>
                    <span class="info-value"><?php echo htmlspecialchars($colaborador['centro_custo']); ?></span>
                </div>
            </div>
        </div>

        <?php if (empty($equipamentosColaborador)): ?>
            <!-- Estado Vazio -->
            <div class="empty-state-card">
                <div class="empty-state-icon">
                    <i class="fas fa-laptop"></i>
                </div>
                <h3>Nenhum equipamento atribuído</h3>
                <p>Este colaborador não possui equipamentos alocados no momento.</p>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i>
                    <span>Voltar</span>
                </a>
            </div>
        <?php else: ?>
            <!-- Formulário de Seleção -->
            <form method="POST" id="formSelecao" class="selection-form">
                <div class="selection-header">
                    <div class="checkbox-all">
                        <label class="checkbox-label-all">
                            <input type="checkbox" id="selecionarTodos" onclick="toggleAll()">
                            <span class="checkbox-custom-all"></span>
                            <strong>Selecionar todos os equipamentos</strong>
                            <span class="selected-count" id="contador">0 selecionado(s)</span>
                        </label>
                    </div>
                </div>

                <div class="equipamentos-grid">
                    <h3 class="equipamentos-title">
                        <i class="fas fa-laptop"></i>
                        Equipamentos Disponíveis
                    </h3>
                    
                    <?php foreach ($equipamentosColaborador as $equipamento): ?>
                    <div class="equipamento-card">
                        <div class="equipamento-checkbox">
                            <input type="checkbox"
                                   name="equipamentos_selecionados[]"
                                   value="<?php echo $equipamento['id']; ?>"
                                   class="checkbox-equipamento"
                                   data-patrimonio="<?php echo htmlspecialchars($equipamento['patrimonio']); ?>"
                                   onchange="atualizarContador()"
                                   id="equip_<?php echo $equipamento['id']; ?>">
                            <label for="equip_<?php echo $equipamento['id']; ?>" class="checkbox-custom"></label>
                        </div>
                        <div class="equipamento-detalhes">
                            <div class="equipamento-header">
                                <span class="equipamento-tipo"><?php echo getTipoTexto($equipamento['tipo']); ?></span>
                                <span class="equipamento-patrimonio-badge">Patrimônio: <?php echo htmlspecialchars($equipamento['patrimonio']); ?></span>
                            </div>
                            <div class="equipamento-nome">
                                <strong><?php echo htmlspecialchars($equipamento['marca'] . ' ' . $equipamento['modelo']); ?></strong>
                            </div>
                            <div class="equipamento-info-detalhes">
                                <?php if (!empty($equipamento['serial'])): ?>
                                <span class="info-badge">
                                    <i class="fas fa-barcode"></i> Série: <?php echo htmlspecialchars($equipamento['serial']); ?>
                                </span>
                                <?php endif; ?>
                                <span class="info-badge">
                                    <i class="fas fa-dollar-sign"></i> CC: <?php echo htmlspecialchars($equipamento['centro_custo']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        <span>Cancelar</span>
                    </a>
                    <button type="submit" class="btn btn-primary" id="btnGerarTermo" disabled>
                        <i class="fas fa-file-contract"></i>
                        <span>Gerar Termo com Itens Selecionados</span>
                    </button>
                </div>
            </form>
        <?php endif; ?>
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
                    <li><a href="index.php"><i class="fas fa-users"></i> Colaboradores</a></li>
                    <li><a href="../equipamentos/index.php"><i class="fas fa-laptop"></i> Equipamentos</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Estatísticas</h3>
                <?php
                // Carregar dados para estatísticas
                $total_colaboradores = count(lerArquivoJSON('../data/colaboradores.json'));
                $total_equipamentos = count(lerArquivoJSON('../data/equipamentos.json'));
                $equipamentos_estoque = 0;
                $equipamentos_data = lerArquivoJSON('../data/equipamentos.json');
                foreach ($equipamentos_data as $e) {
                    if (($e['status'] ?? '') === 'estoque') $equipamentos_estoque++;
                }
                ?>
                <div class="footer-stats">
                    <div class="footer-stat">
                        <span class="stat-number"><?php echo $total_colaboradores; ?></span>
                        <span class="stat-label">Colaboradores</span>
                    </div>
                    <div class="footer-stat">
                        <span class="stat-number"><?php echo $total_equipamentos; ?></span>
                        <span class="stat-label">Equipamentos</span>
                    </div>
                    <div class="footer-stat">
                        <span class="stat-number"><?php echo $equipamentos_estoque; ?></span>
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

    <script>
        function toggleAll() {
            const checkAll = document.getElementById('selecionarTodos');
            const checkboxes = document.querySelectorAll('.checkbox-equipamento');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = checkAll.checked;
            });
            
            atualizarContador();
        }
        
        function atualizarContador() {
            const checkboxes = document.querySelectorAll('.checkbox-equipamento:checked');
            const contador = document.getElementById('contador');
            const btnGerar = document.getElementById('btnGerarTermo');
            
            contador.textContent = checkboxes.length + ' selecionado(s)';
            
            // Habilitar/desabilitar botão
            btnGerar.disabled = checkboxes.length === 0;
            
            // Atualizar checkbox "selecionar todos"
            const totalCheckboxes = document.querySelectorAll('.checkbox-equipamento').length;
            const checkAll = document.getElementById('selecionarTodos');
            
            if (checkboxes.length === totalCheckboxes && totalCheckboxes > 0) {
                checkAll.checked = true;
                checkAll.indeterminate = false;
            } else if (checkboxes.length > 0) {
                checkAll.checked = false;
                checkAll.indeterminate = true;
            } else {
                checkAll.checked = false;
                checkAll.indeterminate = false;
            }
        }
        
        // Inicializar contador
        document.addEventListener('DOMContentLoaded', atualizarContador);
    </script>
</body>
</html>