<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'conexao.php';

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);

    if (!empty($nome) && !empty($telefone)) {
        try {
            $sql = "INSERT INTO clientes (nome, telefone, email) VALUES (:nome, :telefone, :email)";
            $stmt = $conexao->prepare($sql);
            
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':email', $email);
            
            if ($stmt->execute()) {
                $mensagem = "<p style='color: green;'>Cliente cadastrado com sucesso!</p>";
            }
        } catch (PDOException $e) {
            $mensagem = "<p style='color: red;'>Erro ao cadastrar: " . $e->getMessage() . "</p>";
        }
    } else {
        $mensagem = "<p style='color: orange;'>Por favor, preencha os campos obrigatórios (Nome e Telefone).</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Cliente - Salão</title>
</head>
<body>
    <h2>Cadastrar Novo Cliente</h2>
    
    <?php echo $mensagem; ?>

    <form action="cadastro_cliente.php" method="POST">
        <label>Nome *:</label><br>
        <input type="text" name="nome" required><br><br>

        <label>Telefone *:</label><br>
        <input type="text" name="telefone" required><br><br>

        <label>E-mail:</label><br>
        <input type="email" name="email"><br><br>

        <button type="submit">Cadastrar Cliente</button>
    </form>
    <br>
    <a href="cadastrar_servico.php">Ir para Cadastro de Serviços</a>
</body>
</html>