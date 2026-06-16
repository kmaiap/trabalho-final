<?php
require_once 'conexao.php';
$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome_usuario']);
    $senha = trim($_POST['senha_usuario']);
    $senha_confirmacao = trim($_POST['senha_confirmacao']);

    if ($nome === '' || $senha === '' || $senha_confirmacao === '') {
        $mensagem = "<div class='alert alert-warning p-2 small text-center'>Preencha todos os campos.</div>";
    } elseif ($senha !== $senha_confirmacao) {
        $mensagem = "<div class='alert alert-warning p-2 small text-center'>As senhas não coincidem.</div>";
    } else {
        try {
            $stmt = $conexao->prepare('SELECT id FROM usuarios WHERE nome = :nome');
            $stmt->execute([':nome' => $nome]);
            if ($stmt->fetch()) {
                $mensagem = "<div class='alert alert-danger p-2 small text-center'>Este usuário já existe.</div>";
            } else {
                $hash = password_hash($senha, PASSWORD_DEFAULT);
                $insert = $conexao->prepare('INSERT INTO usuarios (nome, senha) VALUES (:nome, :senha)');
                $insert->execute([':nome' => $nome, ':senha' => $hash]);
                header('Location: login.php');
                exit();
            }
        } catch (PDOException $e) {
            $mensagem = "<div class='alert alert-danger p-2 small text-center'>Erro ao cadastrar: " . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário - Lumiére</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght=400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FAF9F6; color: #4A4543; display:flex; min-height:100vh; align-items:center; justify-content:center; }
        .card-register { width:100%; max-width:420px; background:white; border-radius:20px; padding:2.5rem; box-shadow:0 15px 40px rgba(0,0,0,0.08); }
        .card-register h1 { font-family: 'Playfair Display', serif; color:#D4AF37; margin-bottom:0.5rem; }
        .form-label { font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#8A8583; letter-spacing:0.05em; }
        .form-control { background:#FAF9F6; border:1px solid #EAE5DF; border-radius:10px; padding: 10px 15px; }
        .form-control:focus { border-color: #D4AF37; box-shadow: none; background: white; }
        .btn-submit { width:100%; background:#D4AF37; border:none; border-radius:50px; padding:12px; color:white; font-weight:700; text-transform:uppercase; font-size: 0.8rem; margin-top: 10px; }
        .btn-submit:hover { background:#BFA030; }
        .small-link { font-size:0.85rem; }
    </style>
</head>
<body>
    <div class="card-register">
        <h1>Lumiére</h1>
        <p class="text-muted mb-4">Crie seu usuário para acessar o sistema.</p>
        
        <?= $mensagem ?>
        
        <form action="cadastrar_usuario.php" method="POST">
            <div class="mb-3">
                <label class="form-label" for="nome_usuario">Nome de usuário</label>
                <input type="text" class="form-control" name="nome_usuario" id="nome_usuario" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="senha_usuario">Senha</label>
                <input type="password" class="form-control" name="senha_usuario" id="senha_usuario" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="senha_confirmacao">Confirme a senha</label>
                <input type="password" class="form-control" name="senha_confirmacao" id="senha_confirmacao" required>
            </div>
            <button type="submit" class="btn btn-submit">Cadastrar</button>
        </form>
        <p class="text-center mt-4 small-link mb-0">Já tem conta? <a href="login.php" class="text-decoration-none style='color:#D4AF37;'">Entrar</a></p>
    </div>
</body>
</html>