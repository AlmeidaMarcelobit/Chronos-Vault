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

// ============================================
// PROCESSAMENTO DE AÇÕES (ATUALIZAR STATUS / EXCLUIR)
// ============================================
$mensagemAcao = "";
$mensagemAcaoTipo = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && $can_edit) {
    $acao = $_POST["acao"] ?? "";
    $idSolicitacao = $_POST["id"] ?? null;

    if ($acao === "atualizar_status" && $idSolicitacao) {
        $novoStatus = trim($_POST["novo_status"] ?? "");
        $statusValidos = array_keys(getStatusSolicitacaoManutencao());

        $destinoConclusao = null;
        if ($novoStatus === "concluido") {
            $destinoConclusao = $_POST["destino_conclusao"] ?? "colaborador";
            if (!in_array($destinoConclusao, ["colaborador", "estoque", "manter"])) $destinoConclusao = "colaborador";
        }

        if (in_array($novoStatus, $statusValidos)) {
            $solicitacoesRaw = carregarSolicitacoesManutencao();
            $encontrouIndice = null;
            foreach ($solicitacoesRaw as $idx => $s) {
                if (($s["id"] ?? null) == $idSolicitacao) {
                    $encontrouIndice = $idx;
                    break;
                }
            }

            if ($encontrouIndice !== null) {
                $statusAnteriorSolic = $solicitacoesRaw[$encontrouIndice]["status"] ?? "";
                $solicitacoesRaw[$encontrouIndice]["status"] = $novoStatus;

                if (!isset($solicitacoesRaw[$encontrouIndice]["historico_status"]) || !is_array($solicitacoesRaw[$encontrouIndice]["historico_status"])) {
                    $solicitacoesRaw[$encontrouIndice]["historico_status"] = [];
                }

                $solicitacoesRaw[$encontrouIndice]["historico_status"][] = [
                    "status"  => $novoStatus,
                    "data"    => date("Y-m-d H:i:s"),
                    "usuario" => $_SESSION["usuario_nome"] ?? "Sistema",
                    "destino_conclusao" => ($novoStatus === "concluido") ? $destinoConclusao : null
                ];

                if ($novoStatus === "devolvido") {
                    $solicitacoesRaw[$encontrouIndice]["data_devolucao"] = date("Y-m-d H:i:s");
                } elseif ($novoStatus === "concluido") {
                    $solicitacoesRaw[$encontrouIndice]["data_conclusao"] = date("Y-m-d H:i:s");
                    $solicitacoesRaw[$encontrouIndice]["destino_conclusao"] = $destinoConclusao;
                }

                $salvouSolicitacao = salvarSolicitacoesManutencao($solicitacoesRaw);
                $msgEquipamento = "";

                if ($salvouSolicitacao) {
                    $equipamentoRelId = $solicitacoesRaw[$encontrouIndice]["equipamento_relacionado_id"] ?? null;
                    if ($equipamentoRelId) {
                        if ($novoStatus === "concluido") {
                            if ($destinoConclusao === "manter") {
                                $atualizou = atualizarStatusInternoEquipamentoManutencao($equipamentoRelId, "concluido");
                                if (!$atualizou) {
                                    $msgEquipamento = " (Atenção: não foi possível atualizar o status do equipamento)";
                                }
                            } else {
                                $retorno = concluirSolicitacaoRetornarEquipamento($equipamentoRelId, $destinoConclusao);
                                if (!$retorno) {
                                    $detalheEq = "";
                                    $buscaEq = buscarEquipamentoPorId($equipamentoRelId);
                                    if ($buscaEq) {
                                        $dadosEq = $buscaEq["equipamento"] ?? [];
                                        $detalheEq = " (Equipamento ID: " . ($dadosEq["id"] ?? "?") . " — Status Atual: " . ($dadosEq["status"] ?? "?") . " | Origem: " . ($buscaEq["status_origem"] ?? "?") . "). Contate o administrador ou execute a sincronia.";
                                    }
                                    $msgEquipamento = " (Atenção: não foi possível retornar o equipamento da manutenção. {$detalheEq})";
                                } else {
                                    registrarLog("Retorno de Equipamento",
                                        "Equipamento ID: {$equipamentoRelId} | Destino: {$destinoConclusao} | Via Solicitação #{$idSolicitacao}");
                                }
                            }
                        } elseif ($novoStatus === "devolvido") {
                            $buscaEq = buscarEquipamentoPorId($equipamentoRelId);
                            if ($buscaEq && ($buscaEq["equipamento"]["status"] ?? "") === "manutencao") {
                                $colabAntes = $buscaEq["equipamento"]["colaborador_id_antes_manutencao"] ?? null;
                                $statusAntes = $buscaEq["equipamento"]["status_anterior"] ?? "estoque";
                                $destinoAuto = ($colabAntes && in_array($statusAntes, ["alocado", "emprestado"])) ? "colaborador" : "estoque";
                                $retorno = concluirSolicitacaoRetornarEquipamento($equipamentoRelId, $destinoAuto);
                                if (!$retorno) {
                                    $msgEquipamento = " (Atenção: não foi possível devolver o equipamento)";
                                } else {
                                    registrarLog("Devolução de Equipamento",
                                        "Equipamento ID: {$equipamentoRelId} | Destino automático: {$destinoAuto} | Via Solicitação #{$idSolicitacao}");
                                }
                            }
                        } else {
                            $atualizou = atualizarStatusInternoEquipamentoManutencao($equipamentoRelId, $novoStatus);
                            if (!$atualizou) {
                                $msgEquipamento = " (Atenção: não foi possível atualizar o status do equipamento)";
                            }
                        }
                    }

                    registrarLog("Atualização de Solicitação",
                        "ID: {$idSolicitacao} | Status alterado: {$statusAnteriorSolic} → {$novoStatus} | Equip. Rel: {$equipamentoRelId}");
                    $_SESSION["mensagem"] = "Status da solicitação #{$idSolicitacao} atualizado para: " . getStatusSolicitacaoTexto($novoStatus) . $msgEquipamento;
                    $_SESSION["mensagem_tipo"] = empty($msgEquipamento) ? "success" : "error";
                    header("Location: index.php" . (!empty($_GET) ? "?" . http_build_query($_GET) : ""));
                    exit();
                } else {
                    $_SESSION["mensagem"] = "Erro ao salvar solicitação. Verifique permissões da pasta data/.";
                    $_SESSION["mensagem_tipo"] = "error";
                    header("Location: index.php" . (!empty($_GET) ? "?" . http_build_query($_GET) : ""));
                    exit();
                }
            }
        }
    }

    if ($acao === "excluir" && $idSolicitacao && $is_admin) {
        $solicitacoesRaw = carregarSolicitacoesManutencao();
        $encontrouIdx = null;
        foreach ($solicitacoesRaw as $idx => $s) {
            if (($s["id"] ?? null) == $idSolicitacao) {
                $encontrouIdx = $idx;
                break;
            }
        }

        if ($encontrouIdx !== null) {
            $equipamentoRelId = $solicitacoesRaw[$encontrouIdx]["equipamento_relacionado_id"] ?? null;
            array_splice($solicitacoesRaw, $encontrouIdx, 1);

            if (salvarSolicitacoesManutencao($solicitacoesRaw)) {
                if ($equipamentoRelId) {
                    $buscaEq = buscarEquipamentoPorId($equipamentoRelId);
                    if ($buscaEq && ($buscaEq["equipamento"]["status"] ?? "") === "manutencao") {
                        concluirSolicitacaoRetornarEquipamento($equipamentoRelId, "colaborador");
                    }
                }
                registrarLog("Exclusão de Solicitação de Manutenção", "ID: {$idSolicitacao}");
                $_SESSION["mensagem"] = "Solicitação #{$idSolicitacao} excluída com sucesso.";
                $_SESSION["mensagem_tipo"] = "success";
                header("Location: index.php" . (!empty($_GET) ? "?" . http_build_query($_GET) : ""));
                exit();
            } else {
                $_SESSION["mensagem"] = "Erro ao excluir solicitação. Verifique permissões.";
                $_SESSION["mensagem_tipo"] = "error";
                header("Location: index.php" . (!empty($_GET) ? "?" . http_build_query($_GET) : ""));
                exit();
            }
        }
    }
}

