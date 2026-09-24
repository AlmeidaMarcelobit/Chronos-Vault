<?php
session_start();
require_once '../includes/funcoes.php';
if (!isset($_SESSION['usuario_id'])) { header('Location: ../login.php'); exit; }
$arquivo = '../data/colaboradores/terceiros.json';
$terceiros = lerArquivoJSON($arquivo);
if (!is_array($terceiros)) $terceiros = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_id'])) {
    $terceiros = array_values(array_filter($terceiros, fn($t) => (string)($t['id'] ?? '') !== (string)$_POST['excluir_id']));
    salvarArquivoJSON($arquivo, $terceiros);
    header('Location: index.php'); exit;
}
usort($terceiros, fn($a,$b) => strcmp($a['nome'] ?? '', $b['nome'] ?? ''));
$is_admin = ($_SESSION['usuario_nivel'] ?? '') === 'admin';
?>
    <!DOCTYPE html>
    <html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Terceiros - Gestão de Colaboradores</title>
        <link rel="stylesheet" href="../css/colaboradores/index.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="icon" href="../img/favicon/favicon.png">
        <style>
            .terceiros-table-wrapper{width:100%;overflow-x:auto;border:1px solid var(--gray-300);border-radius:16px;background:var(--white);box-shadow:var(--shadow-sm)}
            .terceiros-table{width:100%;min-width:980px;border-collapse:collapse;text-align:left;font-size:.84rem}
            .terceiros-table th,.terceiros-table td{padding:.9rem 1rem;vertical-align:middle;border-bottom:1px solid var(--gray-200)}
            .terceiros-table thead th{background:var(--gray-50);color:var(--gray-600);font-size:.75rem;font-weight:600;white-space:nowrap}
            .terceiros-table tbody tr:last-child td{border-bottom:0}.terceiros-table tbody tr:hover{background:#f5faff}
            .terceiros-table th:first-child,.terceiros-table td:first-child{min-width:230px}.terceiros-table th:nth-child(2){width:150px}.terceiros-table th:nth-child(3){min-width:190px}.terceiros-table th:nth-child(4){min-width:250px}.terceiros-table th:nth-child(5){width:150px}.terceiros-table th:last-child,.terceiros-table td:last-child{width:130px;text-align:center;white-space:nowrap}
            .terceiros-table .action-btn{display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;margin:0 3px;border:1px solid var(--gray-300);border-radius:10px;background:var(--white);cursor:pointer;text-decoration:none}
            .terceiros-table .action-edit{color:var(--primary-dark)}.terceiros-table .action-delete{color:#c62828}.terceiros-table .action-btn:hover{background:var(--primary-light);border-color:var(--primary-soft)}
            .terceiros-table .empty-state{text-align:center;padding:3rem;color:var(--gray-500)}.terceiros-table .empty-state i{font-size:2rem;color:var(--primary);display:block;margin-bottom:.75rem}
            .status-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.35rem .7rem;border-radius:999px;font-size:.75rem;font-weight:600}.status-success{background:#e8f5e9;color:#2e7d32}.status-secondary{background:var(--gray-100);color:var(--gray-600)}
        </style>
        <style>
            .terceiros-section{margin-top:1.5rem;background:var(--white);border:1px solid var(--gray-200);border-radius:16px;box-shadow:var(--shadow-sm);overflow:hidden}
            .terceiros-section-header{display:flex;align-items:center;gap:.75rem;padding:1.25rem 1.5rem;border-bottom:1px solid var(--gray-200);background:var(--gray-50)}
            .terceiros-section-header h2{margin:0;color:var(--gray-800);font-size:1.2rem;display:flex;align-items:center;gap:.6rem}
            .terceiros-section-header h2 i{color:var(--primary)}
        </style>
    </head>

    <body>
        <header class="header">
            <div class="header-content">
                <div class="logo"><a href="../index.php"><i class="fas fa-users"></i><h1>Gestão de Colaboradores</h1></a></div>
                <div class="user-menu">
                    <div class="user-info"><i class="fas fa-user-circle"></i><span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário'); ?></span></div><a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i><span>Sair</span></a></div>
            </div>
            <nav class="nav-container">
                <ul class="nav-menu">
                    <li class="nav-item"><a href="../index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                    <li class="nav-item"><a href="../colaboradores/index.php" class="nav-link active"><i class="fas fa-users"></i><span>Colaboradores</span></a></li>
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
        <main class="main-container">
            <div class="page-header">
                <div>
                    <h1><i class="fas fa-user-group"></i> Terceiros</h1>
                    <p class="page-subtitle">Gerencie terceiros com cadastro separado e controle do Bit instalado</p>
                </div>
                <div style="display:flex;gap:.75rem"><a href="../colaboradores/index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Colaboradores</a><a href="adicionar.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Adicionar Terceiro</a></div>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary"><i class="fas fa-user-group"></i></div>
                    <div class="stat-content">
                        <h3>Total de Terceiros</h3>
                        <div class="stat-number">
                            <?php echo count($terceiros); ?>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success"><i class="fas fa-shield-halved"></i></div>
                    <div class="stat-content">
                        <h3>Bit Instalado</h3>
                        <div class="stat-number">
                            <?php echo count(array_filter($terceiros, fn($t) => ($t['bit_instalado'] ?? 'nao') === 'sim')); ?>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon info"><i class="fas fa-laptop-slash"></i></div>
                    <div class="stat-content">
                        <h3>Equipamentos da Empresa</h3>
                        <div class="stat-number">0</div>
                    </div>
                </div>
            </div>
            <section class="terceiros-section">
                <div class="terceiros-table-wrapper">
                    <table class="terceiros-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>CPF</th>
                                <th>Cargo</th>
                                <th>E-mail</th>
                                <th>Bit instalado</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$terceiros): ?>
                                <tr>
                                    <td colspan="6" class="empty-state"><i class="fas fa-user-group"></i>
                                        <p>Nenhum terceiro cadastrado.</p><a href="adicionar.php" class="btn btn-primary">Adicionar Terceiro</a></td>
                                </tr>
                                <?php else: foreach ($terceiros as $t): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($t['nome'] ?? ''); ?></strong></td>
                                        <td>
                                            <?php echo htmlspecialchars($t['cpf'] ?? ''); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($t['cargo'] ?? ''); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($t['email'] ?? ''); ?>
                                        </td>
                                        <td>
                                            <?php if (($t['bit_instalado'] ?? 'nao') === 'sim'): ?><span class="status-badge status-success"><i class="fas fa-check"></i> Sim</span>
                                                <?php else: ?><span class="status-badge status-secondary"><i class="fas fa-minus"></i> Não</span>
                                                    <?php endif; ?>
                                        </td>
                                        <td><a href="adicionar.php?id=<?php echo urlencode($t['id']); ?>" class="action-btn action-edit" title="Editar"><i class="fas fa-edit"></i></a>
                                            <form method="post" style="display:inline" onsubmit="return confirm('Excluir este terceiro?');">
                                                <input type="hidden" name="excluir_id" value="<?php echo htmlspecialchars($t['id']); ?>">
                                                <button class="action-btn action-delete" title="Excluir"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </body>

    </html>