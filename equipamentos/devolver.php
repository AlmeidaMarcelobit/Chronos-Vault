<?php
session_start();
require_once '../includes/funcoes.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$usuario_nivel = $_SESSION['usuario_nivel'] ?? 'user';
if ($usuario_nivel === 'view') {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Buscar equipamento em alocados ou emprestados
$equipamento = null;
$statusOrigem = null;
$indexOrigem  = null;

foreach (['alocado', 'emprestado'] as $s) {
    $lista = carregarEquipamentosPorStatus($s);
    foreach ($lista as $i => $e) {
        if ($e['id'] == $id) {
            $equipamento  = $e;
            $statusOrigem = $s;
            $indexOrigem  = $i;
            break 2;
        }
    }
}

if (!$equipamento) {
    $_SESSION['mensagem']      = 'Equipamento não encontrado ou não está alocado/emprestado.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

// Colaboradores ativos
$colaboradores = lerArquivoJSON('../data/colaboradores/ativos.json');
if (!is_array($colaboradores)) $colaboradores = [];

$mapaColaboradores = [];
foreach ($colaboradores as $c) {
    $mapaColaboradores[$c['id']] = $c;
}

$colaboradorInfo = $mapaColaboradores[$equipamento['colaborador_id'] ?? ''] ?? null;
$colaboradorNome = $colaboradorInfo ? $colaboradorInfo['nome'] : 'N/A';

// Verificar atraso (apenas para emprestado)
$emAtraso = false;
if ($statusOrigem === 'emprestado' && !empty($equipamento['data_devolucao_prevista'])) {
    $emAtraso = strtotime($equipamento['data_devolucao_prevista']) < strtotime('today');
}

$erro = '';

// ── POST ──────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destino     = $_POST['destino']     ?? 'estoque';
    $observacoes = trim($_POST['observacoes'] ?? '');
    $problema    = trim($_POST['problema']    ?? '');

    if (!in_array($destino, ['estoque', 'manutencao', 'fora_uso'])) {
        $erro = 'Destino inválido.';
    } elseif ($destino === 'manutencao' && empty($problema)) {
        $erro = 'Descreva o problema para enviar à manutenção.';
    } else {
        // Registrar devolução nas observações
        $regDevolucao  = "[DEVOLUÇÃO] " . date('d/m/Y H:i:s');
        $regDevolucao .= "\nColaborador: {$colaboradorNome}";
        $regDevolucao .= "\nStatus anterior: " . getStatusTexto($statusOrigem);
        $regDevolucao .= "\nDestino: " . getStatusTexto($destino);
        if ($observacoes) $regDevolucao .= "\nObservações: {$observacoes}";

        $obsAtual = $equipamento['observacoes'] ?? '';
        $equipamento['observacoes'] = trim(($obsAtual ? $obsAtual . "\n\n" : '') . $regDevolucao);

        // Limpar vínculo com colaborador
        $equipamento['colaborador_id']      = null;
        $equipamento['data_atribuicao']     = null;
        $equipamento['tipo_atribuicao']     = null;
        unset($equipamento['data_devolucao_prevista']);
        unset($equipamento['status_anterior']);

        // Remover da lista de origem
        $listaOrigem = carregarEquipamentosPorStatus($statusOrigem);
        array_splice($listaOrigem, $indexOrigem, 1);

        if (!salvarArquivoJSON(getCaminhoEquipamentoPorStatus($statusOrigem), $listaOrigem)) {
            $erro = 'Erro ao atualizar o status de origem. Tente novamente.';
        } else {
            if ($destino === 'manutencao') {
                // Ao devolver direto para manutenção, status_anterior = estoque
                // (após manutenção o equipamento volta ao estoque, não ao colaborador)
                if (!isset($equipamento['historico_manutencao'])) {
                    $equipamento['historico_manutencao'] = [];
                }
                $equipamento['historico_manutencao'][] = [
                    'data_envio' => date('Y-m-d H:i:s'),
                    'problema'   => $problema,
                    'origem'     => 'devolucao',
                ];
                $equipamento['status']          = 'manutencao';
                $equipamento['status_anterior'] = 'estoque';
                $equipamento['data_manutencao'] = date('Y-m-d H:i:s');
                $equipamento['data_atualizacao'] = date('Y-m-d H:i:s');
            } else {
                $equipamento['status']           = $destino;
                $equipamento['data_atualizacao'] = date('Y-m-d H:i:s');
            }

            $listaDestino = carregarEquipamentosPorStatus($destino);
            $listaDestino[] = $equipamento;

            if (salvarArquivoJSON(getCaminhoEquipamentoPorStatus($destino), $listaDestino)) {
                $_SESSION['mensagem']      = "Equipamento {$equipamento['patrimonio']} devolvido com sucesso! Destino: " . getStatusTexto($destino) . ".";
                $_SESSION['mensagem_tipo'] = 'success';
                header('Location: index.php');
                exit;
            } else {
                $erro = 'Erro ao salvar o destino. Tente novamente.';
                // Reverter remoção da origem
                $listaOrigem[] = $equipamento;
                salvarArquivoJSON(getCaminhoEquipamentoPorStatus($statusOrigem), $listaOrigem);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devolver Equipamento — Sistema de Gestão</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/favicon/favicon.png">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:       #2563EB;
            --primary-dark:  #1D4ED8;
            --danger:        #EF4444;
            --danger-dark:   #DC2626;
            --danger-light:  #FEF2F2;
            --success:       #10B981;
            --success-light: #ECFDF5;
            --warning:       #F59E0B;
            --warning-dark:  #D97706;
            --warning-light: #FFFBEB;
            --info:          #0EA5E9;
            --info-light:    #F0F9FF;
            --gray-50:  #F9FAFB;
            --gray-100: #F3F4F6;
            --gray-200: #E5E7EB;
            --gray-300: #D1D5DB;
            --gray-400: #9CA3AF;
            --gray-500: #6B7280;
            --gray-700: #374151;
            --gray-800: #1F2937;
            --gray-900: #111827;
            --white:    #FFFFFF;
            --radius:   8px;
            --radius-lg: 14px;
            --shadow:    0 1px 3px rgba(0,0,0,.08);
            --shadow-lg: 0 10px 30px rgba(0,0,0,.1);
        }

        body { font-family: 'Inter', sans-serif; background: var(--gray-50); color: var(--gray-900); min-height: 100vh; }

        /* HEADER */
        .header { background: var(--white); border-bottom: 1px solid var(--gray-200); padding: 0 1.5rem; box-shadow: var(--shadow); }
        .header-content { display: flex; align-items: center; justify-content: space-between; height: 64px; max-width: 880px; margin: 0 auto; }
        .logo a { display: flex; align-items: center; gap: .75rem; text-decoration: none; color: var(--primary); }
        .logo h1 { font-size: 1.125rem; font-weight: 700; }
        .user-menu { display: flex; align-items: center; gap: 1rem; }
        .user-info { display: flex; align-items: center; gap: .5rem; color: var(--gray-700); font-size: .875rem; }
        .logout-btn { display: flex; align-items: center; gap: .5rem; padding: .5rem 1rem; border-radius: var(--radius); background: var(--gray-100); color: var(--gray-700); text-decoration: none; font-size: .875rem; transition: background .2s; }
        .logout-btn:hover { background: var(--gray-200); }

        /* MAIN */
        .main { max-width: 880px; margin: 2rem auto; padding: 0 1rem 3rem; }

        /* PAGE TITLE */
        .page-title { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem; gap: 1rem; flex-wrap: wrap; }
        .page-title h2 { font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: .625rem; }
        .page-title h2 i { color: var(--danger); }
        .page-title p { color: var(--gray-500); font-size: .875rem; margin-top: .25rem; }

        /* CARD */
        .card { background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); overflow: hidden; }

        /* BANNER */
        .equip-banner { padding: 1.5rem 2rem; display: flex; align-items: center; gap: 1.25rem; }
        .equip-banner.status-alocado { background: linear-gradient(135deg, #7C3AED 0%, #5B21B6 100%); }
        .equip-banner.status-emprestado { background: linear-gradient(135deg, var(--info) 0%, #0284C7 100%); }
        .equip-icon { width: 56px; height: 56px; border-radius: 12px; background: rgba(255,255,255,.18); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #fff; flex-shrink: 0; }
        .equip-details { color: #fff; flex: 1; }
        .equip-details h3 { font-size: 1.1rem; font-weight: 700; margin-bottom: .25rem; }
        .equip-details .sub { font-size: .875rem; opacity: .85; }
        .equip-meta { display: flex; flex-wrap: wrap; gap: .5rem .75rem; margin-top: .625rem; }
        .equip-tag { font-size: .75rem; background: rgba(255,255,255,.2); padding: 2px 10px; border-radius: 99px; font-weight: 500; color: #fff; }
        .equip-tag.atraso { background: rgba(239,68,68,.85); }

        /* BODY */
        .card-body { padding: 2rem; }

        /* COLABORADOR INFO */
        .colab-box { display: flex; align-items: center; gap: 1rem; background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: var(--radius); padding: 1rem 1.25rem; margin-bottom: 1.75rem; }
        .colab-avatar { width: 44px; height: 44px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; font-size: .9rem; font-weight: 700; color: #fff; flex-shrink: 0; }
        .colab-info-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--gray-400); }
        .colab-info-name { font-size: .95rem; font-weight: 700; color: var(--gray-900); margin-top: 1px; }
        .colab-info-sub { font-size: .8rem; color: var(--gray-500); margin-top: 2px; }
        .colab-extra { margin-left: auto; text-align: right; }
        .colab-extra .label { font-size: .7rem; font-weight: 600; text-transform: uppercase; color: var(--gray-400); }
        .colab-extra .val { font-size: .8rem; font-weight: 600; color: var(--gray-700); margin-top: 2px; }

        /* DESTINO CARDS */
        .section-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--gray-400); margin-bottom: .75rem; }
        .destino-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .75rem; margin-bottom: 1.75rem; }
        .destino-card { border: 2px solid var(--gray-200); border-radius: var(--radius); padding: 1rem; cursor: pointer; transition: all .18s; text-align: center; user-select: none; }
        .destino-card:hover { border-color: var(--gray-300); background: var(--gray-50); }
        .destino-card.active-estoque  { border-color: var(--success); background: var(--success-light); }
        .destino-card.active-manutencao { border-color: var(--warning); background: var(--warning-light); }
        .destino-card.active-fora_uso { border-color: var(--danger);  background: var(--danger-light); }
        .destino-card i { font-size: 1.5rem; margin-bottom: .5rem; display: block; color: var(--gray-400); }
        .destino-card.active-estoque i   { color: var(--success); }
        .destino-card.active-manutencao i { color: var(--warning); }
        .destino-card.active-fora_uso i  { color: var(--danger); }
        .destino-card .dc-title { font-size: .8rem; font-weight: 700; color: var(--gray-700); }
        .destino-card .dc-sub   { font-size: .7rem; color: var(--gray-400); margin-top: 2px; }
        .destino-card.active-estoque .dc-title   { color: var(--success); }
        .destino-card.active-manutencao .dc-title { color: var(--warning-dark); }
        .destino-card.active-fora_uso .dc-title  { color: var(--danger); }

        /* PROBLEMA FIELD */
        #wrap-problema { display: none; }
        #wrap-problema.show { display: block; }

        /* FORM */
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: .875rem; font-weight: 600; color: var(--gray-700); margin-bottom: .5rem; }
        .form-group label .req { color: var(--danger); margin-left: 2px; }
        .form-control { width: 100%; padding: .625rem .875rem; border: 1px solid var(--gray-200); border-radius: var(--radius); font-family: inherit; font-size: .875rem; transition: border-color .2s; background: var(--white); }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
        textarea.form-control { resize: vertical; min-height: 90px; }

        /* ALERT */
        .alert-error { background: var(--danger-light); border: 1px solid #FECACA; color: #DC2626; padding: .875rem 1rem; border-radius: var(--radius); margin-bottom: 1.25rem; display: flex; align-items: center; gap: .625rem; font-size: .875rem; }

        /* AVISO */
        .aviso-box { background: var(--warning-light); border-left: 4px solid var(--warning); border-radius: var(--radius); padding: 1rem 1.25rem; margin-bottom: 1.5rem; }
        .aviso-box .av-title { display: flex; align-items: center; gap: .5rem; font-size: .875rem; font-weight: 700; color: var(--warning-dark); margin-bottom: .5rem; }
        .aviso-box ul { padding-left: 1.25rem; }
        .aviso-box li { font-size: .8rem; color: #92400E; margin-bottom: .25rem; }

        /* CHECKBOX CONFIRM */
        .confirm-row { display: flex; align-items: flex-start; gap: .75rem; padding: 1rem; background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: var(--radius); margin-bottom: 1.25rem; }
        .confirm-row input[type=checkbox] { width: 16px; height: 16px; margin-top: 2px; accent-color: var(--danger); flex-shrink: 0; cursor: pointer; }
        .confirm-row label { font-size: .8rem; color: var(--gray-700); cursor: pointer; line-height: 1.5; }

        /* ACTIONS */
        .form-actions { display: flex; gap: .75rem; justify-content: flex-end; padding-top: 1.25rem; border-top: 1px solid var(--gray-100); }
        .btn { display: inline-flex; align-items: center; gap: .5rem; padding: .625rem 1.25rem; border-radius: var(--radius); font-size: .875rem; font-weight: 600; cursor: pointer; text-decoration: none; border: none; transition: background .2s, opacity .2s; }
        .btn:active { opacity: .85; }
        .btn-secondary { background: var(--gray-100); color: var(--gray-700); }
        .btn-secondary:hover { background: var(--gray-200); }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-danger:hover:not(:disabled) { background: var(--danger-dark); }
        .btn-danger:disabled { opacity: .45; cursor: not-allowed; }

        @media (max-width: 640px) {
            .destino-grid { grid-template-columns: 1fr; }
            .colab-box { flex-wrap: wrap; }
            .colab-extra { margin-left: 0; text-align: left; }
            .form-actions { flex-direction: column-reverse; }
            .btn { justify-content: center; }
            .card-body { padding: 1.25rem; }
            .equip-banner { flex-direction: column; align-items: flex-start; }
        }
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
                <i class="fas fa-laptop-house"></i>
                <h1>Sistema de Gestão</h1>
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

<main class="main">

    <div class="page-title">
        <div>
            <h2><i class="fas fa-undo-alt"></i> Devolver Equipamento</h2>
            <p>Registrar a devolução e definir o novo destino</p>
        </div>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>

    <div class="card">

        <!-- BANNER DO EQUIPAMENTO -->
        <div class="equip-banner status-<?php echo $statusOrigem; ?>">
            <div class="equip-icon">
                <i class="fas fa-<?php echo getIconByType($equipamento['tipo']); ?>"></i>
            </div>
            <div class="equip-details">
                <h3><?php echo htmlspecialchars($equipamento['patrimonio']); ?></h3>
                <div class="sub"><?php echo htmlspecialchars($equipamento['marca'] . ' ' . $equipamento['modelo']); ?></div>
                <div class="equip-meta">
                    <span class="equip-tag"><?php echo getTipoTexto($equipamento['tipo']); ?></span>
                    <span class="equip-tag"><i class="fas fa-<?php echo $statusOrigem === 'alocado' ? 'user-check' : 'handshake'; ?>"></i> <?php echo getStatusTexto($statusOrigem); ?></span>
                    <?php if ($emAtraso): ?>
                        <span class="equip-tag atraso"><i class="fas fa-exclamation-triangle"></i> Devolução em atraso</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card-body">

            <?php if ($erro): ?>
                <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>

            <!-- COLABORADOR -->
            <?php
                $iniciais = $colaboradorInfo
                    ? implode('', array_map(fn($p) => mb_strtoupper(mb_substr($p,0,1)), array_slice(explode(' ', $colaboradorInfo['nome']), 0, 2)))
                    : '?';
            ?>
            <div class="colab-box">
                <div class="colab-avatar"><?php echo $iniciais; ?></div>
                <div>
                    <div class="colab-info-label">Colaborador responsável</div>
                    <div class="colab-info-name"><?php echo htmlspecialchars($colaboradorNome); ?></div>
                    <?php if ($colaboradorInfo): ?>
                        <div class="colab-info-sub">
                            <?php echo htmlspecialchars($colaboradorInfo['cargo'] ?? ''); ?>
                            <?php if (!empty($colaboradorInfo['centro_custo'])): ?> · CC <?php echo htmlspecialchars($colaboradorInfo['centro_custo']); ?><?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="colab-extra">
                    <?php if (!empty($equipamento['data_atribuicao'])): ?>
                        <div class="label">Alocado em</div>
                        <div class="val"><?php echo formatarData($equipamento['data_atribuicao'], 'd/m/Y'); ?></div>
                    <?php endif; ?>
                    <?php if ($statusOrigem === 'emprestado' && !empty($equipamento['data_devolucao_prevista'])): ?>
                        <div class="label" style="margin-top:.5rem">Devolução prevista</div>
                        <div class="val" style="color:<?php echo $emAtraso ? 'var(--danger)' : 'var(--gray-700)'; ?>">
                            <?php if ($emAtraso): ?><i class="fas fa-clock"></i> <?php endif; ?>
                            <?php echo formatarData($equipamento['data_devolucao_prevista'], 'd/m/Y'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <form method="POST" id="formDevolver">
                <input type="hidden" name="destino" id="inputDestino" value="estoque">

                <!-- DESTINO -->
                <div class="section-label">Destino após devolução</div>
                <div class="destino-grid">
                    <div class="destino-card active-estoque" id="card-estoque" onclick="setDestino('estoque')">
                        <i class="fas fa-warehouse"></i>
                        <div class="dc-title">Estoque</div>
                        <div class="dc-sub">Disponível para uso</div>
                    </div>
                    <div class="destino-card" id="card-manutencao" onclick="setDestino('manutencao')">
                        <i class="fas fa-tools"></i>
                        <div class="dc-title">Manutenção</div>
                        <div class="dc-sub">Precisa de conserto</div>
                    </div>
                    <div class="destino-card" id="card-fora_uso" onclick="setDestino('fora_uso')">
                        <i class="fas fa-times-circle"></i>
                        <div class="dc-title">Fora de Uso</div>
                        <div class="dc-sub">Quebrado / obsoleto</div>
                    </div>
                </div>

                <!-- PROBLEMA (só manutenção) -->
                <div id="wrap-problema">
                    <div class="form-group">
                        <label for="problema"><i class="fas fa-wrench" style="color:var(--warning);margin-right:.375rem"></i>Descrição do problema <span class="req">*</span></label>
                        <textarea name="problema" id="problema" class="form-control" placeholder="Descreva o problema identificado no equipamento..."></textarea>
                    </div>
                </div>

                <!-- OBSERVAÇÕES -->
                <div class="form-group">
                    <label for="observacoes"><i class="fas fa-sticky-note" style="color:var(--gray-400);margin-right:.375rem"></i>Observações da devolução <span style="font-weight:400;color:var(--gray-400)">(opcional)</span></label>
                    <textarea name="observacoes" id="observacoes" class="form-control" placeholder="Condição do equipamento, motivo da devolução, observações gerais..."></textarea>
                </div>

                <!-- AVISO -->
                <div class="aviso-box">
                    <div class="av-title"><i class="fas fa-exclamation-triangle"></i> Atenção</div>
                    <ul>
                        <li>O vínculo com <strong><?php echo htmlspecialchars($colaboradorNome); ?></strong> será removido permanentemente.</li>
                        <li>O equipamento mudará de <strong><?php echo getStatusTexto($statusOrigem); ?></strong> para o destino selecionado.</li>
                        <li>Esta ação não pode ser desfeita.</li>
                    </ul>
                </div>

                <!-- CONFIRMAÇÃO -->
                <div class="confirm-row">
                    <input type="checkbox" id="chkConfirm">
                    <label for="chkConfirm">Confirmo que estou ciente de que o vínculo com o colaborador será removido e o status do equipamento será alterado.</label>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                    <button type="submit" class="btn btn-danger" id="btnConfirmar" disabled>
                        <i class="fas fa-undo-alt"></i> Confirmar Devolução
                    </button>
                </div>
            </form>

        </div>
    </div>
</main>

<script>
// ── DESTINO ───────────────────────────────────────────────────────────────────
function setDestino(d) {
    document.getElementById('inputDestino').value = d;

    ['estoque', 'manutencao', 'fora_uso'].forEach(function(s) {
        var card = document.getElementById('card-' + s);
        card.className = 'destino-card' + (s === d ? ' active-' + s : '');
    });

    var wrap = document.getElementById('wrap-problema');
    if (d === 'manutencao') {
        wrap.classList.add('show');
    } else {
        wrap.classList.remove('show');
        document.getElementById('problema').value = '';
    }
}

// ── CHECKBOX ──────────────────────────────────────────────────────────────────
document.getElementById('chkConfirm').addEventListener('change', function() {
    document.getElementById('btnConfirmar').disabled = !this.checked;
});

// ── VALIDAÇÃO ─────────────────────────────────────────────────────────────────
document.getElementById('formDevolver').addEventListener('submit', function(e) {
    var destino = document.getElementById('inputDestino').value;
    var problema = document.getElementById('problema').value.trim();

    if (destino === 'manutencao' && !problema) {
        e.preventDefault();
        document.getElementById('problema').focus();
        document.getElementById('problema').style.borderColor = 'var(--danger)';
        setTimeout(function() {
            document.getElementById('problema').style.borderColor = '';
        }, 2000);
    }
});
</script>

</body>
</html>
