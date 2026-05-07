<?php
session_start();
require_once '../includes/funcoes.php';
verificarSessao();

$mensagem = '';
$tipoMensagem = '';

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar e sanitizar os dados
    $marca = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $patrimonio = trim($_POST['patrimonio'] ?? '');
    $serial = trim($_POST['serial'] ?? '');
    $centro_custo = trim($_POST['centro_custo'] ?? '');
    $status = $_POST['status'] ?? 'estoque';
    $tipo = $_POST['tipo'] ?? 'notebook';
    $colaborador_id = $_POST['colaborador_id'] ?? null;
    $observacoes = trim($_POST['observacoes'] ?? '');
    
    // Validações
    $erros = [];
    
    if (empty($marca)) {
        $erros[] = 'A marca é obrigatória.';
    }
    
    if (empty($modelo)) {
        $erros[] = 'O modelo é obrigatório.';
    }
    
    if (empty($patrimonio)) {
        $erros[] = 'O número de patrimônio é obrigatório.';
    }
    
    if (empty($centro_custo)) {
        $erros[] = 'O centro de custo é obrigatório.';
    }
    
    // Verificar se precisa de colaborador (para alocado ou emprestado)
    if (($status === 'alocado' || $status === 'emprestado') && empty($colaborador_id)) {
        $erros[] = 'Selecione um colaborador para ' . ($status === 'emprestado' ? 'emprestar' : 'alocar') . ' o equipamento.';
    }
    
    // Verificar se patrimônio já existe
    $equipamentos = lerArquivoJSON('../data/equipamentos.json');
    if ($equipamentos === false) {
        $equipamentos = [];
    }
    
    foreach ($equipamentos as $equipamento) {
        if ($equipamento['patrimonio'] === $patrimonio) {
            $erros[] = 'Este número de patrimônio já está cadastrado no sistema.';
            break;
        }
        if (!empty($serial) && $equipamento['serial'] === $serial) {
            $erros[] = 'Este número de série já está cadastrado no sistema.';
            break;
        }
    }
    
    if (empty($erros)) {
        // Criar novo equipamento
        $novoEquipamento = [
            'id' => gerarId($equipamentos),
            'marca' => $marca,
            'modelo' => $modelo,
            'patrimonio' => $patrimonio,
            'serial' => $serial,
            'tipo' => $tipo,
            'centro_custo' => $centro_custo,
            'colaborador_id' => ($status === 'alocado' || $status === 'emprestado') ? $colaborador_id : null,
            'status' => $status,
            'observacoes' => $observacoes,
            'data_cadastro' => date('Y-m-d H:i:s'),
            'data_atribuicao' => ($status === 'alocado' || $status === 'emprestado') ? date('Y-m-d H:i:s') : null,
            'data_atualizacao' => date('Y-m-d H:i:s')
        ];
        
        // Adicionar ao array
        $equipamentos[] = $novoEquipamento;
        
        // Salvar no JSON
        if (salvarArquivoJSON('../data/equipamentos.json', $equipamentos)) {
            $mensagem = 'Equipamento cadastrado com sucesso!';
            $tipoMensagem = 'success';
            
            // Limpar o formulário
            $_POST = [];
        } else {
            $mensagem = 'Erro ao salvar o equipamento. Tente novamente.';
            $tipoMensagem = 'error';
        }
    } else {
        $mensagem = implode('<br>', $erros);
        $tipoMensagem = 'error';
    }
}

