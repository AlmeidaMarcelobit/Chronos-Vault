// Funções JavaScript para o sistema

document.addEventListener('DOMContentLoaded', function() {
    // Máscaras para formulários
    aplicarMascaras();
    
    // Confirmações para exclusão
    configurarConfirmacoes();
    
    // Filtros dinâmicos
    configurarFiltros();
    
    // Alertas automáticos
    configurarAlertas();
});

function aplicarMascaras() {
    // Máscara para CPF
    const cpfInputs = document.querySelectorAll('input[data-mask="cpf"]');
    cpfInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            }
            
            e.target.value = value;
        });
    });
    
    // Máscara para centro de custo (ex: TI001)
    const ccInputs = document.querySelectorAll('input[data-mask="cc"]');
    ccInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase();
            value = value.replace(/[^A-Z0-9]/g, '');
            e.target.value = value;
        });
    });
}

function configurarConfirmacoes() {
    const botoesExcluir = document.querySelectorAll('.btn-excluir, a[href*="excluir"]');
    
    botoesExcluir.forEach(botao => {
        botao.addEventListener('click', function(e) {
            if (!confirm('Tem certeza que deseja excluir este item? Esta ação não pode ser desfeita.')) {
                e.preventDefault();
            }
        });
    });
}

function configurarFiltros() {
    const filtroSelect = document.getElementById('filtro-status');
    if (filtroSelect) {
        filtroSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
}

function configurarAlertas() {
    const alertas = document.querySelectorAll('.alert');
    
    alertas.forEach(alerta => {
        setTimeout(() => {
            alerta.style.opacity = '0';
            alerta.style.transition = 'opacity 0.5s';
            setTimeout(() => {
                alerta.remove();
            }, 500);
        }, 5000);
    });
}

// Função para buscar colaboradores
function buscarColaboradores(termo) {
    // Implementação AJAX para busca em tempo real
    console.log('Buscando colaboradores:', termo);
}

// Função para validar formulários
function validarFormulario(form) {
    let valido = true;
    
    // Validação básica de campos obrigatórios
    const camposObrigatorios = form.querySelectorAll('[required]');
    
    camposObrigatorios.forEach(campo => {
        if (!campo.value.trim()) {
            campo.style.borderColor = '#e74c3c';
            campo.focus();
            valido = false;
            
            // Adicionar mensagem de erro
            if (!campo.nextElementSibling || !campo.nextElementSibling.classList.contains('erro-validacao')) {
                const erro = document.createElement('div');
                erro.className = 'erro-validacao';
                erro.style.color = '#e74c3c';
                erro.style.fontSize = '12px';
                erro.style.marginTop = '5px';
                erro.textContent = 'Este campo é obrigatório';
                campo.parentNode.insertBefore(erro, campo.nextSibling);
            }
        } else {
            campo.style.borderColor = '#ddd';
            
            // Remover mensagem de erro se existir
            const erro = campo.nextElementSibling;
            if (erro && erro.classList.contains('erro-validacao')) {
                erro.remove();
            }
        }
    });
    
    return valido;
}

// Exportar dados
function exportarParaCSV(tabelaId, nomeArquivo) {
    const tabela = document.getElementById(tabelaId);
    const linhas = tabela.querySelectorAll('tr');
    const dados = [];
    
    linhas.forEach(linha => {
        const colunas = linha.querySelectorAll('td, th');
        const linhaDados = Array.from(colunas).map(coluna => {
            return `"${coluna.textContent.replace(/"/g, '""')}"`;
        });
        dados.push(linhaDados.join(','));
    });
    
    const csv = dados.join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (navigator.msSaveBlob) {
        navigator.msSaveBlob(blob, nomeArquivo);
    } else {
        link.href = URL.createObjectURL(blob);
        link.download = nomeArquivo;
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}