<?php
session_start();
require_once '../includes/funcoes.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$mensagem = '';
$tipoMensagem = '';

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar e sanitizar os dados
    $marca = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $patrimonio = trim($_POST['patrimonio'] ?? '');
    $serial = trim($_POST['serial'] ?? '');
    $centro_custo = trim($_POST['centro_custo'] ?? '');
    $status = $_POST['status'] ?? 'estoque';
    $tipo = $_POST['tipo'] ?? 'notebook';
    $colaborador_id = $_POST['colaborador_id'] ?? null;
    $caixa_id = trim($_POST['caixa'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');
    
    // Validações
    $erros = [];
    
    if (empty($marca)) {
        $erros[] = 'A marca é obrigatória.';
    }
    
    if (empty($modelo)) {
        $erros[] = 'O modelo é obrigatório.';
    }
    
    if (empty($patrimonio)) {
        $erros[] = 'O número de patrimônio é obrigatório.';
    }
    
    if (empty($centro_custo)) {
        $erros[] = 'O centro de custo é obrigatório.';
    }
    
    // Verificar se precisa de colaborador (para alocado ou emprestado)
    if (($status === 'alocado' || $status === 'emprestado') && empty($colaborador_id)) {
        $erros[] = 'Selecione um colaborador para ' . ($status === 'emprestado' ? 'emprestar' : 'alocar') . ' o equipamento.';
    }
    
    // Verificar se patrimônio já existe
    $equipamentos = lerArquivoJSON('../data/equipamentos.json');
    if ($equipamentos === false) {
        $equipamentos = [];
    }
    
    foreach ($equipamentos as $equipamento) {
        if ($equipamento['patrimonio'] === $patrimonio) {
            $erros[] = 'Este número de patrimônio já está cadastrado no sistema.';
            break;
        }
        if (!empty($serial) && isset($equipamento['serial']) && $equipamento['serial'] === $serial) {
            $erros[] = 'Este número de série já está cadastrado no sistema.';
            break;
        }
    }
    
    if (empty($erros)) {
        // Criar novo equipamento
        $novoEquipamento = [
            'id' => gerarId($equipamentos),
            'marca' => $marca,
            'modelo' => $modelo,
            'patrimonio' => $patrimonio,
            'serial' => $serial,
            'caixa' => $caixa_id,
            'tipo' => $tipo,
            'centro_custo' => $centro_custo,
            'colaborador_id' => ($status === 'alocado' || $status === 'emprestado') ? $colaborador_id : null,
            'status' => $status,
            'observacoes' => $observacoes,
            'data_cadastro' => date('Y-m-d H:i:s'),
            'data_atribuicao' => ($status === 'alocado' || $status === 'emprestado') ? date('Y-m-d H:i:s') : null,
            'data_atualizacao' => date('Y-m-d H:i:s')
        ];
        
        // Adicionar ao array
        $equipamentos[] = $novoEquipamento;
        
        // Salvar no JSON
        if (salvarArquivoJSON('../data/equipamentos.json', $equipamentos)) {
            $mensagem = 'Equipamento cadastrado com sucesso!';
            $tipoMensagem = 'success';
            
            // Limpar o formulário
            $_POST = [];
        } else {
            $mensagem = 'Erro ao salvar o equipamento. Tente novamente.';
            $tipoMensagem = 'error';
        }
    } else {
        $mensagem = implode('<br>', $erros);
        $tipoMensagem = 'error';
    }
}

// Carregar colaboradores para o select
$colaboradores = lerArquivoJSON('../data/colaboradores.json');
if ($colaboradores === false) {
    $colaboradores = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Equipamento - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/equipamentos.css">
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
                    <a href="../colaboradores/index.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>Colaboradores</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php" class="nav-link active">
                        <i class="fas fa-laptop"></i>
                        <span>Equipamentos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../linhas/index.php" class="nav-link">
                        <i class="fas fa-phone"></i>
                        <span>Linhas</span>
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
                <h1><i class="fas fa-laptop-medical"></i> Adicionar Novo Equipamento</h1>
                <p class="page-subtitle">Preencha os dados abaixo para cadastrar um novo equipamento</p>
            </div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>
        
          <div class="form-container">
            <form method="POST" action="" class="form-card" id="form-equipamento">
                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo" class="form-label"><i class="fas fa-tag"></i> Tipo de Equipamento *</label>
                        <select id="tipo" name="tipo" required class="form-select form-control">
                            <option value="">-- Selecione o tipo --</option>
                            <?php foreach (getTiposEquipamentos() as $key => $value): ?>
                            <option value="<?php echo $key; ?>"
                                    <?php echo ($_POST['tipo'] ?? 'notebook') == $key ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="marca">
                            <i class="fas fa-industry"></i>
                            <span>Marca</span>
                            <span class="required">*</span>
                        </label>
                        <input type="text" id="marca" name="marca" 
                               value="<?php echo htmlspecialchars($_POST['marca'] ?? ''); ?>" 
                               required class="form-control" 
                               placeholder="Ex: Dell, HP, Lenovo, Samsung">
                    </div>

                    <div class="form-group">
                        <label for="modelo">
                            <i class="fas fa-laptop"></i>
                            <span>Modelo</span>
                            <span class="required">*</span>
                        </label>
                        <input type="text" id="modelo" name="modelo" 
                               value="<?php echo htmlspecialchars($_POST['modelo'] ?? ''); ?>" 
                               required class="form-control" 
                               placeholder="Ex: Latitude 5420, iPhone 13, MX Keys">
                    </div>
                    
                    <div class="form-group">
                        <label for="centro_custo">
                            <i class="fas fa-dollar-sign"></i>
                            <span>Centro de Custo</span>
                            <span class="required">*</span>
                        </label>
                        <input type="text" id="centro_custo" name="centro_custo" 
                               value="<?php echo htmlspecialchars($_POST['centro_custo'] ?? ''); ?>" 
                               required class="form-control" 
                               placeholder="Ex: TI001, ADM002">
                    </div>

                    <div class="form-group">
                        <label for="caixa">
                            <i class="fas fa-box"></i>
                            <span>Caixa</span>
                        </label>
                        <input type="text" id="caixa" name="caixa"
                               value="<?php echo htmlspecialchars($_POST['caixa'] ?? ''); ?>"
                               class="form-control"
                               placeholder="Ex: 011, 025, etc.">
                        <small class="form-text">Código de identificação do caixa (opcional)</small>
                    </div>

                    <div class="form-group">
                        <label for="patrimonio">
                            <i class="fas fa-barcode"></i>
                            <span>Número de Patrimônio</span>
                            <span class="required">*</span>
                        </label>
                        <input type="text" id="patrimonio" name="patrimonio" 
                               value="<?php echo htmlspecialchars($_POST['patrimonio'] ?? ''); ?>" 
                               required class="form-control" 
                               placeholder="Ex: PAT001, TI-2023-001">
                        <small class="form-text">Código único de identificação do patrimônio</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="serial">
                            <i class="fas fa-hashtag"></i>
                            <span>Número de Série</span>
                        </label>
                        <input type="text" id="serial" name="serial" 
                               value="<?php echo htmlspecialchars($_POST['serial'] ?? ''); ?>" 
                               class="form-control" 
                               placeholder="Ex: SN123456789">
                        <small class="form-text">Número de série do fabricante (opcional)</small>
                    </div>
                </div>

                <!-- Status do Equipamento -->
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Status do Equipamento *</label>
                    <div class="status-options">
                        <?php 
                        $statusSelecionado = $_POST['status'] ?? 'estoque';
                        ?>
                        
                        <label class="status-option">
                            <input type="radio" name="status" value="estoque" 
                                   <?php echo $statusSelecionado == 'estoque' ? 'checked' : ''; ?>
                                   onchange="toggleColaboradorSelect(false)">
                            <span class="status-dot status-dot-estoque"></span>
                            <span>Em Estoque</span>
                        </label>
                        
                        <label class="status-option">
                            <input type="radio" name="status" value="alocado" 
                                   <?php echo $statusSelecionado == 'alocado' ? 'checked' : ''; ?>
                                   onchange="toggleColaboradorSelect(true)">
                            <span class="status-dot status-dot-alocado"></span>
                            <span>Alocado para Colaborador</span>
                        </label>
                        
                        <label class="status-option">
                            <input type="radio" name="status" value="emprestado" 
                                   <?php echo $statusSelecionado == 'emprestado' ? 'checked' : ''; ?>
                                   onchange="toggleColaboradorSelect(true)">
                            <span class="status-dot status-dot-emprestado"></span>
                            <span>Emprestado</span>
                        </label>
                        
                        <label class="status-option">
                            <input type="radio" name="status" value="manutencao" 
                                   <?php echo $statusSelecionado == 'manutencao' ? 'checked' : ''; ?>
                                   onchange="toggleColaboradorSelect(false)">
                            <span class="status-dot status-dot-manutencao"></span>
                            <span>Em Manutenção</span>
                        </label>
                        
                        <label class="status-option">
                            <input type="radio" name="status" value="fora_uso" 
                                   <?php echo $statusSelecionado == 'fora_uso' ? 'checked' : ''; ?>
                                   onchange="toggleColaboradorSelect(false)">
                            <span class="status-dot status-dot-forauso"></span>
                            <span>Fora de Uso</span>
                        </label>
                    </div>
                </div>
                
                <!-- Select Colaborador (dinâmico) -->
                <div class="form-group" id="colaborador-select" 
                     style="display: <?php echo in_array(($statusSelecionado), ['alocado', 'emprestado']) ? 'block' : 'none'; ?>;">
                    <label for="colaborador_id">
                        <i class="fas fa-user"></i>
                        <span>Selecionar Colaborador</span>
                        <span class="required">*</span>
                    </label>
                    <select id="colaborador_id" name="colaborador_id" class="form-select"
                            <?php echo in_array(($statusSelecionado), ['alocado', 'emprestado']) ? 'required' : ''; ?>>
                        <option value="">-- Selecione um colaborador --</option>
                        <?php foreach ($colaboradores as $colaborador): ?>
                        <option value="<?php echo $colaborador['id']; ?>"
                                <?php echo ($_POST['colaborador_id'] ?? '') == $colaborador['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($colaborador['nome'] . ' - ' . $colaborador['departamento']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Observações -->
                <div class="form-group">
                    <label for="observacoes">
                        <i class="fas fa-sticky-note"></i>
                        <span>Observações</span>
                    </label>
                    <textarea id="observacoes" name="observacoes" class="form-control" 
                              rows="3" placeholder="Observações, características especiais, problemas conhecidos..."><?php echo htmlspecialchars($_POST['observacoes'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <span>Salvar Equipamento</span>
                    </button>
                    <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                        <i class="fas fa-redo"></i>
                        <span>Limpar</span>
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        <span>Cancelar</span>
                    </a>
                </div>
            </form>
            
            <div class="info-card">
                <h4><i class="fas fa-info-circle"></i> Informações Importantes</h4>
                <ul>
                    <li>Todos os campos marcados com <span class="required">*</span> são obrigatórios.</li>
                    <li>O número de patrimônio deve ser único no sistema.</li>
                    <li><strong>Em Estoque:</strong> Equipamento disponível para uso.</li>
                    <li><strong>Alocado:</strong> Equipamento em uso por um colaborador.</li>
                    <li><strong>Emprestado:</strong> Equipamento emprestado temporariamente.</li>
                    <li><strong>Em Manutenção:</strong> Equipamento em conserto ou manutenção.</li>
                    <li><strong>Fora de Uso:</strong> Equipamento quebrado, obsoleto ou aguardando descarte.</li>
                </ul>
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
                    <li><a href="../colaboradores/index.php"><i class="fas fa-users"></i> Colaboradores</a></li>
                    <li><a href="index.php"><i class="fas fa-laptop"></i> Equipamentos</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Estatísticas</h3>
                <?php
                $total_equipamentos = count(lerArquivoJSON('../data/equipamentos.json'));
                $equipamentos_data = lerArquivoJSON('../data/equipamentos.json');
                $equipamentos_estoque = 0;
                foreach ($equipamentos_data as $e) {
                    if (($e['status'] ?? '') === 'estoque') $equipamentos_estoque++;
                }
                ?>
                <div class="footer-stats">
                    <div class="footer-stat">
                        <span class="stat-number"><?php echo $total_equipamentos; ?></span>
                        <span class="stat-label">Equipamentos</span>
                    </div>
                    <div class="footer-stat">
                        <span class="stat-number"><?php echo $equipamentos_estoque; ?></span>
                        <span class="stat-label">Em Estoque</span>
                    </div>
                    <div class="footer-stat">
                        <span class="stat-number"><?php echo count($colaboradores); ?></span>
                        <span class="stat-label">Colaboradores</span>
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
        // Fechar alerta após 5 segundos
        setTimeout(function() {
            const alert = document.querySelector('.global-alert');
            if (alert) {
                alert.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
        
        function toggleColaboradorSelect(show) {
            const selectDiv = document.getElementById('colaborador-select');
            const selectElement = document.getElementById('colaborador_id');
            
            if (show) {
                selectDiv.style.display = 'block';
                selectElement.required = true;
            } else {
                selectDiv.style.display = 'none';
                selectElement.required = false;
                selectElement.value = '';
            }
        }
        
        function resetForm() {
            if (confirm('Tem certeza que deseja limpar todos os campos?')) {
                document.getElementById('form-equipamento').reset();
                toggleColaboradorSelect(false);
            }
        }
        
        // Auto-formatar centro de custo
        const ccInput = document.getElementById('centro_custo');
        if (ccInput) {
            ccInput.addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase();
                value = value.replace(/[^A-Z0-9]/g, '');
                e.target.value = value;
            });
        }
        
        // Validação do formulário
        const form = document.getElementById('form-equipamento');
        if (form) {
            form.addEventListener('submit', function(e) {
                const patrimonio = document.getElementById('patrimonio').value.trim();
                const tipo = document.getElementById('tipo').value;
                const statusRadio = document.querySelector('input[name="status"]:checked');
                
                if (!statusRadio) {
                    alert('Selecione o status do equipamento.');
                    e.preventDefault();
                    return false;
                }
                
                const status = statusRadio.value;
                const colaborador = document.getElementById('colaborador_id').value;
                
                if (patrimonio.length < 3) {
                    alert('O número de patrimônio deve ter pelo menos 3 caracteres.');
                    e.preventDefault();
                    return false;
                }
                
                if (tipo === '') {
                    alert('Selecione o tipo de equipamento.');
                    e.preventDefault();
                    return false;
                }
                
                if ((status === 'alocado' || status === 'emprestado') && colaborador === '') {
                    alert('Selecione um colaborador para ' + (status === 'emprestado' ? 'emprestar' : 'alocar') + ' o equipamento.');
                    e.preventDefault();
                    return false;
                }
                
                return true;
            });
        }
        
        // Inicializar estado do colaborador select
        document.addEventListener('DOMContentLoaded', function() {
            const statusRadio = document.querySelector('input[name="status"]:checked');
            if (statusRadio) {
                const status = statusRadio.value;
                toggleColaboradorSelect(status === 'alocado' || status === 'emprestado');
            }
        });
    </script>
</body>
</html>