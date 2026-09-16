<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Visualizar Anotações</title>
    <?php include '../../pages/includes/header-meta.php'; ?>
    <link rel="stylesheet" href="../../css/exibir.css">
</head>
<body>
<?php include '../../pages/includes/nav.php'; ?>
<div class="container">
<div id="lista"></div>
<script>
    fetch('../../data/data.json')
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
        fetch("resolve.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${id}`
        })
            .then(res => res.json())
            .then(() => location.reload());
    }
</script>
</div>
<?php include '../../pages/includes/footer.php'; ?>
</body>
</html>
