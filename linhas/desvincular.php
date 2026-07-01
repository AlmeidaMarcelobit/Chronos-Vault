<?php
session_start();
require_once '../includes/funcoes.php';

$centroCustoPadrao = getCentroCustoPadrao();

$centroCustoNovo = $centroCustoPadrao;

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
$colaboradores = lerArquivoJSON('../data/colaboradores/ativos.json');

// Definir centro de custo padrão para linhas desvinculadas
define('CENTRO_CUSTO_PADRAO', '11001');

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

// Verificar se a linha está alocada
if ($linhaAtual['status'] !== 'alocado') {
    $_SESSION['mensagem'] = 'Esta linha não está alocada para nenhum colaborador.';
    $_SESSION['mensagem_tipo'] = 'warning';
    header('Location: index.php');
    exit;
}

// Buscar nome do colaborador
$colaboradorNome = '';
foreach ($colaboradores as $colaborador) {
    if ($colaborador['id'] == $linhaAtual['colaborador_id']) {
        $colaboradorNome = $colaborador['nome'];
        break;
    }
}

$mensagem = '';
$tipoMensagem = '';

// Processar desvinculação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    // Registrar centro de custo anterior
    $centroCustoAnterior = $linhaAtual['centro_custo'];
    $centroCustoNovo = CENTRO_CUSTO_PADRAO;

    // Registrar histórico de centro de custo
    if (!isset($linhas[$linhaIndex]['historico_centro_custo']) || !is_array($linhas[$linhaIndex]['historico_centro_custo'])) {
        $linhas[$linhaIndex]['historico_centro_custo'] = [];
    }

    $historicoCC = [
            'data' => date('Y-m-d H:i:s'),
            'usuario' => $_SESSION['usuario_nome'] ?? 'Administrador',
            'centro_custo_anterior' => $centroCustoAnterior,
            'centro_custo_novo' => $centroCustoNovo,
            'motivo' => 'Desvinculação - Linha removida do colaborador ' . $colaboradorNome
    ];
    $linhas[$linhaIndex]['historico_centro_custo'][] = $historicoCC;

    // Atualizar linha
    $linhas[$linhaIndex]['colaborador_id'] = null;
    $linhas[$linhaIndex]['status'] = 'disponivel';
    $linhas[$linhaIndex]['data_atualizacao'] = date('Y-m-d H:i:s');
    $linhas[$linhaIndex]['data_atribuicao'] = null;
    $linhas[$linhaIndex]['centro_custo'] = $centroCustoNovo;

    // Adicionar observação
    $observacaoAtual = $linhas[$linhaIndex]['observacoes'] ?? '';
    $novaObservacao = "\n\n[DESVINCULAÇÃO] " . date('d/m/Y H:i:s');
    $novaObservacao .= "\nLinha desvinculada do colaborador: " . $colaboradorNome;
    $novaObservacao .= "\nCentro de custo alterado de {$centroCustoAnterior} para {$centroCustoNovo} (padrão)";
    $linhas[$linhaIndex]['observacoes'] = $observacaoAtual . $novaObservacao;

    if (salvarArquivoJSON('../data/linhas.json', $linhas)) {
        $_SESSION['mensagem'] = "Linha desvinculada com sucesso! Centro de custo alterado de {$centroCustoAnterior} para {$centroCustoNovo}.";
        $_SESSION['mensagem_tipo'] = 'success';
        header('Location: index.php');
        exit;
    } else {
        $mensagem = 'Erro ao desvincular a linha. Tente novamente.';
        $tipoMensagem = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desvincular Linha - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/linhas/desvincular.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../img/favicon/favicon.png">
</head>
<body>
<header class="header">
    <div class="header-content">
        <div class="logo">
            <a href="../index.php">
                <i class="fas fa-laptop-house"></i>
                <h1>Gestão de Linhas</h1>
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
            <?php if (($_SESSION['usuario_nivel'] ?? '') === 'admin'): ?>
                <li class="nav-item"><a href="../Termos/index.php" class="nav-link"><i class="fas fa-file-contract"></i><span>Termos</span></a></li>
                <li class="nav-item"><a href="../usuarios/index.php" class="nav-link"><i class="fas fa-user-cog"></i><span>Usuários</span></a></li>
            <?php endif; ?>
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
            <h1><i class="fas fa-user-slash"></i> Desvincular Linha</h1>
            <p class="page-subtitle">Remova o vínculo da linha com o colaborador</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="confirmation-card">
        <h2><i class="fas fa-question-circle"></i> Confirmar Desvinculação</h2>

        <div class="info-card">
            <h3><i class="fas fa-phone"></i> Dados da Linha</h3>
            <div class="info-grid">
                <div class="info-item"><span class="info-label">Número:</span><span class="info-value"><?php echo formatarTelefone($linhaAtual['numero']); ?></span></div>
                <div class="info-item">
    <span class="info-label">IMEI:</span>
    <span class="info-value"><?php echo !empty($linhaAtual['imei']) ? htmlspecialchars($linhaAtual['imei']) : '---'; ?></span>
</div>
                <div class="info-item"><span class="info-label">Tipo:</span><span class="info-value"><?php echo getTipoLinhaTexto($linhaAtual['tipo']); ?></span></div>
                <div class="info-item">
                    <span class="info-label">Centro de Custo Atual:</span>
                    <span class="info-value">
                            <span class="cc-badge">
                                <i class="fas fa-dollar-sign"></i>
                                <?php echo htmlspecialchars($linhaAtual['centro_custo']); ?>
                            </span>
                        </span>
                </div>
                <div class="info-item"><span class="info-label">Colaborador:</span><span class="info-value"><?php echo htmlspecialchars($colaboradorNome); ?></span></div>
            </div>
        </div>

        <div class="warning-card">
            <div class="warning-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h4>Atenção!</h4>
            </div>
            <p>Ao desvincular esta linha:</p>
            <ul>
                <li>O status será alterado para <strong>"Disponível"</strong></li>
                <li>O vínculo com o colaborador será removido</li>
                <li>O centro de custo será alterado para <strong>11001 (Padrão)</strong></li>
                <li>Um registro será adicionado ao histórico</li>
                <li>A linha ficará disponível para nova atribuição</li>
            </ul>
        </div>

        <div class="info-card" style="background: rgba(231, 76, 60, 0.05); border-left: 3px solid var(--danger);">
            <h4><i class="fas fa-sync-alt"></i> O que vai acontecer?</h4>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Centro de Custo Antigo:</span>
                    <span class="info-value" style="color: var(--danger);">
                            <i class="fas fa-arrow-left"></i> <?php echo htmlspecialchars($linhaAtual['centro_custo']); ?>
                        </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Novo Centro de Custo:</span>
                    <span class="info-value" style="color: var(--success); font-weight: bold;">
                            <i class="fas fa-arrow-right"></i> 11001 (Padrão)
                        </span>
                </div>
            </div>
        </div>

        <form method="POST" action="" class="confirmation-form">
            <div class="confirmation-checkbox">
                <label class="checkbox-label">
                    <input type="checkbox" id="confirmCheckbox" required>
                    <span class="checkbox-custom"></span>
                    <span class="checkbox-text">
                            Confirmo que desejo desvincular esta linha do colaborador.
                            O centro de custo será alterado para <strong>11001</strong>.
                        </span>
                </label>
            </div>

            <div class="confirmation-actions">
                <button type="submit" name="confirmar" value="1" class="btn btn-warning" id="btnConfirmar" disabled>
                    <i class="fas fa-user-slash"></i> Confirmar Desvinculação
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</main>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3><i class="fas fa-laptop-house"></i>Gestão de Linhas</h3>
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
    const checkbox = document.getElementById('confirmCheckbox');
    const confirmBtn = document.getElementById('btnConfirmar');

    if (checkbox && confirmBtn) {
        checkbox.addEventListener('change', function() {
            confirmBtn.disabled = !this.checked;
        });
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
</html