<?php
session_start();
require_once '../includes/funcoes.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$mensagem = '';
$tipoMensagem = '';

$colaboradores = lerArquivoJSON('../data/colaboradores/ativos.json');

// Criar mapa de colaboradores para buscar CC rapidamente
$mapaColaboradores = [];
foreach ($colaboradores as $c) {
    $mapaColaboradores[$c['id']] = $c;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero        = preg_replace('/[^0-9]/', '', $_POST['numero'] ?? '');
    $tipo          = $_POST['tipo'] ?? 'chip';
    $status        = $_POST['status'] ?? 'disponivel';
    $colaborador_id = !empty($_POST['colaborador_id']) ? $_POST['colaborador_id'] : null;
    $observacoes   = trim($_POST['observacoes'] ?? '');
    $imei          = trim($_POST['imei'] ?? '');

    // Centro de custo: do colaborador se alocado, senão 11001
    if ($status === 'alocado' && $colaborador_id && isset($mapaColaboradores[$colaborador_id])) {
        $centro_custo = $mapaColaboradores[$colaborador_id]['centro_custo'] ?? '11001';
    } else {
        $centro_custo = '11001';
    }

    $erros = [];

    if (empty($numero)) {
        $erros[] = 'O número da linha é obrigatório.';
    } elseif (!validarTelefone($numero)) {
        $erros[] = 'Número inválido. Use o formato DDD + número (ex: 16 99999-9999)';
    }

    // Validar IMEI se informado (15 dígitos)
    if (!empty($imei)) {
        $imeiLimpo = preg_replace('/[^0-9]/', '', $imei);
        if (strlen($imeiLimpo) !== 15) {
            $erros[] = 'IMEI inválido. Deve conter exatamente 15 dígitos.';
        }
    }

    if ($status === 'alocado' && empty($colaborador_id)) {
        $erros[] = 'Selecione um colaborador para alocar a linha.';
    }

    $linhas = lerArquivoJSON('../data/linhas.json');
    foreach ($linhas as $linha) {
        if ($linha['numero'] === $numero) {
            $erros[] = 'Este número já está cadastrado no sistema.';
            break;
        }
        // Verificar IMEI duplicado
        if (!empty($imei) && isset($linha['imei']) && $linha['imei'] === $imei) {
            $erros[] = 'Este IMEI já está cadastrado no sistema.';
            break;
        }
    }

    if (empty($erros)) {
        $novaLinha = [
            'id'             => gerarId($linhas),
            'numero'         => $numero,
            'tipo'           => $tipo,
            'centro_custo'   => $centro_custo,
            'status'         => $status,
            'colaborador_id' => ($status === 'alocado') ? $colaborador_id : null,
            'imei'           => !empty($imei) ? preg_replace('/[^0-9]/', '', $imei) : null,
            'observacoes'    => $observacoes,
            'data_cadastro'  => date('Y-m-d H:i:s'),
            'data_atualizacao' => date('Y-m-d H:i:s')
        ];

        $linhas[] = $novaLinha;

        if (salvarArquivoJSON('../data/linhas.json', $linhas)) {
            $mensagem = 'Linha cadastrada com sucesso! Centro de custo: ' . $centro_custo;
            $tipoMensagem = 'success';
            $_POST = [];
            $colaborador_id = null;
            $status = 'disponivel';
        } else {
            $mensagem = 'Erro ao salvar a linha. Tente novamente.';
            $tipoMensagem = 'error';
        }
    } else {
        $mensagem = implode('<br>', $erros);
        $tipoMensagem = 'error';
    }
}

// CC preview para exibir no formulário
$ccPreview = '11001';
if (!empty($_POST['colaborador_id']) && isset($mapaColaboradores[$_POST['colaborador_id']])) {
    $ccPreview = $mapaColaboradores[$_POST['colaborador_id']]['centro_custo'] ?? '11001';
}

// Passar mapa de CCs para JS
$ccMap = [];
foreach ($colaboradores as $c) {
    $ccMap[$c['id']] = $c['centro_custo'] ?? '11001';
}
?>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Linha - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/linhas/adicionar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/favicon/favicon.png">
</head>
<body>

