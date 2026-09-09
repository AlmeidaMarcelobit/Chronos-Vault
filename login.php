<?php
/*
 * login.php — Página de login autossuficiente.
 * Intencionalmente NÃO usa includes/header.php nem includes/footer.php,
 * pois essas estruturas exigem sessão ativa e renderizam a navbar,
 * comportamento indesejado na tela de autenticação.
 */
session_start();

ini_set('display_errors', 0);
error_reporting(0);

if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

// ── Rate limiting ─────────────────────────────────────────────────────────────
$maxTentativas  = 5;
$bloqueioTempo  = 900; // 15 minutos

if (!isset($_SESSION['login_tentativas'])) {
    $_SESSION['login_tentativas'] = 0;
    $_SESSION['ultima_tentativa'] = time();
}

$bloqueado     = false;
$tempoRestante = 0;

if ($_SESSION['login_tentativas'] >= $maxTentativas) {
    $decorrido = time() - $_SESSION['ultima_tentativa'];
    if ($decorrido < $bloqueioTempo) {
        $bloqueado     = true;
        $tempoRestante = ceil(($bloqueioTempo - $decorrido) / 60);
    } else {
        $_SESSION['login_tentativas'] = 0;
    }
}

// ── Mensagens ─────────────────────────────────────────────────────────────────
$alerta     = '';
$alertaTipo = '';

if ($bloqueado) {
    $alerta     = "Muitas tentativas falhas. Aguarde {$tempoRestante} minuto(s).";
    $alertaTipo = 'warning';
} elseif (isset($_GET['erro'])) {
    switch ($_GET['erro']) {
        case 'timeout':
            $alerta     = 'Sessão expirada. Faça login novamente.';
            $alertaTipo = 'warning';
            break;
        case 'acesso':
            $alerta     = 'Acesso negado. Faça login para continuar.';
            $alertaTipo = 'error';
            break;
        default:
            $restantes  = $maxTentativas - $_SESSION['login_tentativas'];
            $alerta     = "Usuário ou senha incorretos. Tentativas restantes: {$restantes}.";
            $alertaTipo = 'error';
            break;
    }
}

$usuarioSalvo   = isset($_COOKIE['usuario_salvo']) ? htmlspecialchars($_COOKIE['usuario_salvo']) : '';
$lembrarChecked = !empty($usuarioSalvo);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Sistema de Gestão</title>
    <link rel="icon" href="img/favicon/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
</head>

<body>

<main class="login-wrap">

    <!-- ── PAINEL ESQUERDO (branding) ── -->
    <div class="panel-left">
        <div class="brand">
            <div class="brand-icon"><i class="fas fa-laptop-house"></i></div>
            <div class="eyebrow">Amor Saúde</div><h1>Sistema de<br> Gestão</h1>
            <p>Controle centralizado de colaboradores, equipamentos e linhas telefônicas.</p>
        </div>

        <div class="panel-features">
            <div class="feature-item">
                <div class="feature-icon"><i class="fas fa-users"></i></div>
                <div class="feature-text">
                    <strong>Colaboradores</strong>
                    Cadastro, alocações e histórico
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="fas fa-laptop"></i></div>
                <div class="feature-text">
                    <strong>Equipamentos</strong>
                    Estoque, manutenção e rastreabilidade
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="fas fa-phone"></i></div>
                <div class="feature-text">
                    <strong>Linhas</strong>
                    Gestão de telefonia corporativa
                </div>
            </div>
        </div>

        <div class="panel-footer">&copy; 2024 - 2026 — Amor Saúde</div>
    </div>

    <!-- ── PAINEL DIREITO (formulário) ── -->
    <div class="panel-right">

        <div class="form-icon" aria-hidden="true"><i class="fas fa-lock"></i></div><div class="form-title">
            <h2>Bem-vindo de volta</h2>
            <p>Acesse sua conta para continuar a gestão.</p>
        </div>

        <?php if ($alerta): ?>
            <div class="alert alert-<?php echo $alertaTipo; ?>" role="alert">
                <i class="fas fa-<?php echo $alertaTipo === 'warning' ? 'clock' : 'exclamation-circle'; ?>"></i>
                <span><?php echo htmlspecialchars($alerta); ?></span>
            </div>
        <?php endif; ?>

        <form action="processa_login.php" method="POST" id="loginForm">

            <div class="form-group">
                <label for="username">Usuário</label>
                <div class="input-wrap">
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

            <div class="form-group">
                <label for="password">Senha</label>
                <div class="input-wrap">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password"
                           id="password"
                           name="password"
                           required
                           autocomplete="current-password"
                           placeholder="Digite sua senha">
                    <button type="button" class="toggle-pwd" id="togglePwd" title="Mostrar senha" aria-label="Mostrar senha" aria-pressed="false">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="login-opts">
                <label class="chk-label">
                    <input type="checkbox" name="lembrar" id="lembrar" value="1" <?php echo $lembrarChecked ? 'checked' : ''; ?>>
                    Lembrar usuário
                </label>
                <a href="#" class="forgot-link" id="forgotBtn">Esqueceu a senha?</a>
            </div>

            <button type="submit" class="btn-login" id="btnLogin" <?php echo $bloqueado ? 'disabled' : ''; ?>>
                <i class="fas fa-sign-in-alt" id="btnIcon"></i>
                <span id="btnLabel">Entrar</span>
            </button>

        </form>

        <div class="form-footer">
            <i class="fas fa-shield-alt"></i>
            Acesso exclusivo para usuários autorizados
        </div>

    </div>
</main>

<!-- ── MODAL ESQUECI A SENHA ── -->
<div class="modal-overlay" id="forgotModal" role="dialog" aria-modal="true" aria-labelledby="forgotTitle" aria-hidden="true">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="forgotTitle"><i class="fas fa-key"></i> Recuperar Senha</h3>
            <button class="modal-close" id="modalClose" aria-label="Fechar recuperação de senha">&#x2715;</button>
        </div>
        <div class="modal-body">
            <p>Entre em contato com o administrador do sistema para recuperar sua senha.</p>
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <span>marcelloaraujo1920@hotmail.com</span>
            </div>
            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <span>(11) 98801-3848</span>
            </div>
            <button class="modal-btn" id="modalCloseBtn">Fechar</button>
        </div>
    </div>
</div>

<script src="js/login.js"></script>
</body>
</html>
