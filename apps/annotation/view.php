<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Adicionar</title>
    <?php include '../includes/head.html'; ?>
    <link rel="stylesheet" href="../css/exibir.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container">
<div id="lista"></div>
<script>
    fetch('../data/data.json')
        .then(r => r.json())
        .then(data => {
            const lista = document.getElementById('lista');
            data.forEach(item => {
                const div = document.createElement('div');
                div.innerHTML = `
  <strong>${item.tipo === 'problema' ? '⚠️Problema' : '📝 Anotação'}: ${item.titulo}</strong><br/>
<small>📅 ${item.data_hora}</small>
  <p>${item.texto}</p>
  ${
                    item.tipo === 'problema' && !item.resolvido
                        ? `<button onclick="resolver(${item.id})">✅ Marcar como resolvido</button>`
                        : item.tipo === 'problema'
                            ? `<em>✅ Resolvido</em>`
                            : ''
                }
`;
                lista.appendChild(div);
            });
        });
    function resolver(id) {
        fetch("resolver.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${id}`
        })
            .then(res => res.json())
            .then(() => location.reload());
    }
</script>
</div>
<footer>
    <p>&copy; 2024 - 2025 SaúdeTracker - Todos os direitos reservados.</p>
</footer>
</body>
</html>