<!-- HEADER -->
<header class="header">
    <div class="header-content">
        <div class="logo">
            <a href="../index.php">
                <i class="fas fa-phone"></i>
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
            <h1><i class="fas fa-plus-circle"></i> Adicionar Linha</h1>
            <p class="page-subtitle">Cadastre uma nova linha Vivo (Chip ou E-Chip)</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="form-card-container">
        <form method="POST" action="" class="form-card" id="form-linha">
            <div class="form-grid">

                <!-- Número -->
                <div class="form-group">
                    <label for="numero"><i class="fas fa-phone"></i> Número da Linha <span class="required">*</span></label>
                    <input type="text"
                           id="numero"
                           name="numero"
                           value="<?php echo htmlspecialchars($_POST['numero'] ?? ''); ?>"
                           required
                           class="form-control"
                           placeholder="16 99999-9999"
                           maxlength="14">
                    <small class="form-text">Formato: DDD + número (ex: 16 99999-9999)</small>
                </div>

                <!-- IMEI -->
                <div class="form-group">
                    <label for="imei"><i class="fas fa-microchip"></i> IMEI</label>
                    <input type="text"
                           id="imei"
                           name="imei"
                           value="<?php echo htmlspecialchars($_POST['imei'] ?? ''); ?>"
                           class="form-control"
                           placeholder="123456789012345"
                           maxlength="15">
                    <small class="form-text">Opcional - 15 dígitos. Apenas números.</small>
                </div>

                <!-- Tipo -->
                <div class="form-group">
                    <label for="tipo"><i class="fas fa-sim-card"></i> Tipo de Linha <span class="required">*</span></label>
                    <select id="tipo" name="tipo" required class="form-control">
                        <option value="chip"  <?php echo ($_POST['tipo'] ?? 'chip') === 'chip'  ? 'selected' : ''; ?>>Chip Físico</option>
                        <option value="echip" <?php echo ($_POST['tipo'] ?? '')      === 'echip' ? 'selected' : ''; ?>>E-Chip (eSIM)</option>
                    </select>
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label for="status"><i class="fas fa-circle"></i> Status <span class="required">*</span></label>
                    <select id="status" name="status" required class="form-control" onchange="toggleColaborador()">
                        <option value="disponivel" <?php echo ($_POST['status'] ?? 'disponivel') === 'disponivel' ? 'selected' : ''; ?>>Disponível</option>
                        <option value="alocado"    <?php echo ($_POST['status'] ?? '')            === 'alocado'    ? 'selected' : ''; ?>>Alocado</option>
                    </select>
                </div>

                <!-- Centro de Custo (informativo, sem input) -->
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Centro de Custo</label>
                    <div class="cc-info-box" id="cc-info-box">
                        <span class="cc-badge" id="cc-valor"><?php echo htmlspecialchars($ccPreview); ?></span>
                        <span class="cc-desc" id="cc-desc">
                            <?php echo $ccPreview === '11001' ? 'Padrão — sem colaborador alocado' : 'Do colaborador selecionado'; ?>
                        </span>
                    </div>
                </div>

                <!-- Colaborador (só aparece se alocado) -->
                <div class="form-group full-width" id="colaborador-group" style="display: <?php echo (($_POST['status'] ?? 'disponivel') === 'alocado') ? 'block' : 'none'; ?>;">
                    <label for="colaborador_id"><i class="fas fa-user"></i> Colaborador <span class="required">*</span></label>
                    <select id="colaborador_id" name="colaborador_id" class="form-control" onchange="atualizarCC()">
                        <option value="">Selecione um colaborador</option>
                        <?php foreach ($colaboradores as $colaborador): ?>
                            <option value="<?php echo $colaborador['id']; ?>"
                                    data-cc="<?php echo htmlspecialchars($colaborador['centro_custo'] ?? '11001'); ?>"
                                    <?php echo (isset($_POST['colaborador_id']) && $_POST['colaborador_id'] == $colaborador['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($colaborador['nome'] . ' — ' . ($colaborador['cargo'] ?? '') . ' | CC: ' . ($colaborador['centro_custo'] ?? '11001')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Observações -->
                <div class="form-group full-width">
                    <label for="observacoes"><i class="fas fa-sticky-note"></i> Observações</label>
                    <textarea id="observacoes" name="observacoes" class="form-control" rows="3" placeholder="Observações sobre a linha..."><?php echo htmlspecialchars($_POST['observacoes'] ?? ''); ?></textarea>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Linha</button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()"><i class="fas fa-redo"></i> Limpar</button>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            </div>
        </form>
    </div>
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
                <li><a href="index.php"><i class="fas fa-phone"></i> Linhas</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Estatísticas</h3>
            <div class="footer-stats">
                <div class="footer-stat"><span class="stat-number"><?php echo count(lerArquivoJSON('../data/linhas.json')); ?></span><span class="stat-label">Linhas</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo count($colaboradores); ?></span><span class="stat-label">Colaboradores</span></div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
        <p>Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
</footer>

<script>
    const CC_PADRAO = '11001';

    function toggleColaborador() {
        const status = document.getElementById('status').value;
        const grupo  = document.getElementById('colaborador-group');
        const select = document.getElementById('colaborador_id');

        if (status === 'alocado') {
            grupo.style.display = 'block';
            select.required = true;
        } else {
            grupo.style.display = 'none';
            select.required = false;
            select.value = '';
        }
        atualizarCC();
    }

    function atualizarCC() {
        const status   = document.getElementById('status').value;
        const select   = document.getElementById('colaborador_id');
        const ccValor  = document.getElementById('cc-valor');
        const ccDesc   = document.getElementById('cc-desc');
        const opt      = select.options[select.selectedIndex];

        let cc, desc;
        if (status === 'alocado' && opt && opt.value) {
            cc   = opt.getAttribute('data-cc') || CC_PADRAO;
            desc = 'Do colaborador selecionado';
        } else {
            cc   = CC_PADRAO;
            desc = 'Padrão — sem colaborador alocado';
        }

        ccValor.textContent = cc;
        ccDesc.textContent  = desc;
    }

    function resetForm() {
        if (confirm('Limpar todos os campos?')) {
            document.getElementById('form-linha').reset();
            toggleColaborador();
        }
    }

    // Máscara telefone
    document.getElementById('numero').addEventListener('input', function(e) {
        let v = e.target.value.replace(/\D/g, '').substring(0, 11);
        if (v.length === 11) v = v.replace(/(\d{2})(\d{5})(\d{4})/, '$1 $2-$3');
        else if (v.length === 10) v = v.replace(/(\d{2})(\d{4})(\d{4})/, '$1 $2-$3');
        e.target.value = v;
    });

    // Máscara IMEI (apenas números)
    document.getElementById('imei').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '').substring(0, 15);
    });

    // Auto-fechar alerta
    setTimeout(function() {
        const a = document.querySelector('.global-alert');
        if (a) { a.style.animation = 'slideOut 0.3s ease'; setTimeout(() => a.remove(), 300); }
    }, 5000);
</script>
</body>
</html>