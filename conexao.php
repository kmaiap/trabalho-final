<?php
$host = '127.0.0.1'; 
$port = '5432';      
$dbname = 'lumiere_db'; 
$user = 'postgres';    
$password = 'postgres'; 

try {
    $conexao = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Garantir tabelas essenciais para funcionamento das páginas de agendamento, serviço e cliente.
    $conexao->exec("CREATE TABLE IF NOT EXISTS agendamentos (
        id SERIAL PRIMARY KEY,
        nome_cliente VARCHAR(150) NOT NULL,
        telefone VARCHAR(20) NOT NULL,
        servico VARCHAR(150) NOT NULL,
        profissional VARCHAR(150) NOT NULL,
        data_hora TIMESTAMP NOT NULL,
        observacoes TEXT
    )");
    $conexao->exec("ALTER TABLE agendamentos ADD COLUMN IF NOT EXISTS observacoes TEXT");

    $conexao->exec("CREATE TABLE IF NOT EXISTS clientes (
        id SERIAL PRIMARY KEY,
        nome VARCHAR(150) NOT NULL,
        telefone VARCHAR(20) NOT NULL,
        email VARCHAR(150)
    )");

    $conexao->exec("CREATE TABLE IF NOT EXISTS servicos (
        id SERIAL PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        preco NUMERIC(10,2) NOT NULL,
        duracao INT NOT NULL
    )");
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
?>