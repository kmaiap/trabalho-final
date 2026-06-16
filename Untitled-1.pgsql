
CREATE TABLE  servicos (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    preco NUMERIC(10,2) NOT NULL,
    duracao INT NOT NULL 
);


CREATE TABLE clientes (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(150)
);

CREATE TABLE agendamentos (
    id SERIAL PRIMARY KEY,
    nome_cliente VARCHAR(150) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    servico VARCHAR(150) NOT NULL,
    profissional VARCHAR(150) NOT NULL,
    data_hora TIMESTAMP NOT NULL
);