<?php
session_start();
require_once '../includes/funcoes.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$colaboradores = lerArquivoJSON('../data/colaboradores.json');

// Encontrar colaborador
$colaboradorIndex = null;
foreach ($colaboradores as $index => $colaborador) {
    if ($colaborador['id'] == $id) {
        $colaboradorIndex = $index;
        $colaboradorAtual = $colaborador;
        break;
    }
}

if ($colaboradorIndex === null) {
    header('Location: index.php');
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

    // Verificar se CPF já existe (exceto para o próprio colaborador)
    foreach ($colaboradores as $index => $colaborador) {
        if ($index != $colaboradorIndex && $colaborador['cpf'] === $cpf) {
            $erros[] = 'Este CPF já está cadastrado no sistema para outro colaborador.';
            break;
        }
    }

    if (empty($erros)) {
        // Atualizar colaborador
        $colaboradores[$colaboradorIndex]['nome'] = $nome;
        $colaboradores[$colaboradorIndex]['cargo'] = $cargo;
        $colaboradores[$colaboradorIndex]['cpf'] = $cpf;
        $colaboradores[$colaboradorIndex]['departamento'] = $departamento;
        $colaboradores[$colaboradorIndex]['centro_custo'] = $centro_custo;
        $colaboradores[$colaboradorIndex]['data_atualizacao'] = date('Y-m-d H:i:s');

        // Salvar no JSON
        if (salvarArquivoJSON('../data/colaboradores.json', $colaboradores)) {
            $mensagem = 'Colaborador atualizado com sucesso!';
            $tipoMensagem = 'success';

            // Atualizar dados locais
            $colaboradorAtual = $colaboradores[$colaboradorIndex];
        } else {
            $mensagem = 'Erro ao atualizar o colaborador. Tente novamente.';
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
    <title>Editar Colaborador - Sistema de Gestão</title>
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
                <h1><i class="fas fa-edit"></i> Editar Colaborador</h1>
                <p class="page-subtitle">Atualize as informações do colaborador</p>
            </div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>

        <div class="form-card-container">
            <!-- Informações do Colaborador -->
            <div class="info-card">
                <h3><i class="fas fa-info-circle"></i> Informações do Colaborador</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">ID:</span>
                        <span class="info-value"><?php echo $colaboradorAtual['id']; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Data de Cadastro:</span>
                        <span class="info-value"><?php echo formatarData($colaboradorAtual['data_cadastro']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Última Atualização:</span>
                        <span class="info-value"><?php echo isset($colaboradorAtual['data_atualizacao']) ? formatarData($colaboradorAtual['data_atualizacao']) : '---'; ?></span>
                    </div>
                </div>
            </div>

            <!-- Formulário de Edição -->
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
                               value="<?php echo htmlspecialchars($colaboradorAtual['nome']); ?>" 
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
                               value="<?php echo htmlspecialchars($colaboradorAtual['cargo']); ?>" 
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
                               value="<?php echo formatarCPF($colaboradorAtual['cpf']); ?>" 
                               required 
                               class="form-control cpf-mask" 
                               placeholder="000.000.000-00"
                               maxlength="14">
                        <small class="form-text">Digite apenas números ou com pontos e traço</small>
                    </div>

                    <div class="form-group">
                        <label for="departamento">
                            <i class="fas fa-building"></i>
                            <span>Departamento</span>
                            <span class="required">*</span>
                        </label>
                        <select id="departamento" name="departamento" required class="form-select">
                            <option value="">-- Selecione um departamento --</option>
                            <option value="Dental Vidas Administrativo" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Dental Vidas Administrativo' ? 'selected' : ''; ?>>Dental Vidas Administrativo</option>
                            <option value="Relacionamento com Profissionais da Saúde" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Relacionamento com Profissionais da Saúde' ? 'selected' : ''; ?>>Relacionamento com Profissionais da Saúde</option>
                            <option value="AmorLab" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'AmorLab' ? 'selected' : ''; ?>>AmorLab</option>
                            <option value="Financeiro" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Financeiro' ? 'selected' : ''; ?>>Financeiro</option>
                            <option value="Cadastro" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Cadastro' ? 'selected' : ''; ?>>Cadastro</option>
                            <option value="Infraestrutura" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Infraestrutura' ? 'selected' : ''; ?>>Infraestrutura</option>
                            <option value="Retenção" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Retenção' ? 'selected' : ''; ?>>Retenção</option>
                            <option value="Diretoria CEO" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Diretoria CEO' ? 'selected' : ''; ?>>Diretoria CEO</option>
                            <option value="Atendimento" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Atendimento' ? 'selected' : ''; ?>>Atendimento</option>
                            <option value="SAC" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'SAC' ? 'selected' : ''; ?>>SAC</option>
                            <option value="SAF" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'SAF' ? 'selected' : ''; ?>>SAF</option>
                            <option value="Contabilidade" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Contabilidade' ? 'selected' : ''; ?>>Contabilidade</option>
                            <option value="Integração" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Integração' ? 'selected' : ''; ?>>Integração</option>
                            <option value="BackOffice" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'BackOffice' ? 'selected' : ''; ?>>BackOffice</option>
                            <option value="Gestão de Rede" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Gestão de Rede' ? 'selected' : ''; ?>>Gestão de Rede</option>
                            <option value="Técnico" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Técnico' ? 'selected' : ''; ?>>Técnico</option>
                            <option value="Remuneração E Beneficios" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Remuneração E Beneficios' ? 'selected' : ''; ?>>Remuneração E Beneficios</option>
                            <option value="Consultoria de Performance" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Consultoria de Performance' ? 'selected' : ''; ?>>Consultoria de Performance</option>
                            <option value="Internacional" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Internacional' ? 'selected' : ''; ?>>Internacional</option>
                            <option value="Assessoria Regional" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Assessoria Regional' ? 'selected' : ''; ?>>Assessoria Regional</option>
                            <option value="Qualidade de Atendimento" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Qualidade de Atendimento' ? 'selected' : ''; ?>>Qualidade de Atendimento</option>
                            <option value="Cirurgias" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Cirurgias' ? 'selected' : ''; ?>>Cirurgias</option>
                            <option value="Telemedicina" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Telemedicina' ? 'selected' : ''; ?>>Telemedicina</option>
                            <option value="Suporte" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Suporte' ? 'selected' : ''; ?>>Suporte</option>
                            <option value="Treinamento" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Treinamento' ? 'selected' : ''; ?>>Treinamento</option>
                            <option value="Inteligência de Negócio" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Inteligência de Negócio' ? 'selected' : ''; ?>>Inteligência de Negócio</option>
                            <option value="TI Tecnologia" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'TI Tecnologia' ? 'selected' : ''; ?>>TI Tecnologia</option>
                            <option value="Atendimento a Franquia" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Atendimento a Franquia' ? 'selected' : ''; ?>>Atendimento a Franquia</option>
                            <option value="Diretoria de Pessoas e Cultura" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Diretoria de Pessoas e Cultura' ? 'selected' : ''; ?>>Diretoria de Pessoas e Cultura</option>
                            <option value="Governança TI" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Governança TI' ? 'selected' : ''; ?>>Governança TI</option>
                            <option value="CRM" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'CRM' ? 'selected' : ''; ?>>CRM</option>
                            <option value="Diretoria de Marketing" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Diretoria de Marketing' ? 'selected' : ''; ?>>Diretoria de Marketing</option>
                            <option value="Marketing Internacional" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Marketing Internacional' ? 'selected' : ''; ?>>Marketing Internacional</option>
                            <option value="Pessoas" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Pessoas' ? 'selected' : ''; ?>>Pessoas</option>
                            <option value="Cultura" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Cultura' ? 'selected' : ''; ?>>Cultura</option>
                            <option value="Produto" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Produto' ? 'selected' : ''; ?>>Produto</option>
                            <option value="Desenvolvimento" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Desenvolvimento' ? 'selected' : ''; ?>>Desenvolvimento</option>
                            <option value="Atendimento ao Cliente" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Atendimento ao Cliente' ? 'selected' : ''; ?>>Atendimento ao Cliente</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="centro_custo">
                            <i class="fas fa-dollar-sign"></i>
                            <span>Centro de Custo</span>
                            <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="centro_custo" 
                               name="centro_custo" 
                               value="<?php echo htmlspecialchars($colaboradorAtual['centro_custo']); ?>" 
                               required 
                               class="form-control cc-mask" 
                               placeholder="Ex: TI001, ADM002">
                        <small class="form-text">Código do centro de custo (ex: TI001, ADM002)</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <span>Atualizar Colaborador</span>
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
    </script>
</body>
</html>