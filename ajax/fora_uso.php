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

$equipamentos = lerArquivoJSON('../data/equipamentos/equipamentos.json');
$foraUso = lerArquivoJSON('../data/equipamentos/fora_uso.json');

// Procurar em equipamentos ativos
$equipamentoEncontrado = null;
$index = null;
foreach ($equipamentos as $i => $e) {
    if ($e['id'] === $id) {
        $equipamentoEncontrado = $e;
        $index = $i;
        break;
    }
}

// Se não achou, procurar na manutenção
if (!$equipamentoEncontrado) {
    $manutencao = lerArquivoJSON('../data/equipamentos/manutencao.json');
    foreach ($manutencao as $i => $e) {
        if ($e['id'] === $id) {
            $equipamentoEncontrado = $e;
            $index = $i;
            $deManutencao = true;
            break;
        }
    }
}

if ($equipamentoEncontrado) {
    // Desassociar colaborador
    $equipamentoEncontrado['colaborador_id'] = null;
    $equipamentoEncontrado['status_anterior'] = $equipamentoEncontrado['status'];
    $equipamentoEncontrado['status'] = 'fora_uso';
    $equipamentoEncontrado['data_fora_uso'] = date('Y-m-d H:i:s');
    $equipamentoEncontrado['motivo_fora_uso'] = $data['motivo'] ?? 'Marcado como fora de uso';
    
    // Remover da origem e adicionar ao fora_uso
    if (isset($deManutencao)) {
        $manutencao = lerArquivoJSON('../data/equipamentos/manutencao.json');
        array_splice($manutencao, $index, 1);
        salvarArquivoJSON('../data/equipamentos/manutencao.json', $manutencao);
    } else {
        array_splice($equipamentos, $index, 1);
        salvarArquivoJSON('../data/equipamentos/equipamentos.json', $equipamentos);
    }
    
    $foraUso[] = $equipamentoEncontrado;
    
    if (salvarArquivoJSON('../data/equipamentos/fora_uso.json', $foraUso)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Equipamento não encontrado']);
}
?>