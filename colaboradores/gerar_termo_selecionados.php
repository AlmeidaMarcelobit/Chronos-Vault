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

// Configurações para impressão
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termo de Responsabilidade - Entrega</title>
    <link rel="stylesheet" href="../css/colaboradores/gerar_termo_selecionados.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
<!-- Botões de ação -->
<div class="termo-actions no-print">
    <button onclick="window.print()" class="btn-print">
        <i class="fas fa-print"></i> Imprimir Termo
    </button>

    <button onclick="window.location.href='selecionar_equipamentos_termo.php?id=<?php echo $id; ?>'" class="btn-back">
        <i class="fas fa-arrow-left"></i> Selecionar Outros
    </button>

    <button onclick="window.location.href='../index.php'" class="btn-back">
        <i class="fas fa-home"></i> Voltar ao Início
    </button>
</div>

<!-- Conteúdo do Termo -->
<div class="termo-container">
    <?php if (!empty($equipamentosColaborador)): ?>
        <div class="equipamento-count">
            <?php echo count($equipamentosColaborador); ?> equipamento(s)
        </div>
    <?php endif; ?>

    <div class="termo-header">
        <!-- IMAGEM PARA ENTREGA - PODE SER A MESMA OU DIFERENTE -->
        <img src="../img/logo_impressao_08_01_2026.png" alt="Logo">
        <div class="termo-title">TERMO DE RESPONSABILIDADE</div>
        <div class="termo-subtitle">Entrega de Equipamentos</div>
    </div>

    <div class="termo-section">
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

    <div class="termo-section">
        <div class="section-title">2. EQUIPAMENTOS SELECIONADOS PARA O TERMO</div>

        <?php if (empty($equipamentosColaborador)): ?>
            <div style="text-align: center; padding: 30px; background: #f8f9fa; border-radius: 8px;">
                <i class="fas fa-laptop" style="font-size: 48px; color: #6c757d; margin-bottom: 15px;"></i>
                <h4>Nenhum equipamento selecionado</h4>
            </div>
        <?php else: ?>
            <table class="termo-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Patrimônio</th>
                    <th>Tipo</th>
                    <th>Marca/Modelo</th>
                    <th>Nº Série</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $contador = 1;
                foreach ($equipamentosColaborador as $equipamento):
                    ?>
                    <tr>
                        <td><?php echo $contador++; ?></td>
                        <td><strong><?php echo htmlspecialchars($equipamento['patrimonio']); ?></strong></td>
                        <td><?php echo getTipoTexto($equipamento['tipo']); ?></td>
                        <td><?php echo htmlspecialchars($equipamento['marca'] . ' ' . $equipamento['modelo']); ?></td>
                        <td><?php echo !empty($equipamento['serial']) ? htmlspecialchars($equipamento['serial']) : '---'; ?></td>
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

    <div class="termo-section">
        <div class="section-title">3. TERMO DE RESPONSABILIDADE</div>
        <div class="termo-text">
            <div class="clausula">
                <div class="clausula-title">CLÁUSULA 1 - ACEITAÇÃO</div>
                <p>Eu, <strong><?php echo htmlspecialchars($colaborador['nome']); ?></strong>, CPF
                    <strong><?php echo formatarCPF($colaborador['cpf']); ?></strong>, declaro ter recebido os
                    equipamentos listados acima da empresa AMOR SAÚDE LTDA, CNPJ 27.602.235/0001-00 a título de
                    empréstimo, em perfeitas condições de uso, e assumo total responsabilidade por sua guarda,
                    conservação e uso adequado.
                </p>
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
                    <li>Devolver todos os equipamentos listados acima quando solicitado ou ao término do vínculo
                        empregatício.
                    </li>
                </ol>
            </div>

            <div class="clausula">
                <div class="clausula-title">CLÁUSULA 3 - RESPONSABILIDADES</div>
                <p>Em caso de danos por mau uso, negligência ou falta de cuidado, assumo total responsabilidade pelos
                    custos de reparo ou substituição dos equipamentos listados. Em caso de perda ou roubo, comprometo-me
                    a comunicar imediatamente às autoridades competentes e ao departamento de TI, apresentando o boletim
                    de ocorrência.</p>
            </div>

            <div class="clausula">
                <div class="clausula-title">CLÁUSULA 4 - DEVOLUÇÃO</div>
                <p>Ao término do vínculo empregatício ou quando solicitado, devolverei todos os equipamentos
                    relacionados neste termo, com seus acessórios e documentos, em perfeito estado de conservação e
                    funcionamento.</p>
            </div>
        </div>
    </div>

    <div class="termo-assinaturas">
        <div class="assinatura-box">
            <div class="linha-assinatura"></div>
            <div class="nome-assinatura"><?php echo htmlspecialchars($colaborador['nome']); ?></div>
            <div class="nome-assinatura">________________________________________________</div>
            <div class="cargo-assinatura">Colaborador</div>
            <div class="data-assinatura">Data: ______/______/______</div>
            <div class="data-assinatura">CPF: <?php echo formatarCPF($colaborador['cpf']); ?></div>
        </div>

        <div class="assinatura-box">
            <div class="linha-assinatura"></div>
            <div class="cargo-assinatura">Responsável pelo Patrimônio</div>
            <div class="nome-assinatura">________________________________________________</div>
            <div class="data-assinatura">Nome / Assinatura</div>
            <div class="data-assinatura">Data: ______/______/______</div>
        </div>
    </div>
</div>
</body>
</html>