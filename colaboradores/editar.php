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
    <link rel="stylesheet" href="../css/colaboradores.css">
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
                <input type="text" id="nome" name="nome"
                       value="<?php echo htmlspecialchars($colaboradorAtual['nome']); ?>" required class="form-control"
                       placeholder="Digite o nome completo">
            </div>

            <div class="form-group">
                <label for="cargo"><i class="fas fa-briefcase"></i> Cargo *</label>
                <input type="text" id="cargo" name="cargo"
                       value="<?php echo htmlspecialchars($colaboradorAtual['cargo']); ?>" required class="form-control"
                       placeholder="Digite o cargo">
            </div>

            <div class="form-group">
                <label for="cpf"><i class="fas fa-id-card"></i> CPF *</label>
                <input type="text" id="cpf" name="cpf" value="<?php echo formatarCPF($colaboradorAtual['cpf']); ?>"
                       required class="form-control" placeholder="000.000.000-00" data-mask="cpf">
                <small class="form-text">Digite apenas números ou com pontos e traço</small>
            </div>

            <?php include ('../includes/departamento.php'); ?>

            <div class="form-group">
                <label for="centro_custo"><i class="fas fa-dollar-sign"></i> Centro de Custo *</label>
                <input type="text" id="centro_custo" name="centro_custo"
                       value="<?php echo htmlspecialchars($colaboradorAtual['centro_custo']); ?>" required
                       class="form-control" placeholder="Ex: TI001, ADM002" data-mask="cc">
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
