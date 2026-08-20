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

// Lista fixa de equipamentos disponíveis
$EQUIPAMENTOS_DISPONIVEIS = [
    'Monitor',
    'Fone',
    'Notebook',
    'Celular',
    'Suporte',
    'Teclado',
    'Mouse'
];

// Carregar colaboradores do novo caminho (ativos.json)
$colaboradores = lerArquivoJSON('../data/colaboradores/ativos.json');
if ($colaboradores === false) $colaboradores = [];

// Variáveis para a tela de confirmação de mesclagem
$exibirConfirmacao = false;
$divergencias = [];
$dadosParaConfirmar = [];
$colaboradorExistente = null;
$colaboradorIndex = null;

// Função auxiliar: verifica se um valor é "vazio" para fins de mesclagem
function valorEstaVazio($valor) {
    if ($valor === null) return true;
    if (is_string($valor) && trim($valor) === '') return true;
    if (is_array($valor) && empty($valor)) return true;
    return false;
}

// Função auxiliar: compara dois valores de forma inteligente
function valoresDiferentes($v1, $v2) {
    if (is_array($v1) && is_array($v2)) {
        sort($v1);
        sort($v2);
        return $v1 != $v2;
    }
    return $v1 != $v2;
}

// Função: comparar dois colaboradores e retornar lista de divergências
function detectarDivergencias($existente, $novo, $listaEquipamentos) {
    $divergencias = [];

    $camposSimples = [
        'nome' => 'Nome',
        'cargo' => 'Cargo',
        'cpf' => 'CPF',
        'departamento' => 'Departamento',
        'centro_custo' => 'Centro de Custo',
        'email' => 'E-mail',
        'tipo_trabalho' => 'Tipo de Trabalho'
    ];

    foreach ($camposSimples as $campo => $rotulo) {
        $valorNovo = $novo[$campo] ?? null;
        $valorAtual = $existente[$campo] ?? null;

        if (valorEstaVazio($valorNovo)) {
            continue;
        }

        if (valoresDiferentes($valorAtual, $valorNovo)) {
            $vAtual = is_array($valorAtual) ? implode(', ', $valorAtual) : (string)$valorAtual;
            $vNovo = is_array($valorNovo) ? implode(', ', $valorNovo) : (string)$valorNovo;
            $divergencias[] = [
                'campo' => $campo,
                'rotulo' => $rotulo,
                'valor_atual' => $vAtual ?: '(não informado)',
                'valor_novo' => $vNovo ?: '(não informado)'
            ];
        }
    }

    // Comparar campo equipamentos
    if (!valorEstaVazio($novo['equipamentos'] ?? null)) {
        $eqAtual = $existente['equipamentos'] ?? [];
        $eqNovo = $novo['equipamentos'] ?? [];
        if (valoresDiferentes($eqAtual, $eqNovo)) {
            $vAtual = is_array($eqAtual) && !empty($eqAtual) ? implode(', ', $eqAtual) : '(nenhum)';
            $vNovo = is_array($eqNovo) && !empty($eqNovo) ? implode(', ', $eqNovo) : '(nenhum)';
            $divergencias[] = [
                'campo' => 'equipamentos',
                'rotulo' => 'Equipamentos',
                'valor_atual' => $vAtual,
                'valor_novo' => $vNovo
            ];
        }
    }

    // Comparar endereço (se o novo for home office e tiver dados)
    if (($novo['tipo_trabalho'] ?? 'local') === 'home' && !empty($novo['endereco'])) {
        $camposEndereco = [
            'logradouro' => 'Logradouro',
            'numero' => 'Número',
            'complemento' => 'Complemento',
            'bairro' => 'Bairro',
            'cidade' => 'Cidade',
            'estado' => 'Estado',
            'cep' => 'CEP'
        ];
        $endAtual = $existente['endereco'] ?? [];
        if (!is_array($endAtual)) $endAtual = [];
        $endNovo = $novo['endereco'] ?? [];

        foreach ($camposEndereco as $campo => $rotulo) {
            $valorNovo = $endNovo[$campo] ?? null;
            $valorAtual = $endAtual[$campo] ?? null;

            if (valorEstaVazio($valorNovo)) continue;
            if (valoresDiferentes($valorAtual, $valorNovo)) {
                $divergencias[] = [
                    'campo' => "endereco[$campo]",
                    'rotulo' => "Endereço - $rotulo",
                    'valor_atual' => $valorAtual ?: '(não informado)',
                    'valor_novo' => $valorNovo ?: '(não informado)'
                ];
            }
        }
    }

    return $divergencias;
}

