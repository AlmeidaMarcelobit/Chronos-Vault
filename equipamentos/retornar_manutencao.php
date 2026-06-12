<?php
session_start();
require_once '../includes/funcoes.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$usuario_nivel = $_SESSION['usuario_nivel'] ?? 'user';
if ($usuario_nivel === 'view') {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Buscar na manutenção
$manutencao = carregarEquipamentosPorStatus('manutencao');
$equipamento = null;
$indexOrigem = null;

foreach ($manutencao as $i => $e) {
    if ($e['id'] == $id) {
        $equipamento = $e;
        $indexOrigem = $i;
        break;
    }
}

if (!$equipamento) {
    $_SESSION['mensagem'] = 'Equipamento não encontrado na manutenção.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

// Restaurar status anterior ou estoque
$statusAnterior = $equipamento['status_anterior'] ?? 'estoque';
$equipamento['status'] = $statusAnterior;
unset($equipamento['status_anterior']);
$equipamento['data_retorno_manutencao'] = date('Y-m-d H:i:s');
$equipamento['data_atualizacao'] = date('Y-m-d H:i:s');

// Atualizar último histórico de manutenção
if (!empty($equipamento['historico_manutencao'])) {
    $ultimo = count($equipamento['historico_manutencao']) - 1;
    $equipamento['historico_manutencao'][$ultimo]['data_retorno'] = date('Y-m-d H:i:s');
}

// Remover da manutenção
array_splice($manutencao, $indexOrigem, 1);
$caminhoManutencao = getCaminhoEquipamentoPorStatus('manutencao');

if (!salvarArquivoJSON($caminhoManutencao, $manutencao)) {
    $_SESSION['mensagem'] = 'Erro ao remover da manutenção.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

// Adicionar ao status de destino
$destino = carregarEquipamentosPorStatus($statusAnterior);
$destino[] = $equipamento;
$caminhoDestino = getCaminhoEquipamentoPorStatus($statusAnterior);

if (salvarArquivoJSON($caminhoDestino, $destino)) {
    $_SESSION['mensagem'] = 'Equipamento ' . htmlspecialchars($equipamento['patrimonio']) . ' retornado da manutenção com sucesso!';
    $_SESSION['mensagem_tipo'] = 'success';
} else {
    $_SESSION['mensagem'] = 'Erro ao salvar retorno da manutenção.';
    $_SESSION['mensagem_tipo'] = 'error';
}

header('Location: index.php');
exit;
