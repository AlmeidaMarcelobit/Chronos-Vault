<?php
session_start();
require_once '../includes/funcoes.php';
verificarSessao();

// Verificar se há equipamentos selecionados na sessão
if (!isset($_SESSION['termo_equipamentos_selecionados']) || !isset($_SESSION['termo_colaborador_id'])) {
    $_SESSION['mensagem'] = 'Nenhum equipamento selecionado!';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: selecionar_equipamentos_termo.php?id=' . ($_GET['id'] ?? ''));
    exit;
}

$id = $_SESSION['termo_colaborador_id'];
$equipamentosSelecionadosIds = $_SESSION['termo_equipamentos_selecionados'];

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
    unset($_SESSION['termo_equipamentos_selecionados']);
    unset($_SESSION['termo_colaborador_id']);
    header('Location: ../index.php');
    exit;
}

// Buscar equipamentos selecionados
$equipamentosColaborador = [];
foreach ($equipamentos as $equip) {
    if ($equip['colaborador_id'] == $id &&
        in_array($equip['id'], $equipamentosSelecionadosIds)) {
        $equipamentosColaborador[] = $equip;
    }
}

// Limpar sessão após uso
unset($_SESSION['termo_equipamentos_selecionados']);
unset($_SESSION['termo_colaborador_id']);

// Configurações para impressão
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termo de Responsabilidade - Colaborador</title>
    <link rel="stylesheet" href="../css/termo_colaborador.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<!-- Botões de ação -->
<button onclick="window.print()" class="btn-print no-print">
    <i class="fas fa-print"></i> Imprimir Termo
</button>

<a href="selecionar_equipamentos_termo.php?id=<?php echo $id; ?>" class="btn-back no-print">
    <i class="fas fa-arrow-left"></i> Selecionar Outros Equipamentos
</a>

<a href="../index.php" class="btn-back no-print" style="margin-left: 10px;">
    <i class="fas fa-home"></i> Voltar ao Início
</a>