// ============================================
// CARREGAR DADOS E APLICAR FILTROS
// ============================================
$solicitacoes = array_values(array_filter(carregarSolicitacoesManutencao(), function($s) {
    return isset($s["id"]) && !empty($s["id"]);
}));

try {
    $sincronizados = sincronizarSolicitacoesComEquipamentos();
    if ($sincronizados > 0 && empty($_POST)) {
        $solicitacoes = array_values(array_filter(carregarSolicitacoesManutencao(), function($s) {
            return isset($s["id"]) && !empty($s["id"]);
        }));
    }
} catch (\Throwable $e) {
    error_log("Erro sincronizar solicitacoes: " . $e->getMessage());
}

$filtro_status = $_GET["status"] ?? "todos";
$filtro_prioridade = $_GET["prioridade"] ?? "todos";
$filtro_destino = $_GET["destino"] ?? "todos";
$filtro_tipo = $_GET["tipo"] ?? "todos";
$busca = $_GET["busca"] ?? "";

$todasSolicitacoes = $solicitacoes;

$totalGeral = count($todasSolicitacoes);
$totalAguardando = contarSolicitacoesPorStatus("aguardando_envio");
$totalEmManutencao = contarSolicitacoesPorStatus("em_manutencao");
$totalConcluidos = contarSolicitacoesPorStatus("concluido");
$totalDevolvidos = contarSolicitacoesPorStatus("devolvido");

if ($filtro_status !== "todos") {
    $solicitacoes = array_filter($solicitacoes, function($s) use ($filtro_status) {
        return ($s["status"] ?? "") === $filtro_status;
    });
}
if ($filtro_prioridade !== "todos") {
    $solicitacoes = array_filter($solicitacoes, function($s) use ($filtro_prioridade) {
        return ($s["prioridade"] ?? "") === $filtro_prioridade;
    });
}
if ($filtro_destino !== "todos") {
    $solicitacoes = array_filter($solicitacoes, function($s) use ($filtro_destino) {
        return ($s["destino_reparo"] ?? "") === $filtro_destino;
    });
}
if ($filtro_tipo !== "todos") {
    $solicitacoes = array_filter($solicitacoes, function($s) use ($filtro_tipo) {
        return ($s["tipo_equipamento"] ?? "") === $filtro_tipo;
    });
}
if ($busca) {
    $buscaLower = strtolower($busca);
    $solicitacoes = array_filter($solicitacoes, function($s) use ($buscaLower) {
        $idMatch = stripos("#" . ($s["id"] ?? ""), $buscaLower) !== false;
        $probMatch = stripos($s["descricao_problema"] ?? "", $buscaLower) !== false;
        $respMatch = stripos($s["responsavel_envio"] ?? "", $buscaLower) !== false;
        $patrMatch = stripos($s["patrimonio"] ?? "", $buscaLower) !== false;
        $outroMatch = stripos($s["outro_especificar"] ?? "", $buscaLower) !== false;
        return $idMatch || $probMatch || $respMatch || $patrMatch || $outroMatch;
    });
}

