<?php
session_start();
require_once 'includes/funcoes.php';

// Usuários fixos para teste (EM AMBIENTE PRODUÇÃO, NUNCA FAÇA ISSO!)
$usuarios_teste = [
    [
        'id' => 1,
        'username' => 'admin',
        'password' => 'UadI-FjKcDV8', // Senha em texto plano - APENAS PARA TESTE
        'nome' => 'Administrador',
        'email' => 'admin@sistema.com'
    ],
    [
        'id' => 2,
        'username' => 'user',
        'password' => 'user123', // Senha em texto plano - APENAS PARA TESTE
        'nome' => 'Usuário Teste',
        'email' => 'user@sistema.com'
    ],
        [
        'id' => 3,
        'username' => 'Felipe',
        'password' => 'aQxMGwij', // Senha em texto plano - APENAS PARA TESTE
        'nome' => 'Felipe',
        'email' => 'felipe@amorsaude.tech'
    ]
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    foreach ($usuarios_teste as $usuario) {
        if ($usuario['username'] === $username && $usuario['password'] === $password) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
//            $_SESSION['login_time'] = time();
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Location: index.php');
            // header('Location: https://amorsaude.tech/inventario/index.php');
            exit;
        }
    }
    
    // Se não encontrou
    header('Location: login.php?erro=1');
    exit;
} else {
    header('Location: login.php');
    exit;
}