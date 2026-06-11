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

// Carregar dados dos novos caminhos
$colaboradores = lerArquivoJSON('../data/colaboradores/ativos.json');
$equipamentos = lerArquivoJSON('../data/equipamentos.json');
$linhas = lerArquivoJSON('../data/linhas.json');

// Garantir que todos os colaboradores tenham os campos necessários
foreach ($colaboradores as &$colab) {
    if (!isset($colab['matricula'])) $colab['matricula'] = '';
    if (!isset($colab['cpf'])) $colab['cpf'] = '';
    if (!isset($colab['departamento'])) $colab['departamento'] = '';
    if (!isset($colab['email'])) $colab['email'] = '';
    if (!isset($colab['tipo_trabalho'])) $colab['tipo_trabalho'] = 'local';
}

// Ordenar colaboradores em ordem alfabética por nome
usort($colaboradores, function($a, $b) {
    return strcmp($a['nome'], $b['nome']);
});

// Criar mapa de equipamentos por colaborador
$equipamentosPorColaborador = [];
foreach ($equipamentos as $equipamento) {
    if ($equipamento['colaborador_id'] !== null) {
        $colaboradorId = $equipamento['colaborador_id'];
        if (!isset($equipamentosPorColaborador[$colaboradorId])) {
            $equipamentosPorColaborador[$colaboradorId] = [];
        }
        $equipamentosPorColaborador[$colaboradorId][] = $equipamento;
    }
}

// Criar mapa de linhas por colaborador
$linhasPorColaborador = [];
foreach ($linhas as $linha) {
    if (!empty($linha['colaborador_id'])) {
        $colaboradorId = $linha['colaborador_id'];
        if (!isset($linhasPorColaborador[$colaboradorId])) {
            $linhasPorColaborador[$colaboradorId] = [];
        }
        $linhasPorColaborador[$colaboradorId][] = $linha;
    }
}

// Buscar por nome (se aplicável)
$busca = $_GET['busca'] ?? '';
if ($busca) {
    $colaboradores = array_filter($colaboradores, function($colaborador) use ($busca) {
        return stripos($colaborador['nome'], $busca) !== false ||
                (isset($colaborador['matricula']) && stripos($colaborador['matricula'], $busca) !== false) ||
                (isset($colaborador['cpf']) && stripos($colaborador['cpf'], $busca) !== false) ||
                (isset($colaborador['departamento']) && stripos($colaborador['departamento'], $busca) !== false) ||
                (isset($colaborador['email']) && stripos($colaborador['email'], $busca) !== false);
    });
    
    // Reordenar após o filtro
    usort($colaboradores, function($a, $b) {
        return strcmp($a['nome'], $b['nome']);
    });
}

// Estatísticas
$totalColaboradores = count($colaboradores);
$totalEquipamentosAlocados = count($equipamentos);
$totalLinhasAtivas = count($linhas);
$totalHomeOffice = count(array_filter($colaboradores, function($c) {
    return ($c['tipo_trabalho'] ?? 'local') === 'home';
}));

// Processar inativação (verifica tipo de trabalho)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inativar'])) {
    $colaboradorId = $_POST['colaborador_id'] ?? null;
    if ($colaboradorId) {
        // Buscar o tipo de trabalho e os dados do colaborador
        $tipoTrabalho = 'local';
        $colaboradorEncontrado = null;
        $colaboradorIndex = null;
        
        foreach ($colaboradores as $index => $colab) {
            if ($colab['id'] == $colaboradorId) {
                $tipoTrabalho = $colab['tipo_trabalho'] ?? 'local';
                $colaboradorEncontrado = $colab;
                $colaboradorIndex = $index;
                break;
            }
        }
        
        if (!$colaboradorEncontrado) {
            $_SESSION['mensagem'] = 'Colaborador não encontrado.';
            $_SESSION['mensagem_tipo'] = 'error';
            header('Location: index.php');
            exit;
        }
        
        // Adicionar data de inativação
        $colaboradorEncontrado['data_inativacao'] = date('Y-m-d H:i:s');
        
        if ($tipoTrabalho === 'home') {
            $colaboradorEncontrado['motivo_inativacao'] = 'Aguardando devolução de equipamentos (Home Office)';
            $colaboradorEncontrado['status_inativacao'] = 'pendente';
        } else {
            $colaboradorEncontrado['motivo_inativacao'] = 'Inativado pelo sistema';
            $colaboradorEncontrado['status_inativacao'] = 'inativo';
        }
        
        // Carregar colaboradores inativos
        $inativos = lerArquivoJSON('../data/colaboradores/inativos.json');
        if ($inativos === false) $inativos = [];
        
        // Adicionar aos inativos
        $inativos[] = $colaboradorEncontrado;
        
        // Remover dos ativos - USANDO array_values para reindexar
        unset($colaboradores[$colaboradorIndex]);
        $colaboradores = array_values($colaboradores);
        
        // Devolver equipamentos (apenas para presencial)
        $equipamentosAtualizados = 0;
        foreach ($equipamentos as $index => &$equip) {
            if ($equip['colaborador_id'] == $colaboradorId) {
                $equip['colaborador_id'] = null;
                $equip['status'] = 'estoque';
                $equip['data_atribuicao'] = null;
                $equip['data_atualizacao'] = date('Y-m-d H:i:s');
                
                // Adicionar observação de inativação
                $observacaoAtual = $equip['observacoes'] ?? '';
                $novaObservacao = "\n\n[INATIVAÇÃO DE COLABORADOR] " . date('d/m/Y H:i:s');
                $novaObservacao .= "\nColaborador inativado: {$colaboradorEncontrado['nome']}";
                $novaObservacao .= "\nEquipamento devolvido ao estoque.";
                $equip['observacoes'] = $observacaoAtual . $novaObservacao;
                
                $equipamentosAtualizados++;
            }
        }
        
        // Remover vínculo das linhas
        foreach ($linhas as $index => &$linha) {
            if ($linha['colaborador_id'] == $colaboradorId) {
                $linha['colaborador_id'] = null;
                $linha['data_atualizacao'] = date('Y-m-d H:i:s');
            }
        }
        
        // Salvar alterações
        $saveAtivos = salvarArquivoJSON('../data/colaboradores/ativos.json', $colaboradores);
        $saveInativos = salvarArquivoJSON('../data/colaboradores/inativos.json', $inativos);
        $saveEquipamentos = salvarArquivoJSON('../data/equipamentos.json', $equipamentos);
        $saveLinhas = salvarArquivoJSON('../data/linhas.json', $linhas);
        
        if ($saveAtivos && $saveInativos && $saveEquipamentos && $saveLinhas) {
            if ($tipoTrabalho === 'home') {
                $_SESSION['mensagem'] = "Colaborador movido para inativos com pendência de devolução de equipamentos.";
            } else {
                $_SESSION['mensagem'] = "Colaborador inativado com sucesso! {$equipamentosAtualizados} equipamento(s) devolvido(s) ao estoque.";
            }
            $_SESSION['mensagem_tipo'] = 'success';
        } else {
            $_SESSION['mensagem'] = 'Erro ao inativar colaborador. Tente novamente.';
            $_SESSION['mensagem_tipo'] = 'error';
        }
        
        header('Location: index.php');
        exit;
    }
}
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
            $total_equipamentos = count(lerArquivoJSON('../data/equipamentos.json'));
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