# 🏢 Sistema de Gestão

Sistema completo para gestão de colaboradores, equipamentos e linhas telefônicas. Desenvolvido em PHP puro com armazenamento em JSON.

---

## 🎨 Tema Visual - Fanta Uva

| Cor | Nome | Valor Hexadecimal |
|-----|------|-------------------|
| 🟣 | Roxo Escuro | `#4A266A` |
| 🟣 | Roxo Principal | `#6B3E8F` |
| 🟣 | Roxo Claro | `#9B59B6` |
| 🟣 | Roxo Suave | `#C39BD3` |
| 🟢 | Verde | `#2ECC71` |
| 🟡 | Amarelo | `#F1C40F` |

---

## 📋 Funcionalidades

### 👥 Módulo Colaboradores

| Funcionalidade | Status |
|----------------|--------|
| Cadastro de colaboradores | ✅ |
| Edição de colaboradores | ✅ |
| Exclusão de colaboradores | ✅ |
| Listagem com ordenação alfabética | ✅ |
| Busca por nome, CPF, e-mail ou departamento | ✅ |
| Tipo de trabalho (Presencial/Home Office) | ✅ |
| Endereço completo para Home Office | ✅ |
| Vínculo com gestor | ✅ |
| Gerenciamento de termos e documentos | ✅ |
| Upload de arquivos (PDF, imagens, DOC) | ✅ |
| Organograma hierárquico | ✅ |

### 💻 Módulo Equipamentos

| Funcionalidade | Status |
|----------------|--------|
| Cadastro de equipamentos | ✅ |
| Edição de equipamentos | ✅ |
| Exclusão de equipamentos | ✅ |
| Controle de status (Estoque/Alocado/Emprestado/Manutenção/Fora de Uso) | ✅ |
| Histórico de centro de custo | ✅ |
| Atribuição automática de centro de custo via colaborador | ✅ |
| Gerenciamento por caixas | ✅ |
| Atribuição em massa por caixa | ✅ |
| Histórico de manutenções | ✅ |

### 📱 Módulo Linhas Telefônicas

| Funcionalidade | Status |
|----------------|--------|
| Cadastro de linhas (Chip Físico/E-Chip) | ✅ |
| Edição de linhas | ✅ |
| Exclusão de linhas | ✅ |
| Vincular/Desvincular colaborador | ✅ |
| Atualização automática de centro de custo | ✅ |
| Histórico de centro de custo | ✅ |
| Busca por número ou centro de custo | ✅ |
| Operadora Vivo (fixa) | ✅ |

### 📄 Termos e Documentos

| Funcionalidade | Status |
|----------------|--------|
| Upload de termos de responsabilidade | ✅ |
| Upload de termos de devolução | ✅ |
| Upload de documentos gerais | ✅ |
| Organização por pasta do colaborador | ✅ |
| Visualização e exclusão de arquivos | ✅ |

---

## 🗂️ Estrutura do Projeto

```bash
Sistema-Gestao/
├── index.php
├── login.php
├── logout.php
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── funcoes.php
│
├── css/
│   ├── colaboradores.css
│   ├── equipamentos.css
│   └── linhas/
│       ├── index.css
│       ├── adicionar.css
│       ├── editar.css
│       ├── vincular.css
│       ├── desvincular.css
│       └── excluir.css
│
├── js/
│   ├── script.js
│   └── colaboradores.js
│
├── data/
│   ├── colaboradores.json
│   ├── equipamentos.json
│   ├── linhas.json
│   └── usuarios.json
│
├── colaboradores/
├── equipamentos/
├── linhas/
└── termos/

## 🔧 Tecnologias Utilizadas

| Tecnologia    | Versão | Descrição        |
|--------------|--------|------------------|
| PHP          | 7.4+   | Backend          |
| HTML5        | -      | Estrutura        |
| CSS3         | -      | Estilização      |
| JavaScript   | ES6    | Interatividade   |
| JSON         | -      | Armazenamento    |
| Font Awesome | 6.4.0  | Ícones           |

---

### 📦 Estrutura dos Dados (JSON)

### 👤 Colaborador
```json
{
  "id": 1,
  "nome": "João Silva",
  "cargo": "Analista de Sistemas",
  "cpf": "12345678900",
  "departamento": "TI - Tecnologia",
  "centro_custo": "12001",
  "email": "joao@empresa.com.br",
  "gestor_id": 3,
  "tipo_trabalho": "home",
  "endereco": {
    "logradouro": "Rua das Flores",
    "numero": "123",
    "complemento": "Apto 45",
    "bairro": "Centro",
    "cidade": "São Paulo",
    "estado": "SP",
    "cep": "01234-567"
  },
  "data_cadastro": "2024-01-15 10:30:00",
  "data_atualizacao": "2024-01-15 10:30:00"
}

### 💻 Equipamento

```json
{
  "id": 1,
  "patrimonio": "PAT001",
  "tipo": "notebook",
  "marca": "Dell",
  "modelo": "Latitude 5420",
  "serial": "SN123456",
  "caixa": "CAIXA-001",
  "centro_custo": "12001",
  "status": "alocado",
  "colaborador_id": 1,
  "observacoes": "",
  "data_cadastro": "2024-01-15 10:30:00",
  "historico_centro_custo": []
}

### 📱 Linha Telefônica
```json
{
  "id": 1,
  "numero": "16996185975",
  "tipo": "chip",
  "centro_custo": "TI001",
  "status": "alocado",
  "colaborador_id": 1,
  "observacoes": "",
  "data_cadastro": "2024-01-15 10:30:00",
  "historico_centro_custo": []
}

### 🛡️ Segurança

- Sessão com timeout de 30 minutos  
- Verificação de autenticação  
- Sanitização de inputs  
- Proteção contra XSS (`htmlspecialchars`)  
- Validação de CPF e e-mail  



| Responsividade                   |
|--------------|-------------------|
| Dispositivo     | > 1024px       |
| Tablet          | 768px - 1024px |
|Mobile	          |< 768px         |
|Mobile Pequeno	  |< 480px         |