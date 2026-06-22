<?php
session_start();
require_once '../includes/funcoes.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

// Verificar nível do usuário
$usuario_nivel = $_SESSION['usuario_nivel'] ?? 'user';
$is_admin = ($usuario_nivel === 'admin');
$is_view = ($usuario_nivel === 'view');
$can_edit = ($is_admin || $usuario_nivel === 'user');

$mensagem = '';
$tipoMensagem = '';

// Carregar colaboradores do novo caminho (ativos.json)
$colaboradores = lerArquivoJSON('../data/colaboradores/ativos.json');
if ($colaboradores === false) $colaboradores = [];

// Função para mesclar dados do colaborador (mantém dados não vazios)
function mesclarDadosColaborador($existente, $novo) {
    // Mesclar campos básicos (apenas se o novo valor não estiver vazio)
    if (!empty($novo['nome']) && $novo['nome'] !== ($existente['nome'] ?? '')) {
        $existente['nome'] = $novo['nome'];
    }
    if (!empty($novo['cargo']) && $novo['cargo'] !== ($existente['cargo'] ?? '')) {
        $existente['cargo'] = $novo['cargo'];
    }
    if (!empty($novo['cpf']) && $novo['cpf'] !== ($existente['cpf'] ?? '')) {
        $existente['cpf'] = $novo['cpf'];
    }
    if (!empty($novo['departamento']) && $novo['departamento'] !== ($existente['departamento'] ?? '')) {
        $existente['departamento'] = $novo['departamento'];
    }
    if (!empty($novo['centro_custo']) && $novo['centro_custo'] !== ($existente['centro_custo'] ?? '')) {
        $existente['centro_custo'] = $novo['centro_custo'];
    }
    if (!empty($novo['email']) && $novo['email'] !== ($existente['email'] ?? '')) {
        $existente['email'] = $novo['email'];
    }
    if (!empty($novo['tipo_trabalho']) && $novo['tipo_trabalho'] !== ($existente['tipo_trabalho'] ?? 'local')) {
        $existente['tipo_trabalho'] = $novo['tipo_trabalho'];
    }
    
    // Mesclar endereço (se for Home Office e tiver dados novos)
    if (($novo['tipo_trabalho'] ?? 'local') === 'home' && !empty($novo['endereco'])) {
        if (!isset($existente['endereco']) || !is_array($existente['endereco'])) {
            $existente['endereco'] = [];
        }
        
        $camposEndereco = ['logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'estado', 'cep'];
        foreach ($camposEndereco as $campo) {
            if (!empty($novo['endereco'][$campo]) && ($existente['endereco'][$campo] ?? '') !== $novo['endereco'][$campo]) {
                $existente['endereco'][$campo] = $novo['endereco'][$campo];
            }
        }
        
        // Se endereço ficou vazio, remover
        if (empty(array_filter($existente['endereco']))) {
            $existente['endereco'] = null;
        }
    }
    
    // Atualizar data de modificação
    $existente['data_atualizacao'] = date('Y-m-d H:i:s');
    
    return $existente;
}

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar e sanitizar os dados
    $matricula = trim($_POST['matricula'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '');
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
    $departamento = trim($_POST['departamento'] ?? '');
    $centro_custo = trim($_POST['centro_custo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tipo_trabalho = $_POST['tipo_trabalho'] ?? 'local';
    $endereco = trim($_POST['endereco'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');
    $bairro = trim($_POST['bairro'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $cep = preg_replace('/[^0-9]/', '', $_POST['cep'] ?? '');

    // NENHUMA VALIDAÇÃO OBRIGATÓRIA - TODOS OS CAMPOS SÃO OPCIONAIS
    $erros = [];

    // Apenas validações de formato se os campos forem preenchidos
    if (!empty($cpf) && !validarCPF($cpf)) {
        $erros[] = 'CPF inválido.';
    }

    // CPF deve ser único (apenas se informado)
    if (!empty($cpf)) {
        foreach ($colaboradores as $colab) {
            if (!empty($colab['cpf']) && preg_replace('/[^0-9]/', '', $colab['cpf']) === $cpf) {
                $erros[] = 'CPF já cadastrado para o colaborador "' . htmlspecialchars($colab['nome'] ?? 'sem nome') . '".';
                break;
            }
        }
    }

    // Validar e-mail se informado
    if (!empty($email) && !validarEmail($email)) {
        $erros[] = 'E-mail inválido.';
    }

    // Validar endereço se for Home Office e tiver dados
    if ($tipo_trabalho === 'home') {
        if (!empty($endereco) && empty($numero)) {
            $erros[] = 'Se o endereço for informado, o número é obrigatório.';
        }
        if (!empty($cep) && !validarCEP($cep)) {
            $erros[] = 'CEP inválido.';
        }
    }

    if (empty($erros)) {
        // VERIFICAR SE JÁ EXISTE UM COLABORADOR COM ESTA MATRÍCULA
        $colaboradorExistente = null;
        $colaboradorIndex = null;
        
        if (!empty($matricula)) {
            foreach ($colaboradores as $index => $colab) {
                if (isset($colab['matricula']) && $colab['matricula'] === $matricula) {
                    $colaboradorExistente = $colab;
                    $colaboradorIndex = $index;
                    break;
                }
            }
        }
        
        // Se encontrou colaborador existente por matrícula, mesclar dados
        if ($colaboradorExistente) {
            // Preparar dados do novo colaborador para mesclagem
            $novoDados = [
                'nome' => $nome,
                'cargo' => $cargo,
                'cpf' => $cpf,
                'departamento' => $departamento,
                'centro_custo' => $centro_custo,
                'email' => $email ?: null,
                'tipo_trabalho' => $tipo_trabalho,
                'endereco' => $tipo_trabalho === 'home' && !empty($endereco) ? [
                    'logradouro' => $endereco,
                    'numero' => $numero,
                    'complemento' => $complemento ?: null,
                    'bairro' => $bairro,
                    'cidade' => $cidade,
                    'estado' => $estado,
                    'cep' => $cep ?: null
                ] : null
            ];
            
            // Mesclar dados
            $colaboradores[$colaboradorIndex] = mesclarDadosColaborador($colaboradorExistente, $novoDados);
            
            // Salvar no JSON
            if (salvarArquivoJSON('../data/colaboradores/ativos.json', $colaboradores)) {
                $mensagem = 'Colaborador atualizado com sucesso! (Dados mesclados)';
                $tipoMensagem = 'success';
                
                // Limpar o formulário
                $_POST = [];
            } else {
                $mensagem = 'Erro ao atualizar o colaborador. Tente novamente.';
                $tipoMensagem = 'error';
            }
        } else {
            // Criar novo colaborador
            $novoColaborador = [
                'id' => gerarId($colaboradores),
                'matricula' => $matricula ?: null,
                'nome' => $nome ?: null,
                'cargo' => $cargo ?: null,
                'cpf' => $cpf ?: null,
                'departamento' => $departamento ?: null,
                'centro_custo' => $centro_custo ?: null,
                'email' => $email ?: null,
                'tipo_trabalho' => $tipo_trabalho,
                'endereco' => $tipo_trabalho === 'home' && !empty($endereco) ? [
                    'logradouro' => $endereco,
                    'numero' => $numero,
                    'complemento' => $complemento ?: null,
                    'bairro' => $bairro,
                    'cidade' => $cidade,
                    'estado' => $estado,
                    'cep' => $cep ?: null
                ] : null,
                'data_cadastro' => date('Y-m-d H:i:s'),
                'data_atualizacao' => date('Y-m-d H:i:s')
            ];
            
            // Adicionar ao array
            $colaboradores[] = $novoColaborador;
            
            // Salvar no JSON
            if (salvarArquivoJSON('../data/colaboradores/ativos.json', $colaboradores)) {
                $mensagem = 'Colaborador cadastrado com sucesso!';
                $tipoMensagem = 'success';
                
                // Limpar o formulário
                $_POST = [];
            } else {
                $mensagem = 'Erro ao salvar o colaborador. Tente novamente.';
                $tipoMensagem = 'error';
            }
        }
    }
    
    if (!empty($erros)) {
        $mensagem = implode('<br>', $erros);
        $tipoMensagem = 'error';
    }
}

// Função para formatar CEP
function formatarCEP($cep) {
    $cep = preg_replace('/[^0-9]/', '', $cep);
    if (strlen($cep) == 8) {
        return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
    }
    return $cep;
}

// Estatísticas para o footer
$total_colaboradores = count(lerArquivoJSON('../data/colaboradores/ativos.json'));
$total_equipamentos = count(lerArquivoJSON('../data/equipamentos.json'));
$equipamentos_data = lerArquivoJSON('../data/equipamentos.json');
$equipamentos_estoque = 0;
foreach ($equipamentos_data as $e) {
    if (($e['status'] ?? '') === 'estoque') $equipamentos_estoque++;
}
?>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Colaborador - Gestão de Colaboradores</title>
    <link rel="stylesheet" href="../css/colaboradores/adicionar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/favicon/favicon.png">
</head>
<body>
<!-- ==================== HEADER ==================== -->
<header class="header">
    <div class="header-content">
        <div class="logo">
            <a href="../index.php">
                <i class="fas fa-user-plus"></i>
                <h1>Gestão de Colaboradores</h1>
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
            <li class="nav-item"><a href="../index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="index.php" class="nav-link active"><i class="fas fa-users"></i><span>Colaboradores</span></a></li>
            <li class="nav-item"><a href="../equipamentos/index.php" class="nav-link"><i class="fas fa-laptop"></i><span>Equipamentos</span></a></li>
            <li class="nav-item"><a href="../linhas/index.php" class="nav-link"><i class="fas fa-phone"></i><span>Linhas</span></a></li>
            <?php if ($is_admin): ?>
                <li class="nav-item"><a href="../Termos/index.php" class="nav-link"><i class="fas fa-file-contract"></i><span>Termos</span></a></li>
                <li class="nav-item"><a href="../usuarios/index.php" class="nav-link"><i class="fas fa-user-cog"></i><span>Usuários</span></a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<!-- Mensagens de alerta -->
<?php if ($mensagem): ?>
    <div class="global-alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : ($tipoMensagem === 'info' ? 'info' : 'error'); ?>">
        <div class="alert-content">
            <i class="fas fa-<?php echo $tipoMensagem === 'success' ? 'check-circle' : ($tipoMensagem === 'info' ? 'info-circle' : 'exclamation-circle'); ?>"></i>
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
            <p class="page-subtitle" style="color: var(--info); margin-top: 5px;">
                <i class="fas fa-info-circle"></i> 
                <strong>Nota:</strong> Todos os campos são opcionais. Se a matrícula já existir, os dados serão automaticamente mesclados.
            </p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="form-card-container">
        <form method="POST" action="" class="form-card" id="form-colaborador">
            <div class="form-grid">
                <!-- Campo Matrícula (Chamado) - OPCIONAL -->
                <div class="form-group">
                    <label for="matricula">
                        <i class="fas fa-id-badge"></i>
                        <span>Chamado / Matrícula</span>
                    </label>
                    <input type="text"
                           id="matricula"
                           name="matricula"
                           value="<?php echo htmlspecialchars($_POST['matricula'] ?? ''); ?>"
                           class="form-control"
                           placeholder="Ex: #251506, #255676"
                           autofocus>
                    <small class="form-text">Número do chamado do colaborador - Se já existir, os dados serão mesclados</small>
                </div>

                <div class="form-group">
                    <label for="nome">
                        <i class="fas fa-user"></i>
                        <span>Nome Completo</span>
                        
                    </label>
                    <input type="text"
                           id="nome"
                           name="nome"
                           value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>"
                           class="form-control"
                           placeholder="Digite o nome completo">
                </div>

                <div class="form-group">
                    <label for="cargo">
                        <i class="fas fa-briefcase"></i>
                        <span>Cargo</span>
                        
                    </label>
                    <input type="text"
                           id="cargo"
                           name="cargo"
                           value="<?php echo htmlspecialchars($_POST['cargo'] ?? ''); ?>"
                           class="form-control"
                           placeholder="Digite o cargo">
                </div>

                <div class="form-group">
                    <label for="cpf">
                        <i class="fas fa-id-card"></i>
                        <span>CPF</span>
                        
                    </label>
                    <input type="text"
                           id="cpf"
                           name="cpf"
                           value="<?php echo htmlspecialchars($_POST['cpf'] ?? ''); ?>"
                           class="form-control cpf-mask"
                           placeholder="000.000.000-00"
                           maxlength="14">
                    <!--<small class="form-text">Digite apenas números ou com pontos e traço</small>-->
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        <span>E-mail</span>
                        
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           class="form-control"
                           placeholder="colaborador@empresa.com.br">
                </div>

                <div class="form-group">
                    <label for="tipo_trabalho">
                        <i class="fas fa-briefcase"></i>
                        <span>Tipo de Trabalho</span>
                        
                    </label>
                    <select id="tipo_trabalho" name="tipo_trabalho" class="form-select" onchange="toggleEnderecoFields()">
                        <option value="local" <?php echo ($_POST['tipo_trabalho'] ?? 'local') == 'local' ? 'selected' : ''; ?>>Presencial (Local)</option>
                        <option value="home" <?php echo ($_POST['tipo_trabalho'] ?? '') == 'home' ? 'selected' : ''; ?>>Home Office</option>
                    </select>
                </div>
            </div>

            <!-- Seção de Endereço (visível apenas quando Home Office) -->
            <div id="endereco-section" style="display: <?php echo (($_POST['tipo_trabalho'] ?? '') == 'home') ? 'block' : 'none'; ?>;">
                <div class="section-divider">
                    <h3><i class="fas fa-home"></i> Endereço Residencial</h3>
                    <small class="form-text">Todos os campos de endereço são opcionais</small>
                </div>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="endereco">
                            <i class="fas fa-road"></i>
                            <span>Logradouro</span>
                            
                        </label>
                        <input type="text"
                               id="endereco"
                               name="endereco"
                               value="<?php echo htmlspecialchars($_POST['endereco'] ?? ''); ?>"
                               class="form-control"
                               placeholder="Rua, Avenida, Alameda...">
                    </div>

                    <div class="form-group">
                        <label for="numero">
                            <i class="fas fa-hashtag"></i>
                            <span>Número</span>
                            
                        </label>
                        <input type="text"
                               id="numero"
                               name="numero"
                               value="<?php echo htmlspecialchars($_POST['numero'] ?? ''); ?>"
                               class="form-control"
                               placeholder="Número">
                    </div>

                    <div class="form-group">
                        <label for="complemento">
                            <i class="fas fa-plus-circle"></i>
                            <span>Complemento</span>
                            
                        </label>
                        <input type="text"
                               id="complemento"
                               name="complemento"
                               value="<?php echo htmlspecialchars($_POST['complemento'] ?? ''); ?>"
                               class="form-control"
                               placeholder="Apto, Bloco, Casa...">
                    </div>

                    <div class="form-group">
                        <label for="bairro">
                            <i class="fas fa-location-dot"></i>
                            <span>Bairro</span>
                            
                        </label>
                        <input type="text"
                               id="bairro"
                               name="bairro"
                               value="<?php echo htmlspecialchars($_POST['bairro'] ?? ''); ?>"
                               class="form-control"
                               placeholder="Bairro">
                    </div>

                    <div class="form-group">
                        <label for="cidade">
                            <i class="fas fa-city"></i>
                            <span>Cidade</span>
                            
                        </label>
                        <input type="text"
                               id="cidade"
                               name="cidade"
                               value="<?php echo htmlspecialchars($_POST['cidade'] ?? ''); ?>"
                               class="form-control"
                               placeholder="Cidade">
                    </div>

                    <div class="form-group">
                        <label for="estado">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Estado</span>
                            
                        </label>
                        <select id="estado" name="estado" class="form-select">
                            <option value="">Selecione o estado</option>
                            <?php foreach (getEstados() as $sigla => $nome): ?>
                                <option value="<?php echo $sigla; ?>" <?php echo (($_POST['estado'] ?? '') == $sigla) ? 'selected' : ''; ?>>
                                    <?php echo $sigla . ' - ' . $nome; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cep">
                            <i class="fas fa-mail-bulk"></i>
                            <span>CEP</span>
                            
                        </label>
                        <input type="text"
                               id="cep"
                               name="cep"
                               value="<?php echo htmlspecialchars($_POST['cep'] ?? ''); ?>"
                               class="form-control cep-mask"
                               placeholder="00000-000"
                               maxlength="9">
                    </div>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="departamento">
                        <i class="fas fa-building"></i>
                        <span>Departamento</span>
                        
                    </label>
                    <?php include '../includes/departamentos.php' ?>
                </div>

                <div class="form-group">
                    <label for="centro_custo">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Centro de Custo</span>
                        
                    </label>
                    <input type="text"
                           id="centro_custo"
                           name="centro_custo"
                           value="<?php echo htmlspecialchars($_POST['centro_custo'] ?? ''); ?>"
                           class="form-control cc-mask"
                           placeholder="Ex: 12001, 12002">
                    <small class="form-text">Código do centro de custo</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Colaborador
                </button>
                <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-redo"></i> Limpar
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</main>

<!-- ==================== FOOTER ==================== -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3><i class="fas fa-user-plus"></i> Gestão de Colaboradores</h3>
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
            <div class="footer-stats">
                <div class="footer-stat"><span class="stat-number"><?php echo $total_colaboradores; ?></span><span class="stat-label">Colaboradores</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo $total_equipamentos; ?></span><span class="stat-label">Equipamentos</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo $equipamentos_estoque; ?></span><span class="stat-label">Em Estoque</span></div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
        <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
</footer>

<script>
    // Mostrar/esconder campos de endereço
    const selectTipoTrabalho = document.getElementById('tipo_trabalho');
    const enderecoSection = document.getElementById('endereco-section');

    function toggleEnderecoFields() {
        if (selectTipoTrabalho.value === 'home') {
            enderecoSection.style.display = 'block';
        } else {
            enderecoSection.style.display = 'none';
        }
    }

    // Máscara para CPF
    const cpfInput = document.getElementById('cpf');
    if (cpfInput) {
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.substring(0, 11);
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            }
            e.target.value = value;
        });
    }

    // Máscara para CEP
    const cepInput = document.getElementById('cep');
    if (cepInput) {
        cepInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 8) value = value.substring(0, 8);
            if (value.length > 5) {
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
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
            toggleEnderecoFields();
        }
    }

    // Inicializar estado do endereço
    document.addEventListener('DOMContentLoaded', function() {
        toggleEnderecoFields();
    });
</script>
</body>
</html>