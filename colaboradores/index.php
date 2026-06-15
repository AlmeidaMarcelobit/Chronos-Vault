<?php
session_start();
require_once '../includes/funcoes.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

// Verificar nível do usuário
$usuario_nivel = $_SESSION['usuario_nivel'] ?? 'user';
$is_admin = ($usuario_nivel === 'admin');

// ── INATIVAÇÃO (POST deve rodar ANTES de qualquer filtro/sort) ────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inativar'])) {
    $colaboradorId = $_POST['colaborador_id'] ?? null;

    if ($colaboradorId) {
        // Recarregar lista COMPLETA direto do arquivo — nunca usar array já filtrado
        $ativosCompleto = lerArquivoJSON('../data/colaboradores/ativos.json');
        if (!is_array($ativosCompleto)) $ativosCompleto = [];

        $colaboradorEncontrado = null;
        $novosAtivos = [];

        foreach ($ativosCompleto as $colab) {
            if ($colab['id'] == $colaboradorId) {
                $colaboradorEncontrado = $colab;
                // não adiciona em $novosAtivos → remove dos ativos
            } else {
                $novosAtivos[] = $colab;
            }
        }

        if (!$colaboradorEncontrado) {
            $_SESSION['mensagem']      = 'Colaborador não encontrado.';
            $_SESSION['mensagem_tipo'] = 'error';
            header('Location: index.php');
            exit;
        }

        $tipoTrabalho = $colaboradorEncontrado['tipo_trabalho'] ?? 'local';

        $colaboradorEncontrado['data_inativacao']    = date('Y-m-d H:i:s');
        $colaboradorEncontrado['motivo_inativacao']  = $tipoTrabalho === 'home'
            ? 'Aguardando devolução de equipamentos (Home Office)'
            : 'Inativado pelo sistema';
        $colaboradorEncontrado['status_inativacao']  = $tipoTrabalho === 'home' ? 'pendente' : 'inativo';

        // Adicionar aos inativos
        $inativos = lerArquivoJSON('../data/colaboradores/inativos.json');
        if (!is_array($inativos)) $inativos = [];
        $inativos[] = $colaboradorEncontrado;

        // ── Devolver equipamentos (arquitetura atual: arquivos por status) ──────
        $equipamentosAtualizados = 0;

        if ($tipoTrabalho !== 'home') {
            foreach (['alocado', 'emprestado'] as $statusEquip) {
                $listaStatus = carregarEquipamentosPorStatus($statusEquip);
                $ficam       = [];
                $devolver    = [];

                foreach ($listaStatus as $equip) {
                    if ($equip['colaborador_id'] == $colaboradorId) {
                        $obs = $equip['observacoes'] ?? '';
                        $equip['observacoes']    = trim($obs . "\n\n[INATIVAÇÃO] " . date('d/m/Y H:i:s')
                            . "\nColaborador: {$colaboradorEncontrado['nome']} — devolvido ao estoque.");
                        $equip['colaborador_id'] = null;
                        $equip['status']         = 'estoque';
                        $equip['data_atribuicao']= null;
                        $equip['tipo_atribuicao']= null;
                        $equip['data_atualizacao']= date('Y-m-d H:i:s');
                        unset($equip['data_devolucao_prevista']);
                        $devolver[] = $equip;
                        $equipamentosAtualizados++;
                    } else {
                        $ficam[] = $equip;
                    }
                }

                // Salvar lista sem os equipamentos devolvidos
                salvarArquivoJSON(getCaminhoEquipamentoPorStatus($statusEquip), $ficam);

                // Adicionar em lote ao estoque
                if (!empty($devolver)) {
                    $estoque = carregarEquipamentosPorStatus('estoque');
                    $estoque = array_merge($estoque, $devolver);
                    salvarArquivoJSON(getCaminhoEquipamentoPorStatus('estoque'), $estoque);
                }
            }
        }

        // ── Remover vínculo das linhas ────────────────────────────────────────
        $linhasAll = lerArquivoJSON('../data/linhas.json');
        if (!is_array($linhasAll)) $linhasAll = [];
        foreach ($linhasAll as &$linha) {
            if (($linha['colaborador_id'] ?? null) == $colaboradorId) {
                $linha['colaborador_id']   = null;
                $linha['data_atualizacao'] = date('Y-m-d H:i:s');
            }
        }
        unset($linha);

        // ── Salvar tudo ───────────────────────────────────────────────────────
        $saveAtivos   = salvarArquivoJSON('../data/colaboradores/ativos.json',   $novosAtivos);
        $saveInativos = salvarArquivoJSON('../data/colaboradores/inativos.json', $inativos);
        $saveLinhas   = salvarArquivoJSON('../data/linhas.json', $linhasAll);

        if ($saveAtivos && $saveInativos && $saveLinhas) {
            $_SESSION['mensagem'] = $tipoTrabalho === 'home'
                ? "Colaborador movido para inativos com pendência de devolução de equipamentos."
                : "Colaborador inativado com sucesso! {$equipamentosAtualizados} equipamento(s) devolvido(s) ao estoque.";
            $_SESSION['mensagem_tipo'] = 'success';
        } else {
            $_SESSION['mensagem']      = 'Erro ao inativar colaborador. Tente novamente.';
            $_SESSION['mensagem_tipo'] = 'error';
        }

        header('Location: index.php');
        exit;
    }
}

