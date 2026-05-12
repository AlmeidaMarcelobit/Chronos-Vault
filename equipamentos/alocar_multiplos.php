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

// Criar mapa de colaboradores
$mapaColaboradores = [];
foreach ($colaboradores as $colaborador) {
    $mapaColaboradores[$colaborador['id']] = $colaborador;
}

// Filtrar apenas equipamentos disponíveis (em estoque)
$equipamentosDisponiveis = array_filter($equipamentos, function($e) {
    return $e['status'] === 'estoque';
});

// Ordenar equipamentos por patrimônio
usort($equipamentosDisponiveis, function($a, $b) {
    return strcmp($a['patrimonio'], $b['patrimonio']);
});

$mensagem = '';
$tipoMensagem = '';

// Processar alocação múltipla
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $colaborador_id = $_POST['colaborador_id'] ?? null;
    $equipamentos_selecionados = $_POST['equipamentos_selecionados'] ?? [];
    $status = $_POST['status'] ?? 'alocado';
    $observacoes = trim($_POST['observacoes'] ?? '');
    $atualizar_centro_custo = isset($_POST['atualizar_centro_custo']) && $_POST['atualizar_centro_custo'] === 'sim';

    $erros = [];

    if (empty($colaborador_id)) {
        $erros[] = 'Selecione um colaborador.';
    }

    if (empty($equipamentos_selecionados)) {
        $erros[] = 'Selecione pelo menos um equipamento.';
    }

    if (empty($erros)) {
        // Buscar dados do colaborador
        $colaboradorSelecionado = null;
        foreach ($colaboradores as $colab) {
            if ($colab['id'] == $colaborador_id) {
                $colaboradorSelecionado = $colab;
                break;
            }
        }

        if (!$colaboradorSelecionado) {
            $erros[] = 'Colaborador não encontrado.';
        } else {
            $equipamentosAtualizados = 0;
            $equipamentosAlterados = [];
            $centroCustoAlterados = [];
            $centroCustoColaborador = $colaboradorSelecionado['centro_custo'];
            $colaboradorNome = $colaboradorSelecionado['nome'];

            foreach ($equipamentos as $index => &$equip) {
                if (in_array($equip['id'], $equipamentos_selecionados) && $equip['status'] === 'estoque') {
                    $centroCustoOriginal = $equip['centro_custo'];

                    $equipamentos[$index]['colaborador_id'] = (int)$colaborador_id;
                    $equipamentos[$index]['status'] = $status;
                    $equipamentos[$index]['data_atribuicao'] = date('Y-m-d H:i:s');
                    $equipamentos[$index]['data_atualizacao'] = date('Y-m-d H:i:s');
                    $equipamentos[$index]['tipo_atribuicao'] = $status === 'emprestado' ? 'emprestimo' : 'alocacao';

                    // Atualizar centro de custo se a opção estiver marcada
                    if ($atualizar_centro_custo && $centroCustoColaborador) {
                        if (!isset($equip['historico_centro_custo']) || !is_array($equip['historico_centro_custo'])) {
                            $equipamentos[$index]['historico_centro_custo'] = [];
                        }

                        $historicoCC = [
                                'data' => date('Y-m-d H:i:s'),
                                'usuario' => $_SESSION['usuario_nome'] ?? 'Administrador',
                                'centro_custo_anterior' => $centroCustoOriginal,
                                'centro_custo_novo' => $centroCustoColaborador,
                                'motivo' => "Alocação múltipla - Equipamento alocado para {$colaboradorNome}"
                        ];
                        $equipamentos[$index]['historico_centro_custo'][] = $historicoCC;

                        if ($centroCustoOriginal != $centroCustoColaborador) {
                            $equipamentos[$index]['centro_custo'] = $centroCustoColaborador;
                            $centroCustoAlterados[] = $equip['patrimonio'];
                        }
                    }

                    $observacaoAtual = $equipamentos[$index]['observacoes'] ?? '';
                    $novaObservacao = "\n\n[ALOCAÇÃO MÚLTIPLA] " . date('d/m/Y H:i:s');
                    $novaObservacao .= "\nEquipamento alocado para {$colaboradorNome}";

                    if ($atualizar_centro_custo && $centroCustoColaborador && $centroCustoOriginal != $centroCustoColaborador) {
                        $novaObservacao .= "\nCentro de custo atualizado de {$centroCustoOriginal} para {$centroCustoColaborador}";
                    }

                    if (!empty($observacoes)) {
                        $novaObservacao .= "\nObservações: " . $observacoes;
                    }

                    $equipamentos[$index]['observacoes'] = $observacaoAtual . $novaObservacao;

                    $equipamentosAtualizados++;
                    $equipamentosAlterados[] = $equip['patrimonio'];
                }
            }

            if ($equipamentosAtualizados > 0) {
                if (salvarArquivoJSON('../data/equipamentos.json', $equipamentos)) {
                    $mensagemExtra = '';
                    if ($atualizar_centro_custo && $centroCustoColaborador) {
                        $quantidadeCC = count($centroCustoAlterados);
                        if ($quantidadeCC > 0) {
                            $mensagemExtra = " O centro de custo de {$quantidadeCC} equipamento(s) foi atualizado para {$centroCustoColaborador}.";
                        }
                    }
                    $mensagem = "{$equipamentosAtualizados} equipamento(s) alocado(s) com sucesso para {$colaboradorNome}!" . $mensagemExtra;
                    $tipoMensagem = 'success';

                    $_POST = [];
                } else {
                    $mensagem = 'Erro ao salvar as alterações. Tente novamente.';
                    $tipoMensagem = 'error';
                }
            } else {
                $mensagem = 'Nenhum equipamento disponível foi selecionado.';
                $tipoMensagem = 'warning';
            }
        }
    } else {
        $mensagem = implode('<br>', $erros);
        $tipoMensagem = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alocar Múltiplos Equipamentos - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/equipamentos/alocar_multiplos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../img/favicon/favicon.png">
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

<?php if ($mensagem): ?>
    <div class="global-alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : ($tipoMensagem === 'warning' ? 'warning' : 'error'); ?>">
        <div class="alert-content">
            <i class="fas fa-<?php echo $tipoMensagem === 'success' ? 'check-circle' : ($tipoMensagem === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'); ?>"></i>
            <span><?php echo $mensagem; ?></span>
        </div>
        <button class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
<?php endif; ?>

<main class="main-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-layer-group"></i> Alocar Múltiplos Equipamentos</h1>
            <p class="page-subtitle">Selecione vários equipamentos e aloque todos para um colaborador de uma só vez</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="form-card-container">
        <form method="POST" action="" id="form-alocar-multiplos">
            <!-- Seleção do Colaborador com Busca -->
            <div class="form-group">
                <label><i class="fas fa-user"></i> Selecionar Colaborador <span class="required">*</span></label>

                <div class="colaborador-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="busca-colaborador" class="form-control" placeholder="Buscar por nome, cargo ou centro de custo..." autocomplete="off">
                </div>

                <div class="colaboradores-list" id="lista-colaboradores">
                    <?php if (empty($colaboradores)): ?>
                        <div class="sem-resultados">
                            <i class="fas fa-users-slash"></i>
                            <p>Nenhum colaborador cadastrado</p>
                            <a href="../colaboradores/adicionar.php" class="btn btn-primary btn-sm">Cadastrar colaborador</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($colaboradores as $colaborador): ?>
                            <div class="colaborador-item-select" data-id="<?php echo $colaborador['id']; ?>"
                                 data-nome="<?php echo htmlspecialchars($colaborador['nome']); ?>"
                                 data-cargo="<?php echo htmlspecialchars($colaborador['cargo']); ?>"
                                 data-departamento="<?php echo htmlspecialchars($colaborador['departamento']); ?>"
                                 data-centro-custo="<?php echo htmlspecialchars($colaborador['centro_custo']); ?>">
                                <div class="colaborador-info-select">
                                    <div class="colaborador-nome-select"><?php echo htmlspecialchars($colaborador['nome']); ?></div>
                                    <div class="colaborador-detalhes-select">
                                        <span><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($colaborador['cargo']); ?></span>
                                        <span><i class="fas fa-building"></i> <?php echo htmlspecialchars($colaborador['departamento']); ?></span>
                                        <span><i class="fas fa-dollar-sign"></i> <?php echo htmlspecialchars($colaborador['centro_custo']); ?></span>
                                    </div>
                                </div>
                                <div class="radio-select"></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <input type="hidden" id="colaborador_id" name="colaborador_id" required>
            </div>

            <!-- Tipo de Atribuição -->
            <div class="form-group">
                <label for="status"><i class="fas fa-tag"></i> Tipo de Atribuição <span class="required">*</span></label>
                <select id="status" name="status" required class="form-select">
                    <option value="alocado">Alocar (permanente)</option>
                    <option value="emprestado">Emprestar (temporário)</option>
                </select>
            </div>

            <!-- Informações de Centro de Custo -->
            <div id="info-centro-custo" class="info-centro-custo-multiplo" style="display: none;">
                <strong><i class="fas fa-sync-alt"></i> Informações de Centro de Custo</strong>
                <div style="margin-top: var(--spacing-sm);">
                    <div id="cc-detalhes"></div>
                    <div style="margin-top: var(--spacing-sm);">
                        <label class="checkbox-label">
                            <input type="checkbox" id="atualizar_centro_custo" name="atualizar_centro_custo" value="sim">
                            <span class="checkbox-custom"></span>
                            <span class="checkbox-text">
                                    <strong>Atualizar centro de custo dos equipamentos</strong><br>
                                    <small>O centro de custo de TODOS os equipamentos selecionados será alterado para o centro de custo do colaborador.</small>
                                </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <div class="form-group">
                <label for="observacoes"><i class="fas fa-sticky-note"></i> Observações da Atribuição</label>
                <textarea id="observacoes" name="observacoes" class="form-control" rows="2" placeholder="Observações sobre esta alocação em massa..."></textarea>
            </div>

            <!-- Lista de Equipamentos Disponíveis com Busca -->
            <div class="selection-header">
                <h3><i class="fas fa-laptop"></i> Equipamentos Disponíveis (Estoque)</h3>
                <div class="selection-actions">
                    <button type="button" class="select-all-btn" onclick="selecionarTodos()">
                        <i class="fas fa-check-double"></i> Selecionar Todos
                    </button>
                    <button type="button" class="select-all-btn" onclick="deselecionarTodos()">
                        <i class="fas fa-times"></i> Desmarcar Todos
                    </button>
                </div>
            </div>

            <!-- Busca de Equipamentos -->
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="busca-equipamento" class="form-control" placeholder="Buscar por patrimônio, marca, modelo ou centro de custo...">
            </div>

            <div class="equipamentos-lista" id="equipamentos-lista">
                <?php if (empty($equipamentosDisponiveis)): ?>
                    <div class="empty-state" style="padding: var(--spacing-2xl);">
                        <i class="fas fa-warehouse"></i>
                        <p>Nenhum equipamento disponível em estoque.</p>
                        <a href="adicionar.php" class="btn btn-primary">Adicionar Equipamento</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($equipamentosDisponiveis as $equipamento): ?>
                        <div class="equipamento-item-multiplo" data-id="<?php echo $equipamento['id']; ?>"
                             data-patrimonio="<?php echo htmlspecialchars($equipamento['patrimonio']); ?>"
                             data-marca="<?php echo htmlspecialchars($equipamento['marca']); ?>"
                             data-modelo="<?php echo htmlspecialchars($equipamento['modelo']); ?>"
                             data-centro-custo="<?php echo htmlspecialchars($equipamento['centro_custo']); ?>">
                            <div class="equipamento-checkbox-multiplo">
                                <input type="checkbox" name="equipamentos_selecionados[]" value="<?php echo $equipamento['id']; ?>" class="checkbox-equipamento" onchange="atualizarContador()">
                            </div>
                            <div class="equipamento-info-multiplo">
                                    <span class="equipamento-patrimonio">
                                        <i class="fas fa-barcode"></i> <?php echo htmlspecialchars($equipamento['patrimonio']); ?>
                                    </span>
                                <span class="equipamento-descricao">
                                        <?php echo htmlspecialchars($equipamento['marca'] . ' ' . $equipamento['modelo']); ?>
                                    </span>
                                <span class="equipamento-centro-custo">
                                        <i class="fas fa-dollar-sign"></i> <?php echo htmlspecialchars($equipamento['centro_custo']); ?>
                                    </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Contador de Selecionados -->
            <div class="selection-footer" style="margin-top: var(--spacing-md); text-align: right;">
                <span class="selected-count" id="contador-selecionados">0 equipamento(s) selecionado(s)</span>
            </div>

            <!-- Card de Aviso -->
            <div class="warning-card" style="margin-top: var(--spacing-lg);">
                <div class="warning-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h4>Atenção!</h4>
                </div>
                <p>Ao alocar estes equipamentos:</p>
                <ul>
                    <li>Todos os equipamentos selecionados sairão do estoque</li>
                    <li>O status será alterado para o tipo selecionado</li>
                    <li>O colaborador ficará responsável pelos equipamentos</li>
                    <li>Se a opção de atualizar centro de custo estiver marcada, TODOS os equipamentos terão o centro de custo atualizado</li>
                </ul>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="btn-alocar" disabled>
                    <i class="fas fa-user-plus"></i> Alocar Equipamentos Selecionados
                </button>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            </div>
        </form>
    </div>
</main>

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
            <div class="footer-stats">
                <div class="footer-stat"><span class="stat-number"><?php echo count($equipamentos); ?></span><span class="stat-label">Equipamentos</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo count($equipamentosDisponiveis); ?></span><span class="stat-label">Disponíveis</span></div>
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
    // Busca de Colaboradores
    const buscaColaborador = document.getElementById('busca-colaborador');
    const colaboradoresList = document.querySelectorAll('.colaborador-item-select');
    const colaboradorIdInput = document.getElementById('colaborador_id');
    const btnAlocar = document.getElementById('btn-alocar');
    const infoCentroCusto = document.getElementById('info-centro-custo');
    const ccDetalhes = document.getElementById('cc-detalhes');

    let colaboradorSelecionado = null;

    function filtrarColaboradores() {
        const termo = buscaColaborador.value.toLowerCase();

        colaboradoresList.forEach(item => {
            const nome = item.getAttribute('data-nome').toLowerCase();
            const cargo = item.getAttribute('data-cargo').toLowerCase();
            const departamento = item.getAttribute('data-departamento').toLowerCase();
            const centroCusto = item.getAttribute('data-centro-custo').toLowerCase();

            const matches = nome.includes(termo) || cargo.includes(termo) || departamento.includes(termo) || centroCusto.includes(termo);
            item.style.display = matches ? 'flex' : 'none';
        });
    }

    function selecionarColaborador(elemento) {
        colaboradoresList.forEach(item => item.classList.remove('selected'));
        elemento.classList.add('selected');

        const id = elemento.getAttribute('data-id');
        const nome = elemento.getAttribute('data-nome');
        const centroCusto = elemento.getAttribute('data-centro-custo');

        colaboradorSelecionado = { id, nome, centroCusto };
        colaboradorIdInput.value = id;

        atualizarInfoCentroCusto();
        atualizarContador();
    }

    colaboradoresList.forEach(item => {
        item.addEventListener('click', () => selecionarColaborador(item));
    });

    buscaColaborador.addEventListener('input', filtrarColaboradores);

    // Busca de Equipamentos
    const buscaEquipamento = document.getElementById('busca-equipamento');
    const equipamentosItens = document.querySelectorAll('.equipamento-item-multiplo');

    function filtrarEquipamentos() {
        const termo = buscaEquipamento.value.toLowerCase();

        equipamentosItens.forEach(item => {
            const patrimonio = item.getAttribute('data-patrimonio').toLowerCase();
            const marca = item.getAttribute('data-marca').toLowerCase();
            const modelo = item.getAttribute('data-modelo').toLowerCase();
            const centroCusto = item.getAttribute('data-centro-custo').toLowerCase();

            const matches = patrimonio.includes(termo) || marca.includes(termo) || modelo.includes(termo) || centroCusto.includes(termo);
            item.style.display = matches ? 'flex' : 'none';
        });
    }

    buscaEquipamento.addEventListener('input', filtrarEquipamentos);

    // Atualizar informações de centro de custo
    function atualizarInfoCentroCusto() {
        if (colaboradorSelecionado) {
            const equipamentosSelecionados = Array.from(document.querySelectorAll('.checkbox-equipamento:checked'));
            const centrosCustoEquipamentos = equipamentosSelecionados.map(cb => {
                const item = cb.closest('.equipamento-item-multiplo');
                return item ? item.getAttribute('data-centro-custo') : null;
            }).filter(cc => cc);

            const centrosUnicos = [...new Set(centrosCustoEquipamentos)];

            let html = `<strong>Colaborador selecionado:</strong> ${colaboradorSelecionado.nome}<br>`;
            html += `<strong>Centro de custo do colaborador:</strong> <span class="cc-novo-multiplo">${colaboradorSelecionado.centroCusto}</span><br>`;

            if (equipamentosSelecionados.length > 0) {
                html += `<strong>Equipamentos selecionados:</strong> ${equipamentosSelecionados.length}<br>`;
                html += `<strong>Centro(s) de custo dos equipamentos:</strong> ${centrosUnicos.join(', ') || '---'}<br>`;

                if (centrosUnicos.length === 1 && centrosUnicos[0] !== colaboradorSelecionado.centroCusto) {
                    html += `<span style="color: var(--warning);">⚠️ O centro de custo será alterado de ${centrosUnicos[0]} para ${colaboradorSelecionado.centroCusto}</span>`;
                } else if (centrosUnicos.length > 1) {
                    html += `<span style="color: var(--warning);">⚠️ Múltiplos centros de custo detectados. Eles serão padronizados para ${colaboradorSelecionado.centroCusto} se a opção estiver marcada.</span>`;
                } else if (centrosUnicos[0] === colaboradorSelecionado.centroCusto) {
                    html += `<span style="color: var(--success);">✓ Centro de custo já é o mesmo do colaborador.</span>`;
                }
            } else {
                html += `<span style="color: var(--gray-500);">Nenhum equipamento selecionado.</span>`;
            }

            ccDetalhes.innerHTML = html;
            infoCentroCusto.style.display = 'block';
        } else {
            infoCentroCusto.style.display = 'none';
        }
    }

    // Atualizar contador de selecionados
    function atualizarContador() {
        const checkboxes = document.querySelectorAll('.checkbox-equipamento');
        const selecionados = Array.from(checkboxes).filter(cb => cb.checked);
        const total = selecionados.length;
        const contadorSpan = document.getElementById('contador-selecionados');
        contadorSpan.innerHTML = `${total} equipamento(s) selecionado(s)`;

        btnAlocar.disabled = total === 0 || !colaboradorIdInput.value;

        document.querySelectorAll('.equipamento-item-multiplo').forEach(item => {
            const checkbox = item.querySelector('.checkbox-equipamento');
            if (checkbox && checkbox.checked) {
                item.classList.add('selected');
            } else {
                item.classList.remove('selected');
            }
        });

        atualizarInfoCentroCusto();
    }

    function selecionarTodos() {
        const checkboxes = document.querySelectorAll('.checkbox-equipamento');
        checkboxes.forEach(cb => {
            cb.checked = true;
            cb.closest('.equipamento-item-multiplo')?.classList.add('selected');
        });
        atualizarContador();
    }

    function deselecionarTodos() {
        const checkboxes = document.querySelectorAll('.checkbox-equipamento');
        checkboxes.forEach(cb => {
            cb.checked = false;
            cb.closest('.equipamento-item-multiplo')?.classList.remove('selected');
        });
        atualizarContador();
    }

    document.querySelectorAll('.equipamento-item-multiplo').forEach(item => {
        item.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
                const checkbox = this.querySelector('.checkbox-equipamento');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    atualizarContador();
                }
            }
        });
    });

    document.getElementById('form-alocar-multiplos').addEventListener('submit', function(e) {
        if (!colaboradorIdInput.value) {
            e.preventDefault();
            alert('Selecione um colaborador.');
        }
    });

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