<?php
session_start();
require_once '../includes/funcoes.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$equipamentos = lerArquivoJSON('../data/equipamentos.json');
$colaboradores = lerArquivoJSON('../data/colaboradores.json');

// Obter todas as caixas únicas com equipamentos
$caixas = [];
foreach ($equipamentos as $equip) {
    if (!empty($equip['caixa'] ?? '')) {
        $caixas[$equip['caixa']] = [
            'nome' => $equip['caixa'],
            'quantidade' => 0,
            'equipamentos' => []
        ];
    }
}

// Contar equipamentos por caixa
foreach ($equipamentos as $equip) {
    if (!empty($equip['caixa'] ?? '')) {
        $caixas[$equip['caixa']]['quantidade']++;
        $caixas[$equip['caixa']]['equipamentos'][] = $equip;
    }
}

// Ordenar caixas
ksort($caixas);

$mensagem = '';
$tipoMensagem = '';

// Processar atribuição de caixa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caixa_id = $_POST['caixa_id'] ?? '';
    $colaborador_id = $_POST['colaborador_id'] ?? null;
    $status = $_POST['status'] ?? 'alocado';
    $observacoes = trim($_POST['observacoes'] ?? '');

    $erros = [];

    if (empty($caixa_id)) {
        $erros[] = 'Selecione uma caixa.';
    }

    if (empty($colaborador_id)) {
        $erros[] = 'Selecione um colaborador.';
    }

    if (empty($erros)) {
        $equipamentosAtualizados = 0;
        $equipamentosAlterados = [];

        // Atualizar todos os equipamentos da caixa
        foreach ($equipamentos as $index => &$equip) {
            if (($equip['caixa'] ?? '') === $caixa_id && $equip['status'] === 'estoque') {
                $equipamentos[$index]['colaborador_id'] = (int)$colaborador_id;
                $equipamentos[$index]['status'] = $status;
                $equipamentos[$index]['data_atribuicao'] = date('Y-m-d H:i:s');
                $equipamentos[$index]['data_atualizacao'] = date('Y-m-d H:i:s');
                $equipamentos[$index]['tipo_atribuicao'] = $status === 'emprestado' ? 'emprestimo' : 'alocacao';

                // Adicionar observação sobre a atribuição em massa
                $observacaoAtual = $equipamentos[$index]['observacoes'] ?? '';
                $novaObservacao = "\n\n[ATRIBUIÇÃO EM MASSA] " . date('d/m/Y H:i:s');
                $novaObservacao .= "\nAtribuído via caixa " . $caixa_id;
                $novaObservacao .= "\nObservações: " . $observacoes;
                $equipamentos[$index]['observacoes'] = $observacaoAtual . $novaObservacao;

                $equipamentosAtualizados++;
                $equipamentosAlterados[] = $equip['patrimonio'];
            }
        }

        if ($equipamentosAtualizados > 0) {
            if (salvarArquivoJSON('../data/equipamentos.json', $equipamentos)) {
                // Buscar nome do colaborador
                $colaboradorNome = '';
                foreach ($colaboradores as $colab) {
                    if ($colab['id'] == $colaborador_id) {
                        $colaboradorNome = $colab['nome'];
                        break;
                    }
                }

                $mensagem = "{$equipamentosAtualizados} equipamento(s) da caixa {$caixa_id} foram atribuídos com sucesso para {$colaboradorNome}!";
                $tipoMensagem = 'success';
            } else {
                $mensagem = 'Erro ao salvar as alterações. Tente novamente.';
                $tipoMensagem = 'error';
            }
        } else {
            $mensagem = 'Nenhum equipamento disponível na caixa selecionada para atribuição.';
            $tipoMensagem = 'warning';
        }
    } else {
        $mensagem = implode('<br>', $erros);
        $tipoMensagem = 'error';
    }
}

