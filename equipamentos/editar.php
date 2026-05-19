<?php
session_start();
require_once '../includes/funcoes.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$equipamentos = lerArquivoJSON('../data/equipamentos.json');
$colaboradores = lerArquivoJSON('../data/colaboradores.json');

// Verificar se os arrays foram carregados corretamente
if ($equipamentos === false) {
    $equipamentos = [];
}

if ($colaboradores === false) {
    $colaboradores = [];
}

// Encontrar equipamento
$equipamentoIndex = null;
foreach ($equipamentos as $index => $equip) {
    if ($equip['id'] == $id) {
        $equipamentoIndex = $index;
        $equipamento = $equip;
        break;
    }
}

if ($equipamentoIndex === null) {
    $_SESSION['mensagem'] = 'Equipamento não encontrado!';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

$mensagem = '';
$tipoMensagem = '';

// Processar edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $patrimonio = trim($_POST['patrimonio'] ?? '');
    $serial = trim($_POST['serial'] ?? '');
    $centro_custo = trim($_POST['centro_custo'] ?? '');
    $tipo = $_POST['tipo'] ?? 'notebook';
    $status = $_POST['status'] ?? 'estoque';
    $colaborador_id = $_POST['colaborador_id'] ?? null;
    $observacoes = trim($_POST['observacoes'] ?? '');
    $caixa = trim($_POST['caixa'] ?? '');
    $hostname = trim($_POST['hostname'] ?? '');

    // Validações
    $erros = [];

    if (empty($marca)) $erros[] = 'A marca é obrigatória.';
    if (empty($modelo)) $erros[] = 'O modelo é obrigatório.';
    if (empty($patrimonio)) $erros[] = 'O número de patrimônio é obrigatório.';
    if (empty($centro_custo)) $erros[] = 'O centro de custo é obrigatório.';

    // Validar hostname (obrigatório apenas para notebooks)
    if ($tipo === 'notebook' && empty($hostname)) {
        $erros[] = 'O hostname é obrigatório para equipamentos do tipo Notebook.';
    } elseif (!empty($hostname) && !preg_match('/^[a-zA-Z0-9\-_]+$/', $hostname)) {
        $erros[] = 'O hostname deve conter apenas letras, números, hífen ou underline.';
    }

    // Verificar se patrimônio já existe (exceto para o próprio equipamento)
    foreach ($equipamentos as $index => $equip) {
        if ($index != $equipamentoIndex && $equip['patrimonio'] === $patrimonio) {
            $erros[] = 'Este número de patrimônio já está cadastrado para outro equipamento.';
            break;
        }
        if (!empty($serial) && $index != $equipamentoIndex && isset($equip['serial']) && $equip['serial'] === $serial) {
            $erros[] = 'Este número de série já está cadastrado para outro equipamento.';
            break;
        }
    }

    // Verificar se precisa de colaborador (para alocado ou emprestado)
    if (($status === 'alocado' || $status === 'emprestado') && empty($colaborador_id)) {
        $erros[] = 'Selecione um colaborador para ' . ($status === 'emprestado' ? 'emprestar' : 'alocar') . ' o equipamento.';
    }

    // Verificar se equipamentos com status "fora de uso" ou "manutenção" estão alocados
    if (($status === 'fora_uso' || $status === 'manutencao') && !empty($colaborador_id)) {
        $erros[] = 'Equipamentos "' . getStatusTexto($status) . '" não podem estar alocados para colaboradores.';
        $colaborador_id = null;
    }

    if (empty($erros)) {
        // Registrar mudança de centro de custo
        $centroCustoAnterior = $equipamento['centro_custo'] ?? null;
        $centroCustoNovo = $centro_custo;

        // Inicializar histórico de centro de custo se não existir
        if (!isset($equipamento['historico_centro_custo']) || !is_array($equipamento['historico_centro_custo'])) {
            $equipamento['historico_centro_custo'] = [];
        }

        // Registrar alteração de centro de custo
        if ($centroCustoAnterior !== $centroCustoNovo && !empty($centroCustoAnterior)) {
            $historicoCC = [
                    'data' => date('Y-m-d H:i:s'),
                    'usuario' => $_SESSION['usuario_nome'] ?? 'Administrador',
                    'centro_custo_anterior' => $centroCustoAnterior,
                    'centro_custo_novo' => $centroCustoNovo,
                    'motivo' => trim($_POST['motivo_alteracao_cc'] ?? 'Alteração manual')
            ];
            $equipamento['historico_centro_custo'][] = $historicoCC;
        } elseif (empty($centroCustoAnterior) && !empty($centroCustoNovo)) {
            // Primeira atribuição de centro de custo
            $historicoCC = [
                    'data' => date('Y-m-d H:i:s'),
                    'usuario' => $_SESSION['usuario_nome'] ?? 'Administrador',
                    'centro_custo_anterior' => 'Não definido',
                    'centro_custo_novo' => $centroCustoNovo,
                    'motivo' => 'Cadastro inicial'
            ];
            $equipamento['historico_centro_custo'][] = $historicoCC;
        }

        // Registrar mudança de status se for diferente
        $statusAnterior = $equipamento['status'];
        $colaboradorAnterior = $equipamento['colaborador_id'] ?? null;

        // Atualizar equipamento
        $equipamentos[$equipamentoIndex]['marca'] = $marca;
        $equipamentos[$equipamentoIndex]['modelo'] = $modelo;
        $equipamentos[$equipamentoIndex]['patrimonio'] = $patrimonio;
        $equipamentos[$equipamentoIndex]['serial'] = $serial;
        $equipamentos[$equipamentoIndex]['tipo'] = $tipo;
        $equipamentos[$equipamentoIndex]['centro_custo'] = $centro_custo;
        $equipamentos[$equipamentoIndex]['status'] = $status;
        $equipamentos[$equipamentoIndex]['observacoes'] = $observacoes;
        $equipamentos[$equipamentoIndex]['caixa'] = $caixa;
        $equipamentos[$equipamentoIndex]['hostname'] = !empty($hostname) ? $hostname : null;
        $equipamentos[$equipamentoIndex]['data_atualizacao'] = date('Y-m-d H:i:s');

        // Manter o histórico atualizado
        if (isset($equipamento['historico_centro_custo'])) {
            $equipamentos[$equipamentoIndex]['historico_centro_custo'] = $equipamento['historico_centro_custo'];
        }

        // Atualizar dados de alocação
        if ($status === 'alocado' || $status === 'emprestado') {
            $equipamentos[$equipamentoIndex]['colaborador_id'] = (int)$colaborador_id;

            if (empty($equipamento['data_atribuicao']) || $colaboradorAnterior != $colaborador_id) {
                $equipamentos[$equipamentoIndex]['data_atribuicao'] = date('Y-m-d H:i:s');
            }

            $equipamentos[$equipamentoIndex]['tipo_atribuicao'] = $status === 'emprestado' ? 'emprestimo' : 'alocacao';
        } else {
            $equipamentos[$equipamentoIndex]['colaborador_id'] = null;
            $equipamentos[$equipamentoIndex]['data_atribuicao'] = null;

            if (isset($equipamentos[$equipamentoIndex]['data_devolucao_prevista'])) {
                unset($equipamentos[$equipamentoIndex]['data_devolucao_prevista']);
            }
            if (isset($equipamentos[$equipamentoIndex]['tipo_atribuicao'])) {
                unset($equipamentos[$equipamentoIndex]['tipo_atribuicao']);
            }
        }

        // Adicionar histórico de alterações se o status mudou
        if ($statusAnterior !== $status) {
            $historico = "\n\n[ALTERAÇÃO] " . date('d/m/Y H:i:s');
            $historico .= "\nStatus alterado: " . getStatusTexto($statusAnterior) . " → " . getStatusTexto($status);

            if ($colaboradorAnterior && !$colaborador_id) {
                $historico .= "\nRemovido do colaborador";
            } elseif (!$colaboradorAnterior && $colaborador_id) {
                $historico .= "\nAtribuído a novo colaborador";
            }

            $observacoesAtuais = $equipamentos[$equipamentoIndex]['observacoes'] ?? '';
            $equipamentos[$equipamentoIndex]['observacoes'] = $observacoesAtuais . $historico;
        }

        // Salvar no JSON
        if (salvarArquivoJSON('../data/equipamentos.json', $equipamentos)) {
            $mensagem = 'Equipamento atualizado com sucesso!';
            $tipoMensagem = 'success';
            $equipamento = $equipamentos[$equipamentoIndex];
        } else {
            $mensagem = 'Erro ao atualizar o equipamento. Tente novamente.';
            $tipoMensagem = 'error';
        }
    } else {
        $mensagem = implode('<br>', $erros);
        $tipoMensagem = 'error';
    }
}

