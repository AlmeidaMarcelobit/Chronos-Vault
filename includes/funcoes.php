<?php
// Define o fuso horário para América/São Paulo (Brasília)
date_default_timezone_set('America/Sao_Paulo');

// Inicia a sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// FUNÇÕES DE SESSÃO E AUTENTICAÇÃO
// ============================================

// Verificar sessão com timeout
function verificarSessao() {
    if (!isset($_SESSION['usuario_id'])) {
        // Determinar o caminho correto para login.php
        $current_path = $_SERVER['PHP_SELF'];
        if (strpos($current_path, '/colaboradores/') !== false ||
            strpos($current_path, '/equipamentos/') !== false ||
            strpos($current_path, '/linhas/') !== false) {
            header('Location: ../login.php');
        } else {
            header('Location: login.php');
        }
        exit;
    }

    // Verificar timeout (30 minutos)
    verificarTimeoutSessao();
}

// Verificar timeout da sessão (30 minutos)
function verificarTimeoutSessao() {
    $timeout = 1800; // 30 minutos em segundos

    if (isset($_SESSION['login_time'])) {
        $tempoDecorrido = time() - $_SESSION['login_time'];

        if ($tempoDecorrido > $timeout) {
            // Sessão expirou
            session_unset();
            session_destroy();

            // Redirecionar para login com mensagem de timeout
            $current_path = $_SERVER['PHP_SELF'];
            if (strpos($current_path, '/colaboradores/') !== false ||
                strpos($current_path, '/equipamentos/') !== false ||
                strpos($current_path, '/linhas/') !== false) {
                header('Location: ../login.php?timeout=1');
            } else {
                header('Location: login.php?timeout=1');
            }
            exit;
        } else {
            // Atualizar tempo da sessão
            $_SESSION['login_time'] = time();
        }
    }
}

// ============================================
// FUNÇÕES DE ARQUIVO JSON
// ============================================

// Ler arquivo JSON
function lerArquivoJSON($caminho) {
    if (!file_exists($caminho)) {
        return [];
    }

    $conteudo = file_get_contents($caminho);
    if (empty($conteudo) || trim($conteudo) === '') {
        return [];
    }

    $dados = json_decode($conteudo, true);

    // Verificar se o JSON é válido
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [];
    }

    return $dados;
}

// Salvar arquivo JSON
function salvarArquivoJSON($caminho, $dados) {
    // Certificar-se de que os dados são um array
    if (!is_array($dados)) {
        return false;
    }

    $json = json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    // Criar diretório se não existir
    $diretorio = dirname($caminho);
    if (!file_exists($diretorio)) {
        mkdir($diretorio, 0777, true);
    }

    return file_put_contents($caminho, $json) !== false;
}

// Gerar ID único
function gerarId($dados) {
    if (empty($dados) || !is_array($dados)) {
        return 1;
    }

    $ids = array_column($dados, 'id');

    // Filtrar apenas valores numéricos válidos
    $ids = array_filter($ids, function($id) {
        return is_numeric($id) && $id > 0;
    });

    if (empty($ids)) {
        return 1;
    }

    return max($ids) + 1;
}

// ============================================
// FUNÇÕES DE FORMATAÇÃO
// ============================================

// Formatar CPF
function formatarCPF($cpf) {
    if (empty($cpf)) {
        return '';
    }

    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    if (strlen($cpf) == 11) {
        return substr($cpf, 0, 3) . '.' .
            substr($cpf, 3, 3) . '.' .
            substr($cpf, 6, 3) . '-' .
            substr($cpf, 9, 2);
    }

    return $cpf;
}

// Formatar data
function formatarData($data, $formato = 'd/m/Y H:i') {
    if (empty($data) || $data === '---') {
        return '---';
    }

    try {
        $dataObj = new DateTime($data);
        return $dataObj->format($formato);
    } catch (Exception $e) {
        // Tentar formato alternativo
        $timestamp = strtotime($data);
        if ($timestamp === false) {
            return '---';
        }
        return date($formato, $timestamp);
    }
}

