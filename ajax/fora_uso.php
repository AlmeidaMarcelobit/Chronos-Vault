<?php
session_start();
require_once '../includes/funcoes.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? '';

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID não informado']);
    exit;
}

// Carregar equipamentos de todas as origens possíveis
$statuses = ['estoque', 'alocado', 'emprestado', 'manutencao'];
$equipamentoEncontrado = null;
$statusOrigem = null;
$indexOrigem = null;

foreach ($statuses as $status) {
    $equipamentos = carregarEquipamentosPorStatus($status);
    foreach ($equipamentos as $i => $e) {
        if ($e['id'] == $id) {
            $equipamentoEncontrado = $e;
            $statusOrigem = $status;
            $indexOrigem = $i;
            break 2;
        }
    }
}

if (!$equipamentoEncontrado) {
    echo json_encode(['success' => false, 'message' => 'Equipamento não encontrado']);
    exit;
}

// Desassociar colaborador se houver
$equipamentoEncontrado['colaborador_id'] = null;
$equipamentoEncontrado['status_anterior'] = $equipamentoEncontrado['status'];
$equipamentoEncontrado['status'] = 'fora_uso';
$equipamentoEncontrado['data_fora_uso'] = date('Y-m-d H:i:s');
$equipamentoEncontrado['motivo_fora_uso'] = $data['motivo'] ?? 'Marcado como fora de uso';
$equipamentoEncontrado['data_atualizacao'] = date('Y-m-d H:i:s');

// Remover da origem
$equipamentosOrigem = carregarEquipamentosPorStatus($statusOrigem);
array_splice($equipamentosOrigem, $indexOrigem, 1);
$caminhoOrigem = getCaminhoEquipamentoPorStatus($statusOrigem);
salvarArquivoJSON($caminhoOrigem, $equipamentosOrigem);

// Adicionar ao fora_uso
$foraUso = carregarEquipamentosPorStatus('fora_uso');
$foraUso[] = $equipamentoEncontrado;
$caminhoForaUso = getCaminhoEquipamentoPorStatus('fora_uso');

if (salvarArquivoJSON($caminhoForaUso, $foraUso)) {
    echo json_encode(['success' => true, 'message' => 'Equipamento movido para Fora de Uso']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar']);
}
?>