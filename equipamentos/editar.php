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

$mensagem = '';
$tipoMensagem = '';

// Processar edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $patrimonio = trim($_POST['patrimonio'] ?? '');
    $serial = trim($_POST['serial'] ?? '');
    $centro_custo = trim($_POST['centro_custo'] ?? '');
    $tipo = $_POST['tipo'] ?? 'notebook';
    $status = $_POST['status'] ?? 'estoque';
    $colaborador_id = $_POST['colaborador_id'] ?? null;
    $observacoes = trim($_POST['observacoes'] ?? '');
    
    // Validações
    $erros = [];
    
    if (empty($marca)) $erros[] = 'A marca é obrigatória.';
    if (empty($modelo)) $erros[] = 'O modelo é obrigatório.';
    if (empty($patrimonio)) $erros[] = 'O número de patrimônio é obrigatório.';
    if (empty($centro_custo)) $erros[] = 'O centro de custo é obrigatório.';
    
    // Verificar se patrimônio já existe (exceto para o próprio equipamento)
    foreach ($equipamentos as $index => $equip) {
        if ($index != $equipamentoIndex && $equip['patrimonio'] === $patrimonio) {
            $erros[] = 'Este número de patrimônio já está cadastrado para outro equipamento.';
            break;
        }
        if (!empty($serial) && $index != $equipamentoIndex && $equip['serial'] === $serial) {
            $erros[] = 'Este número de série já está cadastrado para outro equipamento.';
            break;
        }
    }
    
    // Verificar se precisa de colaborador (para alocado ou emprestado)
    if (($status === 'alocado' || $status === 'emprestado') && empty($colaborador_id)) {
        $erros[] = 'Selecione um colaborador para ' . ($status === 'emprestado' ? 'emprestar' : 'alocar') . ' o equipamento.';
    }
    
    // Verificar se equipamentos com status "fora de uso" ou "manutenção" estão alocados
    if (($status === 'fora_uso' || $status === 'manutencao') && !empty($colaborador_id)) {
        $erros[] = 'Equipamentos "' . getStatusTexto($status) . '" não podem estar alocados para colaboradores.';
        $colaborador_id = null;
    }
    
    if (empty($erros)) {
        // Registrar mudança de status se for diferente
        $statusAnterior = $equipamento['status'];
        $colaboradorAnterior = $equipamento['colaborador_id'] ?? null;
        
        // Atualizar equipamento
        $equipamentos[$equipamentoIndex]['marca'] = $marca;
        $equipamentos[$equipamentoIndex]['modelo'] = $modelo;
        $equipamentos[$equipamentoIndex]['patrimonio'] = $patrimonio;
        $equipamentos[$equipamentoIndex]['serial'] = $serial;
        $equipamentos[$equipamentoIndex]['tipo'] = $tipo;
        $equipamentos[$equipamentoIndex]['centro_custo'] = $centro_custo;
        $equipamentos[$equipamentoIndex]['status'] = $status;
        $equipamentos[$equipamentoIndex]['observacoes'] = $observacoes;
        $equipamentos[$equipamentoIndex]['data_atualizacao'] = date('Y-m-d H:i:s');
        
        // Atualizar dados de alocação
        if ($status === 'alocado' || $status === 'emprestado') {
            $equipamentos[$equipamentoIndex]['colaborador_id'] = (int)$colaborador_id;
            
            // Se não tinha data de atribuição ou se mudou de colaborador
            if (empty($equipamento['data_atribuicao']) || $colaboradorAnterior != $colaborador_id) {
                $equipamentos[$equipamentoIndex]['data_atribuicao'] = date('Y-m-d H:i:s');
            }
            
            // Definir tipo de atribuição
            $equipamentos[$equipamentoIndex]['tipo_atribuicao'] = $status === 'emprestado' ? 'emprestimo' : 'alocacao';
        } else {
            $equipamentos[$equipamentoIndex]['colaborador_id'] = null;
            $equipamentos[$equipamentoIndex]['data_atribuicao'] = null;
            
            // Limpar campos de empréstimo se existirem
            if (isset($equipamentos[$equipamentoIndex]['data_devolucao_prevista'])) {
                unset($equipamentos[$equipamentoIndex]['data_devolucao_prevista']);
            }
            if (isset($equipamentos[$equipamentoIndex]['tipo_atribuicao'])) {
                unset($equipamentos[$equipamentoIndex]['tipo_atribuicao']);
            }
        }
        
        // Adicionar histórico de alterações se o status mudou
        if ($statusAnterior !== $status) {
            $historico = "\n\n[ALTERAÇÃO] " . date('d/m/Y H:i:s');
            $historico .= "\nStatus alterado: " . getStatusTexto($statusAnterior) . " → " . getStatusTexto($status);
            
            if ($colaboradorAnterior && !$colaborador_id) {
                $historico .= "\nRemovido do colaborador";
            } elseif (!$colaboradorAnterior && $colaborador_id) {
                $historico .= "\nAtribuído a novo colaborador";
            }
            
            $observacoesAtuais = $equipamentos[$equipamentoIndex]['observacoes'] ?? '';
            $equipamentos[$equipamentoIndex]['observacoes'] = $observacoesAtuais . $historico;
        }
        
        // Salvar no JSON
        if (salvarArquivoJSON('../data/equipamentos.json', $equipamentos)) {
            $mensagem = 'Equipamento atualizado com sucesso!';
            $tipoMensagem = 'success';
            $equipamento = $equipamentos[$equipamentoIndex]; // Atualizar dados locais
        } else {
            $mensagem = 'Erro ao atualizar o equipamento. Tente novamente.';
            $tipoMensagem = 'error';
        }
    } else {
        $mensagem = implode('<br>', $erros);
        $tipoMensagem = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Equipamento - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="page-header">
            <h1><i class="fas fa-edit"></i> Editar Equipamento</h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
        
        <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : 'error'; ?>">
            <i class="fas fa-<?php echo $tipoMensagem === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $mensagem; ?>
        </div>
        <?php endif; ?>
        
        <div class="form-container">
            <div class="equipamento-info">
                <h3>Informações do Equipamento</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">ID:</span>
                        <span class="info-value"><?php echo $equipamento['id']; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Data de Cadastro:</span>
                        <span class="info-value"><?php echo formatarData($equipamento['data_cadastro']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Última Atualização:</span>
                        <span class="info-value"><?php echo isset($equipamento['data_atualizacao']) ? formatarData($equipamento['data_atualizacao']) : '---'; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Data de Atribuição:</span>
                        <span class="info-value"><?php echo isset($equipamento['data_atribuicao']) ? formatarData($equipamento['data_atribuicao']) : '---'; ?></span>
                    </div>
                </div>
            </div>
            
            <form method="POST" action="" class="form-card" id="form-equipamento">
                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo"><i class="fas fa-tag"></i> Tipo de Equipamento *</label>
                        <select id="tipo" name="tipo" required class="form-select">
                            <option value="">-- Selecione o tipo --</option>
                            <?php foreach (getTiposEquipamentos() as $key => $value): ?>
                            <option value="<?php echo $key; ?>"
                                    <?php echo $equipamento['tipo'] == $key ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="marca"><i class="fas fa-industry"></i> Marca *</label>
                        <input type="text" id="marca" name="marca" 
                               value="<?php echo htmlspecialchars($equipamento['marca']); ?>" 
                               required class="form-control" 
                               placeholder="Ex: Dell, HP, Lenovo">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="modelo"><i class="fas fa-laptop"></i> Modelo *</label>
                        <input type="text" id="modelo" name="modelo" 
                               value="<?php echo htmlspecialchars($equipamento['modelo']); ?>" 
                               required class="form-control" 
                               placeholder="Ex: Latitude 5420, iPhone 13">
                    </div>
                    
                    <div class="form-group">
                        <label for="centro_custo"><i class="fas fa-dollar-sign"></i> Centro de Custo *</label>
                        <input type="text" id="centro_custo" name="centro_custo" 
                               value="<?php echo htmlspecialchars($equipamento['centro_custo']); ?>" 
                               required class="form-control" 
                               placeholder="Ex: TI001, ADM002"
                               data-mask="cc">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="patrimonio"><i class="fas fa-barcode"></i> Número de Patrimônio *</label>
                        <input type="text" id="patrimonio" name="patrimonio" 
                               value="<?php echo htmlspecialchars($equipamento['patrimonio']); ?>" 
                               required class="form-control" 
                               placeholder="Ex: PAT001, TI-2023-001">
                    </div>
                    
                    <div class="form-group">
                        <label for="serial"><i class="fas fa-hashtag"></i> Número de Série</label>
                        <input type="text" id="serial" name="serial" 
                               value="<?php echo htmlspecialchars($equipamento['serial'] ?? ''); ?>" 
                               class="form-control" 
                               placeholder="Ex: SN123456789">
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Status do Equipamento *</label>
                    <div class="status-options">
                        <?php 
                        $statusClasses = [
                            'estoque' => 'status-ativo',
                            'alocado' => 'status-inativo',
                            'emprestado' => 'status-info',
                            'manutencao' => 'status-warning',
                            'fora_uso' => 'status-danger'
                        ];
                        ?>
                        
                        <label class="status-option">
                            <input type="radio" name="status" value="estoque" 
                                   <?php echo $equipamento['status'] == 'estoque' ? 'checked' : ''; ?>
                                   onchange="toggleColaboradorSelect(false)">
                            <span class="status-dot status-ativo"></span>
                            <span>Em Estoque</span>
                        </label>
                        
                        <label class="status-option">
                            <input type="radio" name="status" value="alocado" 
                                   <?php echo $equipamento['status'] == 'alocado' ? 'checked' : ''; ?>
                                   onchange="toggleColaboradorSelect(true)">
                            <span class="status-dot status-inativo"></span>
                            <span>Alocado para Colaborador</span>
                        </label>
                        
                        <label class="status-option">
                            <input type="radio" name="status" value="emprestado" 
                                   <?php echo $equipamento['status'] == 'emprestado' ? 'checked' : ''; ?>
                                   onchange="toggleColaboradorSelect(true)">
                            <span class="status-dot status-info"></span>
                            <span>Emprestado</span>
                        </label>
                        
                        <label class="status-option">
                            <input type="radio" name="status" value="manutencao" 
                                   <?php echo $equipamento['status'] == 'manutencao' ? 'checked' : ''; ?>
                                   onchange="toggleColaboradorSelect(false)">
                            <span class="status-dot status-warning"></span>
                            <span>Em Manutenção</span>
                        </label>
                        
                        <label class="status-option">
                            <input type="radio" name="status" value="fora_uso" 
                                   <?php echo $equipamento['status'] == 'fora_uso' ? 'checked' : ''; ?>
                                   onchange="toggleColaboradorSelect(false)">
                            <span class="status-dot status-danger"></span>
                            <span>Fora de Uso</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group" id="colaborador-select" 
                     style="display: <?php echo in_array($equipamento['status'], ['alocado', 'emprestado']) ? 'block' : 'none'; ?>;">
                    <label for="colaborador_id"><i class="fas fa-user"></i> Selecionar Colaborador *</label>
                    <select id="colaborador_id" name="colaborador_id" class="form-select"
                            <?php echo in_array($equipamento['status'], ['alocado', 'emprestado']) ? 'required' : ''; ?>>
                        <option value="">-- Selecione um colaborador --</option>
                        <?php if (empty($colaboradores)): ?>
                        <option value="" disabled>Nenhum colaborador cadastrado</option>
                        <?php else: ?>
                            <?php foreach ($colaboradores as $colaborador): ?>
                            <option value="<?php echo $colaborador['id']; ?>"
                                    <?php echo $equipamento['colaborador_id'] == $colaborador['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($colaborador['nome'] . ' - ' . $colaborador['departamento'] . ' (' . $colaborador['centro_custo'] . ')'); ?>
                            </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (empty($colaboradores) && in_array($equipamento['status'], ['alocado', 'emprestado'])): ?>
                    <small class="form-text text-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Não há colaboradores cadastrados. <a href="../colaboradores/adicionar.php">Cadastre um colaborador primeiro</a>.
                    </small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="observacoes"><i class="fas fa-sticky-note"></i> Observações</label>
                    <textarea id="observacoes" name="observacoes" class="form-control" 
                              rows="3" placeholder="Observações, características especiais, problemas conhecidos..."><?php echo htmlspecialchars($equipamento['observacoes'] ?? ''); ?></textarea>
                </div>
                
                <div class="warning-card">
                    <h4><i class="fas fa-exclamation-triangle"></i> Atenção!</h4>
                    <p>Ao alterar o status do equipamento:</p>
                    <ul>
                        <li>Se mudar para "Em Estoque", "Em Manutenção" ou "Fora de Uso", o vínculo com o colaborador será removido</li>
                        <li>Se mudar para "Alocado" ou "Emprestado", será necessário selecionar um colaborador</li>
                        <li>O histórico da alteração será registrado nas observações</li>
                        <li>Esta ação pode afetar a disponibilidade do equipamento</li>
                    </ul>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Atualizar Equipamento
                    </button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../js/script.js"></script>
    <script>
    function toggleColaboradorSelect(show) {
        const selectDiv = document.getElementById('colaborador-select');
        const selectElement = document.getElementById('colaborador_id');
        
        if (show) {
            selectDiv.style.display = 'block';
            if (selectElement) {
                selectElement.required = true;
            }
        } else {
            selectDiv.style.display = 'none';
            if (selectElement) {
                selectElement.required = false;
                selectElement.value = '';
            }
        }
    }
    
    // Auto-formatar centro de custo
    const ccInput = document.getElementById('centro_custo');
    if (ccInput) {
        ccInput.addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase();
            value = value.replace(/[^A-Z0-9]/g, '');
            e.target.value = value;
        });
    }
    
    // Inicializar estado do colaborador select
    document.addEventListener('DOMContentLoaded', function() {
        const statusRadio = document.querySelector('input[name="status"]:checked');
        if (statusRadio) {
            const status = statusRadio.value;
            toggleColaboradorSelect(status === 'alocado' || status === 'emprestado');
        }
        
        // Validação do formulário
        const form = document.getElementById('form-equipamento');
        if (form) {
            form.addEventListener('submit', function(e) {
                const patrimonio = document.getElementById('patrimonio').value.trim();
                const tipo = document.getElementById('tipo').value;
                const statusRadio = document.querySelector('input[name="status"]:checked');
                
                if (!statusRadio) {
                    alert('Selecione o status do equipamento.');
                    e.preventDefault();
                    return false;
                }
                
                const status = statusRadio.value;
                const colaborador = document.getElementById('colaborador_id') ? document.getElementById('colaborador_id').value : '';
                
                if (patrimonio.length < 3) {
                    alert('O número de patrimônio deve ter pelo menos 3 caracteres.');
                    e.preventDefault();
                    return false;
                }
                
                if (tipo === '') {
                    alert('Selecione o tipo de equipamento.');
                    e.preventDefault();
                    return false;
                }
                
                if ((status === 'alocado' || status === 'emprestado') && colaborador === '') {
                    alert('Selecione um colaborador para ' + (status === 'emprestado' ? 'emprestar' : 'alocar') + ' o equipamento.');
                    e.preventDefault();
                    return false;
                }
                
                return true;
            });
        }
    });
    </script>
    
    <style>
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
    
    .status-options {
        display: flex;
        gap: 10px;
        margin-top: 10px;
        flex-wrap: wrap;
    }
    
    .status-option {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 15px;
        border: 2px solid #ddd;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--transition);
        flex: 1;
        min-width: 150px;
    }
    
    .status-option:hover {
        background: #f8f9fa;
    }
    
    .status-option input[type="radio"]:checked {
        accent-color: var(--primary-color);
    }
    
    .status-option input[type="radio"]:checked + .status-dot {
        transform: scale(1.2);
    }
    
    .status-option input[type="radio"]:checked ~ span:last-child {
        font-weight: bold;
        color: var(--dark-color);
    }
    
    .status-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        transition: transform 0.2s;
    }
    
    .status-ativo {
        background: #d4edda;
        color: #155724;
    }
    
    .status-inativo {
        background: #f8d7da;
        color: #721c24;
    }
    
    .status-info {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .status-warning {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-danger {
        background: #f8d7da;
        color: #721c24;
    }
    
    .status-dot.status-ativo {
        background: #28a745;
    }
    
    .status-dot.status-inativo {
        background: #dc3545;
    }
    
    .status-dot.status-info {
        background: #17a2b8;
    }
    
    .status-dot.status-warning {
        background: #ffc107;
    }
    
    .status-dot.status-danger {
        background: #dc3545;
    }
    
    .warning-card {
        margin: 20px 0;
        padding: 15px;
        background: #fff3cd;
        border-radius: var(--border-radius);
        border-left: 4px solid #ffc107;
    }
    
    .warning-card h4 {
        color: #856404;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .warning-card ul {
        margin: 10px 0 0 20px;
        color: #856404;
    }
    
    .warning-card li {
        margin-bottom: 5px;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 10px;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .info-label {
        font-size: 12px;
        color: var(--gray-color);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .info-value {
        font-weight: 500;
        color: var(--dark-color);
    }
    
    .form-text.text-danger {
        color: #dc3545 !important;
    }
    
    .form-text.text-danger a {
        color: #dc3545;
        text-decoration: underline;
    }
    </style>
</body>
</html>