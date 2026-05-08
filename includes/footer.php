<?php
// Obter o caminho base baseado na localização atual
$base_path = '';
if (strpos($_SERVER['PHP_SELF'], '/colaboradores/') !== false) {
    $base_path = '../';
} elseif (strpos($_SERVER['PHP_SELF'], '/equipamentos/') !== false) {
    $base_path = '../';
} elseif (strpos($_SERVER['PHP_SELF'], '/includes/') === false && basename($_SERVER['PHP_SELF']) !== 'index.php' && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    $base_path = './';
}
?>
    
    <?php if (basename($_SERVER['PHP_SELF']) !== 'login.php'): ?>
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3><i class="fas fa-laptop-house"></i> Sistema de Gestão</h3>
                <p>Controle de colaboradores e equipamentos</p>
            </div>
            
            <div class="footer-section">
                <h3>Links Rápidos</h3>
                <ul class="footer-links">
                    <li><a href="<?php echo $base_path; ?>index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="<?php echo $base_path; ?>colaboradores/index.php"><i class="fas fa-users"></i> Colaboradores</a></li>
                    <li><a href="<?php echo $base_path; ?>equipamentos/index.php"><i class="fas fa-laptop"></i> Equipamentos</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Estatísticas</h3>
                <?php
                // Carregar dados para estatísticas
                if (file_exists($base_path . 'data/colaboradores.json') && file_exists($base_path . 'data/equipamentos.json')) {
                    $colaboradores = json_decode(file_get_contents($base_path . 'data/colaboradores.json'), true) ?: [];
                    $equipamentos = json_decode(file_get_contents($base_path . 'data/equipamentos.json'), true) ?: [];
                    
                    $total_colaboradores = count($colaboradores);
                    $total_equipamentos = count($equipamentos);
                    $equipamentos_estoque = count(array_filter($equipamentos, function($e) { 
                        return ($e['status'] ?? '') === 'estoque'; 
                    }));
                } else {
                    $total_colaboradores = 0;
                    $total_equipamentos = 0;
                    $equipamentos_estoque = 0;
                }
                ?>
                <div class="footer-stats">
                    <div class="footer-stat">
                        <span class="stat-number"><?php echo $total_colaboradores; ?></span>
                        <span class="stat-label">Colaboradores</span>
                    </div>
                    <div class="footer-stat">
                        <span class="stat-number"><?php echo $total_equipamentos; ?></span>
                        <span class="stat-label">Equipamentos</span>
                    </div>
                    <div class="footer-stat">
                        <span class="stat-number"><?php echo $equipamentos_estoque; ?></span>
                        <span class="stat-label">Em Estoque</span>
                    </div>
                </div>
            </div>
            
<!--
            <div class="footer-section">
                <h3>Suporte</h3>
                <p><i class="fas fa-envelope"></i> suporte@sistema.com</p>
                <p><i class="fas fa-phone"></i> (11) 99999-9999</p>
                <p><i class="fas fa-clock"></i> Seg-Sex: 8h às 18h</p>
            </div>
-->
        </div>
        
        <div class="footer-bottom">
            <p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
            <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
        </div>
    </footer>
    
    <style>
    .footer {
        background: var(--dark-color);
        color: white;
        padding: 40px 0 20px;
        margin-top: 50px;
    }
    
    .footer-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
    }
    
    .footer-section h3 {
        color: white;
        margin-bottom: 20px;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .footer-section p {
        color: #bbb;
        margin-bottom: 10px;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .footer-links {
        list-style: none;
        padding: 0;
    }
    
    .footer-links li {
        margin-bottom: 10px;
    }
    
    .footer-links a {
        color: #bbb;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: color 0.3s;
    }
    
    .footer-links a:hover {
        color: var(--primary-color);
    }
    
    .footer-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }
    
    .footer-stat {
        text-align: center;
        padding: 10px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: var(--border-radius);
    }
    
    .stat-number {
        display: block;
        font-size: 1.5rem;
        font-weight: bold;
        color: white;
    }
    
    .stat-label {
        display: block;
        font-size: 0.8rem;
        color: #bbb;
    }
    
    .footer-bottom {
        max-width: 1200px;
        margin: 30px auto 0;
        padding: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        text-align: center;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .footer-bottom p {
        color: #bbb;
        font-size: 0.9rem;
        margin: 0;
    }
    
    .footer-version {
        font-size: 0.8rem !important;
        color: #999 !important;
    }
    
    @media (max-width: 768px) {
        .footer-content {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .footer-section {
            text-align: center;
        }
        
        .footer-links a {
            justify-content: center;
        }
        
        .footer-section h3 {
            justify-content: center;
        }
        
        .footer-section p {
            justify-content: center;
        }
    }
    </style>
    <?php endif; ?>
    
    <script src="<?php echo $base_path; ?>js/script.js"></script>
    <?php if (!empty($page_js)): ?>
    <script src="<?php echo $base_path . htmlspecialchars($page_js); ?>"></script>
    <?php endif; ?>
    
    <script>
    // Script para auto-ocultar alertas após 5 segundos
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-ocultar alertas globais
        const globalAlerts = document.querySelectorAll('.global-alert');
        globalAlerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 500);
            }, 5000);
        });
        
        // Auto-ocultar alertas normais
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s';
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 5000);
        });
        
        // Menu responsivo
        const menuToggle = document.getElementById('menuToggle');
        const navMenu = document.querySelector('.nav-menu');
        
        if (menuToggle && navMenu) {
            menuToggle.addEventListener('click', function() {
                navMenu.classList.toggle('show');
            });
        }
        
        // Fechar menu ao clicar em um link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                if (navMenu.classList.contains('show')) {
                    navMenu.classList.remove('show');
                }
            });
        });
    });
    
    // Função para confirmar exclusões
    function confirmDelete(message = 'Tem certeza que deseja excluir este item? Esta ação não pode ser desfeita.') {
        return confirm(message);
    }
    
    // Função para formatar CPF
    function formatCPF(cpf) {
        cpf = cpf.replace(/\D/g, '');
        return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    }
    
    // Função para validar CPF
    function validateCPF(cpf) {
        cpf = cpf.replace(/\D/g, '');
        
        if (cpf.length !== 11) return false;
        
        // Verificar se todos os dígitos são iguais
        if (/^(\d)\1+$/.test(cpf)) return false;
        
        // Validar CPF
        let sum = 0;
        let remainder;
        
        for (let i = 1; i <= 9; i++) {
            sum = sum + parseInt(cpf.substring(i-1, i)) * (11 - i);
        }
        
        remainder = (sum * 10) % 11;
        if ((remainder === 10) || (remainder === 11)) remainder = 0;
        if (remainder !== parseInt(cpf.substring(9, 10))) return false;
        
        sum = 0;
        for (let i = 1; i <= 10; i++) {
            sum = sum + parseInt(cpf.substring(i-1, i)) * (12 - i);
        }
        
        remainder = (sum * 10) % 11;
        if ((remainder === 10) || (remainder === 11)) remainder = 0;
        if (remainder !== parseInt(cpf.substring(10, 11))) return false;
        
        return true;
    }
    </script>
</body>
</html>