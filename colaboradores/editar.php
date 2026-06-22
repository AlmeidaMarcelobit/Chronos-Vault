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

// Carregar colaboradores do novo caminho (ativos.json)
$colaboradores = lerArquivoJSON('../data/colaboradores/ativos.json');

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
    $_SESSION['mensagem'] = 'Colaborador não encontrado.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

$mensagem = '';
$tipoMensagem = '';

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

    // Validações
    $erros = [];

    if (empty($matricula)) {
        $erros[] = 'A matrícula é obrigatória.';
    }

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

    // Verificar se matrícula já existe (exceto para o próprio colaborador)
    if (!empty($matricula)) {
        foreach ($colaboradores as $index => $colaborador) {
            if ($index != $colaboradorIndex && isset($colaborador['matricula']) && $colaborador['matricula'] === $matricula) {
                $erros[] = 'Esta matrícula já está cadastrada no sistema para outro colaborador.';
                break;
            }
        }
    }

    // Verificar se CPF já existe (exceto para o próprio colaborador)
    foreach ($colaboradores as $index => $colaborador) {
        if ($index != $colaboradorIndex && $colaborador['cpf'] === $cpf) {
            $erros[] = 'Este CPF já está cadastrado no sistema para outro colaborador.';
            break;
        }
    }

    // Verificar se e-mail já existe (exceto para o próprio colaborador)
    if (!empty($email)) {
        foreach ($colaboradores as $index => $colaborador) {
            if ($index != $colaboradorIndex && isset($colaborador['email']) && $colaborador['email'] === $email) {
                $erros[] = 'Este e-mail já está cadastrado no sistema para outro colaborador.';
                break;
            }
        }
    }

    if (empty($erros)) {
        // Atualizar colaborador
        $colaboradores[$colaboradorIndex]['matricula'] = $matricula ?: null;
        $colaboradores[$colaboradorIndex]['nome'] = $nome;
        $colaboradores[$colaboradorIndex]['cargo'] = $cargo;
        $colaboradores[$colaboradorIndex]['cpf'] = $cpf;
        $colaboradores[$colaboradorIndex]['departamento'] = $departamento;
        $colaboradores[$colaboradorIndex]['centro_custo'] = $centro_custo;
        $colaboradores[$colaboradorIndex]['email'] = $email ?: null;
        $colaboradores[$colaboradorIndex]['tipo_trabalho'] = $tipo_trabalho;

        // Atualizar endereço
        if ($tipo_trabalho === 'home') {
            $colaboradores[$colaboradorIndex]['endereco'] = [
                'logradouro' => $endereco,
                'numero' => $numero,
                'complemento' => $complemento ?: null,
                'bairro' => $bairro,
                'cidade' => $cidade,
                'estado' => $estado,
                'cep' => $cep ?: null
            ];
        } else {
            $colaboradores[$colaboradorIndex]['endereco'] = null;
        }

        $colaboradores[$colaboradorIndex]['data_atualizacao'] = date('Y-m-d H:i:s');

        // Salvar no JSON (caminho correto: ativos.json)
        if (salvarArquivoJSON('../data/colaboradores/ativos.json', $colaboradores)) {
            $_SESSION['mensagem'] = 'Colaborador atualizado com sucesso!';
            $_SESSION['mensagem_tipo'] = 'success';
            header('Location: index.php');
            exit;
        } else {
            $mensagem = 'Erro ao atualizar o colaborador. Tente novamente.';
            $tipoMensagem = 'error';
        }
    } else {
        $mensagem = implode('<br>', $erros);
        $tipoMensagem = 'error';
    }
}

// Buscar dados do endereço se existir
$enderecoAtual = $colaboradorAtual['endereco'] ?? null;
$tipoTrabalhoAtual = $colaboradorAtual['tipo_trabalho'] ?? 'local';

