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
$colaboradores = lerArquivoJSON('../data/colaboradores.json');

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

$mensagem = '';
$tipoMensagem = '';

// Processar edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = preg_replace('/[^0-9]/', '', $_POST['numero'] ?? '');
    $tipo = $_POST['tipo'] ?? 'chip';
    $centro_custo = trim($_POST['centro_custo'] ?? '');
    $status = $_POST['status'] ?? 'disponivel';
    $colaborador_id = !empty($_POST['colaborador_id']) ? $_POST['colaborador_id'] : null;
    $observacoes = trim($_POST['observacoes'] ?? '');

    $erros = [];

    if (empty($numero)) {
        $erros[] = 'O número da linha é obrigatório.';
    } elseif (!validarTelefone($numero)) {
        $erros[] = 'Número de telefone inválido. Use o formato (DDD) 99999-9999';
    }

    if (empty($centro_custo)) {
        $erros[] = 'O centro de custo é obrigatório.';
    }

    // Verificar se número já existe (exceto para a própria linha)
    foreach ($linhas as $index => $linha) {
        if ($index != $linhaIndex && $linha['numero'] === $numero) {
            $erros[] = 'Este número já está cadastrado no sistema.';
            break;
        }
    }

    // Se status for alocado, precisa de colaborador
    if ($status === 'alocado' && empty($colaborador_id)) {
        $erros[] = 'Selecione um colaborador para alocar a linha.';
    }

    if (empty($erros)) {
        $linhas[$linhaIndex]['numero'] = $numero;
        $linhas[$linhaIndex]['tipo'] = $tipo;
        $linhas[$linhaIndex]['centro_custo'] = $centro_custo;
        $linhas[$linhaIndex]['status'] = $status;
        $linhas[$linhaIndex]['colaborador_id'] = ($status === 'alocado') ? $colaborador_id : null;
        $linhas[$linhaIndex]['observacoes'] = $observacoes;
        $linhas[$linhaIndex]['data_atualizacao'] = date('Y-m-d H:i:s');

        if (salvarArquivoJSON('../data/linhas.json', $linhas)) {
            $mensagem = 'Linha atualizada com sucesso!';
            $tipoMensagem = 'success';
            $linhaAtual = $linhas[$linhaIndex];
        } else {
            $mensagem = 'Erro ao atualizar a linha. Tente novamente.';
            $tipoMensagem = 'error';
        }
    } else {
        $mensagem = implode('<br>', $erros);
        $tipoMensagem = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Linha - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/linhas/editar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../img/favicon/favicon.jpg">
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
            <li class="nav-item"><a href="../equipamentos/index.php" class="nav-link"><i class="fas fa-laptop"></i><span>Equipamentos</span></a></li>
            <li class="nav-item"><a href="index.php" class="nav-link active"><i class="fas fa-phone"></i><span>Linhas</span></a></li>
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
            <h1><i class="fas fa-edit"></i> Editar Linha</h1>
            <p class="page-subtitle">Atualize as informações da linha telefônica</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="form-card-container">
        <!-- INFORMAÇÕES DA LINHA -->
        <div class="info-card">
            <h3><i class="fas fa-info-circle"></i> Informações da Linha</h3>
            <div class="info-grid">
                <div class="info-item"><span class="info-label">ID:</span><span class="info-value"><?php echo $linhaAtual['id']; ?></span></div>
                <div class="info-item"><span class="info-label">Data de Cadastro:</span><span class="info-value"><?php echo formatarData($linhaAtual['data_cadastro']); ?></span></div>
                <div class="info-item"><span class="info-label">Última Atualização:</span><span class="info-value"><?php echo isset($linhaAtual['data_atualizacao']) ? formatarData($linhaAtual['data_atualizacao']) : '---'; ?></span></div>
            </div>
        </div>

        <form method="POST" action="" class="form-card" id="form-linha">
            <div class="form-grid">
                <div class="form-group">
                    <label for="numero"><i class="fas fa-phone"></i> Número da Linha <span class="required">*</span></label>
                    <input type="text" id="numero" name="numero" value="<?php echo formatarTelefone($linhaAtual['numero']); ?>" required class="form-control telefone-mask" placeholder="(11) 99999-9999" maxlength="15">
                    <small class="form-text">Formato: (DDD) 99999-9999</small>
                </div>

                <div class="form-group">
                    <label for="tipo"><i class="fas fa-sim-card"></i> Tipo de Linha <span class="required">*</span></label>
                    <select id="tipo" name="tipo" required class="form-control">
                        <option value="chip" <?php echo $linhaAtual['tipo'] == 'chip' ? 'selected' : ''; ?>>Chip Físico</option>
                        <option value="echip" <?php echo $linhaAtual['tipo'] == 'echip' ? 'selected' : ''; ?>>E-Chip (eSIM)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="centro_custo"><i class="fas fa-dollar-sign"></i> Centro de Custo <span class="required">*</span></label>
                    <input type="text" id="centro_custo" name="centro_custo" value="<?php echo htmlspecialchars($linhaAtual['centro_custo']); ?>" required class="form-control" placeholder="Ex: TI001, ADM002">
                </div>

                <div class="form-group">
                    <label for="status"><i class="fas fa-circle"></i> Status <span class="required">*</span></label>
                    <select id="status" name="status" required class="form-control" onchange="toggleColaborador()">
                        <option value="disponivel" <?php echo $linhaAtual['status'] == 'disponivel' ? 'selected' : ''; ?>>Disponível</option>
                        <option value="alocado" <?php echo $linhaAtual['status'] == 'alocado' ? 'selected' : ''; ?>>Alocado</option>
                    </select>
                </div>

                <div class="form-group" id="colaborador-group" style="display: <?php echo $linhaAtual['status'] == 'alocado' ? 'block' : 'none'; ?>;">
                    <label for="colaborador_id"><i class="fas fa-user"></i> Colaborador <span class="required">*</span></label>
                    <select id="colaborador_id" name="colaborador_id" class="form-control">
                        <option value="">Selecione um colaborador</option>
                        <?php foreach ($colaboradores as $colaborador): ?>
                            <option value="<?php echo $colaborador['id']; ?>" <?php echo ($linhaAtual['colaborador_id'] ?? '') == $colaborador['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($colaborador['nome'] . ' - ' . $colaborador['cargo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label for="observacoes"><i class="fas fa-sticky-note"></i> Observações</label>
                    <textarea id="observacoes" name="observacoes" class="form-control" rows="3" placeholder="Observações sobre a linha..."><?php echo htmlspecialchars($linhaAtual['observacoes'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Atualizar Linha</button>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            </div>
        </form>
    </div>
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
        <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
</footer>

<script>
    function toggleColaborador() {
        const status = document.getElementById('status').value;
        const colaboradorGroup = document.getElementById('colaborador-group');
        const colaboradorSelect = document.getElementById('colaborador_id');

        if (status === 'alocado') {
            colaboradorGroup.style.display = 'block';
            colaboradorSelect.required = true;
        } else {
            colaboradorGroup.style.display = 'none';
            colaboradorSelect.required = false;
            colaboradorSelect.value = '';
        }
    }

    // Máscara para telefone
    const telefoneInput = document.getElementById('numero');
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.substring(0, 11);
            if (value.length <= 11) {
                if (value.length === 11) {
                    value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                } else if (value.length === 10) {
                    value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
                }
            }
            e.target.value = value;
        });
    }

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