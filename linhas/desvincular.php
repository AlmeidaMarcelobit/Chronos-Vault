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

// Processar desvinculação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    // Registrar centro de custo antes de desvincular
    $centroCustoAtual = $linhaAtual['centro_custo'];

    $linhas[$linhaIndex]['colaborador_id'] = null;
    $linhas[$linhaIndex]['status'] = 'disponivel';
    $linhas[$linhaIndex]['data_atualizacao'] = date('Y-m-d H:i:s');
    $linhas[$linhaIndex]['data_atribuicao'] = null;

    // NÃO alterar o centro de custo - mantém o mesmo
    $linhas[$linhaIndex]['centro_custo'] = $centroCustoAtual;

    // Adicionar observação
    $observacaoAtual = $linhas[$linhaIndex]['observacoes'] ?? '';
    $novaObservacao = "\n\n[DESVINCULAÇÃO] " . date('d/m/Y H:i:s');
    $novaObservacao .= "\nLinha desvinculada do colaborador: " . $colaboradorNome;
    $novaObservacao .= "\nCentro de custo mantido: {$centroCustoAtual}";
    $linhas[$linhaIndex]['observacoes'] = $observacaoAtual . $novaObservacao;

    if (salvarArquivoJSON('../data/linhas.json', $linhas)) {
        $_SESSION['mensagem'] = 'Linha desvinculada com sucesso!';
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
                <div class="info-item"><span class="info-label">Tipo:</span><span class="info-value"><?php echo getTipoLinhaTexto($linhaAtual['tipo']); ?></span></div>
                <div class="info-item"><span class="info-label">Centro de Custo:</span><span class="info-value"><?php echo htmlspecialchars($linhaAtual['centro_custo']); ?></span></div>
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
                <li><strong>O centro de custo NÃO será alterado</strong> (permanecerá o mesmo)</li>
                <li>A linha ficará disponível para nova atribuição</li>
            </ul>
        </div>

        <form method="POST" action="" class="confirmation-form">
            <div class="confirmation-checkbox">
                <label class="checkbox-label">
                    <input type="checkbox" id="confirmCheckbox" required>
                    <span class="checkbox-custom"></span>
                    <span class="checkbox-text">Confirmo que desejo desvincular esta linha do colaborador.</span>
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
</html>