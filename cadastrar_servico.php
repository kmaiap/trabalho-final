<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'conexao.php';

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_servico = trim($_POST['nome_servico']);
    $preco = trim($_POST['preco']);
    $duracao = trim($_POST['duracao']); 

    if (strpos($duracao, ':') !== false) {
        list($horas, $minutos) = explode(':', $duracao);
        $duracao = intval($horas) * 60 + intval($minutos);
    } else {
        $duracao = intval($duracao);
    }

    if (!empty($nome_servico) && $preco !== "") {
        try {
            $sql = "INSERT INTO servicos (nome_servico, preco, duracao) VALUES (:nome_servico, :preco, :duracao)";
            $stmt = $conexao->prepare($sql);
            
            $stmt->bindParam(':nome_servico', $nome_servico);
            $stmt->bindParam(':preco', $preco);
            $stmt->bindParam(':duracao', $duracao);
            
            if ($stmt->execute()) {
                $mensagem = "<p style='color: green;'>Serviço cadastrado com sucesso!</p>";
            }
        } catch (PDOException $e) {
            $mensagem = "<p style='color: red;'>Erro ao cadastrar serviço: " . $e->getMessage() . "</p>";
        }
    } else {
        $mensagem = "<p style='color: orange;'>Por favor, preencha os campos obrigatórios (Serviço e Preço).</p>";
    }
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Serviço</title>
</head>
<body>
    <h2>Cadastrar Novo Serviço</h2>
    
    <?php echo $mensagem; ?>

    <form action="cadastrar_servico.php" method="POST">
        <label>Nome do Serviço *:</label><br>
        <input type="text" name="nome_servico" placeholder="Ex: Corte de Cabelo" required><br><br>

        <label>Preço (R$) *:</label><br>
        <input type="number" step="0.01" name="preco" placeholder="0.00" required><br><br>

        <label>Duração (Tempo estimado):</label><br>
        <input type="time" name="duracao"><br><br>

        <button type="submit">Cadastrar Serviço</button>
    </form>
    <br>
    <a href="cadastro_cliente.php">Ir para Cadastro de Clientes</a>
</body>
</html>