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

// Verificar se o colaborador tem equipamentos atribuídos
$equipamentosAtribuidos = [];
foreach ($equipamentos as $equipamento) {
    if ($equipamento['colaborador_id'] == $id) {
        $equipamentosAtribuidos[] = $equipamento;
    }
}

// Encontrar colaborador
$colaboradorIndex = null;
foreach ($colaboradores as $index => $colaborador) {
    if ($colaborador['id'] == $id) {
        $colaboradorIndex = $index;
        $colaboradorAtual = $colaborador;
        break;
    }
}

if ($colaboradorIndex === null) {
    header('Location: index.php');
    exit;
}

$mensagem = '';
$tipoMensagem = '';

// Processar exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirmar'])) {
        // Primeiro, devolver todos os equipamentos para o estoque
        foreach ($equipamentos as &$equipamento) {
            if ($equipamento['colaborador_id'] == $id) {
                $equipamento['colaborador_id'] = null;
                $equipamento['status'] = 'estoque';
                $equipamento['data_atribuicao'] = null;
            }
        }
        
        // Salvar equipamentos atualizados
        salvarArquivoJSON('../data/equipamentos.json', $equipamentos);
        
        // Remover colaborador
        unset($colaboradores[$colaboradorIndex]);
        $colaboradores = array_values($colaboradores); // Reindexar array
        
        // Salvar colaboradores
        if (salvarArquivoJSON('../data/colaboradores.json', $colaboradores)) {
            $_SESSION['mensagem'] = 'Colaborador excluído com sucesso!';
            $_SESSION['mensagem_tipo'] = 'success';
            header('Location: index.php');
            exit;
        } else {
            $mensagem = 'Erro ao excluir o colaborador. Tente novamente.';
            $tipoMensagem = 'error';
        }
    } else {
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
    <title>Excluir Colaborador - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/colaboradores/excluir.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../img/favicon/favicon.jpg">
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
                <li class="nav-item">
                    <a href="../linhas/index.php" class="nav-link">
                        <i class="fas fa-phone"></i>
                        <span>Linhas</span>
                    </a>
                </li>
            </ul>
        </nav>
    </header>
    
    <!-- Mensagens de alerta -->
    <?php if ($mensagem): ?>
    <div class="global-alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : 'error'; ?>">
        <div class="alert-content">
            <i class="fas fa-<?php echo $tipoMensagem === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <span><?php echo $mensagem; ?></span>
        </div>
        <button class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
    <?php endif; ?>
    
    <!-- ==================== CONTEÚDO PRINCIPAL ==================== -->
    <main class="main-container">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-trash-alt"></i> Excluir Colaborador</h1>
                <p class="page-subtitle">Esta ação é irreversível. Confirme os dados antes de prosseguir.</p>
            </div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>
        
        <!-- Alerta de Atenção -->
        <div class="alert-warning-card">
            <div class="alert-warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="alert-warning-content">
                <strong>Atenção!</strong> Esta ação não pode ser desfeita. Todos os dados do colaborador seram removidos.
            </div>
        </div>
        
        <div class="confirmation-card">
            <h2><i class="fas fa-question-circle"></i> Confirmar Exclusão</h2>
            
            <!-- Dados do Colaborador -->
            <div class="info-card">
                <h3><i class="fas fa-user"></i> Dados do Colaborador</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Nome:</span>
                        <span class="info-value"><?php echo htmlspecialchars($colaboradorAtual['nome']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Cargo:</span>
                        <span class="info-value"><?php echo htmlspecialchars($colaboradorAtual['cargo']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">CPF:</span>
                        <span class="info-value"><?php echo formatarCPF($colaboradorAtual['cpf']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Departamento:</span>
                        <span class="info-value"><?php echo htmlspecialchars($colaboradorAtual['departamento']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Centro de Custo:</span>
                        <span class="info-value"><?php echo htmlspecialchars($colaboradorAtual['centro_custo']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Data de Cadastro:</span>
                        <span class="info-value"><?php echo formatarData($colaboradorAtual['data_cadastro']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Equipamentos Atribuídos -->
            <?php if (!empty($equipamentosAtribuidos)): ?>
            <div class="warning-card">
                <div class="warning-header">
                    <i class="fas fa-laptop"></i>
                    <h4>Equipamentos Atribuídos</h4>
                </div>
                <p>Este colaborador possui <strong><?php echo count($equipamentosAtribuidos); ?> equipamento(s)</strong> atribuído(s).</p>
                <p class="warning-message">Ao excluir o colaborador, todos os equipamentos serão <strong>devolvidos automaticamente para o estoque</strong>.</p>
                
                <div class="equipamentos-list">
                    <h5><i class="fas fa-list"></i> Lista de equipamentos:</h5>
                    <ul>
                        <?php foreach ($equipamentosAtribuidos as $equipamento): ?>
                        <li>
                            <i class="fas fa-laptop"></i>
                            <span class="equipamento-nome"><?php echo htmlspecialchars($equipamento['marca'] . ' ' . $equipamento['modelo']); ?></span>
                            <span class="equipamento-patrimonio">(Patrimônio: <?php echo htmlspecialchars($equipamento['patrimonio']); ?>)</span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Formulário de Confirmação -->
            <form method="POST" action="" class="confirmation-form" id="form-exclusao">
                <div class="confirmation-checkbox">
                    <label class="checkbox-label">
                        <input type="checkbox" id="confirm-checkbox" required>
                        <span class="checkbox-custom"></span>
                        <span class="checkbox-text">Confirmo que estou ciente de que esta ação não pode ser desfeita e que todos os dados serão removidos permanentemente.</span>
                    </label>
                </div>
                
                <div class="confirmation-actions">
                    <button type="submit" name="confirmar" value="1" class="btn btn-danger" id="btn-confirmar" disabled>
                        <i class="fas fa-trash-alt"></i>
                        <span>Confirmar Exclusão</span>
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        <span>Cancelar</span>
                    </a>
                </div>
            </form>
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

    <script src="../js/script.js"></script>
    
    <script>
        // Habilitar botão de confirmação apenas quando checkbox estiver marcado
        const checkbox = document.getElementById('confirm-checkbox');
        const confirmBtn = document.getElementById('btn-confirmar');
        
        if (checkbox && confirmBtn) {
            checkbox.addEventListener('change', function() {
                confirmBtn.disabled = !this.checked;
            });
        }
        
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