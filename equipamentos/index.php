<?php
session_start();
require_once '../includes/funcoes.php';
verificarSessao();

$equipamentos = lerArquivoJSON('../data/equipamentos.json');
$colaboradores = lerArquivoJSON('../data/colaboradores.json');

// Criar mapa de colaboradores para busca rápida
$mapaColaboradores = [];
foreach ($colaboradores as $colaborador) {
    $mapaColaboradores[$colaborador['id']] = $colaborador;
}

// Estatísticas GLOBAIS (todos os equipamentos)
$equipamentosCompleto = $equipamentos; // Backup para estatísticas
$totalEquipamentos = count($equipamentos);
$totalEstoque = count(array_filter($equipamentos, function($e) { return $e['status'] === 'estoque'; }));
$totalAlocados = count(array_filter($equipamentos, function($e) { return $e['status'] === 'alocado'; }));
$totalEmprestados = count(array_filter($equipamentos, function($e) { return $e['status'] === 'emprestado'; }));
$totalManutencao = count(array_filter($equipamentos, function($e) { return $e['status'] === 'manutencao'; }));
$totalForaUso = count(array_filter($equipamentos, function($e) { return $e['status'] === 'fora_uso'; }));

// Aplicar filtros
$filtro = $_GET['filtro'] ?? 'todos';
$tipo = $_GET['tipo'] ?? 'todos';
$status = $_GET['status'] ?? 'todos';
$colaboradorId = $_GET['colaborador'] ?? null;
$centro_custo = $_GET['centro_custo'] ?? 'todos'; // NOVO: Inicializar variável

// Extrair todos os centros de custo únicos dos equipamentos
$centrosCustoUnicos = [];
foreach ($equipamentos as $equipamento) {
    if (!empty($equipamento['centro_custo'])) {
        $centrosCustoUnicos[$equipamento['centro_custo']] = $equipamento['centro_custo'];
    }
}
sort($centrosCustoUnicos); // Ordenar alfabeticamente

// Filtrar equipamentos para exibição
$equipamentosFiltrados = $equipamentos;

// Filtro por colaborador (tem prioridade sobre outros filtros)
if ($colaboradorId) {
    $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($equipamento) use ($colaboradorId) {
        return $equipamento['colaborador_id'] == $colaboradorId;
    });
} else {
    // Aplicar filtros por status principal
    if ($filtro === 'estoque') {
        $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($equipamento) {
            return $equipamento['status'] === 'estoque';
        });
    } elseif ($filtro === 'alocados') {
        $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($equipamento) {
            return $equipamento['status'] === 'alocado';
        });
    } elseif ($filtro === 'emprestados') {
        $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($equipamento) {
            return $equipamento['status'] === 'emprestado';
        });
    } elseif ($filtro === 'manutencao') {
        $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($equipamento) {
            return $equipamento['status'] === 'manutencao';
        });
    } elseif ($filtro === 'fora_uso') {
        $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($equipamento) {
            return $equipamento['status'] === 'fora_uso';
        });
    }
}

// Filtro por tipo específico (se não for "todos")
if ($tipo !== 'todos') {
    $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($equipamento) use ($tipo) {
        return $equipamento['tipo'] === $tipo;
    });
}

// Filtro por status específico (se não for "todos" e não tiver filtro principal aplicado)
if ($status !== 'todos' && !$colaboradorId && $filtro === 'todos') {
    $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($equipamento) use ($status) {
        return $equipamento['status'] === $status;
    });
}

// Filtro por centro de custo (NOVO)
if ($centro_custo !== 'todos') {
    $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($equipamento) use ($centro_custo) {
        return $equipamento['centro_custo'] === $centro_custo;
    });
}

// Buscar por patrimônio, serial, marca ou modelo
$busca = $_GET['busca'] ?? '';
if ($busca) {
    $buscaLower = strtolower($busca);
    $equipamentosFiltrados = array_filter($equipamentosFiltrados, function($equipamento) use ($buscaLower) {
        return stripos($equipamento['patrimonio'], $buscaLower) !== false ||
            stripos($equipamento['serial'] ?? '', $buscaLower) !== false ||
            stripos($equipamento['marca'], $buscaLower) !== false ||
            stripos($equipamento['modelo'], $buscaLower) !== false ||
            stripos(getTipoTexto($equipamento['tipo']), $buscaLower) !== false ||
            stripos($equipamento['centro_custo'], $buscaLower) !== false; // Adicionar centro_custo na busca
    });
}

