<?php
// Inicia a sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar sessão com timeout
function verificarSessao() {
    if (!isset($_SESSION['usuario_id'])) {
        // Determinar o caminho correto para login.php
        $current_path = $_SERVER['PHP_SELF'];
        if (strpos($current_path, '/colaboradores/') !== false ||
            strpos($current_path, '/equipamentos/') !== false) {
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
                strpos($current_path, '/equipamentos/') !== false) {
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

    $json = json_encode($dados, JSON_PRETTY_PRINT);

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

// Formatadores (VERSÃO ÚNICA CORRIGIDA)
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

// Validar CPF
function validarCPF($cpf) {
    if (empty($cpf)) {
        return false;
    }

    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    if (strlen($cpf) != 11) {
        return false;
    }

    // Verifica se todos os dígitos são iguais
    if (preg_match('/^(\d)\1*$/', $cpf)) {
        return false;
    }

    // Validação do CPF usando algoritmo oficial
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

// Função para obter tipos de equipamentos (ATUALIZADA)
function getTiposEquipamentos() {
    return [
        'notebook' => 'Notebook',
        'celular' => 'Celular',
        'suporte' => 'Suporte',
        'monitor' => 'Monitor',
        'teclado' => 'Teclado',
        'mouse' => 'Mouse',
        'adaptador' => 'Adaptador de Rede',
        'fone' => 'Fone de Ouvido',
        'outros' => 'Outros'
    ];
}

// Função para obter status disponíveis
function getStatusEquipamentos() {
    return [
        'estoque' => 'Em Estoque',
        'alocado' => 'Alocado para Colaborador',
        'fora_uso' => 'Fora de Uso',
        'manutencao' => 'Em Manutenção',
        'emprestado' => 'Emprestado'
    ];
}

// Função para obter texto do status
function getStatusTexto($status) {
    $statuses = getStatusEquipamentos();
    return $statuses[$status] ?? 'Desconhecido';
}

// Função para obter texto do tipo
function getTipoTexto($tipo) {
    $tipos = getTiposEquipamentos();
    return $tipos[$tipo] ?? 'Outro';
}

// Função para verificar se um status permite atribuição a colaborador
function statusPermiteAtribuicao($status) {
    $statusesComAtribuicao = ['alocado', 'emprestado'];
    return in_array($status, $statusesComAtribuicao);
}

// Função para verificar se um status permite estar no estoque
function statusPermiteEstoque($status) {
    $statusesComEstoque = ['estoque', 'manutencao', 'fora_uso'];
    return in_array($status, $statusesComEstoque);
}

// Função para obter ícone baseado no tipo de equipamento
function getIconByType($tipo) {
    $icones = [
        'notebook' => 'laptop',
        'celular' => 'mobile-alt',
        'suporte' => 'desktop',
        'monitor' => 'tv',
        'teclado' => 'keyboard',
        'mouse' => 'mouse',
        'adaptador' => 'plug',
        'fone' => 'headphones',
        'outros' => 'laptop-medical'
    ];

    return $icones[$tipo] ?? 'laptop-medical';
}

// Função para obter ícone baseado no status
function getIconByStatus($status) {
    $icons = [
        'estoque' => 'warehouse',
        'alocado' => 'user-check',
        'fora_uso' => 'times-circle',
        'manutencao' => 'tools',
        'emprestado' => 'handshake'
    ];

    return $icons[$status] ?? 'question-circle';
}

// Função para limpar e validar string
function limparString($string) {
    if (!is_string($string)) {
        return '';
    }

    $string = trim($string);
    $string = stripslashes($string);
    $string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');

    return $string;
}

// Função para validar e-mail
function validarEmail($email) {
    if (empty($email)) {
        return false;
    }

    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Função para formatar número de telefone
function formatarTelefone($telefone) {
    if (empty($telefone)) {
        return '';
    }

    $telefone = preg_replace('/[^0-9]/', '', $telefone);

    $tamanho = strlen($telefone);

    if ($tamanho == 10) { // (99) 9999-9999
        return '(' . substr($telefone, 0, 2) . ') ' .
            substr($telefone, 2, 4) . '-' .
            substr($telefone, 6, 4);
    } elseif ($tamanho == 11) { // (99) 99999-9999
        return '(' . substr($telefone, 0, 2) . ') ' .
            substr($telefone, 2, 5) . '-' .
            substr($telefone, 7, 4);
    }

    return $telefone;
}

// Função para criar slug (URL amigável)
function criarSlug($texto) {
    if (empty($texto)) {
        return '';
    }

    // Converte para minúsculas
    $texto = mb_strtolower($texto, 'UTF-8');

    // Remove acentos
    $texto = preg_replace('/[áàâãä]/u', 'a', $texto);
    $texto = preg_replace('/[éèêë]/u', 'e', $texto);
    $texto = preg_replace('/[íìîï]/u', 'i', $texto);
    $texto = preg_replace('/[óòôõö]/u', 'o', $texto);
    $texto = preg_replace('/[úùûü]/u', 'u', $texto);
    $texto = preg_replace('/[ç]/u', 'c', $texto);
    $texto = preg_replace('/[ñ]/u', 'n', $texto);

    // Substitui espaços e caracteres especiais por hífen
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);

    // Remove hífens do início e fim
    $texto = trim($texto, '-');

    return $texto;
}

// Função para exibir mensagem de alerta
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

// Função para criar backup dos dados
function criarBackup($diretorio = 'backups/') {
    $arquivos = [
        'data/colaboradores.json',
        'data/equipamentos.json',
        'data/usuarios.json'
    ];

    $timestamp = date('Y-m-d_H-i-s');
    $pastaBackup = $diretorio . $timestamp . '/';

    if (!file_exists($pastaBackup)) {
        mkdir($pastaBackup, 0777, true);
    }

    foreach ($arquivos as $arquivo) {
        if (file_exists($arquivo)) {
            copy($arquivo, $pastaBackup . basename($arquivo));
        }
    }

    return $pastaBackup;
}

// Função para log de atividades
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

// Função para obter valor estimado do equipamento (NOVA - útil para o termo)
function getValorEstimadoEquipamento($tipo) {
    $valores = [
        'notebook' => 3500.00,
        'celular' => 2000.00,
        'suporte' => 1200.00,
        'monitor' => 800.00,
        'teclado' => 150.00,
        'mouse' => 80.00,
        'adaptador' => 120.00,
        'fone' => 200.00,
        'outros' => 500.00
    ];

    return $valores[$tipo] ?? 500.00;
}

// Função para formatar valor monetário (NOVA)
function formatarMoeda($valor) {
    if (!is_numeric($valor)) {
        return 'R$ 0,00';
    }

    return 'R$ ' . number_format($valor, 2, ',', '.');
}

// Função para obter data atual formatada (NOVA)
function getDataAtual($formato = 'd/m/Y') {
    return date($formato);
}

// Função para gerar código único para termos (NOVA)
function gerarCodigoTermo($colaboradorId, $tipo = 'COL') {
    $timestamp = date('YmdHis');
    $idFormatado = str_pad($colaboradorId, 4, '0', STR_PAD_LEFT);
    return $tipo . '-' . $idFormatado . '-' . $timestamp;
}

// Função para contar equipamentos do colaborador (NOVA)
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
?>