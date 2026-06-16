-- PostgreSQL-compatible database setup for Lumière
-- Execute this as a superuser or via psql: CREATE DATABASE salao_beleza_db;

-- Tables (use the database `salao_beleza_db` before running these CREATE TABLE statements)

CREATE TABLE IF NOT EXISTS usuarios (
   id SERIAL PRIMARY KEY,
   nome VARCHAR(100) NOT NULL,
   senha VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS agendamentos (
   id SERIAL PRIMARY KEY,
   nome_cliente VARCHAR(100) NOT NULL,
   telefone VARCHAR(20) NOT NULL,
   servico VARCHAR(100) NOT NULL,
   profissional VARCHAR(100) NOT NULL,
   data_hora TIMESTAMP NOT NULL
);

CREATE TABLE IF NOT EXISTS clientes (
   id SERIAL PRIMARY KEY,
   nome VARCHAR(100) NOT NULL,
   telefone VARCHAR(20) NOT NULL,
   email VARCHAR(150)
);

CREATE TABLE IF NOT EXISTS servicos (
   id SERIAL PRIMARY KEY,
   nome_servico VARCHAR(100) NOT NULL,
   preco NUMERIC(10,2) NOT NULL,
   duracao INTERVAL
);

-- Sample data
INSERT INTO agendamentos (nome_cliente, telefone, servico, profissional, data_hora) VALUES
('Mariana Souza', '(11) 99999-1111', 'Corte e Escova', 'Gabi Cabeleireira', '2026-06-10 14:00:00'),
('Beatriz Lima', '(11) 98888-2222', 'Unha em Gel', 'Ana Nails', '2026-06-10 15:30:00'),
('Juliana Ribeiro', '(11) 97777-3333', 'Design de Sobrancelha', 'Carla Estética', '2026-06-11 10:00:00');
