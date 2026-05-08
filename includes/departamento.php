<div class="form-group">
    <label for="departamento"><i class="fas fa-building"></i> Departamento *</label>
    <select id="departamento" name="departamento" required class="form-select">
        <option value="">-- Selecione um departamento --</option>
        <option value="Dental Vidas Administrativo" <?php echo ($_POST['departamento'] ?? '') == 'Dental Vidas Administrativo' ? 'selected' : ''; ?>>Dental Vidas Administrativo</option>
        <option value="Relacionamento com Profissionais da Saúde" <?php echo ($_POST['departamento'] ?? '') == 'Relacionamento com Profissionais da Saúde' ? 'selected' : ''; ?>>Relacionamento com Profissionais da Saúde</option>
        <option value="AmorLab" <?php echo ($_POST['departamento'] ?? '') == 'AmorLab' ? 'selected' : ''; ?>>AmorLab</option>
        <option value="Financeiro" <?php echo ($_POST['departamento'] ?? '') == 'Financeiro' ? 'selected' : ''; ?>>Financeiro</option>
        <option value="Cadastro" <?php echo ($_POST['departamento'] ?? '') == 'Cadastro' ? 'selected' : ''; ?>>Cadastro</option>
        <option value="Infraestrutura" <?php echo ($_POST['departamento'] ?? '') == 'Infraestrutura' ? 'selected' : ''; ?>>Infraestrutura</option>
        <option value="Retenção" <?php echo ($_POST['departamento'] ?? '') == 'Retenção' ? 'selected' : ''; ?>>Retenção</option>
        <option value="Diretoria CEO" <?php echo ($_POST['departamento'] ?? '') == 'Diretoria CEO' ? 'selected' : ''; ?>>Diretoria CEO</option>
        <option value="Atendimento" <?php echo ($_POST['departamento'] ?? '') == 'Atendimento' ? 'selected' : ''; ?>>Atendimento</option>
        <option value="SAC" <?php echo ($_POST['departamento'] ?? '') == 'SAC' ? 'selected' : ''; ?>>SAC</option>
        <option value="SAF" <?php echo ($_POST['departamento'] ?? '') == 'SAF' ? 'selected' : ''; ?>>SAF</option>
        <option value="Integração" <?php echo ($_POST['departamento'] ?? '') == 'Integração' ? 'selected' : ''; ?>>Integração</option>
        <option value="BackOffice" <?php echo ($_POST['departamento'] ?? '') == 'BackOffice' ? 'selected' : ''; ?>>BackOffice</option>
        <option value="Gestão de Rede" <?php echo ($_POST['departamento'] ?? '') == 'Gestão de Rede' ? 'selected' : ''; ?>>Gestão de Rede</option>
        <option value="Técnico" <?php echo ($_POST['departamento'] ?? '') == 'Técnico' ? 'selected' : ''; ?>>Técnico</option>
        <option value="Remuneração E Beneficios" <?php echo ($_POST['departamento'] ?? '') == 'Remuneração E Beneficios' ? 'selected' : ''; ?>>Remuneração E Beneficios</option>
        <option value="Consultoria de Performance" <?php echo ($_POST['departamento'] ?? '') == 'Consultoria de Performance' ? 'selected' : ''; ?>>Consultoria de Performance</option>
        <option value="Internacional" <?php echo ($_POST['departamento'] ?? '') == 'Internacional' ? 'selected' : ''; ?>>Internacional</option>
        <option value="Assessoria Regional" <?php echo ($_POST['departamento'] ?? '') == 'Assessoria Regional' ? 'selected' : ''; ?>>Assessoria Regional</option>
        <option value="Qualidade de Atendimento" <?php echo ($_POST['departamento'] ?? '') == 'Qualidade de Atendimento' ? 'selected' : ''; ?>>Qualidade de Atendimento</option>
        <option value="Cirurgias" <?php echo ($_POST['departamento'] ?? '') == 'Cirurgias' ? 'selected' : ''; ?>>Cirurgias</option>
        <option value="Telemedicina" <?php echo ($_POST['departamento'] ?? '') == 'Telemedicina' ? 'selected' : ''; ?>>Telemedicina</option>
        <option value="Suporte" <?php echo ($_POST['departamento'] ?? '') == 'Suporte' ? 'selected' : ''; ?>>Suporte</option>
        <option value="Treinamento" <?php echo ($_POST['departamento'] ?? '') == 'Treinamento' ? 'selected' : ''; ?>>Treinamento</option>
        <option value="Inteligência de Negócio" <?php echo ($_POST['departamento'] ?? '') == 'Inteligência de Negócio' ? 'selected' : ''; ?>>Inteligência de Negócio</option>
        <option value="TI Tecnologia" <?php echo ($_POST['departamento'] ?? '') == 'TI Tecnologia' ? 'selected' : ''; ?>>TI Tecnologia</option>
        <option value="Atendimento a Franquia" <?php echo ($_POST['departamento'] ?? '') == 'Atendimento a Franquia' ? 'selected' : ''; ?>>Atendimento a Franquia</option>
        <option value="Diretoria de Pessoas e Cultura" <?php echo ($_POST['departamento'] ?? '') == 'Diretoria de Pessoas e Cultura' ? 'selected' : ''; ?>>Diretoria de Pessoas e Cultura</option>
        <option value="Governança TI" <?php echo ($_POST['departamento'] ?? '') == 'Governança TI' ? 'selected' : ''; ?>>Governança TI</option>
        <option value="CRM" <?php echo ($_POST['departamento'] ?? '') == 'CRM' ? 'selected' : ''; ?>>CRM</option>
        <option value="Diretoria de Marketing" <?php echo ($_POST['departamento'] ?? '') == 'Diretoria de Marketing' ? 'selected' : ''; ?>>Diretoria de Marketing</option>
        <option value="Marketing Internacional" <?php echo ($_POST['departamento'] ?? '') == 'Marketing Internacional' ? 'selected' : ''; ?>>Marketing Internacional</option>
        <option value="Pessoas" <?php echo ($_POST['departamento'] ?? '') == 'Pessoas' ? 'selected' : ''; ?>>Pessoas</option>
        <option value="Cultura" <?php echo ($_POST['departamento'] ?? '') == 'Cultura' ? 'selected' : ''; ?>>Cultura</option>
        <option value="Produto" <?php echo ($_POST['departamento'] ?? '') == 'Produto' ? 'selected' : ''; ?>>Produto</option>
        <option value="Desenvolvimento" <?php echo ($_POST['departamento'] ?? '') == 'Desenvolvimento' ? 'selected' : ''; ?>>Desenvolvimento</option>
        <option value="Atendimento ao Cliente" <?php echo ($_POST['departamento'] ?? '') == 'Atendimento ao Cliente' ? 'selected' : ''; ?>>Atendimento ao Cliente</option>
    </select>
</div>