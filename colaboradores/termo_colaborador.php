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

<a href="../index.php" class="btn-back no-print">
    <i class="fas fa-arrow-left"></i> Voltar
</a>

<!-- Conteúdo do Termo -->
<div class="termo-container">
<!--    <div class="qr-code">-->
<!--        <div>CÓDIGO</div>-->
<!--        <div>ID: --><?php //echo str_pad($colaborador['id'], 4, '0', STR_PAD_LEFT); ?><!--</div>-->
        <div><?php echo date('d/m/Y'); ?></div>
<!--    </div>-->

    <div class="header">
        <img src="../img/logo_impressao_08_01_2026.png" alt="">
        <div class="titulo">TERMO DE RESPONSABILIDADE</div>
    </div>

<!--    <div class="section">-->
<!--        <div class="section-title">1. INFORMAÇÕES DO COLABORADOR</div>-->
<!--        <div class="info-grid">-->
<!--            <div class="info-item">-->
<!--                <div class="info-label">Nome Completo:</div>-->
<!--                <div class="info-value">--><?php //echo htmlspecialchars($colaborador['nome']); ?><!--</div>-->
<!--            </div>-->
<!---->
<!--            <div class="info-item">-->
<!--                <div class="info-label">CPF:</div>-->
<!--                <div class="info-value">--><?php //echo formatarCPF($colaborador['cpf']); ?><!--</div>-->
<!--            </div>-->
<!---->
<!--            <div class="info-item">-->
<!--                <div class="info-label">Cargo:</div>-->
<!--                <div class="info-value">--><?php //echo htmlspecialchars($colaborador['cargo']); ?><!--</div>-->
<!--            </div>-->
<!---->
<!--            <div class="info-item">-->
<!--                <div class="info-label">Departamento:</div>-->
<!--                <div class="info-value">--><?php //echo htmlspecialchars($colaborador['departamento']); ?><!--</div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->

    <div class="section">
        <div class="section-title">1. EQUIPAMENTOS ATRIBUÍDOS</div>

        <?php if (empty($equipamentosColaborador)): ?>
            <div class="empty-state">
                <i class="fas fa-laptop"></i>
                <h3>Nenhum equipamento atribuído</h3>
                <p>Este colaborador não possui equipamentos alocados no momento.</p>
            </div>
        <?php else: ?>

            <table class="equipamentos-table">
                <thead>
                <tr>
                    <th>Patrimônio</th>
                    <th>Tipo</th>
                    <th>Marca/Modelo</th>
                    <th>Nº Série</th>
                    <th>Centro Custo</th>
                    <th>Status</th>
<!--                    <th>Data Atribuição</th>-->
                </tr>
                </thead>
                <tbody>
                <?php
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
<!--                        <td>--><?php //echo formatarData($equipamento['data_atribuicao']); ?><!--</td>-->
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

<!--            <div class="observacoes">-->
<!--                <p><strong>Observações:</strong></p>-->
<!--                <p>• Valor total estimado dos equipamentos: R$ --><?php //echo number_format($valorTotalEstimado, 2, ',', '.'); ?><!--</p>-->
<!--                <p>• Equipamentos devem ser utilizados exclusivamente para atividades profissionais</p>-->
<!--                <p>• Em caso de danos, perda ou roubo, comunicar imediatamente ao Departamento de TI</p>-->
<!--            </div>-->
        <?php endif; ?>
    </div>

    <div class="section">
        <div class="section-title">2. TERMO DE RESPONSABILIDADE</div>
        <div class="termo-text">
            <div class="clausula">
                <div class="clausula-title">CLÁUSULA 1 - ACEITAÇÃO</div>
                <p>Eu, <strong><?php echo htmlspecialchars($colaborador['nome']); ?></strong>, CPF <strong><?php echo formatarCPF($colaborador['cpf']); ?></strong>, declaro ter recebido os equipamentos da empresa AMOR SAÚDE LTDA, CNPJ 27.602.235/0001-00 a título de empréstimo relacionados acima, em perfeitas condições de uso, e assumo total responsabilidade por sua guarda, conservação e uso adequado.</p>
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
                    <li>Devolver todos os equipamentos quando solicitado ou ao término do vínculo empregatício.</li>
                </ol>
            </div>

            <div class="clausula">
                <div class="clausula-title">CLÁUSULA 3 - RESPONSABILIDADES</div>
                <p>Em caso de danos por mau uso, negligência ou falta de cuidado, assumo total responsabilidade pelos custos de reparo ou substituição. Em caso de perda ou roubo, comprometo-me a comunicar imediatamente às autoridades competentes e ao departamento de TI, apresentando o boletim de ocorrência.</p>
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

<!--    <div class="carimbo">-->
<!--        Data de emissão: --><?php //echo date('d/m/Y'); ?>
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

        window.onafterprint = function() {
            // Opcional: restaurar elementos após impressão
        };
    });

    // Função para preencher data atual nos campos de data
    function preencherDataAtual() {
        const hoje = new Date();
        const dia = hoje.getDate().toString().padStart(2, '0');
        const mes = (hoje.getMonth() + 1).toString().padStart(2, '0');
        const ano = hoje.getFullYear();

        // Preencher datas nos campos de assinatura
        document.querySelectorAll('.data-assinatura').forEach(el => {
            const text = el.textContent;
            if (text.includes('______')) {
                el.textContent = text.replace('______/______/______', `${dia}/${mes}/${ano}`);
            }
        });
    }

    // Opcional: Chamar esta função se quiser preencher automaticamente
    // preencherDataAtual();
</script>
</body>
</html>