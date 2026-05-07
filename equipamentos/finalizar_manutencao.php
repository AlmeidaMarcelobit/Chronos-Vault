<?php
session_start();
require_once '../includes/funcoes.php';
verificarSessao();

$id = $_GET['id'] ?? null;
if (!$id) {
    $_SESSION['mensagem'] = 'Equipamento não especificado.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

$equipamentos = lerArquivoJSON('../data/equipamentos.json');
$colaboradores = lerArquivoJSON('../data/colaboradores.json');

// Encontrar equipamento
$equipamentoIndex = null;
$equipamento = null;
foreach ($equipamentos as $index => $equip) {
    if ($equip['id'] == $id) {
        $equipamentoIndex = $index;
        $equipamento = $equip;
        break;
    }
}

if ($equipamentoIndex === null) {
    $_SESSION['mensagem'] = 'Equipamento não encontrado.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

// Verificar se o equipamento está em manutenção
if ($equipamento['status'] !== 'manutencao') {
    $_SESSION['mensagem'] = 'Este equipamento não está em manutenção.';
    $_SESSION['mensagem_tipo'] = 'warning';
    header('Location: index.php');
    exit;
}

// Encontrar a última manutenção ativa
$ultimaManutencao = null;
$manutencaoIndex = null;

if (isset($equipamento['historico_manutencao']) && is_array($equipamento['historico_manutencao'])) {
    $historico = $equipamento['historico_manutencao'];
    
    // Ordenar por data_envio decrescente para pegar a mais recente
    usort($historico, function($a, $b) {
        return strtotime($b['data_envio']) - strtotime($a['data_envio']);
    });
    
    // Buscar a primeira manutenção sem data de retorno (mais recente e ativa)
    foreach ($historico as $index => $manutencao) {
        if (isset($manutencao['data_envio']) && empty($manutencao['data_retorno'])) {
            $ultimaManutencao = $manutencao;
            // Encontrar o índice original no array
            foreach ($equipamento['historico_manutencao'] as $originalIndex => $originalManut) {
                if ($originalManut['data_envio'] === $manutencao['data_envio'] && 
                    empty($originalManut['data_retorno'])) {
                    $manutencaoIndex = $originalIndex;
                    break;
                }
            }
            break;
        }
    }
}

if (!$ultimaManutencao) {
    $_SESSION['mensagem'] = 'Não foi encontrada uma manutenção ativa para este equipamento.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

// Verificar se deve devolver automaticamente ao colaborador
$devolverAutomaticamente = false;
$colaboradorDevolucao = null;

if (isset($ultimaManutencao['manter_com_colaborador']) && $ultimaManutencao['manter_com_colaborador'] === true) {
    $devolverAutomaticamente = true;
    if (isset($ultimaManutencao['colaborador_atual']) && $ultimaManutencao['colaborador_atual']) {
        foreach ($colaboradores as $colaborador) {
            if ($colaborador['id'] == $ultimaManutencao['colaborador_atual']) {
                $colaboradorDevolucao = $colaborador;
                break;
            }
        }
    }
}

// Buscar informações do colaborador atual (se houver)
$colaboradorAtualInfo = null;
if ($equipamento['colaborador_id']) {
    foreach ($colaboradores as $colaborador) {
        if ($colaborador['id'] == $equipamento['colaborador_id']) {
            $colaboradorAtualInfo = $colaborador;
            break;
        }
    }
}

// Processar finalização da manutenção
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = trim($_POST['resultado'] ?? '');
    $custo = !empty($_POST['custo']) ? str_replace(['.', ','], ['', '.'], $_POST['custo']) : null;
    $destino = $_POST['destino'] ?? 'colaborador'; // Padrão para colaborador se houver vínculo
    $colaboradorId = $_POST['colaborador_id'] ?? $equipamento['colaborador_id'] ?? null;
    
    // Se devolução automática está ativada, forçar destino para colaborador
    if ($devolverAutomaticamente && $colaboradorDevolucao) {
        $destino = 'colaborador';
        $colaboradorId = $colaboradorDevolucao['id'];
    }
    
    // Validações
    $erros = [];
    
    if (empty($resultado)) {
        $erros[] = 'Informe o resultado da manutenção.';
    }
    
    if ($destino === 'colaborador' && empty($colaboradorId)) {
        $erros[] = 'Selecione um colaborador para atribuir o equipamento.';
    }
    
    if (empty($erros)) {
        // Atualizar o histórico de manutenção
        $equipamentos[$equipamentoIndex]['historico_manutencao'][$manutencaoIndex]['data_retorno'] = date('Y-m-d H:i:s');
        $equipamentos[$equipamentoIndex]['historico_manutencao'][$manutencaoIndex]['resultado'] = $resultado;
        $equipamentos[$equipamentoIndex]['historico_manutencao'][$manutencaoIndex]['custo'] = $custo;
        $equipamentos[$equipamentoIndex]['historico_manutencao'][$manutencaoIndex]['tecnico_finalizacao'] = $_SESSION['usuario_nome'] ?? 'Administrador';
        
        // Determinar o novo status e destino
        if ($destino === 'colaborador') {
            // Atribuir/Devolver a um colaborador
            $equipamentos[$equipamentoIndex]['status'] = $ultimaManutencao['status_anterior'] ?? 'alocado';
            $equipamentos[$equipamentoIndex]['colaborador_id'] = $colaboradorId;
            $equipamentos[$equipamentoIndex]['data_atribuicao'] = date('Y-m-d H:i:s');
            
            // Buscar nome do colaborador para a mensagem
            $colaboradorNome = '';
            foreach ($colaboradores as $colab) {
                if ($colab['id'] == $colaboradorId) {
                    $colaboradorNome = $colab['nome'];
                    break;
                }
            }
            $mensagem = "Manutenção finalizada e equipamento devolvido para {$colaboradorNome}.";
        } else {
            // Colocar em estoque
            $equipamentos[$equipamentoIndex]['status'] = 'estoque';
            $equipamentos[$equipamentoIndex]['colaborador_id'] = null;
            $equipamentos[$equipamentoIndex]['data_atribuicao'] = null;
            $mensagem = 'Manutenção finalizada e equipamento colocado em estoque.';
        }
        
        // Atualizar data de modificação
        $equipamentos[$equipamentoIndex]['data_atualizacao'] = date('Y-m-d H:i:s');
        
        // Salvar alterações
        if (salvarArquivoJSON('../data/equipamentos.json', $equipamentos)) {
            $_SESSION['mensagem'] = $mensagem;
            $_SESSION['mensagem_tipo'] = 'success';
            
            header('Location: index.php');
            exit;
        } else {
            $erros[] = 'Erro ao salvar as alterações. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Manutenção - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="page-header">
            <h1><i class="fas fa-tools"></i> Finalizar Manutenção</h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-laptop"></i> Equipamento</h3>
            </div>
            <div class="card-body">
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
                        <span class="info-label">Centro de Custo:</span>
                        <span class="info-value"><?php echo htmlspecialchars($equipamento['centro_custo']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status Atual:</span>
                        <span class="info-value">
                            <span class="status-badge status-warning">
                                <i class="fas fa-tools"></i> <?php echo getStatusTexto($equipamento['status']); ?>
                            </span>
                        </span>
                    </div>
                    <?php if ($colaboradorAtualInfo): ?>
                    <div class="info-item">
                        <span class="info-label">Vinculado a:</span>
                        <span class="info-value">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($colaboradorAtualInfo['nome']); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($ultimaManutencao): ?>
                <div class="manutencao-info mt-3">
                    <h4><i class="fas fa-wrench"></i> Informações da Manutenção</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Data de Envio:</span>
                            <span class="info-value">
                                <?php 
                                if (!empty($ultimaManutencao['data_envio'])) {
                                    echo formatarData($ultimaManutencao['data_envio']);
                                } else {
                                    echo 'Data não registrada';
                                }
                                ?>
                            </span>
                        </div>
                        <?php if (!empty($ultimaManutencao['data_envio'])): ?>
                        <div class="info-item">
                            <span class="info-label">Tempo em Manutenção:</span>
                            <span class="info-value">
                                <?php 
                                $dataEnvio = new DateTime($ultimaManutencao['data_envio']);
                                $agora = new DateTime();
                                $diferenca = $agora->diff($dataEnvio);
                                
                                $tempo = '';
                                if ($diferenca->y > 0) $tempo .= $diferenca->y . ' ano(s) ';
                                if ($diferenca->m > 0) $tempo .= $diferenca->m . ' mês(es) ';
                                if ($diferenca->d > 0) $tempo .= $diferenca->d . ' dia(s) ';
                                if ($diferenca->h > 0) $tempo .= $diferenca->h . ' hora(s) ';
                                if ($diferenca->i > 0) $tempo .= $diferenca->i . ' minuto(s) ';
                                
                                echo $tempo ?: 'Menos de 1 minuto';
                                ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($ultimaManutencao['problema'])): ?>
                        <div class="info-item full-width">
                            <span class="info-label">Problema Reportado:</span>
                            <span class="info-value"><?php echo htmlspecialchars($ultimaManutencao['problema']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($ultimaManutencao['manter_com_colaborador']) && $ultimaManutencao['manter_com_colaborador']): ?>
                        <div class="info-item full-width">
                            <span class="info-label">Configuração:</span>
                            <span class="info-value">
                                <i class="fas fa-check-circle text-success"></i> 
                                <strong>Devolução automática ativada</strong> - Equipamento será devolvido ao colaborador atual.
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <form method="POST" action="" class="mt-4">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-clipboard-check"></i> Resultado da Manutenção</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="resultado">Descrição do Serviço Realizado *</label>
                        <textarea id="resultado" name="resultado" class="form-control" 
                                  rows="4" placeholder="Descreva em detalhes o que foi feito na manutenção, peças trocadas, testes realizados, diagnóstico final..."
                                  required><?php echo htmlspecialchars($_POST['resultado'] ?? ''); ?></textarea>
                        <small class="form-text">Informe todos os procedimentos realizados durante a manutenção.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="custo">Custo da Manutenção (R$)</label>
                        <div class="input-group" style="max-width: 300px;">
                            <span class="input-group-text">R$</span>
                            <input type="text" id="custo" name="custo" 
                                   class="form-control" 
                                   placeholder="0,00"
                                   value="<?php echo htmlspecialchars($_POST['custo'] ?? ''); ?>">
                        </div>
                        <small class="form-text">Informe o custo total da manutenção (opcional).</small>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h3><i class="fas fa-map-marker-alt"></i> Destino do Equipamento</h3>
                </div>
                <div class="card-body">
                    <?php if ($devolverAutomaticamente && $colaboradorDevolucao): ?>
                        <div class="alert alert-success">
                            <h4><i class="fas fa-check-circle"></i> Devolução Automática</h4>
                            <p>
                                Este equipamento foi configurado para <strong>devolução automática</strong>.
                                Ele será devolvido automaticamente para:
                            </p>
                            <div class="destino-info">
                                <i class="fas fa-user fa-2x"></i>
                                <div>
                                    <h5><?php echo htmlspecialchars($colaboradorDevolucao['nome']); ?></h5>
                                    <p class="mb-0"><?php echo htmlspecialchars($colaboradorDevolucao['departamento']); ?></p>
                                </div>
                            </div>
                            <input type="hidden" name="destino" value="colaborador">
                            <input type="hidden" name="colaborador_id" value="<?php echo $colaboradorDevolucao['id']; ?>">
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <div class="radio-options">
                                <?php if ($colaboradorAtualInfo): ?>
                                <label class="radio-option">
                                    <input type="radio" name="destino" value="colaborador" 
                                           <?php echo (!isset($_POST['destino']) || $_POST['destino'] === 'colaborador') ? 'checked' : ''; ?>>
                                    <span>
                                        <i class="fas fa-user-check"></i> Devolver para Colaborador
                                    </span>
                                    <small class="form-text">
                                        Devolver para <?php echo htmlspecialchars($colaboradorAtualInfo['nome']); ?> 
                                        (<?php echo htmlspecialchars($colaboradorAtualInfo['departamento']); ?>)
                                    </small>
                                </label>
                                <?php endif; ?>
                                
                                <label class="radio-option">
                                    <input type="radio" name="destino" value="estoque"
                                           <?php echo (isset($_POST['destino']) && $_POST['destino'] === 'estoque') ? 'checked' : ''; ?>>
                                    <span>
                                        <i class="fas fa-warehouse"></i> Colocar em Estoque
                                    </span>
                                    <small class="form-text">O equipamento ficará disponível no estoque para futura atribuição.</small>
                                </label>
                                
                                <?php if (!$colaboradorAtualInfo): ?>
                                <label class="radio-option">
                                    <input type="radio" name="destino" value="novo_colaborador"
                                           <?php echo (isset($_POST['destino']) && $_POST['destino'] === 'novo_colaborador') ? 'checked' : ''; ?>>
                                    <span>
                                        <i class="fas fa-user-plus"></i> Atribuir para Novo Colaborador
                                    </span>
                                    <small class="form-text">Atribuir o equipamento para um colaborador diferente.</small>
                                </label>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div id="novo-colaborador-field" style="display: none;">
                            <div class="form-group">
                                <label for="colaborador_id">Selecionar Novo Colaborador *</label>
                                <select id="colaborador_id" name="colaborador_id" class="form-control">
                                    <option value="">Selecione um colaborador...</option>
                                    <?php foreach ($colaboradores as $colaborador): ?>
                                    <option value="<?php echo $colaborador['id']; ?>"
                                        <?php echo (isset($_POST['colaborador_id']) && $_POST['colaborador_id'] == $colaborador['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($colaborador['nome'] . ' - ' . $colaborador['departamento']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($erros)): ?>
            <div class="alert alert-danger">
                <h4><i class="fas fa-exclamation-triangle"></i> Erros encontrados:</h4>
                <ul>
                    <?php foreach ($erros as $erro): ?>
                    <li><?php echo htmlspecialchars($erro); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-check-circle"></i> Finalizar Manutenção
                </button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (!$devolverAutomaticamente): ?>
        const destinoColaborador = document.querySelector('input[name="destino"][value="colaborador"]');
        const destinoEstoque = document.querySelector('input[name="destino"][value="estoque"]');
        const destinoNovoColaborador = document.querySelector('input[name="destino"][value="novo_colaborador"]');
        const novoColaboradorField = document.getElementById('novo-colaborador-field');
        const colaboradorSelect = document.getElementById('colaborador_id');
        
        // Função para mostrar/ocultar campo de novo colaborador
        function toggleNovoColaboradorField() {
            if (destinoNovoColaborador && destinoNovoColaborador.checked) {
                novoColaboradorField.style.display = 'block';
                if (colaboradorSelect) colaboradorSelect.required = true;
            } else {
                novoColaboradorField.style.display = 'none';
                if (colaboradorSelect) colaboradorSelect.required = false;
            }
        }
        
        // Inicializar estado
        if (destinoNovoColaborador) {
            toggleNovoColaboradorField();
            
            // Adicionar listeners para mudanças
            destinoColaborador.addEventListener('change', toggleNovoColaboradorField);
            destinoEstoque.addEventListener('change', toggleNovoColaboradorField);
            destinoNovoColaborador.addEventListener('change', toggleNovoColaboradorField);
        }
        <?php endif; ?>
        
        // Formatar campo de custo
        const custoInput = document.getElementById('custo');
        if (custoInput) {
            custoInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                value = (value / 100).toFixed(2);
                value = value.replace('.', ',');
                e.target.value = value;
            });
        }
    });
    </script>
    
    <style>
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
    }
    
    .info-item.full-width {
        grid-column: 1 / -1;
    }
    
    .info-label {
        font-weight: 600;
        color: #666;
        font-size: 0.9em;
        margin-bottom: 5px;
    }
    
    .info-value {
        color: #333;
        font-size: 1.1em;
    }
    
    .radio-options {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .radio-option {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 20px;
        border: 2px solid #dee2e6;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .radio-option:hover {
        border-color: #6c757d;
        background-color: #f8f9fa;
    }
    
    .radio-option input[type="radio"]:checked + span {
        color: var(--primary-color);
        font-weight: 600;
    }
    
    .radio-option input[type="radio"]:checked {
        accent-color: var(--primary-color);
    }
    
    .radio-option span {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.1em;
    }
    
    .form-text {
        color: #6c757d;
        font-size: 0.875em;
        margin-top: 5px;
    }
    
    .manutencao-info {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid var(--warning-color);
    }
    
    .manutencao-info h4 {
        color: var(--warning-color);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .alert-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #28a745;
    }
    
    .alert-success h4 {
        color: #155724;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .destino-info {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-top: 15px;
        padding: 15px;
        background: white;
        border-radius: 8px;
    }
    
    .form-actions {
        display: flex;
        gap: 15px;
        padding: 20px 0;
        border-top: 1px solid #dee2e6;
    }
    
    .mt-3 {
        margin-top: 1rem !important;
    }
    
    .mt-4 {
        margin-top: 1.5rem !important;
    }
    
    .btn-lg {
        padding: 12px 30px;
        font-size: 1.1em;
    }
    
    .mb-0 {
        margin-bottom: 0 !important;
    }
    
    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn-lg {
            width: 100%;
        }
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.9em;
        font-weight: 500;
    }
    
    .status-warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
    
    .text-success {
        color: #28a745 !important;
    }
    </style>
</body>
</html>