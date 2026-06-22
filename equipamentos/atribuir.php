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

// Buscar equipamento em estoque
$listaEstoque = carregarEquipamentosPorStatus('estoque');
$equipamento   = null;
$indexOrigem   = null;

foreach ($listaEstoque as $i => $e) {
    if ($e['id'] == $id) {
        $equipamento = $e;
        $indexOrigem = $i;
        break;
    }
}

if (!$equipamento) {
    $_SESSION['mensagem']      = 'Equipamento não encontrado ou não está disponível em estoque.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

// Colaboradores ativos
$colaboradores = lerArquivoJSON('../data/colaboradores/ativos.json');
if (!is_array($colaboradores)) $colaboradores = [];

$erro = '';

// ── POST ──────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $colaborador_id        = $_POST['colaborador_id'] ?? null;
    $tipo                  = $_POST['tipo']           ?? 'alocado';
    $data_devolucao        = trim($_POST['data_devolucao'] ?? '');
    $observacoes           = trim($_POST['observacoes']    ?? '');
    $atualizar_centro_custo = isset($_POST['atualizar_centro_custo']) && $_POST['atualizar_centro_custo'] === 'sim';

    if (!in_array($tipo, ['alocado', 'emprestado'])) $tipo = 'alocado';

    if (!$colaborador_id) {
        $erro = 'Selecione um colaborador.';
    } else {
        // Localizar colaborador
        $colaboradorSelecionado = null;
        foreach ($colaboradores as $c) {
            if ($c['id'] == $colaborador_id) {
                $colaboradorSelecionado = $c;
                break;
            }
        }

        if (!$colaboradorSelecionado) {
            $erro = 'Colaborador não encontrado.';
        } else {
            $ccOriginal    = $equipamento['centro_custo'] ?? '';
            $ccColaborador = $colaboradorSelecionado['centro_custo'] ?? '';

            // Preencher dados de atribuição
            $equipamento['colaborador_id']   = (int) $colaborador_id;
            $equipamento['tipo_atribuicao']  = $tipo === 'emprestado' ? 'emprestimo' : 'alocacao';
            $equipamento['data_atribuicao']  = date('Y-m-d H:i:s');

            if ($tipo === 'emprestado' && $data_devolucao) {
                $equipamento['data_devolucao_prevista'] = $data_devolucao;
            } else {
                unset($equipamento['data_devolucao_prevista']);
            }

            if ($observacoes) {
                $prefixo     = $tipo === 'emprestado' ? '[EMPRÉSTIMO]' : '[ALOCAÇÃO]';
                $obsAtual    = $equipamento['observacoes'] ?? '';
                $sufixo      = $tipo === 'emprestado' && $data_devolucao
                    ? " (Devolução prevista: " . date('d/m/Y', strtotime($data_devolucao)) . ")"
                    : '';
                $equipamento['observacoes'] = trim($obsAtual . "\n\n{$prefixo} " . $observacoes . $sufixo);
            }

            // Atualizar centro de custo
            if ($atualizar_centro_custo && $ccColaborador && $ccColaborador !== $ccOriginal) {
                if (!isset($equipamento['historico_centro_custo']) || !is_array($equipamento['historico_centro_custo'])) {
                    $equipamento['historico_centro_custo'] = [];
                }
                $equipamento['historico_centro_custo'][] = [
                    'data'                 => date('Y-m-d H:i:s'),
                    'usuario'             => $_SESSION['usuario_nome'] ?? 'Usuário',
                    'centro_custo_anterior' => $ccOriginal,
                    'centro_custo_novo'    => $ccColaborador,
                    'motivo'              => "Atribuição automática — equipamento alocado para {$colaboradorSelecionado['nome']}",
                ];
                $equipamento['centro_custo'] = $ccColaborador;
            }

            // Mover para alocado/emprestado
            if (moverEquipamentoParaStatus($equipamento, $tipo)) {
                $acao = $tipo === 'emprestado' ? 'emprestado' : 'alocado';
                $_SESSION['mensagem']      = "Equipamento {$equipamento['patrimonio']} {$acao} com sucesso para {$colaboradorSelecionado['nome']}!";
                $_SESSION['mensagem_tipo'] = 'success';
                header('Location: index.php');
                exit;
            } else {
                $erro = 'Erro ao salvar atribuição. Tente novamente.';
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
    <title>Atribuir Equipamento — Sistema de Gestão</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/favicon/favicon.png">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:       #2563EB;
            --primary-dark:  #1D4ED8;
            --primary-light: #EFF6FF;
            --success:       #10B981;
            --success-dark:  #059669;
            --success-light: #ECFDF5;
            --info:          #0EA5E9;
            --info-dark:     #0284C7;
            --info-light:    #F0F9FF;
            --warning:       #F59E0B;
            --warning-light: #FFFBEB;
            --danger:        #EF4444;
            --danger-light:  #FEF2F2;
            --gray-50:  #F9FAFB;
            --gray-100: #F3F4F6;
            --gray-200: #E5E7EB;
            --gray-300: #D1D5DB;
            --gray-400: #9CA3AF;
            --gray-500: #6B7280;
            --gray-700: #374151;
            --gray-900: #111827;
            --white:    #FFFFFF;
            --radius:   8px;
            --radius-lg: 14px;
            --shadow:   0 1px 3px rgba(0,0,0,.08);
            --shadow-lg: 0 10px 30px rgba(0,0,0,.1);
        }

        body { font-family: 'Inter', sans-serif; background: var(--gray-50); color: var(--gray-900); min-height: 100vh; }

        /* HEADER */
        .header { background: var(--white); border-bottom: 1px solid var(--gray-200); padding: 0 1.5rem; box-shadow: var(--shadow); }
        .header-content { display: flex; align-items: center; justify-content: space-between; height: 64px; max-width: 960px; margin: 0 auto; }
        .logo a { display: flex; align-items: center; gap: .75rem; text-decoration: none; color: var(--primary); }
        .logo h1 { font-size: 1.125rem; font-weight: 700; }
        .user-menu { display: flex; align-items: center; gap: 1rem; }
        .user-info { display: flex; align-items: center; gap: .5rem; color: var(--gray-700); font-size: .875rem; }
        .logout-btn { display: flex; align-items: center; gap: .5rem; padding: .5rem 1rem; border-radius: var(--radius); background: var(--gray-100); color: var(--gray-700); text-decoration: none; font-size: .875rem; transition: background .2s; }
        .logout-btn:hover { background: var(--gray-200); }

        /* MAIN */
        .main { max-width: 960px; margin: 2rem auto; padding: 0 1rem 3rem; }

        /* PAGE TITLE */
        .page-title { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem; gap: 1rem; flex-wrap: wrap; }
        .page-title h2 { font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: .625rem; }
        .page-title h2 i { color: var(--primary); }
        .page-title p { color: var(--gray-500); font-size: .875rem; margin-top: .25rem; }

        /* CARD */
        .card { background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); overflow: hidden; }

        /* EQUIP INFO */
        .equip-banner { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); padding: 1.5rem 2rem; color: var(--white); display: flex; align-items: center; gap: 1.25rem; }
        .equip-icon { width: 56px; height: 56px; border-radius: 12px; background: rgba(255,255,255,.15); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
        .equip-details h3 { font-size: 1.1rem; font-weight: 700; margin-bottom: .25rem; }
        .equip-meta { display: flex; flex-wrap: wrap; gap: .5rem .75rem; margin-top: .5rem; }
        .equip-tag { font-size: .75rem; background: rgba(255,255,255,.2); padding: 2px 10px; border-radius: 99px; font-weight: 500; }

        /* BODY */
        .card-body { padding: 2rem; }

        /* TYPE TOGGLE */
        .type-toggle { display: flex; gap: .75rem; margin-bottom: 2rem; }
        .toggle-btn { flex: 1; display: flex; align-items: center; justify-content: center; gap: .625rem; padding: .875rem 1rem; border: 2px solid var(--gray-200); border-radius: var(--radius); background: var(--white); font-size: .875rem; font-weight: 600; cursor: pointer; transition: all .2s; color: var(--gray-500); }
        .toggle-btn:hover { border-color: var(--gray-300); color: var(--gray-700); }
        .toggle-btn.active-alocar { border-color: var(--success); background: var(--success-light); color: var(--success-dark); }
        .toggle-btn.active-emprestar { border-color: var(--info); background: var(--info-light); color: var(--info-dark); }

        /* SECTION LABEL */
        .section-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--gray-400); margin-bottom: .625rem; }

        /* SEARCH + SELECT */
        .search-wrap { position: relative; margin-bottom: .5rem; }
        .search-wrap i { position: absolute; left: .875rem; top: 50%; transform: translateY(-50%); color: var(--gray-400); font-size: .875rem; pointer-events: none; }
        .search-input { width: 100%; padding: .625rem .875rem .625rem 2.5rem; border: 1px solid var(--gray-200); border-radius: var(--radius); font-family: inherit; font-size: .875rem; background: var(--gray-50); transition: border-color .2s; }
        .search-input:focus { outline: none; border-color: var(--primary); background: var(--white); box-shadow: 0 0 0 3px rgba(37,99,235,.12); }

        .colab-list { border: 1px solid var(--gray-200); border-radius: var(--radius); max-height: 260px; overflow-y: auto; }
        .colab-item { display: flex; align-items: center; gap: .875rem; padding: .75rem 1rem; cursor: pointer; transition: background .15s; border-bottom: 1px solid var(--gray-100); }
        .colab-item:last-child { border-bottom: none; }
        .colab-item:hover { background: var(--gray-50); }
        .colab-item.selected { background: var(--primary-light); }
        .colab-item.hidden { display: none; }
        .colab-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--primary-light); display: flex; align-items: center; justify-content: center; font-size: .8rem; font-weight: 700; color: var(--primary); flex-shrink: 0; }
        .colab-item.selected .colab-avatar { background: var(--primary); color: var(--white); }
        .colab-name { font-size: .875rem; font-weight: 600; color: var(--gray-900); }
        .colab-sub { font-size: .75rem; color: var(--gray-500); margin-top: 1px; }
        .colab-check { margin-left: auto; color: var(--primary); display: none; }
        .colab-item.selected .colab-check { display: block; }
        .colab-empty { padding: 1.5rem; text-align: center; color: var(--gray-400); font-size: .875rem; }

        /* HIDDEN INPUT */
        input[name="colaborador_id"] { display: none; }

        /* COLAB INFO CARD */
        .colab-card { display: none; background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: var(--radius); padding: 1rem 1.25rem; margin-top: .875rem; }
        .colab-card.show { display: block; }
        .colab-card-row { display: flex; gap: 1rem; flex-wrap: wrap; }
        .colab-card-item { flex: 1; min-width: 140px; }
        .colab-card-label { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--gray-400); margin-bottom: 2px; }
        .colab-card-value { font-size: .875rem; font-weight: 600; color: var(--gray-900); }

        /* CC UPDATE */
        .cc-update-row { display: flex; align-items: flex-start; gap: .75rem; background: var(--warning-light); border: 1px solid #FDE68A; border-radius: var(--radius); padding: .875rem 1rem; margin-top: .875rem; }
        .cc-update-row input[type=checkbox] { margin-top: 2px; width: 16px; height: 16px; accent-color: var(--warning); flex-shrink: 0; cursor: pointer; }
        .cc-label { font-size: .8rem; color: var(--gray-700); cursor: pointer; }
        .cc-label strong { display: block; margin-bottom: 2px; }
        .cc-arrow { color: var(--warning); margin: 0 .25rem; }

        /* FORM FIELDS */
        .form-section { margin-top: 1.75rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: .875rem; font-weight: 600; color: var(--gray-700); margin-bottom: .5rem; }
        .form-group label span.req { color: var(--danger); margin-left: 2px; }
        .form-control { width: 100%; padding: .625rem .875rem; border: 1px solid var(--gray-200); border-radius: var(--radius); font-family: inherit; font-size: .875rem; transition: border-color .2s; background: var(--white); }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
        textarea.form-control { resize: vertical; min-height: 90px; }

        /* DATE FIELD hidden by default */
        #wrap-devolucao { display: none; }
        #wrap-devolucao.show { display: block; }

        /* ALERT */
        .alert-error { background: var(--danger-light); border: 1px solid #FECACA; color: #DC2626; padding: .875rem 1rem; border-radius: var(--radius); margin-bottom: 1.25rem; display: flex; align-items: center; gap: .625rem; font-size: .875rem; }

        /* CONFIRM CARD */
        .confirm-card { background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: var(--radius); padding: 1rem 1.25rem; margin: 1.5rem 0 1.25rem; }
        .confirm-card p { font-size: .8rem; color: var(--gray-500); line-height: 1.6; }
        .confirm-card p strong { color: var(--gray-700); }

        /* ACTIONS */
        .form-actions { display: flex; gap: .75rem; justify-content: flex-end; padding-top: 1.25rem; border-top: 1px solid var(--gray-100); }
        .btn { display: inline-flex; align-items: center; gap: .5rem; padding: .625rem 1.25rem; border-radius: var(--radius); font-size: .875rem; font-weight: 600; cursor: pointer; text-decoration: none; border: none; transition: background .2s, transform .1s; }
        .btn:active { transform: scale(.98); }
        .btn-secondary { background: var(--gray-100); color: var(--gray-700); }
        .btn-secondary:hover { background: var(--gray-200); }
        .btn-success { background: var(--success); color: var(--white); }
        .btn-success:hover { background: var(--success-dark); }
        .btn-info { background: var(--info); color: var(--white); }
        .btn-info:hover { background: var(--info-dark); }
        .btn-submit { transition: background .2s; }

        /* SCROLLBAR */
        .colab-list::-webkit-scrollbar { width: 4px; }
        .colab-list::-webkit-scrollbar-thumb { background: var(--gray-300); border-radius: 4px; }

        @media (max-width: 640px) {
            .equip-banner { flex-direction: column; align-items: flex-start; }
            .type-toggle { flex-direction: column; }
            .form-actions { flex-direction: column-reverse; }
            .btn { justify-content: center; }
            .card-body { padding: 1.25rem; }
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

<main class="main">

    <div class="page-title">
        <div>
            <h2><i class="fas fa-user-check"></i> Atribuir Equipamento</h2>
            <p>Alocar ou emprestar para um colaborador</p>
        </div>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>

    <div class="card">

        <!-- BANNER DO EQUIPAMENTO -->
        <div class="equip-banner">
            <div class="equip-icon">
                <i class="fas fa-<?php echo getIconByType($equipamento['tipo']); ?>"></i>
            </div>
            <div class="equip-details">
                <h3><?php echo htmlspecialchars($equipamento['patrimonio']); ?></h3>
                <div><?php echo htmlspecialchars($equipamento['marca'] . ' ' . $equipamento['modelo']); ?></div>
                <div class="equip-meta">
                    <span class="equip-tag"><i class="fas fa-tag"></i> <?php echo getTipoTexto($equipamento['tipo']); ?></span>
                    <span class="equip-tag"><i class="fas fa-dollar-sign"></i> CC: <?php echo htmlspecialchars($equipamento['centro_custo'] ?? '—'); ?></span>
                    <span class="equip-tag"><i class="fas fa-warehouse"></i> Em Estoque</span>
                </div>
            </div>
        </div>

        <div class="card-body">

            <?php if ($erro): ?>
                <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>

            <form method="POST" id="formAtribuir">
                <input type="hidden" name="tipo" id="inputTipo" value="alocado">
                <input type="hidden" name="colaborador_id" id="inputColaboradorId" value="">

                <!-- TOGGLE TIPO -->
                <div class="type-toggle">
                    <button type="button" class="toggle-btn active-alocar" id="btnAlocar" onclick="setTipo('alocado')">
                        <i class="fas fa-user-check"></i> Alocar
                    </button>
                    <button type="button" class="toggle-btn" id="btnEmprestar" onclick="setTipo('emprestado')">
                        <i class="fas fa-handshake"></i> Emprestar
                    </button>
                </div>

                <!-- COLABORADOR -->
                <div class="section-label">Colaborador <span style="color:var(--danger)">*</span></div>

                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" class="search-input" id="buscaColab" placeholder="Buscar por nome, cargo ou CC..." oninput="filtrarColaboradores(this.value)">
                </div>

                <div class="colab-list" id="colabList">
                    <?php if (empty($colaboradores)): ?>
                        <div class="colab-empty"><i class="fas fa-users-slash"></i><br>Nenhum colaborador ativo cadastrado.</div>
                    <?php else: ?>
                        <?php foreach ($colaboradores as $c): ?>
                            <?php
                                $iniciais = implode('', array_map(fn($p) => mb_strtoupper(mb_substr($p,0,1)), array_slice(explode(' ', $c['nome']), 0, 2)));
                            ?>
                            <div class="colab-item"
                                 data-id="<?php echo $c['id']; ?>"
                                 data-nome="<?php echo htmlspecialchars($c['nome']); ?>"
                                 data-cargo="<?php echo htmlspecialchars($c['cargo'] ?? ''); ?>"
                                 data-cc="<?php echo htmlspecialchars($c['centro_custo'] ?? ''); ?>"
                                 data-unidade="<?php echo htmlspecialchars($c['unidade'] ?? ''); ?>"
                                 onclick="selecionarColaborador(this)">
                                <div class="colab-avatar"><?php echo $iniciais; ?></div>
                                <div>
                                    <div class="colab-name"><?php echo htmlspecialchars($c['nome']); ?></div>
                                    <div class="colab-sub">
                                        <?php echo htmlspecialchars($c['cargo'] ?? ''); ?>
                                        <?php if (!empty($c['centro_custo'])): ?> · CC <?php echo htmlspecialchars($c['centro_custo']); ?><?php endif; ?>
                                        <?php if (!empty($c['unidade'])): ?> · <?php echo htmlspecialchars($c['unidade']); ?><?php endif; ?>
                                    </div>
                                </div>
                                <i class="fas fa-check-circle colab-check"></i>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- INFO DO COLABORADOR SELECIONADO -->
                <div class="colab-card" id="colabCard">
                    <div class="colab-card-row">
                        <div class="colab-card-item">
                            <div class="colab-card-label">Colaborador</div>
                            <div class="colab-card-value" id="ccNome">—</div>
                        </div>
                        <div class="colab-card-item">
                            <div class="colab-card-label">Cargo</div>
                            <div class="colab-card-value" id="ccCargo">—</div>
                        </div>
                        <div class="colab-card-item">
                            <div class="colab-card-label">Centro de Custo</div>
                            <div class="colab-card-value" id="ccCC">—</div>
                        </div>
                    </div>

                    <!-- CC UPDATE -->
                    <div class="cc-update-row" id="ccUpdateRow" style="display:none">
                        <input type="checkbox" name="atualizar_centro_custo" value="sim" id="chkCC">
                        <label class="cc-label" for="chkCC">
                            <strong>Atualizar centro de custo do equipamento</strong>
                            CC atual: <strong id="ccAtual"><?php echo htmlspecialchars($equipamento['centro_custo'] ?? '—'); ?></strong>
                            <i class="fas fa-arrow-right cc-arrow"></i>
                            CC do colaborador: <strong id="ccNovo">—</strong>
                        </label>
                    </div>
                </div>

                <!-- DATA DEVOLUÇÃO (só para emprestado) -->
                <div id="wrap-devolucao" class="form-section">
                    <div class="form-group">
                        <label for="data_devolucao"><i class="fas fa-calendar-alt" style="color:var(--info);margin-right:.375rem"></i>Data prevista de devolução</label>
                        <input type="date" name="data_devolucao" id="data_devolucao" class="form-control"
                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>
                </div>

                <!-- OBSERVAÇÕES -->
                <div class="form-section">
                    <div class="form-group">
                        <label for="observacoes"><i class="fas fa-sticky-note" style="color:var(--gray-400);margin-right:.375rem"></i>Observações <span style="font-weight:400;color:var(--gray-400)">(opcional)</span></label>
                        <textarea name="observacoes" id="observacoes" class="form-control" placeholder="Motivo da alocação, local de uso, condições especiais..."></textarea>
                    </div>
                </div>

                <!-- CONFIRMAÇÃO -->
                <div class="confirm-card">
                    <p id="confirmText">
                        O equipamento será <strong>alocado</strong> ao colaborador selecionado e sairá do estoque disponível.
                        O colaborador ficará responsável pelo equipamento.
                    </p>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                    <button type="submit" class="btn btn-success btn-submit" id="btnSubmit">
                        <i class="fas fa-user-check"></i> <span id="btnLabel">Confirmar Alocação</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</main>

<script>
const ccEquipamento = <?php echo json_encode($equipamento['centro_custo'] ?? ''); ?>;

// ── TIPO (alocar / emprestar) ─────────────────────────────────────────────────
function setTipo(tipo) {
    document.getElementById('inputTipo').value = tipo;
    const btnA = document.getElementById('btnAlocar');
    const btnE = document.getElementById('btnEmprestar');
    const wrap = document.getElementById('wrap-devolucao');
    const btnSubmit = document.getElementById('btnSubmit');
    const btnLabel = document.getElementById('btnLabel');
    const confirmText = document.getElementById('confirmText');

    if (tipo === 'alocado') {
        btnA.className = 'toggle-btn active-alocar';
        btnE.className = 'toggle-btn';
        wrap.classList.remove('show');
        btnSubmit.className = 'btn btn-success btn-submit';
        btnLabel.textContent = 'Confirmar Alocação';
        btnSubmit.querySelector('i').className = 'fas fa-user-check';
        confirmText.innerHTML = 'O equipamento será <strong>alocado</strong> ao colaborador selecionado e sairá do estoque disponível. O colaborador ficará responsável pelo equipamento.';
    } else {
        btnA.className = 'toggle-btn';
        btnE.className = 'toggle-btn active-emprestar';
        wrap.classList.add('show');
        btnSubmit.className = 'btn btn-info btn-submit';
        btnLabel.textContent = 'Confirmar Empréstimo';
        btnSubmit.querySelector('i').className = 'fas fa-handshake';
        confirmText.innerHTML = 'O equipamento será <strong>emprestado</strong> temporariamente ao colaborador. Defina uma data de devolução para controle.';
    }
}

// ── FILTRO DE COLABORADORES ───────────────────────────────────────────────────
function filtrarColaboradores(query) {
    const q = query.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    document.querySelectorAll('.colab-item').forEach(item => {
        const texto = (item.dataset.nome + ' ' + item.dataset.cargo + ' ' + item.dataset.cc + ' ' + item.dataset.unidade)
            .toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
        item.classList.toggle('hidden', q.length > 0 && !texto.includes(q));
    });
}

// ── SELEÇÃO DE COLABORADOR ────────────────────────────────────────────────────
function selecionarColaborador(el) {
    // Desmarcar anterior
    document.querySelectorAll('.colab-item').forEach(i => i.classList.remove('selected'));
    el.classList.add('selected');

    const id    = el.dataset.id;
    const nome  = el.dataset.nome;
    const cargo = el.dataset.cargo;
    const cc    = el.dataset.cc;

    document.getElementById('inputColaboradorId').value = id;

    // Info card
    document.getElementById('ccNome').textContent  = nome;
    document.getElementById('ccCargo').textContent = cargo || '—';
    document.getElementById('ccCC').textContent    = cc    || '—';
    document.getElementById('colabCard').classList.add('show');

    // CC update
    const ccRow = document.getElementById('ccUpdateRow');
    if (cc && cc !== ccEquipamento) {
        document.getElementById('ccNovo').textContent = cc;
        ccRow.style.display = 'flex';
    } else {
        ccRow.style.display = 'none';
        document.getElementById('chkCC').checked = false;
    }
}

// ── VALIDAÇÃO ─────────────────────────────────────────────────────────────────
document.getElementById('formAtribuir').addEventListener('submit', function(e) {
    if (!document.getElementById('inputColaboradorId').value) {
        e.preventDefault();
        document.getElementById('colabList').style.border = '2px solid var(--danger)';
        document.getElementById('buscaColab').focus();
        setTimeout(() => document.getElementById('colabList').style.border = '', 2000);
    }
});
</script>

</body>
</html>
