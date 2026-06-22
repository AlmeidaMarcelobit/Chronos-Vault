<?php
session_start();
require_once '../includes/funcoes.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$usuario_nivel = $_SESSION['usuario_nivel'] ?? 'user';
if ($usuario_nivel === 'view') {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Buscar equipamento em todos os status possíveis
$statuses = ['estoque', 'alocado', 'emprestado', 'manutencao'];
$equipamento = null;
$statusOrigem = null;
$indexOrigem = null;

foreach ($statuses as $status) {
    $lista = carregarEquipamentosPorStatus($status);
    foreach ($lista as $i => $e) {
        if ($e['id'] == $id) {
            $equipamento = $e;
            $statusOrigem = $status;
            $indexOrigem = $i;
            break 2;
        }
    }
}

if (!$equipamento) {
    $_SESSION['mensagem'] = 'Equipamento não encontrado ou já está fora de uso.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

$erro = '';

// Processar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $motivo = trim($_POST['motivo'] ?? '');

    if (empty($motivo)) {
        $erro = 'Informe o motivo para marcar como fora de uso.';
    } else {
        // Atualizar equipamento
        $equipamento['colaborador_id'] = null;
        $equipamento['status_anterior'] = $equipamento['status'];
        $equipamento['status'] = 'fora_uso';
        $equipamento['data_fora_uso'] = date('Y-m-d H:i:s');
        $equipamento['motivo_fora_uso'] = $motivo;
        $equipamento['data_atualizacao'] = date('Y-m-d H:i:s');

        // Remover da origem
        $listaOrigem = carregarEquipamentosPorStatus($statusOrigem);
        array_splice($listaOrigem, $indexOrigem, 1);
        $caminhoOrigem = getCaminhoEquipamentoPorStatus($statusOrigem);

        if (!salvarArquivoJSON($caminhoOrigem, $listaOrigem)) {
            $erro = 'Erro ao atualizar status de origem. Tente novamente.';
        } else {
            // Adicionar ao fora_uso
            $foraUso = carregarEquipamentosPorStatus('fora_uso');
            $foraUso[] = $equipamento;
            $caminhoForaUso = getCaminhoEquipamentoPorStatus('fora_uso');

            if (salvarArquivoJSON($caminhoForaUso, $foraUso)) {
                $_SESSION['mensagem'] = 'Equipamento ' . htmlspecialchars($equipamento['patrimonio']) . ' marcado como Fora de Uso.';
                $_SESSION['mensagem_tipo'] = 'success';
                header('Location: index.php');
                exit;
            } else {
                $erro = 'Erro ao salvar. Tente novamente.';
            }
        }
    }
}
?>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marcar Fora de Uso - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/equipamentos/marcar_fora_uso.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/favicon/favicon.png">
</head>
<body>

<header class="header">
    <div class="header-content">
        <div class="logo">
            <a href="../index.php">
                <i class="fas fa-times-circle"></i>
                <h1>Gestão de Equipamentos</h1>
            </a>
        </div>
        <div class="user-menu">
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário'); ?></span>
            </div>
            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </div>
    </div>
</header>
    <nav class="nav-container">
        <ul class="nav-menu">
            <li class="nav-item"><a href="../index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="../colaboradores/index.php" class="nav-link"><i class="fas fa-users"></i><span>Colaboradores</span></a></li>
            <li class="nav-item"><a href="../equipamentos/index.php" class="nav-link active"><i class="fas fa-laptop"></i><span>Equipamentos</span></a></li>
            <li class="nav-item"><a href="../linhas/index.php" class="nav-link"><i class="fas fa-phone"></i><span>Linhas</span></a></li>
            <?php if (($_SESSION['usuario_nivel'] ?? '') === 'admin'): ?>
                <li class="nav-item"><a href="../Termos/index.php" class="nav-link"><i class="fas fa-file-contract"></i><span>Termos</span></a></li>
                <li class="nav-item"><a href="../usuarios/index.php" class="nav-link"><i class="fas fa-user-cog"></i><span>Usuários</span></a></li>
            <?php endif; ?>        </ul>
    </nav>

<main class="main-container">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-times-circle"></i> Marcar como Fora de Uso</h2>
            <p>Esta ação removerá o equipamento da operação</p>
        </div>
        <div class="card-body">

            <div class="alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Atenção: ao confirmar, o equipamento será desassociado de qualquer colaborador e marcado como fora de uso.</span>
            </div>

            <?php if ($erro): ?>
                <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>

            <div class="equip-info">
                <div class="equip-info-item">
                    <span class="equip-info-label">Patrimônio</span>
                    <span class="equip-info-value"><?php echo htmlspecialchars($equipamento['patrimonio']); ?></span>
                </div>
                <div class="equip-info-item">
                    <span class="equip-info-label">Tipo</span>
                    <span class="equip-info-value"><?php echo getTipoTexto($equipamento['tipo']); ?></span>
                </div>
                <div class="equip-info-item">
                    <span class="equip-info-label">Marca / Modelo</span>
                    <span class="equip-info-value"><?php echo htmlspecialchars(($equipamento['marca'] ?? '') . ' ' . ($equipamento['modelo'] ?? '')); ?></span>
                </div>
                <div class="equip-info-item">
                    <span class="equip-info-label">Status atual</span>
                    <span class="equip-info-value"><?php echo getStatusTexto($statusOrigem); ?></span>
                </div>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label for="motivo">Motivo <span class="required">*</span></label>
                    <textarea id="motivo" name="motivo" class="form-control" placeholder="Descreva o motivo para marcar como fora de uso..." required><?php echo htmlspecialchars($_POST['motivo'] ?? ''); ?></textarea>
                </div>
                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Cancelar</a>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-times-circle"></i> Confirmar</button>
                </div>
            </form>

        </div>
    </div>
</main>

</body>
</html>
