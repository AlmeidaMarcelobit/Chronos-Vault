<?php
session_start();
require_once '../includes/funcoes.php';
verificarSessao();

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
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="page-header">
            <h1><i class="fas fa-trash"></i> Excluir Colaborador</h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
        
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Atenção!</strong> Esta ação não pode ser desfeita.
        </div>
        
        <div class="confirmation-card">
            <h2>Confirmar Exclusão</h2>
            
            <div class="colaborador-details">
                <h3>Dados do Colaborador</h3>
                <div class="details-grid">
                    <div class="detail-item">
                        <span class="detail-label">Nome:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($colaboradorAtual['nome']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Cargo:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($colaboradorAtual['cargo']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">CPF:</span>
                        <span class="detail-value"><?php echo formatarCPF($colaboradorAtual['cpf']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Departamento:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($colaboradorAtual['departamento']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Centro de Custo:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($colaboradorAtual['centro_custo']); ?></span>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($equipamentosAtribuidos)): ?>
            <div class="warning-card">
                <h4><i class="fas fa-exclamation-circle"></i> Importante</h4>
                <p>Este colaborador possui <strong><?php echo count($equipamentosAtribuidos); ?> equipamento(s)</strong> atribuído(s).</p>
                <p>Ao excluir o colaborador, todos os equipamentos serão devolvidos automaticamente para o estoque.</p>
                
                <div class="equipamentos-list">
                    <h5>Equipamentos a serem devolvidos:</h5>
                    <ul>
                        <?php foreach ($equipamentosAtribuidos as $equipamento): ?>
                        <li><?php echo htmlspecialchars($equipamento['marca'] . ' ' . $equipamento['modelo']); ?> (Patrimônio: <?php echo htmlspecialchars($equipamento['patrimonio']); ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="confirmation-form">
                <div class="confirmation-actions">
                    <button type="submit" name="confirmar" value="1" class="btn btn-danger">
                        <i class="fas fa-check"></i> Confirmar Exclusão
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
                
                <div class="confirmation-checkbox">
                    <label>
                        <input type="checkbox" required>
                        Confirmo que estou ciente de que esta ação não pode ser desfeita.
                    </label>
                </div>
            </form>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../js/script.js"></script>
    
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
    
    .colaborador-details {
        margin: 30px 0;
        padding: 20px;
        background: #f8f9fa;
        border-radius: var(--border-radius);
    }
    
    .colaborador-details h3 {
        color: var(--dark-color);
        margin-bottom: 15px;
    }
    
    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
    
    .warning-card {
        margin: 30px 0;
        padding: 20px;
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
    
    .equipamentos-list {
        margin-top: 15px;
        padding: 15px;
        background: white;
        border-radius: var(--border-radius);
    }
    
    .equipamentos-list h5 {
        color: var(--dark-color);
        margin-bottom: 10px;
    }
    
    .equipamentos-list ul {
        list-style: none;
        padding-left: 0;
    }
    
    .equipamentos-list li {
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }
    
    .equipamentos-list li:last-child {
        border-bottom: none;
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
    }
    
    .confirmation-checkbox label {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        cursor: pointer;
    }
    
    .alert-warning {
        background: #fff3cd;
        color: #856404;
        border-left: 4px solid #ffc107;
    }
    </style>
</body>
</html>