// Formatar número de telefone
function formatarTelefone($telefone) {
    if (empty($telefone)) {
        return '';
    }

    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    $tamanho = strlen($telefone);

    if ($tamanho == 10) {
        return substr($telefone, 0, 2) . ' ' .
            substr($telefone, 2, 4) . '-' .
            substr($telefone, 6, 4);
    } elseif ($tamanho == 11) {
        return substr($telefone, 0, 2) . ' ' .
            substr($telefone, 2, 5) . '-' .
            substr($telefone, 7, 4);
    }

    return $telefone;
}

// Formatar valor monetário
function formatarMoeda($valor) {
    if (!is_numeric($valor)) {
        return 'R$ 0,00';
    }
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

// ============================================
// FUNÇÕES DE VALIDAÇÃO
// ============================================

// Validar CPF
function validarCPF($cpf) {
    if (empty($cpf)) {
        return false;
    }

    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    if (strlen($cpf) != 11) {
        return false;
    }

    if (preg_match('/^(\d)\1*$/', $cpf)) {
        return false;
    }

    for ($t = 9; $t < 11; $t++) {
        $soma = 0;
        for ($c = 0; $c < $t; $c++) {
            $soma += $cpf[$c] * (($t + 1) - $c);
        }

        $resto = $soma % 11;
        $digito = ($resto < 2) ? 0 : 11 - $resto;

        if ($cpf[$c] != $digito) {
            return false;
        }
    }

    return true;
}

// Validar e-mail
function validarEmail($email) {
    if (empty($email)) {
        return false;
    }
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validar número de telefone
function validarTelefone($numero) {
    $numero = preg_replace('/[^0-9]/', '', $numero);
    $tamanho = strlen($numero);
    return in_array($tamanho, [10, 11]);
}

// Validar CEP
function validarCEP($cep) {
    $cep = preg_replace('/[^0-9]/', '', $cep);
    return strlen($cep) === 8;
}

// Validar estado (UF)
function validarUF($uf) {
    $ufsValidas = [
        'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
        'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
        'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
    ];
    return in_array(strtoupper($uf), $ufsValidas);
}

// ============================================
// FUNÇÕES PARA EQUIPAMENTOS
// ============================================

/**
 * Obtém o caminho do arquivo JSON baseado no status do equipamento
 */
function getCaminhoEquipamentoPorStatus($status) {
    $base = __DIR__ . '/../data/equipamentos/';
    $caminhos = [
        'estoque'   => $base . 'estoque.json',
        'alocado'   => $base . 'alocados.json',
        'emprestado'=> $base . 'emprestados.json',
        'manutencao'=> $base . 'manutencao.json',
        'fora_uso'  => $base . 'fora_uso.json'
    ];
    return $caminhos[$status] ?? $base . 'estoque.json';
}

/**
 * Carrega equipamentos de um status específico
 */
function carregarEquipamentosPorStatus($status) {
    $caminho = getCaminhoEquipamentoPorStatus($status);
    $equipamentos = lerArquivoJSON($caminho);
    return is_array($equipamentos) ? $equipamentos : [];
}

/**
 * Carrega todos os equipamentos de todos os status
 */
function carregarTodosEquipamentos() {
    $statuses = ['estoque', 'alocado', 'emprestado', 'manutencao', 'fora_uso'];
    $todosEquipamentos = [];
    
    foreach ($statuses as $status) {
        $equipamentos = carregarEquipamentosPorStatus($status);
        $todosEquipamentos = array_merge($todosEquipamentos, $equipamentos);
    }
    
    return $todosEquipamentos;
}

/**
 * Busca equipamento por ID em todos os status
 */
function buscarEquipamentoPorId($id) {
    $statuses = ['estoque', 'alocado', 'emprestado', 'manutencao', 'fora_uso'];
    
    foreach ($statuses as $status) {
        $equipamentos = carregarEquipamentosPorStatus($status);
        foreach ($equipamentos as $equipamento) {
            if ($equipamento['id'] == $id) {
                $equipamento['status_origem'] = $status;
                return $equipamento;
            }
        }
    }
    
    return null;
}

/**
 * Adiciona um novo equipamento
 */
function adicionarEquipamento($equipamento) {
    $status = $equipamento['status'];
    $caminho = getCaminhoEquipamentoPorStatus($status);
    $equipamentos = carregarEquipamentosPorStatus($status);
    
    if (!isset($equipamento['id'])) {
        $todosEquipamentos = carregarTodosEquipamentos();
        $equipamento['id'] = gerarId($todosEquipamentos);
    }
    
    $equipamentos[] = $equipamento;
    return salvarArquivoJSON($caminho, $equipamentos);
}

/**
 * Move um equipamento de um status para outro
 */
function moverEquipamentoParaStatus($equipamento, $novoStatus) {
    $statusAntigo = $equipamento['status'];
    
    if ($statusAntigo === $novoStatus) {
        return atualizarEquipamento($equipamento);
    }
    
    $equipamentosAntigos = carregarEquipamentosPorStatus($statusAntigo);
    
    foreach ($equipamentosAntigos as $index => $eq) {
        if ($eq['id'] == $equipamento['id']) {
            array_splice($equipamentosAntigos, $index, 1);
            break;
        }
    }
    
    // Preservar vínculo com colaborador ao enviar para manutenção
    if ($novoStatus === 'manutencao') {
        $equipamento['status_anterior'] = $statusAntigo;
        $equipamento['data_manutencao'] = date('Y-m-d H:i:s');
        // colaborador_id é mantido intencionalmente
    } elseif ($novoStatus === 'fora_uso' || $novoStatus === 'estoque') {
        $equipamento['colaborador_id'] = null;
        $equipamento['data_atribuicao'] = null;
        unset($equipamento['status_anterior']);
    }

    $equipamento['status'] = $novoStatus;
    $equipamento['data_atualizacao'] = date('Y-m-d H:i:s');

    if (in_array($novoStatus, ['alocado', 'emprestado'])) {
        $equipamento['data_atribuicao'] = date('Y-m-d H:i:s');
    } elseif ($novoStatus !== 'manutencao') {
        $equipamento['data_atribuicao'] = null;
    }
    
    $caminhoAntigo = getCaminhoEquipamentoPorStatus($statusAntigo);
    if (!salvarArquivoJSON($caminhoAntigo, $equipamentosAntigos)) {
        return false;
    }
    
    $equipamentosNovos = carregarEquipamentosPorStatus($novoStatus);
    $equipamentosNovos[] = $equipamento;
    $caminhoNovo = getCaminhoEquipamentoPorStatus($novoStatus);
    
    return salvarArquivoJSON($caminhoNovo, $equipamentosNovos);
}

/**
 * Atualiza um equipamento existente
 */
function atualizarEquipamento($equipamento) {
    $status = $equipamento['status'];
    $caminho = getCaminhoEquipamentoPorStatus($status);
    $equipamentos = carregarEquipamentosPorStatus($status);
    
    foreach ($equipamentos as $index => $eq) {
        if ($eq['id'] == $equipamento['id']) {
            $equipamentos[$index] = $equipamento;
            return salvarArquivoJSON($caminho, $equipamentos);
        }
    }
    
    return false;
}

/**
 * Verifica se patrimônio já existe em qualquer status
 */
function patrimonioExiste($patrimonio, $idIgnorar = null) {
    $todosEquipamentos = carregarTodosEquipamentos();
    foreach ($todosEquipamentos as $equip) {
        if ($equip['patrimonio'] === $patrimonio) {
            if ($idIgnorar && $equip['id'] == $idIgnorar) {
                continue;
            }
            return true;
        }
    }
    return false;
}

/**
 * Verifica se serial já existe em qualquer status
 */
function serialExiste($serial, $idIgnorar = null) {
    if (empty($serial)) return false;
    $todosEquipamentos = carregarTodosEquipamentos();
    foreach ($todosEquipamentos as $equip) {
        if (isset($equip['serial']) && $equip['serial'] === $serial) {
            if ($idIgnorar && $equip['id'] == $idIgnorar) {
                continue;
            }
            return true;
        }
    }
    return false;
}

/**
 * Verifica se hostname já existe em qualquer status
 */
function hostnameExiste($hostname, $idIgnorar = null) {
    if (empty($hostname)) return false;
    $todosEquipamentos = carregarTodosEquipamentos();
    foreach ($todosEquipamentos as $equip) {
        if (isset($equip['hostname']) && $equip['hostname'] === $hostname) {
            if ($idIgnorar && $equip['id'] == $idIgnorar) {
                continue;
            }
            return true;
        }
    }
    return false;
}

/**
 * Retorna a lista de tipos de equipamentos com ícones
 */
function getTiposEquipamentosComIcones() {
    return [
        'desktop' => ['nome' => 'Desktop', 'icone' => 'desktop'],
        'notebook' => ['nome' => 'Notebook', 'icone' => 'laptop'],
        'monitor' => ['nome' => 'Monitor', 'icone' => 'tv'],
        'teclado' => ['nome' => 'Teclado', 'icone' => 'keyboard'],
        'mouse' => ['nome' => 'Mouse', 'icone' => 'mouse'],
        'celular' => ['nome' => 'Celular', 'icone' => 'mobile-alt'],
        'suporte' => ['nome' => 'Suporte de Notebook', 'icone' => 'toolbox'],
        'fone' => ['nome' => 'Fone', 'icone' => 'headphones'],
        'tv' => ['nome' => 'TV', 'icone' => 'tv'],
        'outro' => ['nome' => 'Outro', 'icone' => 'plus-circle']
    ];
}

/**
 * Retorna os tipos de equipamentos (versão simples)
 */
function getTiposEquipamentos() {
    return [
        'desktop' => 'Desktop',
        'notebook' => 'Notebook',
        'monitor' => 'Monitor',
        'teclado' => 'Teclado',
        'mouse' => 'Mouse',
        'celular' => 'Celular',
        'suporte' => 'Suporte de Notebook',
        'fone' => 'Fone',
        'tv' => 'TV',
        'outro' => 'Outro'
    ];
}

/**
 * Retorna as especificações que cada tipo de equipamento possui
 */
function getEspecificacoesPorTipo($tipo) {
    $especificacoes = [
        'desktop' => ['ram' => true, 'processador' => true, 'hd' => true],
        'notebook' => ['ram' => true, 'processador' => true, 'hd' => true],
        'monitor' => ['ram' => false, 'processador' => false, 'hd' => false],
        'teclado' => ['ram' => false, 'processador' => false, 'hd' => false],
        'mouse' => ['ram' => false, 'processador' => false, 'hd' => false],
        'celular' => ['ram' => true, 'processador' => true, 'hd' => true],
        'suporte' => ['ram' => false, 'processador' => false, 'hd' => false],
        'fone' => ['ram' => false, 'processador' => false, 'hd' => false],
        'tv' => ['ram' => false, 'processador' => false, 'hd' => false],
        'outro' => ['ram' => false, 'processador' => false, 'hd' => false]
    ];
    return $especificacoes[$tipo] ?? ['ram' => false, 'processador' => false, 'hd' => false];
}

/**
 * Retorna o ícone correspondente ao tipo de equipamento
 */
function getIconByType($tipo) {
    $icones = [
        'notebook' => 'laptop',
        'desktop' => 'desktop',
        'monitor' => 'tv',
        'teclado' => 'keyboard',
        'mouse' => 'mouse',
        'celular' => 'mobile-alt',
        'suporte' => 'toolbox',
        'fone' => 'headphones',
        'tv' => 'tv',
        'outro' => 'plus-circle'
    ];
    return $icones[$tipo] ?? 'question-circle';
}

/**
 * Retorna o texto do tipo de equipamento
 */
function getTipoTexto($tipo) {
    $tipos = getTiposEquipamentos();
    return $tipos[$tipo] ?? $tipo;
}

/**
 * Retorna o ícone correspondente ao status do equipamento
 */
function getIconByStatus($status) {
    $icones = [
        'estoque' => 'warehouse',
        'alocado' => 'user-check',
        'emprestado' => 'handshake',
        'manutencao' => 'tools',
        'fora_uso' => 'times-circle'
    ];
    return $icones[$status] ?? 'question-circle';
}

/**
 * Retorna o texto do status do equipamento
 */
function getStatusTexto($status) {
    $statusList = [
        'estoque' => 'Em Estoque',
        'alocado' => 'Alocado',
        'emprestado' => 'Emprestado',
        'manutencao' => 'Em Manutenção',
        'fora_uso' => 'Fora de Uso'
    ];
    return $statusList[$status] ?? $status;
}

// Obter status disponíveis para equipamentos
function getStatusEquipamentos() {
    return [
        'estoque' => 'Em Estoque',
        'alocado' => 'Alocado para Colaborador',
        'fora_uso' => 'Fora de Uso',
        'manutencao' => 'Em Manutenção',
        'emprestado' => 'Emprestado'
    ];
}

// Verificar se um status permite atribuição a colaborador
function statusPermiteAtribuicao($status) {
    $statusesComAtribuicao = ['alocado', 'emprestado'];
    return in_array($status, $statusesComAtribuicao);
}

// Verificar se um status permite estar no estoque
function statusPermiteEstoque($status) {
    $statusesComEstoque = ['estoque', 'manutencao', 'fora_uso'];
    return in_array($status, $statusesComEstoque);
}

// Obter valor estimado do equipamento
function getValorEstimadoEquipamento($tipo) {
    $valores = [
        'notebook' => 3500.00,
        'desktop' => 3000.00,
        'celular' => 2000.00,
        'suporte' => 1200.00,
        'monitor' => 800.00,
        'teclado' => 150.00,
        'mouse' => 80.00,
        'outro' => 500.00
    ];
    return $valores[$tipo] ?? 500.00;
}

// Contar equipamentos do colaborador
function contarEquipamentosColaborador($colaboradorId, $equipamentos) {
    $count = 0;
    foreach ($equipamentos as $equipamento) {
        if ($equipamento['colaborador_id'] == $colaboradorId &&
            in_array($equipamento['status'], ['alocado', 'emprestado'])) {
            $count++;
        }
    }
    return $count;
}

// ============================================
// FUNÇÕES PARA LINHAS TELEFÔNICAS
// ============================================

// Obter tipos de linha
function getTiposLinha() {
    return [
        'chip' => 'Chip Físico',
        'echip' => 'E-Chip (eSIM)'
    ];
}

// Obter texto do tipo de linha
function getTipoLinhaTexto($tipo) {
    $tipos = getTiposLinha();
    return $tipos[$tipo] ?? 'Desconhecido';
}

// Obter status da linha
function getStatusLinhaTexto($status) {
    $statuses = [
        'disponivel' => 'Disponível',
        'alocado' => 'Alocado',
        'indisponivel' => 'Indisponível',
        'whatsapp_bloqueado' => 'Whatsapp Bloqueado'
    ];
    return $statuses[$status] ?? 'Desconhecido';
}

// ============================================
// FUNÇÕES PARA COLABORADORES
// ============================================

// Obter lista de estados (UF)
function getEstados() {
    return [
        'AC' => 'Acre',
        'AL' => 'Alagoas',
        'AP' => 'Amapá',
        'AM' => 'Amazonas',
        'BA' => 'Bahia',
        'CE' => 'Ceará',
        'DF' => 'Distrito Federal',
        'ES' => 'Espírito Santo',
        'GO' => 'Goiás',
        'MA' => 'Maranhão',
        'MT' => 'Mato Grosso',
        'MS' => 'Mato Grosso do Sul',
        'MG' => 'Minas Gerais',
        'PA' => 'Pará',
        'PB' => 'Paraíba',
        'PR' => 'Paraná',
        'PE' => 'Pernambuco',
        'PI' => 'Piauí',
        'RJ' => 'Rio de Janeiro',
        'RN' => 'Rio Grande do Norte',
        'RS' => 'Rio Grande do Sul',
        'RO' => 'Rondônia',
        'RR' => 'Roraima',
        'SC' => 'Santa Catarina',
        'SP' => 'São Paulo',
        'SE' => 'Sergipe',
        'TO' => 'Tocantins'
    ];
}

// ============================================
// FUNÇÕES PARA TERMOS E DOCUMENTOS
// ============================================

// Obter data atual formatada
function getDataAtual($formato = 'd/m/Y') {
    return date($formato);
}

// Gerar código único para termos
function gerarCodigoTermo($colaboradorId, $tipo = 'COL') {
    $timestamp = date('YmdHis');
    $idFormatado = str_pad($colaboradorId, 4, '0', STR_PAD_LEFT);
    return $tipo . '-' . $idFormatado . '-' . $timestamp;
}

// ============================================
// FUNÇÕES UTILITÁRIAS
// ============================================

// Limpar e validar string
function limparString($string) {
    if (!is_string($string)) {
        return '';
    }

    $string = trim($string);
    $string = stripslashes($string);
    $string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    return $string;
}

// Criar slug (URL amigável)
function criarSlug($texto) {
    if (empty($texto)) {
        return '';
    }

    $texto = mb_strtolower($texto, 'UTF-8');

    $texto = preg_replace('/[áàâãä]/u', 'a', $texto);
    $texto = preg_replace('/[éèêë]/u', 'e', $texto);
    $texto = preg_replace('/[íìîï]/u', 'i', $texto);
    $texto = preg_replace('/[óòôõö]/u', 'o', $texto);
    $texto = preg_replace('/[úùûü]/u', 'u', $texto);
    $texto = preg_replace('/[ç]/u', 'c', $texto);
    $texto = preg_replace('/[ñ]/u', 'n', $texto);

    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    $texto = trim($texto, '-');
    return $texto;
}

// Exibir mensagem de alerta
function exibirAlerta($mensagem, $tipo = 'info') {
    $classes = [
        'success' => 'alert-success',
        'error' => 'alert-error',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];

    $icones = [
        'success' => 'check-circle',
        'error' => 'exclamation-circle',
        'warning' => 'exclamation-triangle',
        'info' => 'info-circle'
    ];

    $classe = $classes[$tipo] ?? $classes['info'];
    $icone = $icones[$tipo] ?? $icones['info'];

    return sprintf(
        '<div class="alert %s">
            <i class="fas fa-%s"></i>
            %s
        </div>',
        $classe,
        $icone,
        htmlspecialchars($mensagem)
    );
}

// Criar backup dos dados
function criarBackup($diretorio = 'backups/') {
    $arquivos = [
        'data/colaboradores/ativos.json',
        'data/colaboradores/inativos.json',
        'data/equipamentos/estoque.json',
        'data/equipamentos/alocados.json',
        'data/equipamentos/emprestados.json',
        'data/equipamentos/manutencao.json',
        'data/equipamentos/fora_uso.json',
        'data/linhas.json',
        'data/usuarios.json'
    ];

    $timestamp = date('Y-m-d_H-i-s');
    $pastaBackup = $diretorio . $timestamp . '/';

    if (!file_exists($pastaBackup)) {
        mkdir($pastaBackup, 0777, true);
    }

    foreach ($arquivos as $arquivo) {
        if (file_exists($arquivo)) {
            $destino = $pastaBackup . basename($arquivo);
            copy($arquivo, $destino);
        }
    }

    return $pastaBackup;
}

// Registrar log de atividades
function registrarLog($acao, $detalhes = '') {
    $logFile = 'data/logs.json';
    $logs = lerArquivoJSON($logFile);

    $log = [
        'id' => gerarId($logs),
        'usuario_id' => $_SESSION['usuario_id'] ?? null,
        'usuario_nome' => $_SESSION['usuario_nome'] ?? 'Sistema',
        'acao' => $acao,
        'detalhes' => $detalhes,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Desconhecido',
        'data' => date('Y-m-d H:i:s')
    ];

    $logs[] = $log;
    salvarArquivoJSON($logFile, $logs);
    return true;
}

// Atualizar centro de custo da linha
function atualizarCentroCustoLinha(&$linha, $colaborador, $usuario) {
    if (empty($colaborador['centro_custo'])) {
        return false;
    }

    $centroCustoAnterior = $linha['centro_custo'] ?? 'Não definido';
    $centroCustoNovo = $colaborador['centro_custo'];

    if ($centroCustoAnterior === $centroCustoNovo) {
        return false;
    }

    if (!isset($linha['historico_centro_custo']) || !is_array($linha['historico_centro_custo'])) {
        $linha['historico_centro_custo'] = [];
    }

    $historico = [
        'data' => date('Y-m-d H:i:s'),
        'usuario' => $usuario,
        'centro_custo_anterior' => $centroCustoAnterior,
        'centro_custo_novo' => $centroCustoNovo,
        'motivo' => 'Vinculação automática ao colaborador ' . $colaborador['nome']
    ];

    $linha['historico_centro_custo'][] = $historico;
    $linha['centro_custo'] = $centroCustoNovo;

    return true;
}

// Obter centro de custo padrão
function getCentroCustoPadrao() {
    return '11001';
}
?>