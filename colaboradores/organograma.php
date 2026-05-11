<?php
session_start();
require_once '../includes/funcoes.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$colaboradores = lerArquivoJSON('../data/colaboradores.json');

// Construir árvore hierárquica
function construirArvore($colaboradores) {
    $arvore = [];
    $mapa = [];

    // Criar mapa de colaboradores por ID
    foreach ($colaboradores as $colaborador) {
        $mapa[$colaborador['id']] = [
            'id' => $colaborador['id'],
            'nome' => $colaborador['nome'],
            'cargo' => $colaborador['cargo'],
            'departamento' => $colaborador['departamento'],
            'email' => $colaborador['email'] ?? null,
            'gestor_id' => $colaborador['gestor_id'] ?? null,
            'filhos' => []
        ];
    }

    // Construir árvore
    foreach ($mapa as $id => &$colaborador) {
        $gestorId = $colaborador['gestor_id'];
        if ($gestorId && isset($mapa[$gestorId])) {
            $mapa[$gestorId]['filhos'][] = &$colaborador;
        } else {
            $arvore[] = &$colaborador;
        }
    }

    return $arvore;
}

// Construir árvore hierárquica
$arvore = construirArvore($colaboradores);

// Contar total de colaboradores
$totalColaboradores = count($colaboradores);

// Encontrar colaboradores sem gestor (topo da hierarquia)
$topo = array_filter($colaboradores, function($colab) {
    return empty($colab['gestor_id']);
});
$totalTopo = count($topo);

