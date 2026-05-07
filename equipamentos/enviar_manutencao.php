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

// Verificar se o equipamento pode ser enviado para manutenção
$statusPermitidos = ['estoque', 'alocado', 'emprestado'];
if (!in_array($equipamento['status'], $statusPermitidos)) {
    $_SESSION['mensagem'] = 'Este equipamento não pode ser enviado para manutenção. Status atual: ' . getStatusTexto($equipamento['status']);
    $_SESSION['mensagem_tipo'] = 'warning';
    header('Location: index.php');
    exit;
}

// Processar envio para manutenção
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $problema = trim($_POST['problema'] ?? '');
    $local_manutencao = trim($_POST['local_manutencao'] ?? '');
    $previsao_retorno = $_POST['previsao_retorno'] ?? null;
    $manter_com_colaborador = isset($_POST['manter_com_colaborador']) && $_POST['manter_com_colaborador'] === 'sim';
    
    // Validações
    $erros = [];
    
    if (empty($problema)) {
        $erros[] = 'Informe o problema do equipamento.';
    }
    
    if (empty($local_manutencao)) {
        $erros[] = 'Informe o local da manutenção.';
    }
    
    if (empty($erros)) {
        // Registrar manutenção no histórico
        $novaManutencao = [
            'data_envio' => date('Y-m-d H:i:s'),
            'problema' => $problema,
            'local_manutencao' => $local_manutencao,
            'previsao_retorno' => $previsao_retorno,
            'data_retorno' => null,
            'resultado' => null,
            'custo' => null,
            'tecnico' => $_SESSION['usuario_nome'] ?? 'Administrador',
            'manter_com_colaborador' => $manter_com_colaborador
        ];
        
        // Registrar colaborador atual se houver
        if ($equipamento['colaborador_id']) {
            $novaManutencao['colaborador_atual'] = $equipamento['colaborador_id'];
            $novaManutencao['status_anterior'] = $equipamento['status'];
        }
        
        // Inicializar histórico se não existir
        if (!isset($equipamento['historico_manutencao']) || !is_array($equipamento['historico_manutencao'])) {
            $equipamento['historico_manutencao'] = [];
        }
        
        // Adicionar nova manutenção ao histórico
        $equipamento['historico_manutencao'][] = $novaManutencao;
        
        // Atualizar status do equipamento
        $equipamento['status'] = 'manutencao';
        $equipamento['data_atualizacao'] = date('Y-m-d H:i:s');
        
        // NÃO REMOVER o colaborador - manter o vínculo
        // O equipamento continua vinculado ao colaborador, apenas com status diferente
        
        // Atualizar no array de equipamentos
        $equipamentos[$equipamentoIndex] = $equipamento;
        
        // Salvar alterações
        if (salvarArquivoJSON('../data/equipamentos.json', $equipamentos)) {
            $_SESSION['mensagem'] = 'Equipamento enviado para manutenção com sucesso!';
            $_SESSION['mensagem_tipo'] = 'success';
            
            header('Location: index.php');
            exit;
        } else {
            $erros[] = 'Erro ao salvar as alterações. Tente novamente.';
        }
    }
}

