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
    header('Location: index.php');
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
    <link rel="stylesheet" href="../css/termos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
<!-- Botões de ação -->
<div class="termo-actions no-print">
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
    <?php if (!empty($equipamentosDevolucao)): ?>
        <div class="equipamento-count">
            <?php echo count($equipamentosDevolucao); ?> equipamento(s)
        </div>
    <?php endif; ?>

    <div class="termo-header">
        <!-- IMAGEM PARA DEVOLUÇÃO - DIFERENTE DA ENTREGA -->
        <img src="../img/logo_impressao_08_01_2026.png" alt="Logo">
        <div class="termo-title">TERMO DE DEVOLUÇÃO DE EQUIPAMENTO</div>
        <div class="termo-subtitle">Documento de Registro de Devolução</div>
    </div>

    <div class="termo-section">
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

    <div class="termo-section">
        <div class="section-title">EQUIPAMENTOS DEVOLVIDOS</div>

        <?php if (empty($equipamentosDevolucao)): ?>
            <div style="text-align: center; padding: 30px; background: #f8f9fa; border-radius: 8px;">
                <i class="fas fa-box-open" style="font-size: 48px; color: #6c757d; margin-bottom: 15px;"></i>
                <h4>Nenhum equipamento selecionado</h4>
            </div>
        <?php else: ?>
            <table class="termo-table devolucao">
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
                        <!-- DESCRIÇÃO mais curta -->
                        <td><?php
                            // Abrevia tipos longos
                            $tipo = getTipoTexto($equipamento['tipo']);
                            $abreviacoes = [
                                'Notebook' => 'Noteb.',
                                'Desktop' => 'Desktop',
                                'Monitor' => 'Monitor',
                                'Impressora' => 'Impress.',
                                'Tablet' => 'Tablet',
                                'Smartphone' => 'Celular',
                                'Roteador' => 'Roteador',
                                'Acessório' => 'Acess.',
                                'Outro' => 'Outro'
                            ];
                            echo $abreviacoes[$tipo] ?? substr($tipo, 0, 10);
                            ?></td>

                        <!-- MARCA/MODELO em duas linhas se necessário -->
                        <td style="font-size: 11px;">
                            <?php
                            $texto = htmlspecialchars($equipamento['marca'] . ' ' . $equipamento['modelo']);
                            if (strlen($texto) > 25) {
                                echo wordwrap($texto, 25, '<br>', true);
                            } else {
                                echo $texto;
                            }
                            ?>
                        </td>

                        <!-- S/N -->
                        <td><?php
                            $serial = !empty($equipamento['serial']) ? htmlspecialchars($equipamento['serial']) : '---';
                            if (strlen($serial) > 12) {
                                echo substr($serial, 0, 9) . '...';
                            } else {
                                echo $serial;
                            }
                            ?></td>

                        <!-- PATRIMÔNIO -->
                        <td><strong><?php
                                $patrimonio = htmlspecialchars($equipamento['patrimonio']);
                                if (strlen($patrimonio) > 10) {
                                    echo substr($patrimonio, 0, 8) . '...';
                                } else {
                                    echo $patrimonio;
                                }
                                ?></strong></td>

                        <!-- STATUS compacto -->
                        <td>
                <span class="status-badge status-<?php echo $equipamento['status']; ?>" style="font-size: 10px; padding: 1px 4px;">
                    <?php
                    $status = getStatusTexto($equipamento['status']);
                    $statusCompacto = [
                        'Em Estoque' => 'Estoque',
                        'Alocado' => 'Alocado',
                        'Emprestado' => 'Empres.',
                        'Em Manutenção' => 'Manut.',
                        'Fora de Uso' => 'Fora Uso'
                    ];
                    echo $statusCompacto[$status] ?? $status;
                    ?>
                </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="termo-section">
        <div class="section-title">CONDIÇÕES DE DEVOLUÇÃO</div>
        <div class="termo-condicoes">
            <div class="condicoes-title">Foi devolvido em <?php echo date('d/m/Y'); ?>, nas seguintes condições:</div>

            <div class="checkbox-group">
                <div class="checkbox-item">
                    <input type="checkbox" id="condicao1">
                    <label for="condicao1">Em perfeito estado</label>
                </div>

                <div class="checkbox-item">
                    <input type="checkbox" id="condicao2">
                    <label for="condicao2">Apresentando defeito</label>
                </div>

                <div class="checkbox-item">
                    <input type="checkbox" id="condicao3">
                    <label for="condicao3">Faltando peças/acessórios</label>
                </div>

                <div class="checkbox-item">
                    <input type="checkbox" id="condicao4">
                    <label for="condicao4">Carregador</label>
                </div>
            </div>
        </div>
    </div>

    <div class="termo-assinaturas">
        <div class="assinatura-box">
            <div class="linha-assinatura"></div>
            <div class="nome-assinatura"><?php echo htmlspecialchars($colaborador['nome']); ?></div>
            <div class="cargo-assinatura">Colaborador</div>
            <div class="data-assinatura">Data: ______/______/______</div>
            <div class="data-assinatura">CPF: <?php echo formatarCPF($colaborador['cpf']); ?></div>
        </div>

        <div class="assinatura-box">
            <div class="linha-assinatura"></div>
            <div class="cargo-assinatura">Responsável pelo Recebimento</div>
            <div class="nome-assinatura">________________________________________________</div>
            <div class="data-assinatura">Nome / Assinatura</div>
            <div class="data-assinatura">Data: ______/______/______</div>
        </div>
    </div>
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
</script>
</body>
</html>