<?php
session_start();
require_once "../includes/funcoes.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../login.php");
    exit();
}

$usuario_nivel = $_SESSION["usuario_nivel"] ?? "user";
$is_admin = $usuario_nivel === "admin";
$is_view = $usuario_nivel === "view";
$can_edit = $is_admin || $usuario_nivel === "user";

if ($is_view) {
    header("Location: index.php");
    exit();
}

$erro = "";
$sucesso = "";

$tiposEquipamento = getTiposEquipamentoSolicitacao();
$destinosReparo = getDestinosReparo();
$prioridades = getPrioridadesSolicitacao();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tipo_equipamento = trim($_POST["tipo_equipamento"] ?? "");
    $outro_especificar = trim($_POST["outro_especificar"] ?? "");
    $destino_reparo = trim($_POST["destino_reparo"] ?? "");
    $descricao_problema = trim($_POST["descricao_problema"] ?? "");
    $prioridade = trim($_POST["prioridade"] ?? "");
    $responsavel_envio = trim($_POST["responsavel_envio"] ?? "");
    $equipamento_relacionado = trim($_POST["equipamento_relacionado"] ?? "");
    $patrimonio = trim($_POST["patrimonio"] ?? "");

    if (empty($tipo_equipamento)) {
        $erro = "Selecione o tipo de equipamento.";
    } elseif ($tipo_equipamento === "outro" && empty($outro_especificar)) {
        $erro = "Especifique qual é o equipamento no campo 'Outro'.";
    } elseif (empty($destino_reparo)) {
        $erro = "Selecione o destino do reparo.";
    } elseif (empty($descricao_problema)) {
        $erro = "Descreva o problema do equipamento.";
    } elseif (empty($prioridade)) {
        $erro = "Selecione a prioridade.";
    } elseif (empty($responsavel_envio)) {
        $erro = "Informe o responsável pelo envio.";
    } else {
        $solicitacoes = carregarSolicitacoesManutencao();
        $novaSolicitacao = [
            "id" => gerarId($solicitacoes),
            "tipo_equipamento"    => $tipo_equipamento,
            "outro_especificar"   => ($tipo_equipamento === "outro") ? $outro_especificar : "",
            "equipamento_relacionado_id" => $equipamento_relacionado ?: null,
            "patrimonio"          => $patrimonio ?: null,
            "destino_reparo"      => $destino_reparo,
            "descricao_problema"  => $descricao_problema,
            "prioridade"          => $prioridade,
            "responsavel_envio"   => $responsavel_envio,
            "data_envio"          => date("Y-m-d H:i:s"),
            "status"              => "aguardando_envio",
            "historico_status"    => [
                [
                    "status" => "aguardando_envio",
                    "data"   => date("Y-m-d H:i:s"),
                    "usuario" => $_SESSION["usuario_nome"] ?? "Sistema"
                ]
            ],
            "usuario_cadastro_id"   => $_SESSION["usuario_id"] ?? null,
            "usuario_cadastro_nome" => $_SESSION["usuario_nome"] ?? "Sistema"
        ];

        $solicitacoes[] = $novaSolicitacao;

        $vinculoSucesso = true;
        $vinculoMensagem = "";

        if (salvarSolicitacoesManutencao($solicitacoes)) {
            if (!empty($equipamento_relacionado)) {
                $vinculo = vincularEquipamentoSolicitacaoManutencao(
                    $equipamento_relacionado,
                    $novaSolicitacao["id"],
                    "aguardando_envio"
                );
                if (!$vinculo) {
                    $vinculoSucesso = false;
                    $vinculoMensagem = " (Atenção: não foi possível atualizar o status do equipamento)";
                } else {
                    registrarLog("Atualização de Equipamento",
                        "Equipamento ID: {$equipamento_relacionado} | Movido para manutenção via Solicitação #{$novaSolicitacao['id']}");
                }
            }

            registrarLog("Criação de Solicitação de Manutenção",
                "ID: {$novaSolicitacao['id']} | Tipo: {$tipo_equipamento} | Prioridade: {$prioridade} | Destino: {$destino_reparo} | Equip. Rel: " . ($equipamento_relacionado ?: 'Nenhum'));
            $_SESSION["mensagem"] = "Solicitação de manutenção criada com sucesso! Protocolo: #{$novaSolicitacao['id']}{$vinculoMensagem}";
            $_SESSION["mensagem_tipo"] = $vinculoSucesso ? "success" : "error";
            header("Location: index.php");
            exit();
        } else {
            $erro = "Erro ao salvar solicitação. Tente novamente.";
        }
    }
}

