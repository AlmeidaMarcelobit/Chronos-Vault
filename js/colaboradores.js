// colaboradores.js - Scripts para a página de colaboradores

document.addEventListener('DOMContentLoaded', function() {
    // Inicializa tooltips
    initTooltips();
    
    // Configura busca em tempo real (opcional)
    initLiveSearch();
    
    // Configura confirmar exclusão com modal
    initDeleteConfirmation();
});

function initTooltips() {
    const actionButtons = document.querySelectorAll('.action-btn');
    actionButtons.forEach(btn => {
        const title = btn.getAttribute('title');
        if (title) {
            btn.setAttribute('data-tooltip', title);
        }
    });
}

function initLiveSearch() {
    const searchInput = document.querySelector('.search-input');
    if (searchInput && !searchInput.value) {
        // Opcional: adicionar busca em tempo real
        let timeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                if (this.value.length >= 3) {
                    this.closest('form').submit();
                }
            }, 500);
        });
    }
}

function initDeleteConfirmation() {
    const deleteButtons = document.querySelectorAll('.action-delete');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Tem certeza que deseja excluir este colaborador?\n\nEsta ação não poderá ser desfeita.')) {
                e.preventDefault();
                return false;
            }
        });
    });
}

// Função para formatar CPF em tempo real
function formatCPF(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        input.value = value;
    }
}

// Função para copiar ID do colaborador
function copyId(id) {
    navigator.clipboard.writeText(id).then(() => {
        showNotification('ID copiado com sucesso!', 'success');
    }).catch(() => {
        showNotification('Erro ao copiar ID', 'error');
    });
}

function showNotification(message, type = 'info') {
    // Criar elemento de notificação
    const notification = document.createElement('div');
    notification.className = `global-alert alert-${type}`;
    notification.innerHTML = `
        <div class="alert-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
    `;
    
    document.body.appendChild(notification);
    
    // Remover após 3 segundos
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Exportar funções para uso global
window.formatCPF = formatCPF;
window.copyId = copyId;