// Reindexar array após filtros
$equipamentosFiltrados = array_values($equipamentosFiltrados);
?>

<?php include '../includes/header.php'; ?>

<main class="container">
    <div class="page-header">
        <h1><i class="fas fa-laptop"></i> Equipamentos</h1>
        <a href="adicionar.php" class="btn btn-success">
            <i class="fas fa-laptop-medical"></i> Adicionar Equipamento
        </a>
    </div>

    <div class="filter-section">
        <div class="filter-tabs">
            <a href="?filtro=todos" class="filter-tab <?php echo $filtro == 'todos' ? 'active' : ''; ?>">
                Todos (<?php echo $totalEquipamentos; ?>)
            </a>
            <a href="?filtro=estoque" class="filter-tab <?php echo $filtro == 'estoque' ? 'active' : ''; ?>">
                Em Estoque (<?php echo $totalEstoque; ?>)
            </a>
            <a href="?filtro=alocados" class="filter-tab <?php echo $filtro == 'alocados' ? 'active' : ''; ?>">
                Alocados (<?php echo $totalAlocados; ?>)
            </a>
            <a href="?filtro=emprestados" class="filter-tab <?php echo $filtro == 'emprestados' ? 'active' : ''; ?>">
                Emprestados (<?php echo $totalEmprestados; ?>)
            </a>
            <a href="?filtro=manutencao" class="filter-tab <?php echo $filtro == 'manutencao' ? 'active' : ''; ?>">
                Em Manutenção (<?php echo $totalManutencao; ?>)
            </a>
            <a href="?filtro=fora_uso" class="filter-tab <?php echo $filtro == 'fora_uso' ? 'active' : ''; ?>">
                Fora de Uso (<?php echo $totalForaUso; ?>)
            </a>
        </div>

        <!-- FORMULÁRIO DE FILTROS -->
        <form method="GET" class="filter-form">
            <div class="filter-grid">
                <div class="filter-group">
                    <label for="tipo"><i class="fas fa-filter"></i> Tipo:</label>
                    <select id="tipo" name="tipo" class="form-control">
                        <option value="todos" <?php echo $tipo == 'todos' ? 'selected' : ''; ?>>Todos os Tipos</option>
                        <?php foreach (getTiposEquipamentos() as $key => $value): ?>
                            <option value="<?php echo $key; ?>"
                                <?php echo $tipo == $key ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="caixa">
                        <i class="fas fa-dollar-sign"></i> Caixa:
                    </label>

                    <select id="caixa" name="caixa" class="form-control">
                        <option value="">
                            Selecione a Caixa
                        </option>

                        <?php foreach ($caixas as $caixa): ?>
                            <option value="<?php echo htmlspecialchars($caixa); ?>"
                                <?php echo ($caixa_id == $caixa) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($caixa); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="centro_custo">
                        <i class="fas fa-dollar-sign"></i> Centro de Custo:
                    </label>
                    <select id="centro_custo" name="centro_custo" class="form-control">
                        <option value="todos" <?php echo $centro_custo == 'todos' ? 'selected' : ''; ?>>
                            Todos os Centros de Custo
                        </option>
                        <?php foreach ($centrosCustoUnicos as $centro): ?>
                            <option value="<?php echo htmlspecialchars($centro); ?>"
                                <?php echo $centro_custo == $centro ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($centro); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="status"><i class="fas fa-info-circle"></i> Status:</label>
                    <select id="status" name="status" class="form-control">
                        <option value="todos" <?php echo $status == 'todos' ? 'selected' : ''; ?>>Todos Status</option>
                        <option value="estoque" <?php echo $status == 'estoque' ? 'selected' : ''; ?>>Em Estoque</option>
                        <option value="alocado" <?php echo $status == 'alocado' ? 'selected' : ''; ?>>Alocado</option>
                        <option value="emprestado" <?php echo $status == 'emprestado' ? 'selected' : ''; ?>>Emprestado</option>
                        <option value="manutencao" <?php echo $status == 'manutencao' ? 'selected' : ''; ?>>Em Manutenção</option>
                        <option value="fora_uso" <?php echo $status == 'fora_uso' ? 'selected' : ''; ?>>Fora de Uso</option>
                    </select>
                </div>

                <?php if (count($colaboradores) > 0): ?>
                    <div class="filter-group">
                        <label for="colaborador"><i class="fas fa-user"></i> Colaborador:</label>
                        <select id="colaborador" name="colaborador" class="form-control">
                            <option value="">Todos os Colaboradores</option>
                            <?php foreach ($colaboradores as $colaborador): ?>
                                <option value="<?php echo $colaborador['id']; ?>"
                                    <?php echo $colaboradorId == $colaborador['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($colaborador['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="filter-group filter-search">
                    <label for="busca"><i class="fas fa-search"></i> Buscar:</label>
                    <div class="search-box">
                        <input type="text"
                               id="busca"
                               name="busca"
                               class="form-control"
                               placeholder="Patrimônio, serial, marca, modelo, centro de custo..."
                               value="<?php echo htmlspecialchars($busca); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Aplicar Filtros
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                </div>
            </div>

            <!-- Campos ocultos para manter filtros -->
            <?php if ($filtro !== 'todos'): ?>
                <input type="hidden" name="filtro" value="<?php echo htmlspecialchars($filtro); ?>">
            <?php endif; ?>
        </form>
    </div>

    <!-- Mostrar estatísticas do filtro aplicado -->
    <?php
    $totalFiltrado = count($equipamentosFiltrados);
    $estoqueFiltrado = count(array_filter($equipamentosFiltrados, function($e) { return $e['status'] === 'estoque'; }));
    $alocadosFiltrado = count(array_filter($equipamentosFiltrados, function($e) { return $e['status'] === 'alocado'; }));
    $emprestadosFiltrado = count(array_filter($equipamentosFiltrados, function($e) { return $e['status'] === 'emprestado'; }));
    $manutencaoFiltrado = count(array_filter($equipamentosFiltrados, function($e) { return $e['status'] === 'manutencao'; }));
    $foraUsoFiltrado = count(array_filter($equipamentosFiltrados, function($e) { return $e['status'] === 'fora_uso'; }));
    ?>

    <div class="stats-cards">
        <div class="stat-card stat-<?php echo $estoqueFiltrado > 0 ? 'primary' : 'secondary'; ?>">
            <div class="stat-icon">
                <i class="fas fa-warehouse"></i>
            </div>
            <div class="stat-content">
                <h3>Em Estoque</h3>
                <p class="stat-number"><?php echo $estoqueFiltrado; ?></p>
                <small class="stat-percent">
                    <?php echo $totalEquipamentos > 0 ? number_format(($estoqueFiltrado/$totalEquipamentos)*100, 1) : '0'; ?>%
                </small>
            </div>
        </div>

        <div class="stat-card stat-<?php echo $alocadosFiltrado > 0 ? 'success' : 'secondary'; ?>">
            <div class="stat-icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-content">
                <h3>Alocados</h3>
                <p class="stat-number"><?php echo $alocadosFiltrado; ?></p>
                <small class="stat-percent">
                    <?php echo $totalEquipamentos > 0 ? number_format(($alocadosFiltrado/$totalEquipamentos)*100, 1) : '0'; ?>%
                </small>
            </div>
        </div>

        <div class="stat-card stat-<?php echo $emprestadosFiltrado > 0 ? 'info' : 'secondary'; ?>">
            <div class="stat-icon">
                <i class="fas fa-handshake"></i>
            </div>
            <div class="stat-content">
                <h3>Emprestados</h3>
                <p class="stat-number"><?php echo $emprestadosFiltrado; ?></p>
                <small class="stat-percent">
                    <?php echo $totalEquipamentos > 0 ? number_format(($emprestadosFiltrado/$totalEquipamentos)*100, 1) : '0'; ?>%
                </small>
            </div>
        </div>

        <div class="stat-card stat-<?php echo $manutencaoFiltrado > 0 ? 'warning' : 'secondary'; ?>">
            <div class="stat-icon">
                <i class="fas fa-tools"></i>
            </div>
            <div class="stat-content">
                <h3>Em Manutenção</h3>
                <p class="stat-number"><?php echo $manutencaoFiltrado; ?></p>
                <small class="stat-percent">
                    <?php echo $totalEquipamentos > 0 ? number_format(($manutencaoFiltrado/$totalEquipamentos)*100, 1) : '0'; ?>%
                </small>
            </div>
        </div>

        <div class="stat-card stat-<?php echo $foraUsoFiltrado > 0 ? 'danger' : 'secondary'; ?>">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-content">
                <h3>Fora de Uso</h3>
                <p class="stat-number"><?php echo $foraUsoFiltrado; ?></p>
                <small class="stat-percent">
                    <?php echo $totalEquipamentos > 0 ? number_format(($foraUsoFiltrado/$totalEquipamentos)*100, 1) : '0'; ?>%
                </small>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
            <tr>
                <th>Tipo</th>
                <th>Patrimônio</th>
                <th>Caixa</th>
                <th>Marca/Modelo</th>
                <th>Nº Série</th>
                <th>Centro de Custo</th>
                <th>Status</th>
                <th>Colaborador</th>
<!--                <th>Data Atribuição</th>-->
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($equipamentosFiltrados)): ?>
                <tr>
                    <td colspan="9" class="text-center">
                        <div class="empty-state">
                            <i class="fas fa-search fa-2x"></i>
                            <h4>Nenhum equipamento encontrado</h4>
                            <p>Tente ajustar os filtros ou a busca</p>
                            <a href="index.php" class="btn btn-primary">
                                <i class="fas fa-times"></i> Limpar filtros
                            </a>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($equipamentosFiltrados as $equipamento):
                    // Classes de status
                    $statusClasses = [
                        'estoque' => 'status-ativo',
                        'alocado' => 'status-inativo',
                        'emprestado' => 'status-info',
                        'manutencao' => 'status-warning',
                        'fora_uso' => 'status-danger'
                    ];
                    $statusClass = $statusClasses[$equipamento['status']] ?? 'status-ativo';
                    $statusText = getStatusTexto($equipamento['status']);

                    $tipoText = getTipoTexto($equipamento['tipo']);

                    $colaboradorNome = 'N/A';
                    if ($equipamento['colaborador_id'] && isset($mapaColaboradores[$equipamento['colaborador_id']])) {
                        $colaboradorNome = $mapaColaboradores[$equipamento['colaborador_id']]['nome'];
                    }
                    ?>
                    <tr>
                        <td>
                            <span class="tipo-badge tipo-<?php echo $equipamento['tipo']; ?>">
                                <i class="fas fa-<?php echo getIconByType($equipamento['tipo']); ?>"></i>
                                <?php echo $tipoText; ?>
                            </span>
                        </td>
                        <td><strong><?php echo htmlspecialchars($equipamento['patrimonio']); ?></strong></td>
                        <td>
                            <?php if (!empty($equipamento['caixa'])): ?>
                                <span class="caixa-badge">
                    <i class="fas fa-box"></i>
                    <?php echo htmlspecialchars($equipamento['caixa']); ?>
                </span>
                            <?php else: ?>
                                <span class="text-muted">---</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($equipamento['marca'] . ' ' . $equipamento['modelo']); ?></td>
                        <td><?php echo !empty($equipamento['serial']) ? htmlspecialchars($equipamento['serial']) : '---'; ?></td>
                        <td>
                            <span class="centro-custo-badge">
                                <i class="fas fa-dollar-sign"></i>
                                <?php echo htmlspecialchars($equipamento['centro_custo']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <i class="fas fa-<?php echo getIconByStatus($equipamento['status']); ?>"></i>
                                <?php echo $statusText; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($colaboradorNome); ?></td>
<!--                        <td>-->
<!--                            --><?php //echo $equipamento['data_atribuicao'] ? formatarData($equipamento['data_atribuicao']) : '---'; ?>
<!--                        </td>-->
                        <td>
                            <div class="action-buttons">
                                <a href="editar.php?id=<?php echo $equipamento['id']; ?>"
                                   class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <?php if ($equipamento['status'] == 'estoque'): ?>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-success dropdown-toggle"
                                                type="button"
                                                data-toggle="dropdown"
                                                aria-haspopup="true"
                                                aria-expanded="false"
                                                title="Atribuir">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="atribuir.php?id=<?php echo $equipamento['id']; ?>&tipo=alocado">
                                                <i class="fas fa-user-check"></i> Alocar para Colaborador
                                            </a>
                                            <a class="dropdown-item" href="atribuir.php?id=<?php echo $equipamento['id']; ?>&tipo=emprestado">
                                                <i class="fas fa-handshake"></i> Emprestar para Colaborador
                                            </a>
                                        </div>
                                    </div>
                                <?php elseif (in_array($equipamento['status'], ['alocado', 'emprestado'])): ?>
                                    <a href="devolver.php?id=<?php echo $equipamento['id']; ?>"
                                       class="btn btn-sm btn-info"
                                       onclick="return confirm('Devolver este equipamento para o estoque?')"
                                       title="Devolver">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                <?php elseif ($equipamento['status'] == 'manutencao'): ?>
                                    <a href="finalizar_manutencao.php?id=<?php echo $equipamento['id']; ?>"
                                       class="btn btn-sm btn-success"
                                       onclick="return confirm('Finalizar manutenção deste equipamento?')"
                                       title="Finalizar Manutenção">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>

                                <!-- Botão de Manutenção para equipamentos alocados/emprestados/estoque -->
                                <?php if (in_array($equipamento['status'], ['alocado', 'emprestado', 'estoque'])): ?>
                                    <a href="enviar_manutencao.php?id=<?php echo $equipamento['id']; ?>"
                                       class="btn btn-sm btn-warning"
                                       onclick="return confirm('Enviar este equipamento para manutenção?')"
                                       title="Enviar para Manutenção">
                                        <i class="fas fa-tools"></i>
                                    </a>
                                <?php endif; ?>

                                <!-- Botão para marcar como Fora de Uso -->
                                <?php if ($equipamento['status'] !== 'fora_uso'): ?>
                                    <a href="marcar_fora_uso.php?id=<?php echo $equipamento['id']; ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Marcar este equipamento como fora de uso? Esta ação é irreversível.')"
                                       title="Marcar como Fora de Uso">
                                        <i class="fas fa-times-circle"></i>
                                    </a>
                                <?php endif; ?>

                                <button type="button" class="btn btn-sm btn-secondary"
                                        onclick="showEquipmentDetails(<?php echo htmlspecialchars(json_encode($equipamento)); ?>)"
                                        title="Ver Detalhes">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="page-footer">
        <p>
            <strong>Filtro aplicado:</strong>
            <?php
            if ($colaboradorId && isset($mapaColaboradores[$colaboradorId])) {
                echo 'Colaborador: ' . htmlspecialchars($mapaColaboradores[$colaboradorId]['nome']);
            } elseif ($filtro !== 'todos') {
                echo 'Status: ' . getStatusTexto($filtro);
            } elseif ($status !== 'todos') {
                echo 'Status: ' . getStatusTexto($status);
            } else {
                echo 'Todos os equipamentos';
            }
            if ($tipo !== 'todos') echo ' | Tipo: ' . getTipoTexto($tipo);
            if ($centro_custo !== 'todos') echo ' | Centro de Custo: ' . htmlspecialchars($centro_custo);
            if ($busca) echo ' | Busca: "' . htmlspecialchars($busca) . '"';
            ?>
        </p>
        <p>
            <strong>Resultados:</strong>
            <?php echo $totalFiltrado; ?> de <?php echo $totalEquipamentos; ?> equipamentos |
            Em estoque: <strong><?php echo $estoqueFiltrado; ?></strong> |
            Alocados: <strong><?php echo $alocadosFiltrado; ?></strong> |
            Emprestados: <strong><?php echo $emprestadosFiltrado; ?></strong> |
            Em manutenção: <strong><?php echo $manutencaoFiltrado; ?></strong> |
            Fora de uso: <strong><?php echo $foraUsoFiltrado; ?></strong>
        </p>
    </div>
</main>

<!-- Modal de Detalhes -->
<div id="equipmentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Detalhes do Equipamento</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Conteúdo será preenchido por JavaScript -->
        </div>
    </div>
</div>

<style>
.empty-state {
    text-align: center;
    padding: 40px;
    color: var(--gray-color);
}

.empty-state i {
    margin-bottom: 15px;
    color: var(--light-color);
}

.empty-state h4 {
    margin-bottom: 10px;
    color: var(--dark-color);
}

.stat-percent {
    display: block;
    font-size: 12px;
    color: #666;
    margin-top: 2px;
}

.page-footer {
    background: #f8f9fa;
    padding: 15px;
    border-radius: var(--border-radius);
    margin-top: 20px;
    border-top: 1px solid #dee2e6;
}

.page-footer p {
    margin: 5px 0;
}

/* Layout dos filtros em grid */
.filter-form {
    margin-top: 15px;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
    align-items: center;
}

.filter-group {
    margin-bottom: 0;
}

.filter-search {
    grid-column: span 2;
}

.search-box {
    display: flex;
    gap: 5px;
}

/*
.search-box .form-control {
    flex: 1;
}
*/

.filter-actions {
    display: flex;
    gap: 10px;
}

/* Para telas menores, ajustar o grid */
@media (max-width: 768px) {
    .filter-grid {
        grid-template-columns: 1fr;
    }

    .filter-search {
        grid-column: span 1;
    }

    .filter-actions {
        flex-direction: column;
        align-items: stretch;
    }
}

/* Status badges corrigidos */
.status-info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.status-ativo {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-inativo {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.status-warning {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.status-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Dropdown styles */
.dropdown {
    display: inline-block;
    position: relative;
}

.dropdown-toggle {
    cursor: pointer;
}

.dropdown-menu {
    display: none;
    position: absolute;
    background: white;
    min-width: 220px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: var(--border-radius);
    z-index: 1000;
    padding: 5px 0;
    right: 0;
}

.dropdown:hover .dropdown-menu {
    display: block;
}

.dropdown-item {
    display: block;
    padding: 8px 15px;
    color: var(--dark-color);
    text-decoration: none;
    transition: background 0.2s;
    white-space: nowrap;
}

.dropdown-item:hover {
    background: #f8f9fa;
    color: var(--primary-color);
}

.dropdown-item i {
    margin-right: 8px;
    width: 16px;
    text-align: center;
}

/* Abas de filtro */
.filter-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 10px;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 5px;
}

.filter-tab {
    padding: 8px 15px;
    background: #f8f9fa;
    border-radius: 5px 5px 0 0;
    text-decoration: none;
    color: var(--dark-color);
    font-weight: 500;
    transition: all 0.3s;
    border: 1px solid transparent;
    border-bottom: none;
    margin-bottom: -2px;
}

.filter-tab:hover {
    background: #e9ecef;
    color: var(--primary-color);
}

.filter-tab.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

/* Cartões de estatísticas */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.stat-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: #e9ecef;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.stat-content {
    flex: 1;
}

.stat-content h3 {
    margin: 0 0 5px 0;
    font-size: 14px;
    color: #666;
}

.stat-number {
    margin: 0;
    font-size: 24px;
    font-weight: bold;
}

/* Cores dos cards */
.stat-primary .stat-icon {
    background: var(--primary-color);
    color: white;
}

.stat-success .stat-icon {
    background: var(--success-color);
    color: white;
}

.stat-info .stat-icon {
    background: var(--info-color);
    color: white;
}

.stat-warning .stat-icon {
    background: var(--warning-color);
    color: #212529;
}

.stat-danger .stat-icon {
    background: var(--danger-color);
    color: white;
}

.stat-secondary .stat-icon {
    background: #6c757d;
    color: white;
}

.stat-primary .stat-number {
    color: var(--primary-color);
}

.stat-success .stat-number {
    color: var(--success-color);
}

.stat-info .stat-number {
    color: var(--info-color);
}

.stat-warning .stat-number {
    color: var(--warning-color);
}

.stat-danger .stat-number {
    color: var(--danger-color);
}

.stat-secondary .stat-number {
    color: #6c757d;
}

/* Modal de detalhes */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    width: 80%;
    max-width: 800px;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}

.modal-header {
    padding: 20px;
    background: var(--primary-color);
    color: white;
    border-radius: 8px 8px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 28px;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-body {
    padding: 20px;
}

/* Estilos para os detalhes do equipamento */
.equipment-details {
    font-family: inherit;
}

.detail-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 15px;
}

.detail-item {
    flex: 1;
    min-width: 200px;
}

.detail-item.full-width {
    flex: 0 0 100%;
}

.detail-item strong {
    display: block;
    margin-bottom: 5px;
    color: #555;
}

.historico-list {
    margin-top: 20px;
}

.historico-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 10px;
    border-left: 4px solid var(--warning-color);
}