// Processar devolução de caixa
if (isset($_GET['devolver']) && isset($_GET['caixa'])) {
    $caixa_id = $_GET['caixa'];
    $colaborador_id = $_GET['colaborador'] ?? null;

    $equipamentosAtualizados = 0;

    foreach ($equipamentos as $index => &$equip) {
        if (($equip['caixa'] ?? '') === $caixa_id && in_array($equip['status'], ['alocado', 'emprestado'])) {
            $equipamentos[$index]['colaborador_id'] = null;
            $equipamentos[$index]['status'] = 'estoque';
            $equipamentos[$index]['data_atribuicao'] = null;
            $equipamentos[$index]['data_atualizacao'] = date('Y-m-d H:i:s');

            // Limpar campos de empréstimo
            if (isset($equipamentos[$index]['data_devolucao_prevista'])) {
                unset($equipamentos[$index]['data_devolucao_prevista']);
            }
            if (isset($equipamentos[$index]['tipo_atribuicao'])) {
                unset($equipamentos[$index]['tipo_atribuicao']);
            }

            // Adicionar observação sobre a devolução em massa
            $observacaoAtual = $equipamentos[$index]['observacoes'] ?? '';
            $novaObservacao = "\n\n[DEVOLUÇÃO EM MASSA] " . date('d/m/Y H:i:s');
            $novaObservacao .= "\nDevolvido via caixa " . $caixa_id;
            $equipamentos[$index]['observacoes'] = $observacaoAtual . $novaObservacao;

            $equipamentosAtualizados++;
        }
    }

    if ($equipamentosAtualizados > 0) {
        if (salvarArquivoJSON('../data/equipamentos.json', $equipamentos)) {
            $_SESSION['mensagem'] = "{$equipamentosAtualizados} equipamento(s) da caixa {$caixa_id} foram devolvidos com sucesso!";
            $_SESSION['mensagem_tipo'] = 'success';
        } else {
            $_SESSION['mensagem'] = 'Erro ao devolver os equipamentos. Tente novamente.';
            $_SESSION['mensagem_tipo'] = 'error';
        }
    } else {
        $_SESSION['mensagem'] = 'Nenhum equipamento alocado na caixa selecionada.';
        $_SESSION['mensagem_tipo'] = 'warning';
    }

    header('Location: atribuir_caixa.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atribuir Caixa - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/equipamentos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .caixa-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--gray-200);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
            transition: var(--transition);
        }

        .caixa-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .caixa-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-md);
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--gray-200);
        }

        .caixa-titulo {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .caixa-titulo i {
            font-size: 1.5rem;
            color: var(--grape);
        }

        .caixa-titulo h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .caixa-stats {
            display: flex;
            gap: var(--spacing-md);
        }

        .stat-badge {
            padding: 4px var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .stat-total {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .stat-alocado {
            background: rgba(46, 204, 113, 0.1);
            color: var(--success);
        }

        .stat-disponivel {
            background: rgba(52, 152, 219, 0.1);
            color: var(--info);
        }

        .equipamentos-lista {
            margin-top: var(--spacing-md);
            max-height: 300px;
            overflow-y: auto;
        }

        .equipamento-item-caixa {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-sm);
            border-bottom: 1px solid var(--gray-100);
            font-size: 0.875rem;
        }

        .equipamento-item-caixa:last-child {
            border-bottom: none;
        }

        .equipamento-info {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            flex-wrap: wrap;
        }

        .equipamento-status {
            font-size: 0.7rem;
            padding: 2px var(--spacing-sm);
            border-radius: var(--radius-sm);
        }

        .btn-caixa {
            padding: var(--spacing-sm) var(--spacing-lg);
        }

        .caixa-actions {
            display: flex;
            gap: var(--spacing-sm);
        }
    </style>
</head>
<body>
<!-- HEADER -->
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
            <li class="nav-item"><a href="../index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="../colaboradores/index.php" class="nav-link"><i class="fas fa-users"></i><span>Colaboradores</span></a></li>
            <li class="nav-item"><a href="index.php" class="nav-link active"><i class="fas fa-laptop"></i><span>Equipamentos</span></a></li>
            <li class="nav-item"><a href="../linhas/index.php" class="nav-link"><i class="fas fa-phone"></i><span>Linhas</span></a></li>
        </ul>
    </nav>
</header>

<!-- MENSAGENS DE ALERTA -->
<?php if ($mensagem): ?>
    <div class="global-alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : ($tipoMensagem === 'warning' ? 'warning' : 'error'); ?>">
        <div class="alert-content">
            <i class="fas fa-<?php echo $tipoMensagem === 'success' ? 'check-circle' : ($tipoMensagem === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'); ?>"></i>
            <span><?php echo $mensagem; ?></span>
        </div>
        <button class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
<?php endif; ?>

<!-- CONTEÚDO PRINCIPAL -->
<main class="main-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-boxes"></i> Atribuir Caixa</h1>
            <p class="page-subtitle">Atribua todos os equipamentos de uma caixa para um colaborador de uma só vez</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <!-- Formulário de Atribuição de Caixa -->
    <div class="form-card-container">
        <form method="POST" action="" class="form-card">
            <div class="form-grid">
                <div class="form-group">
                    <label for="caixa_id"><i class="fas fa-box"></i> Selecionar Caixa <span class="required">*</span></label>
                    <select id="caixa_id" name="caixa_id" required class="form-select" onchange="carregarDetalhesCaixa()">
                        <option value="">-- Selecione uma caixa --</option>
                        <?php foreach ($caixas as $caixa): ?>
                            <option value="<?php echo htmlspecialchars($caixa['nome']); ?>">
                                <?php echo htmlspecialchars($caixa['nome']); ?> (<?php echo $caixa['quantidade']; ?> equipamentos)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="colaborador_id"><i class="fas fa-user"></i> Selecionar Colaborador <span class="required">*</span></label>
                    <select id="colaborador_id" name="colaborador_id" required class="form-select">
                        <option value="">-- Selecione um colaborador --</option>
                        <?php foreach ($colaboradores as $colaborador): ?>
                            <option value="<?php echo $colaborador['id']; ?>">
                                <?php echo htmlspecialchars($colaborador['nome'] . ' - ' . $colaborador['cargo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status"><i class="fas fa-tag"></i> Tipo de Atribuição <span class="required">*</span></label>
                    <select id="status" name="status" required class="form-select">
                        <option value="alocado">Alocar (permanente)</option>
                        <option value="emprestado">Emprestar (temporário)</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label for="observacoes"><i class="fas fa-sticky-note"></i> Observações da Atribuição</label>
                    <textarea id="observacoes" name="observacoes" class="form-control" rows="2" placeholder="Observações sobre esta atribuição em massa..."></textarea>
                </div>
            </div>

            <div id="detalhes-caixa" class="info-card" style="display: none;">
                <h3><i class="fas fa-list"></i> Equipamentos da Caixa</h3>
                <div id="lista-equipamentos"></div>
            </div>

            <div class="warning-card">
                <div class="warning-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h4>Atenção!</h4>
                </div>
                <p>Ao atribuir uma caixa:</p>
                <ul>
                    <li>Todos os equipamentos <strong>EM ESTOQUE</strong> da caixa serão atribuídos ao colaborador</li>
                    <li>Equipamentos já alocados ou em manutenção não serão alterados</li>
                    <li>O status de cada equipamento será alterado para o tipo selecionado</li>
                    <li>Esta ação pode ser desfeita individualmente ou pela caixa</li>
                </ul>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Atribuir Caixa</button>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            </div>
        </form>
    </div>

    <!-- Lista de Caixas e seus Status -->
    <div class="caixas-list">
        <h2><i class="fas fa-boxes"></i> Caixas Registradas</h2>

        <?php if (empty($caixas)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <p>Nenhuma caixa cadastrada.</p>
                <small>Adicione equipamentos com caixa para visualizar aqui.</small>
            </div>
        <?php else: ?>
            <div class="caixas-grid">
                <?php foreach ($caixas as $caixa):
                    $equipamentosCaixa = $caixa['equipamentos'];
                    $total = count($equipamentosCaixa);
                    $alocados = count(array_filter($equipamentosCaixa, function($e) {
                        return in_array($e['status'], ['alocado', 'emprestado']);
                    }));
                    $disponiveis = $total - $alocados;
                    $colaboradorAtual = null;

                    // Verificar se todos os equipamentos estão com o mesmo colaborador
                    $colaboradoresUnicos = [];
                    foreach ($equipamentosCaixa as $eq) {
                        if (!empty($eq['colaborador_id'])) {
                            $colaboradoresUnicos[$eq['colaborador_id']] = true;
                        }
                    }

                    $todosMesmoColaborador = count($colaboradoresUnicos) === 1;
                    if ($todosMesmoColaborador && !empty($colaboradoresUnicos)) {
                        $colabId = array_key_first($colaboradoresUnicos);
                        foreach ($colaboradores as $colab) {
                            if ($colab['id'] == $colabId) {
                                $colaboradorAtual = $colab;
                                break;
                            }
                        }
                    }
                    ?>
                    <div class="caixa-card">
                        <div class="caixa-header">
                            <div class="caixa-titulo">
                                <i class="fas fa-box"></i>
                                <h3>Caixa <?php echo htmlspecialchars($caixa['nome']); ?></h3>
                            </div>
                            <div class="caixa-stats">
                                <span class="stat-badge stat-total"><i class="fas fa-chart-line"></i> Total: <?php echo $total; ?></span>
                                <span class="stat-badge stat-alocado"><i class="fas fa-user-check"></i> Alocados: <?php echo $alocados; ?></span>
                                <span class="stat-badge stat-disponivel"><i class="fas fa-warehouse"></i> Disponíveis: <?php echo $disponiveis; ?></span>
                            </div>
                        </div>

                        <?php if ($colaboradorAtual): ?>
                            <div class="info-card" style="margin-bottom: var(--spacing-md); padding: var(--spacing-sm);">
                                <div class="info-item">
                                    <span class="info-label">Atribuído a:</span>
                                    <span class="info-value">
                                    <i class="fas fa-user-circle"></i>
                                    <?php echo htmlspecialchars($colaboradorAtual['nome']); ?>
                                    (<?php echo htmlspecialchars($colaboradorAtual['cargo']); ?>)
                                </span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="equipamentos-lista">
                            <?php foreach ($equipamentosCaixa as $equip): ?>
                                <div class="equipamento-item-caixa">
                                    <div class="equipamento-info">
                                        <strong><?php echo htmlspecialchars($equip['patrimonio']); ?></strong>
                                        <span><?php echo htmlspecialchars($equip['marca'] . ' ' . $equip['modelo']); ?></span>
                                        <span class="equipamento-status status-<?php
                                        echo $equip['status'] === 'estoque' ? 'ativo' :
                                            ($equip['status'] === 'alocado' ? 'inativo' :
                                                ($equip['status'] === 'emprestado' ? 'info' :
                                                    ($equip['status'] === 'manutencao' ? 'warning' : 'danger')));
                                        ?>">
                                        <i class="fas fa-<?php echo getIconByStatus($equip['status']); ?>"></i>
                                        <?php echo getStatusTexto($equip['status']); ?>
                                    </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="caixa-actions" style="margin-top: var(--spacing-md);">
                            <?php if ($alocados > 0): ?>
                                <a href="?devolver=1&caixa=<?php echo urlencode($caixa['nome']); ?>"
                                   class="btn btn-warning btn-caixa"
                                   onclick="return confirm('Deseja devolver TODOS os equipamentos desta caixa para o estoque?')">
                                    <i class="fas fa-undo"></i> Devolver Caixa
                                </a>
                            <?php endif; ?>
                            <a href="index.php?caixa=<?php echo urlencode($caixa['nome']); ?>" class="btn btn-secondary btn-caixa">
                                <i class="fas fa-eye"></i> Ver Detalhes
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- FOOTER -->
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
                <li><a href="../colaboradores/index.php"><i class="fas fa-users"></i> Colaboradores</a></li>
                <li><a href="index.php"><i class="fas fa-laptop"></i> Equipamentos</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Estatísticas</h3>
            <?php
            $total_equipamentos = count(lerArquivoJSON('../data/equipamentos.json'));
            $total_caixas = count($caixas);
            ?>
            <div class="footer-stats">
                <div class="footer-stat"><span class="stat-number"><?php echo $total_equipamentos; ?></span><span class="stat-label">Equipamentos</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo $total_caixas; ?></span><span class="stat-label">Caixas</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo count($colaboradores); ?></span><span class="stat-label">Colaboradores</span></div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
        <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
</footer>

<script>
    const equipamentosData = <?php
        $equipamentosPorCaixa = [];
        foreach ($equipamentos as $e) {
            if (!empty($e['caixa'])) {
                $equipamentosPorCaixa[$e['caixa']][] = $e;
            }
        }
        echo json_encode($equipamentosPorCaixa);
        ?>;

    function carregarDetalhesCaixa() {
        const caixaSelect = document.getElementById('caixa_id');
        const detalhesDiv = document.getElementById('detalhes-caixa');
        const listaDiv = document.getElementById('lista-equipamentos');
        const caixaSelecionada = caixaSelect.value;

        if (caixaSelecionada && equipamentosData[caixaSelecionada]) {
            const equipamentos = equipamentosData[caixaSelecionada];
            const disponiveis = equipamentos.filter(e => e.status === 'estoque');
            const alocados = equipamentos.filter(e => e.status === 'alocado' || e.status === 'emprestado');

            let html = `
                    <div class="info-grid" style="margin-bottom: var(--spacing-md);">
                        <div class="info-item">
                            <span class="info-label">Total de Equipamentos:</span>
                            <span class="info-value">${equipamentos.length}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Disponíveis para Atribuição:</span>
                            <span class="info-value" style="color: var(--success);">${disponiveis.length}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Já Alocados/Emprestados:</span>
                            <span class="info-value" style="color: var(--warning);">${alocados.length}</span>
                        </div>
                    </div>
                    <div class="equipamentos-lista">
                `;

            equipamentos.forEach(equip => {
                const statusClass = equip.status === 'estoque' ? 'status-ativo' :
                    (equip.status === 'alocado' ? 'status-inativo' :
                        (equip.status === 'emprestado' ? 'status-info' : 'status-warning'));
                const statusText = equip.status === 'estoque' ? 'Disponível' :
                    (equip.status === 'alocado' ? 'Alocado' :
                        (equip.status === 'emprestado' ? 'Emprestado' : 'Em Manutenção'));

                html += `
                        <div class="equipamento-item-caixa">
                            <div class="equipamento-info">
                                <strong>${equip.patrimonio}</strong>
                                <span>${equip.marca} ${equip.modelo}</span>
                                <span class="equipamento-status ${statusClass}">
                                    <i class="fas fa-${equip.status === 'estoque' ? 'warehouse' : (equip.status === 'alocado' ? 'user-check' : 'handshake')}"></i>
                                    ${statusText}
                                </span>
                            </div>
                        </div>
                    `;
            });

            html += `</div>`;
            listaDiv.innerHTML = html;
            detalhesDiv.style.display = 'block';
        } else {
            detalhesDiv.style.display = 'none';
        }
    }

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