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

$erros = [];

// Processar finalização da manutenção
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = trim($_POST['resultado'] ?? '');
    $custo = !empty($_POST['custo']) ? str_replace(['.', ','], ['', '.'], $_POST['custo']) : null;
    $destino = $_POST['destino'] ?? 'colaborador';
    $colaboradorId = $_POST['colaborador_id'] ?? $equipamento['colaborador_id'] ?? null;
    
    // Se devolução automática está ativada, forçar destino para colaborador
    if ($devolverAutomaticamente && $colaboradorDevolucao) {
        $destino = 'colaborador';
        $colaboradorId = $colaboradorDevolucao['id'];
    }
    
    // Validações
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
    <link rel="stylesheet" href="../css/equipamentos/finalizar_manutencao.css">
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
                <h1><i class="fas fa-tools"></i> Finalizar Manutenção</h1>
                <p class="page-subtitle">Registre o resultado da manutenção e defina o destino do equipamento</p>
            </div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>
        
        <!-- Card do Equipamento -->
        <div class="info-card equipment-info-card">
            <h3><i class="fas fa-laptop"></i> Equipamento</h3>
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
        </div>
        
        <!-- Informações da Manutenção -->
        <?php if ($ultimaManutencao): ?>
        <div class="info-card maintenance-info-card">
            <h3><i class="fas fa-wrench"></i> Informações da Manutenção</h3>
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
                        <i class="fas fa-check-circle" style="color: var(--success);"></i> 
                        <strong>Devolução automática ativada</strong> - Equipamento será devolvido ao colaborador atual.
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Formulário -->
        <form method="POST" action="" class="form-card" id="form-manutencao">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="resultado">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Descrição do Serviço Realizado</span>
                        <span class="required">*</span>
                    </label>
                    <textarea id="resultado" name="resultado" class="form-control" 
                              rows="4" placeholder="Descreva em detalhes o que foi feito na manutenção, peças trocadas, testes realizados, diagnóstico final..."
                              required><?php echo htmlspecialchars($_POST['resultado'] ?? ''); ?></textarea>
                    <small class="form-text">Informe todos os procedimentos realizados durante a manutenção.</small>
                </div>
                
                <div class="form-group">
                    <label for="custo">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Custo da Manutenção (R$)</span>
                    </label>
                    <input type="text" id="custo" name="custo" 
                           class="form-control money-mask" 
                           placeholder="0,00"
                           value="<?php echo htmlspecialchars($_POST['custo'] ?? ''); ?>">
                    <small class="form-text">Informe o custo total da manutenção (opcional).</small>
                </div>
            </div>
            
            <!-- Destino do Equipamento -->
            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Destino do Equipamento *</label>
                
                <?php if ($devolverAutomaticamente && $colaboradorDevolucao): ?>
                    <div class="auto-return-card">
                        <div class="auto-return-header">
                            <i class="fas fa-check-circle"></i>
                            <strong>Devolução Automática</strong>
                        </div>
                        <p>Este equipamento foi configurado para <strong>devolução automática</strong>.</p>
                        <p>Ele será devolvido automaticamente para:</p>
                        <div class="destino-info">
                            <i class="fas fa-user-circle"></i>
                            <div>
                                <strong><?php echo htmlspecialchars($colaboradorDevolucao['nome']); ?></strong>
                                <small><?php echo htmlspecialchars($colaboradorDevolucao['departamento']); ?></small>
                            </div>
                        </div>
                        <input type="hidden" name="destino" value="colaborador">
                        <input type="hidden" name="colaborador_id" value="<?php echo $colaboradorDevolucao['id']; ?>">
                    </div>
                <?php else: ?>
                    <div class="radio-options">
                        <?php if ($colaboradorAtualInfo): ?>
                        <label class="radio-option">
                            <input type="radio" name="destino" value="colaborador" 
                                   <?php echo (!isset($_POST['destino']) || $_POST['destino'] === 'colaborador') ? 'checked' : ''; ?>
                                   onchange="toggleNovoColaboradorField()">
                            <div class="radio-content">
                                <i class="fas fa-user-check"></i>
                                <div>
                                    <span class="radio-title">Devolver para Colaborador</span>
                                    <small class="radio-description">
                                        Devolver para <?php echo htmlspecialchars($colaboradorAtualInfo['nome']); ?> 
                                        (<?php echo htmlspecialchars($colaboradorAtualInfo['departamento']); ?>)
                                    </small>
                                </div>
                            </div>
                        </label>
                        <?php endif; ?>
                        
                        <label class="radio-option">
                            <input type="radio" name="destino" value="estoque"
                                   <?php echo (isset($_POST['destino']) && $_POST['destino'] === 'estoque') ? 'checked' : ''; ?>
                                   onchange="toggleNovoColaboradorField()">
                            <div class="radio-content">
                                <i class="fas fa-warehouse"></i>
                                <div>
                                    <span class="radio-title">Colocar em Estoque</span>
                                    <small class="radio-description">O equipamento ficará disponível no estoque para futura atribuição.</small>
                                </div>
                            </div>
                        </label>
                        
                        <?php if (!$colaboradorAtualInfo): ?>
                        <label class="radio-option">
                            <input type="radio" name="destino" value="novo_colaborador"
                                   <?php echo (isset($_POST['destino']) && $_POST['destino'] === 'novo_colaborador') ? 'checked' : ''; ?>
                                   onchange="toggleNovoColaboradorField()">
                            <div class="radio-content">
                                <i class="fas fa-user-plus"></i>
                                <div>
                                    <span class="radio-title">Atribuir para Novo Colaborador</span>
                                    <small class="radio-description">Atribuir o equipamento para um colaborador diferente.</small>
                                </div>
                            </div>
                        </label>
                        <?php endif; ?>
                    </div>
                    
                    <div id="novo-colaborador-field" style="display: none;">
                        <div class="form-group" style="margin-top: var(--spacing-md);">
                            <label for="colaborador_id">
                                <i class="fas fa-user"></i>
                                <span>Selecionar Novo Colaborador</span>
                                <span class="required">*</span>
                            </label>
                            <select id="colaborador_id" name="colaborador_id" class="form-select">
                                <option value="">-- Selecione um colaborador --</option>
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
            
            <div class="form-actions">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check-circle"></i>
                    <span>Finalizar Manutenção</span>
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
        function toggleNovoColaboradorField() {
            const novoColaboradorRadio = document.querySelector('input[name="destino"][value="novo_colaborador"]');
            const novoColaboradorField = document.getElementById('novo-colaborador-field');
            const colaboradorSelect = document.getElementById('colaborador_id');
            
            if (novoColaboradorRadio && novoColaboradorRadio.checked) {
                novoColaboradorField.style.display = 'block';
                if (colaboradorSelect) colaboradorSelect.required = true;
            } else {
                novoColaboradorField.style.display = 'none';
                if (colaboradorSelect) colaboradorSelect.required = false;
            }
        }
        
        // Formatar campo de custo
        const custoInput = document.getElementById('custo');
        if (custoInput) {
            custoInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value) {
                    value = (parseInt(value) / 100).toFixed(2);
                    value = value.replace('.', ',');
                }
                e.target.value = value;
            });
        }
        
        // Inicializar estado do campo de novo colaborador
        <?php if (!$devolverAutomaticamente): ?>
        document.addEventListener('DOMContentLoaded', function() {
            toggleNovoColaboradorField();
            
            const radios = document.querySelectorAll('input[name="destino"]');
            radios.forEach(radio => {
                radio.addEventListener('change', toggleNovoColaboradorField);
            });
        });
        <?php endif; ?>
        
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