// ── CARREGAR DADOS PARA EXIBIÇÃO ──────────────────────────────────────────────
$colaboradores = lerArquivoJSON('../data/colaboradores/ativos.json');
if (!is_array($colaboradores)) $colaboradores = [];

$linhas = lerArquivoJSON('../data/linhas.json');
if (!is_array($linhas)) $linhas = [];

// Normalizar campos
foreach ($colaboradores as &$colab) {
    if (!isset($colab['matricula']))    $colab['matricula']    = '';
    if (!isset($colab['cpf']))          $colab['cpf']          = '';
    if (!isset($colab['departamento'])) $colab['departamento'] = '';
    if (!isset($colab['email']))        $colab['email']        = '';
    if (!isset($colab['tipo_trabalho']))$colab['tipo_trabalho']= 'local';
}
unset($colab);

// Ordenar alfabeticamente
usort($colaboradores, fn($a, $b) => strcmp($a['nome'], $b['nome']));

// Mapa de equipamentos por colaborador (arquitetura atual)
$equipamentosPorColaborador = [];
foreach (carregarTodosEquipamentos() as $equip) {
    if (!empty($equip['colaborador_id']) && in_array($equip['status'], ['alocado', 'emprestado'])) {
        $equipamentosPorColaborador[$equip['colaborador_id']][] = $equip;
    }
}

// Mapa de linhas por colaborador
$linhasPorColaborador = [];
foreach ($linhas as $linha) {
    if (!empty($linha['colaborador_id'])) {
        $linhasPorColaborador[$linha['colaborador_id']][] = $linha;
    }
}

// Filtro de busca (só afeta a exibição, nunca os dados salvos)
$busca = $_GET['busca'] ?? '';
if ($busca) {
    $colaboradores = array_values(array_filter($colaboradores, function($c) use ($busca) {
        return stripos($c['nome'],        $busca) !== false
            || stripos($c['matricula']  ?? '', $busca) !== false
            || stripos($c['cpf']        ?? '', $busca) !== false
            || stripos($c['departamento']?? '', $busca) !== false
            || stripos($c['email']      ?? '', $busca) !== false;
    }));
    usort($colaboradores, fn($a, $b) => strcmp($a['nome'], $b['nome']));
}