// Buscar histórico de centro de custo
$historicoCentroCusto = $equipamento['historico_centro_custo'] ?? [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Equipamento - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/equipamentos/editar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../img/favicon/favicon.png">
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
                <a href="../colaboradores/index.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Colaboradores</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php" class="nav-link active">
                    <i class="fas fa-laptop"></i>
                    <span>Equipamentos</span>
                </a>
            </li>
        </ul>
    </nav>
</header>

<!-- Mensagens de alerta -->
<?php if ($mensagem): ?>
    <div class="global-alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : 'error'; ?>">
        <div class="alert-content">
            <i class="fas fa-<?php echo $tipoMensagem === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <span><?php echo $mensagem; ?></span>
        </div>
        <button class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
<?php endif; ?>

<!-- ==================== CONTEÚDO PRINCIPAL ==================== -->
<main class="main-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-edit"></i> Editar Equipamento</h1>
            <p class="page-subtitle">Atualize as informações do equipamento</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            <span>Voltar</span>
        </a>
    </div>

    <div class="form-card-container">
        <!-- Informações do Equipamento -->
        <div class="info-card equipment-info-card">
            <h3><i class="fas fa-info-circle"></i> Informações do Equipamento</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">ID:</span>
                    <span class="info-value"><?php echo $equipamento['id']; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Data de Cadastro:</span>
                    <span class="info-value"><?php echo formatarData($equipamento['data_cadastro']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Última Atualização:</span>
                    <span class="info-value"><?php echo isset($equipamento['data_atualizacao']) ? formatarData($equipamento['data_atualizacao']) : '---'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Centro de Custo Atual:</span>
                    <span class="info-value">
                            <span class="cc-badge">
                                <i class="fas fa-dollar-sign"></i>
                                <?php echo htmlspecialchars($equipamento['centro_custo']); ?>
                            </span>
                        </span>
                </div>
            </div>
        </div>

        <!-- Histórico de Centro de Custo -->
        <?php if (!empty($historicoCentroCusto)): ?>
            <div class="historico-cc-card">
                <h3><i class="fas fa-history"></i> Histórico de Alterações - Centro de Custo</h3>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Data</th>
                            <th>Usuário</th>
                            <th>Centro de Custo Anterior</th>
                            <th>Centro de Custo Novo</th>
                            <th>Motivo</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach (array_reverse($historicoCentroCusto) as $historico): ?>
                            <tr>
                                <td data-label="Data"><?php echo formatarData($historico['data']); ?></td>
                                <td data-label="Usuário"><?php echo htmlspecialchars($historico['usuario']); ?></td>
                                <td data-label="Centro de Custo Anterior">
                                    <span class="cc-badge cc-anterior">
                                        <i class="fas fa-arrow-left"></i>
                                        <?php echo htmlspecialchars($historico['centro_custo_anterior']); ?>
                                    </span>
                                </td>
                                <td data-label="Centro de Custo Novo">
                                    <span class="cc-badge cc-novo">
                                        <i class="fas fa-arrow-right"></i>
                                        <?php echo htmlspecialchars($historico['centro_custo_novo']); ?>
                                    </span>
                                </td>
                                <td data-label="Motivo"><?php echo htmlspecialchars($historico['motivo']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Formulário de Edição -->
        <form method="POST" action="" class="form-card" id="form-equipamento">
            <div class="form-grid">
                <div class="form-group">
                    <label for="tipo">
                        <i class="fas fa-tag"></i>
                        <span>Tipo de Equipamento</span>
                        <span class="required">*</span>
                    </label>
                    <select id="tipo" name="tipo" required class="form-select" onchange="toggleHostnameRequired()">
                        <option value="">-- Selecione o tipo --</option>
                        <?php foreach (getTiposEquipamentos() as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php echo $equipamento['tipo'] == $key ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="marca">
                        <i class="fas fa-industry"></i>
                        <span>Marca</span>
                        <span class="required">*</span>
                    </label>
                    <input type="text" id="marca" name="marca"
                           value="<?php echo htmlspecialchars($equipamento['marca']); ?>"
                           required class="form-control"
                           placeholder="Ex: Dell, HP, Lenovo">
                </div>

                <div class="form-group">
                    <label for="modelo">
                        <i class="fas fa-laptop"></i>
                        <span>Modelo</span>
                        <span class="required">*</span>
                    </label>
                    <input type="text" id="modelo" name="modelo"
                           value="<?php echo htmlspecialchars($equipamento['modelo']); ?>"
                           required class="form-control"
                           placeholder="Ex: Latitude 5420, iPhone 13">
                </div>

                <div class="form-group">
                    <label for="hostname">
                        <i class="fas fa-network-wired"></i>
                        <span>Hostname</span>
                        <span id="hostname-required" class="required" style="display: <?php echo $equipamento['tipo'] == 'notebook' ? 'inline' : 'none'; ?>;">*</span>
                    </label>
                    <input type="text" id="hostname" name="hostname"
                           value="<?php echo htmlspecialchars($equipamento['hostname'] ?? ''); ?>"
                           class="form-control"
                           placeholder="Ex: NOTEBOOK-001, PC-123">
                    <small class="form-text" id="hostname-help">
                        <?php if ($equipamento['tipo'] == 'notebook'): ?>
                            Obrigatório para equipamentos do tipo Notebook.
                        <?php else: ?>
                            Opcional - Apenas para identificação na rede.
                        <?php endif; ?>
                    </small>
                </div>

                <div class="form-group">
                    <label for="centro_custo">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Centro de Custo</span>
                        <span class="required">*</span>
                    </label>
                    <input type="text" id="centro_custo" name="centro_custo"
                           value="<?php echo htmlspecialchars($equipamento['centro_custo']); ?>"
                           required class="form-control cc-mask"
                           placeholder="Ex: TI001, ADM002">
                    <small class="form-text">Código do centro de custo (ex: TI001, ADM002)</small>
                </div>

                <!-- Motivo da alteração (só aparece se o centro de custo mudar) -->
                <div id="motivo-alteracao-group" class="form-group full-width" style="display: none;">
                    <label for="motivo_alteracao_cc">
                        <i class="fas fa-question-circle"></i>
                        <span>Motivo da Alteração do Centro de Custo</span>
                    </label>
                    <textarea id="motivo_alteracao_cc" name="motivo_alteracao_cc" class="form-control"
                              rows="2" placeholder="Informe o motivo da alteração do centro de custo (ex: Mudança de departamento, Reorganização, etc.)"></textarea>
                    <small class="form-text">Opcional - Informe o motivo para registrar no histórico</small>
                </div>

                <div class="form-group">
                    <label for="caixa">
                        <i class="fas fa-box"></i>
                        <span>Caixa</span>
                    </label>
                    <input type="text" id="caixa" name="caixa"
                           value="<?php echo htmlspecialchars($equipamento['caixa'] ?? ''); ?>"
                           class="form-control caixa-mask"
                           placeholder="Ex: 011, 025, etc.">
                    <small class="form-text">Código de identificação do caixa (opcional)</small>
                </div>

                <div class="form-group">
                    <label for="patrimonio">
                        <i class="fas fa-barcode"></i>
                        <span>Número de Patrimônio</span>
                        <span class="required">*</span>
                    </label>
                    <input type="text" id="patrimonio" name="patrimonio"
                           value="<?php echo htmlspecialchars($equipamento['patrimonio']); ?>"
                           required class="form-control"
                           placeholder="Ex: PAT001, TI-2023-001">
                </div>

                <div class="form-group">
                    <label for="serial">
                        <i class="fas fa-hashtag"></i>
                        <span>Número de Série</span>
                    </label>
                    <input type="text" id="serial" name="serial"
                           value="<?php echo htmlspecialchars($equipamento['serial'] ?? ''); ?>"
                           class="form-control"
                           placeholder="Ex: SN123456789">
                    <small class="form-text">Número de série do fabricante (opcional)</small>
                </div>
            </div>

            <!-- Status do Equipamento -->
            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Status do Equipamento *</label>
                <div class="status-options">
                    <label class="status-option">
                        <input type="radio" name="status" value="estoque"
                                <?php echo $equipamento['status'] == 'estoque' ? 'checked' : ''; ?>
                               onchange="toggleColaboradorSelect(false)">
                        <span class="status-dot status-dot-estoque"></span>
                        <span>Em Estoque</span>
                    </label>

                    <label class="status-option">
                        <input type="radio" name="status" value="alocado"
                                <?php echo $equipamento['status'] == 'alocado' ? 'checked' : ''; ?>
                               onchange="toggleColaboradorSelect(true)">
                        <span class="status-dot status-dot-alocado"></span>
                        <span>Alocado</span>
                    </label>

                    <label class="status-option">
                        <input type="radio" name="status" value="emprestado"
                                <?php echo $equipamento['status'] == 'emprestado' ? 'checked' : ''; ?>
                               onchange="toggleColaboradorSelect(true)">
                        <span class="status-dot status-dot-emprestado"></span>
                        <span>Emprestado</span>
                    </label>

                    <label class="status-option">
                        <input type="radio" name="status" value="manutencao"
                                <?php echo $equipamento['status'] == 'manutencao' ? 'checked' : ''; ?>
                               onchange="toggleColaboradorSelect(false)">
                        <span class="status-dot status-dot-manutencao"></span>
                        <span>Em Manutenção</span>
                    </label>

                    <label class="status-option">
                        <input type="radio" name="status" value="fora_uso"
                                <?php echo $equipamento['status'] == 'fora_uso' ? 'checked' : ''; ?>
                               onchange="toggleColaboradorSelect(false)">
                        <span class="status-dot status-dot-forauso"></span>
                        <span>Fora de Uso</span>
                    </label>
                </div>
            </div>

            <!-- Select Colaborador (dinâmico) -->
            <div class="form-group" id="colaborador-select"
                 style="display: <?php echo in_array($equipamento['status'], ['alocado', 'emprestado']) ? 'block' : 'none'; ?>;">
                <label for="colaborador_id">
                    <i class="fas fa-user"></i>
                    <span>Selecionar Colaborador</span>
                    <span class="required">*</span>
                </label>
                <select id="colaborador_id" name="colaborador_id" class="form-select"
                        <?php echo in_array($equipamento['status'], ['alocado', 'emprestado']) ? 'required' : ''; ?>>
                    <option value="">-- Selecione um colaborador --</option>
                    <?php if (empty($colaboradores)): ?>
                        <option value="" disabled>Nenhum colaborador cadastrado</option>
                    <?php else: ?>
                        <?php foreach ($colaboradores as $colaborador): ?>
                            <option value="<?php echo $colaborador['id']; ?>"
                                    <?php echo ($equipamento['colaborador_id'] ?? '') == $colaborador['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($colaborador['nome'] . ' - ' . $colaborador['departamento']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if (empty($colaboradores) && in_array($equipamento['status'], ['alocado', 'emprestado'])): ?>
                    <small class="form-text text-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Não há colaboradores cadastrados. <a href="../colaboradores/adicionar.php">Cadastre um colaborador primeiro</a>.
                    </small>
                <?php endif; ?>
            </div>

            <!-- Observações -->
            <div class="form-group">
                <label for="observacoes">
                    <i class="fas fa-sticky-note"></i>
                    <span>Observações</span>
                </label>
                <textarea id="observacoes" name="observacoes" class="form-control"
                          rows="3" placeholder="Observações, características especiais, problemas conhecidos..."><?php echo htmlspecialchars($equipamento['observacoes'] ?? ''); ?></textarea>
            </div>

            <!-- Card de Aviso -->
            <div class="warning-card">
                <div class="warning-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h4>Atenção!</h4>
                </div>
                <p>Ao alterar o status do equipamento:</p>
                <ul>
                    <li>Se mudar para "Em Estoque", "Em Manutenção" ou "Fora de Uso", o vínculo com o colaborador será removido</li>
                    <li>Se mudar para "Alocado" ou "Emprestado", será necessário selecionar um colaborador</li>
                    <li>O histórico da alteração será registrado nas observações</li>
                    <li>Esta ação pode afetar a disponibilidade do equipamento</li>
                </ul>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <span>Atualizar Equipamento</span>
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    <span>Cancelar</span>
                </a>
            </div>
        </form>
    </div>
</main>

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
                <li><a href="../colaboradores/index.php"><i class="fas fa-users"></i> Colaboradores</a></li>
                <li><a href="index.php"><i class="fas fa-laptop"></i> Equipamentos</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3>Estatísticas</h3>
            <?php
            $total_equipamentos = count(lerArquivoJSON('../data/equipamentos.json'));
            $equipamentos_data = lerArquivoJSON('../data/equipamentos.json');
            $equipamentos_estoque = 0;
            foreach ($equipamentos_data as $e) {
                if (($e['status'] ?? '') === 'estoque') $equipamentos_estoque++;
            }
            ?>
            <div class="footer-stats">
                <div class="footer-stat">
                    <span class="stat-number"><?php echo $total_equipamentos; ?></span>
                    <span class="stat-label">Equipamentos</span>
                </div>
                <div class="footer-stat">
                    <span class="stat-number"><?php echo $equipamentos_estoque; ?></span>
                    <span class="stat-label">Em Estoque</span>
                </div>
                <div class="footer-stat">
                    <span class="stat-number"><?php echo count($colaboradores); ?></span>
                    <span class="stat-label">Colaboradores</span>
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
    let centroCustoOriginal = document.getElementById('centro_custo').value;

    function toggleColaboradorSelect(show) {
        const selectDiv = document.getElementById('colaborador-select');
        const selectElement = document.getElementById('colaborador_id');

        if (show) {
            selectDiv.style.display = 'block';
            if (selectElement) {
                selectElement.required = true;
            }
        } else {
            selectDiv.style.display = 'none';
            if (selectElement) {
                selectElement.required = false;
                selectElement.value = '';
            }
        }
    }

    // Função para controlar obrigatoriedade do hostname baseado no tipo
    function toggleHostnameRequired() {
        const tipoSelect = document.getElementById('tipo');
        const hostnameInput = document.getElementById('hostname');
        const hostnameRequiredSpan = document.getElementById('hostname-required');
        const hostnameHelp = document.getElementById('hostname-help');

        if (tipoSelect.value === 'notebook') {
            hostnameInput.required = true;
            hostnameRequiredSpan.style.display = 'inline';
            hostnameHelp.innerHTML = '<strong>Obrigatório</strong> para equipamentos do tipo Notebook.';
            hostnameHelp.style.color = 'var(--danger)';
        } else {
            hostnameInput.required = false;
            hostnameRequiredSpan.style.display = 'none';
            hostnameHelp.innerHTML = 'Opcional - Apenas para identificação na rede.';
            hostnameHelp.style.color = 'var(--gray-500)';
        }
    }

    // Mostrar campo de motivo quando centro de custo mudar
    const centroCustoInput = document.getElementById('centro_custo');
    const motivoGroup = document.getElementById('motivo-alteracao-group');

    if (centroCustoInput) {
        centroCustoInput.addEventListener('input', function() {
            if (this.value !== centroCustoOriginal && centroCustoOriginal !== '') {
                motivoGroup.style.display = 'block';
            } else {
                motivoGroup.style.display = 'none';
            }
        });
    }

    // Auto-formatar centro de custo
    const ccInput = document.getElementById('centro_custo');
    if (ccInput) {
        ccInput.addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase();
            value = value.replace(/[^A-Z0-9]/g, '');
            e.target.value = value;
        });
    }

    // Auto-formatar caixa (só números)
    const caixaInput = document.getElementById('caixa');
    if (caixaInput) {
        caixaInput.addEventListener('input', function(e) {
            let value = e.target.value;
            value = value.replace(/[^0-9]/g, '');
            e.target.value = value;
        });
    }

    // Auto-formatar hostname (apenas letras, números, hífen, underline)
    const hostnameInput = document.getElementById('hostname');
    if (hostnameInput) {
        hostnameInput.addEventListener('input', function(e) {
            let value = e.target.value;
            value = value.replace(/[^a-zA-Z0-9\-_]/g, '');
            e.target.value = value;
        });
    }

    // Fechar alerta após 5 segundos
    setTimeout(function() {
        const alert = document.querySelector('.global-alert');
        if (alert) {
            alert.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }
    }, 5000);

    // Inicializar estado do colaborador select e hostname
    document.addEventListener('DOMContentLoaded', function() {
        const statusRadio = document.querySelector('input[name="status"]:checked');
        if (statusRadio) {
            const status = statusRadio.value;
            toggleColaboradorSelect(status === 'alocado' || status === 'emprestado');
        }

        // Salvar centro de custo original
        centroCustoOriginal = document.getElementById('centro_custo').value;

        // Inicializar obrigatoriedade do hostname
        toggleHostnameRequired();

        // Validação do formulário
        const form = document.getElementById('form-equipamento');
        if (form) {
            form.addEventListener('submit', function(e) {
                const patrimonio = document.getElementById('patrimonio').value.trim();
                const tipo = document.getElementById('tipo').value;
                const statusRadio = document.querySelector('input[name="status"]:checked');
                const hostname = document.getElementById('hostname').value.trim();

                if (!statusRadio) {
                    alert('Selecione o status do equipamento.');
                    e.preventDefault();
                    return false;
                }

                const status = statusRadio.value;
                const colaborador = document.getElementById('colaborador_id') ? document.getElementById('colaborador_id').value : '';

                if (patrimonio.length < 3) {
                    alert('O número de patrimônio deve ter pelo menos 3 caracteres.');
                    e.preventDefault();
                    return false;
                }

                if (tipo === '') {
                    alert('Selecione o tipo de equipamento.');
                    e.preventDefault();
                    return false;
                }

                // Validar hostname para notebooks
                if (tipo === 'notebook' && hostname === '') {
                    alert('O hostname é obrigatório para equipamentos do tipo Notebook.');
                    e.preventDefault();
                    return false;
                }

                if ((status === 'alocado' || status === 'emprestado') && colaborador === '') {
                    alert('Selecione um colaborador para ' + (status === 'emprestado' ? 'emprestar' : 'alocar') + ' o equipamento.');
                    e.preventDefault();
                    return false;
                }

                return true;
            });
        }
    });
</script>
</body>
</html>