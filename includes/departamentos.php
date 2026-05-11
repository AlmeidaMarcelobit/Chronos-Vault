<div class="form-group">
    <label for="departamento"><i class="fas fa-building"></i> Departamento <span class="required">*</span></label>
    <select id="departamento" name="departamento" required class="form-control">
        <option value="">Selecione um departamento</option>
        <option value="Dental Vidas Administrativo" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Dental Vidas Administrativo' ? 'selected' : ''; ?>>Dental Vidas Administrativo</option>
        <option value="Relacionamento com Profissionais da Saúde" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Relacionamento com Profissionais da Saúde' ? 'selected' : ''; ?>>Relacionamento com Profissionais da Saúde</option>
        <option value="AmorLab" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'AmorLab' ? 'selected' : ''; ?>>AmorLab</option>
        <option value="Financeiro" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Financeiro' ? 'selected' : ''; ?>>Financeiro</option>
        <option value="Cadastro" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Cadastro' ? 'selected' : ''; ?>>Cadastro</option>
        <option value="Infraestrutura" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Infraestrutura' ? 'selected' : ''; ?>>Infraestrutura</option>
        <option value="Retenção" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Retenção' ? 'selected' : ''; ?>>Retenção</option>
        <option value="Diretoria CEO" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Diretoria CEO' ? 'selected' : ''; ?>>Diretoria CEO</option>
        <option value="Atendimento" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Atendimento' ? 'selected' : ''; ?>>Atendimento</option>
        <option value="SAC" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'SAC' ? 'selected' : ''; ?>>SAC</option>
        <option value="SAF" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'SAF' ? 'selected' : ''; ?>>SAF</option>
        <option value="Contabilidade" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Contabilidade' ? 'selected' : ''; ?>>Contabilidade</option>
        <option value="Integração" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Integração' ? 'selected' : ''; ?>>Integração</option>
        <option value="BackOffice" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'BackOffice' ? 'selected' : ''; ?>>BackOffice</option>
        <option value="Gestão de Rede" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Gestão de Rede' ? 'selected' : ''; ?>>Gestão de Rede</option>
        <option value="Técnico" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Técnico' ? 'selected' : ''; ?>>Técnico</option>
        <option value="Remuneração E Beneficios" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Remuneração E Beneficios' ? 'selected' : ''; ?>>Remuneração E Beneficios</option>
        <option value="Consultoria de Performance" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Consultoria de Performance' ? 'selected' : ''; ?>>Consultoria de Performance</option>
        <option value="Internacional" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Internacional' ? 'selected' : ''; ?>>Internacional</option>
        <option value="Assessoria Regional" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Assessoria Regional' ? 'selected' : ''; ?>>Assessoria Regional</option>
        <option value="Qualidade de Atendimento" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Qualidade de Atendimento' ? 'selected' : ''; ?>>Qualidade de Atendimento</option>
        <option value="Cirurgias" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Cirurgias' ? 'selected' : ''; ?>>Cirurgias</option>
        <option value="Telemedicina" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Telemedicina' ? 'selected' : ''; ?>>Telemedicina</option>
        <option value="Suporte" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Suporte' ? 'selected' : ''; ?>>Suporte</option>
        <option value="Treinamento" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Treinamento' ? 'selected' : ''; ?>>Treinamento</option>
        <option value="Inteligência de Negócio" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Inteligência de Negócio' ? 'selected' : ''; ?>>Inteligência de Negócio</option>
        <option value="TI Tecnologia" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'TI Tecnologia' ? 'selected' : ''; ?>>TI Tecnologia</option>
        <option value="Atendimento a Franquia" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Atendimento a Franquia' ? 'selected' : ''; ?>>Atendimento a Franquia</option>
        <option value="Diretoria de Pessoas e Cultura" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Diretoria de Pessoas e Cultura' ? 'selected' : ''; ?>>Diretoria de Pessoas e Cultura</option>
        <option value="Governança TI" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Governança TI' ? 'selected' : ''; ?>>Governança TI</option>
        <option value="CRM" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'CRM' ? 'selected' : ''; ?>>CRM</option>
        <option value="Diretoria de Marketing" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Diretoria de Marketing' ? 'selected' : ''; ?>>Diretoria de Marketing</option>
        <option value="Marketing Internacional" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Marketing Internacional' ? 'selected' : ''; ?>>Marketing Internacional</option>
        <option value="Pessoas" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Pessoas' ? 'selected' : ''; ?>>Pessoas</option>
        <option value="Cultura" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Cultura' ? 'selected' : ''; ?>>Cultura</option>
        <option value="Produto" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Produto' ? 'selected' : ''; ?>>Produto</option>
        <option value="RT Dentista" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'RT Dentista' ? 'selected' : ''; ?>> RT Dentista</option>
        <option value="Desenvolvimento" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Desenvolvimento' ? 'selected' : ''; ?>>Desenvolvimento</option>
        <option value="Atendimento ao Cliente" <?php echo ($colaboradorAtual['departamento'] ?? '') == 'Atendimento ao Cliente' ? 'selected' : ''; ?>>Atendimento ao Cliente</option>
    </select>
</div>