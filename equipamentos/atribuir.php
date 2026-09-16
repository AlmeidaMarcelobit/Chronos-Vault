<?php
session_start();
require_once '../includes/funcoes.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$id = $_GET['id'] ?? null;
$tipo = $_GET['tipo'] ?? 'alocado';

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
if ($equipamento['status'] !== 'estoque') {
    $_SESSION['mensagem'] = 'Este equipamento não pode ser ' . ($tipo === 'emprestado' ? 'emprestado' : 'alocado') .
            '! Status atual: ' . getStatusTexto($equipamento['status']) .
            '. Apenas equipamentos "Em Estoque" podem ser atribuídos.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

$erro = '';

// Processar atribuição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $colaborador_id = $_POST['colaborador_id'] ?? null;
    $data_devolucao = $_POST['data_devolucao'] ?? null;
    $observacoes = trim($_POST['observacoes'] ?? '');
    $atualizar_centro_custo = isset($_POST['atualizar_centro_custo']) && $_POST['atualizar_centro_custo'] === 'sim';

    if ($colaborador_id) {
        // Buscar centro de custo do colaborador
        $centroCustoColaborador = null;
        $colaboradorNome = '';
        foreach ($colaboradores as $colab) {
            if ($colab['id'] == $colaborador_id) {
                $centroCustoColaborador = $colab['centro_custo'];
                $colaboradorNome = $colab['nome'];
                break;
            }
        }

        // Registrar centro de custo original
        $centroCustoOriginal = $equipamento['centro_custo'];

        // Atualizar equipamento
        $equipamentos[$equipamentoIndex]['colaborador_id'] = (int)$colaborador_id;
        $equipamentos[$equipamentoIndex]['status'] = $tipo;
        $equipamentos[$equipamentoIndex]['data_atribuicao'] = date('Y-m-d H:i:s');
        $equipamentos[$equipamentoIndex]['data_atualizacao'] = date('Y-m-d H:i:s');

        // Atualizar centro de custo se a opção estiver marcada
        if ($atualizar_centro_custo && $centroCustoColaborador) {
            // Registrar no histórico de centro de custo
            if (!isset($equipamento['historico_centro_custo']) || !is_array($equipamento['historico_centro_custo'])) {
                $equipamentos[$equipamentoIndex]['historico_centro_custo'] = [];
            }

            $historicoCC = [
                    'data' => date('Y-m-d H:i:s'),
                    'usuario' => $_SESSION['usuario_nome'] ?? 'Administrador',
                    'centro_custo_anterior' => $centroCustoOriginal,
                    'centro_custo_novo' => $centroCustoColaborador,
                    'motivo' => "Atribuição automática - Equipamento alocado para {$colaboradorNome}"
            ];
            $equipamentos[$equipamentoIndex]['historico_centro_custo'][] = $historicoCC;

            // Atualizar centro de custo do equipamento
            $equipamentos[$equipamentoIndex]['centro_custo'] = $centroCustoColaborador;
        }

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

        // Adicionar observação sobre a mudança de centro de custo
        if ($atualizar_centro_custo && $centroCustoColaborador && $centroCustoOriginal != $centroCustoColaborador) {
            $observacaoAtual = $equipamentos[$equipamentoIndex]['observacoes'] ?? '';
            $novaObservacao = "\n\n[CENTRO DE CUSTO] " . date('d/m/Y H:i:s');
            $novaObservacao .= "\nCentro de custo atualizado automaticamente de {$centroCustoOriginal} para {$centroCustoColaborador}";
            $novaObservacao .= "\nMotivo: Atribuição do equipamento para {$colaboradorNome}";
            $equipamentos[$equipamentoIndex]['observacoes'] = $observacaoAtual . $novaObservacao;
        }

        // Salvar no JSON
        if (salvarArquivoJSON('../data/equipamentos.json', $equipamentos)) {
            $mensagemExtra = '';
            if ($atualizar_centro_custo && $centroCustoColaborador && $centroCustoOriginal != $centroCustoColaborador) {
                $mensagemExtra = " O centro de custo foi atualizado de {$centroCustoOriginal} para {$centroCustoColaborador}.";
            }
            $_SESSION['mensagem'] = 'Equipamento ' . ($tipo === 'emprestado' ? 'emprestado' : 'alocado') . ' com sucesso para o colaborador!' . $mensagemExtra;
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
    <link rel="stylesheet" href="../css/equipamentos/atribuir.css">
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
        </ul>
    </nav>
</header>

<!-- ==================== CONTEÚDO PRINCIPAL ==================== -->
<main class="main-container">
    <div class="page-header">
        <div>
            <h1>
                <i class="fas fa-<?php echo $tipo === 'emprestado' ? 'handshake' : 'user-check'; ?>"></i>
                <?php echo $tipo === 'emprestado' ? 'Emprestar' : 'Alocar'; ?> Equipamento
            </h1>
            <p class="page-subtitle">
                <?php echo $tipo === 'emprestado' ? 'Realize um empréstimo temporário' : 'Realize uma alocação permanente'; ?> do equipamento
            </p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            <span>Voltar</span>
        </a>
    </div>

    <div class="form-card-container">
        <!-- Informações do Equipamento -->
        <div class="info-card equipment-info-card">
            <h3><i class="fas fa-laptop"></i> Equipamento a ser <?php echo $tipo === 'emprestado' ? 'emprestado' : 'alocado'; ?>:</h3>
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
                    <span class="info-label">Centro de Custo Atual:</span>
                    <span class="info-value">
                            <span class="cc-badge">
                                <i class="fas fa-dollar-sign"></i>
                                <?php echo htmlspecialchars($equipamento['centro_custo']); ?>
                            </span>
                        </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status Atual:</span>
                    <span class="info-value">
                            <span class="status-badge status-ativo">
                                <i class="fas fa-warehouse"></i>
                                <?php echo getStatusTexto($equipamento['status']); ?>
                            </span>
                        </span>
                </div>
            </div>
        </div>

        <!-- Formulário de Atribuição -->
        <form method="POST" action="" class="form-card" id="form-atribuicao">
            <div class="form-group">
                <label for="colaborador_id">
                    <i class="fas fa-user"></i>
                    <span>Selecionar Colaborador</span>
                    <span class="required">*</span>
                </label>
                <select id="colaborador_id" name="colaborador_id" required class="form-select" onchange="carregarCentroCustoColaborador()">
                    <option value="">-- Selecione um colaborador --</option>
                    <?php if (empty($colaboradores)): ?>
                        <option value="" disabled>Nenhum colaborador cadastrado</option>
                    <?php else: ?>
                        <?php foreach ($colaboradores as $colaborador): ?>
                            <option value="<?php echo $colaborador['id']; ?>" data-centro-custo="<?php echo htmlspecialchars($colaborador['centro_custo']); ?>" data-nome="<?php echo htmlspecialchars($colaborador['nome']); ?>">
                                <?php echo htmlspecialchars($colaborador['nome'] . ' - ' . $colaborador['cargo'] . ' (CC: ' . $colaborador['centro_custo'] . ')'); ?>
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

            <!-- Opção de atualizar centro de custo -->
            <div id="info-centro-custo" class="info-card" style="display: none; margin-bottom: var(--spacing-lg);">
                <h4><i class="fas fa-dollar-sign"></i> Informações de Centro de Custo</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Centro de Custo do Equipamento:</span>
                        <span class="info-value" id="cc-equipamento"><?php echo htmlspecialchars($equipamento['centro_custo']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Centro de Custo do Colaborador:</span>
                        <span class="info-value" id="cc-colaborador">---</span>
                    </div>
                </div>

                <div class="form-group" style="margin-top: var(--spacing-md);">
                    <label class="checkbox-label">
                        <input type="checkbox" id="atualizar_centro_custo" name="atualizar_centro_custo" value="sim">
                        <span class="checkbox-custom"></span>
                        <span class="checkbox-text">
                                <strong>Atualizar centro de custo do equipamento</strong><br>
                                <small>O centro de custo do equipamento será alterado para o centro de custo do colaborador. O histórico será registrado.</small>
                            </span>
                    </label>
                </div>
            </div>

            <?php if ($tipo === 'emprestado'): ?>
                <div class="form-group">
                    <label for="data_devolucao">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Data Prevista de Devolução</span>
                    </label>
                    <input type="date" id="data_devolucao" name="data_devolucao"
                           class="form-control"
                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    <small class="form-text">Opcional - Defina uma data para o empréstimo</small>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="observacoes">
                    <i class="fas fa-sticky-note"></i>
                    <span>Observações da <?php echo $tipo === 'emprestado' ? 'Empréstimo' : 'Alocação'; ?></span>
                </label>
                <textarea id="observacoes" name="observacoes" class="form-control"
                          rows="3" placeholder="<?php echo $tipo === 'emprestado' ? 'Motivo do empréstimo, condições especiais, local de uso...' : 'Observações sobre a alocação...'; ?>"></textarea>
            </div>

            <?php if (!empty($erro)): ?>
                <div class="alert-error-card">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $erro; ?></span>
                </div>
            <?php endif; ?>

            <!-- Card de Confirmação -->
            <div class="warning-card">
                <div class="warning-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h4>Confirmação</h4>
                </div>
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
                    <span><?php echo $tipo === 'emprestado' ? 'Confirmar Empréstimo' : 'Confirmar Alocação'; ?></span>
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    <span>Cancelar</span>
                </a>
            </div>
        </form>
    </div>
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
            $equipamentos_estoque = 0;
            foreach ($equipamentos_data as $e) {
                if (($e['status'] ?? '') === 'estoque') $equipamentos_estoque++;
            }
            ?>
            <div class="footer-stats">
                <div class="footer-stat">
                    <span class="stat-number"><?php echo $total_equipamentos; ?></span>
                    <span class="stat-label">Equipamentos</span>
                </div>
                <div class="footer-stat">
                    <span class="stat-number"><?php echo $equipamentos_estoque; ?></span>
                    <span class="stat-label">Em Estoque</span>
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
    function carregarCentroCustoColaborador() {
        const select = document.getElementById('colaborador_id');
        const selectedOption = select.options[select.selectedIndex];
        const centroCustoColaborador = selectedOption.getAttribute('data-centro-custo');
        const nomeColaborador = selectedOption.getAttribute('data-nome');
        const infoDiv = document.getElementById('info-centro-custo');
        const ccColaboradorSpan = document.getElementById('cc-colaborador');
        const ccEquipamentoSpan = document.getElementById('cc-equipamento');
        const equipamentoCC = '<?php echo $equipamento['centro_custo']; ?>';

        if (select.value && centroCustoColaborador) {
            ccColaboradorSpan.innerHTML = centroCustoColaborador;
            infoDiv.style.display = 'block';

            // Se os centros de custo são diferentes, destacar
            if (centroCustoColaborador !== equipamentoCC) {
                ccColaboradorSpan.style.color = 'var(--warning)';
                ccColaboradorSpan.style.fontWeight = 'bold';
                ccEquipamentoSpan.style.color = 'var(--danger)';
            } else {
                ccColaboradorSpan.style.color = 'var(--success)';
                ccEquipamentoSpan.style.color = 'var(--success)';
            }
        } else {
            infoDiv.style.display = 'none';
        }
    }

    // Validação do formulário
    const form = document.getElementById('form-atribuicao');
    if (form) {
        form.addEventListener('submit', function(e) {
            const colaborador = document.getElementById('colaborador_id').value;

            if (!colaborador) {
                alert('Selecione um colaborador para <?php echo $tipo === 'emprestado' ? 'emprestar' : 'alocar'; ?> o equipamento.');
                e.preventDefault();
                return false;
            }

            return true;
        });
    }

    // Definir data mínima para devolução
    const dataDevolucao = document.getElementById('data_devolucao');
    if (dataDevolucao) {
        const hoje = new Date();
        const amanha = new Date(hoje);
        amanha.setDate(hoje.getDate() + 1);
        dataDevolucao.min = amanha.toISOString().split('T')[0];
    }

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