// Função: aplicar mesclagem (considerando apenas campos não vazios no novo)
function aplicarMesclagem($existente, $novo) {
    $camposSimples = ['nome', 'cargo', 'cpf', 'departamento', 'centro_custo', 'email', 'tipo_trabalho'];
    foreach ($camposSimples as $campo) {
        $valorNovo = $novo[$campo] ?? null;
        if (!valorEstaVazio($valorNovo)) {
            $existente[$campo] = $valorNovo;
        }
    }

    // Mesclar equipamentos (substitui integralmente se informado)
    if (!valorEstaVazio($novo['equipamentos'] ?? null)) {
        $existente['equipamentos'] = $novo['equipamentos'];
    } elseif (!isset($existente['equipamentos'])) {
        $existente['equipamentos'] = [];
    }

    // Mesclar endereço
    if (($novo['tipo_trabalho'] ?? '') === 'home' && !empty($novo['endereco'])) {
        if (!isset($existente['endereco']) || !is_array($existente['endereco'])) {
            $existente['endereco'] = [];
        }
        $camposEndereco = ['logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'estado', 'cep'];
        foreach ($camposEndereco as $campo) {
            $valorNovo = $novo['endereco'][$campo] ?? null;
            if (!valorEstaVazio($valorNovo)) {
                $existente['endereco'][$campo] = $valorNovo;
            }
        }
    }

    $existente['data_atualizacao'] = date('Y-m-d H:i:s');
    return $existente;
}

// ========== PROCESSAMENTO DO FORMULÁRIO ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? 'salvar';

    // ========================================================
    // CASO 1: Usuário confirmou a mesclagem
    // ========================================================
    if ($acao === 'confirmar_mesclagem') {
        $matricula = trim($_POST['matricula_confirm'] ?? '');
        $colaboradorIndex = null;

        // Buscar colaborador novamente
        foreach ($colaboradores as $index => $colab) {
            if (isset($colab['matricula']) && $colab['matricula'] === $matricula) {
                $colaboradorIndex = $index;
                $colaboradorExistente = $colab;
                break;
            }
        }

        if ($colaboradorExistente !== null) {
            // Recuperar dados do formulário original (via hidden fields)
            $novoDados = reconstruirDadosDoPost($_POST);
            $colaboradores[$colaboradorIndex] = aplicarMesclagem($colaboradorExistente, $novoDados);

            if (salvarArquivoJSON('../data/colaboradores/ativos.json', $colaboradores)) {
                $mensagem = 'Colaborador atualizado com sucesso! (Mesclagem confirmada)';
                $tipoMensagem = 'success';
                $_POST = [];
            } else {
                $mensagem = 'Erro ao atualizar o colaborador. Tente novamente.';
                $tipoMensagem = 'error';
            }
        } else {
            $mensagem = 'Colaborador não encontrado para mesclagem.';
            $tipoMensagem = 'error';
        }

    // ========================================================
    // CASO 2: Usuário cancelou a mesclagem
    // ========================================================
    } elseif ($acao === 'cancelar_mesclagem') {
        $mensagem = 'Mesclagem cancelada. Nenhuma alteração foi realizada.';
        $tipoMensagem = 'info';
        $_POST = [];

    // ========================================================
    // CASO 3: Submissão normal do formulário (acao === 'salvar' ou vazio)
    // ========================================================
    } else {
        $matricula = trim($_POST['matricula'] ?? '');
        $nome = trim($_POST['nome'] ?? '');
        $cargo = trim($_POST['cargo'] ?? '');
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
        $departamento = trim($_POST['departamento'] ?? '');
        $centro_custo = trim($_POST['centro_custo'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $tipo_trabalho = $_POST['tipo_trabalho'] ?? 'local';
        $equipamentos = $_POST['equipamentos'] ?? [];
        if (!is_array($equipamentos)) $equipamentos = [];

        $endereco = trim($_POST['endereco'] ?? '');
        $numero = trim($_POST['numero'] ?? '');
        $complemento = trim($_POST['complemento'] ?? '');
        $bairro = trim($_POST['bairro'] ?? '');
        $cidade = trim($_POST['cidade'] ?? '');
        $estado = trim($_POST['estado'] ?? '');
        $cep = preg_replace('/[^0-9]/', '', $_POST['cep'] ?? '');

        // NENHUMA VALIDAÇÃO OBRIGATÓRIA - Todos os campos são opcionais
        $erros = [];

        // Apenas validações de formato (se preenchidos)
        if (!empty($cpf) && !validarCPF($cpf)) {
            $erros[] = 'CPF inválido.';
        }

        if (!empty($cpf)) {
            foreach ($colaboradores as $colab) {
                $matriculaColab = $colab['matricula'] ?? '';
                if (!empty($colab['cpf']) && preg_replace('/[^0-9]/', '', $colab['cpf']) === $cpf && $matriculaColab !== $matricula) {
                    $erros[] = 'CPF já cadastrado para o colaborador "' . htmlspecialchars($colab['nome'] ?? 'sem nome') . '".';
                    break;
                }
            }
        }

        if (!empty($email) && !validarEmail($email)) {
            $erros[] = 'E-mail inválido.';
        }

        if (!empty($cep) && !validarCEP($cep)) {
            $erros[] = 'CEP inválido.';
        }

        // Validar equipamentos (garantir que só tenha valores da lista fixa)
        if (!empty($equipamentos)) {
            $equipamentos = array_values(array_intersect($equipamentos, $EQUIPAMENTOS_DISPONIVEIS));
        }

        if (empty($erros)) {
            // Preparar dados novos
            $novoDados = [
                'matricula' => $matricula,
                'nome' => $nome,
                'cargo' => $cargo,
                'cpf' => $cpf,
                'departamento' => $departamento,
                'centro_custo' => $centro_custo,
                'email' => $email,
                'tipo_trabalho' => $tipo_trabalho,
                'equipamentos' => $equipamentos,
                'endereco' => $tipo_trabalho === 'home' ? [
                    'logradouro' => $endereco,
                    'numero' => $numero,
                    'complemento' => $complemento,
                    'bairro' => $bairro,
                    'cidade' => $cidade,
                    'estado' => $estado,
                    'cep' => $cep
                ] : null
            ];

            // ========================================================
            // REGRA: Matrícula em branco → sempre cria novo
            // ========================================================
            if (empty($matricula)) {
                $novoColaborador = montarColaboradorNovo($colaboradores, $novoDados);
                $colaboradores[] = $novoColaborador;

                if (salvarArquivoJSON('../data/colaboradores/ativos.json', $colaboradores)) {
                    $mensagem = 'Colaborador cadastrado com sucesso! (Matrícula em branco - novo cadastro)';
                    $tipoMensagem = 'success';
                    $_POST = [];
                } else {
                    $mensagem = 'Erro ao salvar o colaborador. Tente novamente.';
                    $tipoMensagem = 'error';
                }

            // ========================================================
            // REGRA: Matrícula preenchida → verificar duplicidade
            // ========================================================
            } else {
                $colaboradorExistente = null;
                $colaboradorIndex = null;
                foreach ($colaboradores as $index => $colab) {
                    if (isset($colab['matricula']) && $colab['matricula'] === $matricula) {
                        $colaboradorExistente = $colab;
                        $colaboradorIndex = $index;
                        break;
                    }
                }

                // Matrícula inexistente → cria novo
                if (!$colaboradorExistente) {
                    $novoColaborador = montarColaboradorNovo($colaboradores, $novoDados);
                    $colaboradores[] = $novoColaborador;

                    if (salvarArquivoJSON('../data/colaboradores/ativos.json', $colaboradores)) {
                        $mensagem = 'Colaborador cadastrado com sucesso!';
                        $tipoMensagem = 'success';
                        $_POST = [];
                    } else {
                        $mensagem = 'Erro ao salvar o colaborador. Tente novamente.';
                        $tipoMensagem = 'error';
                    }

                // Matrícula existente → modo de mesclagem
                } else {
                    $divergencias = detectarDivergencias($colaboradorExistente, $novoDados, $EQUIPAMENTOS_DISPONIVEIS);

                    // REGRA: Sem divergências → salva direto sem tela
                    if (empty($divergencias)) {
                        $mensagem = 'Nenhuma alteração detectada. O colaborador já possui esses dados.';
                        $tipoMensagem = 'info';
                        $_POST = [];

                    // REGRA: Com divergências → exibir tela de confirmação
                    } else {
                        $exibirConfirmacao = true;
                        $dadosParaConfirmar = $novoDados;
                    }
                }
            }
        } else {
            $mensagem = implode('<br>', $erros);
            $tipoMensagem = 'error';
        }
    }
}

// Função auxiliar: montar estrutura de novo colaborador
function montarColaboradorNovo($colaboradores, $dados) {
    $end = $dados['endereco'];
    $temEndereco = is_array($end) && !empty(array_filter($end, function($v) {
        return !valorEstaVazio($v);
    }));

    return [
        'id' => gerarId($colaboradores),
        'matricula' => $dados['matricula'] ?: null,
        'nome' => $dados['nome'] ?: null,
        'cargo' => $dados['cargo'] ?: null,
        'cpf' => $dados['cpf'] ?: null,
        'departamento' => $dados['departamento'] ?: null,
        'centro_custo' => $dados['centro_custo'] ?: null,
        'email' => $dados['email'] ?: null,
        'tipo_trabalho' => $dados['tipo_trabalho'],
        'equipamentos' => $dados['equipamentos'] ?? [],
        'endereco' => ($dados['tipo_trabalho'] === 'home' && $temEndereco) ? $end : null,
        'data_cadastro' => date('Y-m-d H:i:s'),
        'data_atualizacao' => date('Y-m-d H:i:s')
    ];
}

// Função: reconstruir dados do form do POST de confirmação
function reconstruirDadosDoPost($post) {
    global $EQUIPAMENTOS_DISPONIVEIS;
    $eq = $post['equipamentos_confirm'] ?? [];
    if (!is_array($eq)) $eq = [];
    $eq = array_values(array_intersect($eq, $EQUIPAMENTOS_DISPONIVEIS));

    $tt = $post['tipo_trabalho_confirm'] ?? 'local';
    return [
        'matricula' => trim($post['matricula_confirm'] ?? ''),
        'nome' => trim($post['nome_confirm'] ?? ''),
        'cargo' => trim($post['cargo_confirm'] ?? ''),
        'cpf' => preg_replace('/[^0-9]/', '', $post['cpf_confirm'] ?? ''),
        'departamento' => trim($post['departamento_confirm'] ?? ''),
        'centro_custo' => trim($post['centro_custo_confirm'] ?? ''),
        'email' => trim($post['email_confirm'] ?? ''),
        'tipo_trabalho' => $tt,
        'equipamentos' => $eq,
        'endereco' => $tt === 'home' ? [
            'logradouro' => trim($post['end_logradouro_confirm'] ?? ''),
            'numero' => trim($post['end_numero_confirm'] ?? ''),
            'complemento' => trim($post['end_complemento_confirm'] ?? ''),
            'bairro' => trim($post['end_bairro_confirm'] ?? ''),
            'cidade' => trim($post['end_cidade_confirm'] ?? ''),
            'estado' => trim($post['end_estado_confirm'] ?? ''),
            'cep' => preg_replace('/[^0-9]/', '', $post['end_cep_confirm'] ?? '')
        ] : null
    ];
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
$total_equipamentos = count(carregarTodosEquipamentos());
$equipamentos_estoque = count(carregarEquipamentosPorStatus('estoque'));
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
    <style>
        .confirmacao-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6); z-index: 9999;
            display: flex; align-items: center; justify-content: center; padding: 20px;
        }
        .confirmacao-modal {
            background: white; border-radius: 14px; max-width: 720px; width: 100%;
            max-height: 85vh; overflow: hidden; display: flex; flex-direction: column;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .confirmacao-header {
            padding: 24px 28px; background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white; display: flex; align-items: center; gap: 14px;
        }
        .confirmacao-header .icon-wrap {
            width: 48px; height: 48px; background: rgba(255,255,255,0.2);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 22px;
        }
        .confirmacao-header h2 { margin: 0; font-size: 20px; font-weight: 600; }
        .confirmacao-header p { margin: 4px 0 0 0; font-size: 13px; opacity: 0.92; }
        .confirmacao-body { padding: 24px 28px; overflow-y: auto; }
        .confirmacao-info {
            background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px;
            padding: 14px 16px; margin-bottom: 20px; font-size: 14px; color: #92400e;
        }
        .confirmacao-info strong { color: #78350f; }
        .divergencia-lista { list-style: none; padding: 0; margin: 0; }
        .divergencia-item {
            border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px; margin-bottom: 12px;
            background: #fafafa;
        }
        .divergencia-label {
            font-weight: 600; color: #1f2937; font-size: 14px; margin-bottom: 10px;
            display: flex; align-items: center; gap: 8px;
        }
        .divergencia-label::before {
            content: '→'; color: #f59e0b; font-weight: 700;
        }
        .divergencia-valores {
            display: grid; grid-template-columns: 1fr 14px 1fr; gap: 12px; align-items: center;
            background: white; border-radius: 8px; padding: 12px;
            border: 1px solid #e5e7eb;
        }
        .valor-atual {
            padding: 8px 10px; background: #fef2f2; border-left: 3px solid #ef4444;
            border-radius: 6px; font-size: 13px; color: #7f1d1d; word-break: break-word;
        }
        .valor-novo {
            padding: 8px 10px; background: #ecfdf5; border-left: 3px solid #10b981;
            border-radius: 6px; font-size: 13px; color: #065f46; word-break: break-word;
        }
        .valor-set {
            display: flex; align-items: center; justify-content: center; color: #6b7280;
            font-weight: 700; font-size: 16px;
        }
        .confirmacao-footer {
            padding: 18px 28px; background: #f9fafb; border-top: 1px solid #e5e7eb;
            display: flex; gap: 12px; justify-content: flex-end;
        }
        .btn-cancelar-merge {
            padding: 10px 20px; border-radius: 8px; border: 1px solid #d1d5db;
            background: white; color: #374151; cursor: pointer; font-weight: 500;
            transition: all 0.2s;
        }
        .btn-cancelar-merge:hover { background: #f3f4f6; }
        .btn-confirmar-merge {
            padding: 10px 22px; border-radius: 8px; border: none;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white; cursor: pointer; font-weight: 600; transition: all 0.2s;
        }
        .btn-confirmar-merge:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(245,158,11,0.4); }
        .equipamentos-select {
            min-height: 150px; padding: 8px;
        }
        .equipamentos-select option {
            padding: 6px 10px; border-radius: 4px; cursor: pointer;
        }
        .equipamentos-select option:hover { background: #eff6ff; }
        .equipamentos-select option:checked {
            background: #2563eb; color: white;
        }
        .form-group.opt {
            opacity: 1;
        }
        .form-group .optional-tag {
            display: inline-block; font-size: 10px; padding: 2px 8px;
            background: #e0f2fe; color: #075985; border-radius: 20px; margin-left: 6px;
            font-weight: 500;
        }
    </style>
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
            <li class="nav-item"><a href="../solicitacoes_manutencao/index.php" class="nav-link"><i class="fas fa-tools"></i><span>Solicitações Manutenção</span></a></li>
            <li class="nav-item"><a href="../linhas/index.php" class="nav-link"><i class="fas fa-phone"></i><span>Linhas</span></a></li>
            <?php if ($is_admin): ?>
                <li class="nav-item"><a href="../Termos/index.php" class="nav-link"><i class="fas fa-file-contract"></i><span>Termos</span></a></li>
                <li class="nav-item"><a href="../usuarios/index.php" class="nav-link"><i class="fas fa-user-cog"></i><span>Usuários</span></a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<!-- Mensagens de alerta -->
<?php if ($mensagem && !$exibirConfirmacao): ?>
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
                <strong>Nota:</strong> Todos os campos são opcionais. Se a matrícula já existir, os dados serão analisados para mesclagem.
            </p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="form-card-container">
        <form method="POST" action="" class="form-card" id="form-colaborador">
            <div class="form-grid">
                <!-- Campo Matrícula -->
                <div class="form-group opt">
                    <label for="matricula">
                        <i class="fas fa-id-badge"></i>
                        <span>Chamado / Matrícula <span class="optional-tag">opcional</span></span>
                    </label>
                    <input type="text"
                           id="matricula"
                           name="matricula"
                           value="<?php echo htmlspecialchars($_POST['matricula'] ?? ''); ?>"
                           class="form-control"
                           placeholder="Ex: #251506, #255676"
                           autofocus>
                    <small class="form-text">Número do chamado do colaborador. Em branco = sempre novo cadastro.</small>
                </div>

                <div class="form-group opt">
                    <label for="nome">
                        <i class="fas fa-user"></i>
                        <span>Nome Completo <span class="optional-tag">opcional</span></span>
                    </label>
                    <input type="text"
                           id="nome"
                           name="nome"
                           value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>"
                           class="form-control"
                           placeholder="Digite o nome completo">
                </div>

                <div class="form-group opt">
                    <label for="cargo">
                        <i class="fas fa-briefcase"></i>
                        <span>Cargo <span class="optional-tag">opcional</span></span>
                    </label>
                    <input type="text"
                           id="cargo"
                           name="cargo"
                           value="<?php echo htmlspecialchars($_POST['cargo'] ?? ''); ?>"
                           class="form-control"
                           placeholder="Digite o cargo">
                </div>

                <div class="form-group opt">
                    <label for="cpf">
                        <i class="fas fa-id-card"></i>
                        <span>CPF <span class="optional-tag">opcional</span></span>
                    </label>
                    <input type="text"
                           id="cpf"
                           name="cpf"
                           value="<?php echo htmlspecialchars($_POST['cpf'] ?? ''); ?>"
                           class="form-control cpf-mask"
                           placeholder="000.000.000-00"
                           maxlength="14">
                </div>

                <div class="form-group opt">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        <span>E-mail <span class="optional-tag">opcional</span></span>
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           class="form-control"
                           placeholder="colaborador@empresa.com.br">
                </div>

                <div class="form-group opt">
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

            <!-- Campo Equipamentos (seleção múltipla) -->
            <div class="form-grid">
                <div class="form-group full-width opt">
                    <label for="equipamentos">
                        <i class="fas fa-laptop"></i>
                        <span>Equipamentos <span class="optional-tag">opcional</span></span>
                    </label>
                    <select id="equipamentos" name="equipamentos[]" multiple class="form-select equipamentos-select">
                        <?php
                        $selecionados = $_POST['equipamentos'] ?? [];
                        if (!is_array($selecionados)) $selecionados = [];
                        foreach ($EQUIPAMENTOS_DISPONIVEIS as $eq):
                        ?>
                            <option value="<?php echo htmlspecialchars($eq); ?>"
                                <?php echo in_array($eq, $selecionados) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($eq); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text">Mantenha Ctrl (ou Cmd no Mac) pressionado para selecionar múltiplos itens. Pode deixar vazio.</small>
                </div>
            </div>

            <!-- Seção de Endereço (visível apenas quando Home Office) -->
            <div id="endereco-section" style="display: <?php echo (($_POST['tipo_trabalho'] ?? '') == 'home') ? 'block' : 'none'; ?>;">
                <div class="section-divider">
                    <h3><i class="fas fa-home"></i> Endereço Residencial</h3>
                    <small class="form-text">Todos os campos de endereço são opcionais</small>
                </div>
                <div class="form-grid">
                    <div class="form-group full-width opt">
                        <label for="endereco">
                            <i class="fas fa-road"></i>
                            <span>Logradouro <span class="optional-tag">opcional</span></span>
                        </label>
                        <input type="text"
                               id="endereco"
                               name="endereco"
                               value="<?php echo htmlspecialchars($_POST['endereco'] ?? ''); ?>"
                               class="form-control"
                               placeholder="Rua, Avenida, Alameda...">
                    </div>

                    <div class="form-group opt">
                        <label for="numero">
                            <i class="fas fa-hashtag"></i>
                            <span>Número <span class="optional-tag">opcional</span></span>
                        </label>
                        <input type="text"
                               id="numero"
                               name="numero"
                               value="<?php echo htmlspecialchars($_POST['numero'] ?? ''); ?>"
                               class="form-control"
                               placeholder="Número">
                    </div>

                    <div class="form-group opt">
                        <label for="complemento">
                            <i class="fas fa-plus-circle"></i>
                            <span>Complemento <span class="optional-tag">opcional</span></span>
                        </label>
                        <input type="text"
                               id="complemento"
                               name="complemento"
                               value="<?php echo htmlspecialchars($_POST['complemento'] ?? ''); ?>"
                               class="form-control"
                               placeholder="Apto, Bloco, Casa...">
                    </div>

                    <div class="form-group opt">
                        <label for="bairro">
                            <i class="fas fa-location-dot"></i>
                            <span>Bairro <span class="optional-tag">opcional</span></span>
                        </label>
                        <input type="text"
                               id="bairro"
                               name="bairro"
                               value="<?php echo htmlspecialchars($_POST['bairro'] ?? ''); ?>"
                               class="form-control"
                               placeholder="Bairro">
                    </div>

                    <div class="form-group opt">
                        <label for="cidade">
                            <i class="fas fa-city"></i>
                            <span>Cidade <span class="optional-tag">opcional</span></span>
                        </label>
                        <input type="text"
                               id="cidade"
                               name="cidade"
                               value="<?php echo htmlspecialchars($_POST['cidade'] ?? ''); ?>"
                               class="form-control"
                               placeholder="Cidade">
                    </div>

                    <div class="form-group opt">
                        <label for="estado">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Estado <span class="optional-tag">opcional</span></span>
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

                    <div class="form-group opt">
                        <label for="cep">
                            <i class="fas fa-mail-bulk"></i>
                            <span>CEP <span class="optional-tag">opcional</span></span>
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
                <div class="form-group opt">
                    <label for="departamento">
                        <i class="fas fa-building"></i>
                        <span>Departamento <span class="optional-tag">opcional</span></span>
                    </label>
                    <?php include '../includes/departamentos.php' ?>
                </div>

                <div class="form-group opt">
                    <label for="centro_custo">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Centro de Custo <span class="optional-tag">opcional</span></span>
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

<!-- ==================== TELA DE CONFIRMAÇÃO DE MESCLAGEM ==================== -->
<?php if ($exibirConfirmacao && !empty($divergencias)): ?>
<div class="confirmacao-overlay" id="modal-confirmacao">
    <form method="POST" action="" class="confirmacao-modal" id="form-confirmacao">
        <!-- Hidden fields para carregar dados do form original para a etapa de confirmação -->
        <input type="hidden" name="acao" value="">
        <input type="hidden" name="matricula_confirm" value="<?php echo htmlspecialchars($dadosParaConfirmar['matricula'] ?? ''); ?>">
        <input type="hidden" name="nome_confirm" value="<?php echo htmlspecialchars($dadosParaConfirmar['nome'] ?? ''); ?>">
        <input type="hidden" name="cargo_confirm" value="<?php echo htmlspecialchars($dadosParaConfirmar['cargo'] ?? ''); ?>">
        <input type="hidden" name="cpf_confirm" value="<?php echo htmlspecialchars($dadosParaConfirmar['cpf'] ?? ''); ?>">
        <input type="hidden" name="departamento_confirm" value="<?php echo htmlspecialchars($dadosParaConfirmar['departamento'] ?? ''); ?>">
        <input type="hidden" name="centro_custo_confirm" value="<?php echo htmlspecialchars($dadosParaConfirmar['centro_custo'] ?? ''); ?>">
        <input type="hidden" name="email_confirm" value="<?php echo htmlspecialchars($dadosParaConfirmar['email'] ?? ''); ?>">
        <input type="hidden" name="tipo_trabalho_confirm" value="<?php echo htmlspecialchars($dadosParaConfirmar['tipo_trabalho'] ?? 'local'); ?>">
        <?php foreach ($dadosParaConfirmar['equipamentos'] ?? [] as $eq): ?>
            <input type="hidden" name="equipamentos_confirm[]" value="<?php echo htmlspecialchars($eq); ?>">
        <?php endforeach; ?>
        <?php if (($dadosParaConfirmar['tipo_trabalho'] ?? '') === 'home' && !empty($dadosParaConfirmar['endereco'])): ?>
            <input type="hidden" name="end_logradouro_confirm" value="<?php echo htmlspecialchars($dadosParaConfirmar['endereco']['logradouro'] ?? ''); ?>">
            <input type="hidden" name="end_numero_confirm" value="<?php echo htmlspecialchars($dadosParaConfirmar['endereco']['numero'] ?? ''); ?>">
            <input type="hidden" name="end_complemento_confirm" value="<?php echo htmlspecialchars($dadosParaConfirmar['endereco']['complemento'] ?? ''); ?>">
            <input type="hidden" name="end_bairro_confirm" value="<?php echo htmlspecialchars($dadosParaConfirmar['endereco']['bairro'] ?? ''); ?>">
            <input type="hidden" name="end_cidade_confirm" value="<?php echo htmlspecialchars($dadosParaConfirmar['endereco']['cidade'] ?? ''); ?>">
            <input type="hidden" name="end_estado_confirm" value="<?php echo htmlspecialchars($dadosParaConfirmar['endereco']['estado'] ?? ''); ?>">
            <input type="hidden" name="end_cep_confirm" value="<?php echo htmlspecialchars($dadosParaConfirmar['endereco']['cep'] ?? ''); ?>">
        <?php endif; ?>

        <div class="confirmacao-header">
            <div class="icon-wrap"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <h2>Confirmar Mesclagem de Dados</h2>
                <p>Matrícula <strong><?php echo htmlspecialchars($dadosParaConfirmar['matricula']); ?></strong> já existe. Revise as alterações abaixo.</p>
            </div>
        </div>

        <div class="confirmacao-body">
            <div class="confirmacao-info">
                <i class="fas fa-info-circle"></i>
                Colaborador existente: <strong><?php echo htmlspecialchars($colaboradorExistente['nome'] ?? '(sem nome)'); ?></strong><br>
                Foram encontradas <strong><?php echo count($divergencias); ?> alteração(ões)</strong>.
                Campos em branco no formulário novo <strong>não</strong> alteram os valores existentes.
            </div>

            <ul class="divergencia-lista">
                <?php foreach ($divergencias as $div): ?>
                <li class="divergencia-item">
                    <div class="divergencia-label"><?php echo htmlspecialchars($div['rotulo']); ?></div>
                    <div class="divergencia-valores">
                        <div class="valor-atual"><?php echo htmlspecialchars($div['valor_atual']); ?></div>
                        <div class="valor-set">→</div>
                        <div class="valor-novo"><?php echo htmlspecialchars($div['valor_novo']); ?></div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="confirmacao-footer">
            <button type="button" class="btn-cancelar-merge" onclick="cancelarMesclagem()">
                <i class="fas fa-times"></i> Cancelar (nenhuma alteração)
            </button>
            <button type="button" class="btn-confirmar-merge" onclick="confirmarMesclagem()">
                <i class="fas fa-check"></i> Confirmar e aplicar todas as alterações
            </button>
        </div>
    </form>
</div>

<script>
    function confirmarMesclagem() {
        document.querySelector('input[name="acao"]').value = 'confirmar_mesclagem';
        document.getElementById('form-confirmacao').submit();
    }
    function cancelarMesclagem() {
        if (confirm('Tem certeza? Toda a operação de mesclagem será cancelada e nenhum dado será alterado.')) {
            document.querySelector('input[name="acao"]').value = 'cancelar_mesclagem';
            document.getElementById('form-confirmacao').submit();
        }
    }
</script>
<?php endif; ?>

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
