<?php
$dataFile = '../data/data.json';
$conteudo = file_get_contents($dataFile);
$anotacoes = json_decode($conteudo, true) ?? [];

$id = $_POST['id'];
foreach ($anotacoes as &$item) {
    if ($item['id'] == $id && $item['tipo'] === 'problema') {
        $item['resolvido'] = true;
    }
}

file_put_contents($dataFile, json_encode($anotacoes, JSON_PRETTY_PRINT));
echo json_encode(['status' => 'resolvido']);
?>