$solicitacoes = array_values($solicitacoes);
usort($solicitacoes, function($a, $b) {
    $prioridadeOrdem = ["urgente" => 0, "normal" => 1, "baixa" => 2];
    $pa = $prioridadeOrdem[$a["prioridade"] ?? "normal"] ?? 1;
    $pb = $prioridadeOrdem[$b["prioridade"] ?? "normal"] ?? 1;
    if ($pa !== $pb) return $pa - $pb;
    return strcmp($b["data_envio"] ?? "", $a["data_envio"] ?? "");
});

$totalFiltrado = count($solicitacoes);

$statusLista = getStatusSolicitacaoManutencao();
$prioridadesLista = getPrioridadesSolicitacao();
$destinosLista = getDestinosReparo();
$tiposLista = getTiposEquipamentoSolicitacao();
?>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitações de Manutenção - Sistema de Gestão</title>
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

    <!-- HEADER DA PÁGINA -->
    <div class="page-header">
        <div class="page-title-section">
            <h1><i class="fas fa-tools"></i> Solicitações de Manutenção</h1>
            <p class="page-subtitle">Gerencie todas as solicitações de reparo e manutenção</p>
        </div>
        <?php if ($can_edit): ?>
            <div class="page-actions">
                <a href="adicionar.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span>Nova Solicitação</span>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- STATS CARDS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-content">
                <h3>Total Geral</h3>
                <p class="stat-number"><?php echo $totalGeral; ?></p>
            </div>
        </div>
        <a href="?status=aguardando_envio" style="text-decoration: none;">
            <div class="stat-card">
                <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
                <div class="stat-content">
                    <h3>Aguardando Envio</h3>
                    <p class="stat-number"><?php echo $totalAguardando; ?></p>
                </div>
            </div>
        </a>
        <a href="?status=em_manutencao" style="text-decoration: none;">
            <div class="stat-card">
                <div class="stat-icon info"><i class="fas fa-tools"></i></div>
                <div class="stat-content">
                    <h3>Em Manutenção</h3>
                    <p class="stat-number"><?php echo $totalEmManutencao; ?></p>
                </div>
            </div>
        </a>
        <a href="?status=concluido" style="text-decoration: none;">
            <div class="stat-card">
                <div class="stat-icon success"><i class="fas fa-check-circle"></i></div>
                <div class="stat-content">
                    <h3>Concluídos</h3>
                    <p class="stat-number"><?php echo $totalConcluidos; ?></p>
                </div>
            </div>
        </a>
        <a href="?status=devolvido" style="text-decoration: none;">
            <div class="stat-card">
                <div class="stat-icon primary"><i class="fas fa-undo"></i></div>
                <div class="stat-content">
                    <h3>Devolvidos</h3>
                    <p class="stat-number"><?php echo $totalDevolvidos; ?></p>
                </div>
            </div>
        </a>
    </div>

    <!-- FILTROS RÁPIDOS POR STATUS -->
    <div class="filter-tabs">
        <a href="?" class="filter-tab <?php echo $filtro_status === 'todos' ? 'active' : ''; ?>">
            <i class="fas fa-list"></i> Todos <span class="count-badge"><?php echo $totalGeral; ?></span>
        </a>
        <a href="?status=aguardando_envio" class="filter-tab <?php echo $filtro_status === 'aguardando_envio' ? 'active' : ''; ?>">
            <i class="fas fa-clock"></i> Aguardando <span class="count-badge"><?php echo $totalAguardando; ?></span>
        </a>
        <a href="?status=em_manutencao" class="filter-tab <?php echo $filtro_status === 'em_manutencao' ? 'active' : ''; ?>">
            <i class="fas fa-tools"></i> Em Manutenção <span class="count-badge"><?php echo $totalEmManutencao; ?></span>
        </a>
        <a href="?status=concluido" class="filter-tab <?php echo $filtro_status === 'concluido' ? 'active' : ''; ?>">
            <i class="fas fa-check-circle"></i> Concluídos <span class="count-badge"><?php echo $totalConcluidos; ?></span>
        </a>
        <a href="?status=devolvido" class="filter-tab <?php echo $filtro_status === 'devolvido' ? 'active' : ''; ?>">
            <i class="fas fa-undo"></i> Devolvidos <span class="count-badge"><?php echo $totalDevolvidos; ?></span>
        </a>
    </div>

    <!-- FILTROS AVANÇADOS -->
    <div class="filter-card">
        <form method="GET" class="filter-form">
            <div class="filter-grid">
                <div class="filter-group">
                    <label><i class="fas fa-laptop"></i> Tipo Equipamento</label>
                    <select name="tipo" class="form-control">
                        <option value="todos">Todos os Tipos</option>
                        <?php foreach ($tiposLista as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php echo $filtro_tipo === $key ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($value); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-truck"></i> Destino Reparo</label>
                    <select name="destino" class="form-control">
                        <option value="todos">Todos os Destinos</option>
                        <?php foreach ($destinosLista as $key => $d): ?>
                            <option value="<?php echo $key; ?>" <?php echo $filtro_destino === $key ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-flag"></i> Prioridade</label>
                    <select name="prioridade" class="form-control">
                        <option value="todos">Todas as Prioridades</option>
                        <?php foreach ($prioridadesLista as $key => $p): ?>
                            <option value="<?php echo $key; ?>" <?php echo $filtro_prioridade === $key ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group full-width">
                    <label><i class="fas fa-search"></i> Buscar</label>
                    <input type="text" name="busca" class="form-control"
                        placeholder="Nº protocolo, patrimônio, problema, responsável..."
                        value="<?php echo htmlspecialchars($busca); ?>">
                </div>
            </div>
            <div class="filter-actions">
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Limpar</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Aplicar Filtros</button>
            </div>
            <?php if ($filtro_status !== 'todos'): ?>
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($filtro_status); ?>">
            <?php endif; ?>
        </form>
    </div>

    <!-- TABELA DE SOLICITAÇÕES -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Protocolo</th>
                    <th>Data</th>
                    <th>Equipamento</th>
                    <th>Patrimônio</th>
                    <th>Destino</th>
                    <th>Prioridade</th>
                    <th>Responsável</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($solicitacoes)): ?>
                <tr>
                    <td colspan="9" class="empty-state">
                        <i class="fas fa-search"></i>
                        <p>Nenhuma solicitação encontrada com os filtros selecionados</p>
                        <a href="index.php" class="btn btn-secondary">Limpar filtros</a>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($solicitacoes as $s):
                    $solId = $s["id"] ?? "";
                    $statusAtual = $s["status"] ?? "aguardando_envio";
                    $statusInfo = $statusLista[$statusAtual] ?? $statusLista["aguardando_envio"];
                    $prioridadeInfo = $prioridadesLista[$s["prioridade"] ?? "normal"] ?? $prioridadesLista["normal"];
                    $destinoInfo = $destinosLista[$s["destino_reparo"] ?? "reparo_interno"] ?? $destinosLista["reparo_interno"];
                    $tipoNome = $tiposLista[$s["tipo_equipamento"] ?? "outro"] ?? "Outro";
                    $iconeTipo = match($s["tipo_equipamento"] ?? "outro") {
                        "notebook" => "laptop",
                        "celular"  => "mobile-alt",
                        default    => "box"
                    };
                ?>
                <tr>
                    <td data-label="Protocolo">
                        <strong style="color: var(--primary);">#<?php echo htmlspecialchars($solId); ?></strong>
                    </td>
                    <td data-label="Data">
                        <?php echo formatarData($s["data_envio"] ?? ""); ?>
                    </td>
                    <td data-label="Equipamento">
                        <span class="tipo-badge">
                            <i class="fas fa-<?php echo $iconeTipo; ?>"></i>
                            <?php
                                echo htmlspecialchars($tipoNome);
                                if (!empty($s["outro_especificar"])) {
                                    echo " - " . htmlspecialchars($s["outro_especificar"]);
                                }
                            ?>
                        </span>
                    </td>
                    <td data-label="Patrimônio">
                        <?php echo !empty($s["patrimonio"]) ? htmlspecialchars($s["patrimonio"]) : "---"; ?>
                    </td>
                    <td data-label="Destino">
                        <span class="destino-badge">
                            <i class="fas fa-<?php echo $destinoInfo["icone"]; ?>"></i>
                            <?php echo htmlspecialchars($destinoInfo["nome"]); ?>
                        </span>
                    </td>
                    <td data-label="Prioridade">
                        <span class="badge prioridade-<?php echo $s["prioridade"] ?? "normal"; ?>">
                            <span class="badge-dot"></span>
                            <?php echo htmlspecialchars($prioridadeInfo["nome"]); ?>
                        </span>
                    </td>
                    <td data-label="Responsável">
                        <?php echo htmlspecialchars($s["responsavel_envio"] ?? "---"); ?>
                    </td>
                    <td data-label="Status">
                        <?php if ($can_edit): ?>
                            <form method="POST" class="status-form-inline" data-status-atual="<?php echo $statusAtual; ?>">
                                <input type="hidden" name="acao" value="atualizar_status">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($solId); ?>">
                                <input type="hidden" name="destino_conclusao" class="destino-conclusao-hidden" value="colaborador">
                                <select name="novo_status" class="status-select select-status-solicitacao"
                                    data-id="<?php echo htmlspecialchars($solId); ?>"
                                    data-tem-equipamento="<?php echo (!empty($s["equipamento_relacionado_id"]) ? '1' : '0'); ?>"
                                    data-status-atual="<?php echo $statusAtual; ?>"
                                    style="background: <?php echo $statusInfo['cor']; ?>15; color: <?php echo $statusInfo['cor']; ?>; border-color: <?php echo $statusInfo['cor']; ?>40; font-weight:600;">
                                    <?php foreach ($statusLista as $chave => $st): ?>
                                        <option value="<?php echo $chave; ?>"
                                            style="background: #fff; color: #333; font-weight:normal;"
                                            <?php echo $statusAtual === $chave ? "selected" : ""; ?>>
                                            <?php echo htmlspecialchars($st["nome"]); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        <?php else: ?>
                            <span class="badge status-badge-<?php echo $statusAtual; ?>">
                                <span class="badge-dot"></span>
                                <?php echo htmlspecialchars($statusInfo["nome"]); ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Ações">
                        <div class="action-buttons">
                            <button type="button" class="action-btn action-view"
                                onclick="showDetails(<?php echo htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8'); ?>)"
                                title="Ver Detalhes">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php if ($can_edit): ?>
                                <a href="adicionar.php" class="action-btn action-edit" title="Nova Solicitação" style="text-decoration:none;">
                                    <i class="fas fa-plus"></i>
                                </a>
                            <?php endif; ?>
                            <?php if ($is_admin): ?>
                                <form method="POST" style="display:inline;"
                                    onsubmit="return confirm('Tem certeza que deseja excluir a solicitação #<?php echo htmlspecialchars($solId); ?>? Esta ação é irreversível.');">
                                    <input type="hidden" name="acao" value="excluir">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($solId); ?>">
                                    <button type="submit" class="action-btn action-delete" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- FOOTER DA PÁGINA -->
    <div class="page-footer">
        <div class="total-count">
            <i class="fas fa-chart-line"></i>
            <span>Resultados: <strong><?php echo $totalFiltrado; ?></strong> de <?php echo $totalGeral; ?> solicitações</span>
        </div>
    </div>

