<?php
session_start();
require_once '../includes/funcoes.php';
verificarSessao();

$id = $_GET['id'] ?? null;
$tipo = $_GET['tipo'] ?? 'alocado'; // 'alocado' ou 'emprestado'

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

// Verificar se o equipamento pode ser atribuído
// Só equipamentos "Em Estoque" podem ser alocados/emprestados diretamente
if ($equipamento['status'] !== 'estoque') {
    $_SESSION['mensagem'] = 'Este equipamento não pode ser ' . ($tipo === 'emprestado' ? 'emprestado' : 'alocado') . 
                            '! Status atual: ' . getStatusTexto($equipamento['status']) . 
                            '. Apenas equipamentos "Em Estoque" podem ser atribuídos.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

// Processar atribuição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $colaborador_id = $_POST['colaborador_id'] ?? null;
    $data_devolucao = $_POST['data_devolucao'] ?? null;
    $observacoes = trim($_POST['observacoes'] ?? '');
    
    if ($colaborador_id) {
        // Atualizar equipamento
        $equipamentos[$equipamentoIndex]['colaborador_id'] = (int)$colaborador_id;
        $equipamentos[$equipamentoIndex]['status'] = $tipo;
        $equipamentos[$equipamentoIndex]['data_atribuicao'] = date('Y-m-d H:i:s');
        $equipamentos[$equipamentoIndex]['data_atualizacao'] = date('Y-m-d H:i:s');
        
        // Adicionar informações específicas para empréstimo
        if ($tipo === 'emprestado') {
            $equipamentos[$equipamentoIndex]['data_devolucao_prevista'] = $data_devolucao;
            $equipamentos[$equipamentoIndex]['tipo_atribuicao'] = 'emprestimo';
            
            if (!empty($observacoes)) {
                $observacoesAtuais = $equipamentos[$equipamentoIndex]['observacoes'] ?? '';
                $equipamentos[$equipamentoIndex]['observacoes'] = $observacoesAtuais . 
                    (empty($observacoesAtuais) ? '' : "\n\n") . 
                    "[EMPRÉSTIMO] " . $observacoes . " (Data prevista: " . 
                    (!empty($data_devolucao) ? date('d/m/Y', strtotime($data_devolucao)) : 'Não definida') . ")";
            }
        } else {
            $equipamentos[$equipamentoIndex]['tipo_atribuicao'] = 'alocacao';
            
            if (!empty($observacoes)) {
                $observacoesAtuais = $equipamentos[$equipamentoIndex]['observacoes'] ?? '';
                $equipamentos[$equipamentoIndex]['observacoes'] = $observacoesAtuais . 
                    (empty($observacoesAtuais) ? '' : "\n\n") . 
                    "[ALOCAÇÃO] " . $observacoes;
            }
        }
        
        // Salvar no JSON
        if (salvarArquivoJSON('../data/equipamentos.json', $equipamentos)) {
            $_SESSION['mensagem'] = 'Equipamento ' . ($tipo === 'emprestado' ? 'emprestado' : 'alocado') . ' com sucesso para o colaborador!';
            $_SESSION['mensagem_tipo'] = 'success';
            
            header('Location: index.php');
            exit;
        } else {
            $erro = 'Erro ao salvar as alterações. Tente novamente.';
        }
    } else {
        $erro = 'Selecione um colaborador.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tipo === 'emprestado' ? 'Emprestar' : 'Alocar'; ?> Equipamento - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/equipamentos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="page-header">
            <h1><i class="fas fa-<?php echo $tipo === 'emprestado' ? 'handshake' : 'user-check'; ?>"></i> 
                <?php echo $tipo === 'emprestado' ? 'Emprestar' : 'Alocar'; ?> Equipamento
            </h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
        
        <div class="form-container">
            <div class="equipamento-info">
                <h3>Equipamento a ser <?php echo $tipo === 'emprestado' ? 'emprestado' : 'alocado'; ?>:</h3>
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
                        <span class="info-label">Número de Série:</span>
                        <span class="info-value"><?php echo !empty($equipamento['serial']) ? htmlspecialchars($equipamento['serial']) : '---'; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Centro de Custo:</span>
                        <span class="info-value"><?php echo htmlspecialchars($equipamento['centro_custo']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status Atual:</span>
                        <span class="status-badge status-ativo"><?php echo getStatusTexto($equipamento['status']); ?></span>
                    </div>
                </div>
            </div>
            
            <form method="POST" action="" class="form-card">
                <div class="form-group">
                    <label for="colaborador_id"><i class="fas fa-user"></i> Selecionar Colaborador *</label>
                    <select id="colaborador_id" name="colaborador_id" required class="form-select">
                        <option value="">-- Selecione um colaborador --</option>
                        <?php if (empty($colaboradores)): ?>
                        <option value="" disabled>Nenhum colaborador cadastrado</option>
                        <?php else: ?>
                            <?php foreach ($colaboradores as $colaborador): ?>
                            <option value="<?php echo $colaborador['id']; ?>">
                                <?php echo htmlspecialchars($colaborador['nome'] . ' - ' . $colaborador['departamento'] . ' (' . $colaborador['centro_custo'] . ')'); ?>
                            </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (empty($colaboradores)): ?>
                    <small class="form-text text-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Não há colaboradores cadastrados. <a href="../colaboradores/adicionar.php">Cadastre um colaborador primeiro</a>.
                    </small>
                    <?php endif; ?>
                </div>
                
                <?php if ($tipo === 'emprestado'): ?>
                <div class="form-group">
                    <label for="data_devolucao"><i class="fas fa-calendar-alt"></i> Data Prevista de Devolução</label>
                    <input type="date" id="data_devolucao" name="data_devolucao" 
                           class="form-control"
                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    <small class="form-text">Opcional - Defina uma data para o empréstimo</small>
                </div>
                
                <div class="form-group">
                    <label for="observacoes"><i class="fas fa-sticky-note"></i> Observações do Empréstimo</label>
                    <textarea id="observacoes" name="observacoes" class="form-control" 
                              rows="3" placeholder="Motivo do empréstimo, condições especiais, local de uso..."></textarea>
                </div>
                <?php else: ?>
                <div class="form-group">
                    <label for="observacoes"><i class="fas fa-sticky-note"></i> Observações da Alocação</label>
                    <textarea id="observacoes" name="observacoes" class="form-control" 
                              rows="3" placeholder="Observações sobre a alocação..."></textarea>
                </div>
                <?php endif; ?>
                
                <?php if (isset($erro)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?>
                </div>
                <?php endif; ?>
                
                <div class="warning-card">
                    <h4><i class="fas fa-exclamation-triangle"></i> Confirmação</h4>
                    <p>Ao <?php echo $tipo === 'emprestado' ? 'emprestar' : 'alocar'; ?> este equipamento:</p>
                    <ul>
                        <li>O status será alterado para <strong>"<?php echo getStatusTexto($tipo); ?>"</strong></li>
                        <li>O equipamento sairá do estoque disponível</li>
                        <li>O colaborador ficará responsável pelo equipamento</li>
                        <?php if ($tipo === 'emprestado'): ?>
                        <li>Será registrado como um empréstimo temporário</li>
                        <?php else: ?>
                        <li>Será registrado como uma alocação permanente</li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-<?php echo $tipo === 'emprestado' ? 'info' : 'success'; ?>">
                        <i class="fas fa-<?php echo $tipo === 'emprestado' ? 'handshake' : 'check-circle'; ?>"></i> 
                        <?php echo $tipo === 'emprestado' ? 'Confirmar Empréstimo' : 'Confirmar Alocação'; ?>
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
            
            <div class="info-card">
                <h4><i class="fas fa-info-circle"></i> Informações</h4>
                <?php if ($tipo === 'emprestado'): ?>
                <p><strong>Empréstimo:</strong> Atribuição temporária do equipamento a um colaborador.</p>
                <p>O equipamento será marcado como "Emprestado" e o colaborador ficará responsável por sua devolução na data prevista.</p>
                <p>Após a devolução, o equipamento retornará ao estoque.</p>
                <?php else: ?>
                <p><strong>Alocação:</strong> Atribuição permanente do equipamento a um colaborador.</p>
                <p>O equipamento será marcado como "Alocado para Colaborador" e ficará vinculado ao colaborador até ser devolvido ao estoque.</p>
                <p>O colaborador selecionado receberá este equipamento em seu perfil.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../js/script.js"></script>
    
    <style>
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