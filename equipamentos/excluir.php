<?php
session_start();
$returnFiltro = $_SESSION['equipamentos_filtro'] ?? 'todos';
require_once '../includes/funcoes.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
    $_SESSION['mensagem'] = 'Acesso negado. Apenas administradores podem excluir equipamentos.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php' . ($returnFiltro !== 'todos' ? '?filtro=' . urlencode($returnFiltro) : ''));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensagem'] = 'Método de exclusão inválido.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php' . ($returnFiltro !== 'todos' ? '?filtro=' . urlencode($returnFiltro) : ''));
    exit;
}

$tokenSessao = $_SESSION['csrf_excluir_equipamento'] ?? '';
$tokenRecebido = $_POST['csrf_token'] ?? '';
if ($tokenSessao === '' || !hash_equals($tokenSessao, $tokenRecebido)) {
    $_SESSION['mensagem'] = 'Não foi possível validar a solicitação de exclusão. Atualize a página e tente novamente.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php' . ($returnFiltro !== 'todos' ? '?filtro=' . urlencode($returnFiltro) : ''));
    exit;
}

$id = trim((string)($_POST['id'] ?? ''));
$busca = $id !== '' ? buscarEquipamentoPorId($id) : null;

if (!$busca) {
    $_SESSION['mensagem'] = 'Equipamento não encontrado.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php' . ($returnFiltro !== 'todos' ? '?filtro=' . urlencode($returnFiltro) : ''));
    exit;
}

$equipamento = $busca['equipamento'];
$statusOrigem = $busca['status_origem'];
$equipamentos = carregarEquipamentosPorStatus($statusOrigem);
$quantidadeAnterior = count($equipamentos);

$equipamentos = array_values(array_filter($equipamentos, static function ($item) use ($id) {
    return (string)($item['id'] ?? '') !== $id;
}));

if (count($equipamentos) === $quantidadeAnterior) {
    $_SESSION['mensagem'] = 'Equipamento não encontrado no arquivo de origem.';
    $_SESSION['mensagem_tipo'] = 'error';
} elseif (salvarArquivoJSON(getCaminhoEquipamentoPorStatus($statusOrigem), $equipamentos)) {
    $identificacao = $equipamento['patrimonio'] ?? $equipamento['hostname'] ?? $id;
    $_SESSION['mensagem'] = 'Equipamento ' . $identificacao . ' excluído permanentemente.';
    $_SESSION['mensagem_tipo'] = 'success';
    $_SESSION['csrf_excluir_equipamento'] = bin2hex(random_bytes(32));
} else {
    $_SESSION['mensagem'] = 'Erro ao excluir o equipamento. Tente novamente.';
    $_SESSION['mensagem_tipo'] = 'error';
}

header('Location: index.php' . ($returnFiltro !== 'todos' ? '?filtro=' . urlencode($returnFiltro) : ''));
exit;

