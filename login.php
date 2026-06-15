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
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:       #2563EB;
            --primary-dark:  #1D4ED8;
            --primary-light: #EFF6FF;
            --primary-glow:  rgba(37,99,235,.18);
            --danger:        #EF4444;
            --danger-light:  #FEF2F2;
            --warning:       #F59E0B;
            --warning-light: #FFFBEB;
            --success:       #10B981;
            --gray-50:  #F9FAFB;
            --gray-100: #F3F4F6;
            --gray-200: #E5E7EB;
            --gray-400: #9CA3AF;
            --gray-500: #6B7280;
            --gray-600: #4B5563;
            --gray-700: #374151;
            --gray-900: #111827;
            --white:    #FFFFFF;
            --radius:   10px;
            --radius-lg: 16px;
            --shadow-card: 0 20px 60px rgba(0,0,0,.12), 0 4px 16px rgba(0,0,0,.06);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--gray-50);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        /* Fundo decorativo sutil */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% -10%, rgba(37,99,235,.08) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 110%, rgba(37,99,235,.06) 0%, transparent 60%);
            pointer-events: none;
        }

        /* ── LAYOUT SPLIT ── */
        .login-wrap {
            display: flex;
            width: 100%;
            max-width: 900px;
            min-height: 560px;
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        /* ── PAINEL ESQUERDO ── */
        .panel-left {
            flex: 1;
            background: linear-gradient(145deg, var(--primary) 0%, #1e40af 100%);
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .panel-left::before {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
            top: -80px; right: -80px;
            pointer-events: none;
        }

        .panel-left::after {
            content: '';
            position: absolute;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,.05);
            bottom: -60px; left: -60px;
            pointer-events: none;
        }

        .brand { position: relative; z-index: 1; }

        .brand-icon {
            width: 56px; height: 56px;
            background: rgba(255,255,255,.15);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            color: #fff;
            margin-bottom: 1.5rem;
        }

        .brand h1 {
            font-size: 1.6rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.25;
            margin-bottom: .625rem;
        }

        .brand p {
            font-size: .875rem;
            color: rgba(255,255,255,.72);
            line-height: 1.6;
        }

        .panel-features { position: relative; z-index: 1; }

        .feature-item {
            display: flex;
            align-items: center;
            gap: .875rem;
            padding: .75rem 0;
            border-top: 1px solid rgba(255,255,255,.1);
        }

        .feature-item:last-child { border-bottom: 1px solid rgba(255,255,255,.1); }

        .feature-icon {
            width: 36px; height: 36px;
            background: rgba(255,255,255,.12);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: .875rem;
            color: rgba(255,255,255,.9);
            flex-shrink: 0;
        }

        .feature-text { font-size: .8rem; color: rgba(255,255,255,.78); line-height: 1.4; }
        .feature-text strong { display: block; color: #fff; font-size: .875rem; margin-bottom: 1px; }

        .panel-footer {
            position: relative; z-index: 1;
            font-size: .72rem;
            color: rgba(255,255,255,.4);
        }

        /* ── PAINEL DIREITO ── */
        .panel-right {
            width: 420px;
            flex-shrink: 0;
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-title { margin-bottom: 2rem; }
        .form-title h2 { font-size: 1.375rem; font-weight: 700; color: var(--gray-900); margin-bottom: .375rem; }
        .form-title p  { font-size: .875rem; color: var(--gray-500); }

        /* ── ALERTA ── */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: .625rem;
            padding: .75rem 1rem;
            border-radius: var(--radius);
            font-size: .8rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }

        .alert i { margin-top: 1px; flex-shrink: 0; }
        .alert-error   { background: var(--danger-light);  border: 1px solid #FECACA; color: #991B1B; }
        .alert-warning { background: var(--warning-light); border: 1px solid #FDE68A; color: #92400E; }

        /* ── FORM ── */
        .form-group { margin-bottom: 1.25rem; }

        .form-group label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: .5rem;
        }

        .input-wrap { position: relative; }

        .input-wrap .input-icon {
            position: absolute;
            left: .875rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: .875rem;
            pointer-events: none;
        }

        .input-wrap input {
            width: 100%;
            padding: .7rem .875rem .7rem 2.5rem;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius);
            font-family: inherit;
            font-size: .875rem;
            color: var(--gray-900);
            background: var(--gray-50);
            transition: border-color .2s, box-shadow .2s, background .2s;
            outline: none;
        }

        .input-wrap input:focus {
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        .input-wrap input::placeholder { color: var(--gray-400); }

        .toggle-pwd {
            position: absolute;
            right: .75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-400);
            cursor: pointer;
            padding: .25rem;
            font-size: .875rem;
            transition: color .2s;
        }

        .toggle-pwd:hover { color: var(--gray-600); }

        /* ── OPÇÕES ── */
        .login-opts {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .chk-label {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .8rem;
            color: var(--gray-600);
            cursor: pointer;
            user-select: none;
        }

        .chk-label input[type=checkbox] {
            width: 15px; height: 15px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .forgot-link {
            font-size: .8rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-link:hover { text-decoration: underline; }

        /* ── BOTÃO ── */
        .btn-login {
            width: 100%;
            padding: .8rem 1rem;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: var(--radius);
            font-family: inherit;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s, box-shadow .2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .625rem;
        }

        .btn-login:hover:not(:disabled) {
            background: var(--primary-dark);
            box-shadow: 0 4px 14px rgba(37,99,235,.4);
        }

        .btn-login:active:not(:disabled) { opacity: .9; }

        .btn-login:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        /* ── RODAPÉ DO FORM ── */
        .form-footer {
            margin-top: 2rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--gray-100);
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .75rem;
            color: var(--gray-400);
        }

        .form-footer i { color: var(--success); }

        /* ── MODAL ── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.45);
            z-index: 100;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal-overlay.open { display: flex; }

        .modal-box {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 2rem;
            max-width: 380px;
            width: 100%;
            box-shadow: 0 24px 64px rgba(0,0,0,.2);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
        }

        .modal-header h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .modal-close {
            background: var(--gray-100);
            border: none;
            width: 30px; height: 30px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            color: var(--gray-500);
            display: flex; align-items: center; justify-content: center;
            transition: background .2s;
        }

        .modal-close:hover { background: var(--gray-200); }

        .modal-body p { font-size: .875rem; color: var(--gray-600); margin-bottom: 1rem; }

        .contact-item {
            display: flex;
            align-items: center;
            gap: .625rem;
            padding: .625rem .875rem;
            background: var(--gray-50);
            border-radius: var(--radius);
            font-size: .8rem;
            color: var(--gray-700);
            margin-bottom: .5rem;
        }

        .contact-item i { color: var(--primary); width: 14px; text-align: center; }

        .modal-btn {
            width: 100%;
            margin-top: 1.25rem;
            padding: .65rem;
            background: var(--gray-100);
            border: none;
            border-radius: var(--radius);
            font-family: inherit;
            font-size: .875rem;
            font-weight: 600;
            color: var(--gray-700);
            cursor: pointer;
            transition: background .2s;
        }

        .modal-btn:hover { background: var(--gray-200); }

        /* ── RESPONSIVE ── */
        @media (max-width: 720px) {
            .panel-left { display: none; }
            .panel-right { width: 100%; padding: 2.5rem 2rem; }
            .login-wrap { max-width: 440px; }
        }

        @media (max-width: 400px) {
            .panel-right { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>

<div class="login-wrap">

    <!-- ── PAINEL ESQUERDO (branding) ── -->
    <div class="panel-left">
        <div class="brand">
            <div class="brand-icon"><i class="fas fa-laptop-house"></i></div>
            <h1>Sistema de<br>Gestão</h1>
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

        <div class="panel-footer">&copy; <?php echo date('Y'); ?> — Amor Saúde</div>
    </div>

    <!-- ── PAINEL DIREITO (formulário) ── -->
    <div class="panel-right">

        <div class="form-title">
            <h2>Bem-vindo de volta</h2>
            <p>Informe suas credenciais para acessar</p>
        </div>

        <?php if ($alerta): ?>
            <div class="alert alert-<?php echo $alertaTipo; ?>">
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
                    <button type="button" class="toggle-pwd" id="togglePwd" title="Mostrar/ocultar senha">
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
            Acesso protegido — <?php echo $maxTentativas; ?> tentativas máximas por sessão
        </div>

    </div>
</div>

<!-- ── MODAL ESQUECI A SENHA ── -->
<div class="modal-overlay" id="forgotModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fas fa-key"></i> Recuperar Senha</h3>
            <button class="modal-close" id="modalClose">&#x2715;</button>
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

<script>
// Toggle senha
document.getElementById('togglePwd').addEventListener('click', function () {
    var inp = document.getElementById('password');
    var ico = this.querySelector('i');
    if (inp.type === 'password') {
        inp.type = 'text';
        ico.className = 'fas fa-eye-slash';
    } else {
        inp.type = 'password';
        ico.className = 'fas fa-eye';
    }
});

// Modal
var modal = document.getElementById('forgotModal');

document.getElementById('forgotBtn').addEventListener('click', function (e) {
    e.preventDefault();
    modal.classList.add('open');
});

function closeModal() { modal.classList.remove('open'); }

document.getElementById('modalClose').addEventListener('click', closeModal);
document.getElementById('modalCloseBtn').addEventListener('click', closeModal);
modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

// Loading no submit
document.getElementById('loginForm').addEventListener('submit', function () {
    var btn = document.getElementById('btnLogin');
    if (btn.disabled) return;
    btn.disabled = true;
    document.getElementById('btnIcon').className   = 'fas fa-spinner fa-spin';
    document.getElementById('btnLabel').textContent = 'Entrando…';
});
</script>
</body>
</html>
