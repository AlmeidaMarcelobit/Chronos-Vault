<?php
session_start();
require_once '../includes/funcoes.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

// Verificar se é administrador
if (($_SESSION['usuario_nivel'] ?? 'user') !== 'admin') {
    $_SESSION['mensagem'] = 'Acesso negado. Apenas administradores podem acessar esta página.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: ../index.php');
    exit;
}

$usuarios = lerArquivoJSON('../data/usuarios.json');

// Garantir que todos os usuários tenham os campos necessários
foreach ($usuarios as &$u) {
    if (!isset($u['nivel'])) $u['nivel'] = 'user';
    if (!isset($u['ativo'])) $u['ativo'] = true;
}

// Ordenar usuários por nome
usort($usuarios, function($a, $b) {
    return strcmp($a['nome'], $b['nome']);
});

$totalUsuarios = count($usuarios);
$totalAtivos = count(array_filter($usuarios, function($u) { return $u['ativo'] === true; }));
$totalInativos = $totalUsuarios - $totalAtivos;
$totalAdmins = count(array_filter($usuarios, function($u) { return $u['nivel'] === 'admin'; }));
$totalView = count(array_filter($usuarios, function($u) { return $u['nivel'] === 'view'; }));
$totalUsers = count(array_filter($usuarios, function($u) { return $u['nivel'] === 'user'; }));

// Busca
$busca = $_GET['busca'] ?? '';
if ($busca) {
    $usuarios = array_filter($usuarios, function($usuario) use ($busca) {
        return stripos($usuario['nome'], $busca) !== false ||
                stripos($usuario['username'], $busca) !== false ||
                stripos($usuario['email'], $busca) !== false;
    });
}

$usuario_atual_id = $_SESSION['usuario_id'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/usuarios.css">
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
                <span class="user-level user-level-admin">Admin</span>
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
            <li class="nav-item"><a href="../colaboradores/index.php" class="nav-link"><i class="fas fa-users"></i><span>Colaboradores</span></a></li>
            <li class="nav-item"><a href="../equipamentos/index.php" class="nav-link"><i class="fas fa-laptop"></i><span>Equipamentos</span></a></li>
            <li class="nav-item"><a href="../linhas/index.php" class="nav-link"><i class="fas fa-phone"></i><span>Linhas</span></a></li>
                        <li class="nav-item"><a href="../Termos/index.php" class="nav-link"><i class="fas fa-file-contract"></i><span>Termos</span></a></li>
<li class="nav-item"><a href="index.php" class="nav-link active"><i class="fas fa-user-cog"></i><span>Usuários</span></a></li>
        </ul>
    </nav>
</header>

<!-- ==================== CONTEÚDO PRINCIPAL ==================== -->
<main class="main-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-user-cog"></i> Usuários</h1>
            <p class="page-subtitle">Gerencie os usuários do sistema</p>
        </div>
        <a href="adicionar.php" class="btn btn-primary">
            <i class="fas fa-user-plus"></i>
            <span>Adicionar Usuário</span>
        </a>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-content">
                <h3>Total</h3>
                <p class="stat-number"><?php echo $totalUsuarios; ?></p>
            </div>
        </div>
        <div class="stat-card stat-success">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-content">
                <h3>Ativos</h3>
                <p class="stat-number"><?php echo $totalAtivos; ?></p>
            </div>
        </div>
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="fas fa-ban"></i></div>
            <div class="stat-content">
                <h3>Inativos</h3>
                <p class="stat-number"><?php echo $totalInativos; ?></p>
            </div>
        </div>
        <div class="stat-card stat-info">
            <div class="stat-icon"><i class="fas fa-shield-alt"></i></div>
            <div class="stat-content">
                <h3>Administradores</h3>
                <p class="stat-number"><?php echo $totalAdmins; ?></p>
            </div>
        </div>
    </div>

    <!-- Busca -->
    <div class="search-section">
        <form method="GET" action="" class="search-form">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="busca" class="search-input" placeholder="Buscar por nome, usuário ou e-mail..." value="<?php echo htmlspecialchars($busca); ?>">
                <button type="submit" class="btn btn-primary search-btn"><i class="fas fa-search"></i> Buscar</button>
                <?php if ($busca): ?>
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Limpar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabela de Usuários -->
    <div class="table-container">
        <table class="data-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Usuário</th>
                <th>E-mail</th>
                <th>Nível</th>
                <th>Status</th>
                <th>Data Cadastro</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="8" class="empty-state">
                        <i class="fas fa-users-slash"></i>
                        <p>Nenhum usuário encontrado</p>
                        <?php if ($busca): ?>
                            <a href="index.php" class="btn btn-secondary">Limpar busca</a>
                        <?php else: ?>
                            <a href="adicionar.php" class="btn btn-primary">Adicionar primeiro usuário</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($usuarios as $usuario):
                    $nivel = $usuario['nivel'] ?? 'user';
                    $isAdmin = $nivel === 'admin';
                    $isView = $nivel === 'view';
                    $isAtivo = $usuario['ativo'] === true;
                    ?>
                    <tr>
                        <td data-label="ID"><?php echo $usuario['id']; ?></td>
                        <td data-label="Nome"><?php echo htmlspecialchars($usuario['nome']); ?></td>
                        <td data-label="Usuário"><?php echo htmlspecialchars($usuario['username']); ?></td>
                        <td data-label="E-mail"><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td data-label="Nível">
                                <span class="nivel-badge nivel-<?php echo $nivel; ?>">
                                    <i class="fas fa-<?php echo $isAdmin ? 'crown' : ($isView ? 'eye' : 'user'); ?>"></i>
                                    <?php echo $isAdmin ? 'Administrador' : ($isView ? 'Visualizador' : 'Usuário'); ?>
                                </span>
                        </td>
                        <td data-label="Status">
                                <span class="status-badge status-<?php echo $isAtivo ? 'ativo' : 'inativo'; ?>">
                                    <i class="fas fa-<?php echo $isAtivo ? 'check-circle' : 'ban'; ?>"></i>
                                    <?php echo $isAtivo ? 'Ativo' : 'Inativo'; ?>
                                </span>
                        </td>
                        <td data-label="Data Cadastro">
                            <?php echo isset($usuario['data_cadastro']) ? formatarData($usuario['data_cadastro']) : '---'; ?>
                        </td>
                        <td data-label="Ações">
                            <div class="action-buttons">
                                <a href="editar.php?id=<?php echo $usuario['id']; ?>" class="action-btn action-edit" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($usuario['id'] != $usuario_atual_id): ?>
                                    <a href="excluir.php?id=<?php echo $usuario['id']; ?>" class="action-btn action-delete" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
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
                <li><a href="../colaboradores/index.php"><i class="fas fa-users"></i> Colaboradores</a></li>
                <li><a href="../equipamentos/index.php"><i class="fas fa-laptop"></i> Equipamentos</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Estatísticas</h3>
            <div class="footer-stats">
                <div class="footer-stat">
                    <span class="stat-number"><?php echo $totalUsuarios; ?></span>
                    <span class="stat-label">Usuários</span>
                </div>
                <div class="footer-stat">
                    <span class="stat-number"><?php echo $totalAtivos; ?></span>
                    <span class="stat-label">Ativos</span>
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
</body>
</html>