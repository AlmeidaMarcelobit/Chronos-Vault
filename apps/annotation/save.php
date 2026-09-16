<?php
date_default_timezone_set('America/Sao_Paulo'); // Define o fuso horário

$dataFile = '../data/data.json';
$conteudo = file_get_contents($dataFile);
$anotacoes = json_decode($conteudo, true) ?? [];

$nova = [
    'id' => time(),
    'tipo' => $_POST['tipo'],
    'titulo' => $_POST['titulo'],
    'texto' => $_POST['texto'],
    'resolvido' => false,
    'data_hora' => date('Y-m-d H:i:s')
];

$anotacoes[] = $nova;
file_put_contents($dataFile, json_encode($anotacoes, JSON_PRETTY_PRINT));
echo json_encode(['status' => 'ok']);
?>