// Estatísticas
$totalColaboradores        = count($colaboradores);
$totalEquipamentosAlocados = count(carregarEquipamentosPorStatus('alocado')) + count(carregarEquipamentosPorStatus('emprestado'));
$totalLinhasAtivas         = count($linhas);
$totalHomeOffice           = count(array_filter($colaboradores, fn($c) => ($c['tipo_trabalho'] ?? 'local') === 'home'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colaboradores - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/colaboradores/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/favicon/favicon.png">
</head>
<body>

<!-- ==================== HEADER ==================== -->
<header class="header">
    <div class="header-content">
        <div class="logo">
            <a href="../index.php">
                <i class="fas fa-users"></i>
                <h1>Gestão de Colaboradores</h1>
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
            <li class="nav-item"><a href="index.php" class="nav-link active"><i class="fas fa-users"></i><span>Colaboradores</span></a></li>
            <li class="nav-item"><a href="../equipamentos/index.php" class="nav-link"><i class="fas fa-laptop"></i><span>Equipamentos</span></a></li>
            <li class="nav-item"><a href="../linhas/index.php" class="nav-link"><i class="fas fa-phone"></i><span>Linhas</span></a></li>
            <?php if ($is_admin): ?>
                <li class="nav-item"><a href="../termos/index.php" class="nav-link"><i class="fas fa-file-contract"></i><span>Termos</span></a></li>
                <li class="nav-item"><a href="../usuarios/index.php" class="nav-link"><i class="fas fa-user-cog"></i><span>Usuários</span></a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<!-- Mensagens de alerta -->
<?php if (isset($_SESSION['mensagem'])): ?>
    <div class="global-alert" style="background: <?php echo $_SESSION['mensagem_tipo'] === 'success' ? 'rgba(76,175,80,0.1)' : 'rgba(244,67,54,0.1)'; ?>; border-left: 4px solid <?php echo $_SESSION['mensagem_tipo'] === 'success' ? '#4CAF50' : '#F44336'; ?>;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas fa-<?php echo $_SESSION['mensagem_tipo'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>" style="color: <?php echo $_SESSION['mensagem_tipo'] === 'success' ? '#4CAF50' : '#F44336'; ?>;"></i>
            <span><?php echo htmlspecialchars($_SESSION['mensagem']); ?></span>
        </div>
        <button onclick="this.parentElement.style.display='none'" style="background: none; border: none; font-size: 1.25rem; cursor: pointer;">&times;</button>
    </div>
    <?php unset($_SESSION['mensagem']); unset($_SESSION['mensagem_tipo']); ?>
<?php endif; ?>

<!-- ==================== CONTEÚDO PRINCIPAL ==================== -->
<main class="main-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-users"></i> Colaboradores Ativos</h1>
            <p class="page-subtitle">Gerencie todos os colaboradores ativos da sua organização</p>
        </div>
        <?php if ($is_admin): ?>
            <div style="display: flex; gap: 0.75rem;">
                <a href="inativos.php" class="btn btn-secondary">
                    <i class="fas fa-archive"></i> Ver Inativos
                </a>
                <a href="adicionar.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Adicionar Colaborador
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="fas fa-users"></i></div>
            <div class="stat-content">
                <h3>Total Colaboradores</h3>
                <div class="stat-number"><?php echo $totalColaboradores; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-laptop"></i></div>
            <div class="stat-content">
                <h3>Equipamentos Alocados</h3>
                <div class="stat-number"><?php echo $totalEquipamentosAlocados; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon info"><i class="fas fa-phone"></i></div>
            <div class="stat-content">
                <h3>Linhas Ativas</h3>
                <div class="stat-number"><?php echo $totalLinhasAtivas; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-home"></i></div>
            <div class="stat-content">
                <h3>Home Office</h3>
                <div class="stat-number"><?php echo $totalHomeOffice; ?></div>
            </div>
        </div>
    </div>

    <!-- Busca -->
    <div class="search-section">
        <form method="GET" action="">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="busca" class="search-input" placeholder="Buscar por nome, chamado, CPF, e-mail ou departamento..." value="<?php echo htmlspecialchars($busca); ?>">
                <button type="submit" class="btn btn-primary search-btn">Buscar</button>
                <?php if ($busca): ?>
                    <a href="index.php" class="btn btn-secondary">Limpar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Grid de Colaboradores -->
    <?php if (empty($colaboradores)): ?>
        <div class="empty-state">
            <i class="fas fa-users-slash"></i>
            <p>Nenhum colaborador ativo encontrado</p>
            <?php if ($busca): ?>
                <a href="index.php" class="btn btn-secondary">Limpar busca</a>
            <?php else: ?>
                <a href="adicionar.php" class="btn btn-primary">Adicionar primeiro colaborador</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="colaboradores-grid">
            <?php foreach ($colaboradores as $colaborador):
                $equipamentosColab = $equipamentosPorColaborador[$colaborador['id']] ?? [];
                $linhasColab = $linhasPorColaborador[$colaborador['id']] ?? [];
                $totalEquip = count($equipamentosColab);
                $totalLinhas = count($linhasColab);
                $tipoTrabalho = $colaborador['tipo_trabalho'] ?? 'local';
                $isHomeOffice = $tipoTrabalho === 'home';
                ?>
                <div class="colaborador-card">
                    <div class="card-header">
                        <div class="colaborador-nome">
                            <h3><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($colaborador['nome']); ?></h3>
                            <span class="tipo-badge-header">
                                <i class="fas fa-<?php echo $isHomeOffice ? 'home' : 'building'; ?>"></i>
                                <?php echo $isHomeOffice ? 'Home Office' : 'Presencial'; ?>
                            </span>
                        </div>
                        <?php if (!empty($colaborador['matricula'])): ?>
                            <div class="matricula-badge-header">
                                <i class="fas fa-id-badge"></i> <?php echo htmlspecialchars($colaborador['matricula']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="card-stats">
                            <div class="card-stat"><i class="fas fa-laptop"></i> <?php echo $totalEquip; ?> eqpt(s)</div>
                            <div class="card-stat"><i class="fas fa-phone"></i> <?php echo $totalLinhas; ?> linha(s)</div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="info-group">
                            <div class="info-label"><i class="fas fa-briefcase"></i> Cargo</div>
                            <div class="info-value"><?php echo htmlspecialchars($colaborador['cargo'] ?? 'Não informado'); ?></div>
                        </div>

                        <div class="info-group">
                            <div class="info-label"><i class="fas fa-id-card"></i> CPF</div>
                            <div class="info-value"><?php echo formatarCPF($colaborador['cpf'] ?? ''); ?></div>
                        </div>

                        <div class="info-group">
                            <div class="info-label"><i class="fas fa-envelope"></i> E-mail</div>
                            <div class="info-value">
                                <?php if (!empty($colaborador['email'])): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($colaborador['email']); ?>" class="email-link">
                                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($colaborador['email']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Não informado</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="info-group">
                            <div class="info-label"><i class="fas fa-building"></i> Departamento</div>
                            <div class="info-value">
                                <span class="departamento-badge"><?php echo htmlspecialchars($colaborador['departamento'] ?? 'Não informado'); ?></span>
                            </div>
                        </div>

                        <div class="info-group">
                            <div class="info-label"><i class="fas fa-dollar-sign"></i> Centro de Custo</div>
                            <div class="info-value">
                                <span class="cc-badge"><?php echo htmlspecialchars($colaborador['centro_custo'] ?? 'Não informado'); ?></span>
                            </div>
                        </div>

                        <?php if ($totalEquip > 0): ?>
                            <div class="divider"></div>
                            <div class="info-group">
                                <div class="info-label"><i class="fas fa-laptop"></i> Equipamentos</div>
                                <div class="equipamentos-list">
                                    <?php foreach (array_slice($equipamentosColab, 0, 2) as $equip): ?>
                                        <div class="equipamento-item">
                                            <div class="equipamento-info">
                                                <i class="fas fa-<?php echo $equip['tipo'] === 'notebook' ? 'laptop' : 'desktop'; ?>"></i>
                                                <span><?php echo htmlspecialchars($equip['patrimonio']); ?></span>
                                                <small><?php echo htmlspecialchars($equip['marca']); ?></small>
                                            </div>
                                            <span class="equipamento-status <?php echo $equip['status']; ?>">
                                                <?php echo $equip['status'] === 'alocado' ? 'Alocado' : ($equip['status'] === 'emprestado' ? 'Emprestado' : 'Estoque'); ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if ($totalEquip > 2): ?>
                                        <div class="view-more" onclick="location.href='../equipamentos/index.php?colaborador=<?php echo $colaborador['id']; ?>'">
                                            <i class="fas fa-arrow-right"></i> Ver mais <?php echo $totalEquip - 2; ?> equipamento(s)
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($totalLinhas > 0): ?>
                            <div class="divider"></div>
                            <div class="info-group">
                                <div class="info-label"><i class="fas fa-phone"></i> Linhas</div>
                                <div class="linhas-list">
                                    <?php foreach (array_slice($linhasColab, 0, 2) as $linha): ?>
                                        <div class="linha-item">
                                            <div class="linha-info">
                                                <i class="fas fa-sim-card"></i>
                                                <span><?php echo formatarTelefone($linha['numero']); ?></span>
                                                <span class="linha-type">(<?php echo $linha['tipo'] === 'chip' ? 'Chip' : 'E-Chip'; ?>)</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if ($totalLinhas > 2): ?>
                                        <div class="view-more" onclick="showLinhasModal(<?php echo htmlspecialchars(json_encode($linhasColab)); ?>, '<?php echo htmlspecialchars($colaborador['nome']); ?>')">
                                            <i class="fas fa-arrow-right"></i> Ver mais <?php echo $totalLinhas - 2; ?> linha(s)
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-footer">
                        <?php if ($is_admin): ?>
                            <a href="editar.php?id=<?php echo $colaborador['id']; ?>" class="action-btn edit" title="Editar"><i class="fas fa-edit"></i><span>Editar</span></a>
                            
                            <form method="POST" style="display: inline-block;" onsubmit="return confirm(<?php echo $isHomeOffice ? "'Tem certeza que deseja inativar este colaborador Home Office? Ele será movido para a lista de inativos com pendência de devolução de equipamentos.'" : "'Tem certeza que deseja inativar este colaborador? Todos os equipamentos serão devolvidos ao estoque.'"; ?>)">
                                <input type="hidden" name="colaborador_id" value="<?php echo $colaborador['id']; ?>">
                                <button type="submit" name="inativar" class="action-btn inactivate" title="Inativar">
                                    <i class="fas fa-user-slash"></i><span>Inativar</span>
                                </button>
                            </form>
                        <?php endif; ?>
                        <a href="../equipamentos/index.php?colaborador=<?php echo $colaborador['id']; ?>" class="action-btn equipments" title="Equipamentos"><i class="fas fa-laptop"></i><span>Equipamentos</span></a>
                        <a href="../linhas/index.php?colaborador=<?php echo $colaborador['id']; ?>" class="action-btn linhas" title="Linhas"><i class="fas fa-phone"></i><span>Linhas</span></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<!-- ==================== FOOTER ==================== -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3><i class="fas fa-users"></i> Sistema de Gestão</h3>
            <p>Controle de colaboradores e equipamentos</p>
        </div>
        <div class="footer-section">
            <h3>Links Rápidos</h3>
            <ul class="footer-links">
                <li><a href="../index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="index.php"><i class="fas fa-users"></i> Colaboradores</a></li>
                <li><a href="../equipamentos/index.php"><i class="fas fa-laptop"></i> Equipamentos</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Estatísticas</h3>
            <?php
            $total_colaboradores = count(lerArquivoJSON('../data/colaboradores/ativos.json'));
            $total_equipamentos  = count(carregarTodosEquipamentos());
            ?>
            <div class="footer-stats">
                <div class="footer-stat"><span class="stat-number"><?php echo $total_colaboradores; ?></span><span class="stat-label">Colaboradores</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo $total_equipamentos; ?></span><span class="stat-label">Equipamentos</span></div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
        <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
</footer>

<!-- Modal para lista completa de linhas -->
<div id="modalLinhas" class="modal-linhas">
    <div class="modal-linhas-content">
        <div class="modal-linhas-header">
            <h3><i class="fas fa-phone"></i> <span id="modalTitle">Linhas do Colaborador</span></h3>
            <button class="modal-linhas-close" onclick="closeLinhasModal()">&times;</button>
        </div>
        <div class="modal-linhas-body" id="modalLinhasBody"></div>
    </div>
</div>

<script>
    function showLinhasModal(linhas, colaboradorNome) {
        const modal = document.getElementById('modalLinhas');
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalLinhasBody');
        
        modalTitle.innerHTML = `<i class="fas fa-phone"></i> Linhas de ${colaboradorNome}`;
        
        if (linhas.length === 0) {
            modalBody.innerHTML = '<div class="no-linhas"><i class="fas fa-phone-slash"></i><p>Nenhuma linha atribuída a este colaborador.</p></div>';
        } else {
            let html = '';
            linhas.forEach(linha => {
                const numeroFormatado = formatarTelefoneLocal(linha.numero);
                const tipoTexto = linha.tipo === 'chip' ? 'Chip Físico' : 'E-Chip';
                html += `
                    <div class="linha-detail-item">
                        <div>
                            <span class="linha-number">${numeroFormatado}</span>
                            <span class="linha-type"> (${tipoTexto})</span>
                        </div>
                        <span class="cc-badge" style="font-size: 0.7rem;">
                            <i class="fas fa-dollar-sign"></i> ${linha.centro_custo || '---'}
                        </span>
                    </div>
                `;
            });
            modalBody.innerHTML = html;
        }
        
        modal.style.display = 'block';
    }
    
    function closeLinhasModal() {
        document.getElementById('modalLinhas').style.display = 'none';
    }
    
    function formatarTelefoneLocal(telefone) {
        if (!telefone) return '';
        const numero = telefone.replace(/\D/g, '');
        if (numero.length === 11) {
            return `${numero.substring(0, 2)} ${numero.substring(2, 7)}-${numero.substring(7, 11)}`;
        } else if (numero.length === 10) {
            return `${numero.substring(0, 2)} ${numero.substring(2, 6)}-${numero.substring(6, 10)}`;
        }
        return telefone;
    }
    
    window.onclick = function(event) {
        const modal = document.getElementById('modalLinhas');
        if (event.target === modal) closeLinhasModal();
    }
    
    setTimeout(function() {
        const alert = document.querySelector('.global-alert');
        if (alert) {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }
    }, 5000);
</script>
</body>
</html>