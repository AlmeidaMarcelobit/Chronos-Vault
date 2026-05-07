<?php
session_start();
require_once '../includes/funcoes.php';
verificarSessao();

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
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <div class="page-header">
            <h1><i class="fas fa-edit"></i> Editar Colaborador</h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>

        <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : 'error'; ?>">
            <i class="fas fa-<?php echo $tipoMensagem === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $mensagem; ?>
        </div>
        <?php endif; ?>

        <div class="form-container">
            <div class="colaborador-info">
                <h3>Informações do Colaborador</h3>
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

            <form method="POST" action="" class="form-card">
                <div class="form-group">
                    <label for="nome"><i class="fas fa-user"></i> Nome Completo *</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($colaboradorAtual['nome']); ?>" required class="form-control" placeholder="Digite o nome completo">
                </div>

                <div class="form-group">
                    <label for="cargo"><i class="fas fa-briefcase"></i> Cargo *</label>
                    <input type="text" id="cargo" name="cargo" value="<?php echo htmlspecialchars($colaboradorAtual['cargo']); ?>" required class="form-control" placeholder="Digite o cargo">
                </div>

                <div class="form-group">
                    <label for="cpf"><i class="fas fa-id-card"></i> CPF *</label>
                    <input type="text" id="cpf" name="cpf" value="<?php echo formatarCPF($colaboradorAtual['cpf']); ?>" required class="form-control" placeholder="000.000.000-00" data-mask="cpf">
                    <small class="form-text">Digite apenas números ou com pontos e traço</small>
                </div>

                <div class="form-group">
                    <label for="departamento"><i class="fas fa-building"></i> Departamento *</label>
                    <select id="departamento" name="departamento" required class="form-select">
                        <option value="">-- Selecione um departamento --</option>
                        <option value="Dental Vidas Administrativo" <?php echo ($_POST['departamento'] ?? '') == 'Dental Vidas Administrativo' ? 'selected' : ''; ?>>Dental Vidas Administrativo</option>
                        <option value="Relacionamento com Profissionais da Saúde" <?php echo ($_POST['departamento'] ?? '') == 'Relacionamento com Profissionais da Saúde' ? 'selected' : ''; ?>>Relacionamento com Profissionais da Saúde</option>
                        <option value="AmorLab" <?php echo ($_POST['departamento'] ?? '') == 'AmorLab' ? 'selected' : ''; ?>>AmorLab</option>
                        <option value="Financeiro" <?php echo ($_POST['departamento'] ?? '') == 'Financeiro' ? 'selected' : ''; ?>>Financeiro</option>
                        <option value="Cadastro" <?php echo ($_POST['departamento'] ?? '') == 'Cadastro' ? 'selected' : ''; ?>>Cadastro</option>
                        <option value="Infraestrutura" <?php echo ($_POST['departamento'] ?? '') == 'Infraestrutura' ? 'selected' : ''; ?>>Infraestrutura</option>
                        <option value="Retenção" <?php echo ($_POST['departamento'] ?? '') == 'Retenção' ? 'selected' : ''; ?>>Retenção</option>
                        <option value="Diretoria CEO" <?php echo ($_POST['departamento'] ?? '') == 'Diretoria CEO' ? 'selected' : ''; ?>>Diretoria CEO</option>
                        <option value="Atendimento" <?php echo ($_POST['departamento'] ?? '') == 'Atendimento' ? 'selected' : ''; ?>>Atendimento</option>
                        <option value="SAC" <?php echo ($_POST['departamento'] ?? '') == 'SAC' ? 'selected' : ''; ?>>SAC</option>
                        <option value="SAF" <?php echo ($_POST['departamento'] ?? '') == 'SAF' ? 'selected' : ''; ?>>SAF</option>
                        <option value="Integração" <?php echo ($_POST['departamento'] ?? '') == 'Integração' ? 'selected' : ''; ?>>Integração</option>
                        <option value="BackOffice" <?php echo ($_POST['departamento'] ?? '') == 'BackOffice' ? 'selected' : ''; ?>>BackOffice</option>
                        <option value="Gestão de Rede" <?php echo ($_POST['departamento'] ?? '') == 'Gestão de Rede' ? 'selected' : ''; ?>>Gestão de Rede</option>
                        <option value="Técnico" <?php echo ($_POST['departamento'] ?? '') == 'Técnico' ? 'selected' : ''; ?>>Técnico</option>
                        <option value="Remuneração E Beneficios" <?php echo ($_POST['departamento'] ?? '') == 'Remuneração E Beneficios' ? 'selected' : ''; ?>>Remuneração E Beneficios</option>
                        <option value="Consultoria e Performance" <?php echo ($_POST['departamento'] ?? '') == 'Consultoria e Performance' ? 'selected' : ''; ?>>Consultoria e Performance</option>
                        <option value="Internacional" <?php echo ($_POST['departamento'] ?? '') == 'Internacional' ? 'selected' : ''; ?>>Internacional</option>
                        <option value="Assessoria Regional" <?php echo ($_POST['departamento'] ?? '') == 'Assessoria Regional' ? 'selected' : ''; ?>>Assessoria Regional</option>
                        <option value="Qualidade de Atendimento" <?php echo ($_POST['departamento'] ?? '') == 'Qualidade de Atendimento' ? 'selected' : ''; ?>>Qualidade de Atendimento</option>
                        <option value="Cirurgias" <?php echo ($_POST['departamento'] ?? '') == 'Cirurgias' ? 'selected' : ''; ?>>Cirurgias</option>
                        <option value="Telemedicina" <?php echo ($_POST['departamento'] ?? '') == 'Telemedicina' ? 'selected' : ''; ?>>Telemedicina</option>
                        <option value="Suporte" <?php echo ($_POST['departamento'] ?? '') == 'Suporte' ? 'selected' : ''; ?>>Suporte</option>
                        <option value="Treinamento" <?php echo ($_POST['departamento'] ?? '') == 'Treinamento' ? 'selected' : ''; ?>>Treinamento</option>
                        <option value="Inteligência de Negócio" <?php echo ($_POST['departamento'] ?? '') == 'Inteligência de Negócio' ? 'selected' : ''; ?>>Inteligência de Negócio</option>
                        <option value="TI Tecnologia" <?php echo ($_POST['departamento'] ?? '') == 'TI Tecnologia' ? 'selected' : ''; ?>>TI Tecnologia</option>
                        <option value="Atendimento a Franquia" <?php echo ($_POST['departamento'] ?? '') == 'Atendimento a Franquia' ? 'selected' : ''; ?>>Atendimento a Franquia</option>
                        <option value="Diretoria de Pessoas e Cultura" <?php echo ($_POST['departamento'] ?? '') == 'Diretoria de Pessoas e Cultura' ? 'selected' : ''; ?>>Diretoria de Pessoas e Cultura</option>
                        <option value="Governança TI" <?php echo ($_POST['departamento'] ?? '') == 'Governança TI' ? 'selected' : ''; ?>>Governança TI</option>
                        <option value="CRM" <?php echo ($_POST['departamento'] ?? '') == 'CRM' ? 'selected' : ''; ?>>CRM</option>
                        <option value="Diretoria de Marketing" <?php echo ($_POST['departamento'] ?? '') == 'Diretoria de Marketing' ? 'selected' : ''; ?>>Diretoria de Marketing</option>
                        <option value="Marketing Internacional" <?php echo ($_POST['departamento'] ?? '') == 'Marketing Internacional' ? 'selected' : ''; ?>>Marketing Internacional</option>
                        <option value="Pessoas" <?php echo ($_POST['departamento'] ?? '') == 'Pessoas' ? 'selected' : ''; ?>>Pessoas</option>
                        <option value="Cultura" <?php echo ($_POST['departamento'] ?? '') == 'Cultura' ? 'selected' : ''; ?>>Cultura</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="centro_custo"><i class="fas fa-dollar-sign"></i> Centro de Custo *</label>
                    <input type="text" id="centro_custo" name="centro_custo" value="<?php echo htmlspecialchars($colaboradorAtual['centro_custo']); ?>" required class="form-control" placeholder="Ex: TI001, ADM002" data-mask="cc">
                    <small class="form-text">Código do centro de custo (ex: TI001)</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Atualizar Colaborador
                    </button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/script.js"></script>
</body>

</html>
