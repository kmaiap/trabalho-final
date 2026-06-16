**SALÃO DE BELEZA FEMININO**


- **ETAPA 1**

     - Briefing: O sistema será um Gerenciador de Atendimentos e Serviços. Servirá para a dona do salão (ou  recepcionista) cadastrar e controlar os horários das clientes e os funcionários do salão.

- **ETAPA 2**

     - Páginas:

        -- **cadastrar.php**: O formulário para agendar um novo serviço para uma cliente.

        -- **conexao.php**: (Conexão com o banco) Faz a ponte entre o PHP e o banco de dados MySQL.

        -- **editar.php**: (Edição) Abre o formulário preenchido com os dados daquela cliente para mudar o horário ou o serviço.

        -- **excluir.php**: Deleta o agendamento do banco de dados (quando a cliente cancela) e volta para a página inicial.

        -- **index.php**: (Listagem) A tela principal. Mostra uma tabela com todos os agendamentos do salão, com botões rápidos para editar ou desmarcar (excluir).

- **ETAPA 3 e 5**

     - Estrutura do Banco de Dados: Tabela chamada "agendamentos". Ela vai guardar tudo o que o salão precisa saber sobre o atendimento.

CREATE DATABASE salao_beleza_db;
USE salao_beleza_db;

CREATE TABLE agendamentos (
   id INT AUTO_INCREMENT PRIMARY KEY,
   nome_cliente VARCHAR(100) NOT NULL,
   telefone VARCHAR(20) NOT NULL,
   servico VARCHAR(50) NOT NULL, \\ Ex: Progressiva, Manicure, Mechas
   profissional VARCHAR(50) NOT NULL, \\ Ex: Gabi Cabeleireira, Ana Nails
   data_hora DATETIME NOT NULL \\ Guarda a data e o horário juntos
);

\\ Dados de teste para você validar a listagem na Etapa 5
INSERT INTO agendamentos (nome_cliente, telefone, servico, profissional, data_hora) VALUES
('Mariana Souza', '(11) 99999-1111', 'Corte e Escova', 'Gabi Cabeleireira', '2026-06-10 14:00:00'),
('Beatriz Lima', '(11) 98888-2222', 'Unha', 'Ana Nails', '2026-06-10 15:30:00'),
('Juliana Ribeiro', '(11) 97777-3333', 'Design de Sobrancelha', 'Carla Esteticista', '2026-06-11 10:00:00');

- **ETAPA 4 e 6**

    -- **Serviço**: Em vez de um campo de texto aberto, é usado o <select> (caixa de seleção) com os serviços mais comuns do salão (ex: Corte/Escova, Tintura, Manicure/Pedicure, Depilação). Isso evita que o usuário digite errado.

    -- **Data/Hora**: É usado <input type="datetime-local">. Pois abre um calendário com relógio integrado nativo do navegador, que grava no formato certinho que o MySQL precisa (AAAA-MM-DD HH:MM:SS). 