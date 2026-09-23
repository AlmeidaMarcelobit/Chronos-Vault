# Chronos Vault

Sistema web interno para gestão de colaboradores, equipamentos, linhas telefônicas, documentos e solicitações de manutenção.

Desenvolvido em PHP puro, com interface responsiva e persistência em arquivos JSON.

## Funcionalidades

### Colaboradores

- Cadastro, edição, consulta e exclusão;
- Separação entre colaboradores ativos e inativos;
- Busca, ordenação, gestores e organograma;
- Tipo de trabalho presencial ou home office;
- Associação de equipamentos, linhas, termos e documentos.

### Equipamentos

- Controle de estoque, alocação, empréstimo, manutenção e descarte;
- Atribuição individual ou em massa e organização por caixas;
- Vínculo com colaborador, centro de custo e histórico de movimentações.

### Linhas telefônicas

- Cadastro de chips físicos e eSIMs;
- Vinculação e desvinculação de colaboradores;
- Atualização e relatório por centro de custo;
- Alocação individual ou em massa.

### Outros módulos

- Solicitações e logs de manutenção;
- Termos e documentos organizados por colaborador;
- Cadastro, edição e exclusão de usuários;
- Autenticação com sessão e logout.

## Tecnologias

| Tecnologia | Uso |
| --- | --- |
| PHP 7.4+ | Backend e regras de negócio |
| HTML5 / CSS3 | Estrutura, estilo e responsividade |
| JavaScript ES6 | Interações da interface |
| JSON | Persistência dos dados |
| Font Awesome 6.4.0 | Ícones |

## Requisitos e instalação

- PHP 7.4+ e servidor Apache ou Nginx;
- Permissão de leitura e escrita em `data/` e `Termos/`;
- Revise `includes/config.php` e acesse `login.php`;
- Faça backups periódicos de `data/`, `Termos/` e dos logs.

## Estrutura

```text
Chronos-Vault/
├── includes/                 # Configurações e funções compartilhadas
├── colaboradores/            # Gestão de colaboradores
├── equipamentos/             # Gestão de equipamentos
├── linhas/                   # Gestão de linhas telefônicas
├── solicitacoes_manutencao/  # Solicitações de manutenção
├── usuarios/                 # Usuários do sistema
├── Termos/                   # Documentos por colaborador
├── css/ e js/                # Interface
└── data/                     # Arquivos JSON`r``n``` 

## Segurança

Autenticação por sessão, timeout, sanitização e validação de entradas, escape contra XSS, validação de CPF/e-mail e restrição de extensões nos uploads.

## Responsividade

| Dispositivo | Largura |
| --- | --- |
| Desktop | Acima de 1024px |
| Tablet | 768px a 1024px |
| Mobile | Abaixo de 768px |
| Mobile pequeno | Abaixo de 480px |

