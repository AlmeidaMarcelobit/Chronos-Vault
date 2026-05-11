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

$colaboradores = lerArquivoJSON('../data/colaboradores.json');
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

// Contar equipamentos por colaborador
$equipamentosPorColaborador = [];
foreach ($equipamentos as $equipamento) {
    if ($equipamento['colaborador_id'] !== null) {
        $colaboradorId = $equipamento['colaborador_id'];
        if (!isset($equipamentosPorColaborador[$colaboradorId])) {
            $equipamentosPorColaborador[$colaboradorId] = 0;
        }
        $equipamentosPorColaborador[$colaboradorId]++;
    }
}

// Buscar linhas por colaborador
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
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colaboradores - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/colaboradores/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
<!-- ==================== HEADER ==================== -->
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
            <li class="nav-item">
                <a href="../index.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php" class="nav-link active">
                    <i class="fas fa-users"></i>
                    <span>Colaboradores</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../equipamentos/index.php" class="nav-link">
                    <i class="fas fa-laptop"></i>
                    <span>Equipamentos</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../linhas/index.php" class="nav-link">
                    <i class="fas fa-phone"></i>
                    <span>Linhas</span>
                </a>
            </li>
            <?php if ($is_admin): ?>
                <li class="nav-item">
                    <a href="../usuarios/index.php" class="nav-link">
                        <i class="fas fa-user-cog"></i>
                        <span>Usuários</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<!-- Mensagens de alerta -->
<?php if (isset($_SESSION['mensagem'])): ?>
    <div class="global-alert alert-<?php echo $_SESSION['mensagem_tipo'] ?? 'info'; ?>">
        <div class="alert-content">
            <i class="fas fa-<?php echo $_SESSION['mensagem_tipo'] === 'success' ? 'check-circle' : ($_SESSION['mensagem_tipo'] === 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
            <span><?php echo htmlspecialchars($_SESSION['mensagem']); ?></span>
        </div>
        <button class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
    <?php
    unset($_SESSION['mensagem']);
    unset($_SESSION['mensagem_tipo']);
endif; ?>

