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
    'Dental Vidas',
    'Desenvolvimento',
    'Dir. Operações',
    'Diretoria CEO',
    'Diretoria de Marketing',
    'Diretoria de Pessoas e Cultura',
    'Diretoria de Expansão',
    'Financeiro',
    'Facilities',
    'Expansão',
    'Gerência Técnica',
    'Gestão de Rede',
    'Governança TI',
    'Growth',
    'Infraestrutura',
    'Integração',
    'Inteligência de Negócio',
    'Internacional',
    'Lab Gestão',
    'Marketing Internacional',
    'Marketing',
    'Novos Negócios',
    'Operações de Laboratórios',
    'Pessoas e Cultura',
    'Produto',
    'Projetos',
    'Qualidade de Atendimento',
    'Relacionamento com Profissionais da Saúde',
    'Rede Prestadora',
    'Remuneração E Beneficios',
    'Retenção',
    'RT Dentista',
    'RT Medico',
    'SAC',
    'SAF',
    'Soluções',
    'Suporte',
    'Sistemas',
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