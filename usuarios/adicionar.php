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

$mensagem = '';
$tipoMensagem = '';
$usuarios = lerArquivoJSON('../data/usuarios.json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $nivel = $_POST['nivel'] ?? 'user';
    $ativo = isset($_POST['ativo']) && $_POST['ativo'] === '1';

    $erros = [];

    if (empty($username)) $erros[] = 'O nome de usuário é obrigatório.';
    elseif (strlen($username) < 3) $erros[] = 'O nome de usuário deve ter pelo menos 3 caracteres.';

    if (empty($nome)) $erros[] = 'O nome completo é obrigatório.';
    if (empty($email)) $erros[] = 'O e-mail é obrigatório.';
    elseif (!validarEmail($email)) $erros[] = 'E-mail inválido.';

    if (empty($password)) $erros[] = 'A senha é obrigatória.';
    elseif (strlen($password) < 6) $erros[] = 'A senha deve ter pelo menos 6 caracteres.';

    // Verificar duplicados
    foreach ($usuarios as $usuario) {
        if ($usuario['username'] === $username) $erros[] = 'Este nome de usuário já está cadastrado.';
        if ($usuario['email'] === $email) $erros[] = 'Este e-mail já está cadastrado.';
    }

    if (empty($erros)) {
        $novoUsuario = [
                'id' => gerarId($usuarios),
                'username' => $username,
                'nome' => $nome,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'nivel' => $nivel,
                'ativo' => $ativo,
                'data_cadastro' => date('Y-m-d H:i:s'),
                'data_atualizacao' => date('Y-m-d H:i:s')
        ];

        $usuarios[] = $novoUsuario;

        if (salvarArquivoJSON('../data/usuarios.json', $usuarios)) {
            $mensagem = 'Usuário criado com sucesso!';
            $tipoMensagem = 'success';
            $_POST = [];
        } else {
            $mensagem = 'Erro ao criar usuário. Tente novamente.';
            $tipoMensagem = 'error';
        }
    } else {
        $mensagem = implode('<br>', $erros);
        $tipoMensagem = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Usuário - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/usuarios.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header class="header">
    <div class="header-content">
        <div class="logo"><a href="../index.php"><i class="fas fa-laptop-house"></i><h1>Sistema de Gestão</h1></a></div>
        <div class="user-menu">
            <div class="user-info"><i class="fas fa-user-circle"></i><span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário'); ?></span><span class="user-level user-level-admin">Admin</span></div>
            <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i><span>Sair</span></a>
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
        <div><h1><i class="fas fa-user-plus"></i> Adicionar Usuário</h1><p class="page-subtitle">Crie um novo usuário para acessar o sistema</p></div>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>

    <?php if ($mensagem): ?>
        <div class="global-alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : 'error'; ?>">
            <div class="alert-content"><i class="fas fa-<?php echo $tipoMensagem === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i><span><?php echo $mensagem; ?></span></div>
            <button class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
        </div>
    <?php endif; ?>

    <div class="form-card-container">
        <form method="POST" action="" class="form-card">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nome"><i class="fas fa-user"></i> Nome Completo <span class="required">*</span></label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>" required class="form-control" placeholder="Digite o nome completo">
                </div>
                <div class="form-group">
                    <label for="username"><i class="fas fa-id-badge"></i> Nome de Usuário <span class="required">*</span></label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required class="form-control" placeholder="Ex: admin, joao.silva">
                    <small class="form-text">Usado para fazer login no sistema</small>
                </div>
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> E-mail <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required class="form-control" placeholder="usuario@empresa.com.br">
                </div>
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Senha <span class="required">*</span></label>
                    <input type="password" id="password" name="password" required class="form-control" placeholder="Mínimo 6 caracteres">
                    <small class="form-text">A senha deve ter pelo menos 6 caracteres</small>
                </div>
                <div class="form-group">
                    <label for="nivel"><i class="fas fa-shield-alt"></i> Nível de Acesso <span class="required">*</span></label>
                    <select id="nivel" name="nivel" required class="form-control">
                        <option value="view" <?php echo (isset($_POST['nivel']) && $_POST['nivel'] === 'view') ? 'selected' : ''; ?>>Visualizador (Apenas visualização)</option>
                        <option value="user" <?php echo (isset($_POST['nivel']) && $_POST['nivel'] === 'user') ? 'selected' : ''; ?>>Usuário Comum</option>
                        <option value="admin" <?php echo (isset($_POST['nivel']) && $_POST['nivel'] === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                    </select>
                    <small class="form-text">Administradores têm acesso total. Usuários comuns podem editar. Visualizadores apenas veem.</small>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-toggle-on"></i> Status</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="ativo" name="ativo" value="1" <?php echo (!isset($_POST['ativo']) || $_POST['ativo'] === '1') ? 'checked' : ''; ?>>
                        <label for="ativo" class="toggle-label"><span class="toggle-inner"></span><span class="toggle-switch"></span></label>
                        <span class="toggle-text">Ativo</span>
                    </div>
                    <small class="form-text">Usuários inativos não podem acessar o sistema</small>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Criar Usuário</button>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            </div>
        </form>
    </div>
</main>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-section"><h3><i class="fas fa-laptop-house"></i> Sistema de Gestão</h3><p>Controle de colaboradores e equipamentos</p></div>
        <div class="footer-section"><h3>Links Rápidos</h3><ul class="footer-links"><li><a href="../index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li><li><a href="../colaboradores/index.php"><i class="fas fa-users"></i> Colaboradores</a></li><li><a href="../equipamentos/index.php"><i class="fas fa-laptop"></i> Equipamentos</a></li></ul></div>
        <div class="footer-section"><h3>Estatísticas</h3><div class="footer-stats"><div class="footer-stat"><span class="stat-number"><?php echo count($usuarios); ?></span><span class="stat-label">Usuários</span></div></div></div>
    </div>
    <div class="footer-bottom"><p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p><p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p></div>
</footer>

<script>
    const nomeInput = document.getElementById('nome');
    const usernameInput = document.getElementById('username');
    if (nomeInput && usernameInput) {
        nomeInput.addEventListener('blur', function() {
            if (!usernameInput.value) {
                let nome = this.value.trim().toLowerCase();
                nome = nome.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                nome = nome.replace(/[^a-z0-9]/g, '.');
                nome = nome.replace(/\.+/g, '.');
                nome = nome.replace(/^\.|\.$/g, '');
                usernameInput.value = nome;
            }
        });
    }
    setTimeout(function() { const alert = document.querySelector('.global-alert'); if (alert) { alert.style.animation = 'slideOut 0.3s ease'; setTimeout(() => alert.remove(), 300); } }, 5000);
</script>
</body>
</html>