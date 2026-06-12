<?php
session_start();
require_once '../includes/funcoes.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$acao = $data['acao'] ?? '';
$id = $data['id'] ?? '';

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID não informado']);
    exit;
}

if ($acao === 'enviar') {
    // Buscar equipamento em status que podem ir para manutenção
    $statuses = ['estoque', 'alocado', 'emprestado'];
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
        echo json_encode(['success' => false, 'message' => 'Equipamento não encontrado ou já está em manutenção']);
        exit;
    }
    
    // Salvar status anterior
    $equipamentoEncontrado['status_anterior'] = $equipamentoEncontrado['status'];
    $equipamentoEncontrado['status'] = 'manutencao';
    $equipamentoEncontrado['data_manutencao'] = date('Y-m-d H:i:s');
    $equipamentoEncontrado['data_atualizacao'] = date('Y-m-d H:i:s');
    
    // Adicionar ao histórico
    if (!isset($equipamentoEncontrado['historico_manutencao'])) {
        $equipamentoEncontrado['historico_manutencao'] = [];
    }
    $equipamentoEncontrado['historico_manutencao'][] = [
        'data_envio' => date('Y-m-d H:i:s'),
        'problema' => $data['problema'] ?? 'Enviado para manutenção'
    ];
    
    // Remover da origem
    $equipamentosOrigem = carregarEquipamentosPorStatus($statusOrigem);
    array_splice($equipamentosOrigem, $indexOrigem, 1);
    $caminhoOrigem = getCaminhoEquipamentoPorStatus($statusOrigem);
    salvarArquivoJSON($caminhoOrigem, $equipamentosOrigem);
    
    // Adicionar à manutenção
    $manutencao = carregarEquipamentosPorStatus('manutencao');
    $manutencao[] = $equipamentoEncontrado;
    $caminhoManutencao = getCaminhoEquipamentoPorStatus('manutencao');
    
    if (salvarArquivoJSON($caminhoManutencao, $manutencao)) {
        echo json_encode(['success' => true, 'message' => 'Equipamento enviado para manutenção']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar']);
    }
    
} elseif ($acao === 'retornar') {
    // Buscar na manutenção
    $manutencao = carregarEquipamentosPorStatus('manutencao');
    $equipamentoEncontrado = null;
    $indexOrigem = null;
    
    foreach ($manutencao as $i => $e) {
        if ($e['id'] == $id) {
            $equipamentoEncontrado = $e;
            $indexOrigem = $i;
            break;
        }
    }
    
    if (!$equipamentoEncontrado) {
        echo json_encode(['success' => false, 'message' => 'Equipamento não encontrado na manutenção']);
        exit;
    }
    
    // Restaurar status anterior ou colocar como estoque
    $statusAnterior = $equipamentoEncontrado['status_anterior'] ?? 'estoque';
    $equipamentoEncontrado['status'] = $statusAnterior;
    unset($equipamentoEncontrado['status_anterior']);
    $equipamentoEncontrado['data_retorno_manutencao'] = date('Y-m-d H:i:s');
    $equipamentoEncontrado['data_atualizacao'] = date('Y-m-d H:i:s');
    
    // Atualizar histórico
    if (!empty($equipamentoEncontrado['historico_manutencao'])) {
        $ultimo = count($equipamentoEncontrado['historico_manutencao']) - 1;
        $equipamentoEncontrado['historico_manutencao'][$ultimo]['data_retorno'] = date('Y-m-d H:i:s');
    }
    
    // Remover da manutenção
    array_splice($manutencao, $indexOrigem, 1);
    $caminhoManutencao = getCaminhoEquipamentoPorStatus('manutencao');
    salvarArquivoJSON($caminhoManutencao, $manutencao);
    
    // Adicionar ao status de destino (estoque)
    $estoque = carregarEquipamentosPorStatus('estoque');
    $estoque[] = $equipamentoEncontrado;
    $caminhoEstoque = getCaminhoEquipamentoPorStatus('estoque');
    
    if (salvarArquivoJSON($caminhoEstoque, $estoque)) {
        echo json_encode(['success' => true, 'message' => 'Equipamento retornado da manutenção']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Ação inválida']);
}
?>