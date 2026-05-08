<?php
// Verificar se a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está logado (exceto na página de login)
$current_page = basename($_SERVER['PHP_SELF']);
$is_login_page = ($current_page === 'login.php' || $current_page === 'processa_login.php');

if (!$is_login_page && !isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

// Obter o caminho base baseado na localização atual
$base_path = '';
if (strpos($_SERVER['PHP_SELF'], '/colaboradores/') !== false) {
    $base_path = '../';
} elseif (strpos($_SERVER['PHP_SELF'], '/equipamentos/') !== false) {
    $base_path = '../';
} elseif (strpos($_SERVER['PHP_SELF'], '/includes/') === false && $current_page !== 'index.php' && $current_page !== 'login.php') {
    $base_path = './';
}

// Determinar página ativa
$page_name = basename($_SERVER['PHP_SELF'], '.php');
$active_home = ($page_name === 'index') ? 'active' : '';
$active_colaboradores = (strpos($_SERVER['PHP_SELF'], 'colaboradores/') !== false) ? 'active' : '';
$active_equipamentos = (strpos($_SERVER['PHP_SELF'], 'equipamentos/') !== false) ? 'active' : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Sistema de Gestão'; ?></title>
    <link rel="icon" href="<?php echo $base_path; ?>img/Favicon/Favicon%20Main/favicon.ico">
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
    <?php if (!empty($page_css)): ?>
    <link rel="stylesheet" href="<?php echo $base_path . htmlspecialchars($page_css); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php if (!$is_login_page): ?>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <a href="<?php echo $base_path; ?>index.php" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-laptop-house"></i>
                    <h1>Sistema de Gestão</h1>
                </a>
            </div>
            
            <div class="user-menu">
                <?php if (isset($_SESSION['usuario_nome'])): ?>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
                </div>
                <?php endif; ?>
                
                <a href="<?php echo $base_path; ?>logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sair</span>
                </a>
            </div>
        </div>
        
        <nav class="nav-container">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="<?php echo $base_path; ?>index.php" class="nav-link <?php echo $active_home; ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $base_path; ?>colaboradores/index.php" class="nav-link <?php echo $active_colaboradores; ?>">
                        <i class="fas fa-users"></i>
                        <span>Colaboradores</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $base_path; ?>equipamentos/index.php" class="nav-link <?php echo $active_equipamentos; ?>">
                        <i class="fas fa-laptop"></i>
                        <span>Equipamentos</span>
                    </a>
                </li>
            </ul>
        </nav>
    </header>
    
    <!-- Notificação de mensagens -->
    <?php if (isset($_SESSION['mensagem'])): ?>
    <div class="global-alert alert-<?php echo $_SESSION['mensagem_tipo'] ?? 'info'; ?>">
        <div class="alert-content">
            <i class="fas fa-<?php echo $_SESSION['mensagem_tipo'] === 'success' ? 'check-circle' : ($_SESSION['mensagem_tipo'] === 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
            <span><?php echo htmlspecialchars($_SESSION['mensagem']); ?></span>
        </div>
        <button class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
    <?php 
    // Limpar mensagem após exibir
    unset($_SESSION['mensagem']);
    unset($_SESSION['mensagem_tipo']);
    endif; ?>
    
    <style>
    .global-alert {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 1000;
        padding: 15px 20px;
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 300px;
        max-width: 500px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }
    
    .alert-info {
        background: #d1ecf1;
        color: #0c5460;
        border-left: 4px solid #17a2b8;
    }
    
    .alert-warning {
        background: #fff3cd;
        color: #856404;
        border-left: 4px solid #ffc107;
    }
    
    .alert-content {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
    }
    
    .alert-close {
        background: none;
        border: none;
        color: inherit;
        font-size: 20px;
        cursor: pointer;
        padding: 0;
        margin-left: 10px;
    }
    
    @media (max-width: 768px) {
        .global-alert {
            top: 70px;
            left: 20px;
            right: 20px;
            max-width: none;
        }
    }
    </style>
    <?php endif; ?>