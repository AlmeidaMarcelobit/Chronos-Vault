<?php
session_start();
require_once '../includes/funcoes.php';
verificarSessao();

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
if ($equipamento['colaborador_id'] && isset($colaboradores[$equipamento['colaborador_id'] - 1])) {
    $colaborador = $colaboradores[$equipamento['colaborador_id'] - 1];
    $colaboradorNome = $colaborador['nome'] . ' (' . $colaborador['departamento'] . ')';
}

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
    <link rel="stylesheet" href="../css/equipamentos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="page-header">
            <h1><i class="fas fa-undo"></i> Devolver Equipamento</h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
        
        <div class="confirmation-card">
            <h2>Confirmar Devolução</h2>
            
            <div class="equipamento-details">
                <h3>Detalhes do Equipamento</h3>
                <div class="details-grid">
                    <div class="detail-item">
                        <span class="detail-label">Patrimônio:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($equipamento['patrimonio']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Tipo:</span>
                        <span class="detail-value"><?php echo getTipoTexto($equipamento['tipo']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Marca/Modelo:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($equipamento['marca'] . ' ' . $equipamento['modelo']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Status Atual:</span>
                        <span class="status-badge status-inativo"><?php echo getStatusTexto($equipamento['status']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Colaborador:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($colaboradorNome); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Data de Atribuição:</span>
                        <span class="detail-value"><?php echo formatarData($equipamento['data_atribuicao'] ?? ''); ?></span>
                    </div>
                    <?php if ($equipamento['status'] === 'emprestado' && isset($equipamento['data_devolucao_prevista'])): ?>
                    <div class="detail-item">
                        <span class="detail-label">Devolução Prevista:</span>
                        <span class="detail-value"><?php echo formatarData($equipamento['data_devolucao_prevista']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <form method="POST" action="" class="confirmation-form">
                <div class="form-group">
                    <label for="novo_status"><i class="fas fa-exchange-alt"></i> Novo Status após Devolução *</label>
                    <select id="novo_status" name="novo_status" required class="form-select">
                        <option value="estoque" selected>Em Estoque (disponível para uso)</option>
                        <option value="manutencao">Em Manutenção (precisa de conserto)</option>
                        <option value="fora_uso">Fora de Uso (quebrado/obsoleto)</option>
                    </select>
                    <small class="form-text">Selecione onde o equipamento ficará após a devolução</small>
                </div>
                
                <div class="form-group">
                    <label for="observacoes"><i class="fas fa-sticky-note"></i> Observações da Devolução</label>
                    <textarea id="observacoes" name="observacoes" class="form-control" 
                              rows="3" placeholder="Condição do equipamento, motivos da devolução, problemas identificados..."></textarea>
                </div>
                
                <?php if (isset($erro)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?>
                </div>
                <?php endif; ?>
                
                <div class="confirmation-info">
                    <h4><i class="fas fa-exclamation-triangle"></i> Atenção!</h4>
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
                
                <div class="confirmation-actions">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-check"></i> Confirmar Devolução
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
                
                <div class="confirmation-checkbox">
                    <label>
                        <input type="checkbox" id="confirmCheckbox" required>
                        Confirmo que estou ciente de que esta ação removerá o vínculo com o colaborador e alterará o status do equipamento.
                    </label>
                </div>
            </form>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../js/script.js"></script>
    
    <script>
    // Habilitar/desabilitar botão baseado no checkbox
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('confirmCheckbox');
        const submitBtn = document.querySelector('.btn-danger');
        
        if (checkbox && submitBtn) {
            checkbox.addEventListener('change', function() {
                submitBtn.disabled = !this.checked;
            });
            
            // Desabilitar inicialmente
            submitBtn.disabled = true;
        }
        
        // Validação do formulário
        const form = document.querySelector('.confirmation-form');
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
    </script>
    
    <style>
    .confirmation-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 30px;
        box-shadow: var(--box-shadow);
        margin-top: 20px;
    }
    
    .confirmation-card h2 {
        color: var(--danger-color);
        margin-bottom: 20px;
        text-align: center;
    }
    
    .equipamento-details {
        margin: 30px 0;
        padding: 20px;
        background: #f8f9fa;
        border-radius: var(--border-radius);
    }
    
    .equipamento-details h3 {
        color: var(--dark-color);
        margin-bottom: 15px;
    }
    
    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }
    
    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .detail-label {
        font-size: 12px;
        color: var(--gray-color);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .detail-value {
        font-weight: 500;
        color: var(--dark-color);
    }
    
    .confirmation-info {
        margin: 20px 0;
        padding: 15px;
        background: #fff3cd;
        border-radius: var(--border-radius);
        border-left: 4px solid #ffc107;
    }
    
    .confirmation-info h4 {
        color: #856404;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .confirmation-info ul {
        margin: 10px 0 0 20px;
        color: #856404;
    }
    
    .confirmation-info li {
        margin-bottom: 5px;
    }
    
    .confirmation-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin: 30px 0;
    }
    
    .confirmation-checkbox {
        text-align: center;
        padding: 15px;
        background: #f8f9fa;
        border-radius: var(--border-radius);
        margin-top: 20px;
    }
    
    .confirmation-checkbox label {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        cursor: pointer;
        text-align: left;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-ativo {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .status-inativo {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .status-info {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
    
    .status-warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
    
    .status-danger {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .btn-danger:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    @media (max-width: 768px) {
        .confirmation-checkbox label {
            flex-direction: column;
            text-align: center;
            gap: 5px;
        }
        
        .confirmation-actions {
            flex-direction: column;
        }
        
        .confirmation-actions .btn {
            width: 100%;
        }
    }
    </style>
</body>
</html>