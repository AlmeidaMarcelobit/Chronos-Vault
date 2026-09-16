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
$is_view = ($usuario_nivel === 'view');
$can_edit = ($is_admin || $usuario_nivel === 'user');

if (!$can_edit) {
    $_SESSION['mensagem'] = 'Acesso negado. Apenas administradores e usuários podem acessar esta página.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

$linhas = lerArquivoJSON('../data/linhas.json');
$colaboradores = lerArquivoJSON('../data/colaboradores.json');

// Filtrar apenas linhas disponíveis
$linhasDisponiveis = array_filter($linhas, function($l) {
    return $l['status'] === 'disponivel';
});

// Ordenar linhas por número
usort($linhasDisponiveis, function($a, $b) {
    return strcmp($a['numero'], $b['numero']);
});

// Ordenar colaboradores por nome
usort($colaboradores, function($a, $b) {
    return strcmp($a['nome'], $b['nome']);
});

$mensagem = '';
$tipoMensagem = '';

// Processar salvamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {
    $alocacoes = json_decode($_POST['alocacoes'], true) ?? [];

    if (empty($alocacoes)) {
        $mensagem = 'Nenhuma alocação para salvar.';
        $tipoMensagem = 'warning';
    } else {
        $linhasAtualizadas = 0;
        $erros = [];

        foreach ($alocacoes as $alocacao) {
            $linhaId = $alocacao['linha_id'];
            $colaboradorId = $alocacao['colaborador_id'];

            // Buscar dados do colaborador
            $colaboradorInfo = null;
            foreach ($colaboradores as $colab) {
                if ($colab['id'] == $colaboradorId) {
                    $colaboradorInfo = $colab;
                    break;
                }
            }

            if (!$colaboradorInfo) {
                $erros[] = "Colaborador não encontrado para alocação.";
                continue;
            }

            // Atualizar linha
            foreach ($linhas as $index => &$linha) {
                if ($linha['id'] == $linhaId && $linha['status'] === 'disponivel') {
                    $centroCustoOriginal = $linha['centro_custo'];

                    $linhas[$index]['colaborador_id'] = (int)$colaboradorId;
                    $linhas[$index]['status'] = 'alocado';
                    $linhas[$index]['data_atualizacao'] = date('Y-m-d H:i:s');
                    $linhas[$index]['data_atribuicao'] = date('Y-m-d H:i:s');

                    // Atualizar centro de custo para o do colaborador
                    if (!isset($linha['historico_centro_custo']) || !is_array($linha['historico_centro_custo'])) {
                        $linhas[$index]['historico_centro_custo'] = [];
                    }

                    $historicoCC = [
                            'data' => date('Y-m-d H:i:s'),
                            'usuario' => $_SESSION['usuario_nome'] ?? 'Administrador',
                            'centro_custo_anterior' => $centroCustoOriginal,
                            'centro_custo_novo' => $colaboradorInfo['centro_custo'],
                            'motivo' => "Alocação sequencial - Linha vinculada a {$colaboradorInfo['nome']}"
                    ];
                    $linhas[$index]['historico_centro_custo'][] = $historicoCC;
                    $linhas[$index]['centro_custo'] = $colaboradorInfo['centro_custo'];

                    // Adicionar observação
                    $observacaoAtual = $linhas[$index]['observacoes'] ?? '';
                    $novaObservacao = "\n\n[ALOCAÇÃO SEQUENCIAL] " . date('d/m/Y H:i:s');
                    $novaObservacao .= "\nLinha vinculada a: " . $colaboradorInfo['nome'];
                    $linhas[$index]['observacoes'] = $observacaoAtual . $novaObservacao;

                    $linhasAtualizadas++;
                    break;
                }
            }
        }

        if (salvarArquivoJSON('../data/linhas.json', $linhas)) {
            $mensagem = "{$linhasAtualizadas} linha(s) alocada(s) com sucesso!";
            $tipoMensagem = 'success';
        } else {
            $mensagem = 'Erro ao salvar as alterações. Tente novamente.';
            $tipoMensagem = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alocar Linhas Sequencialmente - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/linhas/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../img/favicon/favicon.png">
    <style>
        .alocacao-container {
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--gray-200);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
        }

        .alocacao-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--gray-200);
        }

        .alocacao-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--grape);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .alocacao-lista {
            min-height: 200px;
        }

        .alocacao-item {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
            padding: var(--spacing-md);
            background: var(--gray-50);
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-md);
            transition: var(--transition);
        }

        .alocacao-item:hover {
            background: var(--gray-100);
        }

        .alocacao-header-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--spacing-sm);
        }

        .alocacao-number {
            width: 32px;
            height: 32px;
            background: var(--grape);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .btn-remover {
            background: none;
            border: none;
            color: var(--danger);
            cursor: pointer;
            font-size: 1.125rem;
            padding: var(--spacing-sm);
            transition: var(--transition);
            border-radius: var(--radius-md);
        }

        .btn-remover:hover {
            background: rgba(231, 76, 60, 0.1);
            transform: scale(1.1);
        }

        .alocacao-body {
            display: flex;
            flex-wrap: wrap;
            gap: var(--spacing-lg);
        }

        .alocacao-linha,
        .alocacao-colaborador {
            flex: 1;
            min-width: 280px;
        }

        .search-select {
            position: relative;
        }

        .search-select input {
            width: 100%;
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            margin-bottom: var(--spacing-xs);
        }

        .search-select input:focus {
            outline: none;
            border-color: var(--grape);
            box-shadow: 0 0 0 3px rgba(107, 62, 143, 0.1);
        }

        .options-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-md);
            background: var(--white);
            display: none;
        }

        .options-list.show {
            display: block;
        }

        .option-item {
            padding: var(--spacing-sm) var(--spacing-md);
            cursor: pointer;
            transition: var(--transition);
            border-bottom: 1px solid var(--gray-100);
        }

        .option-item:hover {
            background: var(--gray-50);
        }

        .option-item.selected {
            background: rgba(107, 62, 143, 0.1);
            color: var(--grape);
            font-weight: 500;
        }

        .option-info {
            font-size: 0.7rem;
            color: var(--gray-500);
            margin-top: var(--spacing-xs);
        }

        .alocacao-info {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            padding: var(--spacing-sm);
            background: var(--white);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
        }

        .info-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-xs);
            padding: 2px var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: 0.7rem;
        }

        .info-badge.warning {
            background: rgba(243, 156, 18, 0.1);
            color: var(--warning);
        }

        .info-badge.success {
            background: rgba(46, 204, 113, 0.1);
            color: var(--success);
        }

        .add-alocacao-btn {
            width: 100%;
            padding: var(--spacing-md);
            background: var(--gray-50);
            border: 2px dashed var(--gray-300);
            border-radius: var(--radius-md);
            color: var(--grape);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            margin-top: var(--spacing-md);
        }

        .add-alocacao-btn:hover {
            background: var(--gray-100);
            border-color: var(--grape);
        }

        .empty-alocacoes {
            text-align: center;
            padding: var(--spacing-2xl);
            color: var(--gray-500);
        }

        .empty-alocacoes i {
            font-size: 3rem;
            margin-bottom: var(--spacing-md);
        }

        .resumo-card {
            background: linear-gradient(135deg, var(--gray-50), var(--white));
            border-radius: var(--radius-lg);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
        }

        .resumo-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-sm) 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .resumo-item:last-child {
            border-bottom: none;
        }

        .resumo-label {
            font-weight: 600;
            color: var(--gray-600);
        }

        .resumo-value {
            font-weight: 700;
            color: var(--grape);
            font-size: 1.125rem;
        }

        .action-buttons {
            display: flex;
            gap: var(--spacing-md);
            justify-content: flex-end;
            margin-top: var(--spacing-xl);
            padding-top: var(--spacing-lg);
            border-top: 1px solid var(--gray-200);
        }

        @media (max-width: 768px) {
            .alocacao-body {
                flex-direction: column;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-buttons .btn {
                justify-content: center;
            }
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
            <li class="nav-item"><a href="../equipamentos/index.php" class="nav-link"><i class="fas fa-laptop"></i><span>Equipamentos</span></a></li>
            <li class="nav-item"><a href="index.php" class="nav-link active"><i class="fas fa-phone"></i><span>Linhas</span></a></li>
        </ul>
    </nav>
</header>

<!-- CONTEÚDO PRINCIPAL -->
<main class="main-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-layer-group"></i> Alocar Linhas Sequencialmente</h1>
            <p class="page-subtitle">Adicione pares de linha → colaborador e depois salve tudo de uma vez</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <?php if ($mensagem): ?>
        <div class="global-alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : ($tipoMensagem === 'warning' ? 'warning' : 'error'); ?>">
            <div class="alert-content">
                <i class="fas fa-<?php echo $tipoMensagem === 'success' ? 'check-circle' : ($tipoMensagem === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'); ?>"></i>
                <span><?php echo $mensagem; ?></span>
            </div>
            <button class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
        </div>
    <?php endif; ?>

    <div class="alocacao-container">
        <div class="alocacao-header">
            <h2><i class="fas fa-list-ol"></i> Lista de Alocações</h2>
            <span class="selected-count" id="contador-alocacoes">0 alocações</span>
        </div>

        <div id="alocacoes-lista" class="alocacao-lista">
            <div class="empty-alocacoes">
                <i class="fas fa-plus-circle"></i>
                <p>Clique no botão abaixo para adicionar uma alocação</p>
            </div>
        </div>

        <button type="button" class="add-alocacao-btn" onclick="adicionarAlocacao()">
            <i class="fas fa-plus"></i> Adicionar Nova Alocação
        </button>
    </div>

    <!-- Resumo -->
    <div class="resumo-card">
        <h3><i class="fas fa-chart-line"></i> Resumo das Alocações</h3>
        <div class="resumo-item">
            <span class="resumo-label">Total de Alocações:</span>
            <span class="resumo-value" id="total-alocacoes">0</span>
        </div>
        <div class="resumo-item">
            <span class="resumo-label">Linhas a serem alocadas:</span>
            <span class="resumo-value" id="total-linhas">0</span>
        </div>
        <div class="resumo-item">
            <span class="resumo-label">Colaboradores envolvidos:</span>
            <span class="resumo-value" id="total-colaboradores">0</span>
        </div>
    </div>

    <form method="POST" action="" id="form-alocar">
        <input type="hidden" name="alocacoes" id="alocacoes-input">
        <div class="action-buttons">
            <button type="submit" name="salvar" class="btn btn-primary" id="btn-salvar" disabled>
                <i class="fas fa-save"></i> Salvar Todas as Alocações
            </button>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
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
                <li><a href="../equipamentos/index.php"><i class="fas fa-laptop"></i> Equipamentos</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
        <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
</footer>

<script>
    // Dados
    const linhasDisponiveis = <?php
            $linhasArray = [];
            foreach ($linhasDisponiveis as $l) {
                $linhasArray[] = [
                        'id' => $l['id'],
                        'numero' => formatarTelefone($l['numero']),
                        'tipo' => getTipoLinhaTexto($l['tipo']),
                        'centro_custo' => $l['centro_custo']
                ];
            }
            echo json_encode($linhasArray);
            ?>;

    const colaboradoresLista = <?php
            $colabsArray = [];
            foreach ($colaboradores as $c) {
                $colabsArray[] = [
                        'id' => $c['id'],
                        'nome' => $c['nome'],
                        'cargo' => $c['cargo'],
                        'departamento' => $c['departamento'],
                        'centro_custo' => $c['centro_custo']
                ];
            }
            echo json_encode($colabsArray);
            ?>;

    let alocacoes = [];
    let nextId = 1;

    // Função para adicionar uma nova alocação
    function adicionarAlocacao(linhaId = null, colaboradorId = null) {
        const id = nextId++;

        alocacoes.push({
            id: id,
            linha_id: linhaId || '',
            colaborador_id: colaboradorId || '',
            linha_busca: '',
            colaborador_busca: ''
        });

        renderizarAlocacoes();
        atualizarResumo();
    }

    // Função para remover uma alocação
    function removerAlocacao(id) {
        alocacoes = alocacoes.filter(a => a.id !== id);
        renderizarAlocacoes();
        atualizarResumo();
    }

    // Função para atualizar linha de uma alocação
    function atualizarLinha(id, linhaId) {
        const alocacao = alocacoes.find(a => a.id === id);
        if (alocacao) {
            alocacao.linha_id = linhaId;
            renderizarAlocacoes();
            atualizarResumo();
        }
    }

    // Função para atualizar colaborador de uma alocação
    function atualizarColaborador(id, colaboradorId) {
        const alocacao = alocacoes.find(a => a.id === id);
        if (alocacao) {
            alocacao.colaborador_id = colaboradorId;
            renderizarAlocacoes();
            atualizarResumo();
        }
    }

    // Função para atualizar busca de linha
    function atualizarBuscaLinha(id, termo) {
        const alocacao = alocacoes.find(a => a.id === id);
        if (alocacao) {
            alocacao.linha_busca = termo;
            renderizarListaLinhas(id, termo);
        }
    }

    // Função para atualizar busca de colaborador
    function atualizarBuscaColaborador(id, termo) {
        const alocacao = alocacoes.find(a => a.id === id);
        if (alocacao) {
            alocacao.colaborador_busca = termo;
            renderizarListaColaboradores(id, termo);
        }
    }

    // Renderizar lista de linhas filtrada
    function renderizarListaLinhas(id, termo) {
        const container = document.getElementById(`linhas-list-${id}`);
        if (!container) return;

        const termoLower = termo.toLowerCase();
        const linhasFiltradas = linhasDisponiveis.filter(l =>
            l.numero.toLowerCase().includes(termoLower) ||
            l.centro_custo.toLowerCase().includes(termoLower)
        );

        const alocacao = alocacoes.find(a => a.id === id);

        if (linhasFiltradas.length === 0) {
            container.innerHTML = '<div class="option-item">Nenhuma linha encontrada</div>';
        } else {
            container.innerHTML = linhasFiltradas.map(l => `
                <div class="option-item ${alocacao.linha_id == l.id ? 'selected' : ''}" onclick="selecionarLinha(${id}, ${l.id})">
                    <strong>${l.numero}</strong> - ${l.tipo}
                    <div class="option-info"><i class="fas fa-dollar-sign"></i> CC: ${l.centro_custo}</div>
                </div>
            `).join('');
        }

        container.classList.add('show');
    }

    // Renderizar lista de colaboradores filtrada
    function renderizarListaColaboradores(id, termo) {
        const container = document.getElementById(`colaboradores-list-${id}`);
        if (!container) return;

        const termoLower = termo.toLowerCase();
        const colaboradoresFiltrados = colaboradoresLista.filter(c =>
            c.nome.toLowerCase().includes(termoLower) ||
            c.cargo.toLowerCase().includes(termoLower) ||
            c.departamento.toLowerCase().includes(termoLower) ||
            c.centro_custo.toLowerCase().includes(termoLower)
        );

        const alocacao = alocacoes.find(a => a.id === id);

        if (colaboradoresFiltrados.length === 0) {
            container.innerHTML = '<div class="option-item">Nenhum colaborador encontrado</div>';
        } else {
            container.innerHTML = colaboradoresFiltrados.map(c => `
                <div class="option-item ${alocacao.colaborador_id == c.id ? 'selected' : ''}" onclick="selecionarColaborador(${id}, ${c.id})">
                    <strong>${c.nome}</strong> - ${c.cargo}
                    <div class="option-info"><i class="fas fa-building"></i> ${c.departamento} | <i class="fas fa-dollar-sign"></i> CC: ${c.centro_custo}</div>
                </div>
            `).join('');
        }

        container.classList.add('show');
    }

    // Selecionar linha
    function selecionarLinha(id, linhaId) {
        const alocacao = alocacoes.find(a => a.id === id);
        if (alocacao) {
            alocacao.linha_id = linhaId;
            alocacao.linha_busca = '';
            renderizarAlocacoes();
            atualizarResumo();
        }
    }

    // Selecionar colaborador
    function selecionarColaborador(id, colaboradorId) {
        const alocacao = alocacoes.find(a => a.id === id);
        if (alocacao) {
            alocacao.colaborador_id = colaboradorId;
            alocacao.colaborador_busca = '';
            renderizarAlocacoes();
            atualizarResumo();
        }
    }

    // Fechar listas ao clicar fora
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-select')) {
            document.querySelectorAll('.options-list').forEach(list => {
                list.classList.remove('show');
            });
        }
    });

    // Obter dados da linha pelo ID
    function getLinhaById(id) {
        return linhasDisponiveis.find(l => l.id == id);
    }

    // Obter dados do colaborador pelo ID
    function getColaboradorById(id) {
        return colaboradoresLista.find(c => c.id == id);
    }

    // Renderizar todas as alocações
    function renderizarAlocacoes() {
        const container = document.getElementById('alocacoes-lista');

        if (alocacoes.length === 0) {
            container.innerHTML = `
                <div class="empty-alocacoes">
                    <i class="fas fa-plus-circle"></i>
                    <p>Clique no botão abaixo para adicionar uma alocação</p>
                </div>
            `;
            document.getElementById('btn-salvar').disabled = true;
            return;
        }

        document.getElementById('btn-salvar').disabled = false;

        let html = '';
        alocacoes.forEach((alocacao, index) => {
            const linha = getLinhaById(alocacao.linha_id);
            const colaborador = getColaboradorById(alocacao.colaborador_id);

            html += `
                <div class="alocacao-item" data-id="${alocacao.id}">
                    <div class="alocacao-header-item">
                        <div class="alocacao-number">${index + 1}</div>
                        <button type="button" class="btn-remover" onclick="removerAlocacao(${alocacao.id})" title="Remover">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                    <div class="alocacao-body">
                        <!-- Busca de Linha -->
                        <div class="alocacao-linha">
                            <div class="search-select">
                                <input type="text"
                                       placeholder="🔍 Buscar linha por número ou CC..."
                                       value="${alocacao.linha_busca || ''}"
                                       onfocus="renderizarListaLinhas(${alocacao.id}, this.value)"
                                       oninput="atualizarBuscaLinha(${alocacao.id}, this.value)">
                                <div class="options-list" id="linhas-list-${alocacao.id}"></div>
                            </div>
                            ${linha ? `
                                <div class="alocacao-info">
                                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                                    Linha selecionada: <strong>${linha.numero}</strong> (CC: ${linha.centro_custo})
                                </div>
                            ` : '<div class="alocacao-info" style="color: var(--warning);"><i class="fas fa-info-circle"></i> Nenhuma linha selecionada</div>'}
                        </div>

                        <!-- Busca de Colaborador -->
                        <div class="alocacao-colaborador">
                            <div class="search-select">
                                <input type="text"
                                       placeholder="🔍 Buscar colaborador por nome, cargo..."
                                       value="${alocacao.colaborador_busca || ''}"
                                       onfocus="renderizarListaColaboradores(${alocacao.id}, this.value)"
                                       oninput="atualizarBuscaColaborador(${alocacao.id}, this.value)">
                                <div class="options-list" id="colaboradores-list-${alocacao.id}"></div>
                            </div>
                            ${colaborador ? `
                                <div class="alocacao-info">
                                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                                    Colaborador: <strong>${colaborador.nome}</strong> (CC: ${colaborador.centro_custo})
                                </div>
                            ` : '<div class="alocacao-info" style="color: var(--warning);"><i class="fas fa-info-circle"></i> Nenhum colaborador selecionado</div>'}
                        </div>
                    </div>

                    ${linha && colaborador ? `
                        <div class="alocacao-info">
                            <i class="fas fa-exchange-alt"></i>
                            Centro de Custo: ${linha.centro_custo} → ${colaborador.centro_custo}
                            ${linha.centro_custo !== colaborador.centro_custo ?
                '<span class="info-badge warning"><i class="fas fa-exclamation-triangle"></i> Será alterado</span>' :
                '<span class="info-badge success"><i class="fas fa-check"></i> OK</span>'}
                        </div>
                    ` : ''}
                </div>
            `;
        });

        container.innerHTML = html;
        document.getElementById('contador-alocacoes').innerHTML = `${alocacoes.length} alocação(ões)`;
    }

    // Atualizar resumo
    function atualizarResumo() {
        const total = alocacoes.length;
        const linhasValidas = alocacoes.filter(a => a.linha_id).length;
        const colaboradoresUnicos = new Set(alocacoes.filter(a => a.colaborador_id).map(a => a.colaborador_id));

        document.getElementById('total-alocacoes').innerHTML = total;
        document.getElementById('total-linhas').innerHTML = linhasValidas;
        document.getElementById('total-colaboradores').innerHTML = colaboradoresUnicos.size;
    }

    // Preparar dados para envio
    function prepararDados() {
        const alocacoesValidas = alocacoes.filter(a => a.linha_id && a.colaborador_id);
        const dados = alocacoesValidas.map(a => ({
            linha_id: a.linha_id,
            colaborador_id: a.colaborador_id
        }));
        document.getElementById('alocacoes-input').value = JSON.stringify(dados);
        return dados.length > 0;
    }

    // Evento de submit do formulário
    document.getElementById('form-alocar').addEventListener('submit', function(e) {
        if (!prepararDados()) {
            e.preventDefault();
            alert('Adicione pelo menos uma alocação válida (linha e colaborador selecionados) para salvar.');
        }
    });

    // Adicionar uma alocação inicial
    adicionarAlocacao();
</script>
</body>
</html>