// Função recursiva para renderizar a árvore
function renderizarArvore($nos, $nivel = 0) {
    $html = '';
    foreach ($nos as $no) {
        $temFilhos = !empty($no['filhos']);
        $html .= '<div class="org-node" style="margin-left: ' . ($nivel * 30) . 'px;">';
        $html .= '<div class="org-card ' . ($temFilhos ? 'has-children' : 'leaf') . '">';
        $html .= '<div class="org-card-header">';
        $html .= '<i class="fas fa-user-circle"></i>';
        $html .= '<div class="org-card-info">';
        $html .= '<h3>' . htmlspecialchars($no['nome']) . '</h3>';
        $html .= '<p class="org-cargo">' . htmlspecialchars($no['cargo']) . '</p>';
        $html .= '<p class="org-departamento">' . htmlspecialchars($no['departamento']) . '</p>';
        if (!empty($no['email'])) {
            $html .= '<p class="org-email"><i class="fas fa-envelope"></i> ' . htmlspecialchars($no['email']) . '</p>';
        }
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="org-card-actions">';
        $html .= '<a href="editar.php?id=' . $no['id'] . '" class="action-btn action-edit" title="Editar"><i class="fas fa-edit"></i></a>';
        $html .= '<a href="../equipamentos/index.php?colaborador=' . $no['id'] . '" class="action-btn action-equipments" title="Equipamentos"><i class="fas fa-laptop"></i></a>';
        $html .= '<a href="termos.php?id=' . $no['id'] . '" class="action-btn action-term" title="Termos"><i class="fas fa-file-pdf"></i></a>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        if ($temFilhos) {
            $html .= '<div class="org-children">';
            $html .= renderizarArvore($no['filhos'], $nivel + 1);
            $html .= '</div>';
        }
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organograma - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/colaboradores.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos específicos para o organograma */
        .org-container {
            background: var(--gray-50);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            min-height: 500px;
        }

        .org-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-xl);
            padding-bottom: var(--spacing-md);
            border-bottom: 2px solid var(--gray-200);
        }

        .org-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--grape);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .org-stats {
            display: flex;
            gap: var(--spacing-lg);
            background: var(--white);
            padding: var(--spacing-sm) var(--spacing-lg);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
        }

        .org-stat {
            text-align: center;
        }

        .org-stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--grape);
            display: block;
        }

        .org-stat-label {
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .org-tree {
            position: relative;
            padding: var(--spacing-lg) 0;
        }

        .org-node {
            position: relative;
            margin-bottom: var(--spacing-lg);
        }

        .org-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--gray-200);
            padding: var(--spacing-md);
            transition: var(--transition);
            position: relative;
            display: inline-block;
            min-width: 280px;
            width: auto;
            box-shadow: var(--shadow-sm);
        }

        .org-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: var(--grape-light);
        }

        .org-card.has-children {
            border-left: 4px solid var(--grape);
        }

        .org-card.leaf {
            border-left: 4px solid var(--gray-400);
        }

        .org-card-header {
            display: flex;
            align-items: flex-start;
            gap: var(--spacing-md);
        }

        .org-card-header i {
            font-size: 2.5rem;
            color: var(--grape);
        }

        .org-card-info {
            flex: 1;
        }

        .org-card-info h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: var(--spacing-xs);
        }

        .org-cargo {
            font-size: 0.75rem;
            color: var(--grape);
            font-weight: 500;
            margin-bottom: var(--spacing-xs);
        }

        .org-departamento {
            font-size: 0.7rem;
            color: var(--gray-500);
            margin-bottom: var(--spacing-xs);
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-xs);
            background: var(--gray-100);
            padding: 2px var(--spacing-sm);
            border-radius: var(--radius-sm);
        }

        .org-email {
            font-size: 0.7rem;
            color: var(--gray-500);
            margin-top: var(--spacing-xs);
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
        }

        .org-email i {
            font-size: 0.7rem;
            color: var(--gray-400);
        }

        .org-card-actions {
            display: flex;
            gap: var(--spacing-xs);
            margin-top: var(--spacing-md);
            padding-top: var(--spacing-sm);
            border-top: 1px solid var(--gray-100);
            justify-content: flex-end;
        }

        .org-children {
            position: relative;
            margin-left: 40px;
            padding-left: 20px;
            border-left: 2px dashed var(--gray-300);
        }

        /* Linha de conexão entre nós */
        .org-node:not(:last-child) > .org-children::before {
            content: '';
            position: absolute;
            left: -2px;
            top: -20px;
            width: 2px;
            height: 20px;
            background: var(--gray-300);
        }

        /* Visualização em grid para níveis mais baixos */
        .org-children {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }

        /* Empty state */
        .org-empty {
            text-align: center;
            padding: var(--spacing-2xl);
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--gray-200);
        }

        .org-empty i {
            font-size: 4rem;
            color: var(--gray-400);
            margin-bottom: var(--spacing-md);
        }

        .org-empty h3 {
            color: var(--gray-700);
            margin-bottom: var(--spacing-sm);
        }

        .org-empty p {
            color: var(--gray-500);
        }

        /* Modo de visualização alternativo (cards em grid) */
        .org-view-toggle {
            display: flex;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-lg);
        }

        .view-btn {
            padding: var(--spacing-sm) var(--spacing-md);
            background: var(--white);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.875rem;
        }

        .view-btn.active {
            background: var(--grape);
            border-color: var(--grape);
            color: var(--white);
        }

        .view-btn:hover:not(.active) {
            border-color: var(--grape);
            color: var(--grape);
        }

        /* Modo grid */
        .org-tree.grid-view {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: var(--spacing-lg);
        }

        .grid-view .org-node {
            margin-left: 0 !important;
            margin-bottom: 0;
        }

        .grid-view .org-children {
            display: none;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .org-container {
                padding: var(--spacing-md);
            }

            .org-header {
                flex-direction: column;
                align-items: stretch;
            }

            .org-stats {
                justify-content: space-around;
            }

            .org-card {
                min-width: 100%;
            }

            .org-children {
                margin-left: 20px;
                padding-left: 15px;
            }
        }

        @media (max-width: 480px) {
            .org-card-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .org-card-actions {
                justify-content: center;
            }
        }
    </style>
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
            </div>

            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sair</span>
            </a>
        </div>
    </div>

    <nav class="nav-container">
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="../index.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php" class="nav-link active">
                    <i class="fas fa-users"></i>
                    <span>Colaboradores</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../equipamentos/index.php" class="nav-link">
                    <i class="fas fa-laptop"></i>
                    <span>Equipamentos</span>
                </a>
            </li>
        </ul>
    </nav>
