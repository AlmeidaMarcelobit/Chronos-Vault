<?php
session_start();
require_once '../includes/funcoes.php';
verificarSessao();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$colaboradores = lerArquivoJSON('../data/colaboradores.json');
$equipamentos = lerArquivoJSON('../data/equipamentos.json');

// Encontrar colaborador
$colaborador = null;
foreach ($colaboradores as $colab) {
    if ($colab['id'] == $id) {
        $colaborador = $colab;
        break;
    }
}

if (!$colaborador) {
    $_SESSION['mensagem'] = 'Colaborador não encontrado!';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

// Buscar equipamentos alocados ao colaborador
$equipamentosColaborador = [];
foreach ($equipamentos as $equip) {
    if ($equip['colaborador_id'] == $id && in_array($equip['status'], ['alocado', 'emprestado'])) {
        $equipamentosColaborador[] = $equip;
    }
}

// Processar seleção
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['equipamentos_selecionados'])) {
    $equipamentosSelecionados = $_POST['equipamentos_selecionados'];

    // Armazenar na sessão
    $_SESSION['termo_equipamentos_selecionados'] = $equipamentosSelecionados;
    $_SESSION['termo_colaborador_id'] = $id;

    // Redirecionar para gerar termo
    header('Location: gerar_termo_selecionados.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecionar Equipamentos - Termo de Responsabilidade</title>
    <link rel="stylesheet" href="../css/termo_colaborador.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>

    </style>
</head>
<body>
<div class="selecao-container">
    <div class="selecao-header">
        <h1><i class="fas fa-clipboard-check"></i> Selecionar Equipamentos para Termo</h1>
        <p class="text-muted">Selecione os equipamentos que deseja incluir no termo de responsabilidade</p>
    </div>

    <div class="info-colaborador">
        <h3><i class="fas fa-user"></i> Colaborador</h3>
        <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 15px;">
            <div>
                <strong>Nome:</strong> <?php echo htmlspecialchars($colaborador['nome']); ?>
            </div>
            <div>
                <strong>CPF:</strong> <?php echo formatarCPF($colaborador['cpf']); ?>
            </div>
            <div>
                <strong>Cargo:</strong> <?php echo htmlspecialchars($colaborador['cargo']); ?>
            </div>
            <div>
                <strong>Departamento:</strong> <?php echo htmlspecialchars($colaborador['departamento']); ?>
            </div>
        </div>
    </div>

    <?php if (empty($equipamentosColaborador)): ?>
        <div class="empty-state">
            <i class="fas fa-laptop" style="font-size: 48px; color: #6c757d; margin-bottom: 20px;"></i>
            <h3>Nenhum equipamento atribuído</h3>
            <p>Este colaborador não possui equipamentos alocados no momento.</p>
            <a href="../index.php" class="btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    <?php else: ?>
        <form method="POST" id="formSelecao">
            <div class="checkbox-all">
                <label style="cursor: pointer;">
                    <input type="checkbox" id="selecionarTodos" onchange="toggleAll()">
                    <strong>Selecionar todos os equipamentos</strong>
                    <span class="selected-count" id="contador">0 selecionados</span>
                </label>
            </div>

            <h3><i class="fas fa-laptop"></i> Equipamentos Disponíveis</h3>

            <?php foreach ($equipamentosColaborador as $equipamento): ?>
                <div class="equipamento-item">
                    <div class="equipamento-checkbox">
                        <input type="checkbox"
                               name="equipamentos_selecionados[]"
                               value="<?php echo $equipamento['id']; ?>"
                               class="checkbox-equipamento"
                               data-patrimonio="<?php echo htmlspecialchars($equipamento['patrimonio']); ?>"
                               onchange="atualizarContador()">
                    </div>
                    <div class="equipamento-info">
                        <div style="display: flex; align-items: center; margin-bottom: 5px;">
                            <span class="equipamento-tipo"><?php echo getTipoTexto($equipamento['tipo']); ?></span>
                            <strong><?php echo htmlspecialchars($equipamento['patrimonio']); ?></strong>
                        </div>
                        <div style="color: #666; font-size: 0.9em;">
                            <?php echo htmlspecialchars($equipamento['marca'] . ' ' . $equipamento['modelo']); ?>
                            <?php if (!empty($equipamento['serial'])): ?>
                                | Série: <?php echo htmlspecialchars($equipamento['serial']); ?>
                            <?php endif; ?>
                            | CC: <?php echo htmlspecialchars($equipamento['centro_custo']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="btn-container">
                <a href="../index.php" class="btn-voltar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn-selecionar" id="btnGerarTermo" disabled>
                    <i class="fas fa-file-contract"></i> Gerar Termo com Itens Selecionados
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
    function toggleAll() {
        const checkAll = document.getElementById('selecionarTodos');
        const checkboxes = document.querySelectorAll('.checkbox-equipamento');
        const btnGerar = document.getElementById('btnGerarTermo');

        checkboxes.forEach(checkbox => {
            checkbox.checked = checkAll.checked;
        });

        atualizarContador();
    }

    function atualizarContador() {
        const checkboxes = document.querySelectorAll('.checkbox-equipamento:checked');
        const contador = document.getElementById('contador');
        const btnGerar = document.getElementById('btnGerarTermo');

        contador.textContent = checkboxes.length + ' selecionado(s)';

        // Habilitar/desabilitar botão
        btnGerar.disabled = checkboxes.length === 0;

        // Atualizar checkbox "selecionar todos"
        const totalCheckboxes = document.querySelectorAll('.checkbox-equipamento').length;
        const checkAll = document.getElementById('selecionarTodos');

        if (checkboxes.length === totalCheckboxes && totalCheckboxes > 0) {
            checkAll.checked = true;
            checkAll.indeterminate = false;
        } else if (checkboxes.length > 0) {
            checkAll.checked = false;
            checkAll.indeterminate = true;
        } else {
            checkAll.checked = false;
            checkAll.indeterminate = false;
        }
    }

    // Inicializar contador
    document.addEventListener('DOMContentLoaded', atualizarContador);
</script>
</body>
</html>