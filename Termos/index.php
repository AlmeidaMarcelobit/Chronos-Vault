<?php
session_start();
require_once '../includes/funcoes.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

// Carregar dados
$colaboradores = lerArquivoJSON('../data/colaboradores/ativos.json');
if (!is_array($colaboradores)) $colaboradores = [];

$equipamentosAlocados    = carregarEquipamentosPorStatus('alocado');
$equipamentosEmprestados = carregarEquipamentosPorStatus('emprestado');
$todosAlocados = array_merge($equipamentosAlocados, $equipamentosEmprestados);

// Agrupar equipamentos por colaborador_id
$equipPorColaborador = [];
foreach ($todosAlocados as $eq) {
    $cid = $eq['colaborador_id'] ?? null;
    if ($cid) $equipPorColaborador[$cid][] = $eq;
}

// Stats footer
$total_equipamentos   = count(carregarTodosEquipamentos());
$equipamentos_estoque = count(carregarEquipamentosPorStatus('estoque'));
$total_colaboradores  = count($colaboradores);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos - Sistema de Gestão</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,500&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/favicon/favicon.png">
    <style>
        :root {
            --primary-light: #E8F4FD;
            --primary: #2196F3;
            --primary-dark: #1976D2;
            --primary-soft: #64B5F6;
            --success: #4CAF50;
            --danger: #F44336;
            --warning: #FFC107;
            --white: #FFFFFF;
            --gray-50: #FAFAFA;
            --gray-100: #F5F5F5;
            --gray-200: #EEEEEE;
            --gray-300: #E0E0E0;
            --gray-400: #BDBDBD;
            --gray-500: #9E9E9E;
            --gray-600: #757575;
            --gray-700: #616161;
            --gray-800: #424242;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --transition: all 0.2s ease;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-50) 100%); color: var(--gray-800); line-height: 1.5; }

        /* ── Header ── */
        .header { background: var(--white); border-bottom: 1px solid var(--gray-200); position: sticky; top: 0; z-index: 100; box-shadow: var(--shadow-sm); }
        .header-content { max-width: 1440px; margin: 0 auto; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .logo a { display: flex; align-items: center; gap: .75rem; text-decoration: none; color: var(--primary-dark); transition: var(--transition); }
        .logo a:hover { transform: translateY(-1px); }
        .logo i { font-size: 1.75rem; }
        .logo h1 { font-size: 1.35rem; font-weight: 600; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .user-menu { display: flex; align-items: center; gap: 1.5rem; }
        .user-info { display: flex; align-items: center; gap: .75rem; background: var(--primary-light); padding: .5rem 1rem; border-radius: 2rem; }
        .user-info i { font-size: 1.1rem; color: var(--primary); }
        .user-name { font-size: .875rem; font-weight: 500; color: var(--gray-700); }
        .logout-btn { display: flex; align-items: center; gap: .5rem; padding: .5rem 1rem; background: var(--gray-100); color: var(--gray-700); text-decoration: none; border-radius: 2rem; font-size: .875rem; transition: var(--transition); }
        .logout-btn:hover { background: var(--gray-200); transform: translateY(-1px); }
        .nav-container { background: var(--white); border-top: 1px solid var(--gray-100); }
        .nav-menu { max-width: 1440px; margin: 0 auto; padding: 0 2rem; list-style: none; display: flex; gap: 2rem; }
        .nav-link { display: flex; align-items: center; gap: .5rem; padding: 1rem 0; color: var(--gray-600); text-decoration: none; font-size: .875rem; font-weight: 500; transition: var(--transition); border-bottom: 2px solid transparent; }
        .nav-link:hover { color: var(--primary); }
        .nav-link.active { color: var(--primary); border-bottom-color: var(--primary); }

        /* ── Layout ── */
        .main-container { max-width: 900px; margin: 0 auto; padding: 2rem; }
        .page-header { margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .page-header h1 { font-size: 1.75rem; font-weight: 700; display: flex; align-items: center; gap: .75rem; }
        .page-header h1 i { color: var(--primary); }
        .page-subtitle { color: var(--gray-500); font-size: .875rem; margin-top: .25rem; margin-left: 2.5rem; }

        /* ── Card ── */
        .card { background: var(--white); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); border: 1px solid var(--gray-200); margin-bottom: 1.5rem; }
        .card-title { font-size: 1rem; font-weight: 600; color: var(--gray-700); margin-bottom: 1.25rem; display: flex; align-items: center; gap: .5rem; padding-bottom: .75rem; border-bottom: 1px solid var(--gray-200); }
        .card-title i { color: var(--primary); }

        /* ── Buttons ── */
        .btn { display: inline-flex; align-items: center; gap: .5rem; padding: .625rem 1.25rem; border-radius: var(--radius-md); font-size: .875rem; font-weight: 500; text-decoration: none; transition: var(--transition); border: none; cursor: pointer; font-family: 'Inter', sans-serif; }
        .btn-primary   { background: var(--primary); color: var(--white); }
        .btn-primary:hover   { background: var(--primary-dark); transform: translateY(-1px); box-shadow: var(--shadow-sm); }
        .btn-success   { background: var(--success); color: var(--white); }
        .btn-success:hover   { background: #388E3C; transform: translateY(-1px); }
        .btn-secondary { background: var(--gray-100); color: var(--gray-700); }
        .btn-secondary:hover { background: var(--gray-200); transform: translateY(-1px); }
        .btn:disabled  { opacity: .45; cursor: not-allowed; transform: none !important; box-shadow: none !important; }

        /* ── Form ── */
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: flex; align-items: center; gap: .5rem; font-weight: 600; color: var(--gray-700); margin-bottom: .5rem; font-size: .875rem; }
        .form-group label i { color: var(--primary); width: 1rem; }
        .form-select { width: 100%; padding: .625rem 2.5rem .625rem 1rem; font-size: .875rem; border: 1px solid var(--gray-300); border-radius: var(--radius-md); transition: var(--transition); background: var(--white); font-family: 'Inter', sans-serif; cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%232196F3' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 1rem center; }
        .form-select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(33,150,243,.1); }

        /* ── Tipo pills ── */
        .tipo-options { display: flex; gap: 1rem; flex-wrap: wrap; }
        .tipo-option { display: flex; align-items: center; justify-content: center; gap: .625rem; padding: .75rem 1.5rem; background: var(--gray-50); border-radius: var(--radius-md); cursor: pointer; transition: var(--transition); border: 2px solid var(--gray-200); flex: 1; min-width: 120px; font-size: .875rem; font-weight: 500; color: var(--gray-700); user-select: none; }
        .tipo-option:hover { border-color: var(--primary-soft); background: var(--primary-light); color: var(--primary-dark); }
        .tipo-option.selected { border-color: var(--primary); background: var(--primary-light); color: var(--primary-dark); font-weight: 600; }
        .tipo-option.selected i { color: var(--primary); }
        .tipo-option i { font-size: 1.1rem; }

        /* ── Equipment list ── */
        .select-all-bar { display: flex; align-items: center; gap: .5rem; margin-bottom: .75rem; font-size: .8rem; color: var(--gray-600); cursor: pointer; user-select: none; }
        .select-all-bar:hover { color: var(--primary); }
        .equip-list { display: flex; flex-direction: column; gap: .5rem; }
        .equip-item { display: flex; align-items: center; gap: .75rem; padding: .75rem 1rem; background: var(--gray-50); border-radius: var(--radius-sm); border: 1px solid var(--gray-200); cursor: pointer; transition: var(--transition); user-select: none; }
        .equip-item:hover { border-color: var(--primary-soft); background: var(--primary-light); }
        .equip-item.checked { border-color: var(--primary); background: var(--primary-light); }
        .equip-item input[type="checkbox"] { accent-color: var(--primary); width: 16px; height: 16px; cursor: pointer; flex-shrink: 0; }
        .equip-icon { width: 32px; height: 32px; border-radius: var(--radius-sm); background: var(--primary-light); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .equip-icon i { color: var(--primary); font-size: .875rem; }
        .equip-info { flex: 1; }
        .equip-name    { font-weight: 600; font-size: .875rem; color: var(--gray-800); }
        .equip-details { font-size: .75rem; color: var(--gray-500); margin-top: 1px; }
        .equip-pat { font-size: .75rem; font-weight: 600; color: var(--primary); white-space: nowrap; }

        /* ── Colab badge ── */
        .colab-badge { display: flex; align-items: center; gap: .75rem; padding: .875rem 1rem; background: var(--primary-light); border-radius: var(--radius-md); border: 1px solid var(--primary-soft); margin-top: 1rem; }
        .colab-badge-info { flex: 1; }
        .colab-badge-name { font-weight: 600; color: var(--gray-800); font-size: .9rem; }
        .colab-badge-sub  { font-size: .75rem; color: var(--gray-600); }
        .colab-badge-equip { font-size: .75rem; font-weight: 600; color: var(--primary); white-space: nowrap; }

        /* ── Condição ── */
        .cond-options { display: flex; flex-direction: column; gap: .5rem; }
        .cond-option { display: flex; align-items: center; gap: .75rem; padding: .625rem 1rem; background: var(--gray-50); border-radius: var(--radius-sm); border: 1px solid var(--gray-200); cursor: pointer; transition: var(--transition); font-size: .875rem; user-select: none; }
        .cond-option:hover { border-color: var(--primary-soft); }
        .cond-option.selected { border-color: var(--primary); background: var(--primary-light); font-weight: 500; color: var(--primary-dark); }
        .cond-option input { accent-color: var(--primary); cursor: pointer; }

        /* ── Upload ── */
        .upload-zone { border: 2px dashed var(--gray-300); border-radius: var(--radius-md); padding: 2rem; text-align: center; color: var(--gray-500); transition: var(--transition); cursor: pointer; }
        .upload-zone:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-light); }
        .upload-zone i { font-size: 2rem; display: block; margin-bottom: .5rem; }
        .upload-zone p { font-size: .875rem; }
        .upload-zone small { font-size: .75rem; }
        .upload-filename { margin-top: .75rem; font-size: .875rem; font-weight: 500; color: var(--primary); display: flex; align-items: center; gap: .5rem; }

        /* ── Empty state ── */
        .empty-state { text-align: center; padding: 2rem; color: var(--gray-500); }
        .empty-state i { font-size: 2.5rem; margin-bottom: .75rem; display: block; opacity: .35; }
        .empty-state p { font-size: .875rem; }

        /* ── Action bar ── */
        .action-bar { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; }

        /* ── Footer ── */
        .footer { background: var(--white); border-top: 1px solid var(--gray-200); margin-top: 3rem; }
        .footer-content { max-width: 1440px; margin: 0 auto; padding: 2rem; display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
        .footer-section h3 { font-size: .875rem; font-weight: 600; color: var(--gray-700); margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem; }
        .footer-section p { font-size: .875rem; color: var(--gray-500); }
        .footer-links { list-style: none; }
        .footer-links li { margin-bottom: .5rem; }
        .footer-links a { color: var(--gray-500); text-decoration: none; font-size: .875rem; display: inline-flex; align-items: center; gap: .5rem; transition: var(--transition); }
        .footer-links a:hover { color: var(--primary); transform: translateX(3px); }
        .footer-stats { display: flex; gap: 1rem; }
        .footer-stat .stat-number { display: block; font-size: 1.25rem; font-weight: 600; color: var(--primary); }
        .footer-stat .stat-label { font-size: .7rem; color: var(--gray-500); }
        .footer-bottom { max-width: 1440px; margin: 0 auto; padding: 1rem 2rem; border-top: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; font-size: .7rem; color: var(--gray-500); }

        @media (max-width: 768px) {
            .main-container { padding: 1rem; }
            .tipo-options { flex-direction: column; }
            .action-bar { flex-direction: column; }
            .action-bar .btn { width: 100%; justify-content: center; }
            .footer-content { grid-template-columns: 1fr; text-align: center; }
            .footer-section h3 { justify-content: center; }
        }
        @media (max-width: 480px) {
            .user-name { display: none; }
            .nav-link span { display: none; }
            .nav-link i { font-size: 1.2rem; }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="header-content">
        <div class="logo">
            <a href="../index.php">
                <i class="fas fa-laptop"></i>
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
            <li><a href="../index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li><a href="../colaboradores/index.php" class="nav-link"><i class="fas fa-users"></i><span>Colaboradores</span></a></li>
            <li><a href="../equipamentos/index.php" class="nav-link"><i class="fas fa-laptop"></i><span>Equipamentos</span></a></li>
            <li><a href="../linhas/index.php" class="nav-link"><i class="fas fa-phone"></i><span>Linhas</span></a></li>
            <li><a href="index.php" class="nav-link active"><i class="fas fa-file-signature"></i><span>Termos</span></a></li>
        </ul>
    </nav>
</header>

<main class="main-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-file-signature"></i> Termos</h1>
            <p class="page-subtitle">Gere termos de entrega e devolução de equipamentos</p>
        </div>
    </div>

    <!-- Colaborador -->
    <div class="card">
        <div class="card-title"><i class="fas fa-user"></i> Colaborador</div>
        <div class="form-group" style="margin-bottom:0;">
            <label for="sel-colab"><i class="fas fa-search"></i> Pesquisar e selecionar colaborador</label>
            <select id="sel-colab" class="form-select" onchange="onColabChange()">
                <option value="">-- Selecione um colaborador --</option>
                <?php
                usort($colaboradores, fn($a,$b) => strcmp($a['nome'], $b['nome']));
                foreach ($colaboradores as $col):
                    $qtd = count($equipPorColaborador[$col['id']] ?? []);
                ?>
                <option value="<?php echo $col['id']; ?>"
                    data-nome="<?php echo htmlspecialchars($col['nome']); ?>"
                    data-cargo="<?php echo htmlspecialchars($col['cargo'] ?? ''); ?>"
                    data-cpf="<?php echo htmlspecialchars($col['cpf'] ?? ''); ?>"
                    data-depto="<?php echo htmlspecialchars($col['departamento'] ?? ''); ?>"
                    data-qtd="<?php echo $qtd; ?>">
                    <?php echo htmlspecialchars($col['nome']); ?>
                    <?php if ($col['departamento']): ?> — <?php echo htmlspecialchars($col['departamento']); ?><?php endif; ?>
                    <?php if ($qtd > 0): ?> (<?php echo $qtd; ?> equip.)<?php endif; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="colab-badge" style="display:none;"></div>
    </div>

    <!-- Tipo de Termo -->
    <div class="card">
        <div class="card-title"><i class="fas fa-file-alt"></i> Tipo de Termo</div>
        <div class="tipo-options">
            <div class="tipo-option selected" id="opt-entrega" onclick="setTipo('entrega')">
                <i class="fas fa-file-arrow-down"></i> Entrega
            </div>
            <div class="tipo-option" id="opt-devolucao" onclick="setTipo('devolucao')">
                <i class="fas fa-rotate-left"></i> Devolução
            </div>
            <div class="tipo-option" id="opt-upload" onclick="setTipo('upload')">
                <i class="fas fa-cloud-arrow-up"></i> Upload
            </div>
        </div>
    </div>

    <!-- Dispositivos -->
    <div class="card" id="card-dispositivos">
        <div class="card-title"><i class="fas fa-laptop"></i> Dispositivos para selecionar</div>
        <div id="equip-container">
            <div class="empty-state">
                <i class="fas fa-user-slash"></i>
                <p>Selecione um colaborador para ver os equipamentos alocados.</p>
            </div>
        </div>
    </div>

    <!-- Condição (Devolução) -->
    <div class="card" id="card-condicao" style="display:none;">
        <div class="card-title"><i class="fas fa-clipboard-check"></i> Condição na Devolução</div>
        <div class="cond-options">
            <div class="cond-option selected" id="cond-perfeito" onclick="setCondicao('perfeito')">
                <input type="radio" name="condicao" checked> Em perfeito estado
            </div>
            <div class="cond-option" id="cond-defeito" onclick="setCondicao('defeito')">
                <input type="radio" name="condicao"> Apresentando defeito
            </div>
            <div class="cond-option" id="cond-pecas" onclick="setCondicao('pecas')">
                <input type="radio" name="condicao"> Faltando peças / acessórios
            </div>
        </div>
    </div>

    <!-- Upload -->
    <div class="card" id="card-upload" style="display:none;">
        <div class="card-title"><i class="fas fa-cloud-arrow-up"></i> Subir Arquivo de Termo</div>
        <p style="font-size:.8rem; color:var(--gray-500); margin-bottom:1rem;">
            O arquivo deve conter o nome do colaborador (ex: <code>Termo_NomeColaborador_01.pdf</code>).
        </p>
        <div class="upload-zone" onclick="document.getElementById('input-file').click()">
            <i class="fas fa-file-arrow-up"></i>
            <p><strong>Clique para selecionar o arquivo</strong></p>
            <small>PDF, JPG ou PNG</small>
            <input type="file" id="input-file" accept=".pdf,.jpg,.jpeg,.png" style="display:none;" onchange="onFileSelected(this)">
        </div>
        <div id="upload-info" style="display:none;" class="upload-filename"></div>
    </div>

    <!-- Ações -->
    <div class="card">
        <div class="action-bar">
            <button class="btn btn-primary" id="btn-imprimir" onclick="gerarTermo('imprimir')" disabled>
                <i class="fas fa-print"></i> Imprimir
            </button>
            <button class="btn btn-success" id="btn-pdf" onclick="gerarTermo('pdf')" disabled>
                <i class="fas fa-file-pdf"></i> Salvar em PDF
            </button>
            <button class="btn btn-secondary" onclick="limpar()">
                <i class="fas fa-broom"></i> Limpar
            </button>
        </div>
    </div>
</main>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3><i class="fas fa-laptop"></i> Sistema de Gestão</h3>
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
                <div class="footer-stat"><span class="stat-number"><?php echo $total_equipamentos; ?></span><span class="stat-label">Equipamentos</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo $equipamentos_estoque; ?></span><span class="stat-label">Em Estoque</span></div>
                <div class="footer-stat"><span class="stat-number"><?php echo $total_colaboradores; ?></span><span class="stat-label">Colaboradores</span></div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
        <p>Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
</footer>

<script>
// ── Dados PHP → JS ───────────────────────────────────────────────────────────
const equipPorColaborador = <?php echo json_encode($equipPorColaborador, JSON_UNESCAPED_UNICODE); ?>;

const ICONES = {
    desktop:'desktop', notebook:'laptop', monitor:'tv', teclado:'keyboard',
    mouse:'mouse', celular:'mobile-alt', suporte:'toolbox', fone:'headphones', outro:'plus-circle'
};
const NOMES = {
    desktop:'Desktop', notebook:'Notebook', monitor:'Monitor', teclado:'Teclado',
    mouse:'Mouse', celular:'Celular', suporte:'Suporte de Notebook', fone:'Fone', outro:'Outro'
};

// URL absoluta do logo para usar nos termos impressos
const LOGO_URL = `${location.protocol}//${location.host}/Chronos-Vault/img/logo_impressao_08_01_2026.png`;

// ── Estado ───────────────────────────────────────────────────────────────────
let tipoTermo   = 'entrega';
let condicao    = 'perfeito';
let colaborador = null;

// ── Colaborador ──────────────────────────────────────────────────────────────
function onColabChange() {
    const sel = document.getElementById('sel-colab');
    const opt = sel.options[sel.selectedIndex];

    if (!sel.value) {
        colaborador = null;
        document.getElementById('colab-badge').style.display = 'none';
        renderEquipamentos([]);
        atualizarBotoes();
        return;
    }

    colaborador = {
        id:    parseInt(sel.value),
        nome:  opt.dataset.nome,
        cargo: opt.dataset.cargo,
        cpf:   opt.dataset.cpf,
        depto: opt.dataset.depto
    };

    const qtd = parseInt(opt.dataset.qtd) || 0;
    const badge = document.getElementById('colab-badge');
    badge.style.display = 'flex';
    badge.className = 'colab-badge';
    badge.innerHTML = `
        <i class="fas fa-user-circle" style="font-size:1.5rem;color:var(--primary);flex-shrink:0;"></i>
        <div class="colab-badge-info">
            <div class="colab-badge-name">${colaborador.nome}</div>
            <div class="colab-badge-sub">${colaborador.cargo || 'Cargo não informado'}${colaborador.depto ? ' — ' + colaborador.depto : ''}</div>
        </div>
        <div class="colab-badge-equip">${qtd} equipamento${qtd !== 1 ? 's' : ''}</div>`;

    renderEquipamentos(equipPorColaborador[colaborador.id] || []);
    atualizarBotoes();
}

// ── Equipamentos ─────────────────────────────────────────────────────────────
function renderEquipamentos(equips) {
    const container = document.getElementById('equip-container');

    if (!colaborador) {
        container.innerHTML = `<div class="empty-state"><i class="fas fa-user-slash"></i><p>Selecione um colaborador para ver os equipamentos alocados.</p></div>`;
        return;
    }
    if (equips.length === 0) {
        container.innerHTML = `<div class="empty-state"><i class="fas fa-inbox"></i><p>Nenhum equipamento alocado para este colaborador.</p></div>`;
        return;
    }

    let html = `<div class="select-all-bar" onclick="toggleAll()">
        <input type="checkbox" id="chk-all" checked style="accent-color:var(--primary);cursor:pointer;" onclick="toggleAll()">
        <span>Selecionar todos</span>
    </div><div class="equip-list">`;

    equips.forEach((eq, i) => {
        const icone   = ICONES[eq.tipo] || 'question-circle';
        const nome    = NOMES[eq.tipo]  || eq.tipo;
        const marcaMod = [eq.marca, eq.modelo].filter(Boolean).join('/') || '—';
        const sn      = eq.serial || '—';
        const pat     = eq.patrimonio || '—';

        html += `<label class="equip-item checked" id="ei-${i}" onclick="toggleItem(${i},event)">
            <input type="checkbox" id="chk-${i}" checked style="accent-color:var(--primary);" onchange="onItemChk(${i})">
            <div class="equip-icon"><i class="fas fa-${icone}"></i></div>
            <div class="equip-info">
                <div class="equip-name">${nome}${eq.hostname ? ' — ' + eq.hostname : ''}</div>
                <div class="equip-details">${marcaMod} · S/N: ${sn}</div>
            </div>
            <div class="equip-pat">PAT: ${pat}</div>
        </label>`;
    });

    html += '</div>';
    container.innerHTML = html;
}

function toggleItem(i, e) {
    if (e.target.tagName === 'INPUT') return;
    const cb = document.getElementById(`chk-${i}`);
    cb.checked = !cb.checked;
    onItemChk(i);
}
function onItemChk(i) {
    document.getElementById(`ei-${i}`).classList.toggle('checked', document.getElementById(`chk-${i}`).checked);
    atualizarBotoes();
}
function toggleAll() {
    const all = document.getElementById('chk-all');
    document.querySelectorAll('.equip-list input[type="checkbox"]').forEach((cb, i) => {
        cb.checked = all.checked;
        const el = document.getElementById(`ei-${i}`);
        if (el) el.classList.toggle('checked', all.checked);
    });
    atualizarBotoes();
}
function getSelecionados() {
    if (!colaborador) return [];
    const equips = equipPorColaborador[colaborador.id] || [];
    return [...document.querySelectorAll('.equip-list input[type="checkbox"]')]
        .map((cb, i) => cb.checked ? equips[i] : null)
        .filter(Boolean);
}

// ── Tipo de termo ─────────────────────────────────────────────────────────────
function setTipo(tipo) {
    tipoTermo = tipo;
    ['entrega','devolucao','upload'].forEach(t =>
        document.getElementById(`opt-${t}`).classList.toggle('selected', t === tipo));

    document.getElementById('card-dispositivos').style.display = tipo === 'upload' ? 'none' : 'block';
    document.getElementById('card-condicao').style.display      = tipo === 'devolucao' ? 'block' : 'none';
    document.getElementById('card-upload').style.display        = tipo === 'upload' ? 'block' : 'none';
    atualizarBotoes();
}

// ── Condição ──────────────────────────────────────────────────────────────────
function setCondicao(c) {
    condicao = c;
    document.querySelectorAll('.cond-option').forEach(el => el.classList.remove('selected'));
    document.getElementById(`cond-${c}`).classList.add('selected');
    document.querySelectorAll('input[name="condicao"]').forEach((r, i) =>
        r.checked = ['perfeito','defeito','pecas'][i] === c);
}

// ── Upload ────────────────────────────────────────────────────────────────────
function onFileSelected(input) {
    const info = document.getElementById('upload-info');
    if (input.files.length) {
        info.style.display = 'flex';
        info.innerHTML = `<i class="fas fa-file-check"></i> ${input.files[0].name}`;
    } else {
        info.style.display = 'none';
    }
    atualizarBotoes();
}

// ── Botões ────────────────────────────────────────────────────────────────────
function atualizarBotoes() {
    let ok = false;
    if (tipoTermo === 'upload') {
        ok = !!colaborador && document.getElementById('input-file').files.length > 0;
    } else {
        ok = !!colaborador && getSelecionados().length > 0;
    }
    document.getElementById('btn-imprimir').disabled = !ok;
    document.getElementById('btn-pdf').disabled      = !ok;
}

function limpar() {
    document.getElementById('sel-colab').value = '';
    colaborador = null;
    document.getElementById('colab-badge').style.display = 'none';
    renderEquipamentos([]);
    setTipo('entrega');
    setCondicao('perfeito');
    document.getElementById('input-file').value = '';
    document.getElementById('upload-info').style.display = 'none';
    atualizarBotoes();
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function fmtCPF(cpf) {
    if (!cpf) return '';
    cpf = cpf.replace(/\D/g,'');
    return cpf.length === 11 ? cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/,'$1.$2.$3-$4') : cpf;
}

function buildTabela(equips) {
    const linhasVazias = Math.max(0, 6 - equips.length);
    let rows = equips.map(eq => `<tr>
        <td>${NOMES[eq.tipo] || eq.tipo}</td>
        <td>${[eq.marca, eq.modelo].filter(Boolean).join('/') || ''}</td>
        <td>${eq.serial || ''}</td>
        <td>${eq.imei || ''}</td>
        <td>${eq.patrimonio || ''}</td>
    </tr>`).join('');
    for (let i = 0; i < linhasVazias; i++)
        rows += `<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>`;
    return rows;
}

// ── CSS comum dos termos ──────────────────────────────────────────────────────
const TERMO_BASE_CSS = `
    @page { size: A4; margin: 20mm 20mm 15mm 20mm; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; line-height: 1.55; }
    table { width: 100%; border-collapse: collapse; font-size: 10pt; }
    table th { background: #f0f0f0; font-weight: bold; text-align: center; border: 1px solid #999; padding: 6px 4px; font-size: 9pt; }
    table td { border: 1px solid #999; padding: 5px 4px; text-align: center; }
    table td:first-child { text-align: left; padding-left: 6px; }
    .footer-bar { position: fixed; bottom: 0; left: 0; right: 0; background: #00BCD4;
        color: #fff; text-align: center; padding: 10px 20px;
        display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 10pt; }
    @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
`;

// ── Geração ───────────────────────────────────────────────────────────────────
function gerarTermo(acao) {
    const equips = getSelecionados();
    if (!colaborador || (!equips.length && tipoTermo !== 'upload')) return;

    const html = tipoTermo === 'entrega'
        ? htmlEntrega(colaborador, equips)
        : htmlDevolucao(colaborador, equips);

    const win = window.open('', '_blank', 'width=900,height=750');
    win.document.write(html);
    win.document.close();
    win.onload = () => { win.focus(); win.print(); };
}

function htmlEntrega(col, equips) {
    const hoje  = new Date();
    const MESES = ['janeiro','fevereiro','março','abril','maio','junho',
                   'julho','agosto','setembro','outubro','novembro','dezembro'];
    const dataLinha = `${hoje.getDate()} de ${MESES[hoje.getMonth()]} de ${hoje.getFullYear()}`;

    return `<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">
<title>Termo de Entrega — ${col.nome}</title>
<style>
${TERMO_BASE_CSS}
.page { padding-bottom: 60px; }
.logo-wrap { text-align: center; margin-bottom: 28px; }
.logo-wrap img { height: 68px; }
h2 { font-size: 11.5pt; font-weight: bold; text-transform: uppercase; margin-bottom: 22px; }
.section-lbl { font-weight: normal; text-transform: uppercase; margin-bottom: 10px; letter-spacing: .03em; }
.ident { margin-bottom: 20px; }
.ident p { margin-bottom: 3px; }
.body-text { text-align: justify; margin-bottom: 20px; }
ol { padding-left: 22px; margin-bottom: 26px; }
ol li { margin-bottom: 8px; text-align: justify; }
.sig { margin-bottom: 28px; }
.sig p { margin-bottom: 14px; }
.linha { display: inline-block; width: 300px; border-bottom: 1px solid #000; }
.table-wrap { margin-top: 6px; }
</style></head><body>
<div class="page">
    <div class="logo-wrap"><img src="${LOGO_URL}" alt="Amor Saúde"></div>

    <h2>Termo de Responsabilidade pela Guarda e Uso de Equipamento de Trabalho</h2>

    <p class="section-lbl">Identificação do Colaborador</p>

    <div class="ident">
        <p><strong>NOME:</strong> ${col.nome.toUpperCase()}</p>
        <p><strong>FUNÇÃO:</strong> ${(col.cargo||'').toUpperCase()}</p>
        <p><strong>CPF:</strong> ${fmtCPF(col.cpf)}</p>
    </div>

    <p class="body-text">Recebi da empresa <strong>AMOR SAÚDE LTDA</strong>, CNPJ <strong>27.602.235/0001-00</strong>
    a título de empréstimo, para uso exclusivo no exercício de minha função, conforme determinado em lei,
    os equipamentos especificados neste termo de responsabilidade, comprometendo-me a mantê-los em perfeito
    estado de conservação, ficando ciente de que:</p>

    <ol>
        <li>Se o equipamento for danificado ou inutilizado por emprego inadequado, mau uso, negligência ou
        extravio, a empresa me fornecerá novo equipamento e cobrará o valor de um equipamento da mesma marca
        ou equivalente.</li>
        <li>Em caso de dano, inutilização, ou extravio do equipamento deverei comunicar imediatamente ao setor
        competente.</li>
        <li>Terminando os serviços ou no caso de rescisão do contrato de trabalho, devolverei o equipamento
        completo e em perfeito estado de conservação, considerando-se o tempo do uso do mesmo (tempo de vida
        útil), ao setor competente.</li>
        <li>Estando os equipamentos em minha posse, estarei sujeito a inspeções sem prévio aviso.</li>
    </ol>

    <div class="sig">
        <p>Ribeirão Preto – SP, ${dataLinha}.</p>
        <br>
        <p>Ciente: <span class="linha"></span></p>
    </div>

    <div class="table-wrap">
        <table>
            <thead><tr>
                <th>DESCRIÇÃO</th><th>MARCA/MODELO</th><th>S/N</th><th>IMEI 1 E 2</th><th>PATRIMÔNIO</th>
            </tr></thead>
            <tbody>${buildTabela(equips)}</tbody>
        </table>
    </div>
</div>
<div class="footer-bar"><span style="font-size:18pt;">♥</span> R. Magid Antônio Calil, 176, Jardim Botânico - Ribeirão Preto-SP</div>
</body></html>`;
}

function htmlDevolucao(col, equips) {
    const hoje   = new Date();
    const dataFmt = String(hoje.getDate()).padStart(2,'0') + '/' +
                    String(hoje.getMonth()+1).padStart(2,'0') + '/' +
                    String(hoje.getFullYear()).slice(-2);

    const marks = { perfeito:['X','_','_'], defeito:['_','X','_'], pecas:['_','_','X'] };
    const m = marks[condicao] || marks.perfeito;

    return `<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">
<title>Termo de Devolução — ${col.nome}</title>
<style>
${TERMO_BASE_CSS}
.page { padding-bottom: 60px; }
.logo-wrap { margin-bottom: 6px; }
.logo-wrap img { height: 58px; }
h2 { font-size: 14pt; font-weight: bold; text-align: center; margin-bottom: 30px; }
.ident { margin: 8px 0 14px; }
.ident p { margin-bottom: 3px; }
.table-wrap { margin: 14px 0; }
.conds { margin: 18px 0; }
.conds p { margin-bottom: 8px; }
.sig-block { margin-top: 26px; }
.sig-block p { margin-bottom: 5px; }
.linha-sig { display: block; width: 320px; border-bottom: 1px solid #000; margin: 5px 0 2px; }
.sig-label { font-size: 9.5pt; color: #444; }
</style></head><body>
<div class="page">
    <div class="logo-wrap"><img src="${LOGO_URL}" alt="Amor Saúde"></div>

    <h2>Termo de Devolução de Equipamento</h2>

    <p>Devolvido por</p>
    <div class="ident">
        <p><strong>NOME:</strong> ${col.nome.toUpperCase()}</p>
        <p><strong>FUNÇÃO:</strong> ${(col.cargo||'').toUpperCase()}</p>
        <p><strong>CPF:</strong> ${fmtCPF(col.cpf)}</p>
    </div>
    <p>os equipamentos abaixo.</p>
    <p style="margin-top:10px;">Atestamos que os bens abaixo:</p>

    <div class="table-wrap">
        <table>
            <thead><tr>
                <th>DESCRIÇÃO</th><th>MARCA/MODELO</th><th>S/N</th><th>IMEI 1 E 2</th><th>PATRIMÔNIO</th>
            </tr></thead>
            <tbody>${buildTabela(equips)}</tbody>
        </table>
    </div>

    <div class="conds">
        <p>Foi devolvido em <strong>${dataFmt}</strong>, nas seguintes condições:</p>
        <p>(${m[0]}) Em perfeito estado</p>
        <p>(${m[1]}) Apresentando defeito</p>
        <p>(${m[2]}) Faltando peças/ acessórios.</p>
    </div>

    <div class="sig-block">
        <p>Ciente:</p>
        <span class="linha-sig"></span>
        <span class="sig-label">(Nome / Assinatura)</span>
    </div>

    <div class="sig-block" style="margin-top:34px;">
        <p>Responsável pelo recebimento:</p>
        <span class="linha-sig"></span>
        <span class="sig-label">(Nome / Data)</span>
    </div>
</div>
<div class="footer-bar"><span style="font-size:18pt;">♥</span> R. Magid Antônio Calil, 176, Jardim Botânico - Ribeirão Preto-SP</div>
</body></html>`;
}
</script>
</body>
</html>
