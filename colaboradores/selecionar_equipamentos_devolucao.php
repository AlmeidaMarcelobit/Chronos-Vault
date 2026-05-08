<?php
session_start();
require_once '../includes/funcoes.php';
verificarSessao();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php'); // Redireciona para a lista de colaboradores
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
    header('Location: index.php'); // Redireciona para a lista de colaboradores
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
    $_SESSION['devolucao_equipamentos_selecionados'] = $equipamentosSelecionados;
    $_SESSION['devolucao_colaborador_id'] = $id;

    // Redirecionar para gerar termo de devolução
    header('Location: gerar_termo_devolucao.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecionar Equipamentos - Termo de Devolução</title>
    <link rel="stylesheet" href="../css/termos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .selecao-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .selecao-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eaeaea;
        }

        .selecao-header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .info-colaborador {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .checkbox-all {
            background: #e3f2fd;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .selected-count {
            background: #2196f3;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.9em;
        }

        .equipamento-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #eaeaea;
            border-radius: 6px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .equipamento-item:hover {
            background: #f8f9fa;
            border-color: #2196f3;
        }

        .equipamento-checkbox {
            margin-right: 15px;
        }

        .equipamento-tipo {
            background: #e3f2fd;
            color: #1565c0;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8em;
            margin-right: 10px;
        }

        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eaeaea;
        }

        .btn-voltar {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-voltar:hover {
            background: #5a6268;
            color: white;
        }

        .btn-selecionar {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 1em;
        }

        .btn-selecionar:disabled {
            background: #cccccc;
            cursor: not-allowed;
        }

        .btn-selecionar:hover:not(:disabled) {
            background: #218838;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
        }

        .empty-state i {
            font-size: 48px;
            color: #6c757d;
            margin-bottom: 20px;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .text-muted {
            color: #6c757d;
        }
    </style>
</head>
<body>
<div class="selecao-container">
    <div class="selecao-header">
        <h1><i class="fas fa-box-open"></i> Selecionar Equipamentos para Termo de Devolução</h1>
        <p class="text-muted">Selecione os equipamentos que serão devolvidos pelo colaborador</p>
    </div>

    <div class="info-colaborador">
        <h3><i class="fas fa-user"></i> Colaborador que está devolvendo</h3>
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
            <h3>Nenhum equipamento para devolução</h3>
            <p>Este colaborador não possui equipamentos para devolver no momento.</p>
            <a href="index.php" class="btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar para Lista
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

            <h3><i class="fas fa-laptop"></i> Equipamentos em Posse do Colaborador</h3>

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
                                | S/N: <?php echo htmlspecialchars($equipamento['serial']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="btn-container">
                <a href="index.php" class="btn-voltar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn-selecionar" id="btnGerarTermo" disabled>
                    <i class="fas fa-file-contract"></i> Gerar Termo de Devolução
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