$equipamentosDisponiveis = carregarTodosEquipamentos();
usort($equipamentosDisponiveis, function($a, $b) {
    return strcmp($a["patrimonio"] ?? "", $b["patrimonio"] ?? "");
});

$equipamento_id_url = trim($_GET["equipamento_id"] ?? "");
if ($equipamento_id_url && empty($_POST)) {
    $_POST["equipamento_relacionado"] = $equipamento_id_url;
    $eqBusca = buscarEquipamentoPorId($equipamento_id_url);
    if ($eqBusca) {
        $eq = $eqBusca["equipamento"];
        $_POST["patrimonio"] = $eq["patrimonio"] ?? "";
        $mapaTipoSolicitacao = [
            "notebook" => "notebook",
            "celular"  => "celular"
        ];
        if (isset($mapaTipoSolicitacao[$eq["tipo"] ?? ""])) {
            $_POST["tipo_equipamento"] = $mapaTipoSolicitacao[$eq["tipo"]];
        }
    }
}
?>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Solicitação de Manutenção - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/solicitacoes_manutencao/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/favicon/favicon.png">
</head>
<body>

<header class="header">
    <div class="header-content">
        <div class="logo">
            <a href="../index.php">
                <i class="fas fa-laptop-house"></i>
                <h1>Gestão de Equipamentos</h1>
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
            <li class="nav-item"><a href="index.php" class="nav-link active"><i class="fas fa-tools"></i><span>Solicitações Manutenção</span></a></li>
            <li class="nav-item"><a href="../linhas/index.php" class="nav-link"><i class="fas fa-phone"></i><span>Linhas</span></a></li>
            <?php if ($is_admin): ?>
                <li class="nav-item"><a href="../Termos/index.php" class="nav-link"><i class="fas fa-file-contract"></i><span>Termos</span></a></li>
                <li class="nav-item"><a href="../usuarios/index.php" class="nav-link"><i class="fas fa-user-cog"></i><span>Usuários</span></a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<main class="main-container">

    <?php if (!empty($_SESSION['mensagem'])): ?>
        <div class="global-alert <?php echo ($_SESSION['mensagem_tipo'] === 'success') ? 'global-alert-success' : 'global-alert-error'; ?>">
            <i class="fas fa-<?php echo ($_SESSION['mensagem_tipo'] === 'success') ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <span><?php echo htmlspecialchars($_SESSION['mensagem']); ?></span>
        </div>
        <?php unset($_SESSION['mensagem'], $_SESSION['mensagem_tipo']); ?>
    <?php endif; ?>

    <div class="page-header">
        <div class="page-title-section">
            <h1><i class="fas fa-plus-circle"></i> Nova Solicitação de Manutenção</h1>
            <p class="page-subtitle">Preencha os campos abaixo para registrar uma nova solicitação</p>
        </div>
        <div class="page-actions">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar para Lista</span>
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-file-medical"></i> Dados da Solicitação</h2>
            <p>Todos os campos marcados com <span class="required">*</span> são obrigatórios</p>
        </div>
        <div class="card-body">

            <?php if ($erro): ?>
                <div class="global-alert global-alert-error" style="margin-bottom: 1.5rem;">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($erro); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-grid">

                    <div class="form-group">
                        <label for="tipo_equipamento">
                            <i class="fas fa-laptop"></i> Equipamento <span class="required">*</span>
                        </label>
                        <select id="tipo_equipamento" name="tipo_equipamento" class="form-control" required onchange="atualizarCampos()">
                            <option value="">Selecione o tipo...</option>
                            <?php foreach ($tiposEquipamento as $chave => $nome): ?>
                                <option value="<?php echo $chave; ?>"
                                    <?php echo (($_POST['tipo_equipamento'] ?? '') === $chave) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nome); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group hidden-field" id="grupo_outro">
                        <label for="outro_especificar">
                            <i class="fas fa-ellipsis-h"></i> Especificar equipamento <span class="required">*</span>
                        </label>
                        <input type="text" id="outro_especificar" name="outro_especificar" class="form-control"
                            placeholder="Ex: Monitor Samsung, Teclado..."
                            value="<?php echo htmlspecialchars($_POST['outro_especificar'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="equipamento_relacionado">
                            <i class="fas fa-link"></i> Equipamento cadastrado (opcional)
                        </label>
                        <select id="equipamento_relacionado" name="equipamento_relacionado" class="form-control" onchange="preencherPatrimonio()">
                            <option value="">Nenhum (não cadastrado)</option>
                            <?php foreach ($equipamentosDisponiveis as $eq):
                                $infoExtra = [];
                                if (!empty($eq["marca"])) $infoExtra[] = $eq["marca"];
                                if (!empty($eq["modelo"])) $infoExtra[] = $eq["modelo"];
                                $infoText = $infoExtra ? " - " . implode(" ", $infoExtra) : "";
                            ?>
                                <option value="<?php echo $eq['id']; ?>"
                                    data-patrimonio="<?php echo htmlspecialchars($eq['patrimonio'] ?? ''); ?>"
                                    data-tipo="<?php echo htmlspecialchars($eq['tipo'] ?? ''); ?>"
                                    <?php echo (($_POST['equipamento_relacionado'] ?? '') == $eq['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars("Patrimônio: {$eq['patrimonio']}" . $infoText); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="help-text">Selecione se o equipamento já está cadastrado no sistema</span>
                    </div>

                    <div class="form-group">
                        <label for="patrimonio">
                            <i class="fas fa-barcode"></i> Patrimônio / Identificação
                        </label>
                        <input type="text" id="patrimonio" name="patrimonio" class="form-control"
                            placeholder="Número de patrimônio ou etiqueta"
                            value="<?php echo htmlspecialchars($_POST['patrimonio'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="destino_reparo">
                            <i class="fas fa-truck"></i> Destino do Reparo <span class="required">*</span>
                        </label>
                        <select id="destino_reparo" name="destino_reparo" class="form-control" required>
                            <option value="">Selecione o destino...</option>
                            <?php foreach ($destinosReparo as $chave => $d): ?>
                                <option value="<?php echo $chave; ?>"
                                    data-tipo-ideal="<?php
                                        if ($chave === 'claudio') echo 'notebook';
                                        elseif ($chave === 'diego') echo 'celular';
                                        else echo 'outro';
                                    ?>"
                                    <?php echo (($_POST['destino_reparo'] ?? '') === $chave) ? 'selected' : ''; ?>>
                                    <i class="fas fa-<?php echo $d['icone']; ?>"></i>
                                    <?php echo htmlspecialchars("{$d['nome']} ({$d['descricao']})"); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="help-text" id="sugestao_destino" style="display:none; color: var(--primary); font-weight:500;"></span>
                    </div>

                    <div class="form-group">
                        <label for="prioridade">
                            <i class="fas fa-flag"></i> Prioridade <span class="required">*</span>
                        </label>
                        <select id="prioridade" name="prioridade" class="form-control" required>
                            <option value="">Selecione a prioridade...</option>
                            <?php foreach ($prioridades as $chave => $p): ?>
                                <option value="<?php echo $chave; ?>"
                                    style="color: <?php echo $p['cor']; ?>; font-weight:600;"
                                    <?php echo (($_POST['prioridade'] ?? '') === $chave) ? 'selected' : ''; ?>>
                                    <?php
                                        $icone = $chave === 'urgente' ? '🔴' : ($chave === 'normal' ? '🟡' : '🟢');
                                        echo "{$icone} {$p['nome']}";
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="descricao_problema">
                            <i class="fas fa-comment-alt"></i> Descrição do Problema <span class="required">*</span>
                        </label>
                        <textarea id="descricao_problema" name="descricao_problema" class="form-control" rows="4" required
                            placeholder="Ex: Tela não liga, Bateria não carrega, Erro ao abrir sistema, Teclado com teclas falhando..."><?php echo htmlspecialchars($_POST['descricao_problema'] ?? ''); ?></textarea>
                        <span class="help-text">Descreva detalhadamente o defeito ou problema apresentado</span>
                    </div>

                    <div class="form-group">
                        <label for="responsavel_envio">
                            <i class="fas fa-user"></i> Responsável pelo Envio <span class="required">*</span>
                        </label>
                        <input type="text" id="responsavel_envio" name="responsavel_envio" class="form-control" required
                            placeholder="Nome de quem está enviando para manutenção"
                            value="<?php echo htmlspecialchars($_POST['responsavel_envio'] ?? ($_SESSION['usuario_nome'] ?? '')); ?>">
                    </div>

                    <div class="form-group">
                        <label>
                            <i class="fas fa-calendar-alt"></i> Data de Envio
                        </label>
                        <input type="text" class="form-control"
                            value="<?php echo date('d/m/Y H:i'); ?>" disabled>
                        <span class="help-text">Preenchido automaticamente</span>
                    </div>

                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-eraser"></i> Limpar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Registrar Solicitação
                    </button>
                </div>
            </form>

        </div>
    </div>

</main>

<script>
    function atualizarCampos() {
        const tipo = document.getElementById('tipo_equipamento').value;
        const grupoOutro = document.getElementById('grupo_outro');
        const outroInput = document.getElementById('outro_especificar');

        if (tipo === 'outro') {
            grupoOutro.classList.add('show');
            outroInput.setAttribute('required', 'required');
        } else {
            grupoOutro.classList.remove('show');
            outroInput.removeAttribute('required');
            outroInput.value = '';
        }

        sugerirDestino(tipo);
    }

    function sugerirDestino(tipoEquipamento) {
        const sugestaoEl = document.getElementById('sugestao_destino');
        const destinoSelect = document.getElementById('destino_reparo');

        if (!tipoEquipamento) {
            sugestaoEl.style.display = 'none';
            return;
        }

        const mapaDestino = {
            'notebook': 'claudio',
            'celular':  'diego',
            'outro':    'reparo_interno'
        };

        const destinoIdeal = mapaDestino[tipoEquipamento];
        if (destinoIdeal) {
            const opcao = destinoSelect.querySelector(`option[value="${destinoIdeal}"]`);
            if (opcao) {
                const nomes = {
                    'claudio': '🔧 Claudio (Notebook)',
                    'diego': '📱 Diego (Celular)',
                    'reparo_interno': '🏢 Reparo Interno'
                };
                sugestaoEl.textContent = '💡 Sugestão: ' + nomes[destinoIdeal];
                sugestaoEl.style.display = 'block';

                if (!destinoSelect.value) {
                    destinoSelect.value = destinoIdeal;
                }
            }
        }
    }

    function preencherPatrimonio() {
        const select = document.getElementById('equipamento_relacionado');
        const patrimonioInput = document.getElementById('patrimonio');
        const tipoSelect = document.getElementById('tipo_equipamento');

        const opcaoSelecionada = select.options[select.selectedIndex];

        if (select.value) {
            const patrimonio = opcaoSelecionada.dataset.patrimonio;
            const tipoEq = opcaoSelecionada.dataset.tipo;

            if (patrimonio && !patrimonioInput.value) {
                patrimonioInput.value = patrimonio;
            }

            if (tipoEq) {
                const mapaTipoSolicitacao = {
                    'notebook': 'notebook',
                    'celular': 'celular'
                };
                const tipoCorrespondente = mapaTipoSolicitacao[tipoEq];
                if (tipoCorrespondente && !tipoSelect.value) {
                    tipoSelect.value = tipoCorrespondente;
                    atualizarCampos();
                }
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        atualizarCampos();
        if (document.getElementById('equipamento_relacionado').value) {
            preencherPatrimonio();
        }
    });
</script>

</body>
</html>