.historico-item strong {
    color: var(--dark-color);
}

.historico-item small {
    color: #666;
    display: block;
    margin-top: 5px;
}
</style>

<script>
function showEquipmentDetails(equipamento) {
    // Formatar dados para exibição
    let historico = '';
    if (equipamento.historico_manutencao && equipamento.historico_manutencao.length > 0) {
        historico = '<h4>Histórico de Manutenção</h4><div class="historico-list">';
        equipamento.historico_manutencao.forEach(item => {
            historico += `
                <div class="historico-item">
                    <strong>${formatarData(item.data_envio)}</strong>
                    ${item.data_retorno ? ` até ${formatarData(item.data_retorno)}` : '(em andamento)'}
                    <br><small>Problema: ${item.problema || '---'}</small>
                    ${item.resultado ? `<br><small>Resultado: ${item.resultado}</small>` : ''}
                </div>
            `;
        });
        historico += '</div>';
    } else {
        historico = '<p>Nenhuma manutenção registrada.</p>';
    }

    const content = `
        <div class="equipment-details">
            <div class="detail-row">
                <div class="detail-item">
                    <strong>Patrimônio:</strong> ${equipamento.patrimonio}
                </div>
                <div class="detail-item">
                    <strong>Tipo:</strong> ${getTipoText(