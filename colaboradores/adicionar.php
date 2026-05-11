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

// Carregar colaboradores para o select de gestor
$colaboradores = lerArquivoJSON('../data/colaboradores.json');

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar e sanitizar os dados
    $nome = trim($_POST['nome'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '');
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
    $departamento = trim($_POST['departamento'] ?? '');
    $centro_custo = trim($_POST['centro_custo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gestor_id = !empty($_POST['gestor_id']) ? $_POST['gestor_id'] : null;
    
    // Novos campos de endereço
    $tipo_trabalho = $_POST['tipo_trabalho'] ?? 'local';
    $endereco = trim($_POST['endereco'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');
    $bairro = trim($_POST['bairro'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $cep = preg_replace('/[^0-9]/', '', $_POST['cep'] ?? '');
    
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
    
    // Validar e-mail se informado
    if (!empty($email) && !validarEmail($email)) {
        $erros[] = 'E-mail inválido.';
    }
    
    // Validar endereço se for Home Office
    if ($tipo_trabalho === 'home') {
        if (empty($endereco)) {
            $erros[] = 'O endereço é obrigatório para Home Office.';
        }
        if (empty($numero)) {
            $erros[] = 'O número é obrigatório para Home Office.';
        }
        if (empty($bairro)) {
            $erros[] = 'O bairro é obrigatório para Home Office.';
        }
        if (empty($cidade)) {
            $erros[] = 'A cidade é obrigatória para Home Office.';
        }
        if (empty($estado)) {
            $erros[] = 'O estado é obrigatório para Home Office.';
        }
        if (!empty($cep) && !validarCEP($cep)) {
            $erros[] = 'CEP inválido.';
        }
    }
    
    // Verificar se CPF já existe
    foreach ($colaboradores as $colaborador) {
        if ($colaborador['cpf'] === $cpf) {
            $erros[] = 'Este CPF já está cadastrado no sistema.';
            break;
        }
    }
    
    // Verificar se e-mail já existe (se informado)
    if (!empty($email)) {
        foreach ($colaboradores as $colaborador) {
            if (isset($colaborador['email']) && $colaborador['email'] === $email) {
                $erros[] = 'Este e-mail já está cadastrado no sistema.';
                break;
            }
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
            'email' => $email ?: null,
            'gestor_id' => $gestor_id,
            'tipo_trabalho' => $tipo_trabalho,
            'endereco' => $tipo_trabalho === 'home' ? [
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

// Função para formatar CEP
function formatarCEP($cep) {
    $cep = preg_replace('/[^0-9]/', '', $cep);
    if (strlen($cep) == 8) {
        return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
    }
    return $cep;
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
                        <small class="form-text">E-mail institucional do colaborador (opcional)</small>
                    </div>

                    <div class="form-group">
                        <label for="tipo_trabalho">
                            <i class="fas fa-briefcase"></i>
                            <span>Tipo de Trabalho</span>
                            <span class="required">*</span>
                        </label>
                        <select id="tipo_trabalho" name="tipo_trabalho" required class="form-control" onchange="toggleEnderecoFields()">
                            <option value="local" <?php echo ($_POST['tipo_trabalho'] ?? 'local') == 'local' ? 'selected' : ''; ?>>Presencial (Local)</option>
                            <option value="home" <?php echo ($_POST['tipo_trabalho'] ?? '') == 'home' ? 'selected' : ''; ?>>Home Office</option>
                        </select>
                    </div>
                </div>

                <!-- Seção de Endereço (visível apenas quando Home Office) -->
                <div id="endereco-section" style="display: <?php echo (($_POST['tipo_trabalho'] ?? '') == 'home') ? 'block' : 'none'; ?>;">
                    <div class="section-divider">
                        <h3><i class="fas fa-home"></i> Endereço Residencial</h3>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="endereco">
                                <i class="fas fa-road"></i>
                                <span>Logradouro</span>
                                <span class="required">*</span>
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
                                <span class="required">*</span>
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
                                <span class="required">*</span>
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
                                <span class="required">*</span>
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
                                <span class="required">*</span>
                            </label>
                            <select id="estado" name="estado" class="form-control">
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
                            <small class="form-text">Opcional - Formato: 00000-000</small>
                        </div>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="departamento">
                            <i class="fas fa-building"></i>
                            <span>Departamento</span>
                            <span class="required">*</span>
                        </label>
                        <select id="departamento" name="departamento" required class="form-control">
                            <option value="">Selecione um departamento</option>
                            <option value="AmorLab" <?php echo ($_POST['departamento'] ?? '') == 'AmorLab' ? 'selected' : ''; ?>>AmorLab</option>
                            <option value="Assessoria Regional" <?php echo ($_POST['departamento'] ?? '') == 'Assessoria Regional' ? 'selected' : ''; ?>>Assessoria Regional</option>
                            <option value="Atendimento" <?php echo ($_POST['departamento'] ?? '') == 'Atendimento' ? 'selected' : ''; ?>>Atendimento</option>
                            <option value="Atendimento a Franquia" <?php echo ($_POST['departamento'] ?? '') == 'Atendimento a Franquia' ? 'selected' : ''; ?>>Atendimento a Franquia</option>
                            <option value="Atendimento ao Cliente" <?php echo ($_POST['departamento'] ?? '') == 'Atendimento ao Cliente' ? 'selected' : ''; ?>>Atendimento ao Cliente</option>
                            <option value="BackOffice" <?php echo ($_POST['departamento'] ?? '') == 'BackOffice' ? 'selected' : ''; ?>>BackOffice</option>
                            <option value="Cadastro" <?php echo ($_POST['departamento'] ?? '') == 'Cadastro' ? 'selected' : ''; ?>>Cadastro</option>
                            <option value="Cirurgias" <?php echo ($_POST['departamento'] ?? '') == 'Cirurgias' ? 'selected' : ''; ?>>Cirurgias</option>
                            <option value="Consultoria de Performance" <?php echo ($_POST['departamento'] ?? '') == 'Consultoria de Performance' ? 'selected' : ''; ?>>Consultoria de Performance</option>
                            <option value="Contabilidade" <?php echo ($_POST['departamento'] ?? '') == 'Contabilidade' ? 'selected' : ''; ?>>Contabilidade</option>
                            <option value="CRM" <?php echo ($_POST['departamento'] ?? '') == 'CRM' ? 'selected' : ''; ?>>CRM</option>
                            <option value="Cultura" <?php echo ($_POST['departamento'] ?? '') == 'Cultura' ? 'selected' : ''; ?>>Cultura</option>
                            <option value="Dental Vidas Administrativo" <?php echo ($_POST['departamento'] ?? '') == 'Dental Vidas Administrativo' ? 'selected' : ''; ?>>Dental Vidas Administrativo</option>
                            <option value="Desenvolvimento" <?php echo ($_POST['departamento'] ?? '') == 'Desenvolvimento' ? 'selected' : ''; ?>>Desenvolvimento</option>
                            <option value="Diretoria CEO" <?php echo ($_POST['departamento'] ?? '') == 'Diretoria CEO' ? 'selected' : ''; ?>>Diretoria CEO</option>
                            <option value="Diretoria de Marketing" <?php echo ($_POST['departamento'] ?? '') == 'Diretoria de Marketing' ? 'selected' : ''; ?>>Diretoria de Marketing</option>
                            <option value="Diretoria de Pessoas e Cultura" <?php echo ($_POST['departamento'] ?? '') == 'Diretoria de Pessoas e Cultura' ? 'selected' : ''; ?>>Diretoria de Pessoas e Cultura</option>
                            <option value="Financeiro" <?php echo ($_POST['departamento'] ?? '') == 'Financeiro' ? 'selected' : ''; ?>>Financeiro</option>
                            <option value="Gestão de Rede" <?php echo ($_POST['departamento'] ?? '') == 'Gestão de Rede' ? 'selected' : ''; ?>>Gestão de Rede</option>
                            <option value="Infraestrutura" <?php echo ($_POST['departamento'] ?? '') == 'Infraestrutura' ? 'selected' : ''; ?>>Infraestrutura</option>
                            <option value="Integração" <?php echo ($_POST['departamento'] ?? '') == 'Integração' ? 'selected' : ''; ?>>Integração</option>
                            <option value="Inteligência de Negócio" <?php echo ($_POST['departamento'] ?? '') == 'Inteligência de Negócio' ? 'selected' : ''; ?>>Inteligência de Negócio</option>
                            <option value="Internacional" <?php echo ($_POST['departamento'] ?? '') == 'Internacional' ? 'selected' : ''; ?>>Internacional</option>
                            <option value="Marketing Internacional" <?php echo ($_POST['departamento'] ?? '') == 'Marketing Internacional' ? 'selected' : ''; ?>>Marketing Internacional</option>
                            <option value="Pessoas" <?php echo ($_POST['departamento'] ?? '') == 'Pessoas' ? 'selected' : ''; ?>>Pessoas</option>
                            <option value="Produto" <?php echo ($_POST['departamento'] ?? '') == 'Produto' ? 'selected' : ''; ?>>Produto</option>
                            <option value="Qualidade de Atendimento" <?php echo ($_POST['departamento'] ?? '') == 'Qualidade de Atendimento' ? 'selected' : ''; ?>>Qualidade de Atendimento</option>
                            <option value="Relacionamento com Profissionais da Saúde" <?php echo ($_POST['departamento'] ?? '') == 'Relacionamento com Profissionais da Saúde' ? 'selected' : ''; ?>>Relacionamento com Profissionais da Saúde</option>
                            <option value="Remuneração e Benefícios" <?php echo ($_POST['departamento'] ?? '') == 'Remuneração e Benefícios' ? 'selected' : ''; ?>>Remuneração e Benefícios</option>
                            <option value="Retenção" <?php echo ($_POST['departamento'] ?? '') == 'Retenção' ? 'selected' : ''; ?>>Retenção</option>
                            <option value="SAC" <?php echo ($_POST['departamento'] ?? '') == 'SAC' ? 'selected' : ''; ?>>SAC</option>
                            <option value="SAF" <?php echo ($_POST['departamento'] ?? '') == 'SAF' ? 'selected' : ''; ?>>SAF</option>
                            <option value="Telemedicina" <?php echo ($_POST['departamento'] ?? '') == 'Telemedicina' ? 'selected' : ''; ?>>Telemedicina</option>
                            <option value="Treinamento" <?php echo ($_POST['departamento'] ?? '') == 'Treinamento' ? 'selected' : ''; ?>>Treinamento</option>
                            <option value="TI - Governança" <?php echo ($_POST['departamento'] ?? '') == 'TI - Governança' ? 'selected' : ''; ?>>TI - Governança</option>
                            <option value="TI - Infraestrutura" <?php echo ($_POST['departamento'] ?? '') == 'TI - Infraestrutura' ? 'selected' : ''; ?>>TI - Infraestrutura</option>
                            <option value="TI - Integração" <?php echo ($_POST['departamento'] ?? '') == 'TI - Integração' ? 'selected' : ''; ?>>TI - Integração</option>
                            <option value="TI - Suporte" <?php echo ($_POST['departamento'] ?? '') == 'TI - Suporte' ? 'selected' : ''; ?>>TI - Suporte</option>
                            <option value="TI - Técnico" <?php echo ($_POST['departamento'] ?? '') == 'TI - Técnico' ? 'selected' : ''; ?>>TI - Técnico</option>
                            <option value="TI - Tecnologia" <?php echo ($_POST['departamento'] ?? '') == 'TI - Tecnologia' ? 'selected' : ''; ?>>TI - Tecnologia</option>
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
                               value="<?php echo htmlspecialchars($_POST['centro_custo'] ?? ''); ?>" 
                               required 
                               class="form-control cc-mask" 
                               placeholder="Ex: TI001, ADM002">
                        <small class="form-text">Código do centro de custo (ex: TI001, ADM002)</small>
                    </div>

                    <div class="form-group">
                        <label for="gestor_id">
                            <i class="fas fa-user-tie"></i>
                            <span>Gestor</span>
                        </label>
                        <select id="gestor_id" name="gestor_id" class="form-control">
                            <option value="">Selecione um gestor</option>
                            <?php
                            foreach ($colaboradores as $colaborador):
                                if ($colaborador['id'] != ($_POST['id'] ?? '')):
                            ?>
                                <option value="<?php echo $colaborador['id']; ?>"
                                        <?php echo (isset($_POST['gestor_id']) && $_POST['gestor_id'] == $colaborador['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($colaborador['nome'] . ' - ' . $colaborador['cargo']); ?>
                                </option>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </select>
                        <small class="form-text">Gestor responsável pelo colaborador (opcional)</small>
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
        // Função para mostrar/ocultar campos de endereço
        function toggleEnderecoFields() {
            const tipoTrabalho = document.getElementById('tipo_trabalho').value;
            const enderecoSection = document.getElementById('endereco-section');
            const camposEndereco = enderecoSection.querySelectorAll('input, select');
            
            if (tipoTrabalho === 'home') {
                enderecoSection.style.display = 'block';
                // Marcar campos como required
                document.getElementById('endereco').required = true;
                document.getElementById('numero').required = true;
                document.getElementById('bairro').required = true;
                document.getElementById('cidade').required = true;
                document.getElementById('estado').required = true;
            } else {
                enderecoSection.style.display = 'none';
                // Remover required
                document.getElementById('endereco').required = false;
                document.getElementById('numero').required = false;
                document.getElementById('bairro').required = false;
                document.getElementById('cidade').required = false;
                document.getElementById('estado').required = false;
                document.getElementById('cep').required = false;
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
        
        // Validação do formulário
        const form = document.getElementById('form-colaborador');
        if (form) {
            form.addEventListener('submit', function(e) {
                let valid = true;
                const cpfInput = document.getElementById('cpf');
                const emailInput = document.getElementById('email');
                const tipoTrabalho = document.getElementById('tipo_trabalho').value;
                const cpfValue = cpfInput.value.replace(/\D/g, '');
                
                // Validar CPF
                if (cpfValue.length !== 11) {
                    alert('CPF deve conter 11 dígitos.');
                    cpfInput.focus();
                    valid = false;
                }
                
                // Validar e-mail se informado
                const emailValue = emailInput.value.trim();
                if (emailValue && !emailValue.includes('@')) {
                    alert('E-mail inválido. Use o formato: nome@empresa.com');
                    emailInput.focus();
                    valid = false;
                }
                
                // Validar campos de endereço se for Home Office
                if (tipoTrabalho === 'home') {
                    const endereco = document.getElementById('endereco').value.trim();
                    const numero = document.getElementById('numero').value.trim();
                    const bairro = document.getElementById('bairro').value.trim();
                    const cidade = document.getElementById('cidade').value.trim();
                    const estado = document.getElementById('estado').value;
                    
                    if (!endereco) {
                        alert('O endereço é obrigatório para Home Office.');
                        document.getElementById('endereco').focus();
                        valid = false;
                    } else if (!numero) {
                        alert('O número é obrigatório para Home Office.');
                        document.getElementById('numero').focus();
                        valid = false;
                    } else if (!bairro) {
                        alert('O bairro é obrigatório para Home Office.');
                        document.getElementById('bairro').focus();
                        valid = false;
                    } else if (!cidade) {
                        alert('A cidade é obrigatória para Home Office.');
                        document.getElementById('cidade').focus();
                        valid = false;
                    } else if (!estado) {
                        alert('O estado é obrigatório para Home Office.');
                        document.getElementById('estado').focus();
                        valid = false;
                    }
                }
                
                if (!valid) {
                    e.preventDefault();
                }
            });
        }
    </script>
</body>
</html>