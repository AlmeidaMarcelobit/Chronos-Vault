<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Adicionar Anotação</title>
    <?php include '../includes/head.html'; ?>
    <link rel="stylesheet" href="../css/anotacao.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container_2">
    <h2>Nova Anotação</h2>

    <label for="titulo">Título:</label><br />
    <input type="text" id="titulo" placeholder="Ex: Equipamento alocado para colaborar X" /><br /><br />

    <label for="tipo">Tipo:</label><br />
    <select id="tipo">
        <option value="anotacao">📝 Anotação</option>
        <option value="problema">⚠️ Problema</option>
    </select><br /><br />

    <label for="texto">Descrição:</label><br />
    <textarea id="texto" rows="4" cols="50" placeholder="Descreva a anotação ou problema..."></textarea><br /><br />

    <div class="button">
        <button id="button" onclick="salvar()">Salvar</button>
    </div>
</div>
<footer>
    <p>&copy; 2024 - 2025 SaúdeTracker - Todos os direitos reservados.</p>
</footer>
<script>
    function salvar() {
        const tipo = document.getElementById("tipo").value;
        const titulo = document.getElementById("titulo").value;
        const texto = document.getElementById("texto").value;

        fetch("salvar.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `tipo=${tipo}&titulo=${encodeURIComponent(titulo)}&texto=${encodeURIComponent(texto)}`
        })
            .then(res => res.json())
            .then(data => {
                // alert("Salvo com sucesso!");
                document.getElementById("titulo").value = "";
                document.getElementById("texto").value = "";
            });
    }
</script>
</body>
</html>