<!-- Conteúdo do Termo -->
<div class="termo-container">
    <div class="header">
        <img src="../img/logo_impressao_08_01_2026.png" alt="">
        <div class="titulo">TERMO DE RESPONSABILIDADE</div>
        <div style="text-align: center; font-size: 14px; color: #666; margin-top: 10px;">
            <i class="fas fa-info-circle"></i>
            Termo gerado com <?php echo count($equipamentosColaborador); ?> equipamento(s) selecionado(s)
        </div>
    </div>

    <div class="section">
        <div class="section-title">1. INFORMAÇÕES DO COLABORADOR</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Nome Completo:</div>
                <div class="info-value"><?php echo htmlspecialchars($colaborador['nome']); ?></div>
            </div>

            <div class="info-item">
                <div class="info-label">CPF:</div>
                <div class="info-value"><?php echo formatarCPF($colaborador['cpf']); ?></div>
            </div>

            <div class="info-item">
                <div class="info-label">Cargo:</div>
                <div class="info-value"><?php echo htmlspecialchars($colaborador['cargo']); ?></div>
            </div>

            <div class="info-item">
                <div class="info-label">Departamento:</div>
                <div class="info-value"><?php echo htmlspecialchars($colaborador['departamento']); ?></div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">2. EQUIPAMENTOS SELECIONADOS PARA O TERMO</div>

        <?php if (empty($equipamentosColaborador)): ?>
            <div class="empty-state">
                <i class="fas fa-laptop"></i>
                <h3>Nenhum equipamento selecionado</h3>
                <p>Não há equipamentos selecionados para incluir no termo.</p>
            </div>
        <?php else: ?>

            <table class="equipamentos-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Patrimônio</th>
                    <th>Tipo</th>
                    <th>Marca/Modelo</th>
                    <th>Nº Série</th>
                    <th>Centro Custo</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $contador = 1;
                $valorTotalEstimado = 0;
                foreach ($equipamentosColaborador as $equipamento):
                    // Valor estimado baseado no tipo
                    $valoresEstimados = [
                        'notebook' => 3500,
                        'desktop' => 2500,
                        'monitor' => 800,
                        'impressora' => 1200,
                        'tablet' => 1500,
                        'smartphone' => 2000,
                        'roteador' => 400,
                        'acessorio' => 200,
                        'outro' => 500
                    ];
                    $valorEstimado = $valoresEstimados[$equipamento['tipo']] ?? 500;
                    $valorTotalEstimado += $valorEstimado;
                    ?>
                    <tr>
                        <td><?php echo $contador++; ?></td>
                        <td><strong><?php echo htmlspecialchars($equipamento['patrimonio']); ?></strong></td>
                        <td><?php echo getTipoTexto($equipamento['tipo']); ?></td>
                        <td><?php echo htmlspecialchars($equipamento['marca'] . ' ' . $equipamento['modelo']); ?></td>
                        <td><?php echo !empty($equipamento['serial']) ? htmlspecialchars($equipamento['serial']) : '---'; ?></td>
                        <td><?php echo htmlspecialchars($equipamento['centro_custo']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $equipamento['status']; ?>">
                                <?php echo getStatusTexto($equipamento['status']); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="observacoes">
                <p><strong>Resumo:</strong></p>
                <p>• Total de equipamentos: <?php echo count($equipamentosColaborador); ?></p>
                <p>• Valor total estimado: R$ <?php echo number_format($valorTotalEstimado, 2, ',', '.'); ?></p>
                <p>• Data de geração do termo: <?php echo date('d/m/Y H:i:s'); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div class="section">
        <div class="section-title">3. TERMO DE RESPONSABILIDADE</div>
        <div class="termo-text">
            <div class="clausula">
                <div class="clausula-title">CLÁUSULA 1 - ACEITAÇÃO</div>
                <p>Eu, <strong><?php echo htmlspecialchars($colaborador['nome']); ?></strong>, CPF <strong><?php echo formatarCPF($colaborador['cpf']); ?></strong>, declaro ter recebido os equipamentos listados acima da empresa AMOR SAÚDE LTDA, CNPJ 27.602.235/0001-00 a título de empréstimo, em perfeitas condições de uso, e assumo total responsabilidade por sua guarda, conservação e uso adequado.</p>
            </div>

            <div class="clausula">
                <div class="clausula-title">CLÁUSULA 2 - OBRIGAÇÕES</div>
                <p>Comprometo-me a:</p>
                <ol style="margin-left: 20px; margin-top: 10px;">
                    <li>Utilizar os equipamentos exclusivamente para fins profissionais;</li>
                    <li>Manter os equipamentos em local seguro e adequado;</li>
                    <li>Realizar a manutenção preventiva conforme orientações do departamento de TI;</li>
                    <li>Comunicar imediatamente qualquer defeito, dano ou necessidade de reparo;</li>
                    <li>Não realizar modificações físicas ou de software sem autorização prévia;</li>
                    <li>Devolver todos os equipamentos listados acima quando solicitado ou ao término do vínculo empregatício.</li>
                </ol>
            </div>

            <div class="clausula">
                <div class="clausula-title">CLÁUSULA 3 - RESPONSABILIDADES</div>
                <p>Em caso de danos por mau uso, negligência ou falta de cuidado, assumo total responsabilidade pelos custos de reparo ou substituição dos equipamentos listados. Em caso de perda ou roubo, comprometo-me a comunicar imediatamente às autoridades competentes e ao departamento de TI, apresentando o boletim de ocorrência.</p>
            </div>

            <div class="clausula">
                <div class="clausula-title">CLÁUSULA 4 - DEVOLUÇÃO</div>
                <p>Ao término do vínculo empregatício ou quando solicitado, devolverei todos os equipamentos relacionados neste termo, com seus acessórios e documentos, em perfeito estado de conservação e funcionamento.</p>
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
            <div class="cargo-assinatura">Responsável pelo Patrimônio</div>
            <div class="data-assinatura">Data: ______/______/______</div>
            <div class="data-assinatura">Departamento de TI</div>
        </div>
    </div>

<!--    <div class="footer-termo">-->
<!--        <p><strong>Observação:</strong> Este termo foi gerado com base na seleção específica de equipamentos realizada em --><?php //echo date('d/m/Y'); ?><!-- e inclui apenas os itens listados acima.</p>-->
<!--    </div>-->
</div>

<script>
    // Adicionar classes CSS dinamicamente para os badges de status
    document.addEventListener('DOMContentLoaded', function() {
        // Adicionar estilos para status
        const style = document.createElement('style');
        style.textContent = `
            .status-alocado { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
            .status-emprestado { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
            .status-manutencao { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
            .status-estoque { background: #e2e3e5; color: #383d41; border: 1px solid #d6d8db; }
            .status-fora_uso { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

            .footer-termo {
                margin-top: 30px;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 5px;
                font-size: 12px;
                color: #666;
                border-left: 4px solid #007bff;
            }

            @media print {
                .termo-container {
                    border: none;
                    padding: 15mm;
                }

                .btn-print, .btn-back {
                    display: none;
                }

                .no-print {
                    display: none !important;
                }
            }
        `;
        document.head.appendChild(style);

        // Configurar impressão
        window.onbeforeprint = function() {
            document.querySelectorAll('.no-print').forEach(el => {
                el.style.display = 'none';
            });
        };
    });
</script>
</body>
</html>