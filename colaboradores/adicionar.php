<?php
session_start();
require_once '../includes/funcoes.php';
verificarSessao();

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
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <div class="page-header">
            <h1><i class="fas fa-user-plus"></i> Adicionar Novo Colaborador</h1>
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
            <form method="POST" action="" class="form-card">
                <div class="form-group">
                    <label for="nome"><i class="fas fa-user"></i> Nome Completo *</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>" required class="form-control" placeholder="Digite o nome completo">
                </div>

                <div class="form-group">
                    <label for="cargo"><i class="fas fa-briefcase"></i> Cargo *</label>
                    <input type="text" id="cargo" name="cargo" value="<?php echo htmlspecialchars($_POST['cargo'] ?? ''); ?>" required class="form-control" placeholder="Digite o cargo">
                </div>

                <div class="form-group">
                    <label for="cpf"><i class="fas fa-id-card"></i> CPF *</label>
                    <input type="text" id="cpf" name="cpf" value="<?php echo htmlspecialchars($_POST['cpf'] ?? ''); ?>" required class="form-control" placeholder="000.000.000-00" data-mask="cpf">
                    <small class="form-text">Digite apenas números ou com pontos e traço</small>
                </div>


                <?php include ('../includes/departamento.php'); ?>

                <div class="form-group">
                    <label for="centro_custo"><i class="fas fa-dollar-sign"></i> Centro de Custo *</label>
                    <input type="text" id="centro_custo" name="centro_custo" value="<?php echo htmlspecialchars($_POST['centro_custo'] ?? ''); ?>" required class="form-control" placeholder="Ex: TI001, ADM002" data-mask="cc">
                    <small class="form-text">Código do centro de custo (ex: TI001)</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Colaborador
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Limpar
                    </button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>

            <!--
            <div class="info-card">
                <h4><i class="fas fa-info-circle"></i> Informações Importantes</h4>
                <ul>
                    <li>Todos os campos marcados com * são obrigatórios.</li>
                    <li>O CPF será validado automaticamente.</li>
                    <li>O sistema não permite CPFs duplicados.</li>
                    <li>O colaborador ficará disponível para receber equipamentos imediatamente após o cadastro.</li>
                </ul>
            </div>
        </div>
-->

            <!--
        <div class="departamentos-reference">
            <h3><i class="fas fa-list"></i> Departamentos Disponíveis</h3>
            <div class="tags">
                <span class="tag">Tecnologia</span>
                <span class="tag">Administrativo</span>
                <span class="tag">Financeiro</span>
                <span class="tag">Recursos Humanos</span>
                <span class="tag">Comercial</span>
                <span class="tag">Marketing</span>
                <span class="tag">Operações</span>
                <span class="tag">Suporte</span>
            </div>
        </div>
-->
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/script.js"></script>
    <script>
        // Validação específica para esta página
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');

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

            // Auto-formatar CPF
            const cpfInput = document.getElementById('cpf');
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

            // Auto-formatar centro de custo
            const ccInput = document.getElementById('centro_custo');
            ccInput.addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase();
                value = value.replace(/[^A-Z0-9]/g, '');
                e.target.value = value;
            });
        });

    </script>

    <style>
        .form-text {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            color: #666;
        }

        .departamentos-reference {
            margin-top: 30px;
            background: white;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .departamentos-reference h3 {
            color: var(--dark-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .tag {
            background: var(--light-color);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            color: var(--dark-color);
        }

    </style>
</body>

</html>
