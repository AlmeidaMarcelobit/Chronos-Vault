<?php
session_start();
require_once '../includes/funcoes.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$mensagem = '';
$tipoMensagem = '';

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar e sanitizar os dados
    $nome = trim($_POST['nome'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '');
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
    $departamento = trim($_POST['departamento'] ?? '');
    $centro_custo = trim($_POST['centro_custo'] ?? '');
    
    // Validações
    $erros = [];
    
    if (empty($nome)) {
        $erros[] = 'O nome é obrigatório.';
    }
    
    if (empty($cargo)) {
        $erros[] = 'O cargo é obrigatório.';
    }
    
    if (empty($cpf) || !validarCPF($cpf)) {
        $erros[] = 'CPF inválido.';
    }
    
    if (empty($departamento)) {
        $erros[] = 'O departamento é obrigatório.';
    }
    
    if (empty($centro_custo)) {
        $erros[] = 'O centro de custo é obrigatório.';
    }
    
    // Verificar se CPF já existe
    $colaboradores = lerArquivoJSON('../data/colaboradores.json');
    foreach ($colaboradores as $colaborador) {
        if ($colaborador['cpf'] === $cpf) {
            $erros[] = 'Este CPF já está cadastrado no sistema.';
            break;
        }
    }
    
    if (empty($erros)) {
        // Criar novo colaborador
        $novoColaborador = [
            'id' => gerarId($colaboradores),
            'nome' => $nome,
            'cargo' => $cargo,
            'cpf' => $cpf,
            'departamento' => $departamento,
            'centro_custo' => $centro_custo,
            'data_cadastro' => date('Y-m-d H:i:s'),
            'data_atualizacao' => date('Y-m-d H:i:s')
        ];
        
        // Adicionar ao array
        $colaboradores[] = $novoColaborador;
        
        // Salvar no JSON
        if (salvarArquivoJSON('../data/colaboradores.json', $colaboradores)) {
            $mensagem = 'Colaborador cadastrado com sucesso!';
            $tipoMensagem = 'success';
            
            // Limpar o formulário
            $_POST = [];
        } else {
            $mensagem = 'Erro ao salvar o colaborador. Tente novamente.';
            $tipoMensagem = 'error';
        }
    } else {
        $mensagem = implode('<br>', $erros);
        $tipoMensagem = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Colaborador - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/colaboradores.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- ==================== HEADER ==================== -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <a href="../index.php">
                    <i class="fas fa-laptop-house"></i>
                    <h1>Sistema de Gestão</h1>
                </a>
            </div>
            
            <div class="user-menu">
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário'); ?></span>
                </div>
                
                <a href="../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sair</span>
                </a>
            </div>
        </div>
        
        <nav class="nav-container">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../index.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php" class="nav-link active">
                        <i class="fas fa-users"></i>
                        <span>Colaboradores</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../equipamentos/index.php" class="nav-link">
                        <i class="fas fa-laptop"></i>
                        <span>Equipamentos</span>
                    </a>
                </li>
            </ul>
        </nav>
    </header>
    
    <!-- Mensagens de alerta -->
    <?php if ($mensagem): ?>
    <div class="global-alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : 'error'; ?>">
        <div class="alert-content">
            <i class="fas fa-<?php echo $tipoMensagem === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <span><?php echo $mensagem; ?></span>
        </div>
        <button class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
    <?php endif; ?>
    
    <!-- ==================== CONTEÚDO PRINCIPAL ==================== -->
    <main class="main-container">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-user-plus"></i> Adicionar Novo Colaborador</h1>
                <p class="page-subtitle">Preencha os dados abaixo para cadastrar um novo colaborador</p>
            </div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>

        <div class="form-card-container">
            <form method="POST" action="" class="form-card" id="form-colaborador">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nome">
                            <i class="fas fa-user"></i>
                            <span>Nome Completo</span>
                            <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="nome" 
                               name="nome" 
                               value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>" 
                               required 
                               class="form-control" 
                               placeholder="Digite o nome completo"
                               autofocus>
                    </div>

                    <div class="form-group">
                        <label for="cargo">
                            <i class="fas fa-briefcase"></i>
                            <span>Cargo</span>
                            <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="cargo" 
                               name="cargo" 
                               value="<?php echo htmlspecialchars($_POST['cargo'] ?? ''); ?>" 
                               required 
                               class="form-control" 
                               placeholder="Digite o cargo">
                    </div>

                    <div class="form-group">
                        <label for="cpf">
                            <i class="fas fa-id-card"></i>
                            <span>CPF</span>
                            <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="cpf" 
                               name="cpf" 
                               value="<?php echo htmlspecialchars($_POST['cpf'] ?? ''); ?>" 
                               required 
                               class="form-control cpf-mask" 
                               placeholder="000.000.000-00"
                               maxlength="14">
                        <small class="form-text">Digite apenas números ou com pontos e traço</small>
                    </div>
                    
                    <?php include ('../includes/departamento.php'); ?>

                    <div class="form-group">
                        <label for="centro_custo">
                            <i class="fas fa-dollar-sign"></i>
                            <span>Centro de Custo</span>
                            <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="centro_custo" 
                               name="centro_custo" 
                               value="<?php echo htmlspecialchars($_POST['centro_custo'] ?? ''); ?>" 
                               required 
                               class="form-control cc-mask" 
                               placeholder="Ex: TI001, ADM002">
                        <small class="form-text">Código do centro de custo (ex: TI001, ADM002)</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <span>Salvar Colaborador</span>
                    </button>
                    <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                        <i class="fas fa-redo"></i>
                        <span>Limpar</span>
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        <span>Cancelar</span>
                    </a>
                </div>
            </form>
        </div>
    </main>

    <!-- ==================== FOOTER ==================== -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3><i class="fas fa-laptop-house"></i> Sistema de Gestão</h3>
                <p>Controle de colaboradores e equipamentos</p>
            </div>
            
            <div class="footer-section">
                <h3>Links Rápidos</h3>
                <ul class="footer-links">
                    <li><a href="../index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="index.php"><i class="fas fa-users"></i> Colaboradores</a></li>
                    <li><a href="../equipamentos/index.php"><i class="fas fa-laptop"></i> Equipamentos</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Estatísticas</h3>
                <?php
                // Carregar dados para estatísticas
                $total_colaboradores = count(lerArquivoJSON('../data/colaboradores.json'));
                $total_equipamentos = count(lerArquivoJSON('../data/equipamentos.json'));
                $equipamentos_estoque = 0;
                $equipamentos_data = lerArquivoJSON('../data/equipamentos.json');
                foreach ($equipamentos_data as $e) {
                    if (($e['status'] ?? '') === 'estoque') $equipamentos_estoque++;
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
        </div>
        
        <div class="footer-bottom">
            <p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
            <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
        </div>
    </footer>

    <script src="../js/script.js"></script>
    
    <script>
        // Fechar alerta após 5 segundos
        setTimeout(function() {
            const alert = document.querySelector('.global-alert');
            if (alert) {
                alert.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
        
        // Máscara para CPF
        const cpfInput = document.getElementById('cpf');
        if (cpfInput) {
            cpfInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value.length > 11) {
                    value = value.substring(0, 11);
                }
                
                if (value.length <= 11) {
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                }
                
                e.target.value = value;
            });
        }
        
        // Máscara para centro de custo
        const ccInput = document.getElementById('centro_custo');
        if (ccInput) {
            ccInput.addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase();
                value = value.replace(/[^A-Z0-9]/g, '');
                e.target.value = value;
            });
        }
        
        // Reset do formulário
        function resetForm() {
            if (confirm('Tem certeza que deseja limpar todos os campos?')) {
                document.getElementById('form-colaborador').reset();
            }
        }
        
        // Validação do formulário
        const form = document.getElementById('form-colaborador');
        if (form) {
            form.addEventListener('submit', function(e) {
                let valid = true;
                const cpfInput = document.getElementById('cpf');
                const cpfValue = cpfInput.value.replace(/\D/g, '');
                
                // Validar CPF
                if (cpfValue.length !== 11) {
                    alert('CPF deve conter 11 dígitos.');
                    cpfInput.focus();
                    valid = false;
                }
                
                if (!valid) {
                    e.preventDefault();
                }
            });
        }
    </script>
</body>
</html>