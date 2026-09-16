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
        <link rel="stylesheet" href="../css/colaboradores/gerar_termo_devolucao.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="icon" href="../img/favicon/favicon.png">
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
        <div class="termo-header">
            <img src="../img/logo_impressao_08_01_2026.png" alt="Logo">
            <div class="termo-title">TERMO DE DEVOLUÇÃO DE EQUIPAMENTO</div>
            <div class="termo-subtitle">Documento de Registro de Devolução</div>
        </div>

        <div class="termo-section">
            <div class="section-title">DEVOLVIDO POR</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Nome:</span>
                    <span class="info-value"><?php echo htmlspecialchars($colaborador['nome']); ?></span>
                </div>

                <div class="info-item">
                    <span class="info-label">Cargo:</span>
                    <span class="info-value"><?php echo htmlspecialchars($colaborador['cargo']); ?></span>
                </div>

                <div class="info-item">
                    <span class="info-label">CPF:</span>
                    <span class="info-value"><?php echo formatarCPF($colaborador['cpf']); ?></span>
                </div>
            </div>
        </div>

        <div class="termo-section">
            <div class="section-title">EQUIPAMENTOS DEVOLVIDOS (<?php echo count($equipamentosDevolucao); ?>)</div>

            <?php if (empty($equipamentosDevolucao)): ?>
                <div style="text-align: center; padding: 30px; background: #f8f9fa; border-radius: 8px;">
                    <i class="fas fa-box-open" style="font-size: 48px; color: #6c757d; margin-bottom: 15px;"></i>
                    <h4>Nenhum equipamento selecionado</h4>
                </div>
            <?php else: ?>
                <table class="termo-table devolucao">
                    <thead>
                    <tr>
                        <th width="25%">DESCRIÇÃO</th>
                        <th width="25%">MARCA/MODELO</th>
                        <th width="20%">S/N</th>
                        <th width="20%">PATRIMÔNIO</th>
                        <th width="10%">STATUS</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($equipamentosDevolucao as $equipamento): ?>
                        <tr>
                            <td data-label="DESCRIÇÃO"><?php echo obterDescricaoResumida($equipamento['tipo']); ?></td>
                            <td data-label="MARCA/MODELO"><?php echo htmlspecialchars(substr($equipamento['marca'] . ' ' . $equipamento['modelo'], 0, 30)); ?></td>
                            <td data-label="S/N"><?php echo !empty($equipamento['serial']) ? htmlspecialchars(substr($equipamento['serial'], 0, 15)) : '---'; ?></td>
                            <td data-label="PATRIMÔNIO"><strong><?php echo htmlspecialchars($equipamento['patrimonio']); ?></strong></td>
                            <td data-label="STATUS"><span class="status-badge status-<?php echo $equipamento['status']; ?>"><?php echo obterStatusResumido($equipamento['status']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="termo-section">
            <div class="section-title">CONDIÇÕES DE DEVOLUÇÃO</div>
            <div class="termo-condicoes">
                <div class="condicoes-title">Devolvido em <?php echo date('d/m/Y'); ?> às <?php echo date('H:i'); ?>h</div>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" id="condicao1">
                        <label for="condicao1"><i class="fas fa-check-circle"></i> Em perfeito estado</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="condicao2">
                        <label for="condicao2"><i class="fas fa-tools"></i> Com defeito</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="condicao3">
                        <label for="condicao3"><i class="fas fa-puzzle-piece"></i> Falta acessórios</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="condicao4">
                        <label for="condicao4"><i class="fas fa-battery-full"></i> Carregador incluso</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="termo-assinaturas">
            <div class="assinatura-box">
                <div class="linha-assinatura"></div>
                <div class="nome-assinatura"><?php echo htmlspecialchars($colaborador['nome']); ?></div>
                <div class="nome-assinatura">________________________________________________</div>
                <div class="cargo-assinatura">Colaborador</div>
                <div class="data-assinatura">Data: ___/___/______</div>
            </div>

            <div class="assinatura-box">
                <div class="linha-assinatura"></div>
                <div class="nome-assinatura">________________________________________________</div>
                <div class="cargo-assinatura">Responsável pelo Recebimento</div>
                <div class="nome-assinatura">Assinatura</div>
                <div class="data-assinatura">Data: ___/___/______</div>
            </div>
        </div>

        <div class="termo-footer">
            <div class="footer-text">Documento gerado em <?php echo date('d/m/Y H:i:s'); ?></div>
        </div>
    </div>

    <script>
        window.onbeforeprint = function() {
            document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.style.opacity = '0.6';
            });
        };
        window.onafterprint = function() {
            document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.style.opacity = '1';
            });
        };
    </script>
    </body>
    </html>

<?php
// Funções auxiliares
function obterDescricaoResumida($tipo) {
    $abreviacoes = [
            'Notebook' => 'NB',
            'Desktop' => 'PC',
            'Monitor' => 'MON',
            'Impressora' => 'IMP',
            'Tablet' => 'TAB',
            'Smartphone' => 'FONE',
            'Roteador' => 'ROT',
            'Acessório' => 'ACESS',
            'Outro' => 'OUT'
    ];
    $tipoTexto = getTipoTexto($tipo);
    return $abreviacoes[$tipoTexto] ?? substr($tipoTexto, 0, 8);
}

function obterStatusResumido($status) {
    $statusCompacto = [
            'Em Estoque' => 'ESTOQUE',
            'Alocado' => 'ALOCADO',
            'Emprestado' => 'EMP',
            'Em Manutenção' => 'MANUT',
            'Fora de Uso' => 'USO'
    ];
    $statusTexto = getStatusTexto($status);
    return $statusCompacto[$statusTexto] ?? substr($statusTexto, 0, 6);
}
?>