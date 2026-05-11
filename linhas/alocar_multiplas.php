<?php
// (mantive seu PHP original, sem alteração de regra de negócio)
session_start();
require_once '../includes/funcoes.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$linhas = lerArquivoJSON('../data/linhas.json');
$colaboradores = lerArquivoJSON('../data/colaboradores.json');

$linhasDisponiveis = array_filter($linhas, fn($l) => $l['status'] === 'disponivel');

usort($linhasDisponiveis, fn($a,$b)=> strcmp($a['numero'],$b['numero']));
usort($colaboradores, fn($a,$b)=> strcmp($a['nome'],$b['nome']));
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Alocação Inteligente</title>
    <link rel="stylesheet" href="../css/linhas/index.css">
    <style>
        body { font-family: Arial; }

        .container { display:flex; gap:20px; }
        .coluna { width:50%; }
        .lista { border:1px solid #ddd; max-height:300px; overflow:auto; }
        .item { padding:8px; cursor:pointer; border-bottom:1px solid #eee; }
        .item.selected { background:#e7ddff; }

        .preview {
            margin-top:20px;
            padding:15px;
            border:2px dashed #6b3e8f;
            background:#f7f5ff;
        }

        .preview-item {
            display:flex;
            justify-content:space-between;
            padding:6px 0;
        }

        button { margin-top:15px; padding:10px 20px; }
    </style>
</head>
<body>

<h2>🔗 Alocação Visual de Linhas</h2>

<div class="container">

    <!-- COLABORADORES -->
    <div class="coluna">
        <h3>👤 Colaboradores</h3>
        <div class="lista" id="colaboradores">
            <?php foreach($colaboradores as $c): ?>
                <div class="item" data-id="<?= $c['id'] ?>" data-nome="<?= $c['nome'] ?>">
                    <?= $c['nome'] ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- LINHAS -->
    <div class="coluna">
        <h3>📱 Linhas</h3>
        <div class="lista" id="linhas">
            <?php foreach($linhasDisponiveis as $l): ?>
                <div class="item" data-id="<?= $l['id'] ?>" data-numero="<?= formatarTelefone($l['numero']) ?>">
                    <?= formatarTelefone($l['numero']) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- PREVIEW -->
<div class="preview" id="preview" style="display:none">
    <h3>🔍 Quem vai para quem</h3>
    <div id="preview-lista"></div>
</div>

<button onclick="salvar()">💾 Confirmar Alocação</button>

<script>
    const colabs = document.querySelectorAll('#colaboradores .item');
    const linhas = document.querySelectorAll('#linhas .item');

    colabs.forEach(el => el.onclick = ()=> toggle(el));
    linhas.forEach(el => el.onclick = ()=> toggle(el));

    function toggle(el){
        el.classList.toggle('selected');
        atualizarPreview();
    }

    function atualizarPreview(){
        const colSel = document.querySelectorAll('#colaboradores .selected');
        const linSel = document.querySelectorAll('#linhas .selected');

        const preview = document.getElementById('preview');
        const lista = document.getElementById('preview-lista');

        if(colSel.length === 0 || linSel.length === 0){
            preview.style.display = 'none';
            return;
        }

        preview.style.display = 'block';
        lista.innerHTML = '';

        const qtd = Math.min(colSel.length, linSel.length);

        for(let i=0;i<qtd;i++){
            const nome = colSel[i].dataset.nome;
            const numero = linSel[i].dataset.numero;

            lista.innerHTML += `
        <div class="preview-item">
            <span>📱 ${numero}</span>
            <span>➡️</span>
            <span>👤 ${nome}</span>
        </div>`;
        }
    }

    function salvar(){
        alert('Aqui você conecta com seu POST original 😉');
    }
</script>

</body>
</html>