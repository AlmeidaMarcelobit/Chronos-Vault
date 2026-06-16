<?php
$departamentoAtual = $colaboradorAtual['departamento'] ?? '';

$departamentos = [
    'Administrativo',
    'Administrativo Soluções em Saúde',
    'AmorLab',
    'Assessoria Regional',
    'Atendimento',
    'Atendimento a Franquia',
    'Atendimento ao Cliente',
    'BackOffice',
    'Cadastro',
    'Cirurgias',
    'Consultoria de Performance',
    'Contabilidade',
    'CRM',
    'Dental Vidas Administrativo',
    'Desenvolvimento',
    'Dir. Operações',
    'Diretoria CEO',
    'Diretoria de Marketing',
    'Diretoria de Pessoas e Cultura',
    'Financeiro',
    'Gerência Técnica',
    'Gestão de Rede',
    'Governança TI',
    'Infraestrutura',
    'Integração',
    'Inteligência de Negócio',
    'Internacional',
    'Marketing Internacional',
    'Marketing',
    'Novos Negócios',
    'Operações de Laboratórios',
    'Pessoas e Cultura',
    'Produto',
    'Projetos',
    'Qualidade de Atendimento',
    'Relacionamento com Profissionais da Saúde',
    'Remuneração E Beneficios',
    'Retenção',
    'RT Dentista',
    'SAC',
    'SAF',
    'Suporte',
    'Telemedicina',
    'TI Tecnologia',
    'Técnico',
    'Treinamento'
];

sort($departamentos);
?>

<select id="departamento" name="departamento" required class="form-control">
    <option value="">Selecione um departamento</option>

    <?php foreach ($departamentos as $dep): ?>
        <option value="<?= htmlspecialchars($dep) ?>"
            <?= $dep === $departamentoAtual ? 'selected' : '' ?>>
            <?= htmlspecialchars($dep) ?>
        </option>
    <?php endforeach; ?>
</select>