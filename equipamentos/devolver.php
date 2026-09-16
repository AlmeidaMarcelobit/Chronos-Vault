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

$equipamentos = lerArquivoJSON('../data/equipamentos.json');
$colaboradores = lerArquivoJSON('../data/colaboradores.json');

// Verificar se os arrays foram carregados corretamente
if ($equipamentos === false) {
    $equipamentos = [];
}

if ($colaboradores === false) {
    $colaboradores = [];
}

// Encontrar equipamento
$equipamentoIndex = null;
foreach ($equipamentos as $index => $equip) {
    if ($equip['id'] == $id) {
        $equipamentoIndex = $index;
        $equipamento = $equip;
        break;
    }
}

if ($equipamentoIndex === null) {
    $_SESSION['mensagem'] = 'Equipamento não encontrado!';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

// Verificar se o equipamento pode ser devolvido
$statusPermitidos = ['alocado', 'emprestado'];
if (!in_array($equipamento['status'], $statusPermitidos)) {
    $_SESSION['mensagem'] = 'Este equipamento não pode ser devolvido! Status atual: ' . getStatusTexto($equipamento['status']);
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

// Obter informações do colaborador
$colaboradorNome = 'N/A';
$colaboradorInfo = null;
if ($equipamento['colaborador_id']) {
    foreach ($colaboradores as $colaborador) {
        if ($colaborador['id'] == $equipamento['colaborador_id']) {
            $colaboradorInfo = $colaborador;
            $colaboradorNome = $colaborador['nome'] . ' (' . $colaborador['departamento'] . ')';
            break;
        }
    }
}

$erro = '';

// Processar devolução
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novo_status = $_POST['novo_status'] ?? 'estoque';
    $observacoes = trim($_POST['observacoes'] ?? '');
    
    // Validação
    if (!in_array($novo_status, ['estoque', 'manutencao', 'fora_uso'])) {
        $erro = 'Status de destino inválido.';
    } else {
        // Atualizar equipamento
        $equipamentos[$equipamentoIndex]['colaborador_id'] = null;
        $equipamentos[$equipamentoIndex]['status'] = $novo_status;
        $equipamentos[$equipamentoIndex]['data_atribuicao'] = null;
        $equipamentos[$equipamentoIndex]['data_atualizacao'] = date('Y-m-d H:i:s');
        
        // Limpar campos específicos de empréstimo
        if (isset($equipamentos[$equipamentoIndex]['data_devolucao_prevista'])) {
            unset($equipamentos[$equipamentoIndex]['data_devolucao_prevista']);
        }
        if (isset($equipamentos[$equipamentoIndex]['tipo_atribuicao'])) {
            unset($equipamentos[$equipamentoIndex]['tipo_atribuicao']);
        }
        
        // Adicionar observação sobre a devolução
        $observacao_devolucao = "\n\n[DEVOLUÇÃO] " . date('d/m/Y H:i:s');
        $observacao_devolucao .= "\nStatus anterior: " . getStatusTexto($equipamento['status']);
        $observacao_devolucao .= "\nColaborador: " . $colaboradorNome;
        $observacao_devolucao .= "\nNovo status: " . getStatusTexto($novo_status);
        
        if (!empty($observacoes)) {
            $observacao_devolucao .= "\nObservações: " . $observacoes;
        }
        
        // Adicionar ao histórico de observações
        $observacoesAtuais = $equipamentos[$equipamentoIndex]['observacoes'] ?? '';
        $equipamentos[$equipamentoIndex]['observacoes'] = $observacoesAtuais . 
            (empty($observacoesAtuais) ? '' : "\n\n") . 
            $observacao_devolucao;
        
        // Salvar no JSON
        if (salvarArquivoJSON('../data/equipamentos.json', $equipamentos)) {
            $_SESSION['mensagem'] = 'Equipamento devolvido com sucesso! Status atualizado para: ' . getStatusTexto($novo_status);
            $_SESSION['mensagem_tipo'] = 'success';
            
            header('Location: index.php');
            exit;
        } else {
            $erro = 'Erro ao salvar as alterações. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devolver Equipamento - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/equipamentos/devolver.css">
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
            </ul>
        </nav>
    </header>
    
    <!-- ==================== CONTEÚDO PRINCIPAL ==================== -->
    <main class="main-container">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-undo-alt"></i> Devolver Equipamento</h1>
                <p class="page-subtitle">Confirme a devolução do equipamento ao estoque</p>
            </div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>
        
        <div class="confirmation-card">
            <h2><i class="fas fa-question-circle"></i> Confirmar Devolução</h2>
            
            <!-- Detalhes do Equipamento -->
            <div class="info-card equipment-details-card">
                <h3><i class="fas fa-laptop"></i> Detalhes do Equipamento</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Patrimônio:</span>
                        <span class="info-value"><?php echo htmlspecialchars($equipamento['patrimonio']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tipo:</span>
                        <span class="info-value"><?php echo getTipoTexto($equipamento['tipo']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Marca/Modelo:</span>
                        <span class="info-value"><?php echo htmlspecialchars($equipamento['marca'] . ' ' . $equipamento['modelo']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status Atual:</span>
                        <span class="info-value">
                            <span class="status-badge status-<?php echo $equipamento['status'] === 'alocado' ? 'inativo' : 'info'; ?>">
                                <i class="fas fa-<?php echo $equipamento['status'] === 'alocado' ? 'user-check' : 'handshake'; ?>"></i>
                                <?php echo getStatusTexto($equipamento['status']); ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Colaborador:</span>
                        <span class="info-value">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($colaboradorNome); ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Data de Atribuição:</span>
                        <span class="info-value"><?php echo formatarData($equipamento['data_atribuicao'] ?? ''); ?></span>
                    </div>
                    <?php if ($equipamento['status'] === 'emprestado' && isset($equipamento['data_devolucao_prevista'])): ?>
                    <div class="info-item">
                        <span class="info-label">Devolução Prevista:</span>
                        <span class="info-value"><?php echo formatarData($equipamento['data_devolucao_prevista']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Formulário -->
            <form method="POST" action="" class="confirmation-form" id="form-devolucao">
                <div class="form-group">
                    <label for="novo_status">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Novo Status após Devolução</span>
                        <span class="required">*</span>
                    </label>
                    <select id="novo_status" name="novo_status" required class="form-select">
                        <option value="estoque" selected>Em Estoque (disponível para uso)</option>
                        <option value="manutencao">Em Manutenção (precisa de conserto)</option>
                        <option value="fora_uso">Fora de Uso (quebrado/obsoleto)</option>
                    </select>
                    <small class="form-text">Selecione onde o equipamento ficará após a devolução</small>
                </div>
                
                <div class="form-group">
                    <label for="observacoes">
                        <i class="fas fa-sticky-note"></i>
                        <span>Observações da Devolução</span>
                    </label>
                    <textarea id="observacoes" name="observacoes" class="form-control" 
                              rows="3" placeholder="Condição do equipamento, motivos da devolução, problemas identificados..."></textarea>
                </div>
                
                <?php if (!empty($erro)): ?>
                <div class="alert-error-card">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $erro; ?></span>
                </div>
                <?php endif; ?>
                
                <!-- Card de Atenção -->
                <div class="warning-card">
                    <div class="warning-header">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h4>Atenção!</h4>
                    </div>
                    <p>Ao devolver este equipamento:</p>
                    <ul>
                        <li>O vínculo com o colaborador <strong><?php echo htmlspecialchars($colaboradorNome); ?></strong> será removido</li>
                        <li>O equipamento sairá do status atual (<strong><?php echo getStatusTexto($equipamento['status']); ?></strong>)</li>
                        <?php if ($equipamento['status'] === 'emprestado'): ?>
                        <li>O empréstimo será finalizado</li>
                        <?php endif; ?>
                        <li>Um registro da devolução será adicionado às observações</li>
                        <li>Esta ação <strong>não pode ser desfeita</strong></li>
                    </ul>
                </div>
                
                <div class="confirmation-checkbox">
                    <label class="checkbox-label">
                        <input type="checkbox" id="confirmCheckbox" required>
                        <span class="checkbox-custom"></span>
                        <span class="checkbox-text">Confirmo que estou ciente de que esta ação removerá o vínculo com o colaborador e alterará o status do equipamento.</span>
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-danger" id="btnConfirmar" disabled>
                        <i class="fas fa-check-circle"></i>
                        <span>Confirmar Devolução</span>
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
                    <li><a href="../colaboradores/index.php"><i class="fas fa-users"></i> Colaboradores</a></li>
                    <li><a href="index.php"><i class="fas fa-laptop"></i> Equipamentos</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Estatísticas</h3>
                <?php
                $total_equipamentos = count(lerArquivoJSON('../data/equipamentos.json'));
                $equipamentos_data = lerArquivoJSON('../data/equipamentos.json');
                $equipamentos_estoque = 0;
                foreach ($equipamentos_data as $e) {
                    if (($e['status'] ?? '') === 'estoque') $equipamentos_estoque++;
                }
                ?>
                <div class="footer-stats">
                    <div class="footer-stat">
                        <span class="stat-number"><?php echo $total_equipamentos; ?></span>
                        <span class="stat-label">Equipamentos</span>
                    </div>
                    <div class="footer-stat">
                        <span class="stat-number"><?php echo $equipamentos_estoque; ?></span>
                        <span class="stat-label">Em Estoque</span>
                    </div>
                    <div class="footer-stat">
                        <span class="stat-number"><?php echo count($colaboradores); ?></span>
                        <span class="stat-label">Colaboradores</span>
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
        // Habilitar/desabilitar botão baseado no checkbox
        document.addEventListener('DOMContentLoaded', function() {
            const checkbox = document.getElementById('confirmCheckbox');
            const submitBtn = document.getElementById('btnConfirmar');
            
            if (checkbox && submitBtn) {
                checkbox.addEventListener('change', function() {
                    submitBtn.disabled = !this.checked;
                });
            }
            
            // Validação do formulário
            const form = document.getElementById('form-devolucao');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const novoStatus = document.getElementById('novo_status').value;
                    const confirmado = document.getElementById('confirmCheckbox').checked;
                    
                    if (!confirmado) {
                        alert('Você precisa confirmar que está ciente das consequências desta ação.');
                        e.preventDefault();
                        return false;
                    }
                    
                    if (novoStatus === '') {
                        alert('Selecione o novo status do equipamento.');
                        e.preventDefault();
                        return false;
                    }
                    
                    return true;
                });
            }
        });
        
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