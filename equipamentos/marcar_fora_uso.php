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

// Verificar se o equipamento já está fora de uso
if ($equipamento['status'] === 'fora_uso') {
    $_SESSION['mensagem'] = 'Este equipamento já está marcado como Fora de Uso.';
    $_SESSION['mensagem_tipo'] = 'warning';
    header('Location: index.php');
    exit;
}

// Obter informações do colaborador (se houver)
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
$sucesso = false;

// Processar marcação como fora de uso
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $motivo = trim($_POST['motivo'] ?? '');
    $destino = $_POST['destino'] ?? 'descartar';
    $observacoes = trim($_POST['observacoes'] ?? '');
    
    // Validações
    if (empty($motivo)) {
        $erro = 'Informe o motivo para marcar o equipamento como fora de uso.';
    } elseif (!in_array($destino, ['descartar', 'arquivar', 'guardar'])) {
        $erro = 'Destino inválido.';
    } else {
        // Registrar histórico
        $registro_fora_uso = [
            'data' => date('Y-m-d H:i:s'),
            'motivo' => $motivo,
            'destino' => $destino,
            'observacoes' => $observacoes,
            'usuario' => $_SESSION['usuario_nome'] ?? 'Administrador',
            'status_anterior' => $equipamento['status']
        ];
        
        // Inicializar histórico se não existir
        if (!isset($equipamento['historico_fora_uso']) || !is_array($equipamento['historico_fora_uso'])) {
            $equipamento['historico_fora_uso'] = [];
        }
        
        // Adicionar ao histórico
        $equipamento['historico_fora_uso'][] = $registro_fora_uso;
        
        // Registrar no histórico de observações
        $observacao_fora_uso = "\n\n[FORA DE USO] " . date('d/m/Y H:i:s');
        $observacao_fora_uso .= "\nMotivo: " . $motivo;
        $observacao_fora_uso .= "\nStatus anterior: " . getStatusTexto($equipamento['status']);
        $observacao_fora_uso .= "\nDestino: " . ($destino === 'descartar' ? 'Descartar' : ($destino === 'arquivar' ? 'Arquivar' : 'Guardar em estoque separado'));
        
        if ($colaboradorNome !== 'N/A') {
            $observacao_fora_uso .= "\nColaborador: " . $colaboradorNome;
        }
        
        if (!empty($observacoes)) {
            $observacao_fora_uso .= "\nObservações adicionais: " . $observacoes;
        }
        
        $observacoesAtuais = $equipamento['observacoes'] ?? '';
        $equipamento['observacoes'] = $observacoesAtuais . 
            (empty($observacoesAtuais) ? '' : "\n\n") . 
            $observacao_fora_uso;
        
        // Remover vínculo com colaborador se houver
        if ($equipamento['colaborador_id']) {
            $equipamento['colaborador_id'] = null;
            $equipamento['data_atribuicao'] = null;
        }
        
        // Atualizar status
        $equipamento['status'] = 'fora_uso';
        $equipamento['data_fora_uso'] = date('Y-m-d H:i:s');
        $equipamento['motivo_fora_uso'] = $motivo;
        $equipamento['destino_fora_uso'] = $destino;
        $equipamento['data_atualizacao'] = date('Y-m-d H:i:s');
        
        // Atualizar no array
        $equipamentos[$equipamentoIndex] = $equipamento;
        
        // Salvar no JSON
        if (salvarArquivoJSON('../data/equipamentos.json', $equipamentos)) {
            $_SESSION['mensagem'] = 'Equipamento marcado como Fora de Uso com sucesso!';
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
    <title>Marcar Fora de Uso - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/equipamentos/marcar_fora_uso.css">
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
                <h1><i class="fas fa-times-circle"></i> Marcar Fora de Uso</h1>
                <p class="page-subtitle">Registre a baixa permanente do equipamento</p>
            </div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>
        
        <div class="warning-card-out-of-use">
            <div class="warning-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h4>Ação Irreversível!</h4>
            </div>
            <p>Marcar um equipamento como <strong>Fora de Uso</strong> é uma ação permanente.</p>
            <ul>
                <li>O equipamento não poderá mais ser utilizado ou alocado</li>
                <li>O vínculo com colaborador será removido automaticamente</li>
                <li>Esta ação não pode ser desfeita pelo sistema</li>
                <li>Um registro será mantido no histórico do equipamento</li>
            </ul>
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
                    <span class="info-label">Centro de Custo:</span>
                    <span class="info-value"><?php echo htmlspecialchars($equipamento['centro_custo']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status Atual:</span>
                    <span class="info-value">
                        <span class="status-badge status-<?php 
                            echo $equipamento['status'] === 'estoque' ? 'ativo' : 
                                ($equipamento['status'] === 'alocado' ? 'inativo' : 
                                ($equipamento['status'] === 'emprestado' ? 'info' : 
                                ($equipamento['status'] === 'manutencao' ? 'warning' : 'danger'))); 
                        ?>">
                            <i class="fas fa-<?php echo getIconByStatus($equipamento['status']); ?>"></i>
                            <?php echo getStatusTexto($equipamento['status']); ?>
                        </span>
                    </span>
                </div>
                <?php if ($colaboradorNome !== 'N/A'): ?>
                <div class="info-item">
                    <span class="info-label">Colaborador Atual:</span>
                    <span class="info-value">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($colaboradorNome); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Formulário -->
        <form method="POST" action="" class="form-card" id="form-fora-uso">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="motivo">
                        <i class="fas fa-question-circle"></i>
                        <span>Motivo para Marcar como Fora de Uso</span>
                        <span class="required">*</span>
                    </label>
                    <select id="motivo" name="motivo" required class="form-select">
                        <option value="">-- Selecione o motivo --</option>
                        <option value="danificado_irreparavel" <?php echo (isset($_POST['motivo']) && $_POST['motivo'] === 'danificado_irreparavel') ? 'selected' : ''; ?>>Danificado - Irreparável</option>
                        <option value="obsoleto" <?php echo (isset($_POST['motivo']) && $_POST['motivo'] === 'obsoleto') ? 'selected' : ''; ?>>Obsoleto / Desatualizado</option>
                        <option value="extraviado" <?php echo (isset($_POST['motivo']) && $_POST['motivo'] === 'extraviado') ? 'selected' : ''; ?>>Extraviado / Perdido</option>
                        <option value="roubo" <?php echo (isset($_POST['motivo']) && $_POST['motivo'] === 'roubo') ? 'selected' : ''; ?>>Roubo / Furto</option>
                        <option value="devolucao_parcial" <?php echo (isset($_POST['motivo']) && $_POST['motivo'] === 'devolucao_parcial') ? 'selected' : ''; ?>>Devolução Parcial (acessórios faltantes)</option>
                        <option value="venda" <?php echo (isset($_POST['motivo']) && $_POST['motivo'] === 'venda') ? 'selected' : ''; ?>>Venda / Alienação</option>
                        <option value="outro" <?php echo (isset($_POST['motivo']) && $_POST['motivo'] === 'outro') ? 'selected' : ''; ?>>Outro</option>
                    </select>
                    <small class="form-text">Selecione o motivo principal para baixa do equipamento</small>
                </div>
                
                <div class="form-group full-width">
                    <label for="destino">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Destino do Equipamento</span>
                        <span class="required">*</span>
                    </label>
                    <div class="radio-options">
                        <label class="radio-option">
                            <input type="radio" name="destino" value="descartar" 
                                   <?php echo (!isset($_POST['destino']) || $_POST['destino'] === 'descartar') ? 'checked' : ''; ?>>
                            <div class="radio-content">
                                <i class="fas fa-trash-alt"></i>
                                <div>
                                    <span class="radio-title">Descartar</span>
                                    <small class="radio-description">Equipamento será descartado conforme normas ambientais</small>
                                </div>
                            </div>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="destino" value="arquivar" 
                                   <?php echo (isset($_POST['destino']) && $_POST['destino'] === 'arquivar') ? 'checked' : ''; ?>>
                            <div class="radio-content">
                                <i class="fas fa-archive"></i>
                                <div>
                                    <span class="radio-title">Arquivar</span>
                                    <small class="radio-description">Equipamento será arquivado para fins de registro</small>
                                </div>
                            </div>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="destino" value="guardar" 
                                   <?php echo (isset($_POST['destino']) && $_POST['destino'] === 'guardar') ? 'checked' : ''; ?>>
                            <div class="radio-content">
                                <i class="fas fa-box"></i>
                                <div>
                                    <span class="radio-title">Guardar em estoque separado</span>
                                    <small class="radio-description">Equipamento será guardado em local separado do estoque ativo</small>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label for="observacoes">
                        <i class="fas fa-sticky-note"></i>
                        <span>Observações Adicionais</span>
                    </label>
                    <textarea id="observacoes" name="observacoes" class="form-control" 
                              rows="3" placeholder="Informações complementares sobre a baixa, localização, detalhes do ocorrido..."><?php echo htmlspecialchars($_POST['observacoes'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <?php if (!empty($erro)): ?>
            <div class="alert-error-card">
                <i class="fas fa-exclamation-circle"></i>
                <div class="alert-content">
                    <strong>Erro:</strong> <?php echo $erro; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="confirmation-checkbox">
                <label class="checkbox-label">
                    <input type="checkbox" id="confirmCheckbox" required>
                    <span class="checkbox-custom"></span>
                    <span class="checkbox-text">Confirmo que estou ciente de que esta ação é <strong>irreversível</strong> e que o equipamento será marcado como <strong>Fora de Uso</strong> permanentemente.</span>
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-danger" id="btnConfirmar" disabled>
                    <i class="fas fa-times-circle"></i>
                    <span>Marcar como Fora de Uso</span>
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
                $equipamentos_fora_uso = 0;
                foreach ($equipamentos_data as $e) {
                    if (($e['status'] ?? '') === 'fora_uso') $equipamentos_fora_uso++;
                }
                ?>
                <div class="footer-stats">
                    <div class="footer-stat">
                        <span class="stat-number"><?php echo $total_equipamentos; ?></span>
                        <span class="stat-label">Equipamentos</span>
                    </div>
                    <div class="footer-stat">
                        <span class="stat-number"><?php echo $equipamentos_fora_uso; ?></span>
                        <span class="stat-label">Fora de Uso</span>
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
            const form = document.getElementById('form-fora-uso');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const motivo = document.getElementById('motivo').value;
                    const confirmado = document.getElementById('confirmCheckbox').checked;
                    
                    if (!confirmado) {
                        alert('Você precisa confirmar que está ciente da irreversibilidade desta ação.');
                        e.preventDefault();
                        return false;
                    }
                    
                    if (motivo === '') {
                        alert('Selecione o motivo para marcar o equipamento como fora de uso.');
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