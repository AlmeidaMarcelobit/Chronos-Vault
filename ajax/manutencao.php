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

// Carregar dados
$equipamentos = lerArquivoJSON('../data/equipamentos/equipamentos.json');
$manutencao = lerArquivoJSON('../data/equipamentos/manutencao.json');

if ($acao === 'enviar') {
    // Encontrar equipamento
    $equipamentoEncontrado = null;
    $index = null;
    foreach ($equipamentos as $i => $e) {
        if ($e['id'] === $id) {
            $equipamentoEncontrado = $e;
            $index = $i;
            break;
        }
    }
    
    if ($equipamentoEncontrado) {
        // Salvar status anterior
        $equipamentoEncontrado['status_anterior'] = $equipamentoEncontrado['status'];
        $equipamentoEncontrado['status'] = 'manutencao';
        $equipamentoEncontrado['data_manutencao'] = date('Y-m-d H:i:s');
        
        // Adicionar ao histórico
        if (!isset($equipamentoEncontrado['historico_manutencao'])) {
            $equipamentoEncontrado['historico_manutencao'] = [];
        }
        $equipamentoEncontrado['historico_manutencao'][] = [
            'data_envio' => date('Y-m-d H:i:s'),
            'problema' => $data['problema'] ?? 'Enviado para manutenção'
        ];
        
        // Remover do array original e adicionar à manutenção
        array_splice($equipamentos, $index, 1);
        $manutencao[] = $equipamentoEncontrado;
        
        // Salvar
        if (salvarArquivoJSON('../data/equipamentos/equipamentos.json', $equipamentos) &&
            salvarArquivoJSON('../data/equipamentos/manutencao.json', $manutencao)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Equipamento não encontrado']);
    }
} elseif ($acao === 'retornar') {
    // Encontrar na manutenção
    $equipamentoEncontrado = null;
    $index = null;
    foreach ($manutencao as $i => $e) {
        if ($e['id'] === $id) {
            $equipamentoEncontrado = $e;
            $index = $i;
            break;
        }
    }
    
    if ($equipamentoEncontrado) {
        // Restaurar status anterior ou colocar como estoque
        $statusAnterior = $equipamentoEncontrado['status_anterior'] ?? 'estoque';
        $equipamentoEncontrado['status'] = $statusAnterior;
        unset($equipamentoEncontrado['status_anterior']);
        $equipamentoEncontrado['data_retorno_manutencao'] = date('Y-m-d H:i:s');
        
        // Atualizar histórico
        if (!empty($equipamentoEncontrado['historico_manutencao'])) {
            $ultimo = count($equipamentoEncontrado['historico_manutencao']) - 1;
            $equipamentoEncontrado['historico_manutencao'][$ultimo]['data_retorno'] = date('Y-m-d H:i:s');
        }
        
        // Remover da manutenção e adicionar aos ativos
        array_splice($manutencao, $index, 1);
        $equipamentos[] = $equipamentoEncontrado;
        
        if (salvarArquivoJSON('../data/equipamentos/equipamentos.json', $equipamentos) &&
            salvarArquivoJSON('../data/equipamentos/manutencao.json', $manutencao)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Equipamento não encontrado']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Ação inválida']);
}
?>