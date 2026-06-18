# ⚜️ Lumiére - Sistema de Gestão Interna para Salão de Beleza

O **Lumiére** é uma plataforma de gestão interna desenvolvida para o gerenciamento do salão Lumiére! O sistema combina uma identidade visual sofisticada, permitindo o controle de agendamentos, gerenciamento completo de clientes e serviços ofertados.

---

## Funcionalidades Principais

| Módulo | Descrição | Recursos Inclusos |
| :--- | :--- | :--- |
| ** Autenticação** | Controle de acesso seguro | Criptografia de senhas com `password_hash`, validação de sessão ativa e proteção de páginas restritas. |
| ** Dashboard** | Painel analítico em tempo real | Indicadores de faturamento diário, taxa de ocupação do salão, listagem cronológica de horários e avatares dinâmicos. |
| ** Agendamentos** | Controle de horários e profissionais | Sistema para agendar e desmarcar procedimentos de forma limpa, evitando choque de horários. |
| ** Serviços** | Catálogo de procedimentos | Cadastro simplificado de serviços com tratamento automático de valores monetários e tempos estimados. |
| ** Clientes** | Portfólio e banco de contatos | Cadastro de clientes com histórico básico, centralização de contatos (Telefone/E-mail) e iniciais automatizadas. |

---

## 🛠️ Tecnologias e Ferramentas

O ecossistema do projeto foi construído utilizando tecnologias modernas e eficientes, separadas conforme a tabela abaixo:

| Camada | Tecnologia | Utilização no Projeto |
| :--- | :--- | :--- |
| **Backend** | PHP 8.x | Processamento lógico, manipulação de sessões (`session_start`) e segurança de dados. |
| **Banco de Dados**| PostgreSQL | Persistência de dados altamente confiável, queries otimizadas e relacionamentos robustos. |
| **Drivers** | PDO (PHP Data Objects) | Camada de abstração de banco de dados para prevenção estrita de *SQL Injection*. |
| **Frontend** | Bootstrap 5.3 | Estrutura responsiva (Grid System) e componentes utilitários modernos. |
| **Estilização** | CSS3 Customizado | Paleta de cores institucional (*Dourado Lumiére*, tons neutros e tipografia Playfair Display). |
| **Ícones** | Bootstrap Icons | Identificação visual de botões, abas da barra lateral e ações do sistema. |

---

## 🗄️ Arquitetura do Banco de Dados

Abaixo está o mapeamento das tabelas estruturadas dentro do banco de dados **PostgreSQL**:

### 1. Tabela: `usuarios`
> Armazena as credenciais dos administradores e colaboradores autorizados a acessar o painel.
| Campo | Tipo | Restrições | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | SERIAL | PRIMARY KEY | Identificador único do usuário. |
| `nome` | VARCHAR(100) | UNIQUE, NOT NULL | Nome de login do usuário. |
| `senha` | VARCHAR(255) | NOT NULL | Hash de segurança gerada com `PASSWORD_DEFAULT`. |

### 2. Tabela: `clientes`
> Guarda o portfólio de clientes atendidas pelo salão.
| Campo | Tipo | Restrições | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | SERIAL | PRIMARY KEY | Identificador único da cliente. |
| `nome` | VARCHAR(150) | NOT NULL | Nome completo da cliente. |
| `telefone` | VARCHAR(20) | NOT NULL | Número de telefone para contato/confirmação. |
| `email` | VARCHAR(150) | NULL | Endereço eletrônico (opcional). |

### 3. Tabela: `servicos`
> Catálogo de procedimentos disponíveis para agendamento.
| Campo | Tipo | Restrições | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | SERIAL | PRIMARY KEY | Identificador único do serviço. |
| `nome` | VARCHAR(100) | NOT NULL | Nome do procedimento (ex: Mechas, Corte). |
| `preco` | NUMERIC(10,2) | NOT NULL | Valor do procedimento (salvo no formato americano). |
| `duracao` | INT | NOT NULL | Tempo estimado em minutos (ex: 60, 120). |

---

## 📂 Estrutura de Arquivos do Projeto

O projeto foi organizado de forma modular para facilitar manutenções futuras e garantir que formulários e visualizações rodem sem conflitos:

```text
📁 lumiere-gestao/
│
├── 📄 conexao.php             # Arquivo central de conexão PDO com o PostgreSQL
├── 📄 login.php               # Tela de autenticação dos usuários
├── 📄 cadastrar_usuario.php   # Tela de criação de novas contas administrativas
├── 📄 index.php               # Dashboard principal com métricas e tabela do dia
├── 📄 cadastrar.php           # Tela para marcar novos agendamentos
├── 📄 servicos.php            # Gestão unificada (cadastro/listagem) de procedimentos
├── 📄 clientes.php            # Gestão unificada (cadastro/listagem) de clientes
├── 📄 excluir.php             # Script lógico para remover agendamentos (via ID)
└── 📄 logout.php              # Finaliza a sessão do usuário com segurança
