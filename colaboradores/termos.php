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
    $_SESSION['mensagem'] = 'Colaborador não especificado.';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

$colaboradores = lerArquivoJSON('../data/colaboradores.json');

// Encontrar colaborador
$colaborador = null;
foreach ($colaboradores as $colab) {
    if ($colab['id'] == $id) {
        $colaborador = $colab;
        break;
    }
}

if (!$colaborador) {
    $_SESSION['mensagem'] = 'Colaborador não encontrado!';
    $_SESSION['mensagem_tipo'] = 'error';
    header('Location: index.php');
    exit;
}

// Criar nome seguro para a pasta do colaborador
function criarNomePasta($nome) {
    $nome = preg_replace('/[^a-zA-Z0-9]/', '_', $nome);
    $nome = preg_replace('/_+/', '_', $nome);
    return trim($nome, '_');
}

$pastaBase = '../termos/';
$nomePasta = criarNomePasta($colaborador['nome']);
$pastaColaborador = $pastaBase . $nomePasta . '/';

// Criar pasta se não existir
if (!file_exists($pastaColaborador)) {
    mkdir($pastaColaborador, 0777, true);
}

// Processar upload de arquivo
$mensagem = '';
$tipoMensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo'])) {
    $arquivo = $_FILES['arquivo'];
    $tipo_termo = $_POST['tipo_termo'] ?? 'termo_responsabilidade';
    
    // Configurações de upload
    $extensoesPermitidas = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    $tamanhoMaximo = 200 * 1024 * 1024; // 200MB
    
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    
    // Validações
    $erros = [];
    
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $erros[] = 'Erro no upload do arquivo.';
    }
    
    if (!in_array($extensao, $extensoesPermitidas)) {
        $erros[] = 'Tipo de arquivo não permitido. Use: PDF, JPG, JPEG, PNG, DOC ou DOCX.';
    }
    
    if ($arquivo['size'] > $tamanhoMaximo) {
        $erros[] = 'Arquivo muito grande. Tamanho máximo: 200MB.';
    }
    
    if (empty($erros)) {
        // Gerar nome do arquivo: Nome_Colaborador_YYYY-MM-DD_HH-MM-SS.extensao
        $nomeColaboradorLimpo = criarNomePasta($colaborador['nome']);
        $dataAtual = date('Y-m-d_H-i-s');
        $nomeArquivo = $nomeColaboradorLimpo . '_' . $dataAtual . '.' . $extensao;
        $caminhoCompleto = $pastaColaborador . $nomeArquivo;
        
        // Mover arquivo
        if (move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
            // Registrar no JSON de termos
            $termosFile = $pastaColaborador . 'termos.json';
            $termos = [];
            
            if (file_exists($termosFile)) {
                $termos = json_decode(file_get_contents($termosFile), true) ?: [];
            }
            
            $novoTermo = [
                'id' => uniqid(),
                'nome_arquivo' => $nomeArquivo,
                'nome_original' => $arquivo['name'],
                'tipo' => $tipo_termo,
                'extensao' => $extensao,
                'tamanho' => $arquivo['size'],
                'data_upload' => date('Y-m-d H:i:s'),
                'usuario' => $_SESSION['usuario_nome'] ?? 'Administrador'
            ];
            
            $termos[] = $novoTermo;
            file_put_contents($termosFile, json_encode($termos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            $mensagem = 'Arquivo enviado com sucesso!';
            $tipoMensagem = 'success';
        } else {
            $erros[] = 'Erro ao salvar o arquivo.';
        }
    }
    
    if (!empty($erros)) {
        $mensagem = implode('<br>', $erros);
        $tipoMensagem = 'error';
    }
}

// Processar exclusão de arquivo
if (isset($_GET['delete'])) {
    $arquivoDelete = $_GET['delete'];
    $arquivoPath = $pastaColaborador . $arquivoDelete;
    
    if (file_exists($arquivoPath)) {
        // Remover do JSON
        $termosFile = $pastaColaborador . 'termos.json';
        if (file_exists($termosFile)) {
            $termos = json_decode(file_get_contents($termosFile), true) ?: [];
            $termos = array_filter($termos, function($termo) use ($arquivoDelete) {
                return $termo['nome_arquivo'] !== $arquivoDelete;
            });
            file_put_contents($termosFile, json_encode(array_values($termos), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        
        // Remover arquivo físico
        unlink($arquivoPath);
        
        $_SESSION['mensagem'] = 'Arquivo excluído com sucesso!';
        $_SESSION['mensagem_tipo'] = 'success';
        header('Location: termos.php?id=' . $id);
        exit;
    }
}

// Listar arquivos existentes
$termos = [];
$termosFile = $pastaColaborador . 'termos.json';
if (file_exists($termosFile)) {
    $termos = json_decode(file_get_contents($termosFile), true) ?: [];
    // Ordenar por data decrescente
    usort($termos, function($a, $b) {
        return strtotime($b['data_upload']) - strtotime($a['data_upload']);
    });
}

// Função para formatar tamanho do arquivo
function formatarTamanho($bytes) {
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' bytes';
}

// Função para obter descrição automática baseada no tipo
function getDescricaoAutomatica($tipo, $data) {
    $tipos = [
        'termo_responsabilidade' => 'Termo de Responsabilidade',
        'termo_devolucao' => 'Termo de Devolução',
        'termo_manutencao' => 'Termo de Manutenção',
        'documento_geral' => 'Documento Geral',
        'foto_equipamento' => 'Foto do Equipamento',
        'outro' => 'Documento'
    ];
    
    $tipoTexto = $tipos[$tipo] ?? 'Documento';
    $dataFormatada = date('d/m/Y H:i', strtotime($data));
    
    return $tipoTexto . ' - ' . $dataFormatada;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Termos - <?php echo htmlspecialchars($colaborador['nome']); ?></title>
    <link rel="stylesheet" href="../css/colaboradores.css">
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
                <h1><i class="fas fa-file-contract"></i> Gerenciar Termos</h1>
                <p class="page-subtitle">
                    Colaborador: <strong><?php echo htmlspecialchars($colaborador['nome']); ?></strong>
                    (CPF: <?php echo formatarCPF($colaborador['cpf']); ?>)
                </p>
            </div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>
        
        <!-- Formulário de Upload Simplificado -->
        <div class="upload-card">
            <h3><i class="fas fa-upload"></i> Upload de Termo</h3>
            <form method="POST" enctype="multipart/form-data" class="upload-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="tipo_termo">
                            <i class="fas fa-tag"></i>
                            Tipo de Documento
                            <span class="required">*</span>
                        </label>
                        <select id="tipo_termo" name="tipo_termo" required class="form-select">
                            <option value="termo_responsabilidade">Termo de Responsabilidade</option>
                            <option value="termo_devolucao">Termo de Devolução</option>
                            <option value="termo_manutencao">Termo de Manutenção</option>
                            <option value="documento_geral">Documento Geral</option>
                            <option value="foto_equipamento">Foto do Equipamento</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="arquivo">
                            <i class="fas fa-file-upload"></i>
                            Selecionar Arquivo
                            <span class="required">*</span>
                        </label>
                        <input type="file" id="arquivo" name="arquivo" required class="form-control"
                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <small class="form-text">
                            Formatos: PDF, JPG, JPEG, PNG, DOC, DOCX | Máx: 200MB
                        </small>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Enviar Arquivo</span>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Lista de Termos -->
        <div class="termos-list">
            <h3><i class="fas fa-list"></i> Termos e Documentos</h3>
            
            <?php if (empty($termos)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>Nenhum termo ou documento cadastrado para este colaborador.</p>
                    <small>Utilize o formulário acima para fazer upload de arquivos.</small>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Tipo</th>
                                <th>Descrição</th>
                                <th>Arquivo</th>
                                <th>Tamanho</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($termos as $termo): ?>
                            <tr>
                                <td data-label="Data"><?php echo formatarData($termo['data_upload'], 'd/m/Y H:i'); ?></td>
                                <td data-label="Tipo">
                                    <span class="tipo-badge tipo-<?php echo $termo['tipo']; ?>">
                                        <i class="fas fa-<?php 
                                            echo $termo['tipo'] === 'termo_responsabilidade' ? 'file-contract' : 
                                                ($termo['tipo'] === 'termo_devolucao' ? 'box-open' : 
                                                ($termo['tipo'] === 'termo_manutencao' ? 'tools' : 
                                                ($termo['tipo'] === 'foto_equipamento' ? 'camera' : 'file'))); 
                                        ?>"></i>
                                        <?php 
                                            $tipos = [
                                                'termo_responsabilidade' => 'Termo de Responsabilidade',
                                                'termo_devolucao' => 'Termo de Devolução',
                                                'termo_manutencao' => 'Termo de Manutenção',
                                                'documento_geral' => 'Documento Geral',
                                                'foto_equipamento' => 'Foto do Equipamento',
                                                'outro' => 'Outro'
                                            ];
                                            echo $tipos[$termo['tipo']] ?? $termo['tipo'];
                                        ?>
                                    </span>
                                </td>
                                <td data-label="Descrição">
                                    <?php echo getDescricaoAutomatica($termo['tipo'], $termo['data_upload']); ?>
                                </td>
                                <td data-label="Arquivo">
                                    <a href="<?php echo '../termos/' . $nomePasta . '/' . $termo['nome_arquivo']; ?>" 
                                       target="_blank" 
                                       class="btn-link">
                                        <i class="fas fa-<?php echo $termo['extensao'] === 'pdf' ? 'file-pdf' : 'file-image'; ?>"></i>
                                        <?php echo htmlspecialchars($termo['nome_original']); ?>
                                    </a>
                                </td>
                                <td data-label="Tamanho"><?php echo formatarTamanho($termo['tamanho']); ?></td>
                                <td data-label="Ações">
                                    <div class="action-buttons">
                                        <a href="<?php echo '../termos/' . $nomePasta . '/' . $termo['nome_arquivo']; ?>" 
                                           target="_blank" 
                                           class="action-btn action-view" 
                                           title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="termos.php?id=<?php echo $id; ?>&delete=<?php echo urlencode($termo['nome_arquivo']); ?>" 
                                           class="action-btn action-delete" 
                                           onclick="return confirm('Tem certeza que deseja excluir este arquivo?')"
                                           title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
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
                        <span class="stat-number"><?php echo count($termos); ?></span>
                        <span class="stat-label">Documentos</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>Sistema de Gestão &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
            <p class="footer-version">Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
        </div>
    </footer>

    <style>
    .upload-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        border: 1px solid var(--gray-200);
        padding: var(--spacing-xl);
        margin-bottom: var(--spacing-xl);
        box-shadow: var(--shadow-sm);
    }
    
    .upload-card h3 {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-size: 1.125rem;
        color: var(--grape);
        margin-bottom: var(--spacing-lg);
        padding-bottom: var(--spacing-sm);
        border-bottom: 2px solid var(--gray-200);
    }
    
    .upload-form {
        max-width: 100%;
    }
    
    .termos-list h3 {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-size: 1.125rem;
        color: var(--grape);
        margin-bottom: var(--spacing-lg);
    }
    
    .btn-link {
        color: var(--grape);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-xs);
        transition: var(--transition);
    }
    
    .btn-link:hover {
        color: var(--grape-dark);
        text-decoration: underline;
    }
    
    .tipo-badge {
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-xs);
        padding: 4px var(--spacing-sm);
        background: var(--gray-100);
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
    }
    
    .tipo-termo_responsabilidade {
        background: rgba(107, 62, 143, 0.1);
        color: var(--grape);
    }
    
    .tipo-termo_devolucao {
        background: rgba(46, 204, 113, 0.1);
        color: var(--success);
    }
    
    .tipo-termo_manutencao {
        background: rgba(243, 156, 18, 0.1);
        color: var(--warning);
    }
    
    .tipo-foto_equipamento {
        background: rgba(52, 152, 219, 0.1);
        color: var(--info);
    }
    
    @media (max-width: 768px) {
        .upload-card {
            padding: var(--spacing-lg);
        }
    }
    </style>
    
    <script>
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