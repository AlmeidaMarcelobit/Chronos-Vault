<?php
/*
 * login.php — Página de login autossuficiente.
 * Intencionalmente NÃO usa includes/header.php nem includes/footer.php,
 * pois essas estruturas exigem sessão ativa e renderizam a navbar,
 * comportamento indesejado na tela de autenticação.
 */
session_start();

// Configurações de segurança
ini_set('display_errors', 0);
error_reporting(0);

// Verificar se já está logado
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

// Verificar tentativas de login
$maxTentativas = 5;
$bloqueioTempo = 900; // 15 minutos em segundos

// Inicializar contador de tentativas
if (!isset($_SESSION['login_tentativas'])) {
    $_SESSION['login_tentativas'] = 0;
    $_SESSION['ultima_tentativa'] = time();
}

// Verificar se está bloqueado
$bloqueado = false;
if ($_SESSION['login_tentativas'] >= $maxTentativas) {
    $tempoDecorrido = time() - $_SESSION['ultima_tentativa'];
    if ($tempoDecorrido < $bloqueioTempo) {
        $bloqueado = true;
        $tempoRestante = ceil(($bloqueioTempo - $tempoDecorrido) / 60);
        $mensagem = '<div class="alert alert-warning">
                        <i class="fas fa-clock"></i> 
                        Muitas tentativas falhas. Aguarde ' . $tempoRestante . ' minutos para tentar novamente.
                     </div>';
    } else {
        // Resetar tentativas após o tempo de bloqueio
        $_SESSION['login_tentativas'] = 0;
        $bloqueado = false;
    }
}

// Mensagem de erro/aviso
$mensagem = '';
if (isset($_GET['erro'])) {
    $erro = $_GET['erro'];
    switch ($erro) {
        case 'timeout':
            $mensagem = '<div class="alert alert-warning">
                            <i class="fas fa-hourglass-end"></i> 
                            Sessão expirada. Por favor, faça login novamente.
                         </div>';
            break;
        case 'acesso':
            $mensagem = '<div class="alert alert-error">
                            <i class="fas fa-ban"></i> 
                            Acesso negado. Faça login para continuar.
                         </div>';
            break;
        default:
            if ($_SESSION['login_tentativas'] >= $maxTentativas) {
                // Já tratado acima
            } else {
                $restantes = $maxTentativas - $_SESSION['login_tentativas'];
                $mensagem = '<div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i> 
                                Usuário ou senha incorretos!<br>
                                <small>Tentativas restantes: ' . $restantes . '</small>
                             </div>';
            }
            break;
    }
}

// Recuperar usuário salvo (se houver "lembrar-me")
$usuarioSalvo = isset($_COOKIE['usuario_salvo']) ? htmlspecialchars($_COOKIE['usuario_salvo']) : '';
$lembrarChecked = !empty($usuarioSalvo);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Login - Sistema de Gestão</title>
    <link rel="icon" href="img/Favicon/Favicon%20Main/favicon.ico">
    <link rel="stylesheet" href="css/login.css">
    <link rel="icon" href="img/favicon/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">

