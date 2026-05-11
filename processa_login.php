<?php
/**
 * processa_login.php - Processamento do login
 *
 * Esta página processa as credenciais de login e inicia a sessão do usuário.
 */

session_start();
require_once 'includes/funcoes.php';

// Configurações de segurança
ini_set('display_errors', 0);
error_reporting(0);

// Headers de segurança
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Verificar se já está logado
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

// Verificar tentativas de login
$maxTentativas = 5;
$bloqueioTempo = 900; // 15 minutos

// Inicializar contador de tentativas
if (!isset($_SESSION['login_tentativas'])) {
    $_SESSION['login_tentativas'] = 0;
    $_SESSION['ultima_tentativa'] = time();
}

// Verificar se está bloqueado
if ($_SESSION['login_tentativas'] >= $maxTentativas) {
    $tempoDecorrido = time() - $_SESSION['ultima_tentativa'];
    if ($tempoDecorrido < $bloqueioTempo) {
        header('Location: login.php?erro=bloqueado');
        exit;
    } else {
        // Resetar tentativas após o tempo de bloqueio
        $_SESSION['login_tentativas'] = 0;
    }
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Validar token CSRF (opcional - recomendo implementar)
// if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
//     header('Location: login.php?erro=csrf');
//     exit;
// }

// Sanitizar e validar inputs
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$lembrar = isset($_POST['lembrar']) && $_POST['lembrar'] == '1';

// Validar campos obrigatórios
if (empty($username) || empty($password)) {
    $_SESSION['login_tentativas']++;
    $_SESSION['ultima_tentativa'] = time();
    header('Location: login.php?erro=campos_vazios');
    exit;
}

// Limitar tamanho dos inputs (prevenção contra ataques)
if (strlen($username) > 50 || strlen($password) > 100) {
    $_SESSION['login_tentativas']++;
    $_SESSION['ultima_tentativa'] = time();
    header('Location: login.php?erro=dados_invalidos');
    exit;
}

// Carregar usuários do arquivo JSON (método correto)
$usuarios = lerArquivoJSON('data/usuarios.json');

// Se não houver usuários no JSON, usar array padrão (apenas para primeira execução)
if (empty($usuarios)) {
    // WARNING: Isso é apenas para desenvolvimento! Em produção, remova ou altere as senhas.
    $usuarios = [
        [
            'id' => 1,
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT), // Senha hash
            'nome' => 'Administrador',
            'email' => 'admin@sistema.com',
            'nivel' => 'admin',
            'ativo' => true
        ],
        [
            'id' => 2,
            'username' => 'user',
            'password' => password_hash('user123', PASSWORD_DEFAULT),
            'nome' => 'Usuário Teste',
            'email' => 'user@sistema.com',
            'nivel' => 'user',
            'ativo' => true
        ]
    ];

    // Salvar usuários no JSON
    salvarArquivoJSON('data/usuarios.json', $usuarios);
}

// Buscar usuário
$usuarioEncontrado = null;
foreach ($usuarios as $usuario) {
    if ($usuario['username'] === $username) {
        // Verificar se usuário está ativo
        if (isset($usuario['ativo']) && $usuario['ativo'] === false) {
            $_SESSION['login_tentativas']++;
            header('Location: login.php?erro=usuario_inativo');
            exit;
        }

        // Verificar senha (compatível com hash e com texto plano para migração)
        $senhaValida = false;

        // Verificar se a senha está em hash (password_hash)
        if (password_verify($password, $usuario['password'])) {
            $senhaValida = true;
        }
        // Compatibilidade com senhas em texto plano (para migração - remover depois)
        elseif (isset($usuario['password_plain']) && $usuario['password_plain'] === $password) {
            $senhaValida = true;
            // Converter para hash na próxima atualização
            // (implementar lógica de upgrade)
        }

        if ($senhaValida) {
            $usuarioEncontrado = $usuario;
            break;
        }
    }
}

// Validar credenciais
if ($usuarioEncontrado) {
    // Resetar tentativas de login
    $_SESSION['login_tentativas'] = 0;

    // Definir variáveis de sessão
    $_SESSION['usuario_id'] = $usuarioEncontrado['id'];
    $_SESSION['usuario_nome'] = $usuarioEncontrado['nome'];
    $_SESSION['usuario_email'] = $usuarioEncontrado['email'] ?? '';
    $_SESSION['usuario_nivel'] = $usuarioEncontrado['nivel'] ?? 'user';
    $_SESSION['login_time'] = time();
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Configurar cookie "lembrar-me" (30 dias)
    if ($lembrar) {
        $token = bin2hex(random_bytes(32));
        $expiracao = time() + (86400 * 30); // 30 dias

        // Salvar token no banco/JSON (opcional)
        // Por enquanto, apenas setar cookie com username
        setcookie('usuario_salvo', $username, $expiracao, '/', '', true, true);
    } else {
        // Remover cookie se existir
        if (isset($_COOKIE['usuario_salvo'])) {
            setcookie('usuario_salvo', '', time() - 3600, '/');
        }
    }

    // Registrar log de acesso (opcional)
    registrarLog('login_sucesso', "Usuário {$usuarioEncontrado['nome']} fez login");

    // Redirecionar para o dashboard
    header('Location: index.php');
    exit;
} else {
    // Incrementar tentativas de login
    $_SESSION['login_tentativas']++;
    $_SESSION['ultima_tentativa'] = time();

    // Registrar log de falha (opcional)
    registrarLog('login_falha', "Tentativa de login falha para usuário: {$username}");

    // Redirecionar com erro
    header('Location: login.php?erro=1');
    exit;
}
?>