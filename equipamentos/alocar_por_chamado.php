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

$equipamentos = lerArquivoJSON('../data/equipamentos.json');
$colaboradores = lerArquivoJSON('../data/colaboradores.json');

// Criar mapa de colaboradores por matrícula (chamado)
$colaboradoresPorMatricula = [];
foreach ($colaboradores as $colab) {
    if (!empty($colab['matricula'])) {
        $colaboradoresPorMatricula[$colab['matricula']] = $colab;
    }
}

// Filtrar equipamentos disponíveis
$equipamentosDisponiveis = array_filter($equipamentos, function($e) {
    return $e['status'] === 'estoque';
});

$mensagem = '';
$tipoMensagem = '';
$chamadoBusca = '';
$colaboradorEncontrado = null;
$equipamentosSelecionados = [];

// Processar busca por chamado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_chamado'])) {
    $chamadoBusca = trim($_POST['chamado_busca']);
    $equipamentosSelecionados = $_POST['equipamentos_selecionados'] ?? [];

    if (isset($colaboradoresPorMatricula[$chamadoBusca])) {
        $colaboradorEncontrado = $colaboradoresPorMatricula[$chamadoBusca];
        $mensagem = "Colaborador encontrado: {$colaboradorEncontrado['nome']}";
        $tipoMensagem = 'success';
    } else {
        // Criar novo colaborador apenas com matrícula
        $mensagem = "Chamado não encontrado. Deseja criar um novo colaborador com esta matrícula?";
        $tipoMensagem = 'warning';
    }
}

// Processar criação de novo colaborador via chamado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_colaborador'])) {
    $chamadoBusca = trim($_POST['chamado_busca']);
    $equipamentosSelecionados = $_POST['equipamentos_selecionados'] ?? [];

    // Verificar se já existe
    if (isset($colaboradoresPorMatricula[$chamadoBusca])) {
        $colaboradorEncontrado = $colaboradoresPorMatricula[$chamadoBusca];
    } else {
        // Criar novo colaborador apenas com matrícula
        $novoColaborador = [
            'id' => gerarId($colaboradores),
            'matricula' => $chamadoBusca,
            'nome' => 'Aguardando dados',
            'cargo' => 'Não informado',
            'cpf' => '',
            'departamento' => 'Não definido',
            'centro_custo' => '0000',
            'email' => null,
            'tipo_trabalho' => 'local',
            'endereco' => null,
            'data_cadastro' => date('Y-m-d H:i:s'),
            'data_atualizacao' => date('Y-m-d H:i:s')
        ];

        $colaboradores[] = $novoColaborador;

        if (salvarArquivoJSON('../data/colaboradores.json', $colaboradores)) {
            $colaboradorEncontrado = $novoColaborador;
            $colaboradoresPorMatricula[$chamadoBusca] = $novoColaborador;
            $mensagem = "Colaborador criado com sucesso! Matrícula: {$chamadoBusca}";
            $tipoMensagem = 'success';
        } else {
            $mensagem = 'Erro ao criar colaborador. Tente novamente.';
            $tipoMensagem = 'error';
        }
    }
}