</header>

<!-- ==================== CONTEÚDO PRINCIPAL ==================== -->
<main class="main-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-sitemap"></i> Organograma</h1>
            <p class="page-subtitle">Visualize a hierarquia de gestão da sua organização</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            <span>Voltar</span>
        </a>
    </div>

    <div class="org-container">
        <div class="org-header">
            <h2><i class="fas fa-chart-line"></i> Estrutura Organizacional</h2>
            <div class="org-stats">
                <div class="org-stat">
                    <span class="org-stat-number"><?php echo $totalColaboradores; ?></span>
                    <span class="org-stat-label">Total</span>
                </div>
                <div class="org-stat">
                    <span class="org-stat-number"><?php echo $totalTopo; ?></span>
                    <span class="org-stat-label">Líderes</span>
                </div>
                <div class="org-stat">
                    <span class="org-stat-number"><?php echo $totalColaboradores - $totalTopo; ?></span>
                    <span class="org-stat-label">Subordinados</span>
                </div>
            </div>
        </div>

        <div class="org-view-toggle">
            <button class="view-btn active" onclick="toggleView('tree')">
                <i class="fas fa-sitemap"></i> Visão Hierárquica
            </button>
            <button class="view-btn" onclick="toggleView('grid')">
                <i class="fas fa-th"></i> Visão em Grade
            </button>
        </div>

        <div id="orgTree" class="org-tree">
            <?php if (empty($arvore)): ?>
                <div class="org-empty">
                    <i class="fas fa-users-slash"></i>
                    <h3>Nenhum colaborador cadastrado</h3>
                    <p>Adicione colaboradores para visualizar o organograma.</p>
                    <a href="adicionar.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Adicionar Colaborador
                    </a>
                </div>
            <?php else: ?>
                <?php echo renderizarArvore($arvore); ?>
            <?php endif; ?>
        </div>
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
                <li><a href="index.php"><i class="fas fa-users"></i> Colaboradores</a></li>
                <li><a href="../equipamentos/index.php"><i class="fas fa-laptop"></i> Equipamentos</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3>Estatísticas</h3>
            <?php
            $total_colaboradores = count(lerArquivoJSON('../data/colaboradores.json'));
            $total_equipamentos = count(lerArquivoJSON('../data/equipamentos.json'));
            $equipamentos_estoque = 0;
            $equipamentos_data = lerArquivoJSON('../data/equipamentos.json');
            foreach ($equipamentos_data as $e) {
                if (($e['status'] ?? '') === 'estoque') $equipamentos_estoque++;
            }
            ?>
            <div class="footer-stats">
                <div class="footer-stat">
                    <span class="stat-number"><?php echo $total_colaboradores; ?></span>
                    <span class="stat-label">Colaboradores</span>
                </div>
                <div class="footer-stat">
                    <span class="stat-number"><?php echo $total_equipamentos; ?></span>
                    <span class="stat-label">Equipamentos</span>
                </div>
                <div class="footer-stat">
                    <span class="stat-number"><?php echo $equipamentos_estoque; ?></span>
                    <span class="stat-label">Em Estoque</span>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
        <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
</footer>

<script>
    function toggleView(view) {
        const tree = document.getElementById('orgTree');
        const btns = document.querySelectorAll('.view-btn');

        btns.forEach(btn => btn.classList.remove('active'));

        if (view === 'tree') {
            tree.classList.remove('grid-view');
            btns[0].classList.add('active');
        } else {
            tree.classList.add('grid-view');
            btns[1].classList.add('active');
        }
    }

    // Fechar alerta após 5 segundos
    setTimeout(function() {
        const alert = document.querySelector('.global-alert');
        if (alert) {
            alert.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }
    }, 5000);
</script>
</body>
</html>