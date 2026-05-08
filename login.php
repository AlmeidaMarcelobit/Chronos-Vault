<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$mensagem = '';
if (isset($_GET['erro'])) {
    $mensagem = '<div class="alert alert-error">Usuário ou senha incorretos!</div>';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestão</title>
    <link rel="icon" href="img/Favicon/Favicon%20Main/favicon.ico">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
<div class="login-container">
    <div class="login-header">
        <h1><i class="fas fa-laptop-house"></i> Sistema de Gestão</h1>
        <p>Controle de Colaboradores e Equipamentos</p>
    </div>

    <div class="login-box">
        <h2>Login</h2>
        <?php echo $mensagem; ?>

        <form action="processa_login.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Usuário</label>
                <input type="text" id="username" name="username" required
                       placeholder="Digite seu usuário">
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Senha</label>
                <input type="password" id="password" name="password" required
                       placeholder="Digite sua senha">
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-sign-in-alt"></i> Entrar
            </button>
        </form>

        <div class="login-info">
            <p><strong>Credenciais padrão:</strong></p>
            <p>Usuário: <code>admin</code></p>
            <p>Senha: <code>admin123</code></p>
        </div>
    </div>

    <div class="login-footer">
        <p>Sistema de Gestão &copy; <?php echo date('Y'); ?></p>
    </div>
</div>
</body>
</html>