<!-- ==================== CONTEÚDO PRINCIPAL ==================== -->
<main class="main-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-users"></i> Colaboradores</h1>
            <p class="page-subtitle">Gerencie todos os colaboradores da sua organização</p>
            <div class="sort-info">
                <i class="fas fa-sort-alpha-down"></i>
                <small>Ordenado por nome (A-Z)</small>
            </div>
        </div>
        <div class="header-actions">
            <?php if ($is_admin): ?>
                <a href="adicionar.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i>
                    <span>Adicionar Colaborador</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="search-section">
        <form method="GET" action="" class="search-form">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="busca" class="search-input" placeholder="Buscar por nome, chamado, CPF, e-mail ou departamento..." value="<?php echo htmlspecialchars($busca); ?>">
                <button type="submit" class="btn btn-primary search-btn">
                    <i class="fas fa-search"></i>
                    <span>Buscar</span>
                </button>
                <?php if ($busca): ?>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        <span>Limpar</span>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
            <tr>
                <th>Nome <i class="fas fa-arrow-down" style="font-size: 0.7rem; opacity: 0.6;"></i></th>
                <th>Chamado</th>
                <th>Cargo</th>
                <th>E-mail</th>
                <th>CPF</th>
                <th>Departamento</th>
                <th>Centro de Custo</th>
                <th>Tipo</th>
                <th>Equipamentos</th>
                <th>Linhas</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($colaboradores)): ?>
                <tr>
                    <td colspan="12" class="empty-state">
                        <i class="fas fa-users-slash"></i>
                        <p>Nenhum colaborador encontrado</p>
                        <?php if ($busca): ?>
                            <a href="index.php" class="btn btn-secondary">Limpar busca</a>
                        <?php else: ?>
                            <a href="adicionar.php" class="btn btn-primary">Adicionar primeiro colaborador</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($colaboradores as $colaborador):
                    $linhasColaborador = $linhasPorColaborador[$colaborador['id']] ?? [];
                    $totalLinhas = count($linhasColaborador);
                    $primeirasLinhas = array_slice($linhasColaborador, 0, 2);
                    ?>
                    <tr>
                        <td data-label="Nome">
                            <div class="colaborador-info">
                                <i class="fas fa-user-circle"></i>
                                <span><?php echo htmlspecialchars($colaborador['nome']); ?></span>
                            </div>
                        </td>
                        <td data-label="Chamado">
                            <span class="matricula-badge">
                                <?php echo htmlspecialchars($colaborador['matricula'] ?? ''); ?>
                            </span>
                        </td>
                        <td data-label="Cargo"><?php echo htmlspecialchars($colaborador['cargo'] ?? ''); ?></td>
                        <td data-label="E-mail">
                            <?php if (!empty($colaborador['email'])): ?>
                                <a href="mailto:<?php echo htmlspecialchars($colaborador['email']); ?>" class="email-link">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo htmlspecialchars($colaborador['email']); ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">---</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="CPF"><?php echo formatarCPF($colaborador['cpf'] ?? ''); ?></td>
                        <td data-label="Departamento">
                            <span class="departamento-badge">
                                <?php echo htmlspecialchars($colaborador['departamento'] ?? ''); ?>
                            </span>
                        </td>
                        <td data-label="Centro de Custo"><?php echo htmlspecialchars($colaborador['centro_custo'] ?? ''); ?></td>

                        <td data-label="Tipo">
                            <?php
                            $tipoTrabalho = $colaborador['tipo_trabalho'] ?? 'local';
                            $tipoClass = $tipoTrabalho === 'home' ? 'badge-home' : 'badge-local';
                            $tipoIcon = $tipoTrabalho === 'home' ? 'home' : 'building';
                            $tipoText = $tipoTrabalho === 'home' ? 'Home Office' : 'Presencial';
                            ?>
                            <span class="tipo-badge <?php echo $tipoClass; ?>">
                                <i class="fas fa-<?php echo $tipoIcon; ?>"></i>
                                <?php echo $tipoText; ?>
                            </span>
                        </td>
                        <td data-label="Equipamentos">
                            <?php
                            $qtdEquipamentos = $equipamentosPorColaborador[$colaborador['id']] ?? 0;
                            ?>
                            <span class="equipamentos-count <?php echo $qtdEquipamentos > 0 ? 'has-equipamentos' : 'no-equipamentos'; ?>">
                                <i class="fas fa-laptop"></i>
                                <?php echo $qtdEquipamentos; ?> equipamento(s)
                            </span>
                        </td>
                        <td data-label="Linhas">
                            <?php if ($totalLinhas === 0): ?>
                                <span class="text-muted">---</span>
                            <?php else: ?>
                                <div class="linhas-list">
                                    <?php foreach ($primeirasLinhas as $linha): ?>
                                        <span class="linha-item-simple">
                                            <i class="fas fa-phone"></i>
                                            <?php echo formatarTelefone($linha['numero']); ?>
                                        </span>
                                    <?php endforeach; ?>
                                    <?php if ($totalLinhas > 2): ?>
                                        <span class="linhas-more" onclick="showLinhasModal(<?php echo htmlspecialchars(json_encode($linhasColaborador)); ?>, '<?php echo htmlspecialchars($colaborador['nome']); ?>')">
                                            +<?php echo ($totalLinhas - 2); ?> outra(s) linha(s)
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td data-label="Ações">
                            <div class="action-buttons">
                                <?php if ($is_admin): ?>
                                    <a href="editar.php?id=<?php echo $colaborador['id']; ?>" class="action-btn action-edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="excluir.php?id=<?php echo $colaborador['id']; ?>" class="action-btn action-delete" onclick="return confirm('Tem certeza que deseja excluir este colaborador?')" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="../equipamentos/index.php?colaborador=<?php echo $colaborador['id']; ?>" class="action-btn action-equipments" title="Ver Equipamentos">
                                    <i class="fas fa-laptop"></i>
                                </a>
                                <a href="../linhas/index.php?colaborador=<?php echo $colaborador['id']; ?>" class="action-btn action-equipments" title="Ver Linhas">
                                    <i class="fas fa-phone"></i>
                                </a>
                                <?php if ($is_admin): ?>
                                    <a href="termos.php?id=<?php echo $colaborador['id']; ?>" class="action-btn action-term" title="Gerenciar Termos">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <?php if ($qtdEquipamentos > 0): ?>
                                        <a href="selecionar_equipamentos_termo.php?id=<?php echo $colaborador['id']; ?>" class="action-btn action-term" title="Gerar Termo de Responsabilidade">
                                            <i class="fas fa-file-contract"></i>
                                        </a>
                                        <a href="selecionar_equipamentos_devolucao.php?id=<?php echo $colaborador['id']; ?>" class="action-btn action-return" title="Gerar Termo de Devolução">
                                            <i class="fas fa-box-open"></i>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="page-footer">
        <div class="total-count">
            <i class="fas fa-chart-line"></i>
            <span>Total de colaboradores: <strong><?php echo count($colaboradores); ?></strong></span>
        </div>
        <?php if ($is_admin): ?>
            <div class="legend">
                <div class="legend-title">
                    <i class="fas fa-info-circle"></i>
                    <span>Legenda de Ações:</span>
                </div>
                <div class="legend-items">
                    <span class="legend-item"><i class="fas fa-edit"></i> Editar</span>
                    <span class="legend-item"><i class="fas fa-trash"></i> Excluir</span>
                    <span class="legend-item"><i class="fas fa-laptop"></i> Equipamentos</span>
                    <span class="legend-item"><i class="fas fa-phone"></i> Linhas</span>
                    <span class="legend-item"><i class="fas fa-file-contract"></i> Termo</span>
                    <span class="legend-item"><i class="fas fa-box-open"></i> Devolução</span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Modal para lista completa de linhas -->