</main>

<!-- ==================== MODAL DETALHES ==================== -->
<div id="modalDetalhes" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-file-alt"></i> Detalhes da Solicitação <span id="modalProtocolo"></span></h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody"></div>
    </div>
</div>

<!-- ==================== MODAL CONCLUIR SOLICITAÇÃO ==================== -->
<div id="modalConcluir" class="modal">
    <div class="modal-content" style="max-width: 520px;">
        <div class="modal-header" style="border-color: rgba(46,204,113,0.3);">
            <h3 style="color: #2ECC71;"><i class="fas fa-check-double"></i> Concluir Solicitação <span id="modalConcluirProtocolo"></span></h3>
            <button class="modal-close" onclick="closeModalConcluir()">&times;</button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom: 1rem; color: var(--gray-700);">
                A manutenção foi concluída. Selecione o destino para o <strong id="modalConcluirEquipamentoNome">equipamento</strong>:
            </p>

            <div id="semEquipamentoAviso" style="display:none;" class="detail-item"
                style="padding:0.75rem 1rem; background:rgba(255,193,7,0.1); border-left:3px solid #F39C12; border-radius:8px; margin-bottom:1rem;">
                <i class="fas fa-exclamation-triangle" style="color:#F39C12;"></i>
                <span style="color: var(--gray-700);"> Esta solicitação não possui equipamento vinculado. O status será atualizado mas nenhum equipamento será movido.</span>
            </div>

            <div class="destino-opcoes" style="display:flex; gap:1rem; flex-direction:column; margin:1rem 0;">
                <label class="destino-opcao" id="opcao-colaborador"
                    style="display:flex; gap:1rem; align-items:flex-start; padding:1.25rem 1rem; border:2px solid var(--gray-200); border-radius:16px; cursor:pointer; transition: all .2s ease; flex:1; background:var(--gray-50);">
                    <input type="radio" name="destino_conclusao_modal" value="colaborador" checked style="margin-top:2px; accent-color: var(--primary);">
                    <div style="flex:1;">
                        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
                            <i class="fas fa-user-check" style="color: var(--primary);"></i>
                            <strong style="color: var(--gray-800); font-size: 0.95rem;">Voltar para o Colaborador</strong>
                        </div>
                        <p style="font-size: 0.825rem; color: var(--gray-600); line-height: 1.45;">
                            O equipamento retorna para o colaborador que estava alocado anteriormente, mantendo o vínculo existente.<br>
                            <span id="nomeColaboradorAtual" style="display:block; margin-top:0.35rem; color: var(--primary-dark); font-weight:600;"></span>
                        </p>
                    </div>
                </label>

                <label class="destino-opcao" id="opcao-estoque"
                    style="display:flex; gap:1rem; align-items:flex-start; padding:1.25rem 1rem; border:2px solid var(--gray-200); border-radius:16px; cursor:pointer; transition: all .2s ease; flex:1; background:var(--gray-50);">
                    <input type="radio" name="destino_conclusao_modal" value="estoque" style="margin-top:2px; accent-color: var(--primary);">
                    <div style="flex:1;">
                        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
                            <i class="fas fa-warehouse" style="color: var(--primary-dark);"></i>
                            <strong style="color: var(--gray-800); font-size: 0.95rem;">Voltar para o Estoque</strong>
                        </div>
                        <p style="font-size: 0.825rem; color: var(--gray-600); line-height: 1.45;">
                            O equipamento é desassociado do colaborador e retorna para o estoque, disponível para nova alocação.
                        </p>
                    </div>
                </label>

                <label class="destino-opcao" id="opcao-manter"
                    style="display:flex; gap:1rem; align-items:flex-start; padding:1.25rem 1rem; border:2px solid var(--gray-200); border-radius:16px; cursor:pointer; transition: all .2s ease; flex:1; background:var(--gray-50);">
                    <input type="radio" name="destino_conclusao_modal" value="manter" style="margin-top:2px; accent-color: #F39C12;">
                    <div style="flex:1;">
                        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
                            <i class="fas fa-tools" style="color: #F39C12;"></i>
                            <strong style="color: var(--gray-800); font-size: 0.95rem;">Manter em Manutenção</strong>
                        </div>
                        <p style="font-size: 0.825rem; color: var(--gray-600); line-height: 1.45;">
                            A solicitação é marcada como concluída, mas o equipamento permanece em manutenção (não volta para colaborador nem estoque). Use se houver necessidade de reparos adicionais ou aguardando retirada.
                        </p>
                    </div>
                </label>
            </div>

            <div class="modal-actions" style="display:flex; gap:0.5rem; justify-content:flex-end; margin-top:1rem; padding-top:1rem; border-top:1px solid var(--gray-200);">
                <button type="button" class="btn btn-secondary" onclick="closeModalConcluir()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" onclick="confirmarConclusaoDestino()">
                    <i class="fas fa-check"></i> Confirmar Conclusão
                </button>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3><i class="fas fa-laptop-house"></i> Gestão de Equipamentos</h3>
            <p>Controle de colaboradores e equipamentos</p>
        </div>
        <div class="footer-section">
            <h3>Links Rápidos</h3>
            <ul class="footer-links">
                <li><a href="../index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="../colaboradores/index.php"><i class="fas fa-users"></i> Colaboradores</a></li>
                <li><a href="../equipamentos/index.php"><i class="fas fa-laptop"></i> Equipamentos</a></li>
                <li><a href="index.php"><i class="fas fa-tools"></i> Solicitações Manutenção</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Estatísticas</h3>
            <div class="footer-stats">
                <div class="footer-stat">
                    <span class="stat-number"><?php echo $totalGeral; ?></span>
                    <span class="stat-label">Total</span>
                </div>
                <div class="footer-stat">
                    <span class="stat-number"><?php echo $totalEmManutencao; ?></span>
                    <span class="stat-label">Em Andamento</span>
                </div>
                <div class="footer-stat">
                    <span class="stat-number"><?php echo $totalDevolvidos + $totalConcluidos; ?></span>
                    <span class="stat-label">Finalizadas</span>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>Sistema de Gestão &copy; <?php echo date("Y"); ?> - Todos os direitos reservados</p>
        <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
