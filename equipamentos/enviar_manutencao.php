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

$erros = [];

// Processar envio para manutenção
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $problema = trim($_POST['problema'] ?? '');
    $local_manutencao = trim($_POST['local_manutencao'] ?? '');
    $previsao_retorno = $_POST['previsao_retorno'] ?? null;
    $manter_com_colaborador = isset($_POST['manter_com_colaborador']) && $_POST['manter_com_colaborador'] === 'sim';
    
    // Validações
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
        
        // Se optou por remover o colaborador, desvincular
        if (!$manter_com_colaborador && $equipamento['colaborador_id']) {
            $equipamento['colaborador_id'] = null;
            $equipamento['data_atribuicao'] = null;
        }
        
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
    <link rel="stylesheet" href="../css/equipamentos/enviar_manutencao.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <h1><i class="fas fa-tools"></i> Enviar para Manutenção</h1>
                <p class="page-subtitle">Registre o envio do equipamento para manutenção</p>
            </div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>
        
        <!-- Card de Informações do Equipamento -->
        <div class="info-card equipment-info-card">
            <h3><i class="fas fa-laptop"></i> Informações do Equipamento</h3>
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
                        <span class="status-badge status-ativo">
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
        
        <!-- Formulário -->
        <form method="POST" action="" class="form-card" id="form-manutencao">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="problema">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Problema / Defeito</span>
                        <span class="required">*</span>
                    </label>
                    <textarea id="problema" name="problema" class="form-control" 
                              rows="4" placeholder="Descreva em detalhes o problema encontrado no equipamento..."
                              required><?php echo htmlspecialchars($_POST['problema'] ?? ''); ?></textarea>
                    <small class="form-text">Descreva o que está acontecendo com o equipamento, sintomas, quando começou, etc.</small>
                </div>
                
                <div class="form-group">
                    <label for="local_manutencao">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Local da Manutenção</span>
                        <span class="required">*</span>
                    </label>
                    <select id="local_manutencao" name="local_manutencao" class="form-select" required>
                        <option value="">Selecione o local...</option>
                        <option value="interno" <?php echo (isset($_POST['local_manutencao']) && $_POST['local_manutencao'] === 'interno') ? 'selected' : ''; ?>>Interno (Manutenção própria)</option>
                        <option value="externo_fornecedor" <?php echo (isset($_POST['local_manutencao']) && $_POST['local_manutencao'] === 'externo_fornecedor') ? 'selected' : ''; ?>>Externo (Fornecedor/Assistência técnica)</option>
                        <option value="garantia" <?php echo (isset($_POST['local_manutencao']) && $_POST['local_manutencao'] === 'garantia') ? 'selected' : ''; ?>>Garantia (Fabricante)</option>
                        <option value="outro" <?php echo (isset($_POST['local_manutencao']) && $_POST['local_manutencao'] === 'outro') ? 'selected' : ''; ?>>Outro</option>
                    </select>
                    <small class="form-text">Onde será realizada a manutenção?</small>
                </div>
                
                <div class="form-group">
                    <label for="previsao_retorno">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Previsão de Retorno</span>
                    </label>
                    <input type="date" id="previsao_retorno" name="previsao_retorno" 
                           class="form-control"
                           value="<?php echo htmlspecialchars($_POST['previsao_retorno'] ?? ''); ?>">
                    <small class="form-text">Data estimada para o retorno do equipamento (opcional)</small>
                </div>
            </div>
            
            <?php if ($temColaboradorAtual): ?>
            <div class="form-group">
                <label><i class="fas fa-user-tie"></i> Manter vínculo com colaborador?</label>
                <div class="radio-options">
                    <label class="radio-option">
                        <input type="radio" name="manter_com_colaborador" value="sim" checked>
                        <div class="radio-content">
                            <i class="fas fa-user-check"></i>
                            <div>
                                <span class="radio-title">Sim, manter vinculado ao colaborador</span>
                                <small class="radio-description">
                                    O equipamento permanecerá vinculado a <?php echo htmlspecialchars($colaboradorAtualInfo['nome']); ?>.
                                    Quando retornar da manutenção, será automaticamente devolvido.
                                </small>
                            </div>
                        </div>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="manter_com_colaborador" value="nao">
                        <div class="radio-content">
                            <i class="fas fa-user-slash"></i>
                            <div>
                                <span class="radio-title">Não, remover do colaborador</span>
                                <small class="radio-description">
                                    O equipamento será removido de <?php echo htmlspecialchars($colaboradorAtualInfo['nome']); ?>.
                                    Quando retornar da manutenção, irá para o estoque.
                                </small>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
            <?php else: ?>
                <input type="hidden" name="manter_com_colaborador" value="nao">
            <?php endif; ?>
            
            <!-- Erros -->
            <?php if (!empty($erros)): ?>
            <div class="alert-error-card">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="alert-content">
                    <strong>Erros encontrados:</strong>
                    <ul>
                        <?php foreach ($erros as $erro): ?>
                        <li><?php echo htmlspecialchars($erro); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Card Informativo -->
            <div class="info-card info-card-light">
                <h4><i class="fas fa-info-circle"></i> Como funciona:</h4>
                <p>Ao enviar este equipamento para manutenção:</p>
                <ul>
                    <li>O status será alterado para <strong>"Em Manutenção"</strong></li>
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
            
            <div class="form-actions">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-paper-plane"></i>
                    <span>Enviar para Manutenção</span>
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    <span>Cancelar</span>
                </a>
            </div>
        </form>
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
                $equipamentos_manutencao = 0;
                foreach ($equipamentos_data as $e) {
                    if (($e['status'] ?? '') === 'manutencao') $equipamentos_manutencao++;
                }
                ?>
                <div class="footer-stats">
                    <div class="footer-stat">
                        <span class="stat-number"><?php echo $total_equipamentos; ?></span>
                        <span class="stat-label">Equipamentos</span>
                    </div>
                    <div class="footer-stat">
                        <span class="stat-number"><?php echo $equipamentos_manutencao; ?></span>
                        <span class="stat-label">Em Manutenção</span>
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
                
                if (!previsaoInput.value) {
                    previsaoInput.value = dataSugerida;
                }
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