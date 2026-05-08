<?php
session_start();
require_once '../includes/funcoes.php';
verificarSessao();

// Verificar se há equipamentos selecionados na sessão
if (!isset($_SESSION['devolucao_equipamentos_selecionados']) || !isset($_SESSION['devolucao_colaborador_id'])) {
    $_SESSION['mensagem'] = 'Nenhum equipamento selecionado para devolução!';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: selecionar_equipamentos_devolucao.php?id=' . ($_GET['id'] ?? ''));
    exit;
}

$id = $_SESSION['devolucao_colaborador_id'];
$equipamentosSelecionadosIds = $_SESSION['devolucao_equipamentos_selecionados'];

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
    unset($_SESSION['devolucao_equipamentos_selecionados']);
    unset($_SESSION['devolucao_colaborador_id']);
    header('Location: ../index.php');
    exit;
}

// Buscar equipamentos selecionados
$equipamentosDevolucao = [];
foreach ($equipamentos as $equip) {
    if ($equip['colaborador_id'] == $id &&
        in_array($equip['id'], $equipamentosSelecionadosIds)) {
        $equipamentosDevolucao[] = $equip;
    }
}

// Configurações para impressão
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termo de Devolução de Equipamento</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Open Sans', Arial, sans-serif;
        }

        body {
            background: #f5f5f5;
            color: #333;
            padding: 20px;
            line-height: 1.5;
        }

        .termo-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
            position: relative;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
        }

        .header img {
            max-width: 200px;
            margin-bottom: 20px;
        }

        .titulo {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .subtitle {
            font-size: 18px;
            color: #7f8c8d;
            margin-top: 10px;
            font-weight: 500;
        }

        .section {
            margin: 30px 0;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #eaeaea;
        }

        .devolucao-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .info-value {
            color: #333;
            font-size: 15px;
            padding: 8px;
            background: white;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .equipamentos-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .equipamentos-table th {
            background: #2c3e50;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }

        .equipamentos-table td {
            padding: 10px;
            border: 1px solid #ddd;
            font-size: 14px;
        }

        .equipamentos-table tr:nth-child(even) {
            background: #f8f9fa;
        }

        .condicoes {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #2c3e50;
        }

        .condicoes-title {
            font-weight: bold;
            margin-bottom: 15px;
            color: #2c3e50;
        }

        .checkbox-group {
            margin: 15px 0;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            margin: 8px 0;
        }

        .checkbox-item input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
        }

        .assinaturas {
            margin-top: 60px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
        }

        .assinatura {
            text-align: center;
            padding-top: 40px;
            position: relative;
        }

        .linha-assinatura {
            width: 100%;
            height: 1px;
            background: #333;
            margin-bottom: 20px;
        }

        .nome-assinatura {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 16px;
        }

        .cargo-assinatura {
            color: #666;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .data-assinatura {
            color: #666;
            font-size: 13px;
            margin-top: 5px;
        }

        .footer-termo {
            margin-top: 40px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }

        /* Botões de ação */
        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
            flex-direction: column;
        }

        .btn-print, .btn-back {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-print {
            background: #2196f3;
            color: white;
        }

        .btn-print:hover {
            background: #0b7dda;
        }

        .btn-back {
            background: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background: #545b62;
        }

        @media print {
            body {
                padding: 0;
                background: white;
            }

            .termo-container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }

            @page {
                margin: 20mm;
            }
        }

        /* Contador de equipamentos */
        .contador-equipamentos {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #2c3e50;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        /* Status do equipamento */
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-alocado {
            background: #e3f2fd;
            color: #1565c0;
        }

        .status-emprestado {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>

<body>
<!-- Botões de ação -->
<div class="no-print">
    <button onclick="window.print()" class="btn-print">
        <i class="fas fa-print"></i> Imprimir Termo
    </button>

    <button onclick="window.location.href='selecionar_equipamentos_devolucao.php?id=<?php echo $id; ?>'" class="btn-back">
        <i class="fas fa-arrow-left"></i> Selecionar Outros
    </button>

    <button onclick="window.location.href='../index.php'" class="btn-back">
        <i class="fas fa-home"></i> Voltar ao Início
    </button>
</div>

<!-- Conteúdo do Termo -->
<div class="termo-container">
    <div class="contador-equipamentos">
        <?php echo count($equipamentosDevolucao); ?> equipamento(s)
    </div>

    <div class="header">
        <div class="titulo">TERMO DE DEVOLUÇÃO DE EQUIPAMENTO</div>
        <div class="subtitle">Documento de Registro de Devolução</div>
    </div>

    <div class="section">
        <div class="section-title">DEVOLVIDO POR</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Nome:</div>
                <div class="info-value"><?php echo htmlspecialchars($colaborador['nome']); ?></div>
            </div>

            <div class="info-item">
                <div class="info-label">Cargo:</div>
                <div class="info-value"><?php echo htmlspecialchars($colaborador['cargo']); ?></div>
            </div>

            <div class="info-item">
                <div class="info-label">CPF:</div>
                <div class="info-value"><?php echo formatarCPF($colaborador['cpf']); ?></div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">EQUIPAMENTOS DEVOLVIDOS</div>

        <?php if (empty($equipamentosDevolucao)): ?>
            <div style="text-align: center; padding: 30px; background: #f8f9fa; border-radius: 8px;">
                <i class="fas fa-box-open" style="font-size: 48px; color: #6c757d; margin-bottom: 15px;"></i>
                <h4>Nenhum equipamento selecionado</h4>
            </div>
        <?php else: ?>
            <table class="equipamentos-table">
                <thead>
                <tr>
                    <th>DESCRIÇÃO</th>
                    <th>MARCA/MODELO</th>
                    <th>S/N</th>
                    <th>PATRIMÔNIO</th>
                    <th>STATUS</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($equipamentosDevolucao as $equipamento): ?>
                    <tr>
                        <td><?php echo getTipoTexto($equipamento['tipo']); ?></td>
                        <td><?php echo htmlspecialchars($equipamento['marca'] . ' ' . $equipamento['modelo']); ?></td>
                        <td><?php echo !empty($equipamento['serial']) ? htmlspecialchars($equipamento['serial']) : '---'; ?></td>
                        <td><strong><?php echo htmlspecialchars($equipamento['patrimonio']); ?></strong></td>
                        <td>
                                <span class="status-badge status-<?php echo $equipamento['status']; ?>">
                                    <?php echo getStatusTexto($equipamento['status']); ?>
                                </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="section">
        <div class="section-title">CONDIÇÕES DE DEVOLUÇÃO</div>
        <div class="condicoes">
            <div class="condicoes-title">Foi devolvido em <?php echo date('d/m/Y'); ?>, nas seguintes condições:</div>

            <div class="checkbox-group">
                <div class="checkbox-item">
                    <input type="checkbox" id="condicao1">
                    <label for="condicao1">( ) Em perfeito estado</label>
                </div>

                <div class="checkbox-item">
                    <input type="checkbox" id="condicao2">
                    <label for="condicao2">( ) Apresentando defeito</label>
                </div>

                <div class="checkbox-item">
                    <input type="checkbox" id="condicao3">
                    <label for="condicao3">( ) Faltando peças/acessórios</label>
                </div>

                <div class="checkbox-item">
                    <input type="checkbox" id="condicao4">
                    <label for="condicao4">( ) Carregador</label>
                </div>
            </div>
        </div>
    </div>

    <div class="assinaturas">
        <div class="assinatura">
            <div class="linha-assinatura"></div>
            <div class="nome-assinatura"><?php echo htmlspecialchars($colaborador['nome']); ?></div>
            <div class="cargo-assinatura">Colaborador</div>
            <div class="data-assinatura">Data: ______/______/______</div>
            <div class="data-assinatura">CPF: <?php echo formatarCPF($colaborador['cpf']); ?></div>
        </div>

        <div class="assinatura">
            <div class="linha-assinatura"></div>
            <div class="cargo-assinatura">Responsável pelo Recebimento</div>
            <div class="nome-assinatura">________________________________________________</div>
            <div class="data-assinatura">Nome / Assinatura</div>
            <div class="data-assinatura">Data: ______/______/______</div>
        </div>
    </div>

<!--    <div class="footer-termo">-->
<!--        <p><strong>Observação:</strong> Este termo foi gerado automaticamente pelo Sistema de Gestão de Equipamentos em --><?php //echo date('d/m/Y H:i:s'); ?><!--</p>-->
<!--        <p><strong>Total de equipamentos:</strong> --><?php //echo count($equipamentosDevolucao); ?><!-- item(ns) devolvido(s)</p>-->
<!--    </div>-->
</div>

<script>
    // Configurar impressão
    window.onbeforeprint = function() {
        // Desmarcar checkboxes temporariamente para impressão mais limpa
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.style.opacity = '0.5';
        });
    };

    window.onafterprint = function() {
        // Restaurar opacidade após impressão
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.style.opacity = '1';
        });
    };

    // Limpar sessão após impressão (opcional)
    window.addEventListener('load', function() {
        // Limpar sessão após 5 minutos de inatividade na página
        setTimeout(function() {
            fetch('../includes/limpar_sessao_devolucao.php')
                .catch(error => console.error('Erro ao limpar sessão:', error));
        }, 300000);
    });
</script>
</body>
</html>