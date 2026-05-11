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
    header('Location: index.php');
    exit;
}

$linhas = lerArquivoJSON('../data/linhas.json');
$colaboradores = lerArquivoJSON('../data/colaboradores.json');

// Encontrar linha
$linhaIndex = null;
foreach ($linhas as $index => $linha) {
    if ($linha['id'] == $id) {
        $linhaIndex = $index;
        $linhaAtual = $linha;
        break;
    }
}

if ($linhaIndex === null) {
    header('Location: index.php');
    exit;
}

// Verificar se a linha já está alocada
if ($linhaAtual['status'] !== 'disponivel') {
    $_SESSION['mensagem'] = 'Esta linha já está alocada para um colaborador.';
    $_SESSION['mensagem_tipo'] = 'warning';
    header('Location: index.php');
    exit;
}

$mensagem = '';
$tipoMensagem = '';

// Processar vinculação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $colaborador_id = $_POST['colaborador_id'] ?? null;

    if (empty($colaborador_id)) {
        $mensagem = 'Selecione um colaborador para vincular a linha.';
        $tipoMensagem = 'error';
    } else {
        // Buscar dados do colaborador
        $colaboradorSelecionado = null;
        foreach ($colaboradores as $colab) {
            if ($colab['id'] == $colaborador_id) {
                $colaboradorSelecionado = $colab;
                break;
            }
        }

        if (!$colaboradorSelecionado) {
            $mensagem = 'Colaborador não encontrado.';
            $tipoMensagem = 'error';
        } else {
            // Registrar centro de custo anterior
            $centroCustoAnterior = $linhaAtual['centro_custo'] ?? 'Não definido';
            $centroCustoNovo = $colaboradorSelecionado['centro_custo'];

            // Atualizar linha
            $linhas[$linhaIndex]['colaborador_id'] = $colaborador_id;
            $linhas[$linhaIndex]['status'] = 'alocado';
            $linhas[$linhaIndex]['data_atualizacao'] = date('Y-m-d H:i:s');
            $linhas[$linhaIndex]['data_atribuicao'] = date('Y-m-d H:i:s');

            // ATUALIZAR CENTRO DE CUSTO AUTOMATICAMENTE
            if (!isset($linhas[$linhaIndex]['historico_centro_custo']) || !is_array($linhas[$linhaIndex]['historico_centro_custo'])) {
                $linhas[$linhaIndex]['historico_centro_custo'] = [];
            }

            $historicoCC = [
                    'data' => date('Y-m-d H:i:s'),
                    'usuario' => $_SESSION['usuario_nome'] ?? 'Administrador',
                    'centro_custo_anterior' => $centroCustoAnterior,
                    'centro_custo_novo' => $centroCustoNovo,
                    'motivo' => 'Vinculação automática ao colaborador ' . $colaboradorSelecionado['nome']
            ];
            $linhas[$linhaIndex]['historico_centro_custo'][] = $historicoCC;
            $linhas[$linhaIndex]['centro_custo'] = $centroCustoNovo;

            // Adicionar observação
            $observacaoAtual = $linhas[$linhaIndex]['observacoes'] ?? '';
            $novaObservacao = "\n\n[VINCULAÇÃO] " . date('d/m/Y H:i:s');
            $novaObservacao .= "\nLinha vinculada ao colaborador: " . $colaboradorSelecionado['nome'];
            $novaObservacao .= "\nCentro de custo atualizado de {$centroCustoAnterior} para {$centroCustoNovo}";
            $linhas[$linhaIndex]['observacoes'] = $observacaoAtual . $novaObservacao;

            if (salvarArquivoJSON('../data/linhas.json', $linhas)) {
                $mensagem = "Linha vinculada com sucesso! Centro de custo atualizado de {$centroCustoAnterior} para {$centroCustoNovo}.";
                $tipoMensagem = 'success';
                header('Location: index.php');
                exit;
            } else {
                $mensagem = 'Erro ao vincular a linha. Tente novamente.';
                $tipoMensagem = 'error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vincular Linha - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/linhas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
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
            <li class="nav-item"><a href="../index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="../colaboradores/index.php" class="nav-link"><i class="fas fa-users"></i><span>Colaboradores</span></a></li>
            <li class="nav-item"><a href="../equipamentos/index.php" class="nav-link"><i class="fas fa-laptop"></i><span>Equipamentos</span></a></li>
            <li class="nav-item"><a href="index.php" class="nav-link active"><i class="fas fa-phone"></i><span>Linhas</span></a></li>
        </ul>
    </nav>
</header>

<?php if ($mensagem): ?>
    <div class="global-alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : 'error'; ?>">
        <div class="alert-content">
            <i class="fas fa-<?php echo $tipoMensagem === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <span><?php echo $mensagem; ?></span>
        </div>
        <button class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
<?php endif; ?>

<main class="main-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-user-plus"></i> Vincular Linha</h1>
            <p class="page-subtitle">Atribua esta linha a um colaborador</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="form-card-container">
        <!-- Informações da Linha -->
        <div class="info-card">
            <h3><i class="fas fa-phone"></i> Linha a ser vinculada</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Número:</span>
                    <span class="info-value numero-link"><?php echo formatarTelefone($linhaAtual['numero']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tipo:</span>
                    <span class="info-value"><?php echo getTipoLinhaTexto($linhaAtual['tipo']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Centro de Custo Atual:</span>
                    <span class="info-value">
                            <span class="cc-badge">
                                <i class="fas fa-dollar-sign"></i>
                                <?php echo htmlspecialchars($linhaAtual['centro_custo']); ?>
                            </span>
                        </span>
                </div>
            </div>
        </div>

        <!-- Formulário de Vinculação -->
        <form method="POST" action="" class="form-card">
            <div class="form-group">
                <label for="colaborador_id"><i class="fas fa-user"></i> Selecionar Colaborador <span class="required">*</span></label>
                <select id="colaborador_id" name="colaborador_id" required class="form-select" onchange="mostrarInfoCentroCusto()">
                    <option value="">-- Selecione um colaborador --</option>
                    <?php foreach ($colaboradores as $colaborador): ?>
                        <option value="<?php echo $colaborador['id']; ?>"
                                data-centro-custo="<?php echo htmlspecialchars($colaborador['centro_custo']); ?>"
                                data-nome="<?php echo htmlspecialchars($colaborador['nome']); ?>">
                            <?php echo htmlspecialchars($colaborador['nome'] . ' - ' . $colaborador['cargo'] . ' (CC: ' . $colaborador['centro_custo'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Informação de Centro de Custo -->
            <div id="info-centro-custo" class="info-card info-centro-custo" style="display: none; margin-top: var(--spacing-md);">
                <h4><i class="fas fa-dollar-sign"></i> Atualização Automática</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Centro de Custo da Linha:</span>
                        <span class="info-value" id="cc-linha"><?php echo htmlspecialchars($linhaAtual['centro_custo']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Centro de Custo do Colaborador:</span>
                        <span class="info-value" id="cc-colaborador">---</span>
                    </div>
                </div>
                <div class="alert-info" style="margin-top: var(--spacing-md); padding: var(--spacing-sm); border-radius: var(--radius-sm); background: rgba(52, 152, 219, 0.1);">
                    <i class="fas fa-sync-alt"></i>
                    <strong>Centro de custo será atualizado automaticamente!</strong>
                    <small>Ao vincular esta linha, o centro de custo será alterado para o centro de custo do colaborador selecionado.</small>
                </div>
            </div>

            <div class="warning-card" style="margin-top: var(--spacing-lg);">
                <div class="warning-header">
                    <i class="fas fa-info-circle"></i>
                    <h4>Confirmação</h4>
                </div>
                <p>Ao vincular esta linha:</p>
                <ul>
                    <li>O status será alterado para <strong>"Alocado"</strong></li>
                    <li>O colaborador ficará responsável pela linha</li>
                    <li>O centro de custo será <strong>atualizado automaticamente</strong> para o centro de custo do colaborador</li>
                    <li>O histórico da alteração será registrado</li>
                </ul>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Confirmar Vinculação</button>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            </div>
        </form>
    </div>
</main>

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
                <li><a href="../equipamentos/index.php"><i class="fas fa-laptop"></i> Equipamentos</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
        <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
</footer>

<script>
    function mostrarInfoCentroCusto() {
        const select = document.getElementById('colaborador_id');
        const selectedOption = select.options[select.selectedIndex];
        const centroCustoColaborador = selectedOption ? selectedOption.getAttribute('data-centro-custo') : null;
        const nomeColaborador = selectedOption ? selectedOption.getAttribute('data-nome') : null;
        const infoDiv = document.getElementById('info-centro-custo');
        const ccColaboradorSpan = document.getElementById('cc-colaborador');
        const ccLinhaSpan = document.getElementById('cc-linha');
        const centroCustoLinha = '<?php echo $linhaAtual['centro_custo']; ?>';

        if (select.value && centroCustoColaborador) {
            ccColaboradorSpan.innerHTML = centroCustoColaborador;
            infoDiv.style.display = 'block';

            // Destacar se são diferentes
            if (centroCustoColaborador !== centroCustoLinha) {
                ccColaboradorSpan.style.color = 'var(--warning)';
                ccColaboradorSpan.style.fontWeight = 'bold';
                ccLinhaSpan.style.color = 'var(--danger)';
            } else {
                ccColaboradorSpan.style.color = 'var(--success)';
                ccLinhaSpan.style.color = 'var(--success)';
            }
        } else {
            infoDiv.style.display = 'none';
        }
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