// Função para formatar CEP com segurança
function formatarCEPSeguro($cep) {
    if (empty($cep)) return '';
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
    <title>Editar Colaborador - Gestão de Colaboradores</title>
    <link rel="stylesheet" href="../css/colaboradores/editar.css">
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
                <i class="fas fa-users"></i>
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
            <?php if (($_SESSION['usuario_nivel'] ?? '') === 'admin'): ?>
                <li class="nav-item"><a href="../Termos/index.php" class="nav-link"><i class="fas fa-file-contract"></i><span>Termos</span></a></li>
                <li class="nav-item"><a href="../usuarios/index.php" class="nav-link"><i class="fas fa-user-cog"></i><span>Usuários</span></a></li>
            <?php endif; ?>
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
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="form-card-container">
        <!-- INFORMAÇÕES DO COLABORADOR -->
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

        <!-- FORMULÁRIO -->
        <form method="POST" action="" id="form-colaborador">
            <div class="form-grid">
                <div class="form-group">
                    <label for="matricula"><i class="fas fa-id-badge"></i> Chamado / Matrícula</label>
                    <input type="text" id="matricula" name="matricula"
                           value="<?php echo htmlspecialchars($colaboradorAtual['matricula'] ?? ''); ?>"
                           class="form-control" placeholder="Ex: #251506, #255676">
                    <small class="form-text">Número do chamado do colaborador</small>
                </div>

                <div class="form-group">
                    <label for="nome"><i class="fas fa-user"></i> Nome Completo <span class="required">*</span></label>
                    <input type="text" id="nome" name="nome"
                           value="<?php echo htmlspecialchars($colaboradorAtual['nome']); ?>" required
                           class="form-control" placeholder="Digite o nome completo">
                </div>

                <div class="form-group">
                    <label for="cargo"><i class="fas fa-briefcase"></i> Cargo <span class="required">*</span></label>
                    <input type="text" id="cargo" name="cargo"
                           value="<?php echo htmlspecialchars($colaboradorAtual['cargo']); ?>" required
                           class="form-control" placeholder="Digite o cargo">
                </div>

                <div class="form-group">
                    <label for="cpf"><i class="fas fa-id-card"></i> CPF <span class="required">*</span></label>
                    <input type="text" id="cpf" name="cpf" value="<?php echo formatarCPF($colaboradorAtual['cpf']); ?>"
                           required class="form-control cpf-mask" placeholder="000.000.000-00" maxlength="14">
                    <small class="form-text">Digite apenas números ou com pontos e traço</small>
                </div>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> E-mail</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo htmlspecialchars($colaboradorAtual['email'] ?? ''); ?>"
                           class="form-control" placeholder="colaborador@empresa.com.br">
                    <small class="form-text">E-mail institucional do colaborador (opcional)</small>
                </div>

                <div class="form-group">
                    <label for="tipo_trabalho"><i class="fas fa-briefcase"></i> Tipo de Trabalho <span class="required">*</span></label>
                    <select id="tipo_trabalho" name="tipo_trabalho" required class="form-select" onchange="toggleEndereco()">
                        <option value="local" <?php echo $tipoTrabalhoAtual == 'local' ? 'selected' : ''; ?>>Presencial (Local)</option>
                        <option value="home" <?php echo $tipoTrabalhoAtual == 'home' ? 'selected' : ''; ?>>Home Office</option>
                    </select>
                </div>
            </div>

            <!-- SEÇÃO DE ENDEREÇO -->
            <div id="endereco-section" style="display: <?php echo $tipoTrabalhoAtual == 'home' ? 'block' : 'none'; ?>;">
                <div class="section-divider">
                    <h3><i class="fas fa-home"></i> Endereço Residencial</h3>
                </div>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="endereco"><i class="fas fa-road"></i> Logradouro <span class="required">*</span></label>
                        <input type="text" id="endereco" name="endereco"
                               value="<?php echo htmlspecialchars($enderecoAtual['logradouro'] ?? ''); ?>"
                               class="form-control" placeholder="Rua, Avenida, Alameda...">
                    </div>
                    <div class="form-group">
                        <label for="numero"><i class="fas fa-hashtag"></i> Número <span class="required">*</span></label>
                        <input type="text" id="numero" name="numero"
                               value="<?php echo htmlspecialchars($enderecoAtual['numero'] ?? ''); ?>"
                               class="form-control" placeholder="Número">
                    </div>
                    <div class="form-group">
                        <label for="complemento"><i class="fas fa-plus-circle"></i> Complemento</label>
                        <input type="text" id="complemento" name="complemento"
                               value="<?php echo htmlspecialchars($enderecoAtual['complemento'] ?? ''); ?>"
                               class="form-control" placeholder="Apto, Bloco, Casa...">
                    </div>
                    <div class="form-group">
                        <label for="bairro"><i class="fas fa-location-dot"></i> Bairro <span class="required">*</span></label>
                        <input type="text" id="bairro" name="bairro"
                               value="<?php echo htmlspecialchars($enderecoAtual['bairro'] ?? ''); ?>"
                               class="form-control" placeholder="Bairro">
                    </div>
                    <div class="form-group">
                        <label for="cidade"><i class="fas fa-city"></i> Cidade <span class="required">*</span></label>
                        <input type="text" id="cidade" name="cidade"
                               value="<?php echo htmlspecialchars($enderecoAtual['cidade'] ?? ''); ?>"
                               class="form-control" placeholder="Cidade">
                    </div>
                    <div class="form-group">
                        <label for="estado"><i class="fas fa-map-marker-alt"></i> Estado <span class="required">*</span></label>
                        <select id="estado" name="estado" class="form-select">
                            <option value="">Selecione o estado</option>
                            <?php foreach (getEstados() as $sigla => $nome): ?>
                                <option value="<?php echo $sigla; ?>" <?php echo (($enderecoAtual['estado'] ?? '') == $sigla) ? 'selected' : ''; ?>>
                                    <?php echo $sigla . ' - ' . $nome; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cep"><i class="fas fa-mail-bulk"></i> CEP</label>
                        <input type="text" id="cep" name="cep"
                               value="<?php echo formatarCEPSeguro($enderecoAtual['cep'] ?? ''); ?>"
                               class="form-control cep-mask" placeholder="00000-000" maxlength="9">
                        <small class="form-text">Opcional - Formato: 00000-000</small>
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
                    <label for="centro_custo"><i class="fas fa-dollar-sign"></i> Centro de Custo <span class="required">*</span></label>
                    <input type="text" id="centro_custo" name="centro_custo"
                           value="<?php echo htmlspecialchars($colaboradorAtual['centro_custo']); ?>" required
                           class="form-control cc-mask" placeholder="Ex: 12001, 12002">
                    <small class="form-text">Código do centro de custo</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Atualizar Colaborador</button>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            </div>
        </form>
    </div>
</main>

<!-- ==================== FOOTER ==================== -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3><i class="fas fa-users"></i>Gestão de Colaboradores</h3>
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

    function toggleEndereco() {
        if (selectTipoTrabalho.value === 'home') {
            enderecoSection.style.display = 'block';
            document.getElementById('endereco').required = true;
            document.getElementById('numero').required = true;
            document.getElementById('bairro').required = true;
            document.getElementById('cidade').required = true;
            document.getElementById('estado').required = true;
        } else {
            enderecoSection.style.display = 'none';
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

    // Fechar alerta após 5 segundos
    setTimeout(function() {
        const alert = document.querySelector('.global-alert');
        if (alert) {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }
    }, 5000);
</script>
</body>
</html>