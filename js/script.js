// ==========================================================================
// SISTEMA DE GESTÃO - SCRIPT PRINCIPAL OTIMIZADO
// ==========================================================================

// Namespace único para evitar conflitos
const SistemaGestao = {

    // ======================================================================
    // INICIALIZAÇÃO
    // ======================================================================
    init: function() {
        'use strict';

        console.log('Sistema Gestão inicializado');

        // Máscaras para formulários
        this.aplicarMascaras();

        // Controle de Dropdowns - CORREÇÃO DO OVERFLOW
        this.initDropdowns();

        // Confirmações para exclusão
        this.configurarConfirmacoes();

        // Filtros dinâmicos
        this.configurarFiltros();

        // Alertas automáticos
        this.configurarAlertas();

        // Modais
        this.initModais();

        // Menu Mobile
        this.initMobileMenu();

        // Tooltips
        this.initTooltips();

        // Tabs
        this.initTabs();
    },

    // ======================================================================
    // MÁSCARAS PARA FORMULÁRIOS
    // ======================================================================
    aplicarMascaras: function() {
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

        // Máscara para telefone
        const telefoneInputs = document.querySelectorAll('input[data-mask="telefone"]');
        telefoneInputs.forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');

                if (value.length <= 10) {
                    value = value.replace(/(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{4})(\d)/, '$1-$2');
                } else {
                    value = value.replace(/(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{5})(\d)/, '$1-$2');
                }

                e.target.value = value;
            });
        });

        // Máscara para patrimônio
        const patrimonioInputs = document.querySelectorAll('input[data-mask="patrimonio"]');
        patrimonioInputs.forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase();
                value = value.replace(/[^A-Z0-9]/g, '');
                e.target.value = value;
            });
        });
    },

    // ======================================================================
    // DROPDOWNS - CORREÇÃO COMPLETA DO PROBLEMA DE OVERFLOW
    // ======================================================================
    initDropdowns: function() {
        const self = this;
        const dropdowns = document.querySelectorAll('.dropdown');

        // Função para fechar todos os dropdowns
        function closeAllDropdowns() {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
                menu.style.visibility = '';
                menu.style.opacity = '';
            });
            document.querySelectorAll('.dropdown.active').forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }

        // Função para ajustar posição do dropdown
        function ajustarPosicaoDropdown(dropdown, menu) {
            // Resetar estilos
            menu.style.top = '';
            menu.style.bottom = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.transform = '';
            menu.style.maxHeight = '';
            menu.style.overflowY = '';

            const rect = dropdown.getBoundingClientRect();
            const menuRect = menu.getBoundingClientRect();
            const windowWidth = window.innerWidth;
            const windowHeight = window.innerHeight;
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            // Verificar se dropdown está dentro de uma tabela com overflow
            const isInTable = dropdown.closest('.table-responsive') !== null;

            // Posicionamento horizontal
            if (rect.left + menuRect.width > windowWidth - 10) {
                // Alinhar à direita
                menu.style.left = 'auto';
                menu.style.right = '0';

                // Se estiver muito à direita, ajustar
                if (rect.right - menuRect.width < 0) {
                    menu.style.left = '0';
                    menu.style.right = 'auto';
                }
            } else {
                menu.style.left = '0';
                menu.style.right = 'auto';
            }

            // Posicionamento vertical com verificação de espaço
            const spaceBelow = windowHeight - rect.bottom;
            const spaceAbove = rect.top;
            const menuHeight = Math.min(menuRect.height, 400); // Limitar altura

            if (spaceBelow < menuHeight && spaceAbove > menuHeight) {
                // Abrir para cima
                menu.style.top = 'auto';
                menu.style.bottom = '100%';
                menu.style.marginTop = '0';
                menu.style.marginBottom = '5px';
            } else {
                // Abrir para baixo
                menu.style.top = '100%';
                menu.style.bottom = 'auto';
                menu.style.marginTop = '5px';
                menu.style.marginBottom = '0';
            }

            // Se estiver em tabela, aplicar correções de overflow
            if (isInTable) {
                menu.style.zIndex = '99999';

                // Se o menu for muito grande, adicionar scroll
                if (menuRect.height > 400) {
                    menu.style.maxHeight = '400px';
                    menu.style.overflowY = 'auto';
                }
            }

            // Garantir que o menu esteja visível
            menu.style.visibility = 'visible';
            menu.style.opacity = '1';
        }

        // Fechar dropdown ao clicar fora
        document.addEventListener('click', function(event) {
            const isDropdown = event.target.closest('.dropdown');

            if (!isDropdown) {
                closeAllDropdowns();
            }
        });

        // Fechar dropdown ao pressionar ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAllDropdowns();
            }
        });

        // Abrir/fechar dropdown ao clicar no toggle
        dropdowns.forEach(dropdown => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            const menu = dropdown.querySelector('.dropdown-menu');

            if (toggle && menu) {
                // Remover evento hover se existir
                dropdown.classList.remove('hover');

                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const isOpen = menu.classList.contains('show');

                    // Fechar todos os outros dropdowns
                    closeAllDropdowns();

                    if (!isOpen) {
                        // Abrir dropdown atual
                        menu.classList.add('show');
                        dropdown.classList.add('active');

                        // Ajustar posição após abrir
                        setTimeout(() => {
                            ajustarPosicaoDropdown(dropdown, menu);
                        }, 10);
                    }
                });
            }
        });

        // Reajustar ao redimensionar
        window.addEventListener('resize', function() {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                const dropdown = menu.closest('.dropdown');
                if (dropdown) {
                    ajustarPosicaoDropdown(dropdown, menu);
                }
            });
        });

        // Reajustar ao rolar a página
        window.addEventListener('scroll', function() {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                const dropdown = menu.closest('.dropdown');
                if (dropdown) {
                    ajustarPosicaoDropdown(dropdown, menu);
                }
            });
        }, { passive: true });
    },

    // ======================================================================
    // CONFIRMAÇÕES PARA EXCLUSÃO
    // ======================================================================
    configurarConfirmacoes: function() {
        const botoesExcluir = document.querySelectorAll('.btn-excluir, a[href*="excluir"], button[data-acao="excluir"]');

        botoesExcluir.forEach(botao => {
            botao.addEventListener('click', function(e) {
                if (!confirm('Tem certeza que deseja excluir este item? Esta ação não pode ser desfeita.')) {
                    e.preventDefault();
                }
            });
        });
    },

    // ======================================================================
    // FILTROS DINÂMICOS
    // ======================================================================
    configurarFiltros: function() {
        // Filtro por select
        const filtroSelects = document.querySelectorAll('.filtro-select, select[data-filtro]');
        filtroSelects.forEach(select => {
            select.addEventListener('change', function() {
                if (this.form) {
                    this.form.submit();
                }
            });
        });

        // Busca em tempo real
        const buscaInputs = document.querySelectorAll('.busca-real, input[data-busca-real]');
        buscaInputs.forEach(input => {
            let timeout = null;

            input.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    self.buscarEmTempoReal(this);
                }, 500);
            });
        });
    },

    // ======================================================================
    // BUSCA EM TEMPO REAL
    // ======================================================================
    buscarEmTempoReal: function(input) {
        const termo = input.value.toLowerCase();
        const container = input.closest('.tabela-container') || document;
        const linhas = container.querySelectorAll('.tabela-linha, tr[data-busca]');

        linhas.forEach(linha => {
            const texto = linha.textContent.toLowerCase();
            if (texto.includes(termo)) {
                linha.style.display = '';
            } else {
                linha.style.display = 'none';
            }
        });
    },

    // ======================================================================
    // ALERTAS AUTOMÁTICOS
    // ======================================================================
    configurarAlertas: function() {
        const alertas = document.querySelectorAll('.alert:not(.alert-permanente)');

        alertas.forEach(alerta => {
            setTimeout(() => {
                alerta.style.transition = 'opacity 0.5s, transform 0.3s';
                alerta.style.opacity = '0';
                alerta.style.transform = 'translateY(-10px)';

                setTimeout(() => {
                    if (alerta.parentNode) {
                        alerta.remove();
                    }
                }, 500);
            }, 5000);
        });
    },

    // ======================================================================
    // MODAIS
    // ======================================================================
    initModais: function() {
        const self = this;
        const botoesAbrir = document.querySelectorAll('[data-modal-target]');
        const botoesFechar = document.querySelectorAll('[data-modal-close], .modal-close, .btn-fechar-modal');

        // Abrir modal
        botoesAbrir.forEach(botao => {
            botao.addEventListener('click', function(e) {
                e.preventDefault();
                const modalId = this.dataset.modalTarget;
                self.abrirModal(modalId);
            });
        });

        // Fechar modal
        botoesFechar.forEach(botao => {
            botao.addEventListener('click', function(e) {
                e.preventDefault();
                const modal = this.closest('.modal');
                if (modal) {
                    self.fecharModal(modal);
                }
            });
        });

        // Fechar ao clicar fora
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                self.fecharModal(e.target);
            }
        });

        // Fechar ao pressionar ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modalAberto = document.querySelector('.modal.show, .modal[style*="display: block"]');
                if (modalAberto) {
                    self.fecharModal(modalAberto);
                }
            }
        });
    },

    abrirModal: function(modalId) {
        const modal = document.getElementById(modalId.replace('#', ''));
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';

            // Disparar evento
            const event = new CustomEvent('modal:aberto', { detail: { modal: modal } });
            document.dispatchEvent(event);
        }
    },

    fecharModal: function(modal) {
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.style.overflow = '';

            // Disparar evento
            const event = new CustomEvent('modal:fechado', { detail: { modal: modal } });
            document.dispatchEvent(event);
        }
    },

    // ======================================================================
    // MENU MOBILE
    // ======================================================================
    initMobileMenu: function() {
        const menuToggle = document.querySelector('.menu-toggle, .navbar-toggler, [data-toggle="menu"]');
        const navMenu = document.querySelector('.nav-menu, .navbar-collapse, [data-menu]');

        if (menuToggle && navMenu) {
            menuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                navMenu.classList.toggle('show');
                menuToggle.classList.toggle('active');

                // Animar ícone
                const icon = menuToggle.querySelector('i');
                if (icon) {
                    if (navMenu.classList.contains('show')) {
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-times');
                    } else {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                }
            });

            // Fechar menu ao clicar em link
            navMenu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        navMenu.classList.remove('show');
                        menuToggle.classList.remove('active');

                        const icon = menuToggle.querySelector('i');
                        if (icon) {
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                        }
                    }
                });
            });
        }
    },

    // ======================================================================
    // TOOLTIPS
    // ======================================================================
    initTooltips: function() {
        const tooltips = document.querySelectorAll('[data-tooltip]');

        tooltips.forEach(element => {
            element.addEventListener('mouseenter', function(e) {
                const tooltipText = this.dataset.tooltip;

                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip-custom';
                tooltip.textContent = tooltipText;
                tooltip.style.position = 'absolute';
                tooltip.style.backgroundColor = '#2c3e50';
                tooltip.style.color = 'white';
                tooltip.style.padding = '6px 12px';
                tooltip.style.borderRadius = '4px';
                tooltip.style.fontSize = '12px';
                tooltip.style.zIndex = '100000';
                tooltip.style.whiteSpace = 'nowrap';
                tooltip.style.pointerEvents = 'none';

                document.body.appendChild(tooltip);

                const rect = this.getBoundingClientRect();
                const tooltipRect = tooltip.getBoundingClientRect();

                tooltip.style.left = rect.left + (rect.width / 2) - (tooltipRect.width / 2) + 'px';
                tooltip.style.top = rect.top - tooltipRect.height - 8 + 'px';

                this.addEventListener('mouseleave', function() {
                    tooltip.remove();
                }, { once: true });
            });
        });
    },

    // ======================================================================
    // TABS
    // ======================================================================
    initTabs: function() {
        const tabContainers = document.querySelectorAll('.tabs-container');

        tabContainers.forEach(container => {
            const tabs = container.querySelectorAll('.tab');
            const contents = container.querySelectorAll('.tab-content');

            tabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();

                    const target = this.dataset.tab;

                    // Remover classe active de todas as tabs
                    tabs.forEach(t => t.classList.remove('active'));

                    // Adicionar classe active na tab clicada
                    this.classList.add('active');

                    // Esconder todos os conteúdos
                    contents.forEach(content => content.classList.remove('active'));

                    // Mostrar conteúdo correspondente
                    const targetContent = container.querySelector(`.tab-content[data-tab="${target}"]`);
                    if (targetContent) {
                        targetContent.classList.add('active');
                    }
                });
            });
        });
    },

    // ======================================================================
    // VALIDAÇÃO DE FORMULÁRIOS
    // ======================================================================
    validarFormulario: function(form) {
        let valido = true;

        // Remover mensagens de erro existentes
        form.querySelectorAll('.erro-validacao').forEach(erro => erro.remove());
        form.querySelectorAll('[style*="border-color: #e74c3c"]').forEach(campo => {
            campo.style.borderColor = '';
        });

        // Validar campos obrigatórios
        const camposObrigatorios = form.querySelectorAll('[required]');

        camposObrigatorios.forEach(campo => {
            if (!campo.value.trim()) {
                campo.style.borderColor = '#e74c3c';
                valido = false;

                // Adicionar mensagem de erro
                const erro = document.createElement('div');
                erro.className = 'erro-validacao';
                erro.style.color = '#e74c3c';
                erro.style.fontSize = '12px';
                erro.style.marginTop = '5px';
                erro.textContent = 'Este campo é obrigatório';

                if (campo.nextSibling) {
                    campo.parentNode.insertBefore(erro, campo.nextSibling);
                } else {
                    campo.parentNode.appendChild(erro);
                }
            }
        });

        // Validar email
        const emailCampos = form.querySelectorAll('input[type="email"]');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        emailCampos.forEach(campo => {
            if (campo.value && !emailRegex.test(campo.value)) {
                campo.style.borderColor = '#e74c3c';
                valido = false;

                const erro = document.createElement('div');
                erro.className = 'erro-validacao';
                erro.style.color = '#e74c3c';
                erro.style.fontSize = '12px';
                erro.style.marginTop = '5px';
                erro.textContent = 'E-mail inválido';

                if (campo.nextSibling) {
                    campo.parentNode.insertBefore(erro, campo.nextSibling);
                } else {
                    campo.parentNode.appendChild(erro);
                }
            }
        });

        return valido;
    },

    // ======================================================================
    // EXPORTAR DADOS PARA CSV
    // ======================================================================
    exportarParaCSV: function(tabelaId, nomeArquivo = 'exportacao.csv') {
        const tabela = document.getElementById(tabelaId);
        if (!tabela) return;

        const linhas = tabela.querySelectorAll('tr');
        const dados = [];

        linhas.forEach(linha => {
            const colunas = linha.querySelectorAll('td, th');
            const linhaDados = Array.from(colunas).map(coluna => {
                // Remover botões e links do texto
                let texto = coluna.textContent;

                // Remover ícones e espaços extras
                texto = texto.replace(/[^\x20-\x7EÀ-ÿ]/g, '');
                texto = texto.trim();

                return `"${texto.replace(/"/g, '""')}"`;
            });

            if (linhaDados.length > 0) {
                dados.push(linhaDados.join(','));
            }
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
    },

    // ======================================================================
    // IMPRIMIR
    // ======================================================================
    imprimir: function(elementoId, titulo = '') {
        const elemento = document.getElementById(elementoId);
        if (!elemento) return;

        const janelaImpressao = window.open('', '_blank');
        janelaImpressao.document.write(`
            <html>
                <head>
                    <title>${titulo || 'Impressão'}</title>
                    <link rel="stylesheet" href="css/style.css">
                    <style>
                        body { padding: 20px; font-family: Arial, sans-serif; }
                        .no-print { display: none; }
                        @media print {
                            body { margin: 0; padding: 15px; }
                        }
                    </style>
                </head>
                <body>
                    ${elemento.outerHTML}
                    <script>
                        window.onload = function() { window.print(); }
                    <\/script>
                </body>
            </html>
        `);
        janelaImpressao.document.close();
    }
};

// ==========================================================================
// INICIALIZAÇÃO
// ==========================================================================
document.addEventListener('DOMContentLoaded', function() {
    SistemaGestao.init();
});

// ==========================================================================
// EXPORTAR PARA USO GLOBAL
// ==========================================================================
window.SistemaGestao = SistemaGestao;
window.exportarParaCSV = SistemaGestao.exportarParaCSV.bind(SistemaGestao);
window.validarFormulario = SistemaGestao.validarFormulario.bind(SistemaGestao);