// Verificar se há colaborador atual
$temColaboradorAtual = false;
$colaboradorAtualInfo = null;
if ($equipamento['colaborador_id']) {
    $temColaboradorAtual = true;
    foreach ($colaboradores as $colaborador) {
        if ($colaborador['id'] == $equipamento['colaborador_id']) {
            $colaboradorAtualInfo = $colaborador;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar para Manutenção - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="page-header">
            <h1><i class="fas fa-tools"></i> Enviar para Manutenção</h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-laptop"></i> Informações do Equipamento</h3>
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
                        <span class="info-label">Status Atual:</span>
                        <span class="info-value">
                            <span class="status-badge <?php echo $equipamento['status'] === 'estoque' ? 'status-ativo' : 'status-inativo'; ?>">
                                <i class="fas fa-<?php echo getIconByStatus($equipamento['status']); ?>"></i>
                                <?php echo getStatusTexto($equipamento['status']); ?>
                            </span>
                        </span>
                    </div>
                    <?php if ($temColaboradorAtual): ?>
                    <div class="info-item">
                        <span class="info-label">Colaborador Atual:</span>
                        <span class="info-value">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($colaboradorAtualInfo['nome']); ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Departamento:</span>
                        <span class="info-value"><?php echo htmlspecialchars($colaboradorAtualInfo['departamento']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <form method="POST" action="" class="mt-4">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-wrench"></i> Informações da Manutenção</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="problema">Problema / Defeito *</label>
                        <textarea id="problema" name="problema" class="form-control" 
                                  rows="4" placeholder="Descreva em detalhes o problema encontrado no equipamento..."
                                  required><?php echo htmlspecialchars($_POST['problema'] ?? ''); ?></textarea>
                        <small class="form-text">Descreva o que está acontecendo com o equipamento, sintomas, quando começou, etc.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="local_manutencao">Local da Manutenção *</label>
                        <select id="local_manutencao" name="local_manutencao" class="form-control" required>
                            <option value="">Selecione o local...</option>
                            <option value="interno" <?php echo (isset($_POST['local_manutencao']) && $_POST['local_manutencao'] === 'interno') ? 'selected' : ''; ?>>Interno (Manutenção própria)</option>
                            <option value="externo_fornecedor" <?php echo (isset($_POST['local_manutencao']) && $_POST['local_manutencao'] === 'externo_fornecedor') ? 'selected' : ''; ?>>Externo (Fornecedor/Assistência técnica)</option>
                            <option value="garantia" <?php echo (isset($_POST['local_manutencao']) && $_POST['local_manutencao'] === 'garantia') ? 'selected' : ''; ?>>Garantia (Fabricante)</option>
                            <option value="outro" <?php echo (isset($_POST['local_manutencao']) && $_POST['local_manutencao'] === 'outro') ? 'selected' : ''; ?>>Outro</option>
                        </select>
                        <small class="form-text">Onde será realizada a manutenção?</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="previsao_retorno">Previsão de Retorno</label>
                        <input type="date" id="previsao_retorno" name="previsao_retorno" 
                               class="form-control"
                               value="<?php echo htmlspecialchars($_POST['previsao_retorno'] ?? ''); ?>">
                        <small class="form-text">Data estimada para o retorno do equipamento (opcional)</small>
                    </div>
                    
                    <?php if ($temColaboradorAtual): ?>
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Manter vínculo com colaborador?</label>
                        <div class="radio-options">
                            <label class="radio-option">
                                <input type="radio" name="manter_com_colaborador" value="sim" checked>
                                <span>Sim, manter vinculado ao colaborador</span>
                                <small class="form-text">
                                    O equipamento permanecerá vinculado a <?php echo htmlspecialchars($colaboradorAtualInfo['nome']); ?>.
                                    Quando retornar da manutenção, será automaticamente devolvido.
                                </small>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="manter_com_colaborador" value="nao">
                                <span>Não, remover do colaborador</span>
                                <small class="form-text">
                                    O equipamento será removido de <?php echo htmlspecialchars($colaboradorAtualInfo['nome']); ?>.
                                    Quando retornar da manutenção, irá para o estoque.
                                </small>
                            </label>
                        </div>
                    </div>
                    <?php else: ?>
                        <input type="hidden" name="manter_com_colaborador" value="nao">
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($erros)): ?>
            <div class="alert alert-danger mt-3">
                <h4><i class="fas fa-exclamation-triangle"></i> Erros encontrados:</h4>
                <ul>
                    <?php foreach ($erros as $erro): ?>
                    <li><?php echo htmlspecialchars($erro); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="alert alert-info mt-3">
                <h4><i class="fas fa-info-circle"></i> Como funciona:</h4>
                <p>Ao enviar este equipamento para manutenção:</p>
                <ul>
                    <li>O status será alterado para "Em Manutenção"</li>
                    <?php if ($temColaboradorAtual): ?>
                    <li><strong>Por padrão, o equipamento continua vinculado ao colaborador</strong></li>
                    <li>Quando a manutenção for finalizada, o equipamento será devolvido automaticamente</li>
                    <?php else: ?>
                    <li>O equipamento permanecerá em estoque (sem colaborador)</li>
                    <?php endif; ?>
                    <li>Será registrado no histórico de manutenções</li>
                    <li>O equipamento ficará indisponível para uso até o retorno</li>
                </ul>
            </div>
            
            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-warning btn-lg">
                    <i class="fas fa-paper-plane"></i> Enviar para Manutenção
                </button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configurar data mínima para previsão de retorno (amanhã)
        const hoje = new Date();
        const amanha = new Date(hoje);
        amanha.setDate(amanha.getDate() + 1);
        
        const dataMinima = amanha.toISOString().split('T')[0];
        const previsaoInput = document.getElementById('previsao_retorno');
        
        if (previsaoInput) {
            previsaoInput.min = dataMinima;
            
            // Sugerir uma data (7 dias a partir de hoje)
            const sugerida = new Date(hoje);
            sugerida.setDate(sugerida.getDate() + 7);
            const dataSugerida = sugerida.toISOString().split('T')[0];
            
            // Só preencher se não houver valor
            if (!previsaoInput.value) {
                previsaoInput.value = dataSugerida;
            }
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
    
    .form-text {
        color: #6c757d;
        font-size: 0.875em;
        margin-top: 5px;
    }
    
    .radio-options {
        display: flex;
        flex-direction: column;
        gap: 15px;
        margin-top: 10px;
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
    
    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #17a2b8;
    }
    
    .alert-info h4 {
        color: #0c5460;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .alert-info ul {
        margin: 10px 0 0 0;
        padding-left: 20px;
    }
    
    .alert-info li {
        margin-bottom: 5px;
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
    
    .status-ativo {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .status-inativo {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
    </style>
</body>
</html>