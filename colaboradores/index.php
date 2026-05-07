<?php
session_start();
require_once '../includes/funcoes.php';
verificarSessao();

$colaboradores = lerArquivoJSON('../data/colaboradores.json');
$equipamentos = lerArquivoJSON('../data/equipamentos.json');

// Contar equipamentos por colaborador
$equipamentosPorColaborador = [];
foreach ($equipamentos as $equipamento) {
    if ($equipamento['colaborador_id'] !== null) {
        $colaboradorId = $equipamento['colaborador_id'];
        if (!isset($equipamentosPorColaborador[$colaboradorId])) {
            $equipamentosPorColaborador[$colaboradorId] = 0;
        }
        $equipamentosPorColaborador[$colaboradorId]++;
    }
}

// Buscar por nome (se aplicável)
$busca = $_GET['busca'] ?? '';
if ($busca) {
    $colaboradores = array_filter($colaboradores, function($colaborador) use ($busca) {
        return stripos($colaborador['nome'], $busca) !== false || 
               stripos($colaborador['cpf'], $busca) !== false ||
               stripos($colaborador['departamento'], $busca) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colaboradores - Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="page-header">
            <h1><i class="fas fa-users"></i> Colaboradores</h1>
            <a href="adicionar.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Adicionar Colaborador
            </a>
        </div>
        
        <div class="search-box">
            <form method="GET" action="">
                <div class="search-input">
                    <i class="fas fa-search"></i>
                    <input type="text" name="busca" placeholder="Buscar por nome, CPF ou departamento..." 
                           value="<?php echo htmlspecialchars($busca); ?>">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
            </form>
        </div>
        
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Cargo</th>
                        <th>CPF</th>
                        <th>Departamento</th>
                        <th>Centro de Custo</th>
                        <th>Equipamentos</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($colaboradores)): ?>
                    <tr>
                        <td colspan="8" class="text-center">Nenhum colaborador encontrado.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($colaboradores as $colaborador): ?>
                        <tr>
                            <td><?php echo $colaborador['id']; ?></td>
                            <td><?php echo htmlspecialchars($colaborador['nome']); ?></td>
                            <td><?php echo htmlspecialchars($colaborador['cargo']); ?></td>
                            <td><?php echo formatarCPF($colaborador['cpf']); ?></td>
                            <td><?php echo htmlspecialchars($colaborador['departamento']); ?></td>
                            <td><?php echo htmlspecialchars($colaborador['centro_custo']); ?></td>
                            <td>
                                <?php 
                                $qtdEquipamentos = $equipamentosPorColaborador[$colaborador['id']] ?? 0;
                                echo $qtdEquipamentos . ' equipamento(s)';
                                ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="editar.php?id=<?php echo $colaborador['id']; ?>" 
                                       class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="excluir.php?id=<?php echo $colaborador['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Tem certeza que deseja excluir este colaborador?')"
                                       title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <a href="../equipamentos/?colaborador=<?php echo $colaborador['id']; ?>" 
                                       class="btn btn-sm btn-info" title="Ver Equipamentos">
                                        <i class="fas fa-laptop"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="page-footer">
            <p>Total de colaboradores: <strong><?php echo count($colaboradores); ?></strong></p>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../js/script.js"></script>
</body>
</html>