// Carregar colaboradores para o select
$colaboradores = lerArquivoJSON('../data/colaboradores.json');
if ($colaboradores === false) {
    $colaboradores = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Equipamento - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="page-header">
            <h1><i class="fas fa-laptop-medical"></i> Adicionar Novo Equipamento</h1>
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
            <form method="POST" action="" class="form-card" id="form-equipamento">
                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo"><i class="fas fa-tag"></i> Tipo de Equipamento *</label>
                        <select id="tipo" name="tipo" required class="form-select">
                            <option value="">-- Selecione o tipo --</option>
                            <?php foreach (getTiposEquipamentos() as $key => $value): ?>
                            <option value="<?php echo $key; ?>"
                                    <?php echo ($_POST['tipo'] ?? 'notebook') == $key ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="marca"><i class="fas fa-industry"></i> Marca *</label>
                        <input type="text" id="marca" name="marca" 
                               value="<?php echo htmlspecialchars($_POST['marca'] ?? ''); ?>" 
                               required class="form-control" 
                               placeholder="Ex: Dell, HP, Lenovo, Samsung">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="modelo"><i class="fas fa-laptop"></i> Modelo *</label>
                        <input type="text" id="modelo" name="modelo" 
                               value="<?php echo htmlspecialchars($_POST['modelo'] ?? ''); ?>" 
                               required class="form-control" 
                               placeholder="Ex: Latitude 5420, iPhone 13, MX Keys">
                    </div>
                    
                    <div class="form-group">
                        <label for="centro_custo"><i class="fas fa-dollar-sign"></i> Centro de Custo *</label>
                        <input type="text" id="centro_custo" name="centro_custo" 
                               value="<?php echo htmlspecialchars($_POST['centro_custo'] ?? ''); ?>" 
                               required class="form-control" 
                               placeholder="Ex: TI001, ADM002"
                               data-mask="cc">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="patrimonio"><i class="fas fa-barcode"></i> Número de Patrimônio *</label>
                        <input type="text" id="patrimonio" name="patrimonio" 
                               value="<?php echo htmlspecialchars($_POST['patrimonio'] ?? ''); ?>" 
                               required class="form-control" 
                               placeholder="Ex: PAT001, TI-2023-001">
                        <small class="form-text">Código único de identificação do patrimônio</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="serial"><i class="fas fa-hashtag"></i> Número de Série</label>
                        <input type="text" id="serial" name="serial" 
                               value="<?php echo htmlspecialchars($_POST['serial'] ?? ''); ?>" 
                               class="form-control" 
                               placeholder="Ex: SN123456789">
                        <small class="form-text">Número de série do fabricante (opcional)</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Status do Equipamento *</label>
                    <div class="status-options">
                        <?php 
                        $statusSelecionado = $_POST['status'] ?? 'estoque';
                        ?>
                        
                        <label class="status-option">
                            <input type="radio" name="status" value="estoque" 
                                   <?php echo $statusSelecionado == 'estoque' ? 'checked' : ''; ?>
                                   onchange="toggleColaboradorSelect(false)">
                            <span class="status-dot status-ativo"></span>
                            <span>Em Estoque</span>
                        </label>
                        
                        <label class="status-option">
                            <input type="radio" name="status" value="alocado" 
                                   <?php echo $statusSelecionado == 'alocado' ? 'checked' : ''; ?>
                                   onchange="toggleColaboradorSelect(true)">
                            <span class="status-dot status-inativo"></span>
                            <span>Alocado para Colaborador</span>
                        </label>
                        
                        <label class="status-option">
                            <input type="radio" name="status" value="emprestado" 
                                   <?php echo $statusSelecionado == 'emprestado' ? 'checked' : ''; ?>
                                   onchange="toggleColaboradorSelect(true)">
                            <span class="status-dot status-success"></span>
                            <span>Emprestado</span>
                        </label>
                        
                        <label class="status-option">
                            <input type="radio" name="status" value="manutencao" 
                                   <?php echo $statusSelecionado == 'manutencao' ? 'checked' : ''; ?>
                                   onchange="toggleColaboradorSelect(false)">
                            <span class="status-dot status-info"></span>
                            <span>Em Manutenção</span>
                        </label>
                        
                        <label class="status-option">
                            <input type="radio" name="status" value="fora_uso" 
                                   <?php echo $statusSelecionado == 'fora_uso' ? 'checked' : ''; ?>
                                   onchange="toggleColaboradorSelect(false)">
                            <span class="status-dot status-warning"></span>
                            <span>Fora de Uso</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group" id="colaborador-select" 
                     style="display: <?php echo in_array(($_POST['status'] ?? ''), ['alocado', 'emprestado']) ? 'block' : 'none'; ?>;">
                    <label for="colaborador_id"><i class="fas fa-user"></i> Selecionar Colaborador *</label>
                    <select id="colaborador_id" name="colaborador_id" class="form-select"
                            <?php echo in_array(($_POST['status'] ?? ''), ['alocado', 'emprestado']) ? 'required' : ''; ?>>
                        <option value="">-- Selecione um colaborador --</option>
                        <?php foreach ($colaboradores as $colaborador): ?>
                        <option value="<?php echo $colaborador['id']; ?>"
                                <?php echo ($_POST['colaborador_id'] ?? '') == $colaborador['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($colaborador['nome'] . ' - ' . $colaborador['departamento'] . ' (' . $colaborador['centro_custo'] . ')'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="observacoes"><i class="fas fa-sticky-note"></i> Observações</label>
                    <textarea id="observacoes" name="observacoes" class="form-control" 
                              rows="3" placeholder="Observações, características especiais, problemas conhecidos..."><?php echo htmlspecialchars($_POST['observacoes'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Salvar Equipamento
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Limpar
                    </button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
            
            <div class="info-card">
                <h4><i class="fas fa-info-circle"></i> Informações Importantes</h4>
                <ul>
                    <li>Todos os campos marcados com * são obrigatórios.</li>
                    <li>O número de patrimônio deve ser único no sistema.</li>
                    <li><strong>Em Estoque:</strong> Equipamento disponível para uso.</li>
                    <li><strong>Alocado para Colaborador:</strong> Equipamento em uso por um colaborador.</li>
                    <li><strong>Emprestado:</strong> Equipamento emprestado temporariamente a um colaborador.</li>
                    <li><strong>Em Manutenção:</strong> Equipamento em conserto ou manutenção.</li>
                    <li><strong>Fora de Uso:</strong> Equipamento quebrado, obsoleto ou aguardando descarte.</li>
                    <li>Equipamentos "Em Manutenção" ou "Fora de Uso" não podem ser alocados para colaboradores.</li>
                </ul>
            </div>
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
            selectElement.required = true;
        } else {
            selectDiv.style.display = 'none';
            selectElement.required = false;
            selectElement.value = '';
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
            const colaborador = document.getElementById('colaborador_id').value;
            
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
    
    // Inicializar estado do colaborador select baseado no status selecionado
    document.addEventListener('DOMContentLoaded', function() {
        const statusRadio = document.querySelector('input[name="status"]:checked');
        if (statusRadio) {
            const status = statusRadio.value;
            toggleColaboradorSelect(status === 'alocado' || status === 'emprestado');
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
    
    .status-success {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .status-info {
        background: #e2e3e5;
        color: #383d41;
    }
    
    .status-warning {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-dot.status-ativo {
        background: #28a745;
    }
    
    .status-dot.status-inativo {
        background: #dc3545;
    }
    
    .status-dot.status-success {
        background: #17a2b8;
    }
    
    .status-dot.status-info {
        background: #6c757d;
    }
    
    .status-dot.status-warning {
        background: #ffc107;
    }
    
    .form-text {
        display: block;
        margin-top: 5px;
        font-size: 12px;
        color: #666;
    }
    
    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }
    </style>
</body>
</html>