<div id="modalLinhas" class="modal-linhas">
    <div class="modal-linhas-content">
        <div class="modal-linhas-header">
            <h3>
                <i class="fas fa-phone"></i>
                <span id="modalTitle">Linhas do Colaborador</span>
            </h3>
            <button class="modal-linhas-close" onclick="closeLinhasModal()">&times;</button>
        </div>
        <div class="modal-linhas-body" id="modalLinhasBody">
            <!-- Conteúdo será preenchido dinamicamente -->
        </div>
    </div>
</div>

<!-- ==================== FOOTER ==================== -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3><i class="fas fa-laptop-house"></i> Sistema de Gestão</h3>
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
            // Carregar dados para estatísticas
            $total_colaboradores = count(lerArquivoJSON('../data/colaboradores.json'));
            $total_equipamentos = count(lerArquivoJSON('../data/equipamentos.json'));
            $total_linhas = count(lerArquivoJSON('../data/linhas.json'));
            $equipamentos_estoque = 0;
            $equipamentos_data = lerArquivoJSON('../data/equipamentos.json');
            foreach ($equipamentos_data as $e) {
                if (($e['status'] ?? '') === 'estoque') $equipamentos_estoque++;
            }
            ?>
            <div class="footer-stats">
                <div class="footer-stat">
                    <span class="stat-number"><?php echo $total_colaboradores; ?></span>
                    <span class="stat-label">Colaboradores</span>
                </div>
                <div class="footer-stat">
                    <span class="stat-number"><?php echo $total_equipamentos; ?></span>
                    <span class="stat-label">Equipamentos</span>
                </div>
                <div class="footer-stat">
                    <span class="stat-number"><?php echo $total_linhas; ?></span>
                    <span class="stat-label">Linhas</span>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
        <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
</footer>

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
                        <span class="info-badge">
                            <i class="fas fa-dollar-sign"></i> ${linha.centro_custo}
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

    // Fechar modal ao clicar fora
    window.onclick = function(event) {
        const modal = document.getElementById('modalLinhas');
        if (event.target === modal) {
            closeLinhasModal();
        }
    }

    // Fechar alerta após 5 segundos
    setTimeout(function() {
        const alert = document.querySelector('.global-alert');
        if (alert) {
            alert.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }
    }, 5000);
</script>
</body>
</html>