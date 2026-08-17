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
    $responsavel_envio = trim($_SESSION["usuario_nome"] ?? "Sistema");
    $equipamento_relacionado = trim($_POST["equipamento_relacionado"] ?? "");
    $patrimonio = trim($_POST["patrimonio"] ?? "");
    $busca_patrimonio = trim($_POST["busca_patrimonio"] ?? "");

    if (empty($equipamento_relacionado) && ($busca_patrimonio || $patrimonio)) {
        $todos = carregarTodosEquipamentos();
        $procurarPat = $busca_patrimonio ?: $patrimonio;
        foreach ($todos as $eq) {
            $pEq = trim($eq["patrimonio"] ?? "");
            if ($pEq !== "" && strcasecmp($pEq, $procurarPat) === 0) {
                $equipamento_relacionado = (string)($eq["id"] ?? "");
                if ($patrimonio === "") $patrimonio = $pEq;
                break;
            }
        }
    }

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
        $erro = "Não foi possível identificar o responsável pelo envio.";
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

$equipamentosDisponiveis = array_values(array_filter(carregarTodosEquipamentos(), function($eq) {
    return isset($eq["id"]) && !empty($eq["id"]);
}));
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
        $eqTipo = $eq["tipo"] ?? "";
        if (isset($mapaTipoSolicitacao[$eqTipo])) {
            $_POST["tipo_equipamento"] = $mapaTipoSolicitacao[$eqTipo];
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
                        <label for="busca_patrimonio">
                            <i class="fas fa-barcode"></i> Buscar por Patrimônio
                        </label>
                        <div style="position:relative;">
                            <input type="text" id="busca_patrimonio" name="busca_patrimonio" class="form-control" autocomplete="off"
                                placeholder="Digite o nº do patrimônio (ex: 1527) e pressione Enter ou selecione..."
                                value="<?php echo htmlspecialchars($_POST['patrimonio'] ?? ''); ?>">
                            <div id="sugestoes_patrimonio" style="display:none; position:absolute; top:100%; left:0; right:0; background:#fff; border:1px solid var(--gray-200); border-radius:0 0 12px 12px; z-index:9999; max-height:260px; overflow-y:auto; box-shadow: 0 6px 16px rgba(0,0,0,0.08);"></div>
                        </div>
                        <input type="hidden" id="equipamento_relacionado" name="equipamento_relacionado" value="<?php echo htmlspecialchars($_POST['equipamento_relacionado'] ?? ''); ?>">
                        <div id="dados_equipamento_encontrado" style="display:none; margin-top:0.5rem; padding:0.75rem 1rem; background:rgba(33,150,243,0.08); border-left:3px solid var(--primary); border-radius:8px; font-size:0.9rem; color:var(--gray-700);">
                            <strong style="color:var(--primary);"><i class="fas fa-info-circle"></i> Equipamento encontrado:</strong>
                            <div id="dados_equipamento_texto" style="margin-top:0.35rem; line-height:1.5;"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="patrimonio">
                            <i class="fas fa-tag"></i> Patrimônio / Identificação (confirmar)
                        </label>
                        <input type="text" id="patrimonio" name="patrimonio" class="form-control"
                            placeholder="(preenchido automaticamente pela busca)"
                            value="<?php echo htmlspecialchars($_POST['patrimonio'] ?? ''); ?>" style="font-weight:600; color:var(--primary); background:rgba(33,150,243,0.03);">
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
                            <i class="fas fa-user"></i> Responsável pelo Envio
                        </label>
                        <div style="position:relative;">
                            <input type="text" id="responsavel_envio" name="responsavel_envio" class="form-control" required readonly
                                style="background:rgba(40,167,69,0.05); color:#28a745; font-weight:600; cursor:not-allowed;"
                                value="<?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? ''); ?>">
                            <i class="fas fa-lock" style="position:absolute; right: 0.9rem; top: 50%; transform: translateY(-50%); color:#28a745; opacity:0.85;"></i>
                        </div>
                        <span class="help-text" style="color:#28a745;"><i class="fas fa-shield-alt"></i> Preenchido automaticamente com o usuário logado (<?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? ''); ?>).</span>
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
    const _listaEquipamentos = <?php
        $export = [];
        foreach ($equipamentosDisponiveis as $eq) {
            $id = (string)($eq["id"] ?? "");
            if ($id === "") continue;
            $export[] = [
                "id" => $id,
                "patrimonio" => trim($eq["patrimonio"] ?? ""),
                "tipo" => $eq["tipo"] ?? "",
                "marca" => $eq["marca"] ?? "",
                "modelo" => $eq["modelo"] ?? "",
                "serial" => $eq["serial"] ?? "",
                "hostname" => $eq["hostname"] ?? "",
                "status" => $eq["status"] ?? "",
                "colaborador_nome" => $eq["colaborador_nome"] ?? ""
            ];
        }
        echo json_encode($export);
    ?>;

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

    function preencherCamposEquipamento(eq) {
        if (!eq) return;
        const tipoSelect = document.getElementById('tipo_equipamento');
        const patrimonioInput = document.getElementById('patrimonio');
        const eqRelHidden = document.getElementById('equipamento_relacionado');
        const outroInput = document.getElementById('outro_especificar');
        const encontradosBox = document.getElementById('dados_equipamento_encontrado');
        const encontradosTexto = document.getElementById('dados_equipamento_texto');

        eqRelHidden.value = String(eq.id || "");

        if (eq.patrimonio) {
            if (!patrimonioInput.value) {
                patrimonioInput.value = eq.patrimonio;
            } else if (patrimonioInput.value.trim() !== eq.patrimonio.trim()) {
                patrimonioInput.value = eq.patrimonio;
            }
        }

        const mapaTipoSolicitacao = {
            'notebook': 'notebook',
            'celular':  'celular'
        };
        const tipoEq = (eq.tipo || "").toLowerCase();
        if (mapaTipoSolicitacao[tipoEq]) {
            tipoSelect.value = mapaTipoSolicitacao[tipoEq];
            outroInput.value = '';
        } else if (tipoEq) {
            tipoSelect.value = 'outro';
            const texto = [eq.marca, eq.modelo].filter(Boolean).join(" ") || tipoEq;
            outroInput.value = texto.charAt(0).toUpperCase() + texto.slice(1);
        }
        atualizarCampos();

        const partesInfo = [];
        if (eq.tipo) partesInfo.push("<strong>Tipo:</strong> " + eq.tipo);
        if (eq.marca || eq.modelo) partesInfo.push("<strong>Modelo:</strong> " + [eq.marca, eq.modelo].filter(Boolean).join(" "));
        if (eq.hostname) partesInfo.push("<strong>Hostname:</strong> " + eq.hostname);
        if (eq.serial) partesInfo.push("<strong>Serial:</strong> " + eq.serial);
        if (eq.status) {
            const statusNome = {
                'estoque':'Estoque',
                'alocado':'Alocado',
                'emprestado':'Emprestado',
                'manutencao':'Em Manutenção',
                'fora_uso':'Fora de Uso'
            }[eq.status] || eq.status;
            partesInfo.push("<strong>Situação atual:</strong> " + statusNome);
        }
        if (eq.colaborador_nome) partesInfo.push("<strong>Colaborador:</strong> " + eq.colaborador_nome);
        encontradosTexto.innerHTML = partesInfo.join(" • ");
        encontradosBox.style.display = "block";
    }

    function buscarSugestoesPatrimonio(texto) {
        const caixa = document.getElementById('sugestoes_patrimonio');
        const t = (texto || "").trim().toLowerCase();
        if (!t) {
            caixa.innerHTML = '';
            caixa.style.display = 'none';
            return;
        }
        let lista = _listaEquipamentos.filter(e => {
            if (!e.patrimonio) return false;
            return String(e.patrimonio).toLowerCase().includes(t);
        });
        lista.sort((a,b) => {
            const ap = String(a.patrimonio).toLowerCase();
            const bp = String(b.patrimonio).toLowerCase();
            const aComeca = ap.startsWith(t) ? 0 : 1;
            const bComeca = bp.startsWith(t) ? 0 : 1;
            if (aComeca !== bComeca) return aComeca - bComeca;
            return ap.localeCompare(bp);
        });
        lista = lista.slice(0, 8);

        if (lista.length === 0) {
            caixa.innerHTML = `<div style="padding:1rem; color:#6c757d; text-align:center; font-size:0.9rem;"><i class="fas fa-search"></i> Nenhum equipamento encontrado com este patrimônio.</div>`;
            caixa.style.display = 'block';
            return;
        }

        caixa.innerHTML = lista.map(e => {
            const linha1 = `<strong style="color:var(--primary);">Patrimônio: ${e.patrimonio || "(sem)"}</strong>`;
            const linha2Partes = [];
            if (e.tipo) linha2Partes.push(e.tipo);
            if (e.marca || e.modelo) linha2Partes.push([e.marca, e.modelo].filter(Boolean).join(" "));
            if (e.colaborador_nome) linha2Partes.push("👤 " + e.colaborador_nome);
            const linha2 = linha2Partes.length ? `<small style="color:#6c757d;">${linha2Partes.join(" • ")}</small>` : "";
            const statusCor = {
                'estoque':'#28a745',
                'alocado':'#007BFF',
                'emprestado':'#9b59b6',
                'manutencao':'#F39C12',
                'fora_uso':'#dc3545'
            }[e.status] || '#6c757d';
            const statusNome = {
                'estoque':'Estoque',
                'alocado':'Alocado',
                'emprestado':'Emprestado',
                'manutencao':'Manutenção',
                'fora_uso':'Fora de Uso'
            }[e.status] || (e.status || "");
            return `<div class="sugestao-item"
                        data-id='${JSON.stringify(e).replace(/'/g, "&#39;")}'
                        style="padding:0.75rem 1rem; cursor:pointer; border-bottom:1px solid var(--gray-100); display:flex; justify-content:space-between; align-items:center; gap:0.5rem;"
                        onmouseover="this.style.background='var(--gray-50)';"
                        onmouseout="this.style.background='#fff';">
                    <div style="flex:1;">
                        <div style="margin-bottom:0.15rem;">${linha1}</div>
                        ${linha2}
                    </div>
                    <span style="font-size:0.75rem; padding:0.25rem 0.5rem; border-radius:999px; background:${statusCor}15; color:${statusCor}; font-weight:600; white-space:nowrap;">
                        ${statusNome}
                    </span>
                   </div>`;
        }).join("");
        caixa.style.display = 'block';

        caixa.querySelectorAll('.sugestao-item').forEach(el => {
            el.addEventListener('click', function() {
                let data;
                try { data = JSON.parse(this.dataset.id.replace(/&#39;/g, "'")); } catch(e) { data = null; }
                if (!data) return;
                document.getElementById('busca_patrimonio').value = data.patrimonio || "";
                preencherCamposEquipamento(data);
                caixa.innerHTML = '';
                caixa.style.display = 'none';
                document.getElementById('descricao_problema').focus();
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        atualizarCampos();

        const buscaInput = document.getElementById('busca_patrimonio');
        const caixa = document.getElementById('sugestoes_patrimonio');

        buscaInput.addEventListener('input', function() {
            buscarSugestoesPatrimonio(this.value);
        });

        buscaInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const t = (this.value || "").trim().toLowerCase();
                if (t) {
                    const achado = _listaEquipamentos.find(e => String(e.patrimonio || "").toLowerCase() === t);
                    if (achado) {
                        preencherCamposEquipamento(achado);
                        caixa.innerHTML = '';
                        caixa.style.display = 'none';
                        document.getElementById('descricao_problema').focus();
                        return;
                    }
                }
                caixa.innerHTML = '';
                caixa.style.display = 'none';
            } else if (e.key === 'Escape') {
                caixa.innerHTML = '';
                caixa.style.display = 'none';
            } else if (e.key === 'Tab') {
                const t = (this.value || "").trim().toLowerCase();
                if (t) {
                    const achado = _listaEquipamentos.find(e => String(e.patrimonio || "").toLowerCase() === t);
                    if (achado) {
                        preencherCamposEquipamento(achado);
                        caixa.innerHTML = '';
                        caixa.style.display = 'none';
                    }
                }
            }
        });

        document.addEventListener('click', function(e) {
            if (!buscaInput.contains(e.target) && !caixa.contains(e.target)) {
                caixa.innerHTML = '';
                caixa.style.display = 'none';
            }
        });

        const eqIdInicial = document.getElementById('equipamento_relacionado').value;
        if (eqIdInicial) {
            const eq = _listaEquipamentos.find(x => String(x.id) === String(eqIdInicial));
            if (eq) {
                preencherCamposEquipamento(eq);
                if (!buscaInput.value && eq.patrimonio) buscaInput.value = eq.patrimonio;
            }
        }
    });
</script>

</body>
</html>
