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

$linhas = lerArquivoJSON('../data/linhas.json');
$colaboradores = lerArquivoJSON('../data/colaboradores/ativos.json');

// Encontrar linha
$linhaIndex = null;
foreach ($linhas as $index => $linha) {
    if ($linha['id'] == $id) {
        $linhaIndex = $index;
        $linhaAtual = $linha;
        break;
    }
}

if ($linhaIndex === null) {
    header('Location: index.php');
    exit;
}

// Verificar se a linha já está alocada
if ($linhaAtual['status'] !== 'disponivel') {
    $_SESSION['mensagem'] = 'Esta linha já está alocada para um colaborador.';
    $_SESSION['mensagem_tipo'] = 'warning';
    header('Location: index.php');
    exit;
}

$mensagem = '';
$tipoMensagem = '';

// Processar vinculação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $colaborador_id = $_POST['colaborador_id'] ?? null;

    if (empty($colaborador_id)) {
        $mensagem = 'Selecione um colaborador para vincular a linha.';
        $tipoMensagem = 'error';
    } else {
        // Buscar dados do colaborador
        $colaboradorSelecionado = null;
        foreach ($colaboradores as $colab) {
            if ($colab['id'] == $colaborador_id) {
                $colaboradorSelecionado = $colab;
                break;
            }
        }

        if (!$colaboradorSelecionado) {
            $mensagem = 'Colaborador não encontrado.';
            $tipoMensagem = 'error';
        } else {
            // Registrar centro de custo anterior
            $centroCustoAnterior = $linhaAtual['centro_custo'] ?? 'Não definido';
            $centroCustoNovo = $colaboradorSelecionado['centro_custo'];

            // Atualizar linha
            $linhas[$linhaIndex]['colaborador_id'] = $colaborador_id;
            $linhas[$linhaIndex]['status'] = 'alocado';
            $linhas[$linhaIndex]['data_atualizacao'] = date('Y-m-d H:i:s');
            $linhas[$linhaIndex]['data_atribuicao'] = date('Y-m-d H:i:s');

            // ATUALIZAR CENTRO DE CUSTO AUTOMATICAMENTE
            if (!isset($linhas[$linhaIndex]['historico_centro_custo']) || !is_array($linhas[$linhaIndex]['historico_centro_custo'])) {
                $linhas[$linhaIndex]['historico_centro_custo'] = [];
            }

            $historicoCC = [
                    'data' => date('Y-m-d H:i:s'),
                    'usuario' => $_SESSION['usuario_nome'] ?? 'Administrador',
                    'centro_custo_anterior' => $centroCustoAnterior,
                    'centro_custo_novo' => $centroCustoNovo,
                    'motivo' => 'Vinculação automática ao colaborador ' . $colaboradorSelecionado['nome']
            ];
            $linhas[$linhaIndex]['historico_centro_custo'][] = $historicoCC;
            $linhas[$linhaIndex]['centro_custo'] = $centroCustoNovo;

            // Adicionar observação
            $observacaoAtual = $linhas[$linhaIndex]['observacoes'] ?? '';
            $novaObservacao = "\n\n[VINCULAÇÃO] " . date('d/m/Y H:i:s');
            $novaObservacao .= "\nLinha vinculada ao colaborador: " . $colaboradorSelecionado['nome'];
            $novaObservacao .= "\nCentro de custo atualizado de {$centroCustoAnterior} para {$centroCustoNovo}";
            $linhas[$linhaIndex]['observacoes'] = $observacaoAtual . $novaObservacao;

            if (salvarArquivoJSON('../data/linhas.json', $linhas)) {
                $mensagem = "Linha vinculada com sucesso! Centro de custo atualizado de {$centroCustoAnterior} para {$centroCustoNovo}.";
                $tipoMensagem = 'success';
                header('Location: index.php');
                exit;
            } else {
                $mensagem = 'Erro ao vincular a linha. Tente novamente.';
                $tipoMensagem = 'error';
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
    <title>Vincular Linha - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/linhas/vincular.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../img/favicon/favicon.png">
</head>
<body>
<header class="header">
    <div class="header-content">
        <div class="logo">
            <a href="../index.php">
                <i class="fas fa-laptop-house"></i>
                <h1>Gestão de Linhas</h1>
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
            <?php if (($_SESSION['usuario_nivel'] ?? '') === 'admin'): ?>
                <li class="nav-item"><a href="../Termos/index.php" class="nav-link"><i class="fas fa-file-contract"></i><span>Termos</span></a></li>
                <li class="nav-item"><a href="../usuarios/index.php" class="nav-link"><i class="fas fa-user-cog"></i><span>Usuários</span></a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<?php if ($mensagem): ?>
    <div class="global-alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : 'error'; ?>">
        <div class="alert-content">
            <i class="fas fa-<?php echo $tipoMensagem === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <span><?php echo $mensagem; ?></span>
        </div>
        <button class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
<?php endif; ?>

<main class="main-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-user-plus"></i> Vincular Linha</h1>
            <p class="page-subtitle">Atribua esta linha a um colaborador</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="form-card-container">
        <!-- Informações da Linha -->
        <div class="info-card">
            <h3><i class="fas fa-phone"></i> Linha a ser vinculada</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Número:</span>
                    <span class="info-value numero-link"><?php echo formatarTelefone($linhaAtual['numero']); ?></span>
                </div>
                <div class="info-item">
    <span class="info-label">IMEI:</span>
    <span class="info-value"><?php echo !empty($linhaAtual['imei']) ? htmlspecialchars($linhaAtual['imei']) : '---'; ?></span>
</div>
                <div class="info-item">
                    <span class="info-label">Tipo:</span>
                    <span class="info-value"><?php echo getTipoLinhaTexto($linhaAtual['tipo']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Centro de Custo Atual:</span>
                    <span class="info-value">
                            <span class="cc-badge" id="cc-atual">
                                <?php echo htmlspecialchars($linhaAtual['centro_custo']); ?>
                            </span>
                        </span>
                </div>
            </div>
        </div>

        <!-- Formulário de Vinculação -->
        <form method="POST" action="" id="form-vincular">
            <div class="form-group">
                <label><i class="fas fa-users"></i> Selecionar Colaborador <span class="required">*</span></label>

                <!-- Campo de busca -->
                <div class="search-colaborador">
                    <i class="fas fa-search"></i>
                    <input type="text" id="busca-colaborador" class="form-control" placeholder="Buscar por nome, cargo ou centro de custo..." autocomplete="off">
                </div>

                <!-- Lista de colaboradores -->
                <div class="colaboradores-list" id="lista-colaboradores">
                    <?php if (empty($colaboradores)): ?>
                        <div class="sem-resultados">
                            <i class="fas fa-users-slash"></i>
                            <p>Nenhum colaborador cadastrado</p>
                            <a href="../colaboradores/adicionar.php" class="btn btn-primary btn-sm">Cadastrar colaborador</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($colaboradores as $colaborador): ?>
                            <div class="colaborador-item" data-id="<?php echo $colaborador['id']; ?>"
                                 data-nome="<?php echo htmlspecialchars($colaborador['nome']); ?>"
                                 data-cargo="<?php echo htmlspecialchars($colaborador['cargo']); ?>"
                                 data-departamento="<?php echo htmlspecialchars($colaborador['departamento']); ?>"
                                 data-centro-custo="<?php echo htmlspecialchars($colaborador['centro_custo']); ?>">
                                <div class="colaborador-info">
                                    <div class="colaborador-nome"><?php echo htmlspecialchars($colaborador['nome']); ?></div>
                                    <div class="colaborador-detalhes">
                                        <span><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($colaborador['cargo']); ?></span>
                                        <span><i class="fas fa-building"></i> <?php echo htmlspecialchars($colaborador['departamento']); ?></span>
                                        <span><i class="fas fa-dollar-sign"></i> <?php echo htmlspecialchars($colaborador['centro_custo']); ?></span>
                                    </div>
                                </div>
                                <div class="radio-custom"></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <input type="hidden" id="colaborador_id" name="colaborador_id" required>
            </div>

            <!-- Informação de Centro de Custo -->
            <div id="info-centro-custo" class="info-centro-custo" style="display: none;">
                <strong><i class="fas fa-sync-alt"></i> O que vai acontecer:</strong>
                <div style="margin-top: var(--spacing-sm);">
                    Centro de custo da linha: <span id="cc-linha" class="cc-anterior"><?php echo htmlspecialchars($linhaAtual['centro_custo']); ?></span><br>
                    Centro de custo do colaborador: <span id="cc-colaborador-selecionado" class="cc-novo">---</span><br>
                    <span id="cc-mensagem" style="font-size: 0.875rem; margin-top: var(--spacing-xs); display: inline-block;"></span>
                </div>
            </div>

            <div class="warning-card" style="margin-top: var(--spacing-lg);">
                <div class="warning-header">
                    <i class="fas fa-info-circle"></i>
                    <h4>Confirmação</h4>
                </div>
                <p>Ao vincular esta linha:</p>
                <ul>
                    <li>O status será alterado para <strong>"Alocado"</strong></li>
                    <li>O colaborador ficará responsável pela linha</li>
                    <li>O centro de custo será <strong>atualizado automaticamente</strong> para o centro de custo do colaborador</li>
                    <li>O histórico da alteração será registrado</li>
                </ul>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="btn-vincular" disabled>
                    <i class="fas fa-check"></i> Confirmar Vinculação
                </button>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            </div>
        </form>
    </div>
</main>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3><i class="fas fa-laptop-house"></i>Gestão de Linhas</h3>
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
    const colaboradoresList = document.querySelectorAll('.colaborador-item');
    const buscaInput = document.getElementById('busca-colaborador');
    const colaboradorIdInput = document.getElementById('colaborador_id');
    const btnVincular = document.getElementById('btn-vincular');
    const infoCentroCusto = document.getElementById('info-centro-custo');
    const ccColaboradorSpan = document.getElementById('cc-colaborador-selecionado');
    const ccLinhaSpan = document.getElementById('cc-linha');
    const ccMensagemSpan = document.getElementById('cc-mensagem');

    const centroCustoLinha = '<?php echo $linhaAtual['centro_custo']; ?>';

    let colaboradorSelecionado = null;

    // Função para filtrar colaboradores
    function filtrarColaboradores() {
        const termo = buscaInput.value.toLowerCase();

        colaboradoresList.forEach(item => {
            const nome = item.getAttribute('data-nome').toLowerCase();
            const cargo = item.getAttribute('data-cargo').toLowerCase();
            const departamento = item.getAttribute('data-departamento').toLowerCase();
            const centroCusto = item.getAttribute('data-centro-custo').toLowerCase();

            const matches = nome.includes(termo) ||
                cargo.includes(termo) ||
                departamento.includes(termo) ||
                centroCusto.includes(termo);

            item.style.display = matches ? 'flex' : 'none';
        });
    }

    // Função para selecionar colaborador
    function selecionarColaborador(elemento) {
        // Remover seleção de todos
        colaboradoresList.forEach(item => {
            item.classList.remove('selected');
        });

        // Adicionar seleção ao clicado
        elemento.classList.add('selected');

        // Obter dados
        const id = elemento.getAttribute('data-id');
        const nome = elemento.getAttribute('data-nome');
        const centroCusto = elemento.getAttribute('data-centro-custo');

        colaboradorSelecionado = { id, nome, centroCusto };
        colaboradorIdInput.value = id;

        // Atualizar informações de centro de custo
        ccColaboradorSpan.innerHTML = centroCusto;
        infoCentroCusto.style.display = 'block';

        if (centroCusto !== centroCustoLinha) {
            ccColaboradorSpan.style.color = 'var(--warning)';
            ccLinhaSpan.style.color = 'var(--danger)';
            ccMensagemSpan.innerHTML = '⚠️ O centro de custo será alterado automaticamente!';
            ccMensagemSpan.style.color = 'var(--warning)';
        } else {
            ccColaboradorSpan.style.color = 'var(--success)';
            ccLinhaSpan.style.color = 'var(--success)';
            ccMensagemSpan.innerHTML = '✓ Centro de custo já é o mesmo do colaborador.';
            ccMensagemSpan.style.color = 'var(--success)';
        }

        // Habilitar botão
        btnVincular.disabled = false;
    }

    // Evento de clique nos colaboradores
    colaboradoresList.forEach(item => {
        item.addEventListener('click', () => selecionarColaborador(item));
    });

    // Evento de busca
    buscaInput.addEventListener('input', filtrarColaboradores);

    // Prevenir submit sem seleção
    document.getElementById('form-vincular').addEventListener('submit', function(e) {
        if (!colaboradorIdInput.value) {
            e.preventDefault();
            alert('Selecione um colaborador para vincular a linha.');
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