</footer>

<script>
    const statusLista = <?php echo json_encode($statusLista); ?>;
    const prioridadesLista = <?php echo json_encode($prioridadesLista); ?>;
    const destinosLista = <?php echo json_encode($destinosLista); ?>;
    const tiposLista = <?php echo json_encode($tiposLista); ?>;

    <?php
    $solicitacoesParaJS = [];
    foreach ($solicitacoes as $s) {
        $item = $s;
        if (!empty($s["equipamento_relacionado_id"])) {
            $buscaEq = buscarEquipamentoPorId($s["equipamento_relacionado_id"]);
            if ($buscaEq) {
                $eq = $buscaEq["equipamento"];
                $colabNome = $eq["colaborador_nome_antes_manutencao"] ?? ($eq["colaborador_nome"] ?? null);
                $item["_colaborador_nome"] = $colabNome;
                $item["_status_anterior_equip"] = $eq["status_anterior"] ?? null;
                $item["_equip_patrimonio"] = $eq["patrimonio"] ?? null;
                if (!isset($item["patrimonio"]) && !empty($eq["patrimonio"])) {
                    $item["patrimonio"] = $eq["patrimonio"];
                }
            }
        }
        $solicitacoesParaJS[] = $item;
    }
    ?>
    window._solicitacoesData = <?php echo json_encode($solicitacoesParaJS); ?>;

    function formatarData(dataString) {
        if (!dataString) return '---';
        try {
            const data = new Date(dataString.replace(/-/g, '/'));
            return data.toLocaleDateString('pt-BR') + ' ' +
                   data.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
        } catch(e) { return dataString; }
    }

    function showDetails(solicitacao) {
        const statusAtual = solicitacao.status || "aguardando_envio";
        const statusInfo = statusLista[statusAtual] || statusLista["aguardando_envio"];
        const prioridade = solicitacao.prioridade || "normal";
        const prioridadeInfo = prioridadesLista[prioridade] || prioridadesLista["normal"];
        const destino = solicitacao.destino_reparo || "reparo_interno";
        const destinoInfo = destinosLista[destino] || destinosLista["reparo_interno"];
        const tipo = solicitacao.tipo_equipamento || "outro";

        document.getElementById('modalProtocolo').textContent = '#' + solicitacao.id;

        const iconeTipo = tipo === 'notebook' ? 'laptop' : (tipo === 'celular' ? 'mobile-alt' : 'box');
        const equipamentoNome = tiposLista[tipo] || 'Outro';
        const outroDetalhe = solicitacao.outro_especificar
            ? `<div class="detail-item outro-box"><i class="fas fa-info-circle"></i> ${solicitacao.outro_especificar}</div>`
            : '';

        let patrimonioHtml = '';
        if (solicitacao.patrimonio) {
            patrimonioHtml = `<div class="detail-item"><strong>Patrimônio / ID:</strong><div>${solicitacao.patrimonio}</div></div>`;
        }
        if (solicitacao.equipamento_relacionado_id) {
            patrimonioHtml += `<div class="detail-item"><strong>Equip. Relacionado:</strong><div>ID #${solicitacao.equipamento_relacionado_id}</div></div>`;
        }

        let historicoHtml = '';
        if (solicitacao.historico_status && Array.isArray(solicitacao.historico_status) && solicitacao.historico_status.length > 0) {
            historicoHtml = `
                <div class="detail-section-title" style="margin-top:1rem;">
                    <i class="fas fa-history"></i> Histórico de Status
                </div>
                <div class="timeline">
                    ${solicitacao.historico_status.slice().reverse().map(h => {
                        const st = statusLista[h.status] || {nome: h.status, icone: 'circle'};
                        const corStatus = st.cor || '#6C757D';
                        return `
                            <div class="timeline-item">
                                <div class="timeline-dot ${h.status}" style="background: ${corStatus}20; color: ${corStatus};">
                                    <i class="fas fa-${st.icone || 'circle'}"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">${st.nome}</div>
                                    <div class="timeline-date">
                                        <i class="fas fa-user"></i> ${h.usuario || 'Sistema'}
                                        &nbsp;•&nbsp;
                                        <i class="fas fa-calendar"></i> ${formatarData(h.data)}
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            `;
        }

        const content = `
            <div class="modal-details">

                <div class="detail-section-title">
                    <i class="fas fa-clipboard"></i> Informações Gerais
                </div>
                <div class="detail-grid">
                    <div class="detail-item">
                        <strong>Protocolo:</strong>
                        <div style="color: var(--primary); font-weight:700; font-size:1.1rem;">#${solicitacao.id}</div>
                    </div>
                    <div class="detail-item">
                        <strong>Data de Envio:</strong>
                        <div>${formatarData(solicitacao.data_envio)}</div>
                    </div>
                    <div class="detail-item">
                        <strong>Status Atual:</strong>
                        <div>
                            <span class="badge" style="background: ${statusInfo.cor}15; color: ${statusInfo.cor};">
                                <span class="badge-dot" style="background:${statusInfo.cor};"></span>
                                <i class="fas fa-${statusInfo.icone}"></i> ${statusInfo.nome}
                            </span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <strong>Prioridade:</strong>
                        <div>
                            <span class="badge prioridade-${prioridade}">
                                <span class="badge-dot" style="background:${prioridadeInfo.cor};"></span>
                                ${prioridadeInfo.nome}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="detail-section-title" style="margin-top:1rem;">
                    <i class="fas fa-laptop"></i> Equipamento
                </div>
                <div class="detail-grid">
                    <div class="detail-item">
                        <strong>Tipo:</strong>
                        <div><i class="fas fa-${iconeTipo}"></i> ${equipamentoNome}</div>
                    </div>
                    ${outroDetalhe ? `<div class="detail-item full-width">${outroDetalhe}</div>` : ''}
                    ${patrimonioHtml}
                </div>

                <div class="detail-section-title" style="margin-top:1rem;">
                    <i class="fas fa-truck"></i> Destino e Responsável
                </div>
                <div class="detail-grid">
                    <div class="detail-item">
                        <strong>Destino do Reparo:</strong>
                        <div>
                            <span class="destino-badge">
                                <i class="fas fa-${destinoInfo.icone}"></i>
                                ${destinoInfo.nome} (${destinoInfo.descricao})
                            </span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <strong>Responsável pelo Envio:</strong>
                        <div><i class="fas fa-user"></i> ${solicitacao.responsavel_envio || '---'}</div>
                    </div>
                    <div class="detail-item full-width">
                        <strong>Solicitante (usuário sistema):</strong>
                        <div>${solicitacao.usuario_cadastro_nome || 'Sistema'}</div>
                    </div>
                </div>

                <div class="detail-section-title" style="margin-top:1rem;">
                    <i class="fas fa-comment-alt"></i> Descrição do Problema
                </div>
                <div class="detail-item full-width problema-box">
                    ${solicitacao.descricao_problema || 'Nenhuma descrição informada.'}
                </div>

                ${historicoHtml}
            </div>
        `;

        document.getElementById('modalBody').innerHTML = content;
        document.getElementById('modalDetalhes').style.display = 'block';
        document.getElementById('modalDetalhes').scrollTop = 0;
        document.getElementById('modalBody').scrollTop = 0;
    }

    function closeModal() {
        document.getElementById('modalDetalhes').style.display = 'none';
    }

    window.onclick = function(event) {
        const modalDet = document.getElementById('modalDetalhes');
        const modalConc = document.getElementById('modalConcluir');
        if (event.target === modalDet) closeModal();
        if (event.target === modalConc) closeModalConcluir();
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
            closeModalConcluir();
        }
    });

    // ==================== MODAL CONCLUIR SOLICITAÇÃO ====================
    let formularioStatusPendente = null;
    let solicitacaoPendente = null;

    function closeModalConcluir() {
        const modal = document.getElementById('modalConcluir');
        if (!modal) return;
        modal.style.display = 'none';
        formularioStatusPendente = null;
        solicitacaoPendente = null;
    }

    function destacarOpcaoModalConcluir() {
        const opcaoColab = document.getElementById('opcao-colaborador');
        const opcaoEstoque = document.getElementById('opcao-estoque');
        const opcaoManter = document.getElementById('opcao-manter');
        if (!opcaoColab || !opcaoEstoque || !opcaoManter) return;

        const radioSelecionado = document.querySelector('input[name="destino_conclusao_modal"]:checked');
        const valor = radioSelecionado ? radioSelecionado.value : 'colaborador';

        opcaoColab.style.borderColor = valor === 'colaborador' ? 'var(--primary)' : 'var(--gray-200)';
        opcaoColab.style.background = valor === 'colaborador' ? 'rgba(33,150,243,0.05)' : 'var(--gray-50)';
        opcaoColab.style.boxShadow = valor === 'colaborador' ? '0 0 0 3px rgba(33,150,243,0.1)' : 'none';

        opcaoEstoque.style.borderColor = valor === 'estoque' ? 'var(--primary-dark)' : 'var(--gray-200)';
        opcaoEstoque.style.background = valor === 'estoque' ? 'rgba(25,118,210,0.05)' : 'var(--gray-50)';
        opcaoEstoque.style.boxShadow = valor === 'estoque' ? '0 0 0 3px rgba(25,118,210,0.1)' : 'none';

        opcaoManter.style.borderColor = valor === 'manter' ? '#F39C12' : 'var(--gray-200)';
        opcaoManter.style.background = valor === 'manter' ? 'rgba(243,156,18,0.05)' : 'var(--gray-50)';
        opcaoManter.style.boxShadow = valor === 'manter' ? '0 0 0 3px rgba(243,156,18,0.15)' : 'none';
    }

    document.querySelectorAll('input[name="destino_conclusao_modal"]').forEach(r => {
        r.addEventListener('change', destacarOpcaoModalConcluir);
    });

    function confirmarConclusaoDestino() {
        if (!formularioStatusPendente) return;

        const radioSelecionado = document.querySelector('input[name="destino_conclusao_modal"]:checked');
        const destino = radioSelecionado ? radioSelecionado.value : 'colaborador';

        const campoDestino = formularioStatusPendente.querySelector('.destino-conclusao-hidden');
        if (campoDestino) campoDestino.value = destino;

        const campoStatus = formularioStatusPendente.querySelector('select[name="novo_status"]');
        if (campoStatus) campoStatus.value = 'concluido';

        formularioStatusPendente.submit();
    }

    // ==================== INTERCEPTA MUDANÇA DE STATUS NA TABELA ====================
    document.addEventListener('DOMContentLoaded', function() {
        destacarOpcaoModalConcluir();

        const selects = document.querySelectorAll('.select-status-solicitacao');
        selects.forEach(sel => {
            sel.addEventListener('change', function(e) {
                const novoValor = this.value;
                const statusAtual = this.dataset.statusAtual || '';
                const temEquipamento = this.dataset.temEquipamento === '1';
                const idSolicitacao = this.dataset.id;

                if (novoValor === statusAtual) return;

                const form = this.closest('.status-form-inline');

                if (novoValor === 'concluido') {
                    e.preventDefault();
                    this.value = statusAtual;
                    formularioStatusPendente = form;

                    document.getElementById('modalConcluirProtocolo').textContent = '#' + idSolicitacao;

                    const avisoSemEquip = document.getElementById('semEquipamentoAviso');
                    const opcaoColab = document.getElementById('opcao-colaborador');
                    const nomeColabSpan = document.getElementById('nomeColaboradorAtual');
                    const equipNomeEl = document.getElementById('modalConcluirEquipamentoNome');

                    const solicitacao = solicitacaoPendente || window._solicitacoesData ? (window._solicitacoesData.find(x => x.id == idSolicitacao) || null) : null;

                    if (!temEquipamento) {
                        avisoSemEquip.style.display = 'block';
                        equipNomeEl.textContent = 'processo';
                    } else {
                        avisoSemEquip.style.display = 'none';
                        equipNomeEl.textContent = 'equipamento';

                        if (solicitacao && solicitacao.patrimonio) {
                            equipNomeEl.textContent = 'equipamento (Patrimônio: ' + solicitacao.patrimonio + ')';
                        }
                    }

                    if (solicitacao && (solicitacao._colaborador_nome || solicitacao._status_anterior_equip === 'alocado' || solicitacao._status_anterior_equip === 'emprestado')) {
                        nomeColabSpan.innerHTML = '<i class="fas fa-user-tag"></i> Colaborador atual: ' + (solicitacao._colaborador_nome ? solicitacao._colaborador_nome : 'Cadastrado');
                        opcaoColab.style.display = 'flex';
                        const radios = document.querySelectorAll('input[name="destino_conclusao_modal"]');
                        radios.forEach(r => { r.checked = (r.value === 'colaborador'); });
                    } else {
                        nomeColabSpan.innerHTML = '<i class="fas fa-warehouse" style="color:#6c757d;"></i> <span style="color:#6c757d; font-size:0.9rem;">Este equipamento não estava atrelado a colaborador — disponível apenas para estoque.</span>';
                        opcaoColab.style.display = 'none';
                        const radios = document.querySelectorAll('input[name="destino_conclusao_modal"]');
                        radios.forEach(r => { r.checked = (r.value === 'estoque'); });
                    }
                    destacarOpcaoModalConcluir();

                    const modal = document.getElementById('modalConcluir');
                    modal.style.display = 'flex';
                    modal.scrollTop = 0;
                } else {
                    if (form) form.submit();
                }
            });
        });
    });
</script>

</body>
</html>
