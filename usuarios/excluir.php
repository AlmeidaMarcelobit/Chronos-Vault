<?php
session_start();
require_once '../includes/funcoes.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

// Verificar se é administrador
if (($_SESSION['usuario_nivel'] ?? 'user') !== 'admin') {
    $_SESSION['mensagem'] = 'Acesso negado. Apenas administradores podem acessar esta página.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$usuarios = lerArquivoJSON('../data/usuarios.json');

// Encontrar usuário
$usuarioIndex = null;
$usuarioAtual = null;
foreach ($usuarios as $index => $usuario) {
    if ($usuario['id'] == $id) {
        $usuarioIndex = $index;
        $usuarioAtual = $usuario;
        break;
    }
}

if ($usuarioIndex === null) {
    $_SESSION['mensagem'] = 'Usuário não encontrado!';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

// Verificar se está tentando excluir a si mesmo
if ($usuarioAtual['id'] == $_SESSION['usuario_id']) {
    $_SESSION['mensagem'] = 'Você não pode excluir seu próprio usuário.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

$mensagem = '';
$tipoMensagem = '';

// Processar exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    // Remover usuário
    unset($usuarios[$usuarioIndex]);
    $usuarios = array_values($usuarios);

    if (salvarArquivoJSON('../data/usuarios.json', $usuarios)) {
        $_SESSION['mensagem'] = 'Usuário excluído com sucesso!';
        $_SESSION['mensagem_tipo'] = 'success';
        header('Location: index.php');
        exit;
    } else {
        $mensagem = 'Erro ao excluir o usuário. Tente novamente.';
        $tipoMensagem = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Usuário - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/usuarios.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header class="header">
    <div class="header-content">
        <div class="logo">
            <a href="../index.php">
                <i class="fas fa-laptop-house"></i>
                <h1>Gestão de Equipamentos</h1>
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
            <li class="nav-item"><a href="../linhas/index.php" class="nav-link"><i class="fas fa-phone"></i><span>Linhas</span></a></li>
            <li class="nav-item"><a href="../Termos/index.php" class="nav-link"><i class="fas fa-file-contract"></i><span>Termos</span></a></li>
            <li class="nav-item"><a href="index.php" class="nav-link active"><i class="fas fa-user-cog"></i><span>Usuários</span></a></li>
        </ul>
    </nav>
</header>

<main class="main-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-trash-alt"></i> Excluir Usuário</h1>
            <p class="page-subtitle">Confirme a exclusão do usuário</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <?php if ($mensagem): ?>
        <div class="global-alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : 'error'; ?>">
            <div class="alert-content">
                <i class="fas fa-<?php echo $tipoMensagem === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <span><?php echo $mensagem; ?></span>
            </div>
            <button class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
        </div>
    <?php endif; ?>

    <div class="confirmation-card">
        <h2><i class="fas fa-exclamation-triangle"></i> Confirmar Exclusão</h2>

        <div class="info-card">
            <h3><i class="fas fa-user"></i> Dados do Usuário</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Nome:</span>
                    <span class="info-value"><?php echo htmlspecialchars($usuarioAtual['nome']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Usuário:</span>
                    <span class="info-value"><?php echo htmlspecialchars($usuarioAtual['username']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">E-mail:</span>
                    <span class="info-value"><?php echo htmlspecialchars($usuarioAtual['email']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Nível:</span>
                    <span class="info-value">
                        <span class="nivel-badge nivel-<?php echo $usuarioAtual['nivel'] ?? 'user'; ?>">
                            <i class="fas fa-<?php echo ($usuarioAtual['nivel'] ?? 'user') === 'admin' ? 'crown' : (($usuarioAtual['nivel'] ?? 'user') === 'view' ? 'eye' : 'user'); ?>"></i>
                            <?php echo ($usuarioAtual['nivel'] ?? 'user') === 'admin' ? 'Administrador' : (($usuarioAtual['nivel'] ?? 'user') === 'view' ? 'Visualizador' : 'Usuário'); ?>
                        </span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        <span class="status-badge status-<?php echo (isset($usuarioAtual['ativo']) && $usuarioAtual['ativo'] === true) ? 'ativo' : 'inativo'; ?>">
                            <i class="fas fa-<?php echo (isset($usuarioAtual['ativo']) && $usuarioAtual['ativo'] === true) ? 'check-circle' : 'ban'; ?>"></i>
                            <?php echo (isset($usuarioAtual['ativo']) && $usuarioAtual['ativo'] === true) ? 'Ativo' : 'Inativo'; ?>
                        </span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Data de Cadastro:</span>
                    <span class="info-value"><?php echo isset($usuarioAtual['data_cadastro']) ? formatarData($usuarioAtual['data_cadastro']) : '---'; ?></span>
                </div>
            </div>
        </div>

        <div class="warning-card">
            <div class="warning-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h4>Atenção!</h4>
            </div>
            <p>Esta ação não pode ser desfeita. O usuário será permanentemente removido do sistema.</p>
        </div>

        <form method="POST" action="" class="confirmation-form">
            <div class="confirmation-checkbox">
                <label class="checkbox-label">
                    <input type="checkbox" id="confirmCheckbox" required>
                    <span class="checkbox-custom"></span>
                    <span class="checkbox-text">Confirmo que estou ciente de que esta ação é irreversível.</span>
                </label>
            </div>

            <div class="confirmation-actions">
                <button type="submit" name="confirmar" value="1" class="btn btn-danger" id="btnConfirmar" disabled>
                    <i class="fas fa-trash-alt"></i> Confirmar Exclusão
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
        <div class="footer-section">
            <h3>Estatísticas</h3>
            <div class="footer-stats">
                <div class="footer-stat">
                    <span class="stat-number"><?php echo count($usuarios); ?></span>
                    <span class="stat-label">Usuários</span>
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