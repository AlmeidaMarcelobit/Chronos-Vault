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
        $mensagem = "Chamado não encontrado. Preencha os dados abaixo para criar o colaborador.";
        $tipoMensagem = 'info';
    }
}

// Processar criação de novo colaborador via chamado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_colaborador'])) {
    $chamadoBusca = trim($_POST['chamado_busca']);
    $equipamentosSelecionados = $_POST['equipamentos_selecionados'] ?? [];
    $novo_nome = trim($_POST['novo_nome'] ?? '');
    $novo_cargo = trim($_POST['novo_cargo'] ?? '');
    $novo_departamento = trim($_POST['novo_departamento'] ?? '');
    $novo_centro_custo = trim($_POST['novo_centro_custo'] ?? '');
    $novo_cpf = trim($_POST['novo_cpf'] ?? '');
    $novo_email = trim($_POST['novo_email'] ?? '');

    // Verificar se já existe
    if (isset($colaboradoresPorMatricula[$chamadoBusca])) {
        $colaboradorEncontrado = $colaboradoresPorMatricula[$chamadoBusca];
        $mensagem = "Colaborador já existe: {$colaboradorEncontrado['nome']}";
        $tipoMensagem = 'warning';
    } else {
        // Criar novo colaborador com dados informados
        $novoColaborador = [
                'id' => gerarId($colaboradores),
                'matricula' => $chamadoBusca,
                'nome' => !empty($novo_nome) ? $novo_nome : 'Aguardando dados',
                'cargo' => !empty($novo_cargo) ? $novo_cargo : 'Não informado',
                'cpf' => $novo_cpf,
                'departamento' => !empty($novo_departamento) ? $novo_departamento : 'Não definido',
                'centro_custo' => !empty($novo_centro_custo) ? $novo_centro_custo : '0000',
                'email' => $novo_email ?: null,
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

// Processar alocação/atualização dos equipamentos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_alocacao'])) {
    $chamadoBusca = trim($_POST['chamado_busca']);
    $equipamentosSelecionados = $_POST['equipamentos_selecionados'] ?? [];
    $tipo_atribuicao = $_POST['tipo_atribuicao'] ?? 'alocado';
    $observacoes = trim($_POST['observacoes'] ?? '');
    $atualizar_centro_custo = isset($_POST['atualizar_centro_custo']) && $_POST['atualizar_centro_custo'] === 'sim';

    // Campos adicionais para atualizar colaborador
    $atualizar_nome = trim($_POST['atualizar_nome'] ?? '');
    $atualizar_cargo = trim($_POST['atualizar_cargo'] ?? '');
    $atualizar_departamento = trim($_POST['atualizar_departamento'] ?? '');
    $atualizar_centro_custo_colab = trim($_POST['atualizar_centro_custo_colab'] ?? '');
    $atualizar_cpf = trim($_POST['atualizar_cpf'] ?? '');
    $atualizar_email = trim($_POST['atualizar_email'] ?? '');

    // Buscar colaborador
    $colaboradorInfo = null;
    $colaboradorIndex = null;
    foreach ($colaboradores as $index => $colab) {
        if ($colab['matricula'] == $chamadoBusca) {
            $colaboradorInfo = $colab;
            $colaboradorIndex = $index;
            break;
        }
    }

    if (!$colaboradorInfo) {
        $mensagem = 'Colaborador não encontrado.';
        $tipoMensagem = 'error';
    } else {
        // Atualizar dados do colaborador se informados
        $colaboradorAtualizado = false;

        if (!empty($atualizar_nome) && $atualizar_nome !== $colaboradorInfo['nome']) {
            $colaboradores[$colaboradorIndex]['nome'] = $atualizar_nome;
            $colaboradorAtualizado = true;
        }
        if (!empty($atualizar_cargo) && $atualizar_cargo !== $colaboradorInfo['cargo']) {
            $colaboradores[$colaboradorIndex]['cargo'] = $atualizar_cargo;
            $colaboradorAtualizado = true;
        }
        if (!empty($atualizar_departamento) && $atualizar_departamento !== $colaboradorInfo['departamento']) {
            $colaboradores[$colaboradorIndex]['departamento'] = $atualizar_departamento;
            $colaboradorAtualizado = true;
        }
        if (!empty($atualizar_centro_custo_colab) && $atualizar_centro_custo_colab !== $colaboradorInfo['centro_custo']) {
            $colaboradores[$colaboradorIndex]['centro_custo'] = $atualizar_centro_custo_colab;
            $colaboradorAtualizado = true;
        }
        if (!empty($atualizar_cpf) && $atualizar_cpf !== ($colaboradorInfo['cpf'] ?? '')) {
            $colaboradores[$colaboradorIndex]['cpf'] = $atualizar_cpf;
            $colaboradorAtualizado = true;
        }
        if (!empty($atualizar_email) && $atualizar_email !== ($colaboradorInfo['email'] ?? '')) {
            $colaboradores[$colaboradorIndex]['email'] = $atualizar_email;
            $colaboradorAtualizado = true;
        }

        if ($colaboradorAtualizado) {
            $colaboradores[$colaboradorIndex]['data_atualizacao'] = date('Y-m-d H:i:s');
            salvarArquivoJSON('../data/colaboradores.json', $colaboradores);
            // Atualizar variável local
            $colaboradorInfo = $colaboradores[$colaboradorIndex];
        }

        $equipamentosAtualizados = 0;
        $centroCustoAlterados = [];

        // Atualizar equipamentos selecionados
        foreach ($equipamentos as $index => &$equip) {
            if (in_array($equip['id'], $equipamentosSelecionados) && $equip['status'] === 'estoque') {
                $centroCustoOriginal = $equip['centro_custo'];

                $equipamentos[$index]['colaborador_id'] = (int)$colaboradorInfo['id'];
                $equipamentos[$index]['status'] = $tipo_atribuicao;
                $equipamentos[$index]['data_atribuicao'] = date('Y-m-d H:i:s');
                $equipamentos[$index]['data_atualizacao'] = date('Y-m-d H:i:s');
                $equipamentos[$index]['tipo_atribuicao'] = $tipo_atribuicao === 'emprestado' ? 'emprestimo' : 'alocacao';

                // Atualizar centro de custo do equipamento
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

        // Mensagem de sucesso (mesmo sem equipamentos)
        $mensagemParts = [];
        if ($equipamentosAtualizados > 0) {
            $mensagemParts[] = "{$equipamentosAtualizados} equipamento(s) alocado(s) com sucesso para o chamado {$chamadoBusca}!";
        }
        if ($colaboradorAtualizado) {
            $mensagemParts[] = "Dados do colaborador atualizados!";
        }
        if (empty($equipamentosSelecionados) && !$colaboradorAtualizado) {
            $mensagemParts[] = "Chamado {$chamadoBusca} registrado/atualizado com sucesso!";
        }

        if (count($mensagemParts) > 0) {
            if (salvarArquivoJSON('../data/equipamentos.json', $equipamentos)) {
                $mensagemExtra = '';
                if ($atualizar_centro_custo && count($centroCustoAlterados) > 0) {
                    $mensagemExtra = " O centro de custo de " . count($centroCustoAlterados) . " equipamento(s) foi atualizado.";
                }
                $_SESSION['mensagem'] = implode(' ', $mensagemParts) . $mensagemExtra;
                $_SESSION['mensagem_tipo'] = 'success';

                header('Location: index.php');
                exit;
            } else {
                $mensagem = 'Erro ao salvar as alterações. Tente novamente.';
                $tipoMensagem = 'error';
            }
        } else {
            $mensagem = 'Nenhuma alteração foi realizada.';
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
    <link rel="stylesheet" href="../css/equipamentos/alocar_por_chamado.css">
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

<main class="main-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-qrcode"></i> Alocar por Chamado</h1>
            <p class="page-subtitle">Digite o número do chamado para alocar equipamentos ou cadastrar colaborador</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <?php if ($mensagem): ?>
        <div class="global-alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : ($tipoMensagem === 'warning' ? 'warning' : ($tipoMensagem === 'info' ? 'info' : 'error')); ?>">
            <div class="alert-content">
                <i class="fas fa-<?php echo $tipoMensagem === 'success' ? 'check-circle' : ($tipoMensagem === 'warning' ? 'exclamation-triangle' : ($tipoMensagem === 'info' ? 'info-circle' : 'exclamation-circle')); ?>"></i>
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
                    <?php if (!empty($colaboradorEncontrado['cpf'])): ?>
                        <p><strong>CPF:</strong> <?php echo formatarCPF($colaboradorEncontrado['cpf']); ?></p>
                    <?php endif; ?>
                </div>
            <?php elseif ($chamadoBusca && !$colaboradorEncontrado): ?>
                <div class="form-criar-colaborador">
                    <div class="warning-criar">
                        <p><i class="fas fa-info-circle"></i> <strong>Chamado não encontrado!</strong></p>
                        <p>Preencha os dados abaixo para cadastrar o colaborador com a matrícula "<?php echo htmlspecialchars($chamadoBusca); ?>"</p>
                    </div>

                    <div class="form-criar-grid">
                        <div class="form-group">
                            <label for="novo_nome"><i class="fas fa-user"></i> Nome Completo</label>
                            <input type="text" id="novo_nome" name="novo_nome" class="form-control" placeholder="Ex: João Silva">
                            <small class="form-text">Opcional - Pode ser atualizado depois</small>
                        </div>

                        <div class="form-group">
                            <label for="novo_cargo"><i class="fas fa-briefcase"></i> Cargo</label>
                            <input type="text" id="novo_cargo" name="novo_cargo" class="form-control" placeholder="Ex: Analista de Sistemas">
                            <small class="form-text">Opcional</small>
                        </div>

                        <div class="form-group">
                            <label for="novo_departamento"><i class="fas fa-building"></i> Departamento</label>
                            <input type="text" id="novo_departamento" name="novo_departamento" class="form-control" placeholder="Ex: TI">
                            <small class="form-text">Opcional</small>
                        </div>

                        <div class="form-group">
                            <label for="novo_centro_custo"><i class="fas fa-dollar-sign"></i> Centro de Custo</label>
                            <input type="text" id="novo_centro_custo" name="novo_centro_custo" class="form-control" placeholder="Ex: TI001">
                            <small class="form-text">Opcional</small>
                        </div>

                        <div class="form-group">
                            <label for="novo_cpf"><i class="fas fa-id-card"></i> CPF</label>
                            <input type="text" id="novo_cpf" name="novo_cpf" class="form-control cpf-mask" placeholder="000.000.000-00">
                            <small class="form-text">Opcional</small>
                        </div>

                        <div class="form-group">
                            <label for="novo_email"><i class="fas fa-envelope"></i> E-mail</label>
                            <input type="email" id="novo_email" name="novo_email" class="form-control" placeholder="exemplo@empresa.com">
                            <small class="form-text">Opcional</small>
                        </div>
                    </div>

                    <div class="form-actions-criar">
                        <button type="submit" name="criar_colaborador" class="btn btn-success">
                            <i class="fas fa-user-plus"></i> Criar Colaborador
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($colaboradorEncontrado): ?>
        <form method="POST" action="" id="form-alocar">
            <input type="hidden" name="chamado_busca" value="<?php echo htmlspecialchars($chamadoBusca); ?>">

            <!-- Campos para atualizar dados do colaborador -->
            <div class="form-card colaborador-update-card">
                <h3><i class="fas fa-edit"></i> Atualizar Dados do Colaborador (Opcional)</h3>
                <div class="form-grid-small">
                    <div class="form-group">
                        <label for="atualizar_nome"><i class="fas fa-user"></i> Nome</label>
                        <input type="text" id="atualizar_nome" name="atualizar_nome" class="form-control"
                               value="<?php echo htmlspecialchars($colaboradorEncontrado['nome'] ?? ''); ?>"
                               placeholder="Nome completo">
                    </div>

                    <div class="form-group">
                        <label for="atualizar_cargo"><i class="fas fa-briefcase"></i> Cargo</label>
                        <input type="text" id="atualizar_cargo" name="atualizar_cargo" class="form-control"
                               value="<?php echo htmlspecialchars($colaboradorEncontrado['cargo'] ?? ''); ?>"
                               placeholder="Cargo">
                    </div>

                    <div class="form-group">
                        <label for="atualizar_departamento"><i class="fas fa-building"></i> Departamento</label>
                        <input type="text" id="atualizar_departamento" name="atualizar_departamento" class="form-control"
                               value="<?php echo htmlspecialchars($colaboradorEncontrado['departamento'] ?? ''); ?>"
                               placeholder="Departamento">
                    </div>

                    <div class="form-group">
                        <label for="atualizar_centro_custo_colab"><i class="fas fa-dollar-sign"></i> Centro de Custo</label>
                        <input type="text" id="atualizar_centro_custo_colab" name="atualizar_centro_custo_colab" class="form-control"
                               value="<?php echo htmlspecialchars($colaboradorEncontrado['centro_custo'] ?? ''); ?>"
                               placeholder="Centro de Custo">
                    </div>

                    <div class="form-group">
                        <label for="atualizar_cpf"><i class="fas fa-id-card"></i> CPF</label>
                        <input type="text" id="atualizar_cpf" name="atualizar_cpf" class="form-control cpf-mask"
                               value="<?php echo htmlspecialchars($colaboradorEncontrado['cpf'] ?? ''); ?>"
                               placeholder="000.000.000-00">
                    </div>

                    <div class="form-group">
                        <label for="atualizar_email"><i class="fas fa-envelope"></i> E-mail</label>
                        <input type="email" id="atualizar_email" name="atualizar_email" class="form-control"
                               value="<?php echo htmlspecialchars($colaboradorEncontrado['email'] ?? ''); ?>"
                               placeholder="exemplo@empresa.com">
                    </div>
                </div>
                <small class="form-text text-muted"><i class="fas fa-info-circle"></i> Preencha apenas os campos que deseja atualizar</small>
            </div>

            <div class="form-group">
                <label for="tipo_atribuicao"><i class="fas fa-tag"></i> Tipo de Atribuição</label>
                <select name="tipo_atribuicao" id="tipo_atribuicao" class="form-select">
                    <option value="alocado">Alocar (permanente)</option>
                    <option value="emprestado">Emprestar (temporário)</option>
                </select>
            </div>

            <!-- Opção de atualizar centro de custo dos equipamentos -->
            <div class="info-centro-custo">
                <strong><i class="fas fa-dollar-sign"></i> Centro de Custo dos Equipamentos</strong>
                <div style="margin-top: var(--spacing-sm);">
                    <label class="checkbox-label">
                        <input type="checkbox" id="atualizar_centro_custo" name="atualizar_centro_custo" value="sim">
                        <span class="checkbox-custom"></span>
                        <span class="checkbox-text">
                                <strong>Atualizar centro de custo dos equipamentos selecionados</strong><br>
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
                <p>Ao confirmar esta operação:</p>
                <ul>
                    <li>Os dados do colaborador serão atualizados (se informados)</li>
                    <li>Os equipamentos selecionados serão atribuídos ao chamado <strong><?php echo htmlspecialchars($chamadoBusca); ?></strong></li>
                    <li>O status dos equipamentos será alterado</li>
                    <li>Se marcado, o centro de custo será atualizado</li>
                </ul>
            </div>

            <div class="form-actions">
                <button type="submit" name="confirmar_alocacao" class="btn btn-primary" id="btn-alocar">
                    <i class="fas fa-save"></i> Salvar / Alocar Equipamentos
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
    // Máscara para CPF
    function maskCPF(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        }
        input.value = value;
    }

    // Aplicar máscara de CPF
    document.querySelectorAll('.cpf-mask').forEach(input => {
        input.addEventListener('input', function() { maskCPF(this); });
    });

    const checkboxes = document.querySelectorAll('.checkbox-equipamento');
    const contadorSpan = document.getElementById('contador-selecionados');
    const buscaEquipamento = document.getElementById('busca-equipamento');

    function atualizarContador() {
        const selecionados = Array.from(checkboxes).filter(cb => cb.checked);
        const total = selecionados.length;
        if (contadorSpan) {
            contadorSpan.innerHTML = `${total} equipamento(s) selecionado(s)`;
        }

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
        if (!buscaEquipamento) return;
        const termo = buscaEquipamento.value.toLowerCase();
        const items = document.querySelectorAll('.equipamento-item');

        items.forEach(item => {
            const patrimonio = item.getAttribute('data-patrimonio')?.toLowerCase() || '';
            const marca = item.getAttribute('data-marca')?.toLowerCase() || '';
            const modelo = item.getAttribute('data-modelo')?.toLowerCase() || '';

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