// Processar alocação dos equipamentos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_alocacao'])) {
    $chamadoBusca = trim($_POST['chamado_busca']);
    $equipamentosSelecionados = $_POST['equipamentos_selecionados'] ?? [];
    $tipo_atribuicao = $_POST['tipo_atribuicao'] ?? 'alocado';
    $observacoes = trim($_POST['observacoes'] ?? '');
    $atualizar_centro_custo = isset($_POST['atualizar_centro_custo']) && $_POST['atualizar_centro_custo'] === 'sim';

    // Buscar colaborador
    $colaboradorInfo = null;
    foreach ($colaboradores as $colab) {
        if ($colab['matricula'] == $chamadoBusca) {
            $colaboradorInfo = $colab;
            break;
        }
    }

    if (!$colaboradorInfo) {
        $mensagem = 'Colaborador não encontrado.';
        $tipoMensagem = 'error';
    } elseif (empty($equipamentosSelecionados)) {
        $mensagem = 'Selecione pelo menos um equipamento.';
        $tipoMensagem = 'error';
    } else {
        $equipamentosAtualizados = 0;
        $centroCustoAlterados = [];

        foreach ($equipamentos as $index => &$equip) {
            if (in_array($equip['id'], $equipamentosSelecionados) && $equip['status'] === 'estoque') {
                $centroCustoOriginal = $equip['centro_custo'];

                $equipamentos[$index]['colaborador_id'] = (int)$colaboradorInfo['id'];
                $equipamentos[$index]['status'] = $tipo_atribuicao;
                $equipamentos[$index]['data_atribuicao'] = date('Y-m-d H:i:s');
                $equipamentos[$index]['data_atualizacao'] = date('Y-m-d H:i:s');
                $equipamentos[$index]['tipo_atribuicao'] = $tipo_atribuicao === 'emprestado' ? 'emprestimo' : 'alocacao';

                // Atualizar centro de custo
                if ($atualizar_centro_custo && $colaboradorInfo['centro_custo']) {
                    if (!isset($equip['historico_centro_custo']) || !is_array($equip['historico_centro_custo'])) {
                        $equipamentos[$index]['historico_centro_custo'] = [];
                    }

                    $historicoCC = [
                        'data' => date('Y-m-d H:i:s'),
                        'usuario' => $_SESSION['usuario_nome'] ?? 'Administrador',
                        'centro_custo_anterior' => $centroCustoOriginal,
                        'centro_custo_novo' => $colaboradorInfo['centro_custo'],
                        'motivo' => "Alocação por chamado - Equipamento alocado para {$colaboradorInfo['nome']} (Matr: {$chamadoBusca})"
                    ];
                    $equipamentos[$index]['historico_centro_custo'][] = $historicoCC;

                    if ($centroCustoOriginal != $colaboradorInfo['centro_custo']) {
                        $equipamentos[$index]['centro_custo'] = $colaboradorInfo['centro_custo'];
                        $centroCustoAlterados[] = $equip['patrimonio'];
                    }
                }

                // Adicionar observação
                $observacaoAtual = $equipamentos[$index]['observacoes'] ?? '';
                $novaObservacao = "\n\n[ALOCAÇÃO POR CHAMADO] " . date('d/m/Y H:i:s');
                $novaObservacao .= "\nEquipamento vinculado ao chamado: {$chamadoBusca}";
                $novaObservacao .= "\nColaborador: {$colaboradorInfo['nome']}";
                if (!empty($observacoes)) {
                    $novaObservacao .= "\nObservações: " . $observacoes;
                }
                $equipamentos[$index]['observacoes'] = $observacaoAtual . $novaObservacao;

                $equipamentosAtualizados++;
            }
        }

        if ($equipamentosAtualizados > 0) {
            if (salvarArquivoJSON('../data/equipamentos.json', $equipamentos)) {
                $mensagemExtra = '';
                if ($atualizar_centro_custo && count($centroCustoAlterados) > 0) {
                    $mensagemExtra = " O centro de custo de " . count($centroCustoAlterados) . " equipamento(s) foi atualizado.";
                }
                $mensagem = "{$equipamentosAtualizados} equipamento(s) alocado(s) com sucesso para o chamado {$chamadoBusca}!{$mensagemExtra}";
                $tipoMensagem = 'success';

                // Limpar seleção
                $equipamentosSelecionados = [];
            } else {
                $mensagem = 'Erro ao salvar as alterações. Tente novamente.';
                $tipoMensagem = 'error';
            }
        } else {
            $mensagem = 'Nenhum equipamento disponível foi selecionado.';
            $tipoMensagem = 'warning';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alocar por Chamado - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/equipamentos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .chamado-section {
            background: linear-gradient(135deg, var(--grape-soft) 0%, var(--gray-50) 100%);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
            border: 1px solid var(--gray-200);
        }

        .chamado-busca {
            display: flex;
            gap: var(--spacing-md);
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .chamado-busca .form-group {
            flex: 2;
            margin-bottom: 0;
        }

        .chamado-info {
            margin-top: var(--spacing-lg);
            padding: var(--spacing-md);
            background: var(--white);
            border-radius: var(--radius-md);
            border-left: 4px solid var(--grape);
        }

        .chamado-info p {
            margin: var(--spacing-xs) 0;
        }

        .warning-criar {
            background: rgba(243, 156, 18, 0.1);
            border-left: 4px solid var(--warning);
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            margin-top: var(--spacing-md);
        }

        .equipamentos-lista {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            background: var(--white);
            margin-top: var(--spacing-md);
        }

        .equipamento-item {
            display: flex;
            align-items: center;
            padding: var(--spacing-sm) var(--spacing-md);
            border-bottom: 1px solid var(--gray-100);
            cursor: pointer;
            transition: var(--transition);
        }

        .equipamento-item:hover {
            background: var(--gray-50);
        }

        .equipamento-item.selected {
            background: rgba(107, 62, 143, 0.08);
            border-left: 3px solid var(--grape);
        }

        .equipamento-checkbox {
            margin-right: var(--spacing-md);
        }

        .equipamento-checkbox input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: var(--grape);
        }

        .equipamento-info {
            flex: 1;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: var(--spacing-md);
        }

        .equipamento-patrimonio {
            font-weight: 600;
            color: var(--grape);
            font-family: monospace;
            min-width: 100px;
        }

        .equipamento-descricao {
            color: var(--gray-700);
            flex: 1;
        }

        .equipamento-centro-custo {
            font-size: 0.7rem;
            color: var(--gray-500);
            background: var(--gray-100);
            padding: 2px var(--spacing-sm);
            border-radius: var(--radius-sm);
        }

        .busca-equipamento {
            margin-bottom: var(--spacing-md);
            position: relative;
        }

        .busca-equipamento i {
            position: absolute;
            left: var(--spacing-md);
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            pointer-events: none;
        }

        .busca-equipamento input {
            padding-left: 2.5rem;
        }

        .selection-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-lg);
            flex-wrap: wrap;
            gap: var(--spacing-md);
        }

        .selected-count {
            background: var(--grape);
            color: var(--white);
            padding: 2px var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
        }

        .info-centro-custo {
            background: rgba(107, 62, 143, 0.05);
            border-left: 3px solid var(--grape);
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-lg);
        }

        @media (max-width: 768px) {
            .chamado-busca {
                flex-direction: column;
            }

            .chamado-busca .btn {
                width: 100%;
                justify-content: center;
            }

            .equipamento-info {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-xs);
            }

            .selection-header {
                flex-direction: column;
                align-items: stretch;
            }
        }
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

<main class="main-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-qrcode"></i> Alocar por Chamado</h1>
            <p class="page-subtitle">Digite o número do chamado para alocar equipamentos rapidamente</p>
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

    <div class="chamado-section">
        <form method="POST" action="" id="form-busca">
            <div class="chamado-busca">
                <div class="form-group">
                    <label for="chamado_busca">
                        <i class="fas fa-id-card"></i> Número do Chamado
                    </label>
                    <input type="text"
                           id="chamado_busca"
                           name="chamado_busca"
                           class="form-control"
                           placeholder="Ex: #12345, MAT001, 123456"
                           value="<?php echo htmlspecialchars($chamadoBusca); ?>"
                           required
                           autofocus>
                    <small class="form-text">Digite o número do chamado (matrícula) do colaborador</small>
                </div>
                <button type="submit" name="buscar_chamado" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar Chamado
                </button>
            </div>

            <?php if ($colaboradorEncontrado): ?>
                <div class="chamado-info">
                    <p><strong><i class="fas fa-user-check"></i> Colaborador encontrado:</strong></p>
                    <p><strong>Nome:</strong> <?php echo htmlspecialchars($colaboradorEncontrado['nome']); ?></p>
                    <p><strong>Matrícula:</strong> <?php echo htmlspecialchars($colaboradorEncontrado['matricula']); ?></p>
                    <p><strong>Cargo:</strong> <?php echo htmlspecialchars($colaboradorEncontrado['cargo']); ?></p>
                    <p><strong>Departamento:</strong> <?php echo htmlspecialchars($colaboradorEncontrado['departamento']); ?></p>
                    <p><strong>Centro de Custo:</strong> <?php echo htmlspecialchars($colaboradorEncontrado['centro_custo']); ?></p>
                </div>
            <?php elseif ($chamadoBusca && !$colaboradorEncontrado): ?>
                <div class="warning-criar">
                    <p><i class="fas fa-exclamation-triangle"></i> <strong>Chamado não encontrado!</strong></p>
                    <p>O número "<?php echo htmlspecialchars($chamadoBusca); ?>" não está cadastrado no sistema.</p>
                    <form method="POST" action="" style="margin-top: var(--spacing-md);">
                        <input type="hidden" name="chamado_busca" value="<?php echo htmlspecialchars($chamadoBusca); ?>">
                        <input type="hidden" name="equipamentos_selecionados" value="<?php echo htmlspecialchars(json_encode($equipamentosSelecionados)); ?>">
                        <button type="submit" name="criar_colaborador" class="btn btn-warning">
                            <i class="fas fa-user-plus"></i> Criar Colaborador com este Chamado
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($colaboradorEncontrado || ($chamadoBusca && isset($_POST['criar_colaborador']))): ?>
        <form method="POST" action="" id="form-alocar">
            <input type="hidden" name="chamado_busca" value="<?php echo htmlspecialchars($chamadoBusca); ?>">

            <div class="form-group">
                <label for="tipo_atribuicao"><i class="fas fa-tag"></i> Tipo de Atribuição</label>
                <select name="tipo_atribuicao" id="tipo_atribuicao" class="form-select">
                    <option value="alocado">Alocar (permanente)</option>
                    <option value="emprestado">Emprestar (temporário)</option>
                </select>
            </div>

            <!-- Opção de atualizar centro de custo -->
            <div class="info-centro-custo">
                <strong><i class="fas fa-dollar-sign"></i> Centro de Custo</strong>
                <div style="margin-top: var(--spacing-sm);">
                    <label class="checkbox-label">
                        <input type="checkbox" id="atualizar_centro_custo" name="atualizar_centro_custo" value="sim">
                        <span class="checkbox-custom"></span>
                        <span class="checkbox-text">
                                <strong>Atualizar centro de custo dos equipamentos</strong><br>
                                <small>O centro de custo dos equipamentos será alterado para o centro de custo do colaborador (<?php echo htmlspecialchars($colaboradorEncontrado['centro_custo'] ?? '0000'); ?>).</small>
                            </span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="observacoes"><i class="fas fa-sticky-note"></i> Observações</label>
                <textarea id="observacoes" name="observacoes" class="form-control" rows="2" placeholder="Observações sobre esta alocação..."></textarea>
            </div>

            <!-- Equipamentos Disponíveis -->
            <div class="selection-header">
                <h3><i class="fas fa-laptop"></i> Equipamentos Disponíveis (Estoque)</h3>
                <div>
                    <button type="button" class="select-all-btn" onclick="selecionarTodos()">
                        <i class="fas fa-check-double"></i> Selecionar Todos
                    </button>
                    <button type="button" class="select-all-btn" onclick="deselecionarTodos()">
                        <i class="fas fa-times"></i> Desmarcar Todos
                    </button>
                </div>
            </div>

            <div class="busca-equipamento">
                <i class="fas fa-search"></i>
                <input type="text" id="busca-equipamento" class="form-control" placeholder="Buscar equipamento por patrimônio, marca ou modelo...">
            </div>

            <div class="equipamentos-lista" id="equipamentos-lista">
                <?php if (empty($equipamentosDisponiveis)): ?>
                    <div class="empty-state" style="padding: var(--spacing-2xl); text-align: center;">
                        <i class="fas fa-warehouse"></i>
                        <p>Nenhum equipamento disponível em estoque.</p>
                        <a href="adicionar.php" class="btn btn-primary">Adicionar Equipamento</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($equipamentosDisponiveis as $equipamento): ?>
                        <div class="equipamento-item" data-id="<?php echo $equipamento['id']; ?>"
                             data-patrimonio="<?php echo htmlspecialchars($equipamento['patrimonio']); ?>"
                             data-marca="<?php echo htmlspecialchars($equipamento['marca']); ?>"
                             data-modelo="<?php echo htmlspecialchars($equipamento['modelo']); ?>"
                             data-centro-custo="<?php echo htmlspecialchars($equipamento['centro_custo']); ?>">
                            <div class="equipamento-checkbox">
                                <input type="checkbox" name="equipamentos_selecionados[]" value="<?php echo $equipamento['id']; ?>" class="checkbox-equipamento" onchange="atualizarContador()">
                            </div>
                            <div class="equipamento-info">
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

            <div class="selection-footer" style="margin-top: var(--spacing-md); text-align: right;">
                <span class="selected-count" id="contador-selecionados">0 equipamento(s) selecionado(s)</span>
            </div>

            <div class="warning-card" style="margin-top: var(--spacing-lg);">
                <div class="warning-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h4>Confirmação</h4>
                </div>
                <p>Ao confirmar esta alocação:</p>
                <ul>
                    <li>Os equipamentos selecionados serão atribuídos ao chamado <strong><?php echo htmlspecialchars($chamadoBusca); ?></strong></li>
                    <li>O status dos equipamentos será alterado</li>
                    <li>Se marcado, o centro de custo será atualizado</li>
                </ul>
            </div>

            <div class="form-actions">
                <button type="submit" name="confirmar_alocacao" class="btn btn-primary" id="btn-alocar" disabled>
                    <i class="fas fa-user-plus"></i> Alocar Equipamentos Selecionados
                </button>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            </div>
        </form>
    <?php endif; ?>
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
                <div class="footer-stat">
                    <span class="stat-number"><?php echo count($equipamentosDisponiveis); ?></span>
                    <span class="stat-label">Equip. Disp.</span>
                </div>
                <div class="footer-stat">
                    <span class="stat-number"><?php echo count($equipamentos); ?></span>
                    <span class="stat-label">Total Equip.</span>
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
    const checkboxes = document.querySelectorAll('.checkbox-equipamento');
    const btnAlocar = document.getElementById('btn-alocar');
    const contadorSpan = document.getElementById('contador-selecionados');
    const buscaEquipamento = document.getElementById('busca-equipamento');

    function atualizarContador() {
        const selecionados = Array.from(checkboxes).filter(cb => cb.checked);
        const total = selecionados.length;
        contadorSpan.innerHTML = `${total} equipamento(s) selecionado(s)`;
        btnAlocar.disabled = total === 0;

        // Atualizar estilo dos itens selecionados
        document.querySelectorAll('.equipamento-item').forEach(item => {
            const checkbox = item.querySelector('.checkbox-equipamento');
            if (checkbox && checkbox.checked) {
                item.classList.add('selected');
            } else {
                item.classList.remove('selected');
            }
        });
    }

    function selecionarTodos() {
        checkboxes.forEach(cb => {
            cb.checked = true;
            cb.closest('.equipamento-item')?.classList.add('selected');
        });
        atualizarContador();
    }

    function deselecionarTodos() {
        checkboxes.forEach(cb => {
            cb.checked = false;
            cb.closest('.equipamento-item')?.classList.remove('selected');
        });
        atualizarContador();
    }

    // Busca de equipamentos
    function filtrarEquipamentos() {
        const termo = buscaEquipamento.value.toLowerCase();
        const items = document.querySelectorAll('.equipamento-item');

        items.forEach(item => {
            const patrimonio = item.getAttribute('data-patrimonio').toLowerCase();
            const marca = item.getAttribute('data-marca').toLowerCase();
            const modelo = item.getAttribute('data-modelo').toLowerCase();

            const matches = patrimonio.includes(termo) || marca.includes(termo) || modelo.includes(termo);
            item.style.display = matches ? 'flex' : 'none';
        });
    }

    if (buscaEquipamento) {
        buscaEquipamento.addEventListener('input', filtrarEquipamentos);
    }

    // Clique nos itens para marcar/desmarcar
    document.querySelectorAll('.equipamento-item').forEach(item => {
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

    // Inicializar
    atualizarContador();

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