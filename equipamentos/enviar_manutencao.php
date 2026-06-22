<?php
session_start();
require_once '../includes/funcoes.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$usuario_nivel = $_SESSION['usuario_nivel'] ?? 'user';
$is_admin = $usuario_nivel === 'admin';
if ($usuario_nivel === 'view') {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Buscar equipamento em todos os status possíveis
$statuses = ['estoque', 'alocado', 'emprestado'];
$equipamento = null;
$statusOrigem = null;
$indexOrigem = null;

foreach ($statuses as $status) {
    $lista = carregarEquipamentosPorStatus($status);
    foreach ($lista as $i => $e) {
        if ($e['id'] == $id) {
            $equipamento = $e;
            $statusOrigem = $status;
            $indexOrigem = $i;
            break 2;
        }
    }
}

if (!$equipamento) {
    $_SESSION['mensagem'] = 'Equipamento não encontrado ou já está em manutenção/fora de uso.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

$erro = '';

// Processar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $problema = trim($_POST['problema'] ?? '');

    if (empty($problema)) {
        $erro = 'Descreva o problema do equipamento.';
    } else {
        // Atualizar equipamento
        $equipamento['status_anterior'] = $equipamento['status'];
        $equipamento['status'] = 'manutencao';
        $equipamento['data_manutencao'] = date('Y-m-d H:i:s');
        $equipamento['data_atualizacao'] = date('Y-m-d H:i:s');

        if (!isset($equipamento['historico_manutencao'])) {
            $equipamento['historico_manutencao'] = [];
        }
        $equipamento['historico_manutencao'][] = [
            'data_envio' => date('Y-m-d H:i:s'),
            'problema'   => $problema,
        ];

        // Remover da origem
        $listaOrigem = carregarEquipamentosPorStatus($statusOrigem);
        array_splice($listaOrigem, $indexOrigem, 1);
        $caminhoOrigem = getCaminhoEquipamentoPorStatus($statusOrigem);

        if (!salvarArquivoJSON($caminhoOrigem, $listaOrigem)) {
            $erro = 'Erro ao atualizar status de origem. Tente novamente.';
        } else {
            // Adicionar à manutenção
            $manutencao = carregarEquipamentosPorStatus('manutencao');
            $manutencao[] = $equipamento;
            $caminhoManutencao = getCaminhoEquipamentoPorStatus('manutencao');

            if (salvarArquivoJSON($caminhoManutencao, $manutencao)) {
                $_SESSION['mensagem'] = 'Equipamento ' . htmlspecialchars($equipamento['patrimonio']) . ' enviado para manutenção com sucesso!';
                $_SESSION['mensagem_tipo'] = 'success';
                header('Location: index.php');
                exit;
            } else {
                $erro = 'Erro ao salvar na manutenção. Tente novamente.';
            }
        }
    }
}
?>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar para Manutenção - Sistema de Gestão</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/favicon/favicon.png">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary: #2563EB; --primary-dark: #1D4ED8;
            --warning: #F59E0B; --warning-dark: #D97706; --warning-light: #FEF3C7;
            --gray-50: #F9FAFB; --gray-100: #F3F4F6; --gray-200: #E5E7EB;
            --gray-500: #6B7280; --gray-700: #374151; --gray-900: #111827;
            --white: #FFFFFF; --radius: 8px; --radius-lg: 12px;
            --shadow: 0 1px 3px rgba(0,0,0,.1); --shadow-lg: 0 10px 25px rgba(0,0,0,.1);
        }
        body { font-family: 'Inter', sans-serif; background: var(--gray-50); color: var(--gray-900); min-height: 100vh; }

        /* HEADER */
        .header { background: var(--white); border-bottom: 1px solid var(--gray-200); padding: 0 1.5rem; box-shadow: var(--shadow); }
        .header-content { display: flex; align-items: center; justify-content: space-between; height: 64px; }
        .logo a { display: flex; align-items: center; gap: .75rem; text-decoration: none; color: var(--primary); }
        .logo h1 { font-size: 1.125rem; font-weight: 700; }
        .user-menu { display: flex; align-items: center; gap: 1rem; }
        .user-info { display: flex; align-items: center; gap: .5rem; color: var(--gray-700); font-size: .875rem; }
        .logout-btn { display: flex; align-items: center; gap: .5rem; padding: .5rem 1rem; border-radius: var(--radius); background: var(--gray-100); color: var(--gray-700); text-decoration: none; font-size: .875rem; transition: background .2s; }
        .logout-btn:hover { background: var(--gray-200); }

        /* MAIN */
        .main-container { max-width: 640px; margin: 2rem auto; padding: 0 1rem; }

        /* CARD */
        .card { background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); overflow: hidden; }
        .card-header { background: linear-gradient(135deg, var(--warning) 0%, var(--warning-dark) 100%); padding: 1.5rem 2rem; color: var(--white); }
        .card-header h2 { font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: .75rem; }
        .card-header p { margin-top: .25rem; opacity: .9; font-size: .9rem; }
        .card-body { padding: 2rem; }

        /* EQUIPAMENTO INFO */
        .equip-info { background: var(--warning-light); border: 1px solid #FDE68A; border-radius: var(--radius); padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: .5rem .75rem; }
        .equip-info-item { display: flex; flex-direction: column; gap: 2px; }
        .equip-info-label { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--warning-dark); }
        .equip-info-value { font-size: .9rem; font-weight: 600; color: var(--gray-900); }

        /* FORM */
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: .875rem; font-weight: 600; color: var(--gray-700); margin-bottom: .5rem; }
        .form-group label span.required { color: #EF4444; margin-left: 2px; }
        textarea.form-control { width: 100%; padding: .75rem 1rem; border: 1px solid var(--gray-200); border-radius: var(--radius); font-family: inherit; font-size: .9rem; resize: vertical; min-height: 110px; transition: border-color .2s; }
        textarea.form-control:focus { outline: none; border-color: var(--warning); box-shadow: 0 0 0 3px rgba(245,158,11,.15); }

        /* ERRO */
        .alert-error { background: #FEF2F2; border: 1px solid #FECACA; color: #DC2626; padding: .875rem 1rem; border-radius: var(--radius); margin-bottom: 1.25rem; display: flex; align-items: center; gap: .5rem; font-size: .875rem; }

        /* BOTÕES */
        .form-actions { display: flex; gap: .75rem; justify-content: flex-end; margin-top: 1.5rem; }
        .btn { display: inline-flex; align-items: center; gap: .5rem; padding: .625rem 1.25rem; border-radius: var(--radius); font-size: .875rem; font-weight: 600; cursor: pointer; text-decoration: none; border: none; transition: background .2s, transform .1s; }
        .btn:active { transform: scale(.98); }
        .btn-secondary { background: var(--gray-100); color: var(--gray-700); }
        .btn-secondary:hover { background: var(--gray-200); }
        .btn-warning { background: var(--warning); color: var(--white); }
        .btn-warning:hover { background: var(--warning-dark); }
        .nav-container { background: var(--white); border-top: 1px solid var(--gray-100); }
        .nav-menu { max-width: 1440px; margin: 0 auto; padding: 0 2rem; list-style: none; display: flex; gap: 2rem; }
        .nav-link { display: flex; align-items: center; gap: .5rem; padding: 1rem 0; color: var(--gray-600); text-decoration: none; font-size: .875rem; font-weight: 500; transition: var(--transition); border-bottom: 2px solid transparent; }
        .nav-link:hover { color: var(--primary); }
        .nav-link.active { color: var(--primary); border-bottom-color: var(--primary); }
    </style>
</head>
<body>

<header class="header">
    <div class="header-content">
        <div class="logo">
            <a href="../index.php">
                <i class="fas fa-tools"></i>
                <h1>Gestão de Equipamentos</h1>
            </a>
        </div>
        <div class="user-menu">
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário'); ?></span>
            </div>
            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </div>
    </div>
</header>
    <nav class="nav-container">
        <ul class="nav-menu">
            <li class="nav-item"><a href="../index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="../colaboradores/index.php" class="nav-link"><i class="fas fa-users"></i><span>Colaboradores</span></a></li>
            <li class="nav-item"><a href="../equipamentos/index.php" class="nav-link active"><i class="fas fa-laptop"></i><span>Equipamentos</span></a></li>
            <li class="nav-item"><a href="../linhas/index.php" class="nav-link"><i class="fas fa-phone"></i><span>Linhas</span></a></li>
            <?php if (($_SESSION['usuario_nivel'] ?? '') === 'admin'): ?>
                <li class="nav-item"><a href="../Termos/index.php" class="nav-link"><i class="fas fa-file-contract"></i><span>Termos</span></a></li>
                <li class="nav-item"><a href="../usuarios/index.php" class="nav-link"><i class="fas fa-user-cog"></i><span>Usuários</span></a></li>
            <?php endif; ?>        </ul>
    </nav>

<main class="main-container">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-tools"></i> Enviar para Manutenção</h2>
            <p>Descreva o problema para registrar o envio</p>
        </div>
        <div class="card-body">

            <?php if ($erro): ?>
                <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>

            <div class="equip-info">
                <div class="equip-info-item">
                    <span class="equip-info-label">Patrimônio</span>
                    <span class="equip-info-value"><?php echo htmlspecialchars($equipamento['patrimonio']); ?></span>
                </div>
                <div class="equip-info-item">
                    <span class="equip-info-label">Tipo</span>
                    <span class="equip-info-value"><?php echo getTipoTexto($equipamento['tipo']); ?></span>
                </div>
                <div class="equip-info-item">
                    <span class="equip-info-label">Marca / Modelo</span>
                    <span class="equip-info-value"><?php echo htmlspecialchars(($equipamento['marca'] ?? '') . ' ' . ($equipamento['modelo'] ?? '')); ?></span>
                </div>
                <div class="equip-info-item">
                    <span class="equip-info-label">Status atual</span>
                    <span class="equip-info-value"><?php echo getStatusTexto($statusOrigem); ?></span>
                </div>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label for="problema">Descrição do problema <span class="required">*</span></label>
                    <textarea id="problema" name="problema" class="form-control" placeholder="Descreva o problema do equipamento..." required><?php echo htmlspecialchars($_POST['problema'] ?? ''); ?></textarea>
                </div>
                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Cancelar</a>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-tools"></i> Confirmar Envio</button>
                </div>
            </form>

        </div>
    </div>
</main>

</body>
</html>