<div class="login-container">
    <!-- Logo e Título -->
    <div class="login-header">
        <div class="logo-animation">
            <i class="fas fa-laptop-house"></i>
        </div>
        <h1>Sistema de Gestão</h1>
        <p>Controle de Colaboradores e Equipamentos</p>
    </div>

    <!-- Card de Login -->
    <div class="login-card">
        <div class="login-card-header">
            <h2>Bem-vindo</h2>
            <p>Informe suas credenciais para acessar o sistema</p>
        </div>

        <?php echo $mensagem; ?>

        <form action="processa_login.php" method="POST" class="login-form" id="loginForm">
            <!-- Campo Usuário -->
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i>
                    <span>Usuário</span>
                </label>
                <div class="input-group">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text"
                           id="username"
                           name="username"
                           value="<?php echo $usuarioSalvo; ?>"
                           required
                           autocomplete="username"
                           placeholder="Digite seu usuário"
                           autofocus>
                </div>
            </div>

            <!-- Campo Senha -->
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i>
                    <span>Senha</span>
                </label>
                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password"
                           id="password"
                           name="password"
                           required
                           autocomplete="current-password"
                           placeholder="Digite sua senha">
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Opções Extras -->
            <div class="login-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="lembrar" id="lembrar" value="1" <?php echo $lembrarChecked ? 'checked' : ''; ?>>
                    <span class="checkbox-custom"></span>
                    <span class="checkbox-text">Lembrar usuário</span>
                </label>
                <a href="#" class="forgot-password" id="forgotPasswordBtn">
                    <i class="fas fa-question-circle"></i> Esqueceu a senha?
                </a>
            </div>

            <!-- Botão de Login -->
            <button type="submit" class="btn btn-primary btn-block" id="btnLogin" <?php echo $bloqueado ? 'disabled' : ''; ?>>
                <i class="fas fa-sign-in-alt"></i>
                <span>Entrar no Sistema</span>
                <div class="btn-spinner" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </button>

            <!-- Informações de Desenvolvimento -->
            <?php if (defined('APP_ENV') && APP_ENV === 'development'): ?>
                <div class="dev-info">
                    <details>
                        <summary><i class="fas fa-code-branch"></i> Credenciais de Desenvolvimento</summary>
                        <div class="dev-creds">
                            <p><strong>Usuário:</strong> <code>admin</code></p>
                            <p><strong>Senha:</strong> <code>admin123</code></p>
                            <p><i class="fas fa-info-circle"></i> Estas credenciais são apenas para ambiente de desenvolvimento</p>
                        </div>
                    </details>
                </div>
            <?php endif; ?>
        </form>

        <!-- Indicador de Segurança -->
        <div class="security-badge">
            <i class="fas fa-shield-alt"></i>
            <span>Conexão Segura</span>
            <div class="security-dots">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="login-footer">
        <p>&copy; <?php echo date('Y'); ?> Sistema de Gestão - Todos os direitos reservados</p>
        <p class="version">Versão 2.0</p>
    </div>
</div>

<!-- Modal de Esqueci a Senha -->
<div id="forgotModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-key"></i> Recuperar Senha</h3>
            <button class="modal-close" onclick="closeForgotModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Entre em contato com o administrador do sistema para recuperar sua senha.</p>
            <div class="contact-info">
                <p><i class="fas fa-envelope"></i> marcelloaraujo1920@hotmail.com</p>
                <p><i class="fas fa-phone"></i> (11) 98801-3848</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeForgotModal()">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script src="js/login.js"></script>
<script>
    // Toggle password visibility
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.querySelector('.toggle-password i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleBtn.classList.remove('fa-eye');
            toggleBtn.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleBtn.classList.remove('fa-eye-slash');
            toggleBtn.classList.add('fa-eye');
        }
    }

    // Modal de esqueci a senha
    const forgotModal = document.getElementById('forgotModal');
    const forgotBtn = document.getElementById('forgotPasswordBtn');

    function openForgotModal() {
        forgotModal.style.display = 'block';
    }

    function closeForgotModal() {
        forgotModal.style.display = 'none';
    }

    if (forgotBtn) {
        forgotBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openForgotModal();
        });
    }

    // Fechar modal ao clicar fora
    window.onclick = function(event) {
        if (event.target == forgotModal) {
            closeForgotModal();
        }
    }

    // Adicionar efeito de loading no submit
    const loginForm = document.getElementById('loginForm');
    const btnLogin = document.getElementById('btnLogin');

    if (loginForm) {
        loginForm.addEventListener('submit', function() {
            if (btnLogin && !btnLogin.disabled) {
                const originalContent = btnLogin.innerHTML;
                btnLogin.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Entrando...';
                btnLogin.disabled = true;
            }
        });
    }

    // Animação de entrada dos elementos
    document.addEventListener('DOMContentLoaded', function() {
        const elements = document.querySelectorAll('.login-card, .login-header');
        elements.forEach((el, index) => {
            el.style.animationDelay = `${index * 0.1}s`;
            el.classList.add('fade-in-up');
        });
